<?php

namespace Simplydigital\AIManagement\AI\Providers;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Simplydigital\AIManagement\AI\Contracts\AIService;

class DeepSeekService implements AIService
{
    public function generate(string $prompt): string
    {
        $response = Http::withToken(
            config('ai-management.deepseek.api_key')
        )->post(
            config('ai-management.deepseek.base_url') . '/responses',
            [
                'model' => config('ai-management.model'),

                'input' => $prompt,
            ]
        );

        $this->throwIfFailed($response);

        return $this->extractText(
            $response->json()
        );
    }

    public function generateStructured(
        string $prompt,
        array $schema
    ): array {
        $response = Http::withToken(
            config('ai-management.deepseek.api_key')
        )->post(
            config('ai-management.deepseek.base_url') . '/responses',
            [
                'model' => config('ai-management.model'),

                'input' => $prompt,

                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'structured_result',
                        'schema' => $schema,
                    ],
                ],
            ]
        );

        $this->throwIfFailed($response);

        return $this->extractJson(
            $response->json()
        );
    }

    protected function throwIfFailed($response): void
    {
        if ($response->failed()) {
            throw new RuntimeException(
                'DeepSeek API request failed: ' .
                $response->body()
            );
        }
    }

    protected function extractText(array $response): string
    {
        $text = '';

        foreach ($response['output'] ?? [] as $outputItem) {
            foreach ($outputItem['content'] ?? [] as $contentItem) {
                if (
                    ($contentItem['type'] ?? null)
                    === 'output_text'
                ) {
                    $text .= $contentItem['text'] ?? '';
                }
            }
        }

        if ($text === '') {
            throw new RuntimeException(
                'DeepSeek returned no text output.'
            );
        }

        return $text;
    }

    protected function extractJson(array $response): array
    {
        $text = '';

        foreach ($response['output'] ?? [] as $outputItem) {
            foreach ($outputItem['content'] ?? [] as $contentItem) {
                if (
                    ($contentItem['type'] ?? null)
                    !== 'output_text'
                ) {
                    continue;
                }

                $text .= $contentItem['text'] ?? '';
            }
        }

        if ($text === '') {
            throw new RuntimeException(
                'DeepSeek returned no structured output.'
            );
        }

        $decoded = json_decode(
            $text,
            true
        );

        if (
            json_last_error()
            !== JSON_ERROR_NONE
        ) {
            throw new RuntimeException(
                'DeepSeek returned invalid JSON: ' .
                json_last_error_msg()
            );
        }

        if (!is_array($decoded)) {
            throw new RuntimeException(
                'DeepSeek returned JSON that is not an object.'
            );
        }

        return $decoded;
    }
}