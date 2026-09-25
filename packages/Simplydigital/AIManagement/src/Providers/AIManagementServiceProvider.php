<?php

namespace Simplydigital\AIManagement\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Simplydigital\AIManagement\AI\Contracts\AIService;
use Simplydigital\AIManagement\AI\Prompts\CaseAssistantPrompt;
use Simplydigital\AIManagement\AI\Providers\OpenAIService;
use Simplydigital\AIManagement\AI\Services\CaseAssistant;
use Simplydigital\AIManagement\Authorization\AIPermissionProvider;
use Simplydigital\UserManagement\Services\PermissionRegistry;

class AIManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        $this->mergeConfigFrom(
            __DIR__.'/../../config/ai-management.php',
            'ai-management'
        );

        $this->app->singleton(
            AIService::class,
            function ($app) {
                return match (
                    config('ai-management.provider')
                ) {
                    'openai' => $app->make(OpenAIService::class),
                    default => throw new \RuntimeException(
                        'Unsupported AI provider: '.config('ai-management.provider')
                    ),

                };
            }
        );

        $this->app->singleton(
            CaseAssistantPrompt::class
        );

        $this->app->singleton(
            CaseAssistant::class
        );

        $this->app->afterResolving(
            PermissionRegistry::class,
            function (PermissionRegistry $registry) {
                $registry->registerProvider(
                    new AIPermissionProvider
                );
            }
        );

    }

    public function boot(): void
    {

        $this->publishes([
            __DIR__.'/../../config/ai-management.php' => config_path('ai-management.php'),
        ], 'ai-management-config');

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'ai-management');  
        
        Livewire::component('ai-management.case-assistant', CaseAssistant::class);        

    }
}
