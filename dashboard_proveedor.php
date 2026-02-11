<?php
session_start();
require_once 'bbdd.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'PROVEEDOR') {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];
$misProductos = getMisProductos($usuario);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/proveedor.css">
    <title>Panel Proveedor | Tienda Informática</title>
</head>
<body>

    <?php include 'menu.php'; ?>

    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
            <div>
                <h2>Mis Productos</h2>
                <p style="color:var(--text-light);">Gestiona tu inventario y precios</p>
            </div>
            <a href="nuevo_producto.php" class="btn btn-primary"> Nuevo Producto</a>
        </div>

        <div class="table-container">
            <?php if(empty($misProductos)): ?>
                <div class="empty-state">
                    <h3>Aún no vendes nada</h3>
                    <p>Empieza a ganar dinero añadiendo tu primer componente al catálogo.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th width="80">Imagen</th>
                                <th>Producto</th>
                                <th>Tu Precio (Coste)</th>
                                <th>Precio Venta (Tienda)</th>
                                <th>Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($misProductos as $p): ?>
                            <tr>
                                <td>
                                    <?php if(!empty($p['IMAGEN'])): ?>
                                        <img src="<?= $p['IMAGEN'] ?>" class="product-thumb" alt="Img">
                                    <?php else: ?>
                                        <div class="product-thumb" style="display:flex;align-items:center;justify-content:center;">📦</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= $p['DESCRIPCION'] ?></strong><br>
                                    <small style="color:var(--text-light);">ID: <?= $p['ID_COMPONENTE'] ?></small>
                                </td>
                                <td><strong style="color:var(--primary);"><?= $p['PRECIO_COMPRA'] ?> €</strong></td>
                                <td style="color:var(--text-light);"><?= $p['PRECIO_VENTA'] ?> €</td>
                                <td>
                                    <?php if($p['STOCK'] < 5): ?>
                                        <span class="badge badge-red">Bajo: <?= $p['STOCK'] ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-green"><?= $p['STOCK'] ?> u.</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="editar_producto.php?id=<?= $p['ID_COMPONENTE'] ?>" class="btn" style="background:#f1f5f9;color:var(--text-main);border:1px solid #ccc;">
                                         Editar
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
</DOCUMENT>