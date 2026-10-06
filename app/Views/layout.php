<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($this->renderSection('titulo')) ?> · carreras_umg</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/app.css') ?>">
    <script>
        // Aplica el tema guardado antes de pintar la página para evitar un parpadeo.
        (function () {
            try {
                var tema = localStorage.getItem('tema');
                if (tema === 'claro' || tema === 'oscuro') {
                    document.documentElement.setAttribute('data-theme', tema === 'oscuro' ? 'dark' : 'light');
                }
            } catch (e) { /* almacenamiento bloqueado: se usa el tema del sistema */ }
        })();
    </script>
</head>
<body>
<?php $esAdmin = session()->get('rol') === 'admin'; ?>

<header class="barra">
    <a class="barra-marca" href="<?= site_url('consulta') ?>">carreras_umg</a>
    <nav class="barra-acciones" aria-label="Navegación principal">
        <span class="barra-usuario"><?= esc(session()->get('nombre') ?? '') ?></span>
        <a class="btn btn-pequeno btn-secundario" href="<?= site_url('consulta') ?>">Consulta</a>
        <?php if ($esAdmin): ?>
            <a class="btn btn-pequeno btn-secundario" href="<?= site_url('admin/carreras') ?>">Administrar carreras</a>
            <a class="btn btn-pequeno btn-secundario" href="<?= site_url('admin/usuarios') ?>">Usuarios</a>
            <a class="btn btn-pequeno btn-secundario" href="<?= site_url('admin/correo') ?>">Correo</a>
        <?php endif; ?>
        <form method="post" action="<?= site_url('logout') ?>" style="margin:0">
            <?= csrf_field() ?>
            <button class="btn btn-pequeno btn-peligro" type="submit">Salir</button>
        </form>
    </nav>
    <button class="tema-boton" type="button" data-tema aria-label="Cambiar tema"></button>
</header>

<main class="contenido">
    <?php if ($ok = session()->getFlashdata('ok')): ?>
        <div class="aviso aviso-ok" role="status"><?= esc($ok) ?></div>
    <?php endif; ?>

    <?php if ($error = session()->getFlashdata('error')): ?>
        <div class="aviso aviso-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <?php if ($errores = session()->getFlashdata('errores')): ?>
        <div class="aviso aviso-error" role="alert">
            <ul>
                <?php foreach ($errores as $mensaje): ?>
                    <li><?= esc($mensaje) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?= $this->renderSection('contenido') ?>
</main>

<script src="<?= base_url('assets/js/app.js') ?>?v=<?= @filemtime(FCPATH . 'assets/js/app.js') ?>" defer></script>
</body>
</html>
