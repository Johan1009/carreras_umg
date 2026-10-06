<?php

namespace App\Models;

use CodeIgniter\Model;

class CarreraModel extends Model
{
    protected $table            = 'carreras';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = ['nombre'];

    /**
     * Carreras con la cantidad de archivos cargados (para el catálogo).
     */
    public function conArchivos(?string $buscar = null): array
    {
        $builder = $this->db->table('carreras c')
            ->select('c.id, c.nombre, COUNT(a.id) AS archivos')
            ->join('carrera_archivos a', 'a.carrera_id = c.id', 'left')
            ->groupBy('c.id')
            ->orderBy('c.nombre', 'ASC');

        if ($buscar !== null && $buscar !== '') {
            $builder->like('c.nombre', $buscar);
        }

        return $builder->get()->getResultArray();
    }
}
