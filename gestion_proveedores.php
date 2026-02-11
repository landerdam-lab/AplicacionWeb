<?php
session_start();
require_once 'bbdd.php';


if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'ADMIN') { 
    header('Location: login.php'); 
    exit(); 
}

if (isset($_GET['borrar'])) {
    eliminarProveedor($_GET['borrar']);
    header('Location: gestion_proveedores.php'); 
    exit();
}

$proveedores = getProveedoresActivos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proveedores | Admin</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container">
        <h2 class="mb-2">Proveedores Activos</h2>

        <div class="table-container">
            <?php if(empty($proveedores)): ?>
                <div class="empty-state">
                    <h3>No hay proveedores activos</h3>
                    <p>Actualmente no hay ninguna empresa validada en el sistema.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>CIF</th>
                                <th>Empresa</th>
                                <th>Usuario</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($proveedores as $p): ?>
                            <tr>
                                <td><?= $p['CIF'] ?></td>
                                <td><strong><?= $p['NOMBRE'] ?></strong></td>
                                <td><?= $p['USUARIO_PROVEEDOR'] ?></td>
                                <td><?= $p['DIRECCION'] ?></td>
                                <td>
                                    <span class="badge badge-green">Activo</span>
                                </td>
                                <td>
                                    <a href="?borrar=<?= $p['CIF'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Dar de baja a este proveedor? Se borrarán todos sus productos del catálogo.')">
                                        Dar de baja
                                    </a>
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