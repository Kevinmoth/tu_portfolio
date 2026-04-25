<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: login.php');
    exit;
}

$mensaje_exito = '';
$mensaje_error = '';

// Agregar categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = limpiar_entrada($_POST['nombre']);
    $slug = limpiar_entrada($_POST['slug']);
    $descripcion = limpiar_entrada($_POST['descripcion']);
    $icono = limpiar_entrada($_POST['icono']);

    if (empty($nombre) || empty($slug)) {
        $mensaje_error = 'El nombre y el slug son obligatorios.';
    } else {
        $stmt = $conn->prepare("INSERT INTO categorias (nombre, slug, descripcion, icono) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $slug, $descripcion, $icono);
        if ($stmt->execute()) {
            $mensaje_exito = "Categoria '$nombre' agregada exitosamente.";
        } else {
            $mensaje_error = 'Error al agregar la categoria: ' . $conn->error;
        }
    }
}

// Editar categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = intval($_POST['id']);
    $nombre = limpiar_entrada($_POST['nombre']);
    $slug = limpiar_entrada($_POST['slug']);
    $descripcion = limpiar_entrada($_POST['descripcion']);
    $icono = limpiar_entrada($_POST['icono']);

    if (empty($nombre) || empty($slug)) {
        $mensaje_error = 'El nombre y el slug son obligatorios.';
    } else {
        $stmt = $conn->prepare("UPDATE categorias SET nombre = ?, slug = ?, descripcion = ?, icono = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nombre, $slug, $descripcion, $icono, $id);
        if ($stmt->execute()) {
            $mensaje_exito = "Categoria actualizada exitosamente.";
        } else {
            $mensaje_error = 'Error al actualizar la categoria.';
        }
    }
}

// Eliminar categoria
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $check = $conn->query("SELECT COUNT(*) as total FROM proyectos WHERE categoria_id = $id");
    $en_uso = $check->fetch_assoc()['total'];

    if ($en_uso > 0) {
        $mensaje_error = "No se puede eliminar esta categoria porque tiene $en_uso proyecto(s) asociado(s).";
    } else {
        $stmt = $conn->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $mensaje_exito = "Categoria eliminada exitosamente.";
        } else {
            $mensaje_error = 'Error al eliminar la categoria.';
        }
    }
}

$categorias = $conn->query("
    SELECT c.*, COUNT(p.id) as proyectos_asociados
    FROM categorias c
    LEFT JOIN proyectos p ON c.id = p.categoria_id
    GROUP BY c.id
    ORDER BY c.id
");

$mensajes_no_leidos = $conn->query("SELECT COUNT(*) as total FROM mensajes_contacto WHERE leido = FALSE")->fetch_assoc()['total'];

$categoria_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $stmt = $conn->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $id_editar);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows === 1) {
        $categoria_editar = $resultado->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias - Panel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/configuracion.css">
</head>
<body>
    <?php $pagina_activa = 'categorias'; require_once 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="top-bar">
            <h3 class="mb-0"><i class="bi bi-tags"></i> Gestion de Categorias</h3>
        </div>

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

        <div class="row">
            <div class="col-lg-4">
                <div class="form-card">
                    <h5 class="mb-4">
                        <i class="bi bi-<?php echo $categoria_editar ? 'pencil' : 'plus-circle'; ?>"></i>
                        <?php echo $categoria_editar ? 'Editar Categoria' : 'Agregar Categoria'; ?>
                    </h5>

                    <form method="POST" action="categorias.php">
                        <?php if ($categoria_editar): ?>
                            <input type="hidden" name="id" value="<?php echo $categoria_editar['id']; ?>">
                            <input type="hidden" name="editar" value="1">
                        <?php else: ?>
                            <input type="hidden" name="agregar" value="1">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   placeholder="Ej: Bots, Programas, etc."
                                   value="<?php echo $categoria_editar ? htmlspecialchars($categoria_editar['nombre']) : ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="slug" name="slug"
                                   placeholder="Ej: bots, programas, etc."
                                   value="<?php echo $categoria_editar ? htmlspecialchars($categoria_editar['slug']) : ''; ?>" required>
                            <small class="text-muted">Identificador unico para la URL (sin espacios ni caracteres especiales)</small>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripcion</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                      placeholder="Descripcion breve de la categoria"><?php echo $categoria_editar ? htmlspecialchars($categoria_editar['descripcion']) : ''; ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="icono" class="form-label">Icono (Bootstrap Icons)</label>
                            <input type="text" class="form-control" id="icono" name="icono"
                                   placeholder="Ej: bi-robot, bi-window-desktop, bi-code-slash"
                                   value="<?php echo $categoria_editar ? htmlspecialchars($categoria_editar['icono']) : 'bi-folder'; ?>">
                            <small class="text-muted">Nombre de la clase de <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a></small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> <?php echo $categoria_editar ? 'Actualizar' : 'Agregar'; ?>
                            </button>
                            <?php if ($categoria_editar): ?>
                                <a href="categorias.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="table-card">
                    <h5 class="mb-4"><i class="bi bi-list-ul"></i> Categorias Registradas</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Slug</th>
                                    <th>Icono</th>
                                    <th>Proyectos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($cat = $categorias->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $cat['id']; ?></td>
                                    <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                                    <td><code><?php echo htmlspecialchars($cat['slug']); ?></code></td>
                                    <td><i class="bi <?php echo htmlspecialchars($cat['icono'] ?? 'bi-folder'); ?>" style="font-size: 1.3rem;"></i></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $cat['proyectos_asociados']; ?> proyecto<?php echo $cat['proyectos_asociados'] != 1 ? 's' : ''; ?></span>
                                    </td>
                                    <td>
                                        <a href="categorias.php?editar=<?php echo $cat['id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
                                        <?php if ($cat['proyectos_asociados'] == 0): ?>
                                            <a href="categorias.php?eliminar=<?php echo $cat['id']; ?>" class="btn btn-sm btn-danger"
                                               onclick="return confirm('Estas seguro de eliminar esta categoria?');"><i class="bi bi-trash"></i></a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled title="En uso, no se puede eliminar"><i class="bi bi-lock"></i></button>
                                        <?php endif; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
