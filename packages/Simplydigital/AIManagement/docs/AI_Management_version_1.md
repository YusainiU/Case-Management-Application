Can you give me full and detailed implementation of the version 1 using the project structure suggested.

Absolutely. For V1, I’d keep the implementation deliberately narrow: an authenticated case user can ask the AI to analyze a case, and the assistant returns a persisted, structured analysis containing a summary, suggested category, suggested priority, next actions, customer-response draft, and warnings.

Below is a complete package-level implementation that fits the architecture we've already established.

Assumptions: Laravel 11/12, Livewire 3, Spatie Permission, queued jobs, and your existing Simplydigital\CaseManagement package. I’ll use CaseModel below to avoid confusion with PHP's case keyword; replace it with your actual Case model class.

1. Final V1 architecture
packages/
├── Simplydigital/
│   ├── AIManagement/
│   │   ├── config/
│   │   │   └── ai-management.php
│   │   ├── database/
│   │   │   └── migrations/
│   │   │       └── xxxx_xx_xx_create_ai_analyses_table.php
│   │   ├── src/
│   │   │   ├── AI/
│   │   │   │   ├── Contracts/
│   │   │   │   │   └── AIService.php
│   │   │   │   ├── DTO/
│   │   │   │   │   ├── CaseAssistantResult.php
│   │   │   │   │   └── CaseContext.php
│   │   │   │   ├── Prompts/
│   │   │   │   │   └── CaseAssistantPrompt.php
│   │   │   │   ├── Providers/
│   │   │   │   │   └── OpenAIService.php
│   │   │   │   └── Services/
│   │   │   │       └── CaseAssistant.php
│   │   │   ├── Authorization/
│   │   │   │   └── Permissions/
│   │   │   │       └── AIPermission.php
│   │   │   ├── Jobs/
│   │   │   │   └── GenerateCaseAssistantAnalysis.php
│   │   │   ├── Livewire/
│   │   │   │   └── CaseAssistant.php
│   │   │   ├── Models/
│   │   │   │   └── AIAnalysis.php
│   │   │   ├── Policies/
│   │   │   │   └── CaseAssistantPolicy.php
│   │   │   ├── Providers/
│   │   │   │   └── AIManagementServiceProvider.php
│   │   │   └── Resources/
│   │   │       └── views/
│   │   │           └── livewire/
│   │   │               └── case-assistant.blade.php
│   │   ├── routes/
│   │   │   └── web.php
│   │   └── composer.json
│   │
│   └── CaseManagement/
│       └── ...

The important dependency direction is:

CaseManagement
      │
      │ provides case context
      ▼
AIManagement
      │
      │ calls
      ▼
AI provider

I would not make AIManagement depend directly on CaseManagement if you want AIManagement to remain independently reusable. Instead, CaseManagement integrates with AIManagement.

2. AIManagement/composer.json

I'd start with:

{
    "name": "simplydigital/ai-management",
    "description": "AI management functionality for Simplydigital applications",
    "type": "library",
    "license": "proprietary",
    "require": {
        "php": "^8.2",
        "illuminate/contracts": "^11.0|^12.0",
        "illuminate/database": "^11.0|^12.0",
        "illuminate/http": "^11.0|^12.0",
        "illuminate/support": "^11.0|^12.0",
        "livewire/livewire": "^3.0",
        "spatie/laravel-permission": "^6.0"
    },
    "autoload": {
        "psr-4": {
            "Simplydigital\\AIManagement\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Simplydigital\\AIManagement\\Providers\\AIManagementServiceProvider"
            ]
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}

If your root application already requires these packages, you can of course adjust the constraints.

3. Configuration

Create:

AIManagement/config/ai-management.php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    */

    'provider' => env('AI_MANAGEMENT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    */

    'model' => env('AI_MANAGEMENT_MODEL'),

    /*
    |--------------------------------------------------------------------------
    | Case Assistant
    |--------------------------------------------------------------------------
    */

    'case_assistant' => [

        'enabled' => env(
            'AI_CASE_ASSISTANT_ENABLED',
            true
        ),

        'queue' => env(
            'AI_CASE_ASSISTANT_QUEUE',
            'default'
        ),

        'prompt_version' => 'case-assistant-v1',

        /*
         * Maximum amount of case comments sent to the AI.
         */
        'max_comments' => 50,

        /*
         * Maximum amount of characters from each comment.
         */
        'max_comment_length' => 5000,

        /*
         * Maximum response tokens.
         */
        'max_output_tokens' => 2000,
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI
    |--------------------------------------------------------------------------
    */

    'openai' => [

        'api_key' => env('OPENAI_API_KEY'),

        'base_url' => env(
            'OPENAI_BASE_URL',
            'https://api.openai.com/v1'
        ),

    ],

];

And in .env:

AI_MANAGEMENT_PROVIDER=openai
AI_MANAGEMENT_MODEL=your-model
AI_CASE_ASSISTANT_ENABLED=true

OPENAI_API_KEY=your-api-key

The actual model name should be selected based on the provider/model you intend to use rather than hard-coding a model into the package.

4. AI service contract

Create:

src/AI/Contracts/AIService.php
<?php

namespace Simplydigital\AIManagement\AI\Contracts;

interface AIService
{
    /**
     * Generate unstructured text.
     */
    public function generate(string $prompt): string;

    /**
     * Generate structured JSON data.
     *
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    public function generateStructured(
        string $prompt,
        array $schema
    ): array;
}

This is the abstraction that keeps the rest of your application independent from OpenAI or any other provider.

5. Case context DTO

Create:

src/AI/DTO/CaseContext.php
<?php

namespace Simplydigital\AIManagement\AI\DTO;

final readonly class CaseContext
{
    /**
     * @param array<string, mixed> $customer
     * @param array<string, mixed> $asset
     * @param array<int, array<string, mixed>> $comments
     */
    public function __construct(
        public int|string $id,
        public string $title,
        public string $description,
        public ?string $status,
        public ?string $priority,
        public ?string $category,
        public array $customer = [],
        public array $asset = [],
        public array $comments = [],
    ) {
    }
}

Notice that this DTO contains only information deliberately selected for AI processing.

Don't pass the Eloquent model directly to the AI provider.

6. Case Assistant result DTO

Create:

src/AI/DTO/CaseAssistantResult.php
<?php

namespace Simplydigital\AIManagement\AI\DTO;

final readonly class CaseAssistantResult
{
    /**
     * @param array<int, string> $suggestedNextActions
     * @param array<int, int|string> $similarCaseIds
     * @param array<int, string> $warnings
     */
    public function __construct(
        public string $summary,
        public ?string $suggestedCategory,
        public ?string $suggestedPriority,
        public array $suggestedNextActions,
        public array $similarCaseIds,
        public ?string $customerResponseDraft,
        public array $warnings,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'summary' => $this->summary,
            'suggested_category' => $this->suggestedCategory,
            'suggested_priority' => $this->suggestedPriority,
            'suggested_next_actions' => $this->suggestedNextActions,
            'similar_case_ids' => $this->similarCaseIds,
            'customer_response_draft' => $this->customerResponseDraft,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            summary: (string) ($data['summary'] ?? ''),

            suggestedCategory:
                isset($data['suggested_category'])
                    ? (string) $data['suggested_category']
                    : null,

            suggestedPriority:
                isset($data['suggested_priority'])
                    ? (string) $data['suggested_priority']
                    : null,

            suggestedNextActions:
                array_values(
                    $data['suggested_next_actions'] ?? []
                ),

            similarCaseIds:
                array_values(
                    $data['similar_case_ids'] ?? []
                ),

            customerResponseDraft:
                isset($data['customer_response_draft'])
                    ? (string) $data['customer_response_draft']
                    : null,

            warnings:
                array_values(
                    $data['warnings'] ?? []
                ),
        );
    }
}

This makes serialization/deserialization straightforward.

7. Prompt builder

Create:

src/AI/Prompts/CaseAssistantPrompt.php
<?php

namespace Simplydigital\AIManagement\AI\Prompts;

use Simplydigital\AIManagement\AI\DTO\CaseContext;

class CaseAssistantPrompt
{
    public function build(CaseContext $context): string
    {
        return <<<PROMPT
You are an AI assistant helping a customer support agent analyze a support case.

Your task is to analyze the supplied case information and provide useful suggestions.

You are an assistant, not the decision maker.

You MUST:
- summarize only information supported by the supplied case data
- clearly identify uncertainty
- suggest a category when there is enough evidence
- suggest a priority when there is enough evidence
- suggest practical next actions
- draft a professional customer response
- identify missing or potentially important information

You MUST NOT:
- invent facts
- claim that a diagnosis is certain unless the supplied data establishes it
- make contractual SLA decisions
- claim that an action has been performed
- modify any system data
- assume that your suggestions are automatically correct

The application will validate any suggestions before applying them.

CASE INFORMATION

Case ID:
{$context->id}

Title:
{$context->title}

Description:
{$context->description}

Current status:
{$context->status}

Current priority:
{$context->priority}

Current category:
{$context->category}

CUSTOMER

{$this->formatCustomer($context->customer)}

ASSET

{$this->formatAsset($context->asset)}

CASE COMMENTS

{$this->formatComments($context->comments)}

Return your response according to the supplied structured output schema.
PROMPT;
    }

    /**
     * @param array<string, mixed> $customer
     */
    protected function formatCustomer(array $customer): string
    {
        if ($customer === []) {
            return 'No customer information supplied.';
        }

        return collect($customer)
            ->map(
                fn ($value, $key) =>
                    "{$key}: {$value}"
            )
            ->implode("\n");
    }

    /**
     * @param array<string, mixed> $asset
     */
    protected function formatAsset(array $asset): string
    {
        if ($asset === []) {
            return 'No asset information supplied.';
        }

        return collect($asset)
            ->map(
                fn ($value, $key) =>
                    "{$key}: {$value}"
            )
            ->implode("\n");
    }

    /**
     * @param array<int, array<string, mixed>> $comments
     */
    protected function formatComments(array $comments): string
    {
        if ($comments === []) {
            return 'No comments supplied.';
        }

        return collect($comments)
            ->map(function (array $comment): string {
                return sprintf(
                    '[%s] %s: %s',
                    $comment['created_at'] ?? 'Unknown date',
                    $comment['author'] ?? 'Unknown author',
                    $comment['body'] ?? '',
                );
            })
            ->implode("\n");
    }
}
8. Case Assistant service

Create:

src/AI/Services/CaseAssistant.php
<?php

namespace Simplydigital\AIManagement\AI\Services;

use Simplydigital\AIManagement\AI\Contracts\AIService;
use Simplydigital\AIManagement\AI\DTO\CaseAssistantResult;
use Simplydigital\AIManagement\AI\DTO\CaseContext;
use Simplydigital\AIManagement\AI\Prompts\CaseAssistantPrompt;

class CaseAssistant
{
    public function __construct(
        protected AIService $ai,
        protected CaseAssistantPrompt $prompt,
    ) {
    }

    public function analyze(
        CaseContext $context
    ): CaseAssistantResult {
        $result = $this->ai->generateStructured(
            prompt: $this->prompt->build($context),
            schema: $this->schema(),
        );

        return CaseAssistantResult::fromArray($result);
    }

    /**
     * @return array<string, mixed>
     */
    protected function schema(): array
    {
        return [
            'type' => 'object',

            'properties' => [

                'summary' => [
                    'type' => 'string',
                ],

                'suggested_category' => [
                    'type' => ['string', 'null'],
                ],

                'suggested_priority' => [
                    'type' => ['string', 'null'],
                ],

                'suggested_next_actions' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                ],

                'similar_case_ids' => [
                    'type' => 'array',
                    'items' => [
                        'type' => [
                            'string',
                            'integer',
                        ],
                    ],
                ],

                'customer_response_draft' => [
                    'type' => ['string', 'null'],
                ],

                'warnings' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                ],
            ],

            'required' => [
                'summary',
                'suggested_category',
                'suggested_priority',
                'suggested_next_actions',
                'similar_case_ids',
                'customer_response_draft',
                'warnings',
            ],

            'additionalProperties' => false,
        ];
    }
}
9. Provider implementation

For V1, I would isolate the actual provider call.

Create:

src/AI/Providers/OpenAIService.php

You can implement this using whatever OpenAI Laravel integration you decide to standardize on. If you're using the official OpenAI PHP client, for example, keep that dependency entirely inside this class.

Conceptually:

<?php

namespace Simplydigital\AIManagement\AI\Providers;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Simplydigital\AIManagement\AI\Contracts\AIService;

class OpenAIService implements AIService
{
    public function generate(string $prompt): string
    {
        $response = Http::withToken(
            config('ai-management.openai.api_key')
        )->post(
            config('ai-management.openai.base_url') . '/responses',
            [
                'model' => config('ai-management.model'),

                'input' => $prompt,
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'AI provider request failed: ' .
                $response->body()
            );
        }

        return $this->extractText(
            $response->json()
        );
    }

    public function generateStructured(
        string $prompt,
        array $schema
    ): array {
        $response = Http::withToken(
            config('ai-management.openai.api_key')
        )->post(
            config('ai-management.openai.base_url') . '/responses',
            [
                'model' => config('ai-management.model'),

                'input' => $prompt,

                // Configure structured output
                // according to the provider API version
                // you standardize on.
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'AI provider request failed: ' .
                $response->body()
            );
        }

        $json = $this->extractJson(
            $response->json()
        );

        if (!is_array($json)) {
            throw new RuntimeException(
                'AI provider returned invalid structured output.'
            );
        }

        return $json;
    }

    protected function extractText(
        array $response
    ): string {
        // Provider-specific response parsing.
        return '';
    }

    protected function extractJson(
        array $response
    ): array {
        // Provider-specific structured-output parsing.
        return [];
    }
}
Important

I deliberately wouldn't hard-code the provider's exact request/response schema here until you've chosen the exact provider SDK/API version.

The rest of your architecture should not care.

If you later switch provider:

CaseAssistant
      ↓
AIService
      ↓
OpenAIService

becomes:

CaseAssistant
      ↓
AIService
      ↓
AnotherAIService

with no changes to CaseManagement.

10. Service provider

Create:

src/Providers/AIManagementServiceProvider.php
<?php

namespace Simplydigital\AIManagement\Providers;

use Illuminate\Support\ServiceProvider;
use Simplydigital\AIManagement\AI\Contracts\AIService;
use Simplydigital\AIManagement\AI\Prompts\CaseAssistantPrompt;
use Simplydigital\AIManagement\AI\Providers\OpenAIService;
use Simplydigital\AIManagement\AI\Services\CaseAssistant;

class AIManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/ai-management.php',
            'ai-management'
        );

        $this->app->singleton(
            AIService::class,
            function ($app) {
                return match (
                    config('ai-management.provider')
                ) {
                    'openai' => $app->make(OpenAIService::class),

                    default => throw new \RuntimeException(
                        'Unsupported AI provider: ' .
                        config('ai-management.provider')
                    ),
                };
            }
        );

        $this->app->singleton(
            CaseAssistantPrompt::class
        );

        $this->app->singleton(
            CaseAssistant::class
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ .
                '/../../config/ai-management.php'
                => config_path('ai-management.php'),
        ], 'ai-management-config');

        $this->loadMigrationsFrom(
            __DIR__ . '/../../database/migrations'
        );

        $this->loadViewsFrom(
            __DIR__ . '/../Resources/views',
            'ai-management'
        );
    }
}
11. AI permission

Create:

src/Authorization/Permissions/AIPermission.php
<?php

namespace Simplydigital\AIManagement\Authorization\Permissions;

enum AIPermission: string
{
    case CaseAssistantView =
        'ai.case_assistant.view';

    case CaseAssistantAnalyze =
        'ai.case_assistant.analyze';

    case CaseAssistantApply =
        'ai.case_assistant.apply';
}

This follows the permission-enum architecture you've already established.

12. Permission provider

Because your UserManagement package owns the permission registry, AIManagement should expose its permissions.

Create:

src/Authorization/AIPermissionProvider.php
<?php

namespace Simplydigital\AIManagement\Authorization;

use Simplydigital\AIManagement\Authorization\Permissions\AIPermission;
use Simplydigital\UserManagement\Contracts\PermissionProvider;
use Simplydigital\UserManagement\Data\PermissionDefinition;

class AIPermissionProvider implements PermissionProvider
{
    public function permissions(): array
    {
        return [
            new PermissionDefinition(
                name: AIPermission::CaseAssistantView->value,
                description: 'View AI case assistant',
                group: 'AI Case Assistant',
            ),

            new PermissionDefinition(
                name: AIPermission::CaseAssistantAnalyze->value,
                description: 'Run AI case analysis',
                group: 'AI Case Assistant',
            ),

            new PermissionDefinition(
                name: AIPermission::CaseAssistantApply->value,
                description: 'Apply AI case assistant suggestions',
                group: 'AI Case Assistant',
            ),
        ];
    }
}
13. Register AI permissions with UserManagement

Because you already use the PermissionRegistry, register the provider explicitly.

In your AIManagement provider:

use Simplydigital\AIManagement\Authorization\AIPermissionProvider;
use Simplydigital\UserManagement\Services\PermissionRegistry;

Then:

public function register(): void
{
    // Existing bindings...

    $this->app->afterResolving(
        PermissionRegistry::class,
        function (PermissionRegistry $registry) {
            $registry->registerProvider(
                new AIPermissionProvider()
            );
        }
    );
}

Then:

php artisan permissions:sync

will create:

ai.case_assistant.view
ai.case_assistant.analyze
ai.case_assistant.apply
14. AI analysis model

Create:

src/Models/AIAnalysis.php
<?php

namespace Simplydigital\AIManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIAnalysis extends Model
{
    protected $table = 'ai_analyses';

    protected $fillable = [
        'case_id',
        'user_id',
        'type',
        'model',
        'prompt_version',
        'status',
        'result',
        'error',
    ];

    protected $casts = [
        'result' => 'array',
    ];

    /*
     * Don't create a hard dependency on CaseManagement
     * in AIManagement if you want this package reusable.
     *
     * You can add relationships in the application layer
     * or via a model resolver later.
     */
}
15. Migration

Create:

database/migrations/xxxx_xx_xx_create_ai_analyses_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->id();

            /*
             * Deliberately don't add a foreign key to cases here.
             *
             * AIManagement remains independent from CaseManagement.
             */
            $table->unsignedBigInteger('case_id');

            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('type');

            $table->string('model')->nullable();

            $table->string('prompt_version')->nullable();

            $table->string('status')->default('pending');

            $table->json('result')->nullable();

            $table->text('error')->nullable();

            $table->timestamps();

            $table->index([
                'case_id',
                'type',
            ]);

            $table->index([
                'user_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};

I'd use statuses:

pending
processing
completed
failed
16. Case context belongs in CaseManagement

This is an important architectural distinction.

Don't put this in AIManagement:

$case->customer
$case->comments
$case->asset

because now AIManagement knows what a Case is.

Instead, CaseManagement creates the context.

For example:

CaseManagement
└── src/
    └── AI/
        └── CaseContextBuilder.php
<?php

namespace Simplydigital\CaseManagement\AI;

use Simplydigital\AIManagement\AI\DTO\CaseContext;
use Simplydigital\CaseManagement\Models\CaseModel;

class CaseContextBuilder
{
    public function build(CaseModel $case): CaseContext
    {
        $case->loadMissing([
            'customer',
            'asset',
            'comments.user',
        ]);

        $comments = $case->comments
            ->sortBy('created_at')
            ->take(
                config(
                    'ai-management.case_assistant.max_comments',
                    50
                )
            )
            ->map(function ($comment) {
                return [
                    'author' => $comment->user?->name,
                    'created_at' => $comment
                        ->created_at
                        ?->toIso8601String(),

                    'body' => mb_substr(
                        $comment->body,
                        0,
                        config(
                            'ai-management.case_assistant.max_comment_length',
                            5000
                        )
                    ),
                ];
            })
            ->values()
            ->all();

        return new CaseContext(
            id: $case->id,

            title: $case->title,

            description: $case->description ?? '',

            status: $case->status?->value
                ?? $case->status?->name,

            priority: $case->priority?->value
                ?? $case->priority?->name,

            category: $case->category?->name,

            customer: [
                'name' => $case->customer?->name,
            ],

            asset: [
                'name' => $case->asset?->name,
                'serial_number' =>
                    $case->asset?->serial_number,
            ],

            comments: $comments,
        );
    }
}

You'll need to adjust the relationship/property names to your actual CaseManagement models.

17. Case Assistant Policy

Now authorization.

Create this in AIManagement:

src/Policies/CaseAssistantPolicy.php
<?php

namespace Simplydigital\AIManagement\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Simplydigital\AIManagement\Authorization\Permissions\AIPermission;

class CaseAssistantPolicy
{
    public function view(
        Authenticatable $user,
        object $case
    ): bool {
        return $user->can(
            AIPermission::CaseAssistantView->value
        );
    }

    public function analyze(
        Authenticatable $user,
        object $case
    ): bool {
        return $user->can(
            AIPermission::CaseAssistantAnalyze->value
        );
    }

    public function apply(
        Authenticatable $user,
        object $case
    ): bool {
        return $user->can(
            AIPermission::CaseAssistantApply->value
        );
    }
}

However, there's an important consideration here.

If the user needs both:

case.view
+
ai.case_assistant.analyze

then the policy should check both.

For example, assuming your Case permission enum is:

CasePermission::View

you can do:

public function analyze(
    Authenticatable $user,
    object $case
): bool {
    return $user->can(
        CasePermission::View->value
    )
        && $user->can(
            AIPermission::CaseAssistantAnalyze->value
        );
}

That gives you:

AI permission
      +
Case permission
      ↓
AI analysis allowed
18. Register the policy

This is the same issue you previously encountered with loadPermissionPolicies().

If your AI package is registering a policy against a CaseManagement model, register it during boot().

For example, in CaseManagement:

use Illuminate\Support\Facades\Gate;
use Simplydigital\AIManagement\Policies\CaseAssistantPolicy;
use Simplydigital\CaseManagement\Models\CaseModel;

Then:

public function boot(): void
{
    $this->loadPermissionPolicies();

    Gate::policy(
        CaseModel::class,
        CaseAssistantPolicy::class
    );
}

But I would actually avoid replacing your CasePolicy with CaseAssistantPolicy.

A cleaner solution is to keep the assistant authorization separate by using a dedicated Gate ability.

For example:

Gate::define(
    'case-assistant.analyze',
    function ($user, $case) {
        return $user->can(
            AIPermission::CaseAssistantAnalyze->value
        )
            && $user->can(
                CasePermission::View->value
            );
    }
);

Then:

$this->authorize(
    'case-assistant.analyze',
    $this->case
);

This avoids having two policies compete for the same Case model.

19. I recommend this authorization model

For your architecture, I'd use:

CasePolicy
   ├── view
   ├── update
   ├── delete
   └── ...

Case Assistant Gate
   ├── case-assistant.view
   ├── case-assistant.analyze
   └── case-assistant.apply

So:

$this->authorize(
    'case-assistant.analyze',
    $this->case
);

And the Gate:

Gate::define(
    'case-assistant.analyze',
    function ($user, $case) {
        return $user->can(
            AIPermission::CaseAssistantAnalyze->value
        )
            && $user->can(
                CasePermission::View->value
            );
    }
);

Your existing Gate::before() Super Admin bypass continues to work.

20. Queue job

Now make the AI operation asynchronous.

Create:

src/Jobs/GenerateCaseAssistantAnalysis.php
<?php

namespace Simplydigital\AIManagement\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Simplydigital\AIManagement\AI\DTO\CaseContext;
use Simplydigital\AIManagement\AI\Services\CaseAssistant;
use Simplydigital\AIManagement\Models\AIAnalysis;

class GenerateCaseAssistantAnalysis implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int|string $analysisId,
        public CaseContext $context,
    ) {
        $this->onQueue(
            config(
                'ai-management.case_assistant.queue',
                'default'
            )
        );
    }

    public function handle(
        CaseAssistant $assistant
    ): void {
        $analysis = AIAnalysis::findOrFail(
            $this->analysisId
        );

        $analysis->update([
            'status' => 'processing',
            'error' => null,
        ]);

        try {
            $result = $assistant->analyze(
                $this->context
            );

            $analysis->update([
                'status' => 'completed',
                'result' => $result->toArray(),
                'model' => config(
                    'ai-management.model'
                ),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            $analysis->update([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
21. Livewire component

Create:

src/Livewire/CaseAssistant.php
<?php

namespace Simplydigital\AIManagement\Livewire;

use Livewire\Component;
use Simplydigital\AIManagement\AI\DTO\CaseAssistantResult;
use Simplydigital\AIManagement\Authorization\Permissions\AIPermission;
use Simplydigital\AIManagement\Jobs\GenerateCaseAssistantAnalysis;
use Simplydigital\AIManagement\Models\AIAnalysis;

class CaseAssistant extends Component
{
    public object $case;

    public ?int $analysisId = null;

    public ?CaseAssistantResult $result = null;

    public bool $analyzing = false;

    public function mount(object $case): void
    {
        $this->case = $case;

        $this->loadLatestAnalysis();
    }

    public function analyze(): void
    {
        $this->authorize(
            'case-assistant.analyze',
            $this->case
        );

        $contextBuilder = app(
            \Simplydigital\CaseManagement\AI\CaseContextBuilder::class
        );

        $context = $contextBuilder->build(
            $this->case
        );

        $analysis = AIAnalysis::create([
            'case_id' => $this->case->getKey(),

            'user_id' => auth()->id(),

            'type' => 'case_assistant',

            'model' => config(
                'ai-management.model'
            ),

            'prompt_version' => config(
                'ai-management.case_assistant.prompt_version'
            ),

            'status' => 'pending',
        ]);

        $this->analysisId = $analysis->id;

        $this->analyzing = true;

        GenerateCaseAssistantAnalysis::dispatch(
            analysisId: $analysis->id,
            context: $context,
        );

        $this->dispatch(
            'case-assistant-analysis-started'
        );
    }

    public function refreshAnalysis(): void
    {
        $this->loadLatestAnalysis();
    }

    protected function loadLatestAnalysis(): void
    {
        $analysis = AIAnalysis::query()
            ->where('case_id', $this->case->getKey())
            ->where('type', 'case_assistant')
            ->latest()
            ->first();

        if (!$analysis) {
            return;
        }

        $this->analysisId = $analysis->id;

        if ($analysis->status === 'completed') {
            $this->result = CaseAssistantResult::fromArray(
                $analysis->result ?? []
            );

            $this->analyzing = false;
        }

        if ($analysis->status === 'pending') {
            $this->analyzing = true;
        }

        if ($analysis->status === 'processing') {
            $this->analyzing = true;
        }

        if ($analysis->status === 'failed') {
            $this->analyzing = false;
        }
    }

    public function getAnalysisProperty(): ?AIAnalysis
    {
        if (!$this->analysisId) {
            return null;
        }

        return AIAnalysis::find(
            $this->analysisId
        );
    }

    public function render()
    {
        return view(
            'ai-management::livewire.case-assistant'
        );
    }
}
22. One architectural change I'd make here

The code above shows the concept, but I'd actually avoid having AIManagement directly instantiate:

Simplydigital\CaseManagement\AI\CaseContextBuilder

because that makes AIManagement depend on CaseManagement.

Instead, have CaseManagement own the integration component.

For example:

CaseManagement
└── Livewire
    └── Case
        └── CaseAssistant.php

or:

CaseManagement
└── AI
    ├── CaseContextBuilder.php
    └── CaseAssistantIntegration.php

Then:

CaseManagement
      │
      ├── knows Case
      │
      └── calls
            ↓
      AIManagement

This is the direction I'd use.

23. Better integration: CaseAssistantService

Inside CaseManagement:

src/AI/CaseAssistantService.php
<?php

namespace Simplydigital\CaseManagement\AI;

use Illuminate\Support\Facades\Gate;
use Simplydigital\AIManagement\AI\DTO\CaseContext;
use Simplydigital\AIManagement\Jobs\GenerateCaseAssistantAnalysis;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\CaseManagement\Models\CaseModel;

class CaseAssistantService
{
    public function requestAnalysis(
        CaseModel $case
    ): AIAnalysis {
        Gate::authorize(
            'case-assistant.analyze',
            $case
        );

        $context = app(
            CaseContextBuilder::class
        )->build($case);

        $analysis = AIAnalysis::create([
            'case_id' => $case->getKey(),

            'user_id' => auth()->id(),

            'type' => 'case_assistant',

            'model' => config(
                'ai-management.model'
            ),

            'prompt_version' => config(
                'ai-management.case_assistant.prompt_version'
            ),

            'status' => 'pending',
        ]);

        GenerateCaseAssistantAnalysis::dispatch(
            analysisId: $analysis->id,
            context: $context,
        );

        return $analysis;
    }
}

Now your Livewire component doesn't know about the mechanics:

public function analyze(): void
{
    $this->authorize(
        'case-assistant.analyze',
        $this->case
    );

    $this->analysis = app(
        CaseAssistantService::class
    )->requestAnalysis(
        $this->case
    );
}

That's much cleaner.

24. Blade component

Create:

src/Resources/views/livewire/case-assistant.blade.php
<div
    wire:poll.3s="refreshAnalysis"
    class="space-y-6"
>
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold">
                AI Case Assistant
            </h2>

            <p class="text-sm text-gray-500">
                AI-generated suggestions based on the case information.
            </p>
        </div>

        @can('case-assistant.analyze', $case)
            <button
                type="button"
                wire:click="analyze"
                wire:loading.attr="disabled"
                class="rounded-md px-4 py-2 text-sm font-medium"
            >
                <span wire:loading.remove wire:target="analyze">
                    Analyze Case
                </span>

                <span wire:loading wire:target="analyze">
                    Starting...
                </span>
            </button>
        @endcan
    </div>

    @if ($analysis?->status === 'pending')
        <div class="rounded-md border p-4">
            AI analysis is waiting to start...
        </div>
    @endif

    @if ($analysis?->status === 'processing')
        <div class="rounded-md border p-4">
            AI is analyzing this case...
        </div>
    @endif

    @if ($analysis?->status === 'failed')
        <div class="rounded-md border p-4">
            <p class="font-medium">
                AI analysis failed.
            </p>

            <p class="mt-1 text-sm">
                Please try again.
            </p>
        </div>
    @endif

    @if ($result)
        <div class="space-y-6">

            {{-- Summary --}}
            <section>
                <h3 class="font-semibold">
                    Summary
                </h3>

                <p class="mt-2 text-sm">
                    {{ $result->summary }}
                </p>
            </section>

            {{-- Classification --}}
            <section>
                <h3 class="font-semibold">
                    Suggested Classification
                </h3>

                <div class="mt-3 grid grid-cols-2 gap-4">

                    <div class="rounded-md border p-4">
                        <div class="text-xs text-gray-500">
                            Category
                        </div>

                        <div class="mt-1 font-medium">
                            {{ $result->suggestedCategory ?? 'No suggestion' }}
                        </div>
                    </div>

                    <div class="rounded-md border p-4">
                        <div class="text-xs text-gray-500">
                            Priority
                        </div>

                        <div class="mt-1 font-medium">
                            {{ $result->suggestedPriority ?? 'No suggestion' }}
                        </div>
                    </div>

                </div>
            </section>

            {{-- Next actions --}}
            <section>
                <h3 class="font-semibold">
                    Suggested Next Actions
                </h3>

                <ul class="mt-3 space-y-2">
                    @forelse (
                        $result->suggestedNextActions
                        as $action
                    )
                        <li class="rounded-md border p-3">
                            {{ $action }}
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">
                            No next actions suggested.
                        </li>
                    @endforelse
                </ul>
            </section>

            {{-- Customer response --}}
            <section>
                <h3 class="font-semibold">
                    Customer Response Draft
                </h3>

                @if ($result->customerResponseDraft)
                    <div class="mt-3 rounded-md border p-4">
                        <div class="whitespace-pre-line text-sm">
                            {{ $result->customerResponseDraft }}
                        </div>
                    </div>

                    @can('case-assistant.apply', $case)
                        <button
                            type="button"
                            class="mt-3 rounded-md border px-4 py-2 text-sm"
                        >
                            Use Draft
                        </button>
                    @endcan
                @else
                    <p class="mt-2 text-sm text-gray-500">
                        No response draft was generated.
                    </p>
                @endif
            </section>

            {{-- Warnings --}}
            @if (count($result->warnings))
                <section>
                    <h3 class="font-semibold">
                        Warnings
                    </h3>

                    <ul class="mt-3 space-y-2">
                        @foreach ($result->warnings as $warning)
                            <li class="rounded-md border p-3 text-sm">
                                {{ $warning }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

        </div>
    @endif
</div>
25. Don't actually use wire:poll forever

For V1 it's perfectly reasonable.

But I'd eventually replace it with an event-driven solution.

For example:

Job completes
      ↓
broadcast/event
      ↓
Livewire receives event
      ↓
refresh analysis

For your first implementation, polling every 3 seconds is much simpler and perfectly adequate.

26. Add the component to your Case page

In your CaseManagement case view:

@can('case-assistant.view', $case)
    <livewire:ai-management.case-assistant
        :case="$case"
    />
@endcan

Depending on how you've registered Livewire components, the actual component name may differ.

27. Register the Livewire component

If you don't use automatic discovery:

use Livewire\Livewire;
use Simplydigital\AIManagement\Livewire\CaseAssistant;

In boot():

Livewire::component(
    'ai-management.case-assistant',
    CaseAssistant::class
);
28. Better: keep the CaseManagement Livewire component

Actually, for your package structure I'd probably go one step further.

Your existing structure already has:

CaseManagement/
└── src/
    └── Livewire/
        └── Case/

So create:

CaseManagement/
└── src/
    ├── AI/
    │   └── CaseAssistant/
    │       └── CaseContextBuilder.php
    │
    └── Livewire/
        └── Case/
            └── CaseAssistant.php

The CaseManagement Livewire component can then call AIManagement:

<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Simplydigital\AIManagement\AI\Services\CaseAssistant as AIService;

class CaseAssistant extends Component
{
    public $case;

    public function analyze(
        AIService $assistant
    ): void {
        $this->authorize(
            'case-assistant.analyze',
            $this->case
        );

        // Build CaseContext here.

        // Dispatch AIManagement job.
    }

    public function render()
    {
        return view(
            'casemanagement::livewire.case.case-assistant'
        );
    }
}

That means:

CaseManagement
    owns UI
    owns Case context
    owns Case-specific business rules

AIManagement
    owns AI abstraction
    owns AI provider
    owns AI job
    owns AI result

That's the separation I'd ultimately choose.

29. Applying suggestions

For V1, I would not automatically apply category or priority.

Instead:

AI says:

Suggested priority: High

                    [Apply]

The button then performs normal authorization.

For example:

public function applyPriority(): void
{
    $this->authorize(
        'case-assistant.apply',
        $this->case
    );

    $this->authorize(
        'update',
        $this->case
    );

    $suggestedPriority =
        $this->result?->suggestedPriority;

    if (!$suggestedPriority) {
        return;
    }

    $priority = CasePriority::tryFrom(
        $suggestedPriority
    );

    if (!$priority) {
        return;
    }

    $this->case->update([
        'priority' => $priority,
    ]);
}

Notice that AI does not perform the update.

It proposes:

"High"

Laravel validates:

"Is High a valid CasePriority?"

Then authorization happens:

"Can this user update this case?"

Then your business logic performs the update.

30. Applying the customer response

Same principle.

AI generates:

Hello John,

We've reviewed the issue...

The user clicks:

Use Draft

Then:

public function useCustomerDraft(): void
{
    $this->authorize(
        'case-assistant.apply',
        $this->case
    );

    $this->authorize(
        'update',
        $this->case
    );

    $this->dispatch(
        'customer-response-draft-created',
        body: $this->result?->customerResponseDraft
    );
}

The actual email/message component can then use your existing NotificationManagement package.

So:

AIManagement
      ↓
draft
      ↓
CaseManagement
      ↓
NotificationManagement
      ↓
customer
31. Don't let AI decide SLA

This is particularly important in your system because you already have Service Level Agreement functionality.

The AI can say:

{
    "suggested_priority": "High"
}

but it should not say:

{
    "sla": "4 hours"
}

and have that applied.

Instead:

AI
 ↓
suggested priority = High
 ↓
CaseManagement
 ↓
business rules
 ↓
SLA calculation
 ↓
actual SLA

For example:

$this->caseService->updatePriority(
    $case,
    CasePriority::High
);

Then your SLA system decides what that means.

That keeps contractual/business logic deterministic.

32. V1 sequence

The complete flow becomes:

User opens Case
       │
       ▼
CaseManagement UI
       │
       ▼
"Analyze Case"
       │
       ▼
authorize(
    'case-assistant.analyze',
    $case
)
       │
       ▼
CaseContextBuilder
       │
       ▼
CaseContext DTO
       │
       ▼
AIAnalysis(status=pending)
       │
       ▼
Queue Job
       │
       ▼
CaseAssistant
       │
       ▼
CaseAssistantPrompt
       │
       ▼
AIService
       │
       ▼
AI Provider
       │
       ▼
Structured JSON
       │
       ▼
CaseAssistantResult
       │
       ▼
AIAnalysis(status=completed)
       │
       ▼
Livewire refresh
       │
       ▼
Display suggestions
33. What I'd put into the first database record

Example:

{
    "summary": "The customer reports intermittent connectivity problems affecting multiple users.",
    "suggested_category": "Network",
    "suggested_priority": "High",
    "suggested_next_actions": [
        "Check switch port error counters.",
        "Review DHCP lease information.",
        "Test connectivity from an affected workstation."
    ],
    "similar_case_ids": [],
    "customer_response_draft": "Hello John,\n\nThank you for reporting the connectivity issue...",
    "warnings": [
        "The number of affected users is not clearly established."
    ]
}

The important thing is that your database contains the AI output, not just the final text.

That makes it possible later to answer questions like:

What did AI suggest?
When did it suggest it?
Which model generated it?
Which prompt version generated it?
Who requested the analysis?
Did the user apply the suggestion?
34. Add audit information now

I strongly recommend expanding ai_analyses slightly:

ai_analyses
────────────────────────────
id
case_id
user_id
type
model
prompt_version
status
result
error
created_at
updated_at

Later you can add:

input_tokens
output_tokens
latency_ms
provider

and potentially:

applied_at
applied_by

This will become very useful once AI usage grows.

35. Testing

You should test this at three levels.

Test 1 — permission
public function test_user_without_ai_permission_cannot_analyze_case(): void
{
    $user = User::factory()->create();

    $case = CaseModel::factory()->create();

    $this->actingAs($user);

    $this->expectException(
        AuthorizationException::class
    );

    Gate::authorize(
        'case-assistant.analyze',
        $case
    );
}
Test 2 — AI service

Mock the AI provider:

$ai = Mockery::mock(AIService::class);

$ai->shouldReceive('generateStructured')
    ->once()
    ->andReturn([
        'summary' => 'Test summary',

        'suggested_category' => 'Network',

        'suggested_priority' => 'High',

        'suggested_next_actions' => [
            'Check network connectivity',
        ],

        'similar_case_ids' => [],

        'customer_response_draft' =>
            'Hello customer...',

        'warnings' => [],
    ]);

$this->app->instance(
    AIService::class,
    $ai
);

Then:

$result = app(CaseAssistant::class)
    ->analyze($context);

$this->assertSame(
    'Test summary',
    $result->summary
);

That means your tests don't call the actual AI provider.

36. Test the queue
Queue::fake();

$analysis = app(
    CaseAssistantService::class
)->requestAnalysis($case);

Queue::assertPushed(
    GenerateCaseAssistantAnalysis::class
);

This verifies that your application dispatches the work correctly.

37. Test authorization separately from AI

This is important.

You should be able to test:

Can user analyze?

without:

Does OpenAI work?

And test:

Does AIService return a valid CaseAssistantResult?

without:

Does this user have permission?

That separation will make your package dramatically easier to maintain.

38. One thing I would change from the original V1 proposal

I originally suggested:

AIManagement
└── Livewire
    └── CaseAssistant

For your particular package ecosystem, I now prefer:

AIManagement
├── AI/
├── Jobs/
├── Models/
├── Providers/
└── ...

CaseManagement
├── AI/
│   └── CaseAssistant/
│       └── CaseContextBuilder.php
│
└── Livewire/
    └── Case/
        └── CaseAssistant.php

Why?

Because the UI is CaseManagement functionality, while the AI engine is AIManagement functionality.

That gives you:

                    CaseManagement
                  ┌─────────────────┐
                  │ Case UI         │
                  │ Case context    │
                  │ Case policies   │
                  └────────┬────────┘
                           │
                           ▼
                    AIManagement
                  ┌─────────────────┐
                  │ AI abstraction  │
                  │ Prompt engine   │
                  │ Provider        │
                  │ Jobs            │
                  │ Results         │
                  └─────────────────┘

And later:

NotificationManagement ──┐
FileUploadManagement ────┤
EventPlannerManagement ──┼──► AIManagement
CaseManagement ───────────┘

That's much more reusable.

39. Recommended final V1 structure

I'd therefore settle on this:

packages/
└── Simplydigital/
    │
    ├── AIManagement/
    │   ├── config/
    │   │   └── ai-management.php
    │   │
    │   ├── database/
    │   │   └── migrations/
    │   │       └── create_ai_analyses_table.php
    │   │
    │   ├── src/
    │   │   ├── AI/
    │   │   │   ├── Contracts/
    │   │   │   │   └── AIService.php
    │   │   │   │
    │   │   │   ├── DTO/
    │   │   │   │   ├── CaseContext.php
    │   │   │   │   └── CaseAssistantResult.php
    │   │   │   │
    │   │   │   ├── Prompts/
    │   │   │   │   └── CaseAssistantPrompt.php
    │   │   │   │
    │   │   │   ├── Providers/
    │   │   │   │   └── OpenAIService.php
    │   │   │   │
    │   │   │   └── Services/
    │   │   │       └── CaseAssistant.php
    │   │   │
    │   │   ├── Authorization/
    │   │   │   ├── AIPermissionProvider.php
    │   │   │   └── Permissions/
    │   │   │       └── AIPermission.php
    │   │   │
    │   │   ├── Jobs/
    │   │   │   └── GenerateCaseAssistantAnalysis.php
    │   │   │
    │   │   ├── Models/
    │   │   │   └── AIAnalysis.php
    │   │   │
    │   │   └── Providers/
    │   │       └── AIManagementServiceProvider.php
    │   │
    │   └── composer.json
    │
    └── CaseManagement/
        │
        ├── src/
        │   ├── AI/
        │   │   └── CaseAssistant/
        │   │       ├── CaseAssistantService.php
        │   │       └── CaseContextBuilder.php
        │   │
        │   ├── Livewire/
        │   │   └── Case/
        │   │       └── CaseAssistant.php
        │   │
        │   └── Resources/
        │       └── views/
        │           └── livewire/
        │               └── case/
        │                   └── case-assistant.blade.php
        │
        └── ...
The V1 boundary

The most important thing is to keep this boundary:

┌─────────────────────────────────────────────────┐
│                  CASEMANAGEMENT                  │
│                                                 │
│  "What is a Case?"                              │
│  "What data can the user see?"                  │
│  "Can the user update this Case?"               │
│  "What is a valid priority?"                    │
│  "What does an SLA mean?"                       │
│                                                 │
└──────────────────────┬──────────────────────────┘
                       │
                       │ CaseContext
                       ▼
┌─────────────────────────────────────────────────┐
│                  AIMANAGEMENT                    │
│                                                 │
│  "How do we call an AI?"                        │
│  "How do we structure AI output?"               │
│  "How do we build prompts?"                     │
│  "How do we queue AI work?"                     │
│  "How do we store AI analysis?"                 │
│                                                 │
└──────────────────────┬──────────────────────────┘
                       │
                       ▼
                  AI PROVIDER

And never let this happen:

AI
 ↓
$model->save()

Instead:

AI
 ↓
structured suggestion
 ↓
user/application
 ↓
Policy
 ↓
Service
 ↓
business rules
 ↓
Model

That will let you add V2 features such as similar cases, attachment/document analysis, knowledge-base retrieval, and eventually controlled AI tools without having to redesign V1.

One caveat: the exact OpenAIService HTTP payload should be aligned with the specific OpenAI API/SDK version you choose. Since that is provider/API-specific and can change, I would treat that class as the only provider-specific part of the implementation; the rest of the architecture above remains stable.

