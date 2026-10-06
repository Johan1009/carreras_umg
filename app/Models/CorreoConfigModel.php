<?php

namespace App\Models;

use CodeIgniter\Model;

class CorreoConfigModel extends Model
{
    protected $table            = 'correo_config';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $useAutoIncrement = false;
    protected $allowedFields    = [
        'host', 'puerto', 'cifrado', 'usuario', 'clave', 'remitente_email', 'remitente_nombre',
    ];

    /**
     * La configuración es una única fila (id = 1).
     */
    public function obtener(): array
    {
        return $this->find(1) ?? [];
    }
}
