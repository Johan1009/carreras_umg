<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ArchivosCarrera;
use App\Models\CarreraArchivoModel;
use App\Models\CarreraModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Files\UploadedFile;
use Throwable;

class Carreras extends BaseController
{
    private const MAX_KB = 25600; // 25 MB por archivo

    public function index()
    {
        return view('admin/carreras/index', [
            'carreras' => model(CarreraModel::class)->conArchivos(),
        ]);
    }

    public function nueva()
    {
        return view('admin/carreras/form', [
            'carrera'  => null,
            'archivos' => [],
            'tipos'    => CarreraArchivoModel::TIPOS,
        ]);
    }

    public function crear()
    {
        $carreras = model(CarreraModel::class);

        $reglas = $this->reglasNombre(null) + $this->reglasArchivos(array_keys(CarreraArchivoModel::TIPOS));
        if (! $this->validate($reglas)) {
            return redirect()->back()->withInput()->with('errores', $this->validator->getErrors());
        }

        $db = $carreras->db;
        $db->transStart();

        $carreraId = (int) $carreras->insert(['nombre' => trim((string) $this->request->getPost('nombre'))], true);

        $guardados = [];
        try {
            foreach (array_keys(CarreraArchivoModel::TIPOS) as $tipo) {
                $archivo = $this->request->getFile($tipo);
                $datos   = ArchivosCarrera::guardar($carreraId, $tipo, $archivo);
                $guardados[] = $datos['ruta'];
                model(CarreraArchivoModel::class)->insert($datos);
            }
            $db->transComplete();
        } catch (Throwable $e) {
            $db->transRollback();
            array_map([ArchivosCarrera::class, 'eliminar'], $guardados);
            ArchivosCarrera::eliminarCarpeta($carreraId);
            log_message('error', 'Error al crear carrera: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'No se pudo guardar la carrera. Intente de nuevo.');
        }

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'No se pudo guardar la carrera. Intente de nuevo.');
        }

        return redirect()->to('/admin/carreras')->with('ok', 'Carrera registrada con sus tres documentos.');
    }

    public function editar(int $id)
    {
        $carrera = $this->carreraOFallar($id);

        return view('admin/carreras/form', [
            'carrera'  => $carrera,
            'archivos' => model(CarreraArchivoModel::class)->porCarrera($id),
            'tipos'    => CarreraArchivoModel::TIPOS,
        ]);
    }

    public function actualizar(int $id)
    {
        $this->carreraOFallar($id);
        $carreras = model(CarreraModel::class);
        $archivosModel = model(CarreraArchivoModel::class);

        // Solo se validan los archivos que el usuario envió; los demás se conservan.
        // Un archivo con error de subida (p. ej. excede upload_max_filesize) también se valida, para avisarlo.
        $tiposNuevos = [];
        foreach (array_keys(CarreraArchivoModel::TIPOS) as $tipo) {
            $archivo = $this->request->getFile($tipo);
            if ($archivo instanceof UploadedFile && $archivo->getError() !== UPLOAD_ERR_NO_FILE) {
                $tiposNuevos[] = $tipo;
            }
        }

        $reglas = $this->reglasNombre($id) + $this->reglasArchivos($tiposNuevos);
        if (! $this->validate($reglas)) {
            return redirect()->back()->withInput()->with('errores', $this->validator->getErrors());
        }

        $existentes = $archivosModel->porCarrera($id);
        $db         = $carreras->db;
        $db->transStart();

        $carreras->update($id, ['nombre' => trim((string) $this->request->getPost('nombre'))]);

        $guardados = [];
        $reemplazados = [];
        try {
            foreach ($tiposNuevos as $tipo) {
                $datos = ArchivosCarrera::guardar($id, $tipo, $this->request->getFile($tipo));
                $guardados[] = $datos['ruta'];

                if (isset($existentes[$tipo])) {
                    $archivosModel->update($existentes[$tipo]['id'], $datos);
                    $reemplazados[] = $existentes[$tipo]['ruta'];
                } else {
                    $archivosModel->insert($datos);
                }
            }
            $db->transComplete();
        } catch (Throwable $e) {
            $db->transRollback();
            array_map([ArchivosCarrera::class, 'eliminar'], $guardados);
            log_message('error', 'Error al actualizar carrera: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'No se pudieron guardar los cambios. Intente de nuevo.');
        }

        if (! $db->transStatus()) {
            array_map([ArchivosCarrera::class, 'eliminar'], $guardados);

            return redirect()->back()->withInput()->with('error', 'No se pudieron guardar los cambios. Intente de nuevo.');
        }

        // Solo se borran los PDF anteriores cuando la base de datos ya quedó actualizada.
        array_map([ArchivosCarrera::class, 'eliminar'], $reemplazados);

        return redirect()->to('/admin/carreras')->with('ok', 'Carrera actualizada.');
    }

    public function eliminar(int $id)
    {
        $this->carreraOFallar($id);

        $db = model(CarreraModel::class)->db;
        $db->transStart();
        model(CarreraModel::class)->delete($id); // ON DELETE CASCADE elimina los registros de archivos
        $db->transComplete();

        if ($db->transStatus()) {
            ArchivosCarrera::eliminarCarpeta($id);

            return redirect()->to('/admin/carreras')->with('ok', 'Carrera eliminada.');
        }

        return redirect()->to('/admin/carreras')->with('error', 'No se pudo eliminar la carrera.');
    }

    private function reglasNombre(?int $id): array
    {
        $unico = $id === null ? 'is_unique[carreras.nombre]' : "is_unique[carreras.nombre,id,{$id}]";

        return [
            'nombre' => [
                'label'  => 'Nombre de la carrera',
                'rules'  => "required|max_length[190]|{$unico}",
                'errors' => [
                    'required'  => 'El nombre de la carrera es obligatorio.',
                    'is_unique' => 'Ya existe una carrera con ese nombre.',
                ],
            ],
        ];
    }

    /**
     * Reglas para cada archivo. No se exige formato ni nombre al documento; solo que sea PDF real y no muy pesado.
     *
     * @param string[] $tipos Tipos de archivo que deben estar presentes y validarse
     */
    private function reglasArchivos(array $tipos): array
    {
        $reglas = [];
        foreach ($tipos as $tipo) {
            $etiqueta = CarreraArchivoModel::TIPOS[$tipo];

            $reglas[$tipo] = [
                'label'  => $etiqueta,
                'rules'  => 'uploaded[' . $tipo . ']|ext_in[' . $tipo . ',pdf]|mime_in[' . $tipo . ',application/pdf]|max_size[' . $tipo . ',' . self::MAX_KB . ']',
                'errors' => [
                    'uploaded' => "Adjunte el archivo: {$etiqueta}.",
                    'ext_in'   => "{$etiqueta} debe ser un archivo PDF.",
                    'mime_in'  => "{$etiqueta} no es un PDF válido.",
                    'max_size' => "{$etiqueta} supera el tamaño máximo de 25 MB.",
                ],
            ];
        }

        return $reglas;
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
