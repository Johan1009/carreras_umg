<?php
    // El tema se guarda en una cookie (no localStorage) para que el servidor pueda
    // aplicarlo desde el primer render de cada página, sin depender de JavaScript
    // ni de que el navegador permita almacenamiento local.
    $tema = $_COOKIE['tema'] ?? null;
    $temaAtributo = $tema === 'oscuro' ? ' data-theme="dark"' : ($tema === 'claro' ? ' data-theme="light"' : '');
?>
<!doctype html>
<html lang="es"<?= $temaAtributo ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>Acceso · Carreras UMG</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/logo-umg.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/app.css') ?>">
</head>
<body>
<div class="cinta-umg" aria-hidden="true"></div>
<button class="tema-boton tema-flotante" type="button" data-tema aria-label="Cambiar tema"></button>
<div class="acceso">
    <div class="acceso-caja">
        <img class="acceso-icono" src="<?= base_url('assets/img/logo-umg.png') ?>" alt="Universidad Mariano Gálvez" width="112" height="112">
        <h1>Carreras UMG</h1>
        <p class="suave">Ingrese con su usuario y contraseña</p>

        <?php if ($ok = session()->getFlashdata('ok')): ?>
            <div class="aviso aviso-ok" role="status"><?= esc($ok) ?></div>
        <?php endif; ?>
        <?php if ($error = session()->getFlashdata('error')): ?>
            <div class="aviso aviso-error" role="alert"><?= esc($error) ?></div>
        <?php endif; ?>

        <form class="tarjeta" method="post" action="<?= site_url('login') ?>" autocomplete="on">
            <?= csrf_field() ?>
            <div class="campo">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" required autofocus autocapitalize="none"
                       autocomplete="username" value="<?= esc(session()->getFlashdata('usuario_previo') ?? '') ?>">
            </div>
            <div class="campo">
                <label for="clave">Contraseña</label>
                <input type="password" id="clave" name="clave" required autocomplete="current-password">
            </div>
            <button class="btn btn-primario btn-bloque" type="submit">Entrar</button>
        </form>
    </div>
</div>
<script src="<?= base_url('assets/js/app.js') ?>?v=<?= @filemtime(FCPATH . 'assets/js/app.js') ?>" defer></script>
</body>
</html>
