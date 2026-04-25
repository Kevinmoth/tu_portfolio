<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: login.php');
    exit;
}

$mensaje_exito = '';
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = limpiar_entrada($_POST['nombre']);
    $color = limpiar_entrada($_POST['color']);
    $icono = limpiar_entrada($_POST['icono']);

    if (empty($nombre) || empty($color)) {
        $mensaje_error = 'El nombre y el color son obligatorios.';
    } else {
        $stmt = $conn->prepare("INSERT INTO tecnologias (nombre, color, icono) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $color, $icono);
        if ($stmt->execute()) {
            $mensaje_exito = "Tecnologia '$nombre' agregada exitosamente.";
        } else {
            $mensaje_error = 'Error al agregar la tecnologia: ' . $conn->error;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = intval($_POST['id']);
    $nombre = limpiar_entrada($_POST['nombre']);
    $color = limpiar_entrada($_POST['color']);
    $icono = limpiar_entrada($_POST['icono']);

    if (empty($nombre) || empty($color)) {
        $mensaje_error = 'El nombre y el color son obligatorios.';
    } else {
        $stmt = $conn->prepare("UPDATE tecnologias SET nombre = ?, color = ?, icono = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nombre, $color, $icono, $id);
        if ($stmt->execute()) {
            $mensaje_exito = "Tecnologia actualizada exitosamente.";
        } else {
            $mensaje_error = 'Error al actualizar la tecnologia.';
        }
    }
}

if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $check = $conn->query("SELECT COUNT(*) as total FROM proyectos_tecnologias WHERE tecnologia_id = $id");
    $en_uso = $check->fetch_assoc()['total'];

    if ($en_uso > 0) {
        $mensaje_error = "No se puede eliminar esta tecnologia porque esta siendo utilizada en $en_uso proyecto(s).";
    } else {
        $stmt = $conn->prepare("DELETE FROM tecnologias WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $mensaje_exito = "Tecnologia eliminada exitosamente.";
        } else {
            $mensaje_error = 'Error al eliminar la tecnologia.';
        }
    }
}

$tecnologias = $conn->query("
    SELECT t.*,
           COUNT(pt.proyecto_id) as proyectos_usando
    FROM tecnologias t
    LEFT JOIN proyectos_tecnologias pt ON t.id = pt.tecnologia_id
    GROUP BY t.id
    ORDER BY t.nombre
");

$mensajes_no_leidos = $conn->query("SELECT COUNT(*) as total FROM mensajes_contacto WHERE leido = FALSE")->fetch_assoc()['total'];

$tecnologia_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $stmt = $conn->prepare("SELECT * FROM tecnologias WHERE id = ?");
    $stmt->bind_param("i", $id_editar);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows === 1) {
        $tecnologia_editar = $resultado->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tecnologias - Panel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/configuracion.css">
</head>
<body>
    <?php $pagina_activa = 'tecnologias'; require_once 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="top-bar">
            <h3 class="mb-0"><i class="bi bi-gear"></i> Gestion de Tecnologias</h3>
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
                        <i class="bi bi-<?php echo $tecnologia_editar ? 'pencil' : 'plus-circle'; ?>"></i>
                        <?php echo $tecnologia_editar ? 'Editar Tecnologia' : 'Agregar Tecnologia'; ?>
                    </h5>

                    <form method="POST" action="tecnologias.php">
                        <?php if ($tecnologia_editar): ?>
                            <input type="hidden" name="id" value="<?php echo $tecnologia_editar['id']; ?>">
                            <input type="hidden" name="editar" value="1">
                        <?php else: ?>
                            <input type="hidden" name="agregar" value="1">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   placeholder="Ej: Python, React, etc."
                                   value="<?php echo $tecnologia_editar ? htmlspecialchars($tecnologia_editar['nombre']) : ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="color" name="color"
                                       value="<?php echo $tecnologia_editar ? htmlspecialchars($tecnologia_editar['color']) : '#4a90e2'; ?>" required>
                                <input type="text" class="form-control" id="color_text" placeholder="#RRGGBB"
                                       value="<?php echo $tecnologia_editar ? htmlspecialchars($tecnologia_editar['color']) : '#4a90e2'; ?>" pattern="^#[0-9A-Fa-f]{6}$">
                            </div>
                            <small class="text-muted">Color del badge en formato hexadecimal</small>
                        </div>

                        <div class="mb-4">
                            <label for="icono" class="form-label">Icono / Logo URL (Opcional)</label>
                            <input type="url" class="form-control" id="icono" name="icono" placeholder="https://..."
                                   value="<?php echo $tecnologia_editar ? htmlspecialchars($tecnologia_editar['icono']) : ''; ?>">
                            <small class="text-muted">URL de un icono o logo (opcional)</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> <?php echo $tecnologia_editar ? 'Actualizar' : 'Agregar'; ?>
                            </button>
                            <?php if ($tecnologia_editar): ?>
                                <a href="tecnologias.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="table-card">
                    <h5 class="mb-4"><i class="bi bi-list-ul"></i> Tecnologias Registradas</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Preview</th>
                                    <th>Color</th>
                                    <th>Proyectos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($tech = $tecnologias->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $tech['id']; ?></td>
                                    <td><?php echo htmlspecialchars($tech['nombre']); ?></td>
                                    <td>
                                        <span class="tech-preview" style="background-color: <?php echo $tech['color']; ?>">
                                            <?php echo htmlspecialchars($tech['nombre']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="color-preview" style="background-color: <?php echo $tech['color']; ?>"></span>
                                        <small class="text-muted ms-2"><?php echo $tech['color']; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $tech['proyectos_usando']; ?> proyecto<?php echo $tech['proyectos_usando'] != 1 ? 's' : ''; ?></span>
                                    </td>
                                    <td>
                                        <a href="tecnologias.php?editar=<?php echo $tech['id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
                                        <?php if ($tech['proyectos_usando'] == 0): ?>
                                            <a href="tecnologias.php?eliminar=<?php echo $tech['id']; ?>" class="btn btn-sm btn-danger"
                                               onclick="return confirm('Estas seguro de eliminar esta tecnologia?');"><i class="bi bi-trash"></i></a>
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
    <script>
        const colorPicker = document.getElementById('color');
        const colorText = document.getElementById('color_text');
        if (colorPicker && colorText) {
            colorPicker.addEventListener('input', function() { colorText.value = this.value; });
            colorText.addEventListener('input', function() {
                if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) { colorPicker.value = this.value; }
            });
        }
    </script>
</body>
</html>
