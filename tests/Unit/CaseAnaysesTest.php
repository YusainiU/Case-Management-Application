<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Simplydigital\AIManagement\AI\Contracts\AIService;
use Simplydigital\AIManagement\AI\DTO\CaseContext;
use Simplydigital\AIManagement\AI\Services\CaseAssistant;
use Simplydigital\AIManagement\Jobs\GenerateCaseAssistantAnalysis;
use Simplydigital\CaseManagement\AI\CaseAssistantService;
use Simplydigital\CaseManagement\Models\tickets;
use Tests\TestCase;

class CaseAnaysesTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_user_without_ai_permission_cannot_analyze_case(): void
    {
        $case = tickets::findOrFail(8);

        $user = User::factory()->create();

        $user->givePermissionTo('case.view');

        $this->actingAs($user);

        $this->assertTrue($user->can('case.view'));
        $this->assertFalse($user->can('ai.case_assistant.analyze'));

        $this->expectException(AuthorizationException::class);

        Gate::authorize(
            'case-assistant.analyze',
            $case
        );
    }

    public function test_user_with_ai_permission_can_analyze_case(): void
    {
        $case = tickets::findOrFail(8);

        $user = User::factory()->create();

        $user->givePermissionTo([
            'case.view',
            'ai.case_assistant.analyze',
        ]);

        $this->actingAs($user);

        Gate::authorize(
            'case-assistant.analyze',
            $case
        );

        $this->assertTrue(true);
    }

    public function test_case_assistant_returns_structured_result(): void
    {
        $context = new CaseContext(
            id: 1,
            title: 'Network problem',
            description: 'Customer cannot connect to the network.',
            status: 'Open',
            priority: 'Normal',
            category: null,
        );

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
                'customer_response_draft' => 'Hello customer...',
                'warnings' => [],
            ]);

        $this->app->instance(
            AIService::class,
            $ai
        );

        $result = app(CaseAssistant::class)
            ->analyze($context);

        $this->assertSame(
            'Test summary',
            $result->summary
        );

        $this->assertSame(
            'Network',
            $result->suggestedCategory
        );

        $this->assertSame(
            'High',
            $result->suggestedPriority
        );

        $this->assertSame(
            ['Check network connectivity'],
            $result->suggestedNextActions
        );
    }

    public function test_authorized_user_can_request_case_analysis(): void
    {
        Queue::fake();

        $case = tickets::findOrFail(8);

        $user = User::factory()->create();

        $user->givePermissionTo([
            'case.view',
            'ai.case_assistant.analyze',
        ]);

        $this->actingAs($user);

        $analysis = app(CaseAssistantService::class)
            ->requestAnalysis($case);

        $this->assertDatabaseHas('ai_analyses', [
            'id' => $analysis->id,
            'case_id' => $case->getKey(),
            'user_id' => $user->id,
            'type' => 'case_assistant',
            'status' => 'pending',
        ]);

        Queue::assertPushed(
            GenerateCaseAssistantAnalysis::class
        );
    }

    public function test_it_can_call_openai(): void
    {
        $ai = app(AIService::class);

        $result = $ai->generate(
            'Respond with exactly: Hello from '.config('ai-management.provider')
        );

        $this->assertNotEmpty($result);

        dump($result);
    }
}
