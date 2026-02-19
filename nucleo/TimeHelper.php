<?php
// nucleo/TimeHelper.php

final class TimeHelper {

    // Zona horaria base (Ecuador)
    private const TZ = 'America/Guayaquil';

    // Retorna fecha y hora actual (YYYY-MM-DD HH:mm:ss)
    public static function now(): string {
        $dt = new DateTime('now', new DateTimeZone(self::TZ));
        return $dt->format('Y-m-d H:i:s');
    }

    // Retorna solo fecha (YYYY-MM-DD)
    public static function today(): string {
        $dt = new DateTime('now', new DateTimeZone(self::TZ));
        return $dt->format('Y-m-d');
    }

    // Suma minutos
    public static function addMinutes(int $min): string {
        $dt = new DateTime('now', new DateTimeZone(self::TZ));
        $dt->modify("+{$min} minutes");
        return $dt->format('Y-m-d H:i:s');
    }

    // Base URL
    public static function baseUrl(): string {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        $protocol = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $dir  = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');

        return "{$protocol}://{$host}{$dir}";
    }
}