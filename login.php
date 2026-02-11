<?php
session_start();
ini_set('display_errors', 1); 
error_reporting(E_ALL);

require_once 'bbdd.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    
    $resultado = verificarLogin($usuario, $password);

    if (isset($resultado['rol'])) {
        $_SESSION['rol'] = $resultado['rol'];
        
        if ($resultado['rol'] == 'ADMIN') {
            $_SESSION['usuario'] = $resultado['datos']['USUARIO_TRABAJADOR'] ?? $usuario;
            header('Location: dashboard_admin.php'); 
        } else {
            $_SESSION['usuario'] = $resultado['datos']['USUARIO_PROVEEDOR'] ?? $usuario;
            header('Location: dashboard_proveedor.php'); 
        }
        exit();

    } elseif (isset($resultado['error'])) {
        $error = $resultado['error'];
    } else {
        $error = "Error desconocido.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso | Tienda Informática</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-page">
    
    <header class="login-header">
        <a href="index.php" class="header-logo">
            <img src="images/logo_tienda.png" alt="TiendaComponentes">
        </a>        
        <a href="registro.php" class="header-link">Crear cuenta de Proveedor</a>
    </header>

    <div class="main-login">
        <div class="login-container">
            <div class="card">
                <h2 class="login-title">Bienvenido</h2>
                <p class="login-subtitle">Introduce tus credenciales para acceder</p>
                
                <?php if(!empty($error)): ?>
                    <div class="alert alert-error">
                        ⚠️ <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label class="login-label">Usuario</label>
                        <input type="text" name="usuario" class="login-input" required placeholder="Ej: admin">
                    </div>
                    <div class="form-group">
                        <label class="login-label">Contraseña</label>
                        <input type="password" name="password" class="login-input" required placeholder="••••••••">
                    </div>
                    
                    <button type="submit" name="entrar" class="btn btn-primary btn-login-full">Iniciar Sesión</button>
                </form>
                
                
            </div>
        </div>
    </div>

    <div class="footer-text">
        &copy; 2026 Tienda Informática System
    </div>

</body>
</html>