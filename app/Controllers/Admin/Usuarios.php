<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Usuarios extends BaseController
{
    public function index()
    {
        return view('admin/usuarios/index', [
            'usuarios' => model(UsuarioModel::class)
                ->select('id, nombre, usuario, rol, activo')
                ->orderBy('nombre', 'ASC')
                ->findAll(),
        ]);
    }

    public function nuevo()
    {
        return view('admin/usuarios/form', ['usuario' => null, 'roles' => UsuarioModel::ROLES]);
    }

    public function crear()
    {
        $reglas = $this->reglasUsuario(null, true);
        if (! $this->validate($reglas)) {
            return redirect()->back()->withInput()->with('errores', $this->validator->getErrors());
        }

        model(UsuarioModel::class)->insert([
            'nombre'   => trim((string) $this->request->getPost('nombre')),
            'usuario'  => trim((string) $this->request->getPost('usuario')),
            'password' => password_hash((string) $this->request->getPost('clave'), PASSWORD_DEFAULT),
            'rol'      => $this->request->getPost('rol'),
            'activo'   => 1,
        ]);

        return redirect()->to('/admin/usuarios')->with('ok', 'Usuario creado.');
    }

    public function editar(int $id)
    {
        return view('admin/usuarios/form', [
            'usuario' => $this->usuarioOFallar($id),
            'roles'   => UsuarioModel::ROLES,
        ]);
    }

    public function actualizar(int $id)
    {
        $this->usuarioOFallar($id);
        $esActual = $id === (int) session()->get('usuario_id');

        if (! $this->validate($this->reglasUsuario($id, false))) {
            return redirect()->back()->withInput()->with('errores', $this->validator->getErrors());
        }

        $datos = [
            'nombre'  => trim((string) $this->request->getPost('nombre')),
            'usuario' => trim((string) $this->request->getPost('usuario')),
        ];

        $clave = (string) $this->request->getPost('clave');
        if ($clave !== '') {
            $datos['password'] = password_hash($clave, PASSWORD_DEFAULT);
        }

        // Nadie puede quitarse a sí mismo el rol de administrador ni desactivarse.
        if (! $esActual) {
            $datos['rol']    = $this->request->getPost('rol');
            $datos['activo'] = $this->request->getPost('activo') ? 1 : 0;
        }

        model(UsuarioModel::class)->update($id, $datos);

        return redirect()->to('/admin/usuarios')->with('ok', 'Usuario actualizado.');
    }

    public function eliminar(int $id)
    {
        $this->usuarioOFallar($id);

        if ($id === (int) session()->get('usuario_id')) {
            return redirect()->to('/admin/usuarios')->with('error', 'No puede eliminar su propio usuario.');
        }

        model(UsuarioModel::class)->delete($id);

        return redirect()->to('/admin/usuarios')->with('ok', 'Usuario eliminado.');
    }

    private function reglasUsuario(?int $id, bool $claveObligatoria): array
    {
        $unico = $id === null ? 'is_unique[usuarios.usuario]' : "is_unique[usuarios.usuario,id,{$id}]";

        return [
            'nombre' => [
                'label'  => 'Nombre',
                'rules'  => 'required|max_length[120]',
                'errors' => ['required' => 'Indique el nombre de la persona.'],
            ],
            'usuario' => [
                'label'  => 'Usuario',
                'rules'  => "required|min_length[3]|max_length[60]|alpha_dash|{$unico}",
                'errors' => [
                    'required'     => 'Indique el nombre de usuario.',
                    'alpha_dash'   => 'El usuario solo puede tener letras, números, guion y guion bajo.',
                    'is_unique'    => 'Ese nombre de usuario ya existe.',
                ],
            ],
            'clave' => [
                'label'  => 'Contraseña',
                'rules'  => ($claveObligatoria ? 'required|' : 'permit_empty|') . 'min_length[8]|max_length[72]',
                'errors' => [
                    'required'   => 'Indique una contraseña.',
                    'min_length' => 'La contraseña debe tener al menos 8 caracteres.',
                ],
            ],
            'rol' => [
                'label'  => 'Rol',
                'rules'  => 'required|in_list[' . implode(',', array_keys(UsuarioModel::ROLES)) . ']',
                'errors' => ['in_list' => 'Seleccione un rol válido.'],
            ],
        ];
    }

    private function usuarioOFallar(int $id): array
    {
        $usuario = model(UsuarioModel::class)->find($id);
        if ($usuario === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $usuario;
    }
}
