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
    | Human-approved AI actions
    |--------------------------------------------------------------------------
    */

    'approvals' => [
        // Safe default: proposals may be reviewed, but no business action is executed.
        'execution_enabled' => env('AI_AGENT_EXECUTION_ENABLED', false),
    ],

    // Independent opt-in: enabling engineer execution never enables category changes.
    'engineer_assignment' => [
        'execution_enabled' => env('AI_ENGINEER_EXECUTION_ENABLED', false),
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

    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),

        'base_url' => env(
            'DEEPSEEK_BASE_URL',
            'https://api.deepseek.com'
        ),
    ],    

];
