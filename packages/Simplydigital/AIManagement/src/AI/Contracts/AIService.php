<?php

namespace Simplydigital\AIManagement\AI\Contracts;

interface AIService
{
    /**
        Generate unstructured text.
     */
    public function generate(string $prompt): string;

    /**
        Generate structured JSON data.
        @param array<string, mixed> $schema
        @return array<string, mixed>
     */
    public function generateStructured(string $prompt, array $schema): array;
}
