<?php
session_start();
require_once 'bbdd.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'ADMIN') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin | Tienda Informática</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

    <?php include 'menu.php'; ?>

    <div class="container">
        <h2 class="mb-2">Bienvenido, Administrador</h2>

        <div class="dash-grid">
            
            <div class="card stat-card">
                <div class="stat-number"></div>
                <div class="stat-label">Solicitudes Pendientes</div>
                <p class="mt-2 mb-2">Revisa qué proveedores quieren unirse.</p>
                <a href="solicitudes_proveedores.php" class="btn btn-primary">Gestionar</a>
            </div>

            <div class="card stat-card">
                <div class="stat-number stat-number-success"></div>
                <div class="stat-label">Proveedores Activos</div>
                <p class="mt-2 mb-2">Listado de empresas validadas.</p>
                <a href="gestion_proveedores.php" class="btn btn-success">Ver Listado</a>
            </div>

            <div class="card stat-card">
                <div class="stat-number stat-number-warning"></div>
                <div class="stat-label">Catálogo Global</div>
                <p class="mt-2 mb-2">Control total del stock y productos.</p>
                <a href="gestion_productos.php" class="btn btn-warning">Ver Productos</a>
            </div>

        </div>
    </div>

</body>
</html>