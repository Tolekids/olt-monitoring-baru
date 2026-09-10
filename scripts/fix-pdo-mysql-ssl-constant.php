<?php

declare(strict_types=1);

$frameworkDatabaseConfig = dirname(__DIR__).'/vendor/laravel/framework/config/database.php';

if (PHP_VERSION_ID < 80500 || ! is_file($frameworkDatabaseConfig)) {
    exit(0);
}

$contents = file_get_contents($frameworkDatabaseConfig);

if ($contents === false || ! str_contains($contents, 'PDO::MYSQL_ATTR_SSL_CA')) {
    exit(0);
}

$updatedContents = str_replace(
    'PDO::MYSQL_ATTR_SSL_CA',
    'Pdo\\Mysql::ATTR_SSL_CA',
    $contents,
);

if ($updatedContents !== $contents) {
    file_put_contents($frameworkDatabaseConfig, $updatedContents);
}
