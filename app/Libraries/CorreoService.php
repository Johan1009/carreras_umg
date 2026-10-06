<?php

namespace App\Libraries;

use App\Models\CorreoConfigModel;
use RuntimeException;

/**
 * Envía correos usando la configuración SMTP guardada en la base de datos
 * (panel de administración), en lugar de Config\Email.
 */
class CorreoService
{
    public static function cifrar(string $texto): string
    {
        return base64_encode(service('encrypter')->encrypt($texto));
    }

    public static function descifrar(string $cifrado): string
    {
        return service('encrypter')->decrypt(base64_decode($cifrado));
    }

    /**
     * @param string[] $rutasAdjuntos Rutas absolutas de los archivos a adjuntar
     *
     * @throws RuntimeException si falta la configuración o el envío falla
     */
    public function enviar(string $destino, string $asunto, string $cuerpoHtml, array $rutasAdjuntos = []): void
    {
        $config = model(CorreoConfigModel::class)->obtener();
        if (empty($config['host']) || empty($config['remitente_email'])) {
            throw new RuntimeException('Falta configurar el servidor de correo (SMTP).');
        }

        $email = \Config\Services::email($this->parametrosSmtp($config), false);
        $email->setFrom($config['remitente_email'], $config['remitente_nombre'] ?: 'Secretaría');
        $email->setTo($destino);
        $email->setSubject($asunto);
        $email->setMessage($cuerpoHtml);

        foreach ($rutasAdjuntos as $ruta) {
            $email->attach($ruta);
        }

        if (! $email->send(false)) {
            log_message('error', 'Fallo al enviar correo: ' . $email->printDebugger(['headers']));
            $email->clear(true);

            throw new RuntimeException('No se pudo enviar el correo. Revise la configuración SMTP.');
        }

        $email->clear(true);
    }

    private function parametrosSmtp(array $config): array
    {
        $clave = '';
        if (! empty($config['clave'])) {
            $clave = self::descifrar($config['clave']);
        }

        return [
            'protocol'    => 'smtp',
            'SMTPHost'    => $config['host'],
            'SMTPPort'    => (int) $config['puerto'],
            'SMTPCrypto'  => $config['cifrado'] === 'ninguno' ? '' : $config['cifrado'],
            'SMTPUser'    => (string) $config['usuario'],
            'SMTPPass'    => $clave,
            'SMTPTimeout' => 20,
            'mailType'    => 'html',
            'charset'     => 'UTF-8',
            'wordWrap'    => true,
            'newline'     => "\r\n",
            'CRLF'        => "\r\n",
        ];
    }
}
