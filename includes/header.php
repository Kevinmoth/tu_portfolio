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
    <style>
        :root {
            --primary-blue: #4a90e2;
            --dark-blue: #2c5aa0;
            --light-gray: #f5f5f5;
            --card-gray: #e8e8e8;
            --text-dark: #333333;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            color: var(--text-dark);
        }
        
        /* Navbar */
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.8rem;
            color: var(--text-dark) !important;
            letter-spacing: 2px;
        }
        
        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            margin: 0 10px;
            transition: color 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--primary-blue) !important;
        }
        
        /* Banner superior */
        .hero-banner {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        
        .hero-banner h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .hero-banner p {
            font-size: 1.2rem;
            opacity: 0.95;
        }
        
        .hero-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255,255,255,0.3);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        /* Tarjetas de proyectos */
        .project-card {
            background-color: var(--card-gray);
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .project-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .project-card-body {
            padding: 20px;
        }
        
        .project-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--text-dark);
            margin-bottom: 10px;
        }
        
        .project-description {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
        }
        
        .tech-badge {
            display: inline-block;
            padding: 4px 10px;
            margin: 3px;
            border-radius: 15px;
            font-size: 0.8rem;
            background-color: var(--primary-blue);
            color: white;
        }
        
        /* Botones */
        .btn-primary-custom {
            background-color: var(--primary-blue);
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .btn-primary-custom:hover {
            background-color: var(--dark-blue);
        }
        
        /* Búsqueda */
        .search-box {
            max-width: 500px;
            margin: 30px auto;
        }
        
        .search-box input {
            border-radius: 25px;
            padding: 12px 20px;
            border: 2px solid #ddd;
        }
        
        .search-box input:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }
        
        /* Footer */
        .footer {
            background-color: var(--text-dark);
            color: white;
            padding: 30px 0;
            margin-top: 60px;
        }
        
        .social-links a {
            color: white;
            font-size: 1.5rem;
            margin: 0 15px;
            transition: color 0.3s;
        }
        
        .social-links a:hover {
            color: var(--primary-blue);
        }
        
        /* Carrusel */
        .carousel-section {
            margin-bottom: 50px;
        }
        
        .carousel-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 30px;
            color: var(--text-dark);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-banner h1 {
                font-size: 2rem;
            }
            
            .hero-banner p {
                font-size: 1rem;
            }
            
            .project-card img {
                height: 180px;
            }
        }
    </style>
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
                        <a class="nav-link <?php echo ($pagina_actual == 'proyectos') ? 'active' : ''; ?>" href="proyectos.php">Proyectos</a>
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