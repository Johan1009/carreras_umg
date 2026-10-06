<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

/**
 * Guarda los PDF de las carreras fuera de la carpeta pública.
 * Los archivos se renombran con un nombre aleatorio: el nombre original se conserva solo para mostrarlo.
 * Se sirven únicamente a través de Consulta::archivo(), que exige sesión iniciada.
 */
class ArchivosCarrera
{
    public const CARPETA = 'uploads/carreras/';

    public static function guardar(int $carreraId, string $tipo, UploadedFile $archivo): array
    {
        $carpeta = \WRITEPATH . self::CARPETA . $carreraId . DIRECTORY_SEPARATOR;
        if (! is_dir($carpeta) && ! mkdir($carpeta, 0750, true) && ! is_dir($carpeta)) {
            throw new RuntimeException('No se pudo crear la carpeta para los archivos.');
        }

        $nombreOriginal = mb_substr(basename($archivo->getClientName()), 0, 255);
        $tamano         = (int) $archivo->getSize();
        $nombreNuevo    = $archivo->getRandomName();

        $archivo->move($carpeta, $nombreNuevo);

        return [
            'carrera_id'      => $carreraId,
            'tipo'            => $tipo,
            'nombre_original' => $nombreOriginal,
            'ruta'            => self::CARPETA . $carreraId . '/' . $nombreNuevo,
            'tamano'          => $tamano,
        ];
    }

    public static function rutaAbsoluta(string $ruta): string
    {
        return \WRITEPATH . $ruta;
    }

    public static function eliminar(string $ruta): void
    {
        $absoluta = self::rutaAbsoluta($ruta);
        if (is_file($absoluta)) {
            unlink($absoluta);
        }
    }

    public static function eliminarCarpeta(int $carreraId): void
    {
        $carpeta = \WRITEPATH . self::CARPETA . $carreraId;
        if (! is_dir($carpeta)) {
            return;
        }

        foreach (glob($carpeta . DIRECTORY_SEPARATOR . '*') ?: [] as $archivo) {
            unlink($archivo);
        }
        rmdir($carpeta);
    }
}
