<?php

namespace Simplydigital\AIManagement\AI\Providers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Simplydigital\AIManagement\AI\Contracts\AIService;

class OpenAIService implements AIService
{
    public function generate(string $prompt): string
    {

        $response = Http::withToken(config('ai-management.openai.api_key'))
            ->post(
                config('ai-management.openai.base_url').'/responses',
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
            config('ai-management.openai.api_key')
        )->post(
            config('ai-management.openai.base_url').'/responses',
            [
                'model' => config('ai-management.model'),
                'input' => $prompt,
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'case_assistant_result',
                        'strict' => true,
                        'schema' => $schema,
                    ],
                ],
            ]
        );

        $this->throwIfFailed($response);

        $json = $this->extractJson(
            $response->json()
        );

        if (! \is_array($json)) {
            throw new RuntimeException(
                'AI provider returned invalid structured output.'
            );
        }

        return $json;

    }

    protected function extractText(
        array $response
    ): string {

        $text = '';

        foreach ($response['output'] ?? [] as $outputItem) {
            foreach ($outputItem['content'] ?? [] as $contentItem) {
                if (($contentItem['type'] ?? null) === 'output_text') {
                    $text .= $contentItem['text'] ?? '';
                }
            }
        }

        if ($text === '') {
            throw new RuntimeException(
                'AI provider returned no text output.'
            );
        }

        return $text;

    }

    protected function extractJson(
        array $response
    ): array {

    foreach ($response['output'] ?? [] as $outputItem) {

        foreach ($outputItem['content'] ?? [] as $contentItem) {

            if (($contentItem['type'] ?? null) !== 'output_text') {
                    continue;
                }

                $text = $contentItem['text'] ?? '';

                if ($text === '') {
                    continue;
                }

                $decoded = json_decode($text, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException(
                        'AI provider returned invalid JSON: '.
                        json_last_error_msg()
                    );
                }

                if (! \is_array($decoded)) {
                    throw new RuntimeException(
                        'AI provider returned valid JSON, but not a JSON object.'
                    );
                }

                return $decoded;
            }
        }

        throw new RuntimeException(
            'AI provider returned no structured output.'
        );

    }

    protected function throwIfFailed(
        Response $response
    ): void {
        if ($response->failed()) {
            throw new RuntimeException(
                'AI provider request failed: '.$response->body()
            );
        }
    }
}
