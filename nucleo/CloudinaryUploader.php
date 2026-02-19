<?php
// nucleo/CloudinaryUploader.php

require_once __DIR__ . '/../config/env.php';

class CloudinaryUploader {

    // ==========================================================
    // 1. SUBIR IMAGEN
    // ==========================================================
    // CAMBIO CLAVE: $carpeta = null por defecto. 
    // Así, si no se especifica (como en el Registro), usa la del .env
    public static function subirImagen($archivoTemporal, $carpeta = null) {
        
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey    = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');
        
        // Si $carpeta viene vacía, usamos la del .env. Si esa falla, 'TuLook360'
        $folder = $carpeta ?: env('CLOUDINARY_FOLDER', 'TuLook360');

        if (!$cloudName || !$apiKey || !$apiSecret) {
            throw new Exception("Faltan credenciales Cloudinary");
        }

        $timestamp = time();
        
        // Firma
        $params = ['folder' => $folder, 'timestamp' => $timestamp];
        ksort($params);
        $str = "";
        foreach ($params as $k => $v) $str .= "$k=$v&";
        $str = rtrim($str, "&") . $apiSecret;
        $signature = sha1($str);

        // Envío
        $postFields = [
            'file'      => new CURLFile($archivoTemporal),
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'folder'    => $folder,
            'signature' => $signature
        ];

        $ch = curl_init("https://api.cloudinary.com/v1_1/$cloudName/image/upload");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $response = curl_exec($ch);
        if (curl_errno($ch)) throw new Exception(curl_error($ch));
        curl_close($ch);

        $json = json_decode($response, true);
        if (isset($json['error'])) throw new Exception($json['error']['message']);

        return $json['secure_url'] ?? null;
    }

    // ==========================================================
    // 2. ELIMINAR IMAGEN (NUEVO)
    // ==========================================================
    public static function eliminarImagen($urlImagen) {
        if (!$urlImagen) return false;

        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey    = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');

        $publicId = self::extraerPublicId($urlImagen);
        if (!$publicId) return false;

        $timestamp = time();
        
        $params = ['public_id' => $publicId, 'timestamp' => $timestamp];
        ksort($params);
        $str = "";
        foreach ($params as $k => $v) $str .= "$k=$v&";
        $str = rtrim($str, "&") . $apiSecret;
        $signature = sha1($str);

        $postFields = [
            'public_id' => $publicId,
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature
        ];

        $ch = curl_init("https://api.cloudinary.com/v1_1/$cloudName/image/destroy");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response, true);
        
        return isset($json['result']) && $json['result'] === 'ok';
    }

    // Helper privado
    private static function extraerPublicId($url) {
        try {
            $path = parse_url($url, PHP_URL_PATH); 
            $parts = explode('/upload/', $path);
            if (count($parts) < 2) return null;
            
            $rightPart = $parts[1]; 
            // Quitar versión (v12345/)
            $rightPart = preg_replace('/^v\d+\//', '', $rightPart); 
            
            // Quitar extensión
            $lastDot = strrpos($rightPart, '.');
            if ($lastDot !== false) {
                return substr($rightPart, 0, $lastDot);
            }
            return $rightPart;
        } catch (Exception $e) {
            return null;
        }
    }
}