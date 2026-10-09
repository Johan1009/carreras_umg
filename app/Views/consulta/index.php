<?= $this->extend('layout') ?>

<?= $this->section('titulo') ?>Consulta de carreras<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<h1>Carreras UMG</h1>
<p class="suave" style="margin-bottom:24px">Toque una carrera para ver su información.</p>

<?php if (empty($carreras)): ?>
    <div class="tarjeta vacio">
        <p>Aún no hay carreras registradas.</p>
        <?php if (session()->get('rol') === 'admin'): ?>
            <a class="btn btn-primario" href="<?= site_url('admin/carreras/nueva') ?>">Registrar una carrera</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <input class="buscador" id="buscador" type="search" placeholder="Buscar carrera" aria-label="Buscar carrera"
           autocomplete="off">

    <ul class="reticula">
        <?php foreach ($carreras as $carrera): ?>
            <li>
                <a class="baldosa" href="<?= site_url('consulta/carrera/' . $carrera['id']) ?>"
                   data-carrera="<?= esc($carrera['nombre']) ?>">
                    <span class="icono icono-<?= ((int) $carrera['id'] % 6) + 1 ?>" aria-hidden="true">
                        <?= esc(mb_strtoupper(mb_substr($carrera['nombre'], 0, 1))) ?>
                    </span>
                    <span class="baldosa-nombre"><?= esc($carrera['nombre']) ?></span>
                    <span class="baldosa-detalle"><?= (int) $carrera['archivos'] ?>/3 documentos</span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <p class="vacio" hidden id="sin-resultados">No se encontró ninguna carrera con ese nombre.</p>
<?php endif; ?>
<?= $this->endSection() ?>
