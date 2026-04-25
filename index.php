<?php
require_once 'includes/header.php';

// Obtener proyectos destacados
$stmt_destacados = $conn->prepare("
    SELECT p.*, c.nombre as categoria_nombre, c.slug as categoria_slug 
    FROM proyectos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    WHERE p.destacado = TRUE AND p.activo = TRUE 
    ORDER BY p.fecha_creacion DESC 
    LIMIT 6
");
$stmt_destacados->execute();
$proyectos_destacados = $stmt_destacados->get_result();

// Obtener proyectos recientes
$stmt_recientes = $conn->prepare("
    SELECT p.*, c.nombre as categoria_nombre, c.slug as categoria_slug 
    FROM proyectos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    WHERE p.activo = TRUE 
    ORDER BY p.fecha_creacion DESC 
    LIMIT 6
");
$stmt_recientes->execute();
$proyectos_recientes = $stmt_recientes->get_result();

?>

<!-- Banner Hero -->
<div class="hero-banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-3 text-center mb-4 mb-md-0">
                <img src="assets/img/logo.png" alt="Logo" class="hero-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22150%22 height=%22150%22%3E%3Crect fill=%22%23ffffff%22 width=%22150%22 height=%22150%22 rx=%2275%22/%3E%3Ctext fill=%22%234a90e2%22 font-family=%22Arial%22 font-size=%2260%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EK%3C/text%3E%3C/svg%3E'">
            </div>
            <div class="col-md-9">
                <h1><?php echo htmlspecialchars($hero_titulo); ?></h1>
                <?php if ($hero_subtitulo): ?>
                    <p class="lead"><?php echo htmlspecialchars($hero_subtitulo); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="container">
    
    <!-- Sección de Proyectos Destacados -->
    <?php if ($proyectos_destacados->num_rows > 0): ?>
    <div class="carousel-section">
        <h2 class="carousel-title text-center">
            <i class="bi bi-star-fill text-warning"></i> Proyectos Destacados
        </h2>
        
        <div class="row g-4">
            <?php while ($proyecto = $proyectos_destacados->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <?php echo renderizar_tarjeta_proyecto($proyecto); ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Sección de Proyectos Recientes -->
    <?php if ($proyectos_recientes->num_rows > 0): ?>
    <div class="carousel-section">
        <h2 class="carousel-title text-center">
            <i class="bi bi-clock-history"></i> Proyectos Recientes
        </h2>
        
        <div class="row g-4">
            <?php
            $proyectos_recientes->data_seek(0);
            while ($proyecto = $proyectos_recientes->fetch_assoc()):
            ?>
            <div class="col-md-6 col-lg-4">
                <?php echo renderizar_tarjeta_proyecto($proyecto); ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Call to Action -->
    <div class="text-center my-5 py-5">
        <h3 class="mb-4">¿Quieres ver más proyectos?</h3>
        <a href="proyectos.php" class="btn btn-lg btn-primary-custom">
            <?php echo htmlspecialchars($hero_cta_texto); ?> <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    
</div>

<?php require_once 'includes/footer.php'; ?>