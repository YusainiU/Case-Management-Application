# Guided local installation — Ubuntu / Linux

This project provides a **guided Python installer** which inspects the host before downloading the Case Management Application. It is designed for **local development / beta testing**, not automated production server provisioning.

**Installer:** [`scripts/install-case-management.py`](../scripts/install-case-management.py)

## Quick start

You need **Python 3.8+** to launch the Python installer. Download the file from GitHub (or use the following commands, which download from the feature branch):

```bash
curl -fL -o install-case-management.py \
  https://raw.githubusercontent.com/YusainiU/Case-Management-Application/feature/ai-agent-approvals-v1/scripts/install-case-management.py

# Inspect any downloaded installer before running it:
less install-case-management.py

# Run a no-change preflight inspection first:
python3 install-case-management.py --check-only

# Begin the guided checkout and dependency installation:
python3 install-case-management.py
```

Prefer a pinned commit URL for repeatable or security-sensitive installations. The script itself **never runs sudo**, modifies host packages, or executes remotely fetched shell code.

## What is checked

| Tool/requirement | Version / condition | Reason |
| --- | --- | --- |
| Python | 3.8+ | Required to execute the installer |
| Git | 2.20+ | Clone the public source repository |
| PHP CLI | **8.3+** | Laravel 13 requires PHP 8.3+; the README's older PHP 8.2 note is not sufficient |
| Composer | 2+ | Install locked PHP and local path-package dependencies |
| Node.js | **20.19.x or 22.12+** (22 LTS recommended) | Vite 8 runtime requirement |
| npm | 10+ | Install and build frontend dependencies |
| unzip | available | Composer package extraction |
| PHP extensions | `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `intl`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `simplexml`, `tokenizer`, `xml`, `xmlwriter`, `zip` | PHP framework, package and MySQL integrations |

When **any baseline prerequisite fails**, the script prints which requirement is missing or too old, guidance for installing it, and **exits without cloning or installing**. Install the missing software, then rerun the exact same script. The script never attempts to install system packages itself.

**MySQL:** the installer verifies the PHP MySQL driver, but does not insist on a local MySQL server when the application might use a remote instance. Create and configure a supported MySQL database separately. Live connection and credentials cannot be validated until the user has configured `.env`. Check MySQL compatibility against the deployed Laravel/database versions and your operational environment.

## Guided install flow

1. The **preflight** reports pass/fail for every baseline requirement and exits on missing requirements.
2. Once preflight passes, the script asks for the **destination folder** (default: `Case-Management-Application`) and **Git branch** (default: `main`). If testing the AI Case Assistant work in draft PR #29, select `feature/ai-agent-approvals-v1` instead.
3. It performs `git clone --single-branch --branch ...`. If a destination already exists, it will reuse it **only** if the repository origin and branch match and the user confirms. It never pulls, resets or overwrites a checkout.
4. After verifying `artisan`, `composer.json` and `package.json`, it runs `composer install --no-interaction --prefer-dist`, then `npm ci` if `package-lock.json` is present (otherwise `npm install`), then `npm run build`. Each failure is printed and stops the installer.
5. It preserves any existing `.env`. Otherwise it copies `.env.example` if present or generates a **private** starter `.env` if absent. The generated file has mode `0600` and contains no database credentials or AI API key.
6. It runs `php artisan key:generate --no-interaction` only when no generated app key is already set, and prints a checklist for the remaining user-controlled steps.

**Important:** this repository's `composer.json` contains a `setup` script that automatically runs migrations. The guided installer intentionally uses **`composer install`**, not `composer run setup`, to leave migrations under explicit user control.

## Complete the application setup manually

From the new project directory:

```bash
cd Case-Management-Application  # or your chosen directory
nano .env                       # set DB_* credentials, APP_URL, mail, etc.
php artisan migrate:status      # inspect planned/current schema state
php artisan migrate             # only after reviewing the database and migrations
php artisan storage:link        # public upload/download symlink
```

Review any package documentation before running `php artisan vendor:publish`. Do not blindly publish all assets or run `--force` over existing files.

**Seeders need care.** The README documents `php artisan db:seed` to create the initial administrative login (currently `admin@example.com` / `password123`), which **must be changed immediately**. Only run seeders after reviewing their effects and on the intended database; `php artisan db:seed --class=DemoDatabaseSeeder` is for testing/demo environments, not production.

Optionally run the automated suite using a **separate test database**:

```bash
php artisan test
```

When the database is configured and migrations have completed, launch local development:

```bash
composer run dev
```

That script runs the Laravel dev server, the queue listener, application logs and Vite together. Visit `http://127.0.0.1:8000` unless `APP_URL` or the server port differs.

## Production deployment is a separate process

Running `composer run dev` is **not** an acceptable production process. Configure a hardened web server + HTTPS, production environment flags, least-privilege filesystem permissions, backups, mail, optional AI API keys and provider consent, Laravel scheduler via cron, and a persistent background worker. See the [Ubuntu Supervisor Queue Worker Guide](UBUNTU_SUPERVISOR_QUEUE_WORKER_GUIDE.md). Remember that AI analysis/recommendation jobs may incur provider charges.

**Security:** Never use `sudo python3 install-case-management.py`; do not commit `.env`; do not run the admin seeder on an existing production database without reviewing its implementation. The script is designed to stop and explain failures rather than run guessed host repair commands.

## References

- [Laravel 13 deployment requirements](https://laravel.com/docs/13.x/deployment)
- [Vite 8 Node.js requirement](https://vite.dev/blog/announcing-vite8.html)
- [Composer installation](https://getcomposer.org/download/)
