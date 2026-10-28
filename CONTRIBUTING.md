# Contributing

Thank you for considering a contribution. This project is in beta, and
contributions that improve reliability, documentation, accessibility, and
tests are welcome.

## Before you start

- Check existing issues and pull requests to avoid duplicating work.
- For significant changes, open an issue first to discuss the proposed approach.
- For security issues, follow [SECURITY.md](SECURITY.md); do not open a public
  issue.

## Development setup

1. Install PHP 8.2 or newer, Composer, Node.js, npm, and a supported database.
2. Install dependencies with `composer install` and `npm install`.
3. Copy `.env.example` to `.env`, configure local services, and run
   `php artisan key:generate`.
4. Run database migrations with `php artisan migrate`.
5. Start the development environment with `composer run dev`.

Some dependencies may require separate credentials or a license, including
Flux UI. Do not commit credentials, license keys, `.env` files, or generated
dependency/build output. If you do not have access to a required dependency,
raise that in an issue before submitting work that depends on it.

## Making changes

- Create a focused branch from `main`.
- Follow the existing Laravel and package conventions. Application modules
  live primarily under `packages/Simplydigital`; the root `app/` directory
  contains application-shell concerns.
- Add or update tests for behavior changes.
- Keep changes focused and update relevant documentation.

## Checks

Run the relevant checks before opening a pull request:

```bash
vendor/bin/pint
vendor/bin/phpunit
npm run build
```

If a check cannot run locally because a licensed dependency or service is
unavailable, mention that in the pull request.

## Pull requests

Include a concise description, the reason for the change, testing performed,
and any relevant screenshots for user-facing changes. Call out breaking
changes, configuration or migration requirements, and known limitations.
