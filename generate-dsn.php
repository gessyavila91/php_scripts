<?php

function loadEnv($filePath)
{
    if (!file_exists($filePath)) {
        throw new Exception("No se encontró el archivo {$filePath}");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value, "\"'");
        }
    }

    return $env;
}

function generateDsn(array $env): string
{
    $driver = strtolower($env['DB_CONNECTION'] ?? 'mysql');
    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $port = $env['DB_PORT'] ?? '';
    $database = $env['DB_DATABASE'] ?? '';
    $username = $env['DB_USERNAME'] ?? '';
    $password = $env['DB_PASSWORD'] ?? '';

    if (!driverIsSupported($driver)) {
        throw new Exception("❌ Driver {$driver} no soportado.");
    }

    return match ($driver) {
        'mysql' => "mysql://$username:$password@$host:" . ($port ?: '3306') . "/$database",
        'pgsql', 'postgres' => "pgsql://$username:$password@$host:" . ($port ?: '5432') . "/$database",
        'sqlsrv', 'mssql' => "sqlsrv:Server=$host" . ($port ? ",$port" : "") . ";Database=$database;UID=$username;PWD=$password",
        'sqlite' => "sqlite:$database", // DB_DATABASE should be full path or ':memory:'
        'mongodb' => "mongodb://$username:$password@$host:" . ($port ?: '27017') . "/$database"
    };
}

function driverIsSupported($driver)
{
    $supportedDrivers = ['mysql', 'pgsql', 'postgres', 'sqlsrv', 'mssql', 'sqlite', 'mongodb'];
    return in_array($driver, $supportedDrivers);
}


try {
    $envPath = $argv[1] ?? '.env';
    $env = loadEnv($envPath);
    $dsn = generateDsn($env);
    echo "🔗 DSN generado:\n$dsn\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
