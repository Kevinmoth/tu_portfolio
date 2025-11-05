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

// Obtener categorías
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nombre");

// Obtener tecnologías
$tecnologias = $conn->query("SELECT * FROM tecnologias ORDER BY nombre");

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = limpiar_entrada($_POST['titulo']);
    $descripcion = limpiar_entrada($_POST['descripcion']);
    $descripcion_corta = limpiar_entrada($_POST['descripcion_corta']);
    $categoria_id = intval($_POST['categoria_id']);
    $url_demo = limpiar_entrada($_POST['url_demo']);
    $url_github = limpiar_entrada($_POST['url_github']);
    $url_repo = limpiar_entrada($_POST['url_repo']);
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $activo = isset($_POST['activo']) ? 1 : 0;
    $tecnologias_seleccionadas = isset($_POST['tecnologias']) ? $_POST['tecnologias'] : [];
    
    // Validaciones
    if (empty($titulo) || empty($descripcion) || empty($categoria_id)) {
        $mensaje_error = 'Por favor, completa todos los campos obligatorios.';
    } else {
        // Procesar imagen
        $imagen = null;
        $imagen_tipo = null;
        $imagen_tamanio = null;
        $imagen_nombre = null;
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $imagen_tmp = $_FILES['imagen']['tmp_name'];
            $imagen_nombre = $_FILES['imagen']['name'];
            $imagen_tamanio = $_FILES['imagen']['size'];
            $imagen_tipo = $_FILES['imagen']['type'];
            
            // Validar tipo de imagen
            $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($imagen_tipo, $tipos_permitidos)) {
                $mensaje_error = 'Solo se permiten imágenes JPG, PNG, GIF o WEBP.';
            } elseif ($imagen_tamanio > 5000000) { // 5MB máximo
                $mensaje_error = 'La imagen no debe superar los 5MB.';
            } else {
                $imagen = file_get_contents($imagen_tmp);
            }
        }
        
        if (empty($mensaje_error)) {
            // Insertar proyecto
            $stmt = $conn->prepare("
                INSERT INTO proyectos 
                (titulo, descripcion, descripcion_corta, categoria_id, imagen, imagen_tipo, imagen_tamanio, imagen_nombre, 
                url_demo, url_github, url_repo, destacado, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param(
                "sssisbissssii",
                $titulo,
                $descripcion,
                $descripcion_corta,
                $categoria_id,
                $imagen,
                $imagen_tipo,
                $imagen_tamanio,
                $imagen_nombre,
                $url_demo,
                $url_github,
                $url_repo,
                $destacado,
                $activo
            );
            
            if ($stmt->execute()) {
                $proyecto_id = $conn->insert_id;
                
                // Insertar tecnologías relacionadas
                if (!empty($tecnologias_seleccionadas)) {
                    $stmt_tech = $conn->prepare("INSERT INTO proyectos_tecnologias (proyecto_id, tecnologia_id) VALUES (?, ?)");
                    foreach ($tecnologias_seleccionadas as $tech_id) {
                        $tech_id = intval($tech_id);
                        $stmt_tech->bind_param("ii", $proyecto_id, $tech_id);
                        $stmt_tech->execute();
                    }
                }
                
                $mensaje_exito = '¡Proyecto creado exitosamente!';
                
                // Limpiar campos
                $titulo = $descripcion = $descripcion_corta = $url_demo = $url_github = $url_repo = '';
                $destacado = $activo = 0;
                $tecnologias_seleccionadas = [];
            } else {
                $mensaje_error = 'Error al crear el proyecto: ' . $conn->error;
            }
        }
    }
}

// Obtener conteo de mensajes no leídos para el sidebar
$mensajes_no_leidos = $conn->query("SELECT COUNT(*) as total FROM mensajes_contacto WHERE leido = FALSE")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Proyecto - Panel Administrativo</title>
    
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
        }
        
        .tech-checkbox {
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .tech-checkbox:hover {
            border-color: var(--primary-blue);
            background-color: #f8f9fa;
        }
        
        .tech-checkbox input:checked + label {
            color: var(--primary-blue);
            font-weight: bold;
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
                <a href="subir-proyecto.php" class="active">
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
            <h3 class="mb-0">
                <i class="bi bi-plus-circle"></i> Subir Nuevo Proyecto
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
        
        <!-- Formulario -->
        <div class="form-card">
            <form method="POST" action="subir-proyecto.php" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8 mb-4">
                        <label for="titulo" class="form-label">
                            Título del Proyecto <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="titulo" 
                               name="titulo" 
                               value="<?php echo htmlspecialchars($titulo ?? ''); ?>"
                               required>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <label for="categoria_id" class="form-label">
                            Categoría <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-lg" id="categoria_id" name="categoria_id" required>
                            <option value="">Selecciona una categoría</option>
                            <?php 
                            $categorias->data_seek(0);
                            while ($cat = $categorias->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="descripcion_corta" class="form-label">Descripción Corta</label>
                    <input type="text" 
                           class="form-control" 
                           id="descripcion_corta" 
                           name="descripcion_corta" 
                           maxlength="500"
                           placeholder="Descripción breve para las tarjetas"
                           value="<?php echo htmlspecialchars($descripcion_corta ?? ''); ?>">
                    <small class="text-muted">Máximo 500 caracteres</small>
                </div>
                
                <div class="mb-4">
                    <label for="descripcion" class="form-label">
                        Descripción Completa <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" 
                              id="descripcion" 
                              name="descripcion" 
                              rows="6" 
                              required><?php echo htmlspecialchars($descripcion ?? ''); ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="imagen" class="form-label">Imagen del Proyecto</label>
                    <input type="file" 
                           class="form-control" 
                           id="imagen" 
                           name="imagen" 
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <small class="text-muted">Formatos: JPG, PNG, GIF, WEBP. Tamaño máximo: 5MB</small>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="url_demo" class="form-label">URL Demo</label>
                        <input type="url" 
                               class="form-control" 
                               id="url_demo" 
                               name="url_demo" 
                               placeholder="https://"
                               value="<?php echo htmlspecialchars($url_demo ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="url_github" class="form-label">URL GitHub</label>
                        <input type="url" 
                               class="form-control" 
                               id="url_github" 
                               name="url_github" 
                               placeholder="https://github.com/..."
                               value="<?php echo htmlspecialchars($url_github ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="url_repo" class="form-label">URL Repositorio</label>
                        <input type="url" 
                               class="form-control" 
                               id="url_repo" 
                               name="url_repo" 
                               placeholder="https://"
                               value="<?php echo htmlspecialchars($url_repo ?? ''); ?>">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Tecnologías Utilizadas</label>
                    <div class="row g-3">
                        <?php 
                        $tecnologias->data_seek(0);
                        while ($tech = $tecnologias->fetch_assoc()): 
                        ?>
                            <div class="col-md-3">
                                <div class="tech-checkbox">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="tecnologias[]" 
                                               value="<?php echo $tech['id']; ?>" 
                                               id="tech_<?php echo $tech['id']; ?>">
                                        <label class="form-check-label" for="tech_<?php echo $tech['id']; ?>">
                                            <span class="badge" style="background-color: <?php echo $tech['color']; ?>">
                                                <?php echo htmlspecialchars($tech['nombre']); ?>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="destacado" 
                                   name="destacado" 
                                   <?php echo (isset($destacado) && $destacado) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="destacado">
                                <i class="bi bi-star-fill text-warning"></i> Marcar como destacado
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="activo" 
                                   name="activo" 
                                   checked>
                            <label class="form-check-label" for="activo">
                                <i class="bi bi-check-circle-fill text-success"></i> Proyecto activo (visible)
                            </label>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="text-end">
                    <a href="dashboard.php" class="btn btn-secondary btn-lg me-2">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-lg" style="background-color: var(--primary-blue); color: white;">
                        <i class="bi bi-save"></i> Guardar Proyecto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>