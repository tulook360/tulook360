<?php
class Crypto {

    private static function clave(): string {
        // CORRECCIÓN: Usamos $_ENV directamente o el helper si está disponible.
        // Esto asegura que si 'env.php' cargó las variables, las encontremos sí o sí.
        $key = $_ENV['APP_CRYPTO_KEY'] ?? getenv('APP_CRYPTO_KEY') ?? ''; 
        
        if (strlen($key) < 32) {
            // Para desarrollo local si olvidas la clave
            return substr(hash('sha256', 'clave_local_temporal'), 0, 32);
        }
        return substr($key, 0, 32);
    }
    
    private static function b64urlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64urlDecode(string $data): string {
        $pad = 4 - (strlen($data) % 4);
        if ($pad < 4) $data .= str_repeat('=', $pad);
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function encriptar(array $payload): string {
        $key = self::clave();
        $iv  = random_bytes(16);
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $cipher = openssl_encrypt(
            $json, 'AES-256-CBC',
            $key, OPENSSL_RAW_DATA, $iv
        );

        $mac = hash_hmac('sha256', $iv . $cipher, $key, true);

        return self::b64urlEncode($mac . $iv . $cipher);
    }

    public static function desencriptar(string $token): ?array {
        $key = self::clave();

        $raw = self::b64urlDecode($token);
        if (!$raw || strlen($raw) < 48) return null;

        $mac = substr($raw, 0, 32);
        $iv  = substr($raw, 32, 16);
        $cipher = substr($raw, 48);

        $calcMac = hash_hmac('sha256', $iv . $cipher, $key, true);

        if (!hash_equals($mac, $calcMac)) return null;

        $json = openssl_decrypt(
            $cipher, 'AES-256-CBC',
            $key, OPENSSL_RAW_DATA, $iv
        );

        return $json ? json_decode($json, true) : null;
    }
}
