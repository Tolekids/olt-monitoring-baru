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

## Features
- TBD

## License
Proprietary / Closed Source
