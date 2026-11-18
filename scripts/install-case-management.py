#!/usr/bin/env python3
"""Guided local installer; never runs migrations, seeders or sudo."""
import argparse
import os
from pathlib import Path
import re
import shutil
import subprocess
import sys

REPO = 'https://github.com/YusainiU/Case-Management-Application.git'
DEFAULT_BRANCH = 'main'
EXTENSIONS = ('bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'filter', 'intl',
              'mbstring', 'openssl', 'pdo', 'pdo_mysql', 'simplexml',
              'tokenizer', 'xml', 'xmlwriter', 'zip')

ENV_FALLBACK = '''APP_NAME="Case Management"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=case_management
DB_USERNAME=
DB_PASSWORD=
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=180
MAIL_MAILER=log
FILEUPLOAD_DISK=local
AI_CASE_ASSISTANT_ENABLED=false
AI_AGENT_EXECUTION_ENABLED=false
AI_ENGINEER_EXECUTION_ENABLED=false
AI_WORK_START_EXECUTION_ENABLED=false
OPENAI_API_KEY=
CASE_PUBLIC_HOLIDAY_COUNTRY=GB
CASE_PUBLIC_HOLIDAY_SOURCE=auto
# Optional ISO 3166-2 state/province, e.g. DE-BY with country DE:
CASE_PUBLIC_HOLIDAY_SUBDIVISION=
'''


def version(text):
    match = re.search(r'\d+(?:\.\d+){1,2}', text or '')
    if match is None:
        return (0, 0, 0)
    pieces = [int(x) for x in match.group().split('.')]
    return tuple((pieces + [0, 0, 0])[:3])


def result(command, *, cwd=None):
    return subprocess.run(command, cwd=cwd, text=True, capture_output=True, check=False)


def check_host():
    print('== PRE-FLIGHT: all requirements must pass before cloning ==')
    failures = []
    requirements = (
        ('git', ['git', '--version'], (2, 20, 0), 'sudo apt install git'),
        ('php', ['php', '-r', 'echo PHP_VERSION;'], (8, 3, 0), 'Install PHP 8.3+ CLI'),
        ('composer', ['composer', '--version'], (2, 0, 0), 'https://getcomposer.org/download/'),
        ('node', ['node', '--version'], (22, 12, 0), 'https://nodejs.org/ (Node 22 LTS recommended)'),
        ('npm', ['npm', '--version'], (10, 0, 0), 'Install npm 10+'),
    )
    for name, cmd, minimum, guide in requirements:
        if not shutil.which(name):
            failures.append(f'{name}: missing; need {minimum}, install: {guide}')
            continue
        proc = result(cmd)
        found = version(proc.stdout or proc.stderr)
        supported = (found >= (22, 12, 0) or
                     (20, 19, 0) <= found < (21, 0, 0)) if name == 'node' else found >= minimum
        if proc.returncode != 0 or not supported:
            failures.append(f'{name}: found {found}, need {"20.19.x or >=22.12" if name == "node" else minimum}; {guide}')
        else:
            print(f'  OK {name}: {".".join(map(str, found))}')

    if not shutil.which('unzip'):
        failures.append('unzip: missing; install with sudo apt install unzip')
    else:
        print('  OK unzip')

    if shutil.which('php'):
        for ext in EXTENSIONS:
            proc = result(['php', '-r', 'exit(extension_loaded($argv[1]) ? 0 : 1);', ext])
            if proc.returncode:
                failures.append(f'PHP extension {ext}: missing for CLI PHP 8.3+; check php --ini and Ubuntu php8.3-{ext} packages')
            else:
                print(f'  OK PHP extension {ext}')

    if failures:
        print('\nSTOP — install or upgrade these requirements:')
        for problem in failures:
            print('  - ' + problem)
        print('\nRerun the installer after resolving ALL requirements (or use --check-only).')
        return False
    print('All baseline checks passed. Live MySQL access is checked after configuring .env.')
    return True


def run(command, directory, label):
    print(f'\n== {label} ==')
    print('Running:', ' '.join(command))
    code = subprocess.run(command, cwd=directory, check=False).returncode
    if code:
        sys.exit(f'{label} failed (exit {code}). Fix the error above, then rerun and reuse the checkout.')


def setup():
    parser = argparse.ArgumentParser(description='Guided Ubuntu/Linux local Case Management setup')
    parser.add_argument('--check-only', action='store_true', help='Run host prerequisite checks and exit')
    args = parser.parse_args()
    if os.geteuid() == 0:
        sys.exit('Do not run installer as root/sudo. Use your normal Linux account.')
    if not check_host():
        sys.exit(2)
    if args.check_only:
        return
    if not sys.stdin.isatty():
        sys.exit('Interactive terminal required. Use --check-only for headless checks.')

    name = input('\nClone directory [Case-Management-Application]: ').strip() or 'Case-Management-Application'
    if name in ('.', '..') or Path(name).name != name or '\\' in name:
        sys.exit('Use a single directory name, without /, \\, . or ..')
    branch = input('Git branch [main] (optional: main): ').strip() or DEFAULT_BRANCH
    if branch.startswith('-') or any(c.isspace() for c in branch):
        sys.exit('Invalid Git branch name.')
    destination = Path.cwd() / name
    if destination.exists():
        url = result(['git', '-C', str(destination), 'remote', 'get-url', 'origin'])
        existing_branch = result(['git', '-C', str(destination), 'branch', '--show-current'])
        if url.stdout.strip() != REPO or existing_branch.stdout.strip() != branch:
            sys.exit('Existing destination is not a matching clone/branch. Choose a different directory.')
        if input('Reuse existing clone without git pull/reset? [y/N]: ').strip().lower() != 'y':
            sys.exit('Stopped without changing the existing checkout.')
    else:
        run(['git', 'clone', '--single-branch', '--branch', branch, REPO, str(destination)], None, 'Clone repository')

    if not all((destination / x).exists() for x in ('artisan', 'composer.json', 'package.json')):
        sys.exit('Incomplete checkout: artisan/composer.json/package.json missing.')
    run(['composer', 'install', '--no-interaction', '--prefer-dist'], destination, 'Install Composer dependencies')
    npm_cmd = ['npm', 'ci'] if (destination / 'package-lock.json').exists() else ['npm', 'install']
    run(npm_cmd, destination, 'Install npm dependencies')
    run(['npm', 'run', 'build'], destination, 'Build assets')

    env = destination / '.env'
    if not env.exists():
        previous_umask = os.umask(0o077)
        try:
            if (destination / '.env.example').exists():
                shutil.copy2(destination / '.env.example', env)
                print('Created .env from .env.example')
            else:
                env.write_text(ENV_FALLBACK, encoding='utf-8')
                print('No .env.example found; created safe starter .env without secrets')
        finally:
            os.umask(previous_umask)
        env.chmod(0o600)
    else:
        print('Preserving existing .env')
    if not re.search(r'^APP_KEY=base64:', env.read_text(encoding='utf-8'), flags=re.MULTILINE):
        run(['php', 'artisan', 'key:generate', '--no-interaction'], destination, 'Generate APP_KEY')

    print(f'''\nINITIAL SETUP COMPLETE: {destination}

Manual next steps (do these from the project directory):
  1. Edit .env: configure MySQL DB_DATABASE, DB_USERNAME, DB_PASSWORD,
     DB_HOST, APP_URL, mail; create the database/user. No credentials are stored by this installer.
  2. Verify MySQL connectivity and run 'php artisan migrate:status'.
     After reviewing migrations: 'php artisan migrate'.
  3. Review seeders, then optionally 'php artisan db:seed' (initial admin
     credentials documented in README are admin@example.com/password123;
     CHANGE THEM IMMEDIATELY). DemoDatabaseSeeder is for nonproduction only.
  4. 'php artisan storage:link' for public uploads. Review package-specific
     vendor:publish steps; do not blindly publish everything.
  5. OPTIONAL: use an isolated test DB and run 'php artisan test'.
  6. Start local development: 'composer run dev', then browse http://127.0.0.1:8000.

For production: configure HTTPS web server, APP_DEBUG=false, queues via
Supervisor, cron scheduler, backups, permissions and monitoring separately.
Never run composer run dev as your production process. AI features are optional
and default off in the fallback environment; provider calls may incur costs.
''')


if __name__ == '__main__':
    setup()
