<?php

namespace App\Models;

use CodeIgniter\Model;

class CarreraArchivoModel extends Model
{
    protected $table            = 'carrera_archivos';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = ['carrera_id', 'tipo', 'nombre_original', 'ruta', 'tamano'];

    /**
     * Tipos de archivo fijos por carrera: 1 trifoliar oficial + 2 complementarios.
     */
    public const TIPOS = [
        'principal'        => 'Trifoliar oficial',
        'complementario_1' => 'Datos administrativos 1',
        'complementario_2' => 'Datos administrativos 2',
    ];

    /**
     * Devuelve los archivos de una carrera indexados por tipo.
     */
    public function porCarrera(int $carreraId): array
    {
        $filas = $this->where('carrera_id', $carreraId)->findAll();

        $porTipo = [];
        foreach ($filas as $fila) {
            $porTipo[$fila['tipo']] = $fila;
        }

        return $porTipo;
    }
}
