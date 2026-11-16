<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Simplydigital\AIManagement\AI\DTO\CaseContext;
use Simplydigital\AIManagement\AI\Prompts\CaseAssistantPrompt;

class CaseAssistantClassificationPromptTest extends TestCase
{
    public function test_prompt_treats_existing_category_as_unverified_context(): void
    {
        $context = new CaseContext(
            id: 'CASE-TEST',
            title: 'Need advice configuring a device',
            description: 'Customer requests technical guidance.',
            status: 'Open',
            priority: null,
            category: 'Failure',
        );

        $prompt = (new CaseAssistantPrompt)->build($context);

        $this->assertStringContainsString('Current category:', $prompt);
        $this->assertStringContainsString('Failure', $prompt);
        $this->assertStringContainsString('classify the case independently', $prompt);
        $this->assertStringContainsString('may be incorrect', $prompt);
        $this->assertStringContainsString('Technical Support', $prompt);
        $this->assertStringContainsString('Do not change a label merely to produce a different recommendation.', $prompt);
    }
}
