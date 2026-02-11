<?php
session_start();
require_once 'bbdd.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'ADMIN') {
    header('Location: login.php');
    exit();
}

$error = "";
$mensaje = "";

if (isset($_GET['borrar'])) {
    $resultado = borrarProducto($_GET['borrar']);
    
    if ($resultado === true) {
        header('Location: gestion_productos.php');
        exit();
    } else {
        $error = "❌ " . $resultado;
    }
}

$productos = getAllProductosAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos | Admin</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        /* Estilos específicos para los botones de acción con texto */
        .actions-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn-action {
            padding: 6px 12px;
            font-size: 0.9rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;
        }
        .btn-edit {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #7dd3fc;
        }
        .btn-edit:hover {
            background: #bae6fd;
            border-color: #60a5fa;
        }
        .btn-delete {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .btn-delete:hover {
            background: #fecaca;
            border-color: #f87171;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container">
        <h2 class="mb-2">Catálogo Global</h2>

        <?php if($error): ?>
            <div class="alert alert-error">
                <?= $error ?>
                <br><strong>Posible causa:</strong> El producto está comprado en un pedido o carrito.
            </div>
        <?php endif; ?>

        <div class="table-container">
            <?php if(empty($productos)): ?>
                <div class="empty-state">
                    <h3>📦 Sin stock</h3>
                    <p>Aún no hay productos registrados en la plataforma.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th width="80">Imagen</th>
                                <th>Producto</th>
                                <th>Proveedor</th>
                                <th>Precios</th>
                                <th>Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($productos as $prod): ?>
                            <tr>
                                <td>
                                    <?php if(!empty($prod['IMAGEN'])): ?>
                                        <img src="<?= $prod['IMAGEN'] ?>" class="product-thumb" alt="Img">
                                    <?php else: ?>
                                        <div class="product-thumb" style="display:flex;align-items:center;justify-content:center;">📦</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: bold;"><?= htmlspecialchars($prod['DESCRIPCION'] ?? 'Sin descripción') ?></div>
                                    <div style="font-size: 0.85rem; color: var(--text-light);">ID: <?= $prod['ID_COMPONENTE'] ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-blue">
                                        <?= htmlspecialchars($prod['NOMBRE_PROVEEDOR'] ?? 'Sin proveedor') ?>
                                    </span>
                                </td>
                                <td>
                                    <div>Venta: <strong><?= number_format($prod['PRECIO_VENTA'] ?? 0, 2) ?>€</strong></div>
                                    <div style="font-size: 0.8rem; color: var(--text-light);">Coste: <?= number_format($prod['PRECIO_COMPRA'] ?? 0, 2) ?>€</div>
                                </td>
                                <td>
                                    <?php if($prod['STOCK'] < 5): ?>
                                        <span class="badge badge-red">Bajo: <?= $prod['STOCK'] ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-green"><?= $prod['STOCK'] ?> u.</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions-group">
                                        <a href="editar_producto.php?id=<?= $prod['ID_COMPONENTE'] ?>" 
                                           class="btn-action btn-edit">
                                            Editar
                                        </a>
                                        <a href="?borrar=<?= $prod['ID_COMPONENTE'] ?>" 
                                           class="btn-action btn-delete"
                                           onclick="return confirm('¿Realmente quieres borrar el producto «<?= htmlspecialchars($prod['DESCRIPCION'] ?? 'Sin nombre') ?>»? Esta acción no se puede deshacer.')">
                                            Borrar
                                        </a>
                                    </div>
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