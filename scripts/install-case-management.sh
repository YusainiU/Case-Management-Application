#!/usr/bin/env bash
# Guided Ubuntu/Linux installer for YusainiU/Case-Management-Application.
# No sudo, auto package installs, migrations, seeding, or remote script execution.
set -Eeuo pipefail

readonly REPO_URL='https://github.com/YusainiU/Case-Management-Application.git'
readonly DEFAULT_DIR='Case-Management-Application'
readonly DEFAULT_BRANCH='main'
readonly SCRIPT_NAME="$(basename "$0")"

if [[ -t 1 ]]; then
  RED=$'\033[31m'; GREEN=$'\033[32m'; YELLOW=$'\033[33m'; BLUE=$'\033[34m'; RESET=$'\033[0m'
else
  RED=''; GREEN=''; YELLOW=''; BLUE=''; RESET=''
fi

say() { printf '%s\n' "$*"; }
info() { printf '%s%s%s\n' "$BLUE" "$*" "$RESET"; }
warn() { printf '%sWARNING: %s%s\n' "$YELLOW" "$*" "$RESET" >&2; }
fail() { printf '%sERROR: %s%s\n' "$RED" "$*" "$RESET" >&2; exit 1; }
ok() { printf '%sOK: %s%s\n' "$GREEN" "$*" "$RESET"; }
step() { printf '\n%s== %s ==%s\n' "$BLUE" "$*" "$RESET"; }

usage() {
  cat <<EOF
$SCRIPT_NAME — interactive Case Management Application setup (Ubuntu/Linux)

Usage: bash $SCRIPT_NAME [--check-only] [--help]

  --check-only   Inspect the host; do not clone or install anything.
  --help         Show this help.

What this script DOES:
  Check required tools, versions and PHP extensions before cloning.
  Ask for a clone directory and Git branch, clone the application.
  Install Composer and npm dependencies, build frontend assets.
  Prepare .env (without credentials), generate an application key.
  Explain manual database, migration, seeding, storage, and dev steps.

What it DOES NOT do:
  Install system software, change permissions via sudo, create DB users,
  migrate/seed databases, publish vendor assets blindly, or deploy a
  secure production web server. It is a guided LOCAL development setup.
EOF
}

CHECK_ONLY=0
for arg in "$@"; do
  case "$arg" in
    --check-only) CHECK_ONLY=1 ;;
    --help|-h) usage; exit 0 ;;
    *) usage; fail "Unknown argument: $arg" ;;
  esac
done

if [[ "$EUID" -eq 0 ]]; then
  fail 'Run this script as your regular Linux user, not as root or sudo.'
fi

# Semantic version compares for the requirements we actually validate.
version_at_least() {
  local actual="$1" required="$2" a b c x y z
  actual="${actual#v}"; required="${required#v}"
  actual="${actual%%[^0-9.]*}"; required="${required%%[^0-9.]*}"
  IFS=. read -r a b c <<< "$actual"
  IFS=. read -r x y z <<< "$required"
  [[ "${a:-}" =~ ^[0-9]+$ && "${x:-}" =~ ^[0-9]+$ ]] || return 1
  a=$((10#$a)); b=$((10#${b:-0})); c=$((10#${c:-0}))
  x=$((10#$x)); y=$((10#${y:-0})); z=$((10#${z:-0}))
  (( a > x || (a == x && b > y) || (a == x && b == y && c >= z) ))
}

missing=()
report_missing() {
  local name="$1" need="$2" guidance="$3"
  missing+=("$name: $need")
  printf '%sMISSING / TOO OLD: %s — %s%s\n' "$RED" "$name" "$need" "$RESET"
  say "  Install guidance: $guidance"
}
check_tool() {
  local name="$1" required="$2" detected="$3" guidance="$4"
  if ! command -v "$name" >/dev/null 2>&1; then
    report_missing "$name" "$required" "$guidance"; return
  fi
  local output
  output="$($name --version 2>/dev/null | head -n1 || true)"
  if [[ "$name" == 'php' ]]; then
    output="$(php -r 'echo PHP_VERSION;' 2>/dev/null || true)"
  elif [[ "$name" == 'composer' ]]; then
    output="$(composer --no-ansi --version 2>/dev/null | grep -oE '[0-9]+\.[0-9]+(\.[0-9]+)?' | head -n1 || true)"
  elif [[ "$name" == 'git' ]]; then
    output="$(git --version | grep -oE '[0-9]+\.[0-9]+(\.[0-9]+)?' | head -n1 || true)"
  elif [[ "$name" == 'npm' ]]; then
    output="$(npm --version 2>/dev/null || true)"
  elif [[ "$name" == 'node' ]]; then
    output="$(node --version 2>/dev/null || true)"
  fi
  if [[ "$name" == 'node' ]]; then
    if version_at_least "$output" '22.12.0' || { version_at_least "$output" '20.19.0' && ! version_at_least "$output" '21.0.0'; }; then
      ok "Node.js $output (Vite 8 compatible)"
    else
      report_missing 'Node.js' '20.19.x or 22.12+ (recommend Node 22 LTS)' "$guidance (detected: ${output:-unknown})"
    fi
  elif version_at_least "$output" "$required"; then
    ok "$name $output (minimum $required)"
  else
    report_missing "$name" "minimum $required" "$guidance (detected: ${output:-unknown})"
  fi
}

step '1/5 — Host prerequisites'
say 'Target: Laravel 13 (PHP 8.3+) and Vite 8 (Node 20.19.x or 22.12+).'
say 'No changes will be made until ALL preflight checks pass.'
say ''
check_tool git '2.20.0' '' 'Ubuntu: sudo apt install git'
check_tool php '8.3.0' '' 'Install PHP 8.3+ CLI and the extensions listed below (your Ubuntu package source may vary).'
check_tool composer '2.0.0' '' 'Install Composer 2 from https://getcomposer.org/download/'
check_tool node '22.12.0' '' 'Install a compatible Node release from https://nodejs.org/ or nvm.'
check_tool npm '10.0.0' '' 'Install npm 10+ alongside a compatible Node release.'
if ! command -v unzip >/dev/null 2>&1; then
  report_missing unzip 'command-line unzip' 'Ubuntu: sudo apt install unzip'
else
  ok 'unzip available'
fi

if command -v php >/dev/null 2>&1; then
  # Framework/Composer extensions. Also require pdo_mysql for this app's
  # documented MySQL setup. More package-specific requirements are checked
  # by Composer itself during the dependency installation.
  for ext in bcmath ctype curl dom fileinfo filter intl mbstring openssl pdo pdo_mysql simplexml tokenizer xml xmlwriter zip; do
    if php -r 'exit(extension_loaded($argv[1]) ? 0 : 1);' "$ext" 2>/dev/null; then
      ok "PHP extension: $ext"
    else
      report_missing "PHP extension $ext" 'enabled for CLI PHP 8.3+' "Check 'php --ini'; on Ubuntu try the matching php8.3-$ext package when available."
    fi
  done
fi

if ((${#missing[@]} > 0)); then
  printf '\n%sPreflight failed: %d requirement(s) missing or incompatible.%s\n' "$RED" "${#missing[@]}" "$RESET"
  say 'Install or upgrade the requirements above, then RERUN THE SAME SCRIPT.'
  say "You may rerun: bash $SCRIPT_NAME --check-only"
  exit 2
fi
ok 'All baseline executable and PHP extension checks passed.'

if ((CHECK_ONLY)); then
  say 'Check-only complete; no clone or installation was attempted.'
  say 'Note: Database credentials/connectivity and Composer package-specific checks come later.'
  exit 0
fi

if [[ ! -t 0 ]]; then
  fail 'Interactive setup needs a terminal. Use --check-only for noninteractive checks.'
fi

step '2/5 — Clone repository'
read -r -p "Directory name [${DEFAULT_DIR}]: " dirname
dirname="${dirname:-$DEFAULT_DIR}"
if [[ -z "$dirname" || "$dirname" == '.' || "$dirname" == '..' || "$dirname" == *'/'* || "$dirname" == *'\\'* || "$dirname" == *$'\n'* ]]; then
  fail 'Directory must be a single folder name with no slash or parent traversal.'
fi
read -r -p "Git branch [${DEFAULT_BRANCH}] (AI development: feature/ai-agent-approvals-v1): " branch
branch="${branch:-$DEFAULT_BRANCH}"
if [[ "$branch" == -* || "$branch" == *[[:space:]]* ]]; then
  fail 'Invalid branch name.'
fi

project="$(pwd -P)/$dirname"
if [[ -e "$project" ]]; then
  if [[ -d "$project/.git" ]] && [[ "$(git -C "$project" remote get-url origin 2>/dev/null || true)" == "$REPO_URL" ]]; then
    warn "A clone already exists: $project"
    read -r -p 'Reuse that existing checkout WITHOUT pulling or resetting it? [y/N]: ' reuse
    if [[ ! "$reuse" =~ ^[Yy]$ ]]; then fail 'Stopped without modifying the existing checkout.'; fi
    current_branch="$(git -C "$project" branch --show-current)"
    if [[ "$current_branch" != "$branch" ]]; then
      fail "Existing checkout is on '$current_branch', not '$branch'. Switch branches manually or use another folder."
    fi
  else
    fail "Destination '$project' already exists and is not this repository. Choose another directory."
  fi
else
  say "Cloning $REPO_URL (branch: $branch) into $project"
  if ! git clone --single-branch --branch "$branch" "$REPO_URL" "$project"; then
    fail 'Git clone failed. Check network access, repository availability, disk space, credentials, and the branch name. Rerun after correcting the issue.'
  fi
fi

[[ -f "$project/artisan" && -f "$project/composer.json" && -f "$project/package.json" ]] || fail 'Clone is incomplete or not a Laravel project (artisan/composer.json/package.json missing).'
cd "$project"
ok "Project checkout ready: $project"

step '3/5 — Install PHP and JavaScript dependencies'
say 'Running composer install (not composer update) to respect composer.lock.'
if ! composer install --no-interaction --prefer-dist; then
  fail "Composer install failed. Read the error above (often missing extensions, PHP incompatibility, or dependency access). After fixing it, rerun the script and choose the existing clone."
fi
if [[ -f package-lock.json ]]; then
  say 'Running npm ci from package-lock.json.'
  if ! npm ci; then
    fail 'npm ci failed. Review the log, Node/npm versions, network and disk space. Fix and rerun using the existing clone.'
  fi
else
  warn 'No package-lock.json found: falling back to npm install.'
  if ! npm install; then
    fail 'npm install failed. Fix the reported issue and rerun using the existing clone.'
  fi
fi
say 'Building frontend assets.'
if ! npm run build; then
  fail 'Frontend build failed. Review the error above and rerun after resolving it.'
fi
ok 'Composer dependencies, npm dependencies, and frontend build completed.'

step '4/5 — Prepare Laravel environment (no secrets requested)'
umask 077
if [[ -f .env ]]; then
  warn '.env already exists. Keeping it unchanged.'
else
  if [[ -f .env.example ]]; then
    cp .env.example .env
    ok 'Copied .env.example to .env.'
  else
    warn '.env.example is absent in this checkout; creating a small starter .env with NO passwords or API keys.'
    cat > .env <<'ENVFILE'
APP_NAME="Case Management"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

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
FILEUPLOAD_ROOT=uploads

# Optional AI Assistant (off until provider credentials and permissions configured)
AI_CASE_ASSISTANT_ENABLED=false
AI_AGENT_EXECUTION_ENABLED=false
AI_ENGINEER_EXECUTION_ENABLED=false
AI_WORK_START_EXECUTION_ENABLED=false
OPENAI_API_KEY=
ENVFILE
    ok 'Created starter .env.'
  fi
fi

if ! grep -Eq '^APP_KEY=base64:' .env; then
  say 'Generating APP_KEY for this local installation.'
  if ! php artisan key:generate --no-interaction; then
    fail 'Could not generate APP_KEY. Check .env and storage/bootstrap write permissions.'
  fi
else
  ok 'Existing APP_KEY preserved.'
fi

step '5/5 — Finish setup manually'
cat <<'NEXT'

NEXT STEPS — perform these from your project directory:

  1. Edit .env and configure DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD,
     APP_URL and mail settings. Set up the MySQL database and user yourself.
     Do NOT commit .env to Git or share API keys.

  2. Verify MySQL is running and your database credentials work.
     The preflight checked pdo_mysql, NOT live database access.

  3. Review migrations, then run them when ready:
       php artisan migrate:status
       php artisan migrate

  4. Seed ONLY after checking the seeder and database environment:
       php artisan db:seed
     WARNING: The documented initial admin login is admin@example.com /
     password123. CHANGE THESE CREDENTIALS IMMEDIATELY.
     The demo seeder is for nonproduction testing only:
       php artisan db:seed --class=DemoDatabaseSeeder

  5. Review asset publishing requirements for installed packages rather
     than publishing every package's assets indiscriminately.
     Create Laravel's public storage link when appropriate:
       php artisan storage:link

  6. Recommended optional tests (use an ISOLATED testing database):
       php artisan test
     Check phpunit.xml.dist / your testing environment before running.

  7. Start local development:
       composer run dev
     This runs Laravel's dev server, queue listener, logs and Vite.
     Open http://127.0.0.1:8000 (or your APP_URL).

  8. For a REAL server deployment, separately configure an HTTPS web server,
     secure APP_ENV=production / APP_DEBUG=false, a dedicated queue worker
     (e.g. Supervisor), cron schedule:run, backups, mail, and permissions.
     Do NOT use `composer run dev` as a production daemon.

  IMPORTANT: AI Assistant actions use queues and may incur external provider
  charges. Keep AI execution flags off until explicitly configured.
NEXT
ok "Initial guided setup complete: $project"
