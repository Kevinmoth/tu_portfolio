<?php
require_once 'includes/header.php';

// Obtener ID de categoría "Programas"
$categoria_query = $conn->prepare("SELECT id FROM categorias WHERE slug = 'programas'");
$categoria_query->execute();
$categoria_result = $categoria_query->get_result();
$categoria_id = $categoria_result->fetch_assoc()['id'];

// Configuración de paginación
$proyectos_por_pagina = intval(obtener_config('proyectos_por_pagina')) ?: 9;
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina - 1) * $proyectos_por_pagina;

// Búsqueda
$busqueda = isset($_GET['buscar']) ? limpiar_entrada($_GET['buscar']) : '';

// Construir consulta
$where = "WHERE p.activo = TRUE AND p.categoria_id = $categoria_id";
if ($busqueda) {
    $where .= " AND (p.titulo LIKE '%$busqueda%' OR p.descripcion LIKE '%$busqueda%')";
}

// Contar total de proyectos
$count_query = "SELECT COUNT(*) as total FROM proyectos p $where";
$count_result = $conn->query($count_query);
$total_proyectos = $count_result->fetch_assoc()['total'];
$total_paginas = ceil($total_proyectos / $proyectos_por_pagina);

// Obtener proyectos
$query = "
    SELECT p.*, c.nombre as categoria_nombre, c.slug as categoria_slug 
    FROM proyectos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    $where
    ORDER BY p.fecha_creacion DESC 
    LIMIT $proyectos_por_pagina OFFSET $offset
";
$proyectos = $conn->query($query);

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

<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="display-4">
            <i class="bi bi-window-desktop" style="color: var(--primary-blue);"></i> Programas
        </h1>
        <p class="lead text-muted">Software y aplicaciones desarrolladas</p>
    </div>
    
    <!-- Barra de búsqueda -->
    <div class="search-box">
        <form method="GET" action="programas.php">
            <div class="input-group">
                <input type="text" 
                       class="form-control" 
                       name="buscar" 
                       placeholder="Buscar programas..." 
                       value="<?php echo htmlspecialchars($busqueda); ?>">
                <button class="btn btn-primary-custom" type="submit">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </form>
    </div>
    
    <?php if ($busqueda): ?>
        <div class="text-center mb-4">
            <p class="text-muted">
                Resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong> 
                (<?php echo $total_proyectos; ?> programa<?php echo $total_proyectos != 1 ? 's' : ''; ?>)
                <a href="programas.php" class="btn btn-sm btn-outline-secondary ms-2">Limpiar</a>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Grid de proyectos -->
    <?php if ($proyectos->num_rows > 0): ?>
        <div class="row g-4 mb-5">
            <?php while ($proyecto = $proyectos->fetch_assoc()): ?>
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
                        
                        <?php if ($proyecto['destacado']): ?>
                            <span class="badge bg-warning text-dark mb-2">
                                <i class="bi bi-star-fill"></i> Destacado
                            </span>
                        <?php endif; ?>
                        
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
                                   class="btn btn-sm btn-primary-custom" target="_blank">
                                    <i class="bi bi-box-arrow-up-right"></i> Demo
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($proyecto['url_github']): ?>
                                <a href="<?php echo htmlspecialchars($proyecto['url_github']); ?>" 
                                   class="btn btn-sm btn-outline-dark" target="_blank">
                                    <i class="bi bi-github"></i> Código
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Paginación -->
        <?php if ($total_paginas > 1): ?>
        <nav aria-label="Navegación de páginas">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?><?php echo $busqueda ? '&buscar=' . urlencode($busqueda) : ''; ?>">
                        Anterior
                    </a>
                </li>
                
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo $busqueda ? '&buscar=' . urlencode($busqueda) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $pagina >= $total_paginas ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?><?php echo $busqueda ? '&buscar=' . urlencode($busqueda) : ''; ?>">
                        Siguiente
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-window-desktop" style="font-size: 4rem; color: #ccc;"></i>
            <h3 class="mt-3">No se encontraron programas</h3>
            <p class="text-muted">
                <?php echo $busqueda ? 'Intenta con otros términos de búsqueda' : 'Aún no hay programas publicados'; ?>
            </p>
        </div>
    <?php endif; ?>
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