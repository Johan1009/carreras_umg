<?= $this->extend('layout') ?>

<?= $this->section('titulo') ?><?= $usuario ? 'Editar usuario' : 'Nuevo usuario' ?><?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<?php
    $editando = $usuario !== null;
    $esActual = $editando && (int) $usuario['id'] === (int) session()->get('usuario_id');
    $accion   = $editando ? site_url('admin/usuarios/' . $usuario['id']) : site_url('admin/usuarios');
    $valor    = fn (string $campo) => old($campo) ?? ($usuario[$campo] ?? '');
    $rolActual = $valor('rol') ?: 'secretaria';
?>
<p class="pequeno" style="margin-bottom:8px"><a href="<?= site_url('admin/usuarios') ?>">&larr; Volver a usuarios</a></p>
<h1><?= $editando ? 'Editar usuario' : 'Nuevo usuario' ?></h1>
<p class="suave" style="margin-bottom:24px">
    Administrador: gestiona carreras, usuarios y correo. Secretaría: solo consulta y envía información.
</p>

<form class="tarjeta" method="post" action="<?= $accion ?>" autocomplete="off">
    <?= csrf_field() ?>

    <div class="campo">
        <label for="nombre">Nombre completo</label>
        <input type="text" id="nombre" name="nombre" required maxlength="120" value="<?= esc($valor('nombre')) ?>">
    </div>

    <div class="campo">
        <label for="usuario">Nombre de usuario</label>
        <input type="text" id="usuario" name="usuario" required minlength="3" maxlength="60"
               autocapitalize="none" autocomplete="off" value="<?= esc($valor('usuario')) ?>">
        <div class="campo-ayuda">Letras, números, guion y guion bajo.</div>
    </div>

    <div class="campo">
        <label for="clave">Contraseña</label>
        <input type="password" id="clave" name="clave" minlength="8" maxlength="72" autocomplete="new-password"
               <?= $editando ? '' : 'required' ?>>
        <div class="campo-ayuda">
            <?= $editando ? 'Déjela vacía para conservar la contraseña actual. ' : '' ?>Mínimo 8 caracteres.
        </div>
    </div>

    <?php if ($esActual): ?>
        <input type="hidden" name="rol" value="<?= esc($rolActual) ?>">
    <?php endif; ?>
    <div class="campo">
        <label for="rol">Rol</label>
        <select id="rol" <?= $esActual ? 'disabled' : 'name="rol"' ?>>
            <?php foreach ($roles as $valorRol => $etiqueta): ?>
                <option value="<?= $valorRol ?>" <?= $rolActual === $valorRol ? 'selected' : '' ?>><?= esc($etiqueta) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($esActual): ?>
            <div class="campo-ayuda">No puede cambiar su propio rol.</div>
        <?php endif; ?>
    </div>

    <?php if ($editando): ?>
        <div class="campo">
            <label class="casilla">
                <input type="checkbox" name="activo" value="1" <?= (int) $usuario['activo'] === 1 ? 'checked' : '' ?>
                       <?= $esActual ? 'disabled' : '' ?>>
                Cuenta activa
            </label>
            <?php if ($esActual): ?>
                <div class="campo-ayuda">No puede desactivar su propia cuenta.</div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="acciones-form">
        <a class="btn btn-secundario" href="<?= site_url('admin/usuarios') ?>">Cancelar</a>
        <button class="btn btn-primario" type="submit">Guardar</button>
    </div>
</form>
<?= $this->endSection() ?>
