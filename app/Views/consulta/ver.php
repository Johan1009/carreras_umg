<?= $this->extend('layout') ?>

<?= $this->section('titulo') ?><?= esc($carrera['nombre']) ?><?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<?php
    $primero = null;
    foreach (array_keys($tipos) as $t) {
        if (isset($archivos[$t])) {
            $primero = $t;
            break;
        }
    }
?>

<div class="cabecera-carrera">
    <div>
        <p class="suave pequeno" style="margin:0 0 4px"><a href="<?= site_url('consulta') ?>">&larr; Todas las carreras</a></p>
        <h1><?= esc($carrera['nombre']) ?></h1>
    </div>
    <button class="btn btn-primario" type="button" data-abrir-hoja>Enviar por correo</button>
</div>

<?php if ($primero === null): ?>
    <div class="tarjeta vacio">
        <p>Esta carrera no tiene documentos cargados.</p>
    </div>
<?php else: ?>
    <div class="segmentado" role="tablist" aria-label="Documentos de la carrera">
        <?php foreach ($tipos as $tipo => $etiqueta): ?>
            <button type="button" role="tab"
                    aria-selected="<?= $tipo === $primero ? 'true' : 'false' ?>"
                    data-src="<?= site_url('archivo/' . $carrera['id'] . '/' . $tipo) ?>#toolbar=1&navpanes=0"
                    data-href="<?= site_url('archivo/' . $carrera['id'] . '/' . $tipo) ?>"
                    <?= isset($archivos[$tipo]) ? '' : 'disabled' ?>>
                <?= esc($etiqueta) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <section class="visor" id="visor" aria-label="Visor de documento">
        <button class="btn btn-secundario visor-cerrar" type="button" id="btn-cerrar-visor">Cerrar pantalla completa</button>
        <iframe class="visor-marco" id="visor-marco" title="Documento PDF"
                src="<?= site_url('archivo/' . $carrera['id'] . '/' . $primero) ?>#toolbar=1&navpanes=0"></iframe>
        <p class="suave pequeno" style="margin-top:12px">
            ¿No ve el documento aquí? En celular o tableta el visor integrado a veces no carga el PDF; use "Abrir en pestaña nueva".
        </p>
        <div class="visor-pie">
            <span class="suave pequeno">Documento: <?= esc($archivos[$primero]['nombre_original']) ?></span>
            <div style="display:flex; gap:12px; flex-wrap:wrap">
                <a class="btn btn-secundario" id="btn-abrir-pestana" target="_blank" rel="noopener"
                   href="<?= site_url('archivo/' . $carrera['id'] . '/' . $primero) ?>">Abrir en pestaña nueva</a>
                <button class="btn btn-secundario" type="button" id="btn-ampliar">Ampliar a pantalla completa</button>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php $hojaAbierta = old('correo') !== null; // si el envío falló, se reabre con los datos escritos ?>
<div class="hoja-fondo" id="hoja-correo" <?= $hojaAbierta ? '' : 'hidden' ?> role="dialog" aria-modal="true" aria-labelledby="titulo-hoja">
    <form class="hoja" method="post" action="<?= site_url('consulta/carrera/' . $carrera['id'] . '/enviar') ?>">
        <?= csrf_field() ?>
        <div class="hoja-agarre" aria-hidden="true"></div>
        <h2 id="titulo-hoja">Enviar información por correo</h2>
        <p class="suave pequeno">Carrera: <?= esc($carrera['nombre']) ?></p>

        <div class="campo">
            <label for="correo">Correo del interesado</label>
            <input type="email" id="correo" name="correo" required inputmode="email" autocomplete="off"
                   autocapitalize="none" value="<?= esc(old('correo') ?? '') ?>" placeholder="nombre@correo.com">
        </div>

        <div class="campo">
            <span class="etiqueta">Documentos a adjuntar</span>
            <?php foreach ($tipos as $tipo => $etiqueta): ?>
                <?php if (isset($archivos[$tipo])): ?>
                    <label class="casilla">
                        <input type="checkbox" name="adjuntos[]" value="<?= $tipo ?>"
                               <?= $tipo === 'principal' ? 'checked' : '' ?>>
                        <?= esc($etiqueta) ?>
                    </label>
                <?php endif; ?>
            <?php endforeach; ?>
            <p class="campo-ayuda">Si no marca ninguno, se envía solo el texto informativo.</p>
        </div>

        <div class="campo">
            <label for="mensaje">Mensaje adicional (opcional)</label>
            <textarea id="mensaje" name="mensaje" maxlength="1000"><?= esc(old('mensaje') ?? '') ?></textarea>
        </div>

        <div class="acciones-form">
            <button class="btn btn-secundario" type="button" data-cerrar-hoja>Cancelar</button>
            <button class="btn btn-primario" type="submit">Enviar</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
