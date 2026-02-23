<?php

namespace Simplydigital\EngineerManagement\Provider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

use Simplydigital\EngineerManagement\Http\Components\BladeEngineerManagementComponent;
use Simplydigital\EngineerManagement\Http\Components\BladeSkillComponent;
use Simplydigital\EngineerManagement\Http\Components\BladeCertificateComponent;
use Simplydigital\EngineerManagement\Http\Components\BladeEngineersSummaryComponent;

use Simplydigital\EngineerManagement\Livewire\AddUpdateEngineerModalComponent;
use Simplydigital\EngineerManagement\Livewire\AddUpdateSkillModalComponent;
use Simplydigital\EngineerManagement\Livewire\AddUpdateCertificateModalComponent;
use Simplydigital\EngineerManagement\Livewire\SkillsComponent;
use Simplydigital\EngineerManagement\Livewire\EngineerManagementComponent;
use Simplydigital\EngineerManagement\Livewire\AddUpdateEngineerSkillsModalComponent;
use Simplydigital\EngineerManagement\Livewire\AddUpdateEngineerCertificateModalComponent;
use Simplydigital\EngineerManagement\Livewire\CertificateComponent;
use Simplydigital\EngineerManagement\Livewire\EngineerCertificateComponent;
use Simplydigital\EngineerManagement\Livewire\EngineersSummaryComponent;
use Simplydigital\EngineerManagement\Livewire\EngineerDetailComponent;
use Simplydigital\EngineerManagement\Livewire\EngineerSkillComponent;

use Simplydigital\EngineerManagement\Services\EngineerManagementServices;

class EngineerManagementServiceProvider extends ServiceProvider
{
    /**
    * Bootstrap any package services.
    * @return void  
    */
    public function boot()
    {
        $this->loadViews();
        $this->publishFiles();
        $this->registerBladeComponents();      
        $this->registerLivewireComponents();
     
    }

    public function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'engineermanagement');
        $this->loadViewComponentsAs('engineermanagement-ui',[\Simplydigital\EngineerManagement\View\Components\Icon::class]);
        if (file_exists(__DIR__ . '/../Routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        }    
    }

    public function publishFiles(): void
    {
        //Publish migrations
        $this->publishMigrations();
        //Publis config
        $this->publishConfig();
        //Publish resources
        $this->publishResources();

    }

    public function publishMigrations(): void
    {
        $this->publishes([
            __DIR__ . '/../Database/migrations/create_engineer_management_table.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_engineer_management_table.php'),
            __DIR__ . '/../Database/migrations/create_skills_table.stub' => database_path('migrations/' . date('Y_m_d_His', time() + 1) . '_create_skills_table.php'),
            __DIR__ . '/../Database/migrations/create_engineer_skills_table.stub' => database_path('migrations/' . date('Y_m_d_His', time() + 2) . '_create_engineer_skills_table.php'),
            __DIR__ . '/../Database/migrations/create_certificate_table.stub' => database_path('migrations/' . date('Y_m_d_His', time() + 3) . '_create_certificate_table.php'),
            __DIR__ . '/../Database/migrations/create_engineer_certification_table.stub' => database_path('migrations/' . date('Y_m_d_His', time() + 4) . '_create_engineer_certification_table.php'),  
        ], 'engineer-management-migrations');        
    }

    public function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/engineer-management.stub' => config_path('engineer-management.php'),
        ], 'engineer-management-config');
    }

    public function publishResources(): void
    {
        $this->publishes([
            __DIR__.'/../Resources/icons' => resource_path('Simplydigital/EngineerManagement/icons')
        ], 'simplydigital-resources');
    }

    public function registerBladeComponents(): void
    {
        Blade::component('blade-engineermanagement', BladeEngineerManagementComponent::class);
        Blade::component('blade-skill', BladeSkillComponent::class);
        Blade::component('blade-certificate', BladeCertificateComponent::class);
        Blade::component('blade-engineerssummary',BladeEngineersSummaryComponent::class);
    }

    public function registerLivewireComponents(): void
    {
        Livewire::component('engineermanagement-component', EngineerManagementComponent::class);
        Livewire::component('addupdateengineermodal-component', AddUpdateEngineerModalComponent::class);
        Livewire::component('addupdateskillmodal-component', AddUpdateSkillModalComponent::class);
        Livewire::component('addupdatecertificatemodal-component', AddUpdateCertificateModalComponent::class);
        Livewire::component('skills-component', SkillsComponent::class);
        Livewire::component('certificate-component', CertificateComponent::class);
        Livewire::component('addupdateengineerskillsmodal-component', AddUpdateEngineerSkillsModalComponent::class);
        Livewire::component('addupdateengineercertificatesmodal-component', AddUpdateEngineerCertificateModalComponent::class);
        Livewire::component('engineerssummary-component', EngineersSummaryComponent::class);
        Livewire::component('engineerdetail-component', EngineerDetailComponent::class);
        Livewire::component('engineerskill-component', EngineerSkillComponent::class);
        Livewire::component('engineercertificate-component', EngineerCertificateComponent::class);
    }

    public function register(): void
    {
        $this->app->singleton('engineermanagementservices', function () {
            return new EngineerManagementServices();
        });       
    }    

}