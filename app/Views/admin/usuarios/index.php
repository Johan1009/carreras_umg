<?= $this->extend('layout') ?>

<?= $this->section('titulo') ?>Usuarios<?= $this->endSection() ?>

<?= $this->section('contenido') ?>
<?php $idActual = (int) session()->get('usuario_id'); ?>

<div class="cabecera-carrera">
    <h1>Usuarios</h1>
    <a class="btn btn-primario" href="<?= site_url('admin/usuarios/nuevo') ?>">+ Nuevo usuario</a>
</div>

<div class="lista">
    <?php foreach ($usuarios as $usuario): ?>
        <div class="lista-fila">
            <div class="lista-fila-texto">
                <div class="lista-fila-titulo">
                    <?= esc($usuario['nombre']) ?>
                    <?php if ((int) $usuario['id'] === $idActual): ?><span class="suave pequeno">(usted)</span><?php endif; ?>
                </div>
                <div class="suave pequeno">
                    <?= esc($usuario['usuario']) ?> · <?= esc(\App\Models\UsuarioModel::ROLES[$usuario['rol']] ?? $usuario['rol']) ?>
                    · <?= (int) $usuario['activo'] === 1 ? '<span style="color:var(--verde)">Activo</span>' : '<span style="color:var(--rojo)">Inactivo</span>' ?>
                </div>
            </div>
            <div class="lista-fila-acciones">
                <a class="btn btn-pequeno btn-secundario" href="<?= site_url('admin/usuarios/' . $usuario['id'] . '/editar') ?>">Editar</a>
                <?php if ((int) $usuario['id'] !== $idActual): ?>
                    <form method="post" action="<?= site_url('admin/usuarios/' . $usuario['id'] . '/eliminar') ?>"
                          data-confirm="¿Eliminar al usuario «<?= esc($usuario['usuario'], 'js') ?>»?" style="margin:0">
                        <?= csrf_field() ?>
                        <button class="btn btn-pequeno btn-peligro" type="submit">Eliminar</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
