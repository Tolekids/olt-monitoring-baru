# OLT Monitoring Baru

A Laravel-based application for OLT Monitoring.

## Prerequisites

- [PHP](https://www.php.net/) 8.0+
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) & NPM
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (for MySQL Database)

## Setup Instructions

Follow these steps to set up the project locally:

### 1. Install Dependencies

Install the PHP and Node.js dependencies:

```bash
composer update
npm install
```

> **Note**: If you encounter issues with `composer install` due to security advisories, use `composer update` as configured in `composer.json`.

### 2. Configure Environment variables

Copy the example environment file and create your `.env`:

```bash
cp .env.example .env
```

### 3. Start the Database (Docker)

This project uses Docker to easily run the MySQL database. Start the database container in the background:

```bash
docker compose up -d
```

### 4. Generate App Key & Migrate Database

Generate the Laravel application key and run the database migrations:

```bash
php artisan key:generate
php artisan migrate
```

### 5. Build Assets & Start Development Server

Compile the frontend assets:

```bash
npm run build
```
*(Or use `npm run dev` during active development)*

Finally, start the local development server:

```bash
php artisan serve
```

The application will be accessible at `http://127.0.0.1:8000`.

## MVP Features

- Laravel 11 baseline with MySQL and Redis infrastructure.
- RBAC roles: Admin, NOC, and Teknisi Field.
- Device management for ZTE OLT and MikroTik RouterOS.
- Encrypted device credentials.
- Asynchronous device polling through Laravel queues.
- Dashboard and traffic summary API.
- UDP syslog ingestion and SSE stream.
- ZTE SSH/Telnet CLI sessions with read-only command allowlist and audit logs.

## Development processes

Start the infrastructure:

```bash
docker compose up -d
```

Run the application, queue worker, scheduler, syslog listener, and Reverb server in separate processes:

```bash
php artisan serve
php artisan queue:work --queue=device-polling
php artisan schedule:work
php artisan syslog:listen
php artisan reverb:start
```

The local seeded administrator is `admin@netio.local` with password `password`. Change this credential before using a shared environment.

## API quick start

Authenticate and use the returned Sanctum bearer token:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@netio.local","password":"password"}'
```

The API base path is `/api/v1`. Device credentials are encrypted before persistence and are never returned by API resources.

## License
Proprietary / Closed Source
