<?php
session_start();
require_once '../includes/conexion.php';

// Verificar si está logueado
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: login.php');
    exit;
}

$mensaje_exito = '';
$mensaje_error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actualizado = 0;
    
    foreach ($_POST as $clave => $valor) {
        if ($clave !== 'submit') {
            $valor_limpio = limpiar_entrada($valor);
            $stmt = $conn->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?");
            $stmt->bind_param("ss", $valor_limpio, $clave);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $actualizado++;
            }
        }
    }
    
    if ($actualizado > 0) {
        $mensaje_exito = "Configuración actualizada exitosamente. ($actualizado cambios)";
    } else {
        $mensaje_error = 'No se realizaron cambios.';
    }
}

// Obtener todas las configuraciones
$configuraciones = $conn->query("SELECT * FROM configuracion ORDER BY clave");

$mensajes_no_leidos = $conn->query("SELECT COUNT(*) as total FROM mensajes_contacto WHERE leido = FALSE")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Panel Administrativo</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-blue: #4a90e2;
            --dark-blue: #2c5aa0;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--dark-blue) 0%, var(--primary-blue) 100%);
            color: white;
            padding: 20px;
            overflow-y: auto;
        }
        
        .sidebar-header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
        }
        
        .sidebar-header h4 {
            margin: 0;
            font-weight: bold;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            padding-left: 20px;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
        }
        
        .top-bar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .config-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .config-section:last-child {
            border-bottom: none;
        }
        
        .config-section h5 {
            color: var(--primary-blue);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>KRON Admin</h4>
            <small><?php echo $_SESSION['admin_usuario']; ?></small>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="subir-proyecto.php">
                    <i class="bi bi-plus-circle"></i> Subir Proyecto
                </a>
            </li>
            <li>
                <a href="editar-proyecto.php">
                    <i class="bi bi-pencil-square"></i> Editar Proyectos
                </a>
            </li>
            <li>
                <a href="mensajes.php">
                    <i class="bi bi-envelope"></i> Mensajes
                    <?php if ($mensajes_no_leidos > 0): ?>
                        <span class="badge bg-danger"><?php echo $mensajes_no_leidos; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="tecnologias.php">
                    <i class="bi bi-gear"></i> Tecnologías
                </a>
            </li>
            <li>
                <a href="configuracion.php" class="active">
                    <i class="bi bi-sliders"></i> Configuración
                </a>
            </li>
            <li>
                <hr style="border-color: rgba(255,255,255,0.2);">
            </li>
            <li>
                <a href="../index.php" target="_blank">
                    <i class="bi bi-globe"></i> Ver Sitio
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h3 class="mb-0">
                <i class="bi bi-sliders"></i> Configuración del Sitio
            </h3>
        </div>
        
        <!-- Mensajes -->
        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> <?php echo $mensaje_exito; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($mensaje_error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $mensaje_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Formulario de Configuración -->
        <div class="form-card">
            <form method="POST" action="configuracion.php">
                
                <!-- Configuración General -->
                <div class="config-section">
                    <h5>
                        <i class="bi bi-info-circle"></i> Información General
                    </h5>
                    
                    <?php
                    $configuraciones->data_seek(0);
                    while ($config = $configuraciones->fetch_assoc()):
                        if (in_array($config['clave'], ['nombre_sitio', 'email_contacto'])):
                    ?>
                        <div class="mb-4">
                            <label for="<?php echo $config['clave']; ?>" class="form-label">
                                <?php echo ucfirst(str_replace('_', ' ', $config['clave'])); ?>
                            </label>
                            
                            <?php if ($config['tipo'] === 'textarea'): ?>
                                <textarea class="form-control" 
                                          id="<?php echo $config['clave']; ?>" 
                                          name="<?php echo $config['clave']; ?>" 
                                          rows="4"><?php echo htmlspecialchars($config['valor']); ?></textarea>
                            <?php elseif ($config['tipo'] === 'number'): ?>
                                <input type="number" 
                                       class="form-control" 
                                       id="<?php echo $config['clave']; ?>" 
                                       name="<?php echo $config['clave']; ?>" 
                                       value="<?php echo htmlspecialchars($config['valor']); ?>">
                            <?php else: ?>
                                <input type="text" 
                                       class="form-control" 
                                       id="<?php echo $config['clave']; ?>" 
                                       name="<?php echo $config['clave']; ?>" 
                                       value="<?php echo htmlspecialchars($config['valor']); ?>">
                            <?php endif; ?>
                            
                            <?php if ($config['descripcion']): ?>
                                <small class="text-muted"><?php echo htmlspecialchars($config['descripcion']); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php 
                        endif;
                    endwhile; 
                    ?>
                </div>
                
                <!-- Sobre Mí -->
                <div class="config-section">
                    <h5>
                        <i class="bi bi-person-circle"></i> Sección "Sobre Mí"
                    </h5>
                    
                    <?php
                    $configuraciones->data_seek(0);
                    while ($config = $configuraciones->fetch_assoc()):
                        if (in_array($config['clave'], ['sobre_mi_titulo', 'sobre_mi_contenido'])):
                    ?>
                        <div class="mb-4">
                            <label for="<?php echo $config['clave']; ?>" class="form-label">
                                <?php echo ucfirst(str_replace('_', ' ', $config['clave'])); ?>
                            </label>
                            
                            <?php if ($config['tipo'] === 'textarea'): ?>
                                <textarea class="form-control" 
                                          id="<?php echo $config['clave']; ?>" 
                                          name="<?php echo $config['clave']; ?>" 
                                          rows="6"><?php echo htmlspecialchars($config['valor']); ?></textarea>
                            <?php else: ?>
                                <input type="text" 
                                       class="form-control" 
                                       id="<?php echo $config['clave']; ?>" 
                                       name="<?php echo $config['clave']; ?>" 
                                       value="<?php echo htmlspecialchars($config['valor']); ?>">
                            <?php endif; ?>
                            
                            <?php if ($config['descripcion']): ?>
                                <small class="text-muted"><?php echo htmlspecialchars($config['descripcion']); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php 
                        endif;
                    endwhile; 
                    ?>
                </div>
                
                <!-- Redes Sociales -->
                <div class="config-section">
                    <h5>
                        <i class="bi bi-share"></i> Redes Sociales
                    </h5>
                    
                    <?php
                    $configuraciones->data_seek(0);
                    while ($config = $configuraciones->fetch_assoc()):
                        if (in_array($config['clave'], ['github_url', 'linkedin_url', 'twitter_url'])):
                    ?>
                        <div class="mb-4">
                            <label for="<?php echo $config['clave']; ?>" class="form-label">
                                <i class="bi bi-<?php 
                                    echo ($config['clave'] == 'github_url') ? 'github' : 
                                         (($config['clave'] == 'linkedin_url') ? 'linkedin' : 'twitter'); 
                                ?>"></i>
                                <?php echo ucfirst(str_replace('_', ' ', $config['clave'])); ?>
                            </label>
                            
                            <input type="url" 
                                   class="form-control" 
                                   id="<?php echo $config['clave']; ?>" 
                                   name="<?php echo $config['clave']; ?>" 
                                   placeholder="https://"
                                   value="<?php echo htmlspecialchars($config['valor']); ?>">
                            
                            <?php if ($config['descripcion']): ?>
                                <small class="text-muted"><?php echo htmlspecialchars($config['descripcion']); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php 
                        endif;
                    endwhile; 
                    ?>
                </div>
                
                <!-- Configuración de Visualización -->
                <div class="config-section">
                    <h5>
                        <i class="bi bi-eye"></i> Configuración de Visualización
                    </h5>
                    
                    <?php
                    $configuraciones->data_seek(0);
                    while ($config = $configuraciones->fetch_assoc()):
                        if (in_array($config['clave'], ['proyectos_por_pagina'])):
                    ?>
                        <div class="mb-4">
                            <label for="<?php echo $config['clave']; ?>" class="form-label">
                                <?php echo ucfirst(str_replace('_', ' ', $config['clave'])); ?>
                            </label>
                            
                            <input type="number" 
                                   class="form-control" 
                                   id="<?php echo $config['clave']; ?>" 
                                   name="<?php echo $config['clave']; ?>" 
                                   min="1"
                                   max="50"
                                   value="<?php echo htmlspecialchars($config['valor']); ?>">
                            
                            <?php if ($config['descripcion']): ?>
                                <small class="text-muted"><?php echo htmlspecialchars($config['descripcion']); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php 
                        endif;
                    endwhile; 
                    ?>
                </div>
                
                <hr>
                
                <div class="text-end">
                    <button type="submit" name="submit" class="btn btn-lg" style="background-color: var(--primary-blue); color: white;">
                        <i class="bi bi-save"></i> Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Información adicional -->
        <div class="alert alert-info">
            <h5 class="alert-heading">
                <i class="bi bi-info-circle"></i> Información
            </h5>
            <p class="mb-0">
                Los cambios realizados en esta sección se reflejarán automáticamente en todo el sitio público. 
                Asegúrate de verificar cómo se ven los cambios visitando el sitio.
            </p>
        </div>
        
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>