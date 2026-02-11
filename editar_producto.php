<?php
session_start();
require_once 'bbdd.php';

// 1. SEGURIDAD
$rol = $_SESSION['rol'] ?? '';
if ($rol != 'PROVEEDOR' && $rol != 'ADMIN') {
    header('Location: login.php'); 
    exit();
}

// Lógica para volver
if ($rol == 'ADMIN') {
    $rutaVolver = 'gestion_productos.php';
} else {
    $rutaVolver = 'dashboard_proveedor.php';
}

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: $rutaVolver"); exit(); }

$p = getProductoDetalle($id);
$marcas = getMarcas(); 

if (!$p) { die("Producto no encontrado"); }

$mensaje = "";
$error = "";

// PROCESAR ACTUALIZACIÓN
if (isset($_POST['actualizar'])) {
    
    $rutaImagen = ""; 
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = time() . "_" . basename($_FILES['foto']['name']);
        $directorio = "images/"; 
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $directorio . $nombreArchivo)) {
            $rutaImagen = $directorio . $nombreArchivo;
        }
    }

    $datos = [
        'descripcion'   => $_POST['descripcion'],
        'id_marca'      => $_POST['marca'],
        'precio_compra' => $_POST['precio_coste'],
        'precio_venta'  => $_POST['precio_publico'],
        'stock'         => $_POST['stock'],
        'imagen'        => $rutaImagen,
        'categoria'     => $p['CATEGORIA'], 

        // Específicos
        'socket'        => $_POST['socket'] ?? null,
        'formato'       => $_POST['formato'] ?? null,
        'nucleos'       => $_POST['nucleos'] ?? null,
        'frecuencia'    => $_POST['frecuencia'] ?? null,
        'tipo_memoria'  => $_POST['tipo_memoria'] ?? null,
        'capacidad_ram' => $_POST['capacidad_ram'] ?? null,
        'dimensiones'   => $_POST['dimensiones'] ?? null,
        'puertos'       => $_POST['puertos'] ?? null,
        'tipo_refrig'   => $_POST['tipo_refrig'] ?? null,
        'tamano_refrig' => $_POST['tamano_refrig'] ?? null,
        'certificacion' => $_POST['certificacion'] ?? null,
        'potencia'      => $_POST['potencia'] ?? null,
        'vram'          => $_POST['vram'] ?? null,
        'tipo_disco'    => $_POST['tipo_disco'] ?? null,
        'capacidad_disco'=> $_POST['capacidad_disco'] ?? null,
        'tipo_teclado'  => $_POST['tipo_teclado'] ?? null,
        'cable_teclado' => $_POST['cable_teclado'] ?? null,
        'dpi'           => $_POST['dpi'] ?? null,
        'tipo_raton'    => $_POST['tipo_raton'] ?? null,
        'hz'            => $_POST['hz'] ?? null,
        'medidas'       => $_POST['medidas'] ?? null
    ];

    if (actualizarProducto($id, $datos, $rol)) {
        $mensaje = "¡Producto actualizado correctamente!";
        $p = getProductoDetalle($id); 
    } else {
        $error = "Error al actualizar (Revisa stock o conexión).";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

    <?php include 'menu.php'; ?>

    <div class="container">
        <div class="page-header">
            <a href="<?= $rutaVolver ?>" class="btn btn-secondary">Volver</a>
        </div>
        
        <div class="container-edit">
            <div class="card">
                <?php if($mensaje): ?><div class="alert alert-success">✅ <?= $mensaje ?></div><?php endif; ?>
                <?php if($error): ?><div class="alert alert-error">❌ <?= $error ?></div><?php endif; ?>

                <?php if($rol == 'ADMIN'): ?>
                    <div class="notice-admin">
                         <strong>Modo Admin:</strong> Editando producto del proveedor.
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    
                    <h3 class="form-section-title"> Datos Genéricos (<?= $p['CATEGORIA'] ?>)</h3>

                    <div class="form-group">
                        <label>Nombre / Descripción</label>
                        <input type="text" name="descripcion" value="<?= $p['DESCRIPCION'] ?>" required>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label>Marca</label>
                            <select name="marca" required>
                                <?php foreach($marcas as $m): ?>
                                    <option value="<?= $m['ID_MARCA'] ?>" <?= ($m['ID_MARCA'] == $p['ID_MARCA']) ? 'selected' : '' ?>>
                                        <?= $m['NOMBRE'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Stock (Solo aumentar)</label>
                            <input type="number" name="stock" value="<?= $p['STOCK'] ?>" required min="0">
                        </div>
                    </div>

                    <div class="grid-2 panel-especifico <?= ($p['CATEGORIA'] == 'PLACA_BASE') ? 'panel-visible' : '' ?>">
                        <div class="form-group"><label>Socket</label><input type="text" name="socket" value="<?= $p['SOCKET'] ?? '' ?>"></div>
                        <div class="form-group"><label>Factor Forma</label><input type="text" name="formato" value="<?= $p['FACTOR_DE_FORMA'] ?? '' ?>"></div>
                    </div>

                    <div class="grid-2 panel-especifico <?= ($p['CATEGORIA'] == 'PROCESADOR') ? 'panel-visible' : '' ?>">
                        <div class="form-group"><label>Nº Núcleos</label><input type="number" name="nucleos" value="<?= $p['NUMERO_DE_NUCLEOS'] ?? '' ?>"></div>
                        <div class="form-group"><label>Frecuencia</label><input type="text" name="frecuencia" value="<?= $p['FRECUENCIA_BASE'] ?? '' ?>"></div>
                    </div>

                    <div class="grid-2 panel-especifico <?= ($p['CATEGORIA'] == 'RAM') ? 'panel-visible' : '' ?>">
                        <div class="form-group"><label>Tipo</label><input type="text" name="tipo_memoria" value="<?= $p['TIPO'] ?? '' ?>"></div>
                        <div class="form-group"><label>Capacidad</label><input type="text" name="capacidad_ram" value="<?= $p['CAPACIDAD'] ?? '' ?>"></div>
                    </div>

                    <div class="grid-2 panel-especifico <?= ($p['CATEGORIA'] == 'CAJA') ? 'panel-visible' : '' ?>">
                        <div class="form-group"><label>Dimensiones</label><input type="text" name="dimensiones" value="<?= $p['DIMENSIONES'] ?? '' ?>"></div>
                        <div class="form-group"><label>Puertos</label><input type="text" name="puertos" value="<?= $p['PUERTOS_FRONTALES'] ?? '' ?>"></div>
                    </div>

                    <div class="grid-2 panel-especifico <?= ($p['CATEGORIA'] == 'TARJETA_GRAFICA') ? 'panel-visible' : '' ?>">
                        <div class="form-group"><label>VRAM</label><input type="text" name="vram" value="<?= $p['VRAM'] ?? '' ?>"></div>
                    </div>

                    <div class="grid-2 panel-especifico <?= ($p['CATEGORIA'] == 'DISCO_DURO') ? 'panel-visible' : '' ?>">
                        <div class="form-group"><label>Tipo</label><input type="text" name="tipo_disco" value="<?= $p['TIPO_DE_ALMACENAMIENTO'] ?? '' ?>"></div>
                        <div class="form-group"><label>Capacidad</label><input type="text" name="capacidad_disco" value="<?= $p['CAPACIDAD'] ?? '' ?>"></div>
                    </div>

                    <div class="grid-2 panel-especifico <?= ($p['CATEGORIA'] == 'MONITOR') ? 'panel-visible' : '' ?>">
                        <div class="form-group"><label>Hz</label><input type="text" name="hz" value="<?= $p['HZ'] ?? '' ?>"></div>
                        <div class="form-group"><label>Medidas</label><input type="text" name="medidas" value="<?= $p['MEDIDAS'] ?? '' ?>"></div>
                    </div>

                    <div class="grid-2 panel-especifico <?= ($p['CATEGORIA'] == 'TECLADO') ? 'panel-visible' : '' ?>">
                        <div class="form-group"><label>Tipo</label><input type="text" name="tipo_teclado" value="<?= $p['TIPO'] ?? '' ?>"></div>
                        <div class="form-group"><label>Cable</label><input type="text" name="cable_teclado" value="<?= $p['TIPO_CABLE'] ?? '' ?>"></div>
                    </div>

                    <div class="grid-2 panel-especifico <?= ($p['CATEGORIA'] == 'RATON') ? 'panel-visible' : '' ?>">
                        <div class="form-group"><label>DPI</label><input type="text" name="dpi" value="<?= $p['DPI'] ?? '' ?>"></div>
                        <div class="form-group"><label>Tipo</label><input type="text" name="tipo_raton" value="<?= $p['TIPO'] ?? '' ?>"></div>
                    </div>

                    <div class="grid-2 panel-especifico <?= ($p['CATEGORIA'] == 'REFRIGERACION') ? 'panel-visible' : '' ?>">
                        <div class="form-group"><label>Tipo</label><input type="text" name="tipo_refrig" value="<?= $p['TIPO'] ?? '' ?>"></div>
                        <div class="form-group"><label>Tamaño</label><input type="text" name="tamano_refrig" value="<?= $p['TAMANO'] ?? '' ?>"></div>
                    </div>

                    <div class="grid-2 panel-especifico <?= ($p['CATEGORIA'] == 'FUENTE_ALIMENTACION') ? 'panel-visible' : '' ?>">
                        <div class="form-group"><label>Certificación</label><input type="text" name="certificacion" value="<?= $p['CERTIFICACION_ENERGETICA'] ?? '' ?>"></div>
                        <div class="form-group"><label>Potencia</label><input type="text" name="potencia" value="<?= $p['POTENCIA'] ?? '' ?>"></div>
                    </div>

                    <h3 class="form-section-title">Precios y Foto</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Coste Compra (€)</label>
                            <input type="number" step="0.01" name="precio_coste" value="<?= $p['PRECIO_COMPRA'] ?? 0 ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>PVP Venta (€) <?php if($rol!='ADMIN') echo '<small>(Solo Admin)</small>'; ?></label>
                            <input type="number" step="0.01" name="precio_publico" value="<?= $p['PRECIO_VENTA'] ?>" 
                                   required 
                                   <?= ($rol != 'ADMIN') ? 'readonly' : '' ?> >
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Cambiar Imagen</label>
                        <?php if(!empty($p['IMAGEN'])): ?>
                            <div class="img-preview"><img src="<?= $p['IMAGEN'] ?>" alt="Imagen Actual"></div>
                        <?php endif; ?>
                        <input type="file" name="foto" accept="image/*">
                    </div>

                    <button type="submit" name="actualizar" class="btn btn-primary btn-lg">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>