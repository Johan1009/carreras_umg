<?php

namespace App\Filters;

use App\Models\UsuarioModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Exige sesión iniciada. En cada petición vuelve a comprobar en la base de datos
 * que el usuario siga activo y actualiza su rol, así un cambio hecho por un
 * administrador surte efecto de inmediato.
 */
class AuthFilter implements FilterInterface
{
    public const INACTIVIDAD_MAX = 1800; // 30 minutos

    public function before(RequestInterface $request, $arguments = null)
    {
        $sesion = session();
        $usuarioId = (int) $sesion->get('usuario_id');

        if ($usuarioId === 0) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesión.');
        }

        $ultimaActividad = (int) $sesion->get('ultima_actividad');
        if ($ultimaActividad !== 0 && time() - $ultimaActividad > self::INACTIVIDAD_MAX) {
            $sesion->destroy();

            return redirect()->to('/login')->with('error', 'La sesión expiró por inactividad.');
        }

        $usuario = model(UsuarioModel::class)->find($usuarioId);
        if ($usuario === null || (int) $usuario['activo'] !== 1) {
            $sesion->destroy();

            return redirect()->to('/login')->with('error', 'Su cuenta no está disponible.');
        }

        $sesion->set([
            'rol'              => $usuario['rol'],
            'nombre'           => $usuario['nombre'],
            'ultima_actividad' => time(),
        ]);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
