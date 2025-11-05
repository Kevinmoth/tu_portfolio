<?php
session_start();
require_once '../includes/conexion.php';


if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: login.php');
    exit;
}
// Verificar que se recibió un ID
if (!isset($_GET['id'])) {
    header('Location: editar-proyecto.php');
    exit;
}

$id = intval($_GET['id']);

// mirar que el proyecto exista
$stmt = $conn->prepare("SELECT titulo FROM proyectos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $_SESSION['mensaje_error'] = 'Proyecto no encontrado.';
    header('Location: editar-proyecto.php');
    exit;
}

$proyecto = $resultado->fetch_assoc();

// Eliminar el proyecto
// Las relaciones en proyectos_tecnologias y visitas_proyectos ahora si se borran solas :D
$stmt_delete = $conn->prepare("DELETE FROM proyectos WHERE id = ?");
$stmt_delete->bind_param("i", $id);

if ($stmt_delete->execute()) {
    $_SESSION['mensaje_exito'] = "Proyecto '{$proyecto['titulo']}' eliminado exitosamente.";
} else {
    $_SESSION['mensaje_error'] = 'Error al eliminar el proyecto.';
}

header('Location: editar-proyecto.php');
exit;
?>