<?php
session_start();
require_once '../includes/conexion.php';


if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: login.php');
    exit;
}

$mensaje_exito = '';
$mensaje_error = '';
$editando = false;
$proyecto = null;

// Obtener categorías
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nombre");
// Obtener tecnologías
$tecnologias = $conn->query("SELECT * FROM tecnologias ORDER BY nombre");

// Si hay un ID, cargar el proyecto
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM proyectos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 1) {
        $proyecto = $resultado->fetch_assoc();
        $editando = true;
        
        // Obtener tecnologías del proyecto
        $stmt_tech = $conn->prepare("SELECT tecnologia_id FROM proyectos_tecnologias WHERE proyecto_id = ?");
        $stmt_tech->bind_param("i", $id);
        $stmt_tech->execute();
        $result_tech = $stmt_tech->get_result();
        $tecnologias_proyecto = [];
        while ($row = $result_tech->fetch_assoc()) {
            $tecnologias_proyecto[] = $row['tecnologia_id'];
        }
    } else {
        $mensaje_error = 'Proyecto no encontrado.';
    }
}

// Procesar formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = intval($_POST['id']);
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
    
    if (empty($titulo) || empty($descripcion) || empty($categoria_id)) {
        $mensaje_error = 'Por favor, completa todos los campos obligatorios.';
    } else {
        // Procesar nueva imagen pero solo s si se subió
        $actualizar_imagen = false;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $imagen_tmp = $_FILES['imagen']['tmp_name'];
            $imagen_nombre = $_FILES['imagen']['name'];
            $imagen_tamanio = $_FILES['imagen']['size'];
            $imagen_tipo = $_FILES['imagen']['type'];
            
            $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($imagen_tipo, $tipos_permitidos)) {
                $mensaje_error = 'Solo se permiten imágenes JPG, PNG, GIF o WEBP.';
            } elseif ($imagen_tamanio > 5000000) {
                $mensaje_error = 'La imagen no debe superar los 5MB.';
            } else {
                $imagen = file_get_contents($imagen_tmp);
                $actualizar_imagen = true;
            }
        }
        
        if (empty($mensaje_error)) {
            // Actualizar proyecto
            if ($actualizar_imagen) {
                $stmt = $conn->prepare("
                    UPDATE proyectos SET 
                    titulo = ?, descripcion = ?, descripcion_corta = ?, categoria_id = ?, 
                    imagen = ?, imagen_tipo = ?, imagen_tamanio = ?, imagen_nombre = ?,
                    url_demo = ?, url_github = ?, url_repo = ?, destacado = ?, activo = ?
                    WHERE id = ?
                ");
                $stmt->bind_param(
                    "sssibsissssiii", //tipos de valor
                    $titulo, $descripcion, $descripcion_corta, $categoria_id,
                    $imagen, $imagen_tipo, $imagen_tamanio, $imagen_nombre,
                    $url_demo, $url_github, $url_repo, $destacado, $activo, $id
                );
            } else {
                $stmt = $conn->prepare("
                    UPDATE proyectos SET 
                    titulo = ?, descripcion = ?, descripcion_corta = ?, categoria_id = ?, 
                    url_demo = ?, url_github = ?, url_repo = ?, destacado = ?, activo = ?
                    WHERE id = ?
                ");
                $stmt->bind_param(
                    "sssisssiii",
                    $titulo, $descripcion, $descripcion_corta, $categoria_id,
                    $url_demo, $url_github, $url_repo, $destacado, $activo, $id
                );
            }
            
            if ($stmt->execute()) {
                // Eliminar tecnologías anteriores
                $conn->query("DELETE FROM proyectos_tecnologias WHERE proyecto_id = $id");
                
                // Insertar lass nuevas 
                if (!empty($tecnologias_seleccionadas)) {
                    $stmt_tech = $conn->prepare("INSERT INTO proyectos_tecnologias (proyecto_id, tecnologia_id) VALUES (?, ?)");
                    foreach ($tecnologias_seleccionadas as $tech_id) {
                        $tech_id = intval($tech_id);
                        $stmt_tech->bind_param("ii", $id, $tech_id);
                        $stmt_tech->execute();
                    }
                }
                
                $mensaje_exito = 'Proyecto actualizado exitosamente!';
                
                // Recargar proyecto 
                $stmt_reload = $conn->prepare("SELECT * FROM proyectos WHERE id = ?");
                $stmt_reload->bind_param("i", $id);
                $stmt_reload->execute();
                $proyecto = $stmt_reload->get_result()->fetch_assoc();
                
                // Recargar tecnologías
                $stmt_tech = $conn->prepare("SELECT tecnologia_id FROM proyectos_tecnologias WHERE proyecto_id = ?");
                $stmt_tech->bind_param("i", $id);
                $stmt_tech->execute();
                $result_tech = $stmt_tech->get_result();
                $tecnologias_proyecto = [];
                while ($row = $result_tech->fetch_assoc()) {
                    $tecnologias_proyecto[] = $row['tecnologia_id'];
                }
            } else {
                $mensaje_error = 'Error al actualizar tu proyecto.';
            }
        }
    }
}

// Listar todos los proyectos
$proyectos_lista = $conn->query("
    SELECT p.*, c.nombre as categoria 
    FROM proyectos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    ORDER BY p.fecha_creacion DESC
");

$mensajes_no_leidos = $conn->query("SELECT COUNT(*) as total FROM mensajes_contacto WHERE leido = FALSE")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proyectos - Panel Administrativo</title>
    
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    
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
                <a href="editar-proyecto.php" class="active">
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
    
    <!-- contenido sobre la tabla -->
    <div class="main-content">
        <div class="top-bar">
            <h3 class="mb-0">
                <i class="bi bi-pencil-square"></i> <?php echo $editando ? 'Editar Proyecto' : 'Gestionar Proyectos'; ?>
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
        
        <?php if ($editando && $proyecto): ?>
        <!-- Formulario para editar-->
        <div class="form-card mb-4">
            <h5 class="mb-4">
                <i class="bi bi-pencil"></i> Editando: <?php echo htmlspecialchars($proyecto['titulo']); ?>
            </h5>
            
            <form method="POST" action="editar-proyecto.php?id=<?php echo $proyecto['id']; ?>" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $proyecto['id']; ?>">
                <input type="hidden" name="editar" value="1">
                
                <div class="row">
                    <div class="col-md-8 mb-4">
                        <label for="titulo" class="form-label">
                            Título del Proyecto <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="titulo" 
                               name="titulo" 
                               value="<?php echo htmlspecialchars($proyecto['titulo']); ?>"
                               required>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <label for="categoria_id" class="form-label">
                            Categoría <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-lg" id="categoria_id" name="categoria_id" required>
                            <?php 
                            $categorias->data_seek(0);
                            while ($cat = $categorias->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $proyecto['categoria_id']) ? 'selected' : ''; ?>>
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
                           value="<?php echo htmlspecialchars($proyecto['descripcion_corta']); ?>">
                </div>
                
                <div class="mb-4">
                    <label for="descripcion" class="form-label">
                        Descripción Completa <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" 
                              id="descripcion" 
                              name="descripcion" 
                              rows="6" 
                              required><?php echo htmlspecialchars($proyecto['descripcion']); ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Imagen Actual</label>
                    <div class="mb-3">
                        <?php if ($proyecto['imagen']): ?>
                            <img src="../includes/conexion.php?img=proyecto&id=<?php echo $proyecto['id']; ?>" 
                                 class="proyecto-img-preview" 
                                 alt="Imagen del proyecto">
                        <?php else: ?>
                            <p class="text-muted">Sin imagen</p>
                        <?php endif; ?>
                    </div>
                    <label for="imagen" class="form-label">Cambiar Imagen</label>
                    <input type="file" 
                           class="form-control" 
                           id="imagen" 
                           name="imagen" 
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <small class="text-muted">Deja vacío si no deseas cambiar la imagen</small>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="url_demo" class="form-label">URL Demo</label>
                        <input type="url" 
                               class="form-control" 
                               id="url_demo" 
                               name="url_demo" 
                               value="<?php echo htmlspecialchars($proyecto['url_demo']); ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="url_github" class="form-label">URL GitHub</label>
                        <input type="url" 
                               class="form-control" 
                               id="url_github" 
                               name="url_github" 
                               value="<?php echo htmlspecialchars($proyecto['url_github']); ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="url_repo" class="form-label">URL Repositorio</label>
                        <input type="url" 
                               class="form-control" 
                               id="url_repo" 
                               name="url_repo" 
                               value="<?php echo htmlspecialchars($proyecto['url_repo']); ?>">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Tecnologías Utilizadas</label>
                    <div class="row g-3">
                        <?php 
                        $tecnologias->data_seek(0);
                        while ($tech = $tecnologias->fetch_assoc()): 
                        $checked = in_array($tech['id'], $tecnologias_proyecto) ? 'checked' : '';
                        ?>
                            <div class="col-md-3">
                                <div class="tech-checkbox">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="tecnologias[]" 
                                               value="<?php echo $tech['id']; ?>" 
                                               id="tech_<?php echo $tech['id']; ?>"
                                               <?php echo $checked; ?>>
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
                                   <?php echo $proyecto['destacado'] ? 'checked' : ''; ?>>
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
                                   <?php echo $proyecto['activo'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="activo">
                                <i class="bi bi-check-circle-fill text-success"></i> Proyecto activo (visible)
                            </label>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between">
                    <a href="editar-proyecto.php" class="btn btn-secondary btn-lg">
                        <i class="bi bi-arrow-left"></i> Volver al listado
                    </a>
                    <button type="submit" class="btn btn-lg" style="background-color: var(--primary-blue); color: white;">
                        <i class="bi bi-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <!-- Lista de Proyectos -->
        <div class="table-card">
            <h5 class="mb-4">
                <i class="bi bi-list-ul"></i> Todos los Proyectos
            </h5>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($proj = $proyectos_lista->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $proj['id']; ?></td>
                            <td><?php echo htmlspecialchars($proj['titulo']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($proj['categoria']); ?></span></td>
                            <td>
                                <?php if ($proj['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                                <?php if ($proj['destacado']): ?>
                                    <span class="badge bg-warning text-dark">Destacado</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($proj['fecha_creacion'])); ?></td>
                            <td>
                                <a href="editar-proyecto.php?id=<?php echo $proj['id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <a href="eliminar-proyecto.php?id=<?php echo $proj['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('¿Estás seguro de eliminar este proyecto?');">
                                    <i class="bi bi-trash"></i> Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>