<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Auth extends BaseController
{
    private const MAX_INTENTOS = 5;
    private const BLOQUEO      = 300; // segundos

    public function login()
    {
        if (session()->get('usuario_id')) {
            return redirect()->to('/consulta');
        }

        return view('auth/login');
    }

    public function intentar()
    {
        $claveCache = 'login_intentos_' . md5((string) $this->request->getIPAddress());
        $estado     = cache()->get($claveCache) ?? ['fallos' => 0, 'hasta' => 0];

        if ($estado['hasta'] > time()) {
            return redirect()->to('/login')->with('error', 'Demasiados intentos fallidos. Espere unos minutos.');
        }

        $usuario = trim((string) $this->request->getPost('usuario'));
        $clave   = (string) $this->request->getPost('clave');

        $fila = $usuario === '' ? null : model(UsuarioModel::class)->where('usuario', $usuario)->first();

        if ($fila === null || (int) $fila['activo'] !== 1 || ! password_verify($clave, $fila['password'])) {
            $estado['fallos']++;
            if ($estado['fallos'] >= self::MAX_INTENTOS) {
                $estado = ['fallos' => 0, 'hasta' => time() + self::BLOQUEO];
            }
            cache()->save($claveCache, $estado, self::BLOQUEO * 2);

            // No se devuelve la contraseña ni se usa withInput(), para no reenviarla.
            return redirect()->to('/login')
                ->with('usuario_previo', $usuario)
                ->with('error', 'Usuario o contraseña incorrectos.');
        }

        cache()->delete($claveCache);

        session()->regenerate(true);
        session()->set([
            'usuario_id'       => (int) $fila['id'],
            'nombre'           => $fila['nombre'],
            'rol'              => $fila['rol'],
            'ultima_actividad' => time(),
        ]);

        return redirect()->to('/consulta');
    }

    public function salir()
    {
        session()->destroy();

        return redirect()->to('/login')->with('ok', 'Sesión cerrada.');
    }
}
