# Engineer Management

A Laravel 12 monorepo for Simplydigital operational and internal management modules, built with Livewire, Fortify, and modular package-based architecture.

This project brings together several domain modules under a single application shell while keeping them organized as reusable packages in `packages/Simplydigital`.

## Overview

The application is designed to support a set of management workflows, including:

- Engineer management
- User management
- Case management
- Event planning
- Notifications
- File upload handling
- AI-powered tooling

The base application is a Laravel app, and the business capabilities are split into focused package modules to keep the codebase maintainable and scalable.

## Project structure

```text
.
├── app/
│   ├── Console/
│   ├── Http/
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
│       ├── FileUploadManagement/
│       ├── NotificationManagement/
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
└── README.md
```

## Stack

- PHP 8.2+
- Laravel 12
- Livewire
- Vite + Tailwind CSS
- Composer path-based package development
- PHPUnit for testing
- MySQL-ready configuration

## Requirements

Before you begin, ensure you have:

- PHP 8.2 or later
- Composer
- Node.js 18+ and npm
- A local database (MySQL is supported by the project configuration)

## Local setup

1. Install PHP dependencies:

```bash
composer install
```

2. Install frontend dependencies:

```bash
npm install
```

3. Configure your environment:

- Update `.env` with your application key, database credentials, and app URL if needed.
- Ensure the database exists and is accessible.

4. Run the database migrations:

```bash
php artisan migrate
```

5. Start the application:

For the full dev environment:

```bash
composer run dev
```

Or run the app and frontend separately:

```bash
php artisan serve
npm run dev
```

## Available packages

The monorepo includes the following package modules:

- `AIManagement` — AI-related functionality and service integrations
- `CaseManagement` — case tracking and workflow support
- `EngineerManagement` — engineer-related domain logic
- `EventPlannerManagement` — calendar/event planning features
- `FileUploadManagement` — upload and storage workflows
- `NotificationManagement` — notification delivery and related services
- `UserManagement` — user access and administration

These packages are registered through Composer path repositories in the root `composer.json`, which makes them easy to work on locally without publishing them to a package registry.

## Development workflow

### Run tests

```bash
php artisan test
```

### Build frontend assets

```bash
npm run build
```

### Check formatting

```bash
./vendor/bin/pint
```

## Contributing

1. Create a feature branch from the main branch.
2. Make changes in the relevant package or app layer.
3. Add or update tests for behavior changes.
4. Run the relevant test suite before submitting changes.

## Notes

This repository is structured as a modular Laravel application rather than a single monolithic feature set. That enables package-level isolation while keeping the full platform under one project shell.

If you are working on a specific domain package, start in the corresponding folder under `packages/Simplydigital` and keep package boundaries consistent with the surrounding architecture.
