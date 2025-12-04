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

// Función para obtener tecnologías de un proyecto
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
?>

<!-- Banner Hero -->
<div class="hero-banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-3 text-center mb-4 mb-md-0">
                <img src="assets/img/logo.jpeg" alt="Logo" class="hero-image">

            </div>
            <div class="col-md-9">
                <h1>Kevin Kronbauer</h1>
                <p class="lead">Analista de sistemas y Desarrollador buscando crear soluciones innovadoras.<br> En esta pagina vas a poder conocer mis proyectos y habilidades.</p>
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
                <a href="detalle-proyecto.php?id=<?php echo $proyecto['id']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="project-card">
                        <?php if ($proyecto['imagen']): ?>
                            <img src="includes/conexion.php?img=proyecto&id=<?php echo $proyecto['id']; ?>" 
                                 alt="<?php echo htmlspecialchars($proyecto['titulo']); ?>">
                        <?php else: ?>
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='200'%3E%3Crect fill='%23e9ecef' width='400' height='200'/%3E%3Ctext fill='%236c757d' font-family='Arial' font-size='20' x='50%25' y='50%25' text-anchor='middle' dy='.3em'%3ESin imagen%3C/text%3E%3C/svg%3E" 
                                 alt="Sin imagen">
                        <?php endif; ?>
                        
                        <div class="project-card-body">
                            <h3 class="project-title"><?php echo htmlspecialchars($proyecto['titulo']); ?></h3>
                        
                            <span class="badge bg-secondary mb-2">
                                <?php echo htmlspecialchars($proyecto['categoria_nombre']); ?>
                            </span>
                            
                            <p class="project-description">
                                <?php 
                                $desc = $proyecto['descripcion_corta'] ?? $proyecto['descripcion'];
                                echo htmlspecialchars(substr($desc, 0, 100)) . (strlen($desc) > 100 ? '...' : ''); 
                                ?>
                            </p>
                            
                            <div class="mb-3">
                                <?php
                                $tecnologias = obtener_tecnologias($proyecto['id']);
                                while ($tech = $tecnologias->fetch_assoc()):
                                ?>
                                    <span class="tech-badge" style="background-color: <?php echo $tech['color']; ?>">
                                        <?php echo htmlspecialchars($tech['nombre']); ?>
                                    </span>
                                <?php endwhile; ?>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <?php if ($proyecto['url_demo']): ?>
                                    <a href="<?php echo htmlspecialchars($proyecto['url_demo']); ?>" 
                                       class="btn btn-sm btn-primary-custom" target="_blank" onclick="event.stopPropagation();">
                                        <i class="bi bi-box-arrow-up-right"></i> Demo
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($proyecto['url_github']): ?>
                                    <a href="<?php echo htmlspecialchars($proyecto['url_github']); ?>" 
                                       class="btn btn-sm btn-outline-dark" target="_blank" onclick="event.stopPropagation();">
                                        <i class="bi bi-github"></i> Código
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
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
            $proyectos_recientes->data_seek(0); // Resetear el puntero
            while ($proyecto = $proyectos_recientes->fetch_assoc()): 
            ?>
            <div class="col-md-6 col-lg-4">
                <a href="detalle-proyecto.php?id=<?php echo $proyecto['id']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="project-card">
                        <?php if ($proyecto['imagen']): ?>
                            <img src="includes/conexion.php?img=proyecto&id=<?php echo $proyecto['id']; ?>" 
                                 alt="<?php echo htmlspecialchars($proyecto['titulo']); ?>">
                        <?php else: ?>
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='200'%3E%3Crect fill='%23e9ecef' width='400' height='200'/%3E%3Ctext fill='%236c757d' font-family='Arial' font-size='20' x='50%25' y='50%25' text-anchor='middle' dy='.3em'%3ESin imagen%3C/text%3E%3C/svg%3E" 
                                 alt="Sin imagen">
                        <?php endif; ?>
                        
                        <div class="project-card-body">
                            <h3 class="project-title"><?php echo htmlspecialchars($proyecto['titulo']); ?></h3>
                            
                            <span class="badge bg-secondary mb-2">
                                <?php echo htmlspecialchars($proyecto['categoria_nombre']); ?>
                            </span>
                            
                            <p class="project-description">
                                <?php 
                                $desc = $proyecto['descripcion_corta'] ?? $proyecto['descripcion'];
                                echo htmlspecialchars(substr($desc, 0, 100)) . (strlen($desc) > 100 ? '...' : ''); 
                                ?>
                            </p>
                        
                            <div class="mb-3">
                                <?php
                                $tecnologias = obtener_tecnologias($proyecto['id']);
                                while ($tech = $tecnologias->fetch_assoc()):
                                ?>
                                    <span class="tech-badge" style="background-color: <?php echo $tech['color']; ?>">
                                        <?php echo htmlspecialchars($tech['nombre']); ?>
                                    </span>
                                <?php endwhile; ?>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <?php if ($proyecto['url_demo']): ?>
                                    <a href="<?php echo htmlspecialchars($proyecto['url_demo']); ?>" 
                                       class="btn btn-sm btn-primary-custom" target="_blank" onclick="event.stopPropagation();">
                                        <i class="bi bi-box-arrow-up-right"></i> Demo
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($proyecto['url_github']): ?>
                                    <a href="<?php echo htmlspecialchars($proyecto['url_github']); ?>" 
                                       class="btn btn-sm btn-outline-dark" target="_blank" onclick="event.stopPropagation();">
                                        <i class="bi bi-github"></i> Código
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Call to Action -->
    <div class="text-center my-5 py-5">
        <h3 class="mb-4">¿Quieres ver más proyectos?</h3>
        <a href="proyectos.php" class="btn btn-lg btn-primary-custom">
            Ver Todos los Proyectos <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    
</div>

<!-- Footer -->
<footer class="footer">
    <div class="container text-center">
        <div class="social-links mb-3">
            <?php if ($github_url): ?>
                <a href="<?php echo htmlspecialchars($github_url); ?>" target="_blank">
                    <i class="bi bi-github"></i>
                </a>
            <?php endif; ?>
            
            <?php if ($linkedin_url): ?>
                <a href="<?php echo htmlspecialchars($linkedin_url); ?>" target="_blank">
                    <i class="bi bi-linkedin"></i>
                </a>
            <?php endif; ?>
            
            <?php if ($twitter_url): ?>
                <a href="<?php echo htmlspecialchars($twitter_url); ?>" target="_blank">
                    <i class="bi bi-twitter"></i>
                </a>
            <?php endif; ?>
        </div>
        
        <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($nombre_sitio); ?>. Todos los derechos reservados.</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>