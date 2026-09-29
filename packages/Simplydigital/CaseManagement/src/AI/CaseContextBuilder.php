<?php

namespace Simplydigital\CaseManagement\AI;

use Simplydigital\AIManagement\AI\DTO\CaseContext;
use Simplydigital\CaseManagement\Models\tickets;

class CaseContextBuilder
{
    public function build(tickets $case): CaseContext
    {
        $case->loadMissing([
            'customer.assets.product',
            'taskLogs.user'
        ]);

        $assets = $case->customer->assets->map(fn($assets) => [
                    'id' => $assets->number,
                    'name' => $assets->product->name,
                    'serial_number' => $assets->number,
                    'type' => $assets?->product->type->value,
                ])
                ->values()
                ->all();

        $comments = $case->taskLogs
            ->sortBy('created_at')
            ->take(
                config('ai-management.case_assistant.max_comments',50)
            )
            ->map(function ($comment) {
                return [
                    'author' => $comment->user?->name,
                    'created_at' => $comment
                        ->created_at
                        ?->toIso8601String(),

                    'body' => mb_substr(
                        $comment->description,
                        0,
                        config('ai-management.case_assistant.max_comment_length',5000)
                    ),
                ];
            })
            ->values()
            ->all();

        return new CaseContext(
            id: $case->number,
            title: $case->short_description,
            description: $case->description ?? '',
            status: $case->state?->value ?? $case->state?->name,
            priority: $case->priority?->value ?? $case->priority?->name,
            category: $case->category?->value ?? $case->category?->name,
            customer: [
                'name' => $case->customer?->name,
            ],
            assets: $assets,
            comments: $comments,
        );
    }
}
