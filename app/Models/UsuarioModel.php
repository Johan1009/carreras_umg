<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = ['nombre', 'usuario', 'password', 'rol', 'activo'];

    public const ROLES = [
        'admin'      => 'Administrador',
        'secretaria' => 'Secretaría',
    ];
}
