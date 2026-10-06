<?= $this->extend('layout') ?>

<?= $this->section('titulo') ?>Configuración de correo<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<?php
    $valor = fn (string $campo) => old($campo) ?? ($config[$campo] ?? '');
    $tieneClave = ! empty($config['clave']);
?>
<h1>Correo de salida</h1>
<p class="suave" style="margin-bottom:24px">
    Servidor SMTP con el que se envía la información a los interesados. Para Gmail se necesita una
    <strong>contraseña de aplicación</strong> (no la contraseña normal de la cuenta).
</p>

<form class="tarjeta" method="post" action="<?= site_url('admin/correo') ?>" autocomplete="off">
    <?= csrf_field() ?>

    <div class="acciones-form" style="justify-content:flex-start;margin:0 0 20px">
        <button class="btn btn-secundario" type="button" data-preset-gmail>Usar Gmail</button>
    </div>

    <div class="campo">
        <label for="host">Servidor SMTP</label>
        <input type="text" id="host" name="host" required maxlength="190" value="<?= esc($valor('host')) ?>">
    </div>

    <div class="campo">
        <label for="puerto">Puerto</label>
        <input type="number" id="puerto" name="puerto" required min="1" max="65535" inputmode="numeric"
               value="<?= esc($valor('puerto')) ?>">
        <div class="campo-ayuda">587 con TLS (recomendado) o 465 con SSL.</div>
    </div>

    <div class="campo">
        <label for="cifrado">Cifrado</label>
        <select id="cifrado" name="cifrado" required>
            <?php foreach (['tls' => 'TLS (STARTTLS)', 'ssl' => 'SSL', 'ninguno' => 'Ninguno'] as $valorCifrado => $etiqueta): ?>
                <option value="<?= $valorCifrado ?>" <?= $valor('cifrado') === $valorCifrado ? 'selected' : '' ?>><?= esc($etiqueta) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="campo">
        <label for="usuario">Usuario SMTP</label>
        <input type="text" id="usuario" name="usuario" maxlength="190" autocapitalize="none"
               autocomplete="off" value="<?= esc($valor('usuario')) ?>">
        <div class="campo-ayuda">Normalmente la dirección de correo completa.</div>
    </div>

    <div class="campo">
        <label for="clave">Contraseña SMTP</label>
        <input type="password" id="clave" name="clave" maxlength="190" autocomplete="new-password"
               placeholder="<?= $tieneClave ? '•••••••• (sin cambios)' : '' ?>">
        <div class="campo-ayuda">
            <?= $tieneClave ? 'Guardada cifrada. Escriba una nueva solo si desea cambiarla.' : 'Se guardará cifrada en la base de datos.' ?>
        </div>
    </div>

    <div class="campo">
        <label for="remitente_email">Correo remitente</label>
        <input type="email" id="remitente_email" name="remitente_email" required maxlength="190"
               inputmode="email" autocapitalize="none" value="<?= esc($valor('remitente_email')) ?>">
    </div>

    <div class="campo">
        <label for="remitente_nombre">Nombre del remitente</label>
        <input type="text" id="remitente_nombre" name="remitente_nombre" maxlength="190"
               value="<?= esc($valor('remitente_nombre')) ?>" placeholder="Secretaría">
    </div>

    <div class="acciones-form">
        <button class="btn btn-primario" type="submit">Guardar configuración</button>
    </div>
</form>

<form class="tarjeta" method="post" action="<?= site_url('admin/correo/probar') ?>">
    <?= csrf_field() ?>
    <h2>Probar configuración</h2>
    <p class="suave pequeno">Envía un correo de prueba al correo remitente configurado.</p>
    <button class="btn btn-secundario btn-bloque" type="submit">Enviar correo de prueba</button>
</form>
<?= $this->endSection() ?>
