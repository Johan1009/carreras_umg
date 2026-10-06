<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Información de la carrera</title>
</head>
<body style="margin:0;padding:24px;background:#f2f2f7;font-family:Arial,Helvetica,sans-serif;color:#1c1c1e;">
<div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:16px;padding:28px;">
    <h2 style="margin:0 0 16px;font-size:22px;">Información de la carrera</h2>
    <p style="font-size:17px;margin:0 0 16px;">
        <strong><?= esc($carrera['nombre']) ?></strong>
    </p>

    <?php if ($mensaje !== ''): ?>
        <p style="font-size:16px;line-height:1.5;margin:0 0 16px;white-space:pre-line;"><?= esc($mensaje) ?></p>
    <?php endif; ?>

    <?php if (! empty($adjuntos)): ?>
        <p style="font-size:16px;margin:0 0 8px;">Documentos adjuntos:</p>
        <ul style="font-size:16px;margin:0 0 16px;padding-left:20px;">
            <?php foreach ($adjuntos as $nombre): ?>
                <li><?= esc($nombre) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p style="font-size:16px;margin:0 0 16px;">Para más información, comuníquese con la secretaría.</p>
    <?php endif; ?>

    <p style="font-size:14px;color:#6e6e73;margin:24px 0 0;">Este mensaje fue enviado desde la secretaría. Si no lo esperaba, puede ignorarlo.</p>
</div>
</body>
</html>
