<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/conexion.php';

// Obtener configuraciones del sitio
$nombre_sitio = obtener_config('nombre_sitio') ?? 'Mi Portfolio';
$github_url = obtener_config('github_url');
$linkedin_url = obtener_config('linkedin_url');
$twitter_url = obtener_config('twitter_url');

// Determinar la página actual
$pagina_actual = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nombre_sitio); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/headear_general.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">KRON</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($pagina_actual == 'index') ? 'active' : ''; ?>" href="index.php">Inicio</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($pagina_actual == 'bots') ? 'active' : ''; ?>" href="bots.php">Bots</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($pagina_actual == 'programas') ? 'active' : ''; ?>" href="programas.php">Programas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($pagina_actual == 'scripts') ? 'active' : ''; ?>" href="scripts.php">Scripts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($pagina_actual == 'sobre-mi') ? 'active' : ''; ?>" href="sobre-mi.php">Sobre Mí</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($pagina_actual == 'contacto') ? 'active' : ''; ?>" href="contacto.php">Contacto</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>