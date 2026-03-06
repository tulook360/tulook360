<?php
// nucleo/BrevoMailer.php

class BrevoMailer {
    public static function enviarRecuperacion($destinatarioEmail, $destinatarioNombre, $codigoSeguridad) {
        $apiKey = getenv('BREVO_API_KEY') ?: $_ENV['BREVO_API_KEY'] ?? '';
        $remitenteEmail = getenv('BREVO_SENDER_EMAIL') ?: $_ENV['BREVO_SENDER_EMAIL'] ?? 'soporte@tulook360.com';
        $remitenteNombre = getenv('BREVO_SENDER_NAME') ?: $_ENV['BREVO_SENDER_NAME'] ?? 'TuLook360 Soporte';

        if (empty($apiKey)) return false;

        $url = 'https://api.brevo.com/v3/smtp/email';
        
        $anioActual = date('Y');

        // Diseño de correo PREMIUM basado en Tablas (Estándar para todos los clientes de correo)
        $htmlContent = "
        <!DOCTYPE html>
        <html lang='es'>
        <body style='margin: 0; padding: 0; background-color: #f4f6f9; font-family: Helvetica, Arial, sans-serif;'>
            <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #f4f6f9; padding: 40px 20px;'>
                <tr>
                    <td align='center'>
                        <table width='100%' border='0' cellspacing='0' cellpadding='0' style='max-width: 600px; background-color: #ffffff; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden;'>
                            
                            <tr>
                                <td align='center' style='padding: 40px 30px 20px;'>
                                    <h1 style='color: #1e272e; font-size: 34px; margin: 0; font-weight: 800; letter-spacing: -1px;'>TuLook<span style='color: #ff3366;'>360</span></h1>
                                </td>
                            </tr>
                            
                            <tr>
                                <td align='center' style='padding: 0 40px 20px;'>
                                    <h2 style='color: #2d3436; font-size: 22px; margin: 0 0 15px; font-weight: 600;'>Recuperación de Acceso</h2>
                                    <p style='color: #636e72; font-size: 16px; line-height: 1.6; margin: 0;'>
                                        Hola <strong>{$destinatarioNombre}</strong>,<br><br>
                                        Hemos recibido una solicitud para restablecer tu contraseña. Copia y pega el siguiente código de seguridad en la aplicación para continuar:
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td align='center' style='padding: 15px 40px 30px;'>
                                    <div style='background-color: #fff0f3; border: 1px solid #ffccd5; color: #ff3366; padding: 20px 30px; border-radius: 12px; font-weight: 700; font-size: 42px; letter-spacing: 12px; display: inline-block;'>
                                        {$codigoSeguridad}
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td align='center' style='padding: 0 40px 40px;'>
                                    <p style='color: #b2bec3; font-size: 14px; line-height: 1.5; margin: 0; padding-top: 25px; border-top: 1px solid #f1f2f6;'>
                                        Este código expirará en <strong>1 hora</strong> por tu seguridad.<br>
                                        Si no has solicitado este cambio, por favor ignora este correo. Tu cuenta está segura.
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <td align='center' style='background-color: #f8f9fa; padding: 25px 40px; border-top: 1px solid #eeeeee;'>
                                    <p style='color: #a4b0be; font-size: 12px; line-height: 1.6; margin: 0;'>
                                        &copy; {$anioActual} TuLook360. Todos los derechos reservados.<br>
                                        Este es un mensaje generado automáticamente, por favor no lo respondas.
                                    </p>
                                </td>
                            </tr>
                            
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";

        $data = [
            "sender" => ["name" => $remitenteNombre, "email" => $remitenteEmail],
            "to" => [["email" => $destinatarioEmail, "name" => $destinatarioNombre]],
            "subject" => "Tu código de seguridad es " . $codigoSeguridad . " - TuLook360",
            "htmlContent" => $htmlContent
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $apiKey,
            'content-type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode == 201 || $httpCode == 200);
    }
}