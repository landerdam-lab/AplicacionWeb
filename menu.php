<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar">
    <div class="logo">
        <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'ADMIN'): ?>
            Panel Admin
        <?php elseif(isset($_SESSION['rol']) && $_SESSION['rol'] == 'PROVEEDOR'): ?>
            Panel Proveedor
        <?php else: ?>
            Tienda
        <?php endif; ?>
    </div>

    <ul>
        <?php if (isset($_SESSION['usuario'])): ?>
            
            <?php if ($_SESSION['rol'] == 'PROVEEDOR'): ?>
                <li><a href="dashboard_proveedor.php">Mis Productos</a></li>
                <li><a href="nuevo_producto.php"> Añadir</a></li>
            
            <?php else: ?>
                <li><a href="dashboard_admin.php">Inicio</a></li>
                <li><a href="solicitudes_proveedores.php">Solicitudes</a></li>
                <li><a href="gestion_proveedores.php">Proveedores</a></li>
                <li><a href="gestion_productos.php">Productos</a></li>
            <?php endif; ?>

            <li>
                <a href="logout.php" style="color: var(--danger); font-weight: bold; margin-left: 10px;">
                    Cerrar Sesión
                </a>
            </li>

        <?php else: ?>
            <li><a href="login.php">Acceder</a></li>
            <li><a href="registro.php" class="btn btn-primary" style="padding: 0.5rem 1rem; width: auto;">Registro</a></li>
        <?php endif; ?>
    </ul>
</nav>