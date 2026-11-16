# Ubuntu Supervisor Guide — Laravel AI Queue Workers

**Application:** Case Management Application (Laravel 13 / Livewire 4)  
**Audience:** System administrators, developers and deployment operators  
**Purpose:** Automatically process AI jobs on Ubuntu without having to run `php artisan queue:work ... --once` every time.

> This is an Ubuntu/Debian deployment guide, not a change to the AI case-handling process. Follow your organisation's deployment, security and backup practices. Replace every example path and username with values from your server.

## 1. Why a queue worker is needed

With `QUEUE_CONNECTION=database`, clicking **Analyze Case** or **Re-analyze Case** records a pending analysis and places a `GenerateCaseAssistantAnalysis` job in Laravel's database queue. A worker must take the job, call the configured AI provider, and save the result.

The **Recommend Solution** button also dispatches a queued AI job, as do some optional engineer matching and approved actions. If no worker is listening, these jobs remain pending and the interface may look stuck.

This command processes **one job and exits**:

```bash
php artisan queue:work database --queue=default --once -v
```

For a running Ubuntu server, Supervisor keeps a worker alive, including after a restart or unexpected process exit. It does **not** eliminate the need for a working AI provider, valid API credentials, database connectivity or correct permissions.

## 2. Before you configure Supervisor

From the Laravel application's actual directory, collect:

```bash
pwd
command -v php
whoami
php -v
```

Example output (**do not copy these paths blindly**):

```text
/var/www/Case-Management-Application
/usr/bin/php
deploy
```

Choose the Unix user that should run the queue worker. Prefer the same **non-root deployment/application user** your server already uses, provided it can read `.env`, connect to the database, and write to `storage` and `bootstrap/cache`. Do not change ownership or chmod recursively without first understanding your server's permission model.

Check that Laravel is configured for the database queue in `.env`:

```dotenv
QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=180
```

**Project-specific timeout warning:** In this repository, `config/queue.php` reads `DB_QUEUE_RETRY_AFTER` and otherwise defaults to **90 seconds**, but AI jobs can have a **120-second timeout**. Set `DB_QUEUE_RETRY_AFTER=180` (or another value safely greater than the worker timeout) to avoid an uncompleted job becoming eligible for a second worker while the first is still processing it.

If configuration is cached, apply changes from the project directory:

```bash
php artisan config:clear
php artisan config:show queue.connections.database
```

Confirm the effective `retry_after` is longer than 120 seconds. Do not publish `.env` credentials or full application configuration output.

Ensure required queue migrations have been applied through your normal deployment process:

```bash
php artisan migrate:status
```

If migrations are outstanding, review them and apply them using your normal deployment procedure. Do not run production migrations blindly.

## 3. Install and enable Supervisor on Ubuntu

```bash
sudo apt update
sudo apt install supervisor -y
sudo systemctl enable --now supervisor
sudo systemctl status supervisor
```

The service should report `active (running)`.

## 4. Create the worker configuration

Create the configuration file:

```bash
sudo nano /etc/supervisor/conf.d/case-management-worker.conf
```

Use the following example, **replacing `directory`, the PHP path, and `user`** with the output and chosen account from step 2:

```ini
[program:case-management-worker]
process_name=%(program_name)s_%(process_num)02d
directory=/var/www/Case-Management-Application
command=/usr/bin/php artisan queue:work database --queue=default --sleep=3 --tries=3 --timeout=120 --memory=256 --max-time=3600 -v

user=deploy
numprocs=1
autostart=true
autorestart=true
startsecs=5
stopasgroup=true
killasgroup=true
stopwaitsecs=180

redirect_stderr=true
stdout_logfile=/var/log/case-management-worker.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
```

**What the key options mean:**

| Setting | Purpose |
| --- | --- |
| `directory` | Absolute path containing Laravel's `artisan` file |
| `command` | Absolute PHP binary, running the **database** queue on `default` |
| `user` | Dedicated, non-root Linux account with application access |
| `numprocs=1` | Start with one worker; tune capacity only after testing |
| `autostart=true` / `autorestart=true` | Start on boot and restart exited processes |
| `--sleep=3` | Pause briefly between checks when no job is available |
| `--tries=3` | Allow job retries subject to job-specific limits |
| `--timeout=120` | Worker timeout; **must be lower than** the database queue's `retry_after` |
| `--max-time=3600` | Periodically recycle workers to pick up a fresh process |
| `stopwaitsecs=180` | Give an in-progress job time to stop gracefully |
| `stdout_logfile` | Location of worker output; the Supervisor daemon must be able to create/write it |

Make sure the `/var/log` location exists and Supervisor can write the log. The AI provider may take time to respond, so the progress bar is indeterminate rather than a reliable percentage.

## 5. Load the configuration and start the worker

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

If the worker is stopped, start it:

```bash
sudo supervisorctl start "case-management-worker:*"
sudo supervisorctl status
```

Expected result (the exact process name may differ):

```text
case-management-worker:case-management-worker_00   RUNNING
```

Once the worker is running, new queued analyses should be processed without manually running `--once`.

## 6. Verify end-to-end processing

1. Open a test case in the application.
2. Click **Analyze Case** or **Re-analyze Case**.
3. Confirm the interface moves from **Queued** to **Processing** and eventually **Completed** (or **Failed**, which requires investigation).
4. Observe the worker output:

   ```bash
   sudo tail -f /var/log/case-management-worker.log
   ```

5. Review failed Laravel jobs if necessary:

   ```bash
   php artisan queue:failed
   ```

6. Confirm that **Recommend Solution** also completes after its explicit credit-usage consent. Provider calls can incur charges.

A worker in the `RUNNING` state is necessary, but not sufficient: successful processing also depends on the selected queue name, database, service credentials and network connectivity.

## 7. Troubleshooting: `ERROR (spawn error)`

For example:

```text
sudo supervisorctl start "case-management-worker:*"
case-management-worker:case-management-worker_00: ERROR (spawn error)
```

This means Supervisor could not start the process successfully. **Do not reinstall Supervisor or recursively change file ownership as a first reaction.** Inspect the error first.

### Step A — Read the Supervisor daemon log

```bash
sudo tail -n 80 /var/log/supervisor/supervisord.log
sudo supervisorctl status
```

On some installations the daemon log path differs; check the Supervisor service's configuration and journal if needed:

```bash
sudo journalctl -u supervisor -n 80 --no-pager
```

### Step B — Check the worker's output

```bash
sudo tail -n 80 /var/log/case-management-worker.log
```

If the worker log does not exist, the process may have failed before it could start, or the configured path may be wrong.

### Step C — Check the configuration values

```bash
sudo cat /etc/supervisor/conf.d/case-management-worker.conf
```

Confirm:

- `directory` really points to the folder that contains `artisan`.
- `command` uses the installed, executable PHP binary (`command -v php`).
- `user` is an existing Ubuntu account with access to the project.
- PHP can read the application's `.env` and dependencies.
- Laravel can write to `storage` and `bootstrap/cache`.
- The log destination is writable by the Supervisor service.

**Privacy:** Redact any credentials or secrets before sharing configuration or error output with anyone.

### Step D — Run the command as the worker user

If the configuration says `user=deploy`, test under that identity. Replace the sample username, paths and working directory:

```bash
cd /var/www/Case-Management-Application
sudo -u deploy /usr/bin/php artisan queue:work database --queue=default --once -v
```

This processes at most one job and exits. It may also make a real, chargeable provider call if an AI job is waiting. Use a test environment or ensure that is acceptable.

If PHP reports a missing file, permission error, database problem, or missing dependency, fix that underlying issue before restarting Supervisor.

**Common causes:**

| Error or symptom | Likely cause | First check |
| --- | --- | --- |
| `can't find command`, `ENOENT` | PHP path invalid | `command -v php` |
| `can't chdir` | Project directory invalid or inaccessible | `pwd` and `ls -ld /path/to/project` |
| `unknown user`, `can't setuid` | Wrong `user=` | `id deploy` (use your actual account) |
| `Permission denied` | Inaccessible files or log path | File/directory ownership and worker permissions |
| `exited too quickly`, `BACKOFF`, `FATAL` | Worker starts but fails on boot | Laravel log, daemon log, worker log |
| Worker is `RUNNING` but analysis stays `pending` | Wrong queue/DB, no usable worker, or stalled jobs | Effective `QUEUE_CONNECTION`, `DB_QUEUE`, Supervisor command |
| Jobs repeat or overlap | `retry_after` shorter than job timeout, or excess workers | `DB_QUEUE_RETRY_AFTER=180` and worker timeout |
| Jobs fail after processing | Provider/API, job exception, network or permission issue | `storage/logs/laravel.log`, `php artisan queue:failed` |

### Step E — Reload after fixing configuration

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart "case-management-worker:*"
sudo supervisorctl status
```

If the application is on a different path, **edit the configuration first**; Supervisor does not automatically discover Laravel.

## 8. Maintenance and deployments

After deploying new code, request a graceful Laravel worker restart from the project directory:

```bash
php artisan queue:restart
```

Supervisor should bring the process back. If you change the Supervisor configuration itself, use `reread` and `update` before restarting the program.

Useful commands:

| Task | Command |
| --- | --- |
| Check worker | `sudo supervisorctl status` |
| Start worker | `sudo supervisorctl start "case-management-worker:*"` |
| Stop worker | `sudo supervisorctl stop "case-management-worker:*"` |
| Restart worker | `sudo supervisorctl restart "case-management-worker:*"` |
| Reload configuration | `sudo supervisorctl reread && sudo supervisorctl update` |
| Read worker log | `sudo tail -f /var/log/case-management-worker.log` |
| Read daemon log | `sudo tail -n 80 /var/log/supervisor/supervisord.log` |
| Read failed Laravel jobs | `php artisan queue:failed` |
| Graceful restart after deploy | `php artisan queue:restart` |

### Development versus production

The application's `composer run dev` script starts a queue listener alongside the development server, log viewer and Vite. You generally **do not need both** this development listener and a Supervisor worker against the same queue for ordinary local development.

For production, keep the web server separate from a supervised queue worker. Configure monitoring, backup, job failure alerting, provider usage limits and log retention according to your environment.

**Security:** Never run the worker as root merely to work around permissions. Do not paste API keys, customer case data or credentials into tickets or public issues.

## 9. References

- [Laravel 13 — Queues and Supervisor configuration](https://laravel.com/docs/13.x/queues#supervisor-configuration)
- [Supervisor documentation](https://supervisord.org/)
- [AI Assistance User and Management Guide](AI_ASSISTANCE_USER_AND_MANAGEMENT_GUIDE.md)
- [AI Solution Recommendations and Progress UI](AI_SOLUTION_RECOMMENDATIONS_AND_PROGRESS_UI.md)

**Document status:** Operational guide for the current feature branch. Validate paths, users, queue settings and permissions on your actual Ubuntu server before applying production changes.
