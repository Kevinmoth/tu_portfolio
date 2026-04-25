<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
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
    
    if (($row = $resultado->fetch_assoc()) && $row['imagen']) {
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

function obtener_tecnologias($proyecto_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT t.nombre, t.color
        FROM tecnologias t
        INNER JOIN proyectos_tecnologias pt ON t.id = pt.tecnologia_id
        WHERE pt.proyecto_id = ?
    ");
    $stmt->bind_param("i", $proyecto_id);
    $stmt->execute();
    return $stmt->get_result();
}

function renderizar_tarjeta_proyecto($proyecto) {
    $output = '';
    $output .= '<a href="proyecto-detalle.php?id=' . $proyecto['id'] . '" style="text-decoration: none; color: inherit;">';
    $output .= '<div class="project-card">';

    if ($proyecto['imagen']) {
        $output .= '<img src="includes/conexion.php?img=proyecto&id=' . $proyecto['id'] . '" alt="' . htmlspecialchars($proyecto['titulo']) . '">';
    } else {
        $output .= '<img src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'200\'%3E%3Crect fill=\'%23e9ecef\' width=\'400\' height=\'200\'/%3E%3Ctext fill=\'%236c757d\' font-family=\'Arial\' font-size=\'20\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3ESin imagen%3C/text%3E%3C/svg%3E" alt="Sin imagen">';
    }

    $output .= '<div class="project-card-body">';
    $output .= '<h3 class="project-title">' . htmlspecialchars($proyecto['titulo']) . '</h3>';

    if (isset($proyecto['categoria_nombre'])) {
        $output .= '<span class="badge bg-secondary mb-2">' . htmlspecialchars($proyecto['categoria_nombre']) . '</span> ';
    }

    if (!empty($proyecto['destacado'])) {
        $output .= '<span class="badge bg-warning text-dark mb-2"><i class="bi bi-star-fill"></i> Destacado</span> ';
    }

    $desc = $proyecto['descripcion_corta'] ?? $proyecto['descripcion'];
    $output .= '<p class="project-description">' . htmlspecialchars(substr($desc, 0, 100)) . (strlen($desc) > 100 ? '...' : '') . '</p>';

    $output .= '<div class="mb-3">';
    $tecnologias = obtener_tecnologias($proyecto['id']);
    while ($tech = $tecnologias->fetch_assoc()) {
        $output .= '<span class="tech-badge" style="background-color: ' . $tech['color'] . '">' . htmlspecialchars($tech['nombre']) . '</span> ';
    }
    $output .= '</div>';

    $output .= '<div class="d-flex gap-2">';
    if ($proyecto['url_demo']) {
        $output .= '<span class="btn btn-sm btn-primary-custom"><i class="bi bi-box-arrow-up-right"></i> Demo</span>';
    }
    if ($proyecto['url_github']) {
        $output .= '<span class="btn btn-sm btn-outline-dark"><i class="bi bi-github"></i> Código</span>';
    }
    $output .= '</div>';

    $output .= '</div>';
    $output .= '</div>';
    $output .= '</a>';
    return $output;
}

function renderizar_paginacion($pagina, $total_paginas, $busqueda = '') {
    if ($total_paginas <= 1) return '';

    $busqueda_param = $busqueda ? '&buscar=' . urlencode($busqueda) : '';
    $output = '<nav aria-label="Navegación de páginas"><ul class="pagination justify-content-center">';

    $output .= '<li class="page-item ' . ($pagina <= 1 ? 'disabled' : '') . '">';
    $output .= '<a class="page-link" href="?pagina=' . ($pagina - 1) . $busqueda_param . '">Anterior</a>';
    $output .= '</li>';

    for ($i = 1; $i <= $total_paginas; $i++) {
        $output .= '<li class="page-item ' . ($i == $pagina ? 'active' : '') . '">';
        $output .= '<a class="page-link" href="?pagina=' . $i . $busqueda_param . '">' . $i . '</a>';
        $output .= '</li>';
    }

    $output .= '<li class="page-item ' . ($pagina >= $total_paginas ? 'disabled' : '') . '">';
    $output .= '<a class="page-link" href="?pagina=' . ($pagina + 1) . $busqueda_param . '">Siguiente</a>';
    $output .= '</li>';

    $output .= '</ul></nav>';
    return $output;
}

// Si se llama este archivo directamente con un parámetro de imagen
if (isset($_GET['img']) && isset($_GET['id'])) {
    $tipo = $_GET['img'];
    $id = intval($_GET['id']);
    mostrar_imagen($id, $tipo);
}
?>