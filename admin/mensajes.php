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

// Marcar como leído
if (isset($_GET['marcar_leido'])) {
    $id = intval($_GET['marcar_leido']);
    $stmt = $conn->prepare("UPDATE mensajes_contacto SET leido = TRUE WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $mensaje_exito = 'Mensaje marcado como leído.';
    }
}

// Marcar como no leído
if (isset($_GET['marcar_no_leido'])) {
    $id = intval($_GET['marcar_no_leido']);
    $stmt = $conn->prepare("UPDATE mensajes_contacto SET leido = FALSE WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $mensaje_exito = 'Mensaje marcado como no leído.';
    }
}

// Eliminar mensaje
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conn->prepare("DELETE FROM mensajes_contacto WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $mensaje_exito = 'Mensaje eliminado exitosamente.';
    } else {
        $mensaje_error = 'Error al eliminar el mensaje.';
    }
}

// Filtros
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
$where = '';

switch ($filtro) {
    case 'no_leidos':
        $where = 'WHERE leido = FALSE';
        break;
    case 'leidos':
        $where = 'WHERE leido = TRUE';
        break;
    default:
        $where = '';
}

// Obtener mensajes
$mensajes = $conn->query("
    SELECT * FROM mensajes_contacto 
    $where
    ORDER BY fecha_envio DESC
");

$total_mensajes = $conn->query("SELECT COUNT(*) as total FROM mensajes_contacto")->fetch_assoc()['total'];
$mensajes_no_leidos = $conn->query("SELECT COUNT(*) as total FROM mensajes_contacto WHERE leido = FALSE")->fetch_assoc()['total'];
$mensajes_leidos = $conn->query("SELECT COUNT(*) as total FROM mensajes_contacto WHERE leido = TRUE")->fetch_assoc()['total'];

// Ver detalle de mensaje
$mensaje_detalle = null;
if (isset($_GET['ver'])) {
    $id_ver = intval($_GET['ver']);
    $stmt = $conn->prepare("SELECT * FROM mensajes_contacto WHERE id = ?");
    $stmt->bind_param("i", $id_ver);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows === 1) {
        $mensaje_detalle = $resultado->fetch_assoc();
        
        // Marcar como leído automáticamente al ver
        if (!$mensaje_detalle['leido']) {
            $stmt_update = $conn->prepare("UPDATE mensajes_contacto SET leido = TRUE WHERE id = ?");
            $stmt_update->bind_param("i", $id_ver);
            $stmt_update->execute();
            $mensaje_detalle['leido'] = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - Panel Administrativo</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../assets/css/configuracion.css">
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
                <a href="mensajes.php" class="active">
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
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">
                    <i class="bi bi-envelope"></i> Mensajes de Contacto
                </h3>
                
                <div>
                    <span class="stat-badge" style="background-color: #e7f3ff; color: var(--primary-blue);">
                        <i class="bi bi-envelope"></i> Total: <?php echo $total_mensajes; ?>
                    </span>
                    <span class="stat-badge" style="background-color: #fff3cd; color: #856404;">
                        <i class="bi bi-envelope-exclamation"></i> No leídos: <?php echo $mensajes_no_leidos; ?>
                    </span>
                    <span class="stat-badge" style="background-color: #d1e7dd; color: #0f5132;">
                        <i class="bi bi-envelope-check"></i> Leídos: <?php echo $mensajes_leidos; ?>
                    </span>
                </div>
            </div>
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
        
        <?php if ($mensaje_detalle): ?>
        <!-- Detalle del Mensaje -->
        <div class="mensaje-detalle-card mb-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <h5>
                    <i class="bi bi-envelope-open"></i> Detalle del Mensaje
                </h5>
                <a href="mensajes.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong><i class="bi bi-person"></i> Nombre:</strong><br>
                    <?php echo htmlspecialchars($mensaje_detalle['nombre']); ?>
                </div>
                <div class="col-md-6">
                    <strong><i class="bi bi-envelope"></i> Email:</strong><br>
                    <a href="mailto:<?php echo htmlspecialchars($mensaje_detalle['email']); ?>">
                        <?php echo htmlspecialchars($mensaje_detalle['email']); ?>
                    </a>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong><i class="bi bi-tag"></i> Asunto:</strong><br>
                    <?php echo htmlspecialchars($mensaje_detalle['asunto']); ?>
                </div>
                <div class="col-md-6">
                    <strong><i class="bi bi-calendar"></i> Fecha:</strong><br>
                    <?php echo date('d/m/Y H:i', strtotime($mensaje_detalle['fecha_envio'])); ?>
                </div>
            </div>
            
            <hr>
            
            <div class="mb-4">
                <strong><i class="bi bi-chat-left-text"></i> Mensaje:</strong>
                <div class="mt-2 p-3" style="background-color: #f8f9fa; border-radius: 8px;">
                    <?php echo nl2br(htmlspecialchars($mensaje_detalle['mensaje'])); ?>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <?php if ($mensaje_detalle['leido']): ?>
                    <a href="mensajes.php?marcar_no_leido=<?php echo $mensaje_detalle['id']; ?>" 
                       class="btn btn-warning">
                        <i class="bi bi-envelope"></i> Marcar como No Leído
                    </a>
                <?php endif; ?>
                
                <a href="mailto:<?php echo htmlspecialchars($mensaje_detalle['email']); ?>?subject=Re: <?php echo urlencode($mensaje_detalle['asunto']); ?>" 
                   class="btn btn-primary">
                    <i class="bi bi-reply"></i> Responder por Email
                </a>
                
                <a href="mensajes.php?eliminar=<?php echo $mensaje_detalle['id']; ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('¿Estás seguro de eliminar este mensaje?');">
                    <i class="bi bi-trash"></i> Eliminar
                </a>
            </div>
        </div>
        <?php else: ?>
        <!-- Lista de Mensajes -->
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> Bandeja de Entrada
                </h5>
                
                <div class="btn-group" role="group">
                    <a href="mensajes.php?filtro=todos" 
                       class="btn btn-sm btn-outline-primary <?php echo $filtro == 'todos' ? 'active' : ''; ?>">
                        Todos
                    </a>
                    <a href="mensajes.php?filtro=no_leidos" 
                       class="btn btn-sm btn-outline-warning <?php echo $filtro == 'no_leidos' ? 'active' : ''; ?>">
                        No Leídos
                    </a>
                    <a href="mensajes.php?filtro=leidos" 
                       class="btn btn-sm btn-outline-success <?php echo $filtro == 'leidos' ? 'active' : ''; ?>">
                        Leídos
                    </a>
                </div>
            </div>
            
            <?php if ($mensajes->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%"></th>
                            <th width="20%">Nombre</th>
                            <th width="20%">Email</th>
                            <th width="25%">Asunto</th>
                            <th width="15%">Fecha</th>
                            <th width="15%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($msg = $mensajes->fetch_assoc()): ?>
                        <tr class="mensaje-row <?php echo !$msg['leido'] ? 'mensaje-no-leido' : ''; ?>"
                            onclick="window.location='mensajes.php?ver=<?php echo $msg['id']; ?>'">
                            <td class="text-center">
                                <?php if (!$msg['leido']): ?>
                                    <i class="bi bi-envelope-fill text-primary"></i>
                                <?php else: ?>
                                    <i class="bi bi-envelope-open text-muted"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($msg['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($msg['email']); ?></td>
                            <td>
                                <?php 
                                $asunto = $msg['asunto'] ? htmlspecialchars($msg['asunto']) : 'Sin asunto';
                                echo strlen($asunto) > 40 ? substr($asunto, 0, 40) . '...' : $asunto;
                                ?>
                            </td>
                            <td>
                                <small><?php echo date('d/m/Y H:i', strtotime($msg['fecha_envio'])); ?></small>
                            </td>
                            <td onclick="event.stopPropagation();">
                                <a href="mensajes.php?ver=<?php echo $msg['id']; ?>" 
                                   class="btn btn-sm btn-info"
                                   title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                <?php if (!$msg['leido']): ?>
                                    <a href="mensajes.php?marcar_leido=<?php echo $msg['id']; ?>" 
                                       class="btn btn-sm btn-success"
                                       title="Marcar como leído">
                                        <i class="bi bi-check2"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="mensajes.php?eliminar=<?php echo $msg['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('¿Estás seguro de eliminar este mensaje?');"
                                   title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">No hay mensajes</h4>
                <p class="text-muted">
                    <?php 
                    switch ($filtro) {
                        case 'no_leidos':
                            echo 'No tienes mensajes sin leer';
                            break;
                        case 'leidos':
                            echo 'No tienes mensajes leídos';
                            break;
                        default:
                            echo 'Aún no has recibido ningún mensaje';
                    }
                    ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>