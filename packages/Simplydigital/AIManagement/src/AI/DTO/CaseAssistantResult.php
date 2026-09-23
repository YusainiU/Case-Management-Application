<?php

namespace Simplydigital\AIManagement\AI\DTO;

final readonly class CaseAssistantResult
{
    /**
        @param array<int, string> $suggestedNextActions
        @param array<int, int|string> $similarCaseIds
        @param array<int, string> $warnings
     */
    public function __construct(

        public string $summary,

        public ?string $suggestedCategory,

        public ?string $suggestedPriority,

        public array $suggestedNextActions,

        public array $similarCaseIds,

        public ?string $customerResponseDraft,

        public array $warnings,

    ) {}

    /**
        @return array<string, mixed>
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
        @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {

        return new self(
            summary: (string) ($data['summary'] ?? ''),
            suggestedCategory: isset($data['suggested_category'])
                    ? (string) $data['suggested_category']
                    : null,
            suggestedPriority: isset($data['suggested_priority'])
                    ? (string) $data['suggested_priority']
                    : null,
            suggestedNextActions: array_values(
                $data['suggested_next_actions'] ?? []
            ),
            similarCaseIds: array_values(
                $data['similar_case_ids'] ?? []
            ),
            customerResponseDraft: isset($data['customer_response_draft'])
                    ? (string) $data['customer_response_draft']
                    : null,
            warnings: array_values(
                $data['warnings'] ?? []
            ),
        );

    }
}
