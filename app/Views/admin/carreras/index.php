<?= $this->extend('layout') ?>

<?= $this->section('titulo') ?>Administrar carreras<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<div class="cabecera-carrera">
    <h1>Carreras</h1>
    <a class="btn btn-primario" href="<?= site_url('admin/carreras/nueva') ?>">+ Nueva carrera</a>
</div>

<?php if (empty($carreras)): ?>
    <div class="tarjeta vacio"><p>No hay carreras registradas todavía.</p></div>
<?php else: ?>
    <div class="lista">
        <?php foreach ($carreras as $carrera): ?>
            <div class="lista-fila">
                <div class="lista-fila-texto">
                    <div class="lista-fila-titulo"><?= esc($carrera['nombre']) ?></div>
                    <div class="suave pequeno"><?= (int) $carrera['archivos'] ?>/3 documentos</div>
                </div>
                <div class="lista-fila-acciones">
                    <a class="btn btn-pequeno btn-secundario" href="<?= site_url('admin/carreras/' . $carrera['id'] . '/editar') ?>">Editar</a>
                    <form method="post" action="<?= site_url('admin/carreras/' . $carrera['id'] . '/eliminar') ?>"
                          data-confirm="¿Eliminar la carrera «<?= esc($carrera['nombre'], 'js') ?>» y sus documentos?" style="margin:0">
                        <?= csrf_field() ?>
                        <button class="btn btn-pequeno btn-peligro" type="submit">Eliminar</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
