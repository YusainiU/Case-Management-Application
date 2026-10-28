# Case Management Application

![SimplyDigital logo](public/simplydigital-logo.svg)

A Laravel-based management platform for Simplydigital operations, built around modular packages in `packages/Simplydigital` and powered by Laravel, Livewire, and Tailwind.

This repository is a monorepo-style application with a Laravel application shell and several feature packages that can be developed and tested independently while still sharing the same app runtime.

> **Beta testing:** The current version is ready for real-use testing, but remains in the beta testing stage. If you are interested in helping, you are welcome to clone the repository, run the application, and test it.

## Stack

- PHP 8.2+
- Laravel 13
- Livewire 4
- Livewire Flux 2
- Tailwind CSS 4
- Vite for frontend assets
- Composer path-based package development
- PHPUnit for automated testing

## Overview

The application includes operational modules for:

- Case management
- A customer portal
- Engineer management
- User management
- Event planning
- Stock management, including part requests, orders, dispatches, and receipts
- Expense claims
- Notifications
- File upload handling
- Table and workflow views for managing cases
- An AI-powered Case Assistant helper

The business logic and UI are primarily delivered through package modules, with the main `/app` layer handling the Laravel application shell, authentication and bootstrap concerns.

## Project structure

```text
.
├── app/
│   ├── Console/
│   ├── Http/
│   ├── Livewire/
│   ├── Models/
│   ├── Providers/
│   └── ...
├── bootstrap/
├── config/
├── database/
├── packages/
│   └── Simplydigital/
│       ├── AIManagement/
│       ├── CaseManagement/
│       ├── EngineerManagement/
│       ├── EventPlannerManagement/
│       ├── Expenses/
│       ├── FileUploadManagement/
│       ├── NotificationManagement/
│       ├── StockManagement/
│       └── UserManagement/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── artisan
├── composer.json
├── package.json
├── phpunit.xml
├── vite.config.js
├── .env.example
└── README.md
```

## Package architecture

The project is structured around reusable package directories under `packages/Simplydigital`, for example:

- `AIManagement` — AI service integrations and case-assistant tooling
- `CaseManagement` — customer portal and case workflows, including table and workflow views, dashboard, SLA logic, and site visits
- `EngineerManagement` — engineers, skills, certificates and related views
- `EventPlannerManagement` — event scheduling and planner tooling
- `Expenses` — expense claim workflows
- `FileUploadManagement` — upload, storage and file handling workflows
- `NotificationManagement` — notifications and communication features
- `StockManagement` — part requests, orders, dispatches, and receipt tracking
- `UserManagement` — access, roles, and user administration

These packages are registered through Composer path repositories in the root `composer.json`.

## Requirements

Before running the project, ensure you have:

- PHP 8.2 or newer
- Composer
- Node.js 18+ and npm
- A local database (MySQL is configured and supported)
- An `.env` file for local application configuration

## How to install

1. Clone the repository and enter the project directory:

```bash
git clone <repository-url>
cd Case-Management-Application
```

2. Install PHP dependencies:

```bash
composer install
```

3. Install frontend dependencies:

```bash
npm install
```

4. Create your local environment file:

```bash
cp .env.example .env
```

Then edit `.env` and set values for:

- application URL
- database connection and credentials
- mail configuration
- AI provider configuration (if using the AI management features)

Example settings:

```env
APP_NAME="Case Management"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=case_management
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

AUDIT_AUTO_MIGRATION=true
AUDIT_RETENTION_ENABLED=false
AUDIT_RETENTION_DAYS=365
AUDIT_RETENTION_STRATEGY=delete
AUDIT_RETENTION_BATCH_SIZE=1000

FILEUPLOAD_DISK=local
FILEUPLOAD_ROOT=uploads
FILEUPLOAD_MAX_SIZE=10240

AI_MANAGEMENT_PROVIDER=openai
AI_MANAGEMENT_MODEL=gpt-4o-mini
AI_CASE_ASSISTANT_ENABLED=true
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_API_KEY=
```

5. Generate the application key:

```bash
php artisan key:generate
```

6. Run the database migrations:

```bash
php artisan migrate
```

7. Seed the database with the required initial account and demo data:

```bash
php artisan db:seed
php artisan db:seed --class=DemoDatabaseSeeder
```

`DatabaseSeeder` creates the initial Super Admin account required to log in:

| Username | Password |
| --- | --- |
| `admin@example.com` | `password123` |

**Change the Super Admin username and password immediately after your first login.** These are default credentials and should not be left in place.

`DemoDatabaseSeeder` creates sample users, customers, suppliers, and products for testing. It is intended for non-production environments and will refuse to seed demo data in production.

8. Build frontend assets for the first time:

```bash
npm run build
```

9. Start the app in development mode:

```bash
composer run dev
```

This starts Laravel, the queue worker, logs, and Vite together.

If you prefer to run pieces separately:

```bash
php artisan serve
npm run dev
```

## Typical local workflow

- Backend and routes: `php artisan serve`
- Frontend assets: `npm run dev`
- Database migration updates: `php artisan migrate`
- Package package-level changes: edit files under `packages/Simplydigital/*`
- Production asset build: `npm run build`

## UI previews and Blade-based snapshots

Yes. It is possible to produce page snapshots from the Blade templates directly, especially for static or semi-static layouts such as the case dashboard, customer summaries, and other UI sections.

The project already uses Blade views and reusable component partials under the package view directories, for example:

- `packages/Simplydigital/CaseManagement/src/Resources/views/livewire/case/dashboard.blade.php`
- `packages/Simplydigital/CaseManagement/src/Resources/views/components/page-elements/...`

These can be rendered in one of two ways:

1. Real app preview: use the actual Livewire component in the browser with seeded data.
2. Static layout preview: render a Blade view or page fragment with sample values for a design review or screenshot capture.

Example for a static preview route:

```php
Route::get('/preview/case-dashboard', function () {
    $status = [
        'totalOpenCases' => 18,
        'nearBreach' => 7,
        'unassigned' => 4,
        'noUpdate' => 12,
        'resolved' => 91,
        'paused' => 3,
        'total' => 142,
        'totalVisits' => 28,
    ];

    return view('casemanagement::preview.case-dashboard', compact('status'));
});
```

This is useful for visual QA or generating layout snapshots when you only need a UI reference and not live data.

For a real dashboard page, the route already exists in `routes/web.php` using the Livewire case dashboard component:

```php
Route::get('/dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
```

In other words, the Blade files can absolutely be used as the source for snapshots, but the fully live experience still depends on the Laravel + Livewire runtime and any real data behind it.

## Contributing

1. Create a feature branch from the main branch.
2. Make changes in the relevant package or app layer.
3. Add or update tests for logic and behavior changes.
4. Run the smallest relevant test suite before submitting changes.

## Notes

This repository is intentionally modular rather than a single monolithic app. That helps keep domain logic organized while allowing package-level development inside one Laravel application shell.

If you are working on a specific domain, start in the corresponding folder under `packages/Simplydigital` and keep package boundaries consistent with the surrounding architecture.
