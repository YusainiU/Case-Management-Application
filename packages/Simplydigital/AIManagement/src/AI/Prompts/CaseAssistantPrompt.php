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

            {$this->formatAsset($context->assets)}

        CASE COMMENTS

            {$this->formatComments($context->comments)}

        Return your response according to the supplied structured output schema.

        PROMPT;

    }



    /**
        @param array<string, mixed> $customer
    */

   protected function formatCustomer(array $customer): string

   {

       if ($customer === []) {
           return 'No customer information supplied.';
       }

       return collect($customer)
           ->map( fn ($value, $key) => "{$key}: {$value}")
           ->implode("n");

   }



    /**
        @param array<string, mixed> $asset
    */

   protected function formatAsset(array $assets): string
   {

       if ($assets === []) {
           return 'No asset information supplied.';
       }

    return collect($assets)
        ->map(function (array $asset) {
            return \sprintf(
                '- %s (Type: %s, Serial: %s)',
                $asset['name'] ?? 'Unknown',
                $asset['type'] ?? 'Unknown',
                $asset['serial_number'] ?? 'Unknown',
            );
        })
        ->implode("\n");

   }

    /**
        @param array<int, array<string, mixed>> $comments
    */

   protected function formatComments(array $comments): string
   {

       if ($comments === []) {
           return 'No comments supplied.';
       }

       return collect($comments)
           ->map(function (array $comment): string {
               return \sprintf(
                   '[%s] %s: %s',
                   $comment['created_at'] ?? 'Unknown date',
                   $comment['author'] ?? 'Unknown author',
                   $comment['body'] ?? '',
               );
            })
            ->implode("n");
    }

}
