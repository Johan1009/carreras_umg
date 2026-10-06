<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CorreoService;
use App\Models\CorreoConfigModel;
use RuntimeException;

class Correo extends BaseController
{
    private const CIFRADOS = ['tls', 'ssl', 'ninguno'];

    public function index()
    {
        return view('admin/correo', [
            'config' => model(CorreoConfigModel::class)->obtener(),
        ]);
    }

    public function guardar()
    {
        $reglas = [
            'host' => [
                'label'  => 'Servidor SMTP',
                'rules'  => 'required|max_length[190]',
                'errors' => ['required' => 'Indique el servidor SMTP (por ejemplo smtp.gmail.com).'],
            ],
            'puerto' => [
                'label'  => 'Puerto',
                'rules'  => 'required|is_natural_no_zero|less_than_equal_to[65535]',
                'errors' => ['required' => 'Indique el puerto (Gmail usa 587 con TLS o 465 con SSL).'],
            ],
            'cifrado' => [
                'label'  => 'Cifrado',
                'rules'  => 'required|in_list[' . implode(',', self::CIFRADOS) . ']',
            ],
            'usuario' => [
                'label'  => 'Usuario SMTP',
                'rules'  => 'permit_empty|max_length[190]',
            ],
            'remitente_email' => [
                'label'  => 'Correo remitente',
                'rules'  => 'required|valid_email|max_length[190]',
                'errors' => [
                    'required'    => 'Indique el correo desde el que se enviarán los mensajes.',
                    'valid_email' => 'El correo remitente no es válido.',
                ],
            ],
            'remitente_nombre' => [
                'label'  => 'Nombre remitente',
                'rules'  => 'permit_empty|max_length[190]',
            ],
        ];

        if (! $this->validate($reglas)) {
            return redirect()->back()->withInput()->with('errores', $this->validator->getErrors());
        }

        $datos = [
            'host'             => trim((string) $this->request->getPost('host')),
            'puerto'           => (int) $this->request->getPost('puerto'),
            'cifrado'          => $this->request->getPost('cifrado'),
            'usuario'          => trim((string) $this->request->getPost('usuario')),
            'remitente_email'  => trim((string) $this->request->getPost('remitente_email')),
            'remitente_nombre' => trim((string) $this->request->getPost('remitente_nombre')),
        ];

        // La contraseña se guarda cifrada y solo se cambia si se escribe una nueva.
        $clave = (string) $this->request->getPost('clave');
        if ($clave !== '') {
            try {
                $datos['clave'] = CorreoService::cifrar($clave);
            } catch (\Throwable $e) {
                log_message('error', 'No se pudo cifrar la clave SMTP: ' . $e->getMessage());

                return redirect()->back()->withInput()->with('error', 'No se pudo proteger la contraseña. Revise la clave de cifrado (encryption.key) en .env.');
            }
        }

        model(CorreoConfigModel::class)->update(1, $datos);

        return redirect()->to('/admin/correo')->with('ok', 'Configuración de correo guardada.');
    }

    public function probar()
    {
        $config = model(CorreoConfigModel::class)->obtener();
        $destino = $config['remitente_email'] ?? '';

        if ($destino === '') {
            return redirect()->to('/admin/correo')->with('error', 'Guarde primero el correo remitente.');
        }

        try {
            (new CorreoService())->enviar(
                $destino,
                'Prueba de configuración de correo',
                '<p>La configuración del servidor de correo funciona correctamente.</p>',
            );
        } catch (RuntimeException $e) {
            return redirect()->to('/admin/correo')->with('error', $e->getMessage());
        }

        return redirect()->to('/admin/correo')->with('ok', 'Correo de prueba enviado a ' . $destino . '.');
    }
}
