<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>Acceso · carreras_umg</title>
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
<button class="tema-boton tema-flotante" type="button" data-tema aria-label="Cambiar tema"></button>
<div class="acceso">
    <div class="acceso-caja">
        <div class="acceso-icono" aria-hidden="true">C</div>
        <h1>carreras_umg</h1>
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
