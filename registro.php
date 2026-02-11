<?php
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'bbdd.php';

$exito = "";
$error = "";

if (isset($_POST['registrar'])) {
    $cif = $_POST['cif'];
    $nombre = $_POST['nombre'];
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];
    $direccion = $_POST['direccion']; 
    
    if (registrarProveedor($cif, $nombre, $usuario, $contrasena, $direccion)) {
        $exito = "¡Solicitud enviada correctamente! Un administrador revisará tu cuenta pronto.";
    } else {
        $error = "Error al registrar. Verifica que el CIF o el Usuario no existan ya.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/registro.css">
    <title>Registro Proveedor | Tienda Informática</title>
</head>
<body class="login-page">

    <header class="login-header">
        <a href="index.php" class="header-logo">
            <img src="images/logo_tienda.png" alt="TiendaComponentes">
        </a>        
        <a href="login.php" class="header-link">Iniciar sesion</a>
    </header>

    <div class="main-login">
        <div class="login-container">
            <div class="card">
                <h2 class="card-title">Alta de Proveedor</h2>
                <p class="card-subtitle">
                    Únete a nuestra plataforma para vender tus productos
                </p>
                
                <?php if(!empty($exito)): ?>
                    <div class="alert alert-success">✅ <?= $exito ?></div>
                    <p style="text-align: center; margin-top: 1.5rem;">
                        <a href="login.php" class="btn">Ir al Login</a>
                    </p>
                <?php endif; ?>

                <?php if(!empty($error)): ?>
                    <div class="alert alert-error">❌ <?= $error ?></div>
                <?php endif; ?>
                
                <?php if(empty($exito)): ?>
                <form method="POST">
                    
                    <h3 class="form-section-title"> Datos de la Empresa</h3>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label>CIF / NIF</label>
                            <input type="text" name="cif" required placeholder="Ej: B12345678">
                        </div>
                        <div class="form-group">
                            <label>Nombre Fiscal</label>
                            <input type="text" name="nombre" required placeholder="Ej: Componentes PC S.L.">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Dirección Física</label>
                        <input type="text" name="direccion" required placeholder="C/ Ejemplo, 123, Madrid">
                    </div>

                    <h3 class="form-section-title"> Datos de Acceso</h3>

                    <div class="grid-2">
                        <div class="form-group">
                            <label>Usuario (Login)</label>
                            <input type="text" name="usuario" required placeholder="Usuario único">
                        </div>
                        <div class="form-group">
                            <label>Contraseña</label>
                            <input type="password" name="contrasena" required placeholder="••••••••">
                        </div>
                    </div>
                    
                    <button type="submit" name="registrar" class="btn btn-success" style="margin-top: 2rem;">
                        Solicitar Registro
                    </button>
                    
                    <p class="legal-text">
                        Al registrarte, aceptas que tu cuenta quedará en estado <strong>"Pendiente"</strong> hasta ser validada por un administrador del sistema.
                    </p>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>