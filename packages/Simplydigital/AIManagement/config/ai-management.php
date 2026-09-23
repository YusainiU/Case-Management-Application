<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    */

    'provider' => env('AI_MANAGEMENT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    */

    'model' => env('AI_MANAGEMENT_MODEL'),

    /*
    |--------------------------------------------------------------------------
    | Case Assistant
    |--------------------------------------------------------------------------
    */

    'case_assistant' => [

        'enabled' => env(
            'AI_CASE_ASSISTANT_ENABLED',
            true
        ),

        'queue' => env(
            'AI_CASE_ASSISTANT_QUEUE',
            'default'
        ),

        'prompt_version' => 'case-assistant-v1',

        /*
         * Maximum amount of case comments sent to the AI.
         */
        'max_comments' => 50,

        /*
         * Maximum amount of characters from each comment.
         */
        'max_comment_length' => 5000,

        /*
         * Maximum response tokens.
         */
        'max_output_tokens' => 2000,
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI
    |--------------------------------------------------------------------------
    */

    'openai' => [

        'api_key' => env('OPENAI_API_KEY'),

        'base_url' => env(
            'OPENAI_BASE_URL',
            'https://api.openai.com/v1'
        ),

    ],

];
