<?php
session_start();
require_once 'bbdd.php';

if (!isset($_SESSION['rol'])) {
    header('Location: login.php'); exit();
}

$rol = $_SESSION['rol'];
$marcas = getMarcas(); 

$mensaje = "";
$error = "";

if (isset($_POST['guardar'])) {
    
    $rutaImagen = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = time() . "_" . basename($_FILES['foto']['name']);
        $directorio = "images/"; 
        if (!is_dir($directorio)) { mkdir($directorio, 0777, true); }
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $directorio . $nombreArchivo)) {
            $rutaImagen = $directorio . $nombreArchivo;
        }
    }

    $precioVenta = ($rol == 'ADMIN') ? $_POST['precio_publico'] : 0;

    // Recoger datos 
    $datos = [
        // Comunes
        'descripcion'   => $_POST['descripcion'],
        'id_marca'      => $_POST['marca'],
        'precio_compra' => $_POST['precio_coste'],
        'precio_venta'  => $precioVenta, 
        'stock'         => $_POST['stock'],
        'imagen'        => $rutaImagen,
        'categoria'     => $_POST['categoria'],
        
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

    if (insertarProducto($datos, $_SESSION['usuario'])) {
        $mensaje = "Producto guardado correctamente";
    } else {
        $error = "Hubo un error al guardar. Revisa los datos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Producto Completo</title>
    <link rel="stylesheet" href="css/estilos.css">
    <script>
        function mostrarCampos() {
            var cat = document.getElementById('select_categoria').value;
            
            var ids = ['campos_placa', 'campos_cpu', 'campos_ram', 'campos_caja', 
                       'campos_refrig', 'campos_fuente', 'campos_gpu', 'campos_disco',
                       'campos_teclado', 'campos_raton', 'campos_monitor'];
            
            ids.forEach(function(id) {
                var el = document.getElementById(id);
                if(el) el.style.display = 'none';
            });
            
            if(cat === 'PLACA_BASE') document.getElementById('campos_placa').style.display = 'grid';
            if(cat === 'PROCESADOR') document.getElementById('campos_cpu').style.display = 'grid';
            if(cat === 'RAM') document.getElementById('campos_ram').style.display = 'grid';
            if(cat === 'CAJA') document.getElementById('campos_caja').style.display = 'grid';
            if(cat === 'REFRIGERACION') document.getElementById('campos_refrig').style.display = 'grid';
            if(cat === 'FUENTE_ALIMENTACION') document.getElementById('campos_fuente').style.display = 'grid';
            if(cat === 'TARJETA_GRAFICA') document.getElementById('campos_gpu').style.display = 'grid';
            if(cat === 'DISCO_DURO') document.getElementById('campos_disco').style.display = 'grid';
            if(cat === 'TECLADO') document.getElementById('campos_teclado').style.display = 'grid';
            if(cat === 'RATON') document.getElementById('campos_raton').style.display = 'grid';
            if(cat === 'MONITOR') document.getElementById('campos_monitor').style.display = 'grid';
        }
    </script>
</head>
<body>

    <?php include 'menu.php'; ?>

    <div class="container">
        <div class="page-header">
            <h2>Añadir Componente</h2>
            <a href="<?= ($rol == 'ADMIN') ? 'panel_admin.php' : 'dashboard_proveedor.php' ?>" class="btn btn-secondary">Volver</a>
        </div>
        
        <div class="container-edit"> <div class="card">
                <?php if($mensaje): ?>
                    <div class="alert alert-success">✅ <?= $mensaje ?></div>
                    <div class="text-center"><a href="nuevo_producto.php" class="btn btn-primary" style="width:auto;">Añadir otro</a></div>
                <?php else: ?>

                <?php if($error): ?><div class="alert alert-error">❌ <?= $error ?></div><?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    
                    <h3 class="form-section-title">Datos Genéricos</h3>
                    
                    <div class="form-group">
                        <label style="color:var(--primary); font-weight:bold;">Categoría del Componente</label>
                        <select name="categoria" id="select_categoria" required onchange="mostrarCampos()" style="border: 2px solid var(--primary);">
                            <option value="">-- Selecciona --</option>
                            <option value="PLACA_BASE">Placa Base</option>
                            <option value="PROCESADOR">Procesador (CPU)</option>
                            <option value="RAM">Memoria RAM</option>
                            <option value="CAJA">Caja / Torre</option>
                            <option value="REFRIGERACION">Refrigeración</option>
                            <option value="FUENTE_ALIMENTACION">Fuente de Alimentación</option>
                            <option value="TARJETA_GRAFICA">Tarjeta Gráfica (GPU)</option>
                            <option value="DISCO_DURO">Disco Duro / SSD</option>
                            <option value="TECLADO">Teclado</option>
                            <option value="RATON">Ratón</option>
                            <option value="MONITOR">Monitor</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Nombre / Descripción</label>
                        <input type="text" name="descripcion" required placeholder="Ej: Intel Core i7 12700K">
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label>Marca</label>
                            <select name="marca" required>
                                <?php foreach($marcas as $m): ?>
                                    <option value="<?= $m['ID_MARCA'] ?>"><?= $m['NOMBRE'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" name="stock" required min="1" value="1">
                        </div>
                    </div>

                    <div id="campos_placa" class="grid-2 panel-especifico">
                        <div class="form-group"><label>Socket</label><input type="text" name="socket" placeholder="Ej: AM4, LGA1700"></div>
                        <div class="form-group"><label>Factor Forma</label><input type="text" name="formato" placeholder="Ej: ATX"></div>
                    </div>

                    <div id="campos_cpu" class="grid-2 panel-especifico">
                        <div class="form-group"><label>Nº Núcleos</label><input type="number" name="nucleos"></div>
                        <div class="form-group"><label>Frecuencia Base</label><input type="text" name="frecuencia" placeholder="Ej: 3.6 GHz"></div>
                    </div>

                    <div id="campos_ram" class="grid-2 panel-especifico">
                        <div class="form-group"><label>Tipo (DDR)</label><input type="text" name="tipo_memoria" placeholder="Ej: DDR4"></div>
                        <div class="form-group"><label>Capacidad</label><input type="text" name="capacidad_ram" placeholder="Ej: 16GB"></div>
                    </div>

                    <div id="campos_caja" class="grid-2 panel-especifico">
                        <div class="form-group"><label>Dimensiones</label><input type="text" name="dimensiones"></div>
                        <div class="form-group"><label>Puertos Frontales</label><input type="text" name="puertos"></div>
                    </div>

                    <div id="campos_refrig" class="grid-2 panel-especifico">
                        <div class="form-group"><label>Tipo</label><input type="text" name="tipo_refrig" placeholder="Aire / Líquida"></div>
                        <div class="form-group"><label>Tamaño</label><input type="text" name="tamano_refrig"></div>
                    </div>

                    <div id="campos_fuente" class="grid-2 panel-especifico">
                        <div class="form-group"><label>Certificación</label><input type="text" name="certificacion" placeholder="Ej: 80 Plus Gold"></div>
                        <div class="form-group"><label>Potencia (W)</label><input type="number" name="potencia"></div>
                    </div>

                    <div id="campos_gpu" class="grid-2 panel-especifico">
                        <div class="form-group"><label>VRAM</label><input type="text" name="vram" placeholder="Ej: 8GB GDDR6"></div>
                    </div>

                    <div id="campos_disco" class="grid-2 panel-especifico">
                        <div class="form-group"><label>Tipo Almacenamiento</label><input type="text" name="tipo_disco" placeholder="SSD / HDD"></div>
                        <div class="form-group"><label>Capacidad</label><input type="text" name="capacidad_disco"></div>
                    </div>

                    <div id="campos_teclado" class="grid-2 panel-especifico">
                        <div class="form-group"><label>Tipo</label><input type="text" name="tipo_teclado" placeholder="Mecánico / Membrana"></div>
                        <div class="form-group"><label>Cable</label><input type="text" name="cable_teclado" placeholder="USB / Inalámbrico"></div>
                    </div>

                    <div id="campos_raton" class="grid-2 panel-especifico">
                        <div class="form-group"><label>DPI</label><input type="number" name="dpi"></div>
                        <div class="form-group"><label>Tipo</label><input type="text" name="tipo_raton" placeholder="Óptico / Láser"></div>
                    </div>

                    <div id="campos_monitor" class="grid-2 panel-especifico">
                        <div class="form-group"><label>Hz (Refresco)</label><input type="number" name="hz"></div>
                        <div class="form-group"><label>Medidas (Pulgadas)</label><input type="text" name="medidas"></div>
                    </div>

                    <h3 class="form-section-title"> Precios y Foto</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Coste Compra (€)</label>
                            <input type="number" step="0.01" name="precio_coste" required placeholder="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label>PVP Venta (€) <?= ($rol!='ADMIN') ? '<small style="color:var(--text-light)">(Solo Admin)</small>' : '' ?></label>
                            
                            <?php if($rol == 'ADMIN'): ?>
                                <input type="number" step="0.01" name="precio_publico" required>
                            <?php else: ?>
                                <input type="text" value="Pendiente de asignar" readonly style="background-color:#f1f5f9; color:#94a3b8; cursor:not-allowed; border-color:#e2e8f0;">
                                <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Imagen</label>
                        <input type="file" name="foto" accept="image/*" style="border: 1px dashed #cbd5e1; padding: 15px;">
                    </div>

                    <button type="submit" name="guardar" class="btn btn-primary btn-lg" style="margin-top:1rem;">Guardar en Inventario</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>