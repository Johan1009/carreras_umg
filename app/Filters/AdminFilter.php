<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Restringe una ruta a usuarios con rol "admin". Incluye la comprobación de sesión.
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $respuesta = (new AuthFilter())->before($request, $arguments);
        if ($respuesta !== null) {
            return $respuesta;
        }

        if (session()->get('rol') !== 'admin') {
            return redirect()->to('/consulta')->with('error', 'No tiene permisos para esa sección.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
