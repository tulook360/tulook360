<?php
// config/database.php

require_once __DIR__ . '/env.php';

class Database
{
    private ?PDO $pdo = null;

    public function getConnection(): PDO
    {
        if ($this->pdo === null) {

            $driver  = env('DB_DRIVER');
            $host    = env('DB_HOST');
            $port    = env('DB_PORT');
            $dbname  = env('DB_NAME');
            $charset = env('DB_CHARSET');

            $user    = env('DB_USER');
            $pass    = env('DB_PASS');

            $dsn = "$driver:host=$host;port=$port;dbname=$dbname;charset=$charset";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                $this->pdo = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Si estamos en local, mostramos el error. En Render (producción), lo ocultamos.
                if (env('APP_ENV') === 'local') {
                    die('Error de conexión: ' . $e->getMessage());
                } else {
                    // Loguear el error internamente y mostrar mensaje genérico
                    error_log($e->getMessage());
                    die('Error crítico: No se pudo establecer la conexión con el servidor de datos.');
                }
            }
        }

        return $this->pdo;
    }
}
