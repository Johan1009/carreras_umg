<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Crea el primer administrador y la fila de configuración SMTP.
 * Si no existe ningún usuario, genera una contraseña aleatoria y la muestra UNA sola vez.
 *
 * Uso: php spark db:seed AdminInicialSeeder
 */
class AdminInicialSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = date('Y-m-d H:i:s');

        if ($this->db->table('usuarios')->countAllResults() === 0) {
            $clave = bin2hex(random_bytes(6));

            $this->db->table('usuarios')->insert([
                'nombre'     => 'Administrador',
                'usuario'    => 'admin',
                'password'   => password_hash($clave, PASSWORD_DEFAULT),
                'rol'        => 'admin',
                'activo'     => 1,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            echo "\nUsuario creado: admin\n";
            echo "Contraseña temporal: {$clave}\n";
            echo "Guárdela y cámbiela desde el módulo de usuarios.\n\n";
        }

        if ($this->db->table('correo_config')->countAllResults() === 0) {
            $this->db->table('correo_config')->insert([
                'id'         => 1,
                'host'       => 'smtp.gmail.com',
                'puerto'     => 587,
                'cifrado'    => 'tls',
                'updated_at' => $ahora,
            ]);
        }
    }
}
