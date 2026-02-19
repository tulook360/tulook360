<?php
// config/env.php
// Carga sencilla de variables desde .env y helper env()

if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Saltar comentarios
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2));

            // Quitar comillas si las hubiera
            $value = trim($value, "\"'");

            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?? $default;
    }
}

// Cargar .env al incluir este archivo
$envPath = dirname(__DIR__) . '/.env';
loadEnv($envPath);
