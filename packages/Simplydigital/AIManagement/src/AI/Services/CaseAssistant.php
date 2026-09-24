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
  ) {}



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
