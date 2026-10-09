<?php

namespace App\Controllers;

use App\Libraries\ArchivosCarrera;
use App\Libraries\CorreoService;
use App\Models\CarreraArchivoModel;
use App\Models\CarreraModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

/**
 * Módulo de consulta para secretaría: catálogo de carreras, visor de PDF y envío por correo.
 */
class Consulta extends BaseController
{
    public function index()
    {
        return view('consulta/index', [
            'carreras' => model(CarreraModel::class)->conArchivos(),
        ]);
    }

    public function ver(int $id)
    {
        $carrera = $this->carreraOFallar($id);

        return view('consulta/ver', [
            'carrera'  => $carrera,
            'archivos' => model(CarreraArchivoModel::class)->porCarrera($id),
            'tipos'    => CarreraArchivoModel::TIPOS,
        ]);
    }

    /**
     * Sirve un PDF de la carrera. Nunca se expone la ruta real en disco.
     */
    public function archivo(int $carreraId, string $tipo)
    {
        if (! array_key_exists($tipo, CarreraArchivoModel::TIPOS)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $fila = model(CarreraArchivoModel::class)
            ->where(['carrera_id' => $carreraId, 'tipo' => $tipo])
            ->first();

        $ruta = $fila ? ArchivosCarrera::rutaAbsoluta($fila['ruta']) : null;
        if ($ruta === null || ! is_file($ruta)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $nombre = preg_replace('/[^\w.\- ]/u', '_', $fila['nombre_original']) ?: 'documento.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $nombre . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody((string) file_get_contents($ruta));
    }

    public function enviar(int $id)
    {
        $carrera = $this->carreraOFallar($id);
        $volver  = '/consulta/carrera/' . $id;

        $reglas = [
            'correo' => [
                'label' => 'Correo del interesado',
                'rules' => 'required|valid_email|max_length[190]',
                'errors' => [
                    'required'    => 'Indique el correo electrónico del interesado.',
                    'valid_email' => 'El correo electrónico no es válido.',
                ],
            ],
            // No se exige formato: el texto se escapa al mostrarlo (ver Views/correo/informacion.php)
            // y nunca se usa en consultas a la base de datos. Solo se limita el tamaño.
            'mensaje' => [
                'label' => 'Mensaje adicional',
                'rules' => 'permit_empty|max_length[1000]',
                'errors' => [
                    'max_length' => 'El mensaje adicional no puede superar los 1000 caracteres.',
                ],
            ],
        ];
        if (! $this->validate($reglas)) {
            $error = $this->validator->getError('correo') ?: $this->validator->getError('mensaje');

            return redirect()->to($volver)->withInput()->with('error', $error);
        }

        $seleccion = array_values(array_intersect(
            (array) $this->request->getPost('adjuntos'),
            array_keys(CarreraArchivoModel::TIPOS),
        ));

        $archivos = model(CarreraArchivoModel::class)->porCarrera($id);
        $rutas    = [];
        foreach ($seleccion as $tipo) {
            if (isset($archivos[$tipo])) {
                $rutas[] = ArchivosCarrera::rutaAbsoluta($archivos[$tipo]['ruta']);
            }
        }

        $correo  = (string) $this->request->getPost('correo');
        $mensaje = trim((string) $this->request->getPost('mensaje'));

        try {
            (new CorreoService())->enviar(
                $correo,
                'Información de la carrera: ' . $carrera['nombre'],
                view('correo/informacion', [
                    'carrera' => $carrera,
                    'mensaje' => $mensaje,
                    'adjuntos' => array_map(
                        static fn (string $t): string => CarreraArchivoModel::TIPOS[$t],
                        array_intersect($seleccion, array_keys($archivos)),
                    ),
                ]),
                $rutas,
            );
        } catch (RuntimeException $e) {
            return redirect()->to($volver)->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to($volver)->with('ok', 'Información enviada a ' . $correo . '.');
    }

    private function carreraOFallar(int $id): array
    {
        $carrera = model(CarreraModel::class)->find($id);
        if ($carrera === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $carrera;
    }
}
