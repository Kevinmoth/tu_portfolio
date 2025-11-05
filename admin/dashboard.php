<?php
session_start();
require_once '../includes/conexion.php';

// Verificar si está logueado
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: login.php');
    exit;
}

// traer las stats de la bd
$total_proyectos = $conn->query("SELECT COUNT(*) as total FROM proyectos")->fetch_assoc()['total'];
$total_mensajes = $conn->query("SELECT COUNT(*) as total FROM mensajes_contacto")->fetch_assoc()['total'];
$mensajes_no_leidos = $conn->query("SELECT COUNT(*) as total FROM mensajes_contacto WHERE leido = FALSE")->fetch_assoc()['total'];
$total_visitas = $conn->query("SELECT COUNT(*) as total FROM visitas_proyectos")->fetch_assoc()['total'];

// Obtener proyectos recientes
$proyectos_recientes = $conn->query("
    SELECT p.*, c.nombre as categoria 
    FROM proyectos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    ORDER BY p.fecha_creacion DESC 
    LIMIT 5
");

// Obtener mensajes
$mensajes_recientes = $conn->query("
    SELECT * FROM mensajes_contacto 
    ORDER BY fecha_envio DESC 
    LIMIT 5
");

// Obtener proyectos más visitados
$proyectos_populares = $conn->query("
    SELECT p.titulo, COUNT(v.id) as visitas 
    FROM proyectos p 
    LEFT JOIN visitas_proyectos v ON p.id = v.proyecto_id 
    GROUP BY p.id 
    ORDER BY visitas DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>TU portfolio</h4>
            <small><?php echo $_SESSION['admin_usuario']; ?></small>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php" class="active">
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
                <a href="configuracion.php">
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
            <div>
                <h3 class="mb-0">Dashboard</h3>
                <small class="text-muted">Bienvenido al panel de administración</small>
            </div>
            
        </div>
        
        <!-- Estadísticas -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card" style="border-left: 4px solid #4a90e2;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Tus proyectos</h6>
                            <h2 class="mb-0"><?php echo $total_proyectos; ?></h2>
                        </div>
                        <i class="bi bi-folder-fill stat-icon" style="color: #4a90e2;"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card" style="border-left: 4px solid #28a745;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Mensajes</h6>
                            <h2 class="mb-0"><?php echo $total_mensajes; ?></h2>
                        </div>
                        <i class="bi bi-envelope-fill stat-icon" style="color: #28a745;"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card" style="border-left: 4px solid #ffc107;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">No Leídos</h6>
                            <h2 class="mb-0"><?php echo $mensajes_no_leidos; ?></h2>
                        </div>
                        <i class="bi bi-bell-fill stat-icon" style="color: #ffc107;"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card" style="border-left: 4px solid #17a2b8;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Visitas (en construccion)</h6>
                            <h2 class="mb-0"><?php echo $total_visitas; ?></h2>
                        </div>
                        <i class="bi bi-eye-fill stat-icon" style="color: #17a2b8;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Proyectos Recientes -->
        <div class="table-card">
            <h5 class="mb-4">
                <i class="bi bi-clock-history"></i> Proyectos Recientes
            </h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($proyecto = $proyectos_recientes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($proyecto['titulo']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($proyecto['categoria']); ?></span></td>
                            <td>
                                <?php if ($proyecto['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                                <?php if ($proyecto['destacado']): ?>
                                    <span class="badge bg-warning text-dark">Destacado</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($proyecto['fecha_creacion'])); ?></td>
                            <td>
                                <a href="editar-proyecto.php?id=<?php echo $proyecto['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Mensajes Recientes -->
            <div class="col-md-6">
                <div class="table-card">
                    <h5 class="mb-4">
                        <i class="bi bi-envelope"></i> Mensajes Recientes
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Asunto</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($mensaje = $mensajes_recientes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($mensaje['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($mensaje['asunto'], 0, 30)); ?>...</td>
                                    <td>
                                        <?php if ($mensaje['leido']): ?>
                                            <span class="badge bg-secondary">Leído</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Nuevo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($mensaje['fecha_envio'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Proyectos Más Visitados -->
            <div class="col-md-6">
                <div class="table-card">
                    <h5 class="mb-4">
                        <i class="bi bi-graph-up"></i> Proyectos Más Visitados
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Proyecto</th>
                                    <th>Visitas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($popular = $proyectos_populares->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($popular['titulo']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $popular['visitas']; ?> visitas
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>