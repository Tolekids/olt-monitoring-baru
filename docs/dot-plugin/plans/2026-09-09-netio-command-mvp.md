# Netio Command MVP Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `dot-plugin:subagent-driven-development` (recommended) or `dot-plugin:executing-plans` to implement this plan task-by-task. Steps use checkbox syntax for tracking.

**Issue:** Build the approved Netio Command MVP for NOC operations.

**Goal:** Deliver a Laravel 11 application that monitors ZTE C300/C320 and MikroTik RouterOS devices, streams syslog and dashboard updates, provides controlled OLT CLI access, and records all privileged operations.

**Architecture:** Use a modular Laravel monolith. Controllers serve web/API requests, domain services own business rules, protocol adapters isolate ZTE and MikroTik implementations, and queue jobs perform device polling. MySQL stores normalized state and history; Redis handles queues, cache, and real-time coordination; SSE serves one-way dashboard/syslog updates and WebSockets serve interactive CLI sessions.

**Tech Stack:** PHP 8.2+, Laravel 11, MySQL 8, Redis, Blade, Tailwind CSS, Alpine.js, Chart.js or ApexCharts, Laravel queue/scheduler, `phpseclib`, SNMP extension/library, RouterOS API adapter, PHPUnit.

## File map

The implementation should follow these boundaries:

- `app/Domain/Devices/`: device models, repositories, services, policies, and protocol adapters.
- `app/Domain/Monitoring/`: normalized metrics, polling jobs, status transitions, and dashboard queries.
- `app/Domain/Syslog/`: UDP listener, parser, persistence, filters, and SSE stream.
- `app/Domain/RemoteCli/`: CLI sessions, transport services, WebSocket events, and audit logging.
- `app/Domain/Access/`: roles, permissions, authorization, and authentication integration.
- `app/Http/Controllers/Api/V1/`: thin HTTP controllers and request validation.
- `app/Http/Controllers/`: web dashboard and streaming endpoints.
- `app/Events/`, `app/Listeners/`, and `app/Broadcasting/`: real-time event delivery.
- `app/Console/Commands/`: polling dispatch and UDP syslog listener commands.
- `database/migrations/`, `database/seeders/`, and `database/factories/`: persistence and deterministic test data.
- `resources/views/`, `resources/js/`, and `resources/css/`: NOC dashboard, device management, syslog, and terminal UI.
- `tests/Unit/`, `tests/Feature/`, and `tests/Integration/`: test coverage by boundary.
- `docs/database/` and `docs/openapi/`: database and API contracts.

## Tasks

### Task 1: Upgrade the application baseline to Laravel 11

**Description:** Upgrade the existing Laravel 9 skeleton to PHP 8.2-compatible Laravel 11 and keep the application booting, migrating, and building assets before feature work begins.

**Skill:** `dot-plugin:backend-development`

**Files:**

- Modify: `composer.json`
- Modify: `composer.lock`
- Modify: `bootstrap/app.php`
- Modify: `app/Providers/*`
- Modify: `routes/*`
- Modify: `phpunit.xml`
- Modify: `.env.example`
- Modify: `docker-compose.yml`
- Modify: `README.md`

- [ ] **Step 1: Record the current baseline**

Run:

```bash
php artisan --version
php artisan test
npm run build
```

Expected: commands complete or expose the exact existing compatibility failures.

- [ ] **Step 2: Update framework and PHP constraints**

Update Composer constraints for Laravel 11-compatible packages and PHP 8.2. Preserve Sanctum unless the authentication design replaces it explicitly.

- [ ] **Step 3: Apply the Laravel 11 application structure**

Move middleware, exception, and route configuration to the Laravel 11 bootstrap conventions where required. Remove obsolete configuration only after the application boots.

- [ ] **Step 4: Add Redis to local infrastructure**

Extend Docker Compose with Redis and document MySQL/Redis environment variables. Do not place production secrets in the repository.

- [ ] **Step 5: Verify the upgraded baseline**

Run:

```bash
composer validate
php artisan about
php artisan migrate:fresh --seed
php artisan test
npm run build
```

Expected: the application boots, migrations complete, tests pass, and assets build successfully.

### Task 2: Define database and API contracts

**Description:** Convert the approved database sketch and endpoint outline into maintainable project contracts before implementation. The contracts must define names, relationships, authorization expectations, pagination, and error responses.

**Skill:** `dot-plugin:design-dbml-database`, then `dot-plugin:design-openapi-contract`

**Files:**

- Create: `docs/database/netio-command.dbml`
- Create: `docs/openapi/openapi.yaml`
- Create: `docs/openapi/paths/`
- Create: `docs/openapi/components/schemas/`

- [ ] **Step 1: Create the DBML schema**

Define users/RBAC references, devices, encrypted credentials, OLT PON ports, ONUs, metrics, traffic samples, syslog events, CLI sessions, command audit logs, and polling runs. Add UUID or Laravel-compatible primary keys consistently, soft deletes where appropriate, foreign keys, and indexes for device/time-series queries.

- [ ] **Step 2: Create the OpenAPI split-file contract**

Define authentication, device management, connection testing, dashboard, metrics, traffic, ONU actions, syslog, CLI sessions, and audit log paths. Include reusable pagination, validation, authorization, and error schemas.

- [ ] **Step 3: Validate both contracts**

Run the available DBML and Redocly/OpenAPI validation commands. Expected: no broken references, duplicate schemas, or invalid relationships.

### Task 3: Implement persistence, access control, and seed data

**Description:** Create migrations, models, factories, policies, and seeders for the approved schema. Add RBAC with least-privilege defaults and encrypted device credentials.

**Skill:** `dot-plugin:backend-development`

**Files:**

- Create: `database/migrations/*_create_devices_table.php`
- Create: `database/migrations/*_create_device_credentials_table.php`
- Create: `database/migrations/*_create_olt_pon_ports_table.php`
- Create: `database/migrations/*_create_onus_table.php`
- Create: `database/migrations/*_create_metric_and_traffic_tables.php`
- Create: `database/migrations/*_create_syslog_events_table.php`
- Create: `database/migrations/*_create_cli_and_audit_tables.php`
- Create: `database/migrations/*_create_device_poll_runs_table.php`
- Create: `app/Models/Device.php`
- Create: `app/Models/DeviceCredential.php`
- Create: `app/Models/OltPonPort.php`
- Create: `app/Models/Onu.php`
- Create: `app/Models/TrafficSample.php`
- Create: `app/Models/SyslogEvent.php`
- Create: `app/Models/CliSession.php`
- Create: `app/Models/CommandAuditLog.php`
- Create: `database/seeders/RolesAndPermissionsSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Modify: `app/Models/User.php`
- Create: `app/Policies/*Policy.php`

- [ ] **Step 1: Add the RBAC dependency and configure roles**

Create permissions for viewing dashboards, managing devices, viewing syslog, opening CLI sessions, executing ONU actions, and viewing audit logs. Seed Admin, NOC, and Teknisi Field roles.

- [ ] **Step 2: Add migrations and indexes**

Use foreign keys and indexes based on the approved DBML. Add unique constraints for device management identity and ONU identifiers where the domain requires them.

- [ ] **Step 3: Add encrypted credential casting**

Credentials must be encrypted at rest and excluded from model serialization, logs, and API resources.

- [ ] **Step 4: Add factories and deterministic seed data**

Create representative ZTE, MikroTik, PON, ONU, metric, syslog, and user records for local development and feature tests.

- [ ] **Step 5: Verify persistence and policies**

Run migrations, seed the database, and test that unauthorized users cannot access protected records or actions.

### Task 4: Build protocol adapters and normalized device services

**Description:** Isolate SNMP, RouterOS API, SSH, and Telnet details behind interfaces. Return normalized DTOs so the rest of the application does not depend on vendor response formats.

**Skill:** `dot-plugin:backend-development`

**Files:**

- Create: `app/Domain/Devices/Contracts/DeviceAdapter.php`
- Create: `app/Domain/Devices/Contracts/OltAdapter.php`
- Create: `app/Domain/Devices/Contracts/RouterAdapter.php`
- Create: `app/Domain/Devices/Adapters/ZteSnmpAdapter.php`
- Create: `app/Domain/Devices/Adapters/ZteCliAdapter.php`
- Create: `app/Domain/Devices/Adapters/MikrotikApiAdapter.php`
- Create: `app/Domain/Devices/Data/*Data.php`
- Create: `app/Domain/Devices/Services/DeviceConnectionService.php`
- Create: `app/Domain/Devices/Services/DeviceStatusService.php`
- Create: `config/network-devices.php`
- Create: `tests/Unit/Domain/Devices/*Test.php`
- Create: `tests/Integration/Domain/Devices/*Test.php`

- [ ] **Step 1: Define adapter contracts and normalized data objects**

Define timeout, authentication, transport, and protocol error behavior. Avoid returning raw vendor arrays from adapters.

- [ ] **Step 2: Implement ZTE SNMP parsing**

Implement the configured OIDs for C300/C320 status, PON ports, ONU identity, ONU status, and RX power. Keep OIDs in configuration or dedicated mapping classes, not scattered through jobs.

- [ ] **Step 3: Implement MikroTik polling**

Implement RouterOS v6/v7 access for router identity, interface traffic, and active client/session statistics. Normalize version-specific response differences.

- [ ] **Step 4: Implement ZTE SSH/Telnet transport**

Support connection setup, prompt handling, timeout, clean close, and protocol errors. Do not allow arbitrary secrets or command output to enter exception messages.

- [ ] **Step 5: Add adapter tests**

Cover valid responses, timeouts, authentication failures, incomplete data, malformed responses, and unsupported values using fixtures/mocks.

### Task 5: Implement polling, status transitions, and dashboard queries

**Description:** Run polling asynchronously, persist normalized results, record poll runs, and expose aggregated dashboard data without making web requests contact devices directly.

**Skill:** `dot-plugin:backend-development`

**Files:**

- Create: `app/Jobs/PollZteOltJob.php`
- Create: `app/Jobs/PollMikrotikJob.php`
- Create: `app/Jobs/PollTrafficInterfacesJob.php`
- Create: `app/Jobs/DiscoverOnusJob.php`
- Create: `app/Domain/Monitoring/Services/MetricPersistenceService.php`
- Create: `app/Domain/Monitoring/Services/DevicePollingService.php`
- Create: `app/Domain/Monitoring/Queries/DashboardSummaryQuery.php`
- Create: `app/Domain/Monitoring/Queries/TrafficQuery.php`
- Modify: `routes/console.php`
- Create: `app/Console/Commands/DispatchDevicePolling.php`
- Create: `tests/Feature/Jobs/*Test.php`
- Create: `tests/Unit/Domain/Monitoring/*Test.php`

- [ ] **Step 1: Implement poll-run lifecycle**

Create a record for started, completed, failed, and timed-out runs. Ensure one device failure does not fail a batch for other devices.

- [ ] **Step 2: Implement normalized persistence**

Upsert device, PON, and ONU state. Append time-series samples with bounded precision and timestamps from the monitoring system.

- [ ] **Step 3: Implement offline transitions**

On timeout or transport failure, update device status and last error without exposing credentials. Restore online state on the next successful poll.

- [ ] **Step 4: Schedule queue dispatch**

Schedule polling at configurable intervals and dispatch jobs to named queues so device polling can be scaled separately from web work.

- [ ] **Step 5: Implement dashboard query services**

Aggregate online/offline devices, ONU states, traffic, recent syslog, and recent poll errors using bounded queries and pagination where applicable.

- [ ] **Step 6: Verify queue behavior**

Run queue tests with fake adapters and verify timeout, retry, status transition, and idempotency behavior.

### Task 6: Implement syslog ingestion and SSE streaming

**Description:** Receive UDP syslog from lab devices, parse and persist events, associate events with known devices, and stream new events to authorized dashboard clients.

**Skill:** `dot-plugin:backend-development`

**Files:**

- Create: `app/Domain/Syslog/Parsers/SyslogParser.php`
- Create: `app/Domain/Syslog/Services/SyslogIngestionService.php`
- Create: `app/Console/Commands/ListenForSyslog.php`
- Create: `app/Events/SyslogEventReceived.php`
- Create: `app/Http/Controllers/SyslogStreamController.php`
- Create: `resources/views/syslog/index.blade.php`
- Create: `resources/js/syslog.js`
- Create: `tests/Unit/Domain/Syslog/*Test.php`
- Create: `tests/Feature/Syslog/*Test.php`

- [ ] **Step 1: Implement parser and device matching**

Support the required syslog fields, preserve the original message, map source IP to a device when possible, and classify severity safely.

- [ ] **Step 2: Implement UDP listener command**

Use a configurable bind address and port. Handle malformed packets and listener errors without terminating the process unexpectedly.

- [ ] **Step 3: Persist and publish events**

Persist the event before publishing it. Apply authorization to the SSE endpoint and avoid broadcasting credentials or sensitive command output.

- [ ] **Step 4: Build syslog UI**

Add filters, severity styling, connection status, reconnect behavior, and paginated history.

- [ ] **Step 5: Verify with fixture packets and lab devices**

Test valid packets, malformed packets, unknown source IPs, filtering, reconnects, and real UDP delivery.

### Task 7: Implement remote CLI, WebSocket transport, and audit logging

**Description:** Provide controlled browser terminal sessions to ZTE OLTs and record every command with user/device/session metadata.

**Skill:** `dot-plugin:backend-development`

**Files:**

- Create: `app/Domain/RemoteCli/Services/CliSessionService.php`
- Create: `app/Domain/RemoteCli/Services/CommandAuditService.php`
- Create: `app/Domain/RemoteCli/Rules/AllowedCommandRule.php`
- Create: `app/Events/CliOutputReceived.php`
- Create: `app/Events/CliSessionClosed.php`
- Create: `app/Http/Controllers/Api/V1/CliSessionController.php`
- Create: `app/WebSockets/*`
- Create: `resources/views/cli/index.blade.php`
- Create: `resources/js/cli-terminal.js`
- Create: `tests/Unit/Domain/RemoteCli/*Test.php`
- Create: `tests/Feature/RemoteCli/*Test.php`

- [ ] **Step 1: Implement session lifecycle**

Create, authenticate, track, close, and expire CLI sessions. Enforce one explicit authorization check before opening a session.

- [ ] **Step 2: Implement command validation and audit**

Record command text, user, device, timestamps, success, and a bounded output excerpt. Redact known sensitive patterns and never persist passwords.

- [ ] **Step 3: Implement WebSocket bridge**

Forward terminal input/output with connection limits, idle timeout, clean close behavior, and reconnect-safe session status.

- [ ] **Step 4: Build terminal UI**

Use xterm.js, display connection state, show close/error status, and prevent command submission after the session closes.

- [ ] **Step 5: Verify safety controls**

Test denied roles, invalid devices, timeout, disconnect, forbidden commands, audit completeness, and lab SSH/Telnet sessions.

### Task 8: Implement API, dashboard UI, and device management screens

**Description:** Expose the approved endpoints through thin controllers and build the Blade-based NOC interface over the query services.

**Skill:** `dot-plugin:backend-development`

**Files:**

- Create: `app/Http/Controllers/Api/V1/AuthController.php`
- Create: `app/Http/Controllers/Api/V1/DeviceController.php`
- Create: `app/Http/Controllers/Api/V1/DashboardController.php`
- Create: `app/Http/Controllers/Api/V1/SyslogController.php`
- Create: `app/Http/Requests/Api/V1/*Request.php`
- Create: `app/Http/Resources/Api/V1/*Resource.php`
- Modify: `routes/api.php`
- Modify: `routes/web.php`
- Create: `resources/views/layouts/app.blade.php`
- Create: `resources/views/dashboard/index.blade.php`
- Create: `resources/views/devices/index.blade.php`
- Create: `resources/views/devices/form.blade.php`
- Create: `resources/js/dashboard.js`
- Modify: `resources/css/app.css`
- Modify: `resources/js/app.js`
- Create: `tests/Feature/Api/V1/*Test.php`

- [ ] **Step 1: Implement request validation and resources**

Validate IP addresses, protocols, ports, device types, pagination, date ranges, and action parameters. Resources must never expose encrypted credentials.

- [ ] **Step 2: Implement authenticated routes and policies**

Apply Sanctum/session authentication as appropriate for the Blade application and enforce policies on every device and operational action.

- [ ] **Step 3: Build the dashboard**

Render summary cards, traffic charts, device state, ONU state, polling errors, and recent syslog. Load the initial state from query endpoints and receive updates through SSE.

- [ ] **Step 4: Build device management**

Add list, create, edit, enable/disable polling, connection test, and status display. Show errors without exposing sensitive connection details.

- [ ] **Step 5: Add responsive and failure states**

Cover empty data, offline device, loading, unauthorized action, SSE reconnect, and API error states.

- [ ] **Step 6: Verify the API contract**

Run API feature tests and validate responses against the OpenAPI contract.

### Task 9: Add observability, retention, and operational documentation

**Description:** Make the system operable in a lab and future production environment by documenting workers, listeners, environment variables, failure behavior, and time-series retention.

**Skill:** `dot-plugin:backend-development`

**Files:**

- Modify: `.env.example`
- Modify: `config/queue.php`
- Modify: `config/logging.php`
- Create: `config/network-devices.php`
- Create: `config/monitoring.php`
- Create: `app/Console/Commands/PurgeMonitoringHistory.php`
- Modify: `README.md`
- Create: `docs/operations/netio-command-mvp.md`

- [ ] **Step 1: Document required environment variables**

Document database, Redis, queue, syslog bind, WebSocket, polling interval, timeout, retry, and retention settings.

- [ ] **Step 2: Add structured operational logs**

Include device ID, job type, poll run ID, and safe error classification. Exclude passwords, communities, tokens, and command secrets.

- [ ] **Step 3: Add retention command**

Delete only expired metric and traffic records according to explicit configuration. Keep audit and syslog retention rules separate.

- [ ] **Step 4: Document run commands**

Document the web server, queue worker, scheduler, syslog listener, WebSocket server, database, Redis, and frontend asset processes.

### Task 10: Run end-to-end verification and prepare handoff

**Description:** Verify the complete MVP against the approved design and available lab hardware. Do not claim completion until automated and manual checks produce evidence.

**Skill:** `dot-plugin:verification-before-completion`

**Files:**

- Modify: `docs/operations/netio-command-mvp.md` with verified commands/results if needed.
- Modify: `README.md` with final setup corrections if needed.

- [ ] **Step 1: Run static and dependency checks**

Run:

```bash
composer validate
php artisan route:list
php artisan config:clear
npm run build
```

- [ ] **Step 2: Run automated tests**

Run:

```bash
php artisan test
```

Expected: all unit, feature, and integration tests pass.

- [ ] **Step 3: Run infrastructure checks**

Start MySQL and Redis, migrate and seed a clean database, run queue/scheduler/listener processes, and verify no credential leakage in logs.

- [ ] **Step 4: Run lab-device checks**

Verify ZTE SNMP, ZTE SSH/Telnet, MikroTik API, traffic polling, ONU status, UDP syslog, SSE updates, WebSocket terminal interaction, and audit records.

- [ ] **Step 5: Review requirements traceability**

Map each MVP requirement to an implemented module and test evidence. Record any device-specific limitations or deferred behavior.

## Execution notes

- Do not begin feature implementation until this plan is explicitly approved.
- Execute tasks in order because the upgrade, contracts, and persistence establish the interfaces used by later tasks.
- Preserve unrelated working-tree changes.
- Use real lab devices only for integration verification; use mocks for deterministic automated tests.
- Do not commit secrets, device IPs, credentials, or raw sensitive command output.
