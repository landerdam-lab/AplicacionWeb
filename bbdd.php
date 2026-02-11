<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- CONFIGURACIÓN DE CONEXIÓN ---
define('DB_HOST', '192.168.1.137/ORCLCDB'); 
define('DB_USER', 'tienda_informatica');
define('DB_PASS', 'Almi12345');

function getConexion() {
    $conn = oci_connect(DB_USER, DB_PASS, DB_HOST, 'AL32UTF8');
    if (!$conn) {
        $e = oci_error();
        die("Error de conexión: " . $e['message']);
    }
    return $conn;
}

// --- VERIFICACIÓN DE LOGIN ---
function verificarLogin($usuario, $password) {
    $conn = getConexion();

    // 1. Verificar Trabajador
    $sql = "SELECT * FROM TRABAJADOR WHERE USUARIO_TRABAJADOR = :usr";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':usr', $usuario);
    oci_execute($stmt);
    $trabajador = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS);
    
    if ($trabajador) {
        $passDB = $trabajador['CONTRASEÑA_TRABAJADOR'] ?? $trabajador['CONTRASENA_TRABAJADOR'] ?? '';
        if (password_verify($password, $passDB) || $password === $passDB) {
            if ($trabajador['ES_ADMIN'] == 1 || $trabajador['ES_ADMIN'] == 'S') {
                oci_close($conn);
                return ['rol' => 'ADMIN', 'datos' => $trabajador];
            }
        }
    }

    $sql2 = "SELECT * FROM PROVEEDOR WHERE USUARIO_PROVEEDOR = :usr";
    $stmt2 = oci_parse($conn, $sql2);
    oci_bind_by_name($stmt2, ':usr', $usuario);
    oci_execute($stmt2);
    $proveedor = oci_fetch_array($stmt2, OCI_ASSOC + OCI_RETURN_NULLS);

    if ($proveedor) {
        $passDB = $proveedor['CONTRASEÑA_PROVEEDOR'] ?? $proveedor['CONTRASENA_PROVEEDOR'] ?? '';
        if (password_verify($password, $passDB) || $password === $passDB) {
            if ($proveedor['ESTADO'] === 'ACTIVO' || $proveedor['ESTADO'] === 'ACEPTADO') {
                oci_close($conn);
                return ['rol' => 'PROVEEDOR', 'datos' => $proveedor];
            } else {
                oci_close($conn);
                return ['error' => 'Tu cuenta aún no está activa.'];
            }
        }
    }

    oci_close($conn);
    return ['error' => 'Credenciales incorrectas.'];
}

function getMarcas() {
    $conn = getConexion();
    $sql = "SELECT * FROM MARCA ORDER BY NOMBRE ASC";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $res = [];
    while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) { 
        $res[] = $row; 
    }
    oci_close($conn);
    return $res;
}


function registrarProveedor($cif, $nombre, $user, $pass, $direccion) {
    $conn = getConexion();
    $passHash = password_hash($pass, PASSWORD_DEFAULT);
    $estado = 'PENDIENTE';

    $sql = "INSERT INTO PROVEEDOR (CIF, NOMBRE, DIRECCION, USUARIO_PROVEEDOR, CONTRASEÑA_PROVEEDOR, ESTADO) 
            VALUES (:cif, :nombre, :dir, :usr, :pass, :est)";
    
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':cif', $cif);
    oci_bind_by_name($stmt, ':nombre', $nombre);
    oci_bind_by_name($stmt, ':dir', $direccion);
    oci_bind_by_name($stmt, ':usr', $user);
    oci_bind_by_name($stmt, ':pass', $passHash); 
    oci_bind_by_name($stmt, ':est', $estado);

    $res = oci_execute($stmt); 
    oci_close($conn);
    return $res;
}

function getProveedoresPendientes() {
    $conn = getConexion();
    $sql = "SELECT * FROM PROVEEDOR WHERE UPPER(TRIM(ESTADO)) = 'PENDIENTE' ORDER BY NOMBRE";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $res = [];
    while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $res[] = $row;
    }
    oci_close($conn);
    return $res;
}

function getProveedoresActivos() {
    $conn = getConexion();
    $sql = "SELECT * FROM PROVEEDOR WHERE UPPER(TRIM(ESTADO)) IN ('ACTIVO', 'ACEPTADO')";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $res = [];
    while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) { 
        $res[] = $row; 
    }
    oci_close($conn);
    return $res;
}

function cambiarEstadoProveedor($cif, $nuevoEstado) {
    $conn = getConexion();
    $sql = "UPDATE PROVEEDOR SET ESTADO = :estado WHERE CIF = :cif";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':estado', $nuevoEstado);
    oci_bind_by_name($stmt, ':cif', $cif);
    $res = oci_execute($stmt);
    oci_close($conn);
    return $res;
}

function eliminarProveedor($cif) {
    $conn = getConexion();
    $sql = "DELETE FROM PROVEEDOR WHERE CIF = :cif";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':cif', $cif);
    $res = oci_execute($stmt);
    oci_close($conn);
    return $res;
}

function getAllProductosAdmin() {
    $conn = getConexion();
    $sql = "SELECT c.ID_COMPONENTE, 
                   c.NOMBRE AS DESCRIPCION, 
                   c.STOCK, 
                   c.IMAGEN, 
                   c.PRECIO_VENTA,
                   cp.PRECIO_COMPRA, 
                   p.NOMBRE AS NOMBRE_PROVEEDOR
            FROM COMPONENTE c
            LEFT JOIN COMPONENTE_PROVEEDOR cp ON c.ID_COMPONENTE = cp.ID_COMPONENTE
            LEFT JOIN PROVEEDOR p ON cp.CIF_PROVEEDOR = p.CIF
            ORDER BY c.ID_COMPONENTE DESC";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $res = [];
    while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $res[] = $row;
    }
    oci_close($conn);
    return $res;
}

function getMisProductos($usuarioProveedor) {
    $conn = getConexion();
    $sql = "SELECT c.*, cp.PRECIO_COMPRA 
            FROM COMPONENTE c 
            JOIN COMPONENTE_PROVEEDOR cp ON c.ID_COMPONENTE = cp.ID_COMPONENTE
            JOIN PROVEEDOR p ON cp.CIF_PROVEEDOR = p.CIF
            WHERE p.USUARIO_PROVEEDOR = :usr
            ORDER BY c.ID_COMPONENTE DESC";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':usr', $usuarioProveedor);
    oci_execute($stmt);
    $res = [];
    while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) { 
        $res[] = $row; 
    }
    oci_close($conn);
    return $res;
}

function borrarProducto($id) {
    $conn = getConexion();
    
    $sql1 = "DELETE FROM COMPONENTE_PROVEEDOR WHERE ID_COMPONENTE = :id";
    $stmt1 = oci_parse($conn, $sql1);
    oci_bind_by_name($stmt1, ':id', $id);
    @oci_execute($stmt1, OCI_NO_AUTO_COMMIT);
    
    $tablas = ['PLACA_BASE', 'PROCESADOR', 'RAM', 'CAJA', 'REFRIGERACION', 
               'FUENTE_ALIMENTACION', 'TARJETA_GRAFICA', 'DISCO_DURO', 
               'TECLADO', 'RATON', 'MONITOR', 'PORTATIL'];
    
    foreach ($tablas as $tabla) {
        $sqlT = "DELETE FROM $tabla WHERE ID_COMPONENTE = :id";
        $stmtT = oci_parse($conn, $sqlT);
        oci_bind_by_name($stmtT, ':id', $id);
        @oci_execute($stmtT, OCI_NO_AUTO_COMMIT);
    }
    
    $sqlPC = "DELETE FROM PEDIDO_COMPONENTE WHERE ID_COMPONENTE = :id";
    $stmtPC = oci_parse($conn, $sqlPC);
    oci_bind_by_name($stmtPC, ':id', $id);
    @oci_execute($stmtPC, OCI_NO_AUTO_COMMIT);
    
    $sql2 = "DELETE FROM COMPONENTE WHERE ID_COMPONENTE = :id";
    $stmt2 = oci_parse($conn, $sql2);
    oci_bind_by_name($stmt2, ':id', $id);
    
    if (!@oci_execute($stmt2, OCI_NO_AUTO_COMMIT)) {
        $e = oci_error($stmt2);
        oci_rollback($conn);
        oci_close($conn);
        return $e['message'];
    }
    
    oci_commit($conn);
    oci_close($conn);
    return true;
}

function insertarProducto($datos, $usuarioProveedor) {
    $conn = getConexion();

    $sqlProv = "SELECT cif FROM proveedor WHERE usuario_proveedor = :usr";
    $stmtProv = oci_parse($conn, $sqlProv);
    oci_bind_by_name($stmtProv, ':usr', $usuarioProveedor);
    oci_execute($stmtProv);
    $rowProv = oci_fetch_array($stmtProv, OCI_ASSOC);
    
    if (!$rowProv) { 
        oci_close($conn); return false; 
    }
    $cif = $rowProv['CIF'];

    $sql1 = "INSERT INTO componente (nombre, descripcion, precio_venta, imagen, id_marca, stock) 
             VALUES (:nom, :dscr, :prcv, :imgn, :idmk, :stk) 
             RETURNING id_componente INTO :idn";
    
    $stmt1 = oci_parse($conn, $sql1);
    $idNuevo = 0;

    oci_bind_by_name($stmt1, ':nom',  $datos['descripcion']);
    oci_bind_by_name($stmt1, ':dscr', $datos['descripcion']);
    oci_bind_by_name($stmt1, ':prcv', $datos['precio_venta']);
    oci_bind_by_name($stmt1, ':imgn', $datos['imagen']);
    oci_bind_by_name($stmt1, ':idmk', $datos['id_marca']);
    oci_bind_by_name($stmt1, ':stk',  $datos['stock']);
    oci_bind_by_name($stmt1, ':idn',  $idNuevo, -1, SQLT_INT);

    if (!@oci_execute($stmt1, OCI_NO_AUTO_COMMIT)) {
        $e = oci_error($stmt1);
        oci_rollback($conn); oci_close($conn); 
        echo "<div style='background:darkred;color:white;padding:20px;'>ERROR SQL1: " . $e['message'] . "</div>";
        return false;
    }

    $sql2 = "INSERT INTO componente_proveedor (cif_proveedor, id_componente, precio_compra) 
             VALUES (:cif_p, :id_c, :prc_c)";
             
    $stmt2 = oci_parse($conn, $sql2);
    oci_bind_by_name($stmt2, ':cif_p', $cif);
    oci_bind_by_name($stmt2, ':id_c', $idNuevo);
    oci_bind_by_name($stmt2, ':prc_c', $datos['precio_compra']);

    if (!@oci_execute($stmt2, OCI_NO_AUTO_COMMIT)) {
        $e = oci_error($stmt2);
        oci_rollback($conn); oci_close($conn); 
        echo "<div style='background:darkred;color:white;padding:20px;'>ERROR SQL2: " . $e['message'] . "</div>";
        return false;
    }

    $categoria = $datos['categoria'];
    $sql3 = "";
    $stmt3 = null;

    switch ($categoria) {
        case 'PLACA_BASE':
            $sql3 = "INSERT INTO placa_base (id_componente, socket, factor_de_forma) VALUES (:id, :p1, :p2)";
            $stmt3 = oci_parse($conn, $sql3);
            oci_bind_by_name($stmt3, ':p1', $datos['socket']);
            oci_bind_by_name($stmt3, ':p2', $datos['formato']);
            break;

        case 'PROCESADOR':
            $sql3 = "INSERT INTO procesador (id_componente, numero_de_nucleos, frecuencia_base) VALUES (:id, :p1, :p2)";
            $stmt3 = oci_parse($conn, $sql3);
            oci_bind_by_name($stmt3, ':p1', $datos['nucleos']);
            oci_bind_by_name($stmt3, ':p2', $datos['frecuencia']);
            break;

        case 'RAM':
            $sql3 = "INSERT INTO ram (id_componente, tipo, capacidad) VALUES (:id, :p1, :p2)";
            $stmt3 = oci_parse($conn, $sql3);
            oci_bind_by_name($stmt3, ':p1', $datos['tipo_memoria']);
            oci_bind_by_name($stmt3, ':p2', $datos['capacidad_ram']);
            break;

        case 'CAJA':
            $sql3 = "INSERT INTO caja (id_componente, dimensiones, puertos_frontales) VALUES (:id, :p1, :p2)";
            $stmt3 = oci_parse($conn, $sql3);
            oci_bind_by_name($stmt3, ':p1', $datos['dimensiones']);
            oci_bind_by_name($stmt3, ':p2', $datos['puertos']);
            break;

        case 'REFRIGERACION':
            $sql3 = "INSERT INTO refrigeracion (id_componente, tipo, tamano) VALUES (:id, :p1, :p2)";
            $stmt3 = oci_parse($conn, $sql3);
            oci_bind_by_name($stmt3, ':p1', $datos['tipo_refrig']);
            oci_bind_by_name($stmt3, ':p2', $datos['tamano_refrig']);
            break;

        case 'FUENTE_ALIMENTACION':
            $sql3 = "INSERT INTO fuente_alimentacion (id_componente, certificacion_energetica, potencia) VALUES (:id, :p1, :p2)";
            $stmt3 = oci_parse($conn, $sql3);
            oci_bind_by_name($stmt3, ':p1', $datos['certificacion']);
            oci_bind_by_name($stmt3, ':p2', $datos['potencia']);
            break;

        case 'TARJETA_GRAFICA':
            $sql3 = "INSERT INTO tarjeta_grafica (id_componente, vram) VALUES (:id, :p1)";
            $stmt3 = oci_parse($conn, $sql3);
            oci_bind_by_name($stmt3, ':p1', $datos['vram']);
            break;

        case 'DISCO_DURO':
            $sql3 = "INSERT INTO disco_duro (id_componente, tipo_de_almacenamiento, capacidad) VALUES (:id, :p1, :p2)";
            $stmt3 = oci_parse($conn, $sql3);
            oci_bind_by_name($stmt3, ':p1', $datos['tipo_disco']);
            oci_bind_by_name($stmt3, ':p2', $datos['capacidad_disco']);
            break;
            
        case 'TECLADO':
            $sql3 = "INSERT INTO teclado (id_componente, tipo, tipo_cable) VALUES (:id, :p1, :p2)";
            $stmt3 = oci_parse($conn, $sql3);
            oci_bind_by_name($stmt3, ':p1', $datos['tipo_teclado']);
            oci_bind_by_name($stmt3, ':p2', $datos['cable_teclado']);
            break;

        case 'RATON':
            $sql3 = "INSERT INTO raton (id_componente, dpi, tipo) VALUES (:id, :p1, :p2)";
            $stmt3 = oci_parse($conn, $sql3);
            oci_bind_by_name($stmt3, ':p1', $datos['dpi']);
            oci_bind_by_name($stmt3, ':p2', $datos['tipo_raton']);
            break;

        case 'MONITOR':
            $sql3 = "INSERT INTO monitor (id_componente, hz, medidas) VALUES (:id, :p1, :p2)";
            $stmt3 = oci_parse($conn, $sql3);
            oci_bind_by_name($stmt3, ':p1', $datos['hz']);
            oci_bind_by_name($stmt3, ':p2', $datos['medidas']);
            break;
    }

    if ($stmt3) {
        oci_bind_by_name($stmt3, ':id', $idNuevo);
        if (!@oci_execute($stmt3, OCI_NO_AUTO_COMMIT)) {
            $e = oci_error($stmt3);
            oci_rollback($conn); 
            echo "<div style='background:darkred;color:white;padding:20px;'>ERROR SQL3 ($categoria): " . $e['message'] . "</div>";
            return false;
        }
    }

    oci_commit($conn);
    oci_close($conn);
    return true;
}


function getProductoDetalle($id) {
    $conn = getConexion();
    
    $sql = "SELECT * FROM COMPONENTE WHERE ID_COMPONENTE = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':id', $id);
    oci_execute($stmt);
    $prod = oci_fetch_array($stmt, OCI_ASSOC);
    if (!$prod) return null;

    $tablas = ['PLACA_BASE', 'PROCESADOR', 'RAM', 'CAJA', 'REFRIGERACION', 'FUENTE_ALIMENTACION', 
               'TARJETA_GRAFICA', 'DISCO_DURO', 'TECLADO', 'RATON', 'MONITOR'];

    $prod['CATEGORIA'] = 'DESCONOCIDO';
    foreach ($tablas as $tabla) {
        $sqlS = "SELECT * FROM $tabla WHERE ID_COMPONENTE = :id";
        $stmtS = oci_parse($conn, $sqlS);
        oci_bind_by_name($stmtS, ':id', $id);
        oci_execute($stmtS);
        $extra = oci_fetch_array($stmtS, OCI_ASSOC);
        if ($extra) {
            $prod = array_merge($prod, $extra);
            $prod['CATEGORIA'] = $tabla;
            break;
        }
    }

    $sqlP = "SELECT PRECIO_COMPRA FROM COMPONENTE_PROVEEDOR WHERE ID_COMPONENTE = :id";
    $stmtP = oci_parse($conn, $sqlP);
    oci_bind_by_name($stmtP, ':id', $id);
    oci_execute($stmtP);
    $rel = oci_fetch_array($stmtP, OCI_ASSOC);
    if ($rel) $prod['PRECIO_COMPRA'] = $rel['PRECIO_COMPRA'];

    oci_close($conn);
    return $prod;
}

function actualizarProducto($id, $datos, $rol) {
    $conn = getConexion();

    $sql = "UPDATE COMPONENTE SET NOMBRE = :n, DESCRIPCION = :d, ID_MARCA = :m, STOCK = :s";
    if ($rol == 'ADMIN') {
        $sql .= ", PRECIO_VENTA = :pv";
    }
    if ($datos['imagen'] != "") {
        $sql .= ", IMAGEN = :img";
    }
    $sql .= " WHERE ID_COMPONENTE = :id";

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':n', $datos['descripcion']);
    oci_bind_by_name($stmt, ':d', $datos['descripcion']);
    oci_bind_by_name($stmt, ':m', $datos['id_marca']);
    oci_bind_by_name($stmt, ':s', $datos['stock']);
    oci_bind_by_name($stmt, ':id', $id);
    if ($rol == 'ADMIN') oci_bind_by_name($stmt, ':pv', $datos['precio_venta']);
    if ($datos['imagen'] != "") oci_bind_by_name($stmt, ':img', $datos['imagen']);

    if (!oci_execute($stmt, OCI_NO_AUTO_COMMIT)) { oci_rollback($conn); return false; }

    $sql2 = "UPDATE COMPONENTE_PROVEEDOR SET PRECIO_COMPRA = :pc WHERE ID_COMPONENTE = :id";
    $stmt2 = oci_parse($conn, $sql2);
    oci_bind_by_name($stmt2, ':pc', $datos['precio_compra']);
    oci_bind_by_name($stmt2, ':id', $id);
    if (!oci_execute($stmt2, OCI_NO_AUTO_COMMIT)) { oci_rollback($conn); return false; }


    $stmt3 = null;
    switch ($datos['categoria']) {
        case 'PLACA_BASE':
            $stmt3 = oci_parse($conn, "UPDATE PLACA_BASE SET SOCKET = :p1, FACTOR_DE_FORMA = :p2 WHERE ID_COMPONENTE = :id");
            oci_bind_by_name($stmt3, ':p1', $datos['socket']);
            oci_bind_by_name($stmt3, ':p2', $datos['formato']);
            break;
        case 'PROCESADOR':
            $stmt3 = oci_parse($conn, "UPDATE PROCESADOR SET NUMERO_DE_NUCLEOS = :p1, FRECUENCIA_BASE = :p2 WHERE ID_COMPONENTE = :id");
            oci_bind_by_name($stmt3, ':p1', $datos['nucleos']);
            oci_bind_by_name($stmt3, ':p2', $datos['frecuencia']);
            break;
        case 'RAM':
            $stmt3 = oci_parse($conn, "UPDATE RAM SET TIPO = :p1, CAPACIDAD = :p2 WHERE ID_COMPONENTE = :id");
            oci_bind_by_name($stmt3, ':p1', $datos['tipo_memoria']);
            oci_bind_by_name($stmt3, ':p2', $datos['capacidad_ram']);
            break;

            }

    if ($stmt3) {
        oci_bind_by_name($stmt3, ':id', $id);
        if (!oci_execute($stmt3, OCI_NO_AUTO_COMMIT)) { oci_rollback($conn); return false; }
    }

    oci_commit($conn);
    oci_close($conn);
    return true;
}
?>