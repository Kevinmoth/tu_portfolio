<?php
session_start();
require_once '../includes/conexion.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['admin_logueado']) && $_SESSION['admin_logueado'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = limpiar_entrada($_POST['usuario']);
    $password = $_POST['password'];
    
    if (empty($usuario) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        // Buscar usuario
        $stmt = $conn->prepare("SELECT id, usuario, password FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            $user = $resultado->fetch_assoc();
            
            // Verificar contraseña
            if (password_verify($password, $user['password'])) {
                // Login exitoso
                $_SESSION['admin_logueado'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_usuario'] = $user['usuario'];
                
                // Actualizar último acceso
                $update_stmt = $conn->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Usuario o contraseña incorrectos.';
            }
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Panel Administrativo</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        :root {
            --primary-blue: #4a90e2;
            --dark-blue: #2c5aa0;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-shield-lock-fill"></i>
            <h2 class="mb-0">Panel Administrativo</h2>
            <p class="mb-0 mt-2">Ingresa tus credenciales</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="mb-4">
                    <label for="usuario" class="form-label">
                        <i class="bi bi-person-fill"></i> Usuario
                    </label>
                    <input type="text" 
                           class="form-control form-control-lg" 
                           id="usuario" 
                           name="usuario" 
                           placeholder="Ingresa tu usuario"
                           required 
                           autofocus>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock-fill"></i> Contraseña
                    </label>
                    <input type="password" 
                           class="form-control form-control-lg" 
                           id="password" 
                           name="password" 
                           placeholder="Ingresa tu contraseña"
                           required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100 btn-lg">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                </button>
            </form>
            
            <div class="text-center mt-4">
                <a href="../index.php" class="text-decoration-none" style="color: var(--primary-blue);">
                    <i class="bi bi-arrow-left"></i> Volver al sitio
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>