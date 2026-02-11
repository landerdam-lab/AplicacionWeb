<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'bbdd.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'ADMIN') {
    header('Location: login.php');
    exit();
}

$mensaje = "";

// Aceptar proveedor
if (isset($_GET['aceptar'])) {
    cambiarEstadoProveedor($_GET['aceptar'], 'ACEPTADO');
    header('Location: solicitudes_proveedores.php?msg=aceptado'); 
    exit();
}

// Rechazar proveedor
if (isset($_GET['rechazar'])) {
    eliminarProveedor($_GET['rechazar']);
    header('Location: solicitudes_proveedores.php?msg=rechazado'); 
    exit();
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'aceptado') $mensaje = "Proveedor aceptado correctamente";
    if ($_GET['msg'] == 'rechazado') $mensaje = "Solicitud rechazada";
}

$solicitudes = getProveedoresPendientes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitudes | Admin</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Solicitudes Pendientes</h2>
            <span class="badge badge-yellow"><?= count($solicitudes) ?> Pendientes</span>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-success">✅ <?= $mensaje ?></div>
        <?php endif; ?>

        <div class="table-container">
            <?php if (empty($solicitudes)): ?>
                <div class="empty-state">
                    <h3>✅ Todo limpio</h3>
                    <p>No hay solicitudes de nuevos proveedores pendientes.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>CIF</th>
                                <th>Empresa / Dirección</th>
                                <th>Usuario</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitudes as $p): ?>
                            <tr>
                                <td style="font-family: monospace; font-weight: bold;"><?= htmlspecialchars($p['CIF']) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($p['NOMBRE']) ?></strong><br>
                                    <small style="color: #64748b;"><?= htmlspecialchars($p['DIRECCION'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($p['USUARIO_PROVEEDOR'] ?? '') ?></td>
                                <td><span class="badge badge-yellow">Pendiente</span></td>
                                <td>
                                    <a href="?aceptar=<?= urlencode($p['CIF']) ?>" class="btn btn-success btn-sm">Aceptar</a>
                                    <a href="?rechazar=<?= urlencode($p['CIF']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Rechazar esta solicitud?')">Rechazar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>