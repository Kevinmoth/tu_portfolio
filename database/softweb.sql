<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'portfolio_db');

// Crear conexión
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer charset UTF-8
$conn->set_charset("utf8mb4");

// Función para limpiar datos de entrada
function limpiar_entrada($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Función para obtener configuración
function obtener_config($clave) {
    global $conn;
    $stmt = $conn->prepare("SELECT valor FROM configuracion WHERE clave = ?");
    $stmt->bind_param("s", $clave);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($row = $resultado->fetch_assoc()) {
        return $row['valor'];
    }
    return null;
}

// Función para mostrar imagen desde la base de datos
function mostrar_imagen($id_proyecto, $tipo = 'proyecto') {
    global $conn;
    
    if ($tipo === 'proyecto') {
        $stmt = $conn->prepare("SELECT imagen, imagen_tipo FROM proyectos WHERE id = ?");
    } else {
        $stmt = $conn->prepare("SELECT imagen, imagen_tipo FROM imagenes_proyecto WHERE id = ?");
    }
    
    $stmt->bind_param("i", $id_proyecto);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($row = $resultado->fetch_assoc() && $row['imagen']) {
        header("Content-Type: " . $row['imagen_tipo']);
        echo $row['imagen'];
        exit;
    } else {
        // Imagen por defecto si no hay imagen
        header("Content-Type: image/svg+xml");
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300">
            <rect fill="#e9ecef" width="400" height="300"/>
            <text fill="#6c757d" font-family="Arial" font-size="20" x="50%" y="50%" text-anchor="middle" dy=".3em">Sin imagen</text>
        </svg>';
        exit;
    }
}

// Si se llama este archivo directamente con un parámetro de imagen
if (isset($_GET['img']) && isset($_GET['id'])) {
    $tipo = $_GET['img'];
    $id = intval($_GET['id']);
    mostrar_imagen($id, $tipo);
}
?>