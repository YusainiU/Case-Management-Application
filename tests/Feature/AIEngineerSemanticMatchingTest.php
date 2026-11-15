<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Simplydigital\AIManagement\AI\Contracts\AIService;
use Simplydigital\AIManagement\Jobs\GenerateSemanticEngineerSkills;
use Simplydigital\AIManagement\Livewire\EngineerAssignmentApprovals;
use Simplydigital\AIManagement\Models\AIAnalysis;
use Simplydigital\AIManagement\Services\EngineerRecommendationService;
use Simplydigital\AIManagement\Services\EngineerAssignmentProposalService;
use Simplydigital\AIManagement\Services\SemanticEngineerSkillService;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\tickets;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AIEngineerSemanticMatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_developer_can_match_http_500_portal_case_without_literal_skill_name(): void
    {
        config()->set('ai-management.engineer_semantic.enabled', true);
        $owner = User::factory()->create();
        $dan = User::factory()->create(['name' => 'Dan Cool']);
        $this->engineer($dan, 'Application Developer');
        $case = $this->portalCase($owner);

        $this->assertSame([], app(EngineerRecommendationService::class)
            ->recommend($case)[0]['matched_skills']);

        $fake = \Mockery::mock(AIService::class);
        $fake->shouldReceive('generateStructured')->once()
            ->withArgs(function ($prompt, $schema) {
                $this->assertStringContainsString('Application Developer', $prompt);
                $this->assertStringNotContainsString('Dan Cool', $prompt);
                return isset($schema['properties']['matches']);
            })
            ->andReturn(['matches' => [[
                'skill' => 'Application Developer',
                'case_evidence' => '500 Internal Server Error',
                'reason' => 'An HTTP 500 at login may require server-side application debugging; the root cause is unknown.',
                'confidence' => 'medium',
            ]]]);
        $this->app->instance(AIService::class, $fake);

        $service = app(SemanticEngineerSkillService::class);
        $result = $service->analyze($service->context($case));
        $this->storeAnalysis($case, $result);

        $candidate = app(EngineerRecommendationService::class)->recommend($case)[0];
        $this->assertSame($dan->id, $candidate['user_id']);
        $this->assertSame('semantic_match', $candidate['evidence']);
        $this->assertSame('Application Developer', $candidate['semantic_matches'][0]['skill']);
        $this->assertSame('500 Internal Server Error', $candidate['semantic_matches'][0]['case_evidence']);

        $proposal = app(EngineerAssignmentProposalService::class)->propose($case, $dan->id);
        $this->assertStringContainsString('AI-suggested skill: Application Developer', $proposal->reason);
        $this->assertNull($case->refresh()->assigned_to);
    }

    public function test_provider_skills_and_evidence_are_validated_and_low_confidence_ignored(): void
    {
        $owner = User::factory()->create();
        $this->engineer(User::factory()->create(), 'Application Developer');
        $case = $this->portalCase($owner);
        $fake = \Mockery::mock(AIService::class);
        $fake->shouldReceive('generateStructured')->once()->andReturn(['matches' => [
            ['skill' => 'Imaginary Admin', 'case_evidence' => '500 Internal Server Error', 'reason' => 'Invented', 'confidence' => 'high'],
            ['skill' => 'Application Developer', 'case_evidence' => 'nonexistent database failure', 'reason' => 'Unverifiable', 'confidence' => 'high'],
            ['skill' => 'Application Developer', 'case_evidence' => '500 Internal Server Error', 'reason' => 'Too uncertain', 'confidence' => 'low'],
        ]]);
        $this->app->instance(AIService::class, $fake);

        $service = app(SemanticEngineerSkillService::class);
        $result = $service->analyze($service->context($case));
        $this->assertSame([], $result['matches']);
    }

    public function test_changed_case_text_invalidates_cached_semantic_evidence(): void
    {
        config()->set('ai-management.engineer_semantic.enabled', true);
        $owner = User::factory()->create();
        $dan = User::factory()->create();
        $this->engineer($dan, 'Application Developer');
        $case = $this->portalCase($owner);
        $service = app(SemanticEngineerSkillService::class);
        $this->storeAnalysis($case, [
            'context_hash' => $service->context($case)['hash'],
            'matches' => [[
                'skill' => 'Application Developer',
                'case_evidence' => '500 Internal Server Error',
                'reason' => 'Application debugging might be needed.',
                'confidence' => 'medium',
            ]],
        ]);

        $this->assertCount(1, app(EngineerRecommendationService::class)->recommend($case)[0]['semantic_matches']);

        $case->update(['description' => 'An unrelated printer problem.']);
        $this->assertSame([], app(EngineerRecommendationService::class)->recommend($case->refresh())[0]['semantic_matches']);
    }

    public function test_semantic_analysis_is_opt_in_and_requires_case_permission(): void
    {
        config()->set('ai-management.engineer_semantic.enabled', true);
        Bus::fake();
        $owner = User::factory()->create();
        $this->grant($owner, 'case.view', 'case.update', 'ai.case_assistant.analyze');
        $this->engineer(User::factory()->create(), 'Application Developer');
        $case = $this->portalCase($owner);

        $this->actingAs($owner);
        Livewire::test(EngineerAssignmentApprovals::class, ['case' => $case])
            ->call('requestSemanticMatch')
            ->assertSee('Semantic skill matching queued');
        $this->assertDatabaseHas('ai_analyses', ['case_id' => $case->id, 'type' => 'engineer_semantic_skills', 'status' => 'pending']);
        Bus::assertDispatched(GenerateSemanticEngineerSkills::class);
    }

    public function test_semantic_matching_disabled_does_not_enqueue(): void
    {
        config()->set('ai-management.engineer_semantic.enabled', false);
        Bus::fake();
        $owner = User::factory()->create();
        $this->grant($owner, 'case.view', 'case.update', 'ai.case_assistant.analyze');
        $case = $this->portalCase($owner);

        $this->actingAs($owner);
        Livewire::test(EngineerAssignmentApprovals::class, ['case' => $case])
            ->call('requestSemanticMatch')
            ->assertSee('Semantic matching is disabled');
        Bus::assertNotDispatched(GenerateSemanticEngineerSkills::class);
    }

    private function storeAnalysis(tickets $case, array $result): AIAnalysis
    {
        return AIAnalysis::query()->create([
            'case_id' => $case->id, 'type' => 'engineer_semantic_skills',
            'status' => 'completed', 'result' => $result,
        ]);
    }

    private function grant(User $user, string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user->givePermissionTo($permissions);
    }

    private function engineer(User $user, string $skillName): void
    {
        $engineerId = DB::table('engineer_management')->insertGetId([
            'number' => 'ENG-'.uniqid(), 'user_id' => $user->id, 'active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $skillId = DB::table('skills')->insertGetId([
            'number' => 'SKL-'.uniqid(), 'skills' => $skillName, 'active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('engineer_skills')->insert([
            'number' => 'ES-'.uniqid(), 'engineer_id' => $engineerId, 'skill_id' => $skillId,
            'active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function portalCase(User $owner): tickets
    {
        return tickets::query()->create([
            'customer_id' => Customer::factory()->create()->id,
            'category' => CaseCategory::TechnicalSupport,
            'created_by' => $owner->id,
            'source' => CaseSource::Phone,
            'state' => CaseState::Open,
            'short_description' => 'Application Portal login failure',
            'description' => 'A user cannot access the Application Portal: login returns a 500 Internal Server Error. No diagnostics establish the cause.',
            'contact_telephone' => '0123456789',
            'contact_email' => 'customer@example.test',
        ]);
    }
}
