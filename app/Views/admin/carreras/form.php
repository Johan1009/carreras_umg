<?= $this->extend('layout') ?>

<?= $this->section('titulo') ?><?= $carrera ? 'Editar carrera' : 'Nueva carrera' ?><?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<?php
    $editando = $carrera !== null;
    $accion   = $editando ? site_url('admin/carreras/' . $carrera['id']) : site_url('admin/carreras');
?>
<p class="pequeno" style="margin-bottom:8px"><a href="<?= site_url('admin/carreras') ?>">&larr; Volver a carreras</a></p>
<h1><?= $editando ? 'Editar carrera' : 'Nueva carrera' ?></h1>
<p class="suave" style="margin-bottom:24px">
    Adjunte los tres PDF de la carrera. Puede usar cualquier nombre de archivo.
</p>

<form class="tarjeta" method="post" action="<?= $accion ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="campo">
        <label for="nombre">Nombre de la carrera</label>
        <input type="text" id="nombre" name="nombre" required maxlength="190"
               value="<?= esc(old('nombre') ?? $carrera['nombre'] ?? '') ?>">
    </div>

    <h2 style="margin-top:8px">Documentos</h2>

    <?php foreach ($tipos as $tipo => $etiqueta): ?>
        <?php $actual = $archivos[$tipo] ?? null; ?>
        <div class="archivo-tarjeta">
            <label class="etiqueta" for="<?= $tipo ?>">
                <?= esc($etiqueta) ?>
                <?php if ($tipo === 'principal'): ?>(trifoliar: información, inscripciones y costos)<?php else: ?>(datos administrativos)<?php endif; ?>
            </label>
            <input type="file" id="<?= $tipo ?>" name="<?= $tipo ?>" accept="application/pdf,.pdf"
                   <?= $editando && $actual ? '' : 'required' ?>>
            <?php if ($actual): ?>
                <div class="campo-actual">Actual: <?= esc($actual['nombre_original']) ?></div>
                <div class="campo-ayuda">Deje vacío para conservar este documento.</div>
            <?php else: ?>
                <div class="campo-ayuda">Archivo PDF, máximo 25 MB.</div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <div class="acciones-form">
        <a class="btn btn-secundario" href="<?= site_url('admin/carreras') ?>">Cancelar</a>
        <button class="btn btn-primario" type="submit">Guardar carrera</button>
    </div>
</form>
<?= $this->endSection() ?>
