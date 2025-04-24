<?php

function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception("No se encontró el archivo .env en: $filePath");
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

try {
    $envPath = $argv[1] ?? '.env';

    $env = loadEnv($envPath);

    $driver = strtolower($env['DB_CONNECTION'] ?? 'mysql');
    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $port = $env['DB_PORT'] ?? ($driver === 'mysql' ? '3306' : '5432');
    $database = $env['DB_DATABASE'] ?? '';
    $username = $env['DB_USERNAME'] ?? '';
    $password = $env['DB_PASSWORD'] ?? '';

    $dsn = match ($driver) {
        'mysql' => "mysql://$username:$password@$host:$port/$database",
        'pgsql' => "pgsql://$username:$password@$host:$port/$database",
        default => throw new Exception("Driver '$driver' no soportado."),
    };

    echo $dsn . PHP_EOL;

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
