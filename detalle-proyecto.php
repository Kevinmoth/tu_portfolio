<?php
require_once 'includes/header.php';

// Los estilos están en assets/css/public.css

// Verificar que se recibió un ID
if (!isset($_GET['id'])) {
    header('Location: proyectos.php');
    exit;
}

$id = intval($_GET['id']);

// Obtener el proyecto
$stmt = $conn->prepare("
    SELECT p.*, c.nombre as categoria_nombre, c.slug as categoria_slug 
    FROM proyectos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    WHERE p.id = ? AND p.activo = TRUE
");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header('Location: proyectos.php');
    exit;
}

$proyecto = $resultado->fetch_assoc();

// Registrar visita
$ip_visitante = $_SERVER['REMOTE_ADDR'];
$stmt_visita = $conn->prepare("INSERT INTO visitas_proyectos (proyecto_id, ip_visitante) VALUES (?, ?)");
$stmt_visita->bind_param("is", $id, $ip_visitante);
$stmt_visita->execute();

// Obtener tecnologías del proyecto
$stmt_tech = $conn->prepare("
    SELECT t.nombre, t.color 
    FROM tecnologias t 
    INNER JOIN proyectos_tecnologias pt ON t.id = pt.tecnologia_id 
    WHERE pt.proyecto_id = ?
");
$stmt_tech->bind_param("i", $id);
$stmt_tech->execute();
$tecnologias = $stmt_tech->get_result();

// Obtener proyectos relacionados (misma categoría)
$stmt_relacionados = $conn->prepare("
    SELECT p.*, c.nombre as categoria_nombre 
    FROM proyectos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    WHERE p.categoria_id = ? AND p.id != ? AND p.activo = TRUE 
    ORDER BY RAND() 
    LIMIT 3
");
$stmt_relacionados->bind_param("ii", $proyecto['categoria_id'], $id);
$stmt_relacionados->execute();
$proyectos_relacionados = $stmt_relacionados->get_result();

// Función para obtener tecnologías de un proyecto
function obtener_tecnologias($proyecto_id)
{
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

<div class="proyecto-detalle-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb" style="background: transparent; padding: 0;">
                        <li class="breadcrumb-item">
                            <a href="index.php" style="color: rgba(255,255,255,0.8);">Inicio</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?php echo $proyecto['categoria_slug']; ?>.php"
                                style="color: rgba(255,255,255,0.8);">
                                <?php echo htmlspecialchars($proyecto['categoria_nombre']); ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active" style="color: white;">
                            <?php echo htmlspecialchars($proyecto['titulo']); ?>
                        </li>
                    </ol>
                </nav>

                <h1 class="display-4 mb-3"><?php echo htmlspecialchars($proyecto['titulo']); ?></h1>

                <div class="mb-4">
                    <span class="badge bg-light text-dark me-2" style="font-size: 1rem; padding: 8px 15px;">
                        <i class="bi bi-folder"></i> <?php echo htmlspecialchars($proyecto['categoria_nombre']); ?>
                    </span>

                    <?php if ($proyecto['destacado']): ?>
                        <span class="badge bg-warning text-dark" style="font-size: 1rem; padding: 8px 15px;">
                            <i class="bi bi-star-fill"></i> Destacado
                        </span>
                    <?php endif; ?>
                </div>

                <p class="lead" style="font-size: 1.3rem; opacity: 0.95;">
                    <?php echo htmlspecialchars($proyecto['descripcion_corta'] ?? substr($proyecto['descripcion'], 0, 150) . '...'); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <!-- Columna Principal -->
        <div class="col-lg-8 mb-4">
            <!-- Imagen Principal -->
            <?php if ($proyecto['imagen']): ?>
                <div class="mb-4">
                    <img src="includes/conexion.php?img=proyecto&id=<?php echo $proyecto['id']; ?>"
                        alt="<?php echo htmlspecialchars($proyecto['titulo']); ?>" class="proyecto-imagen-principal">
                </div>
            <?php endif; ?>

            <!-- Descripción Completa -->
            <div class="descripcion-completa">
                <h3 class="mb-4">
                    <i class="bi bi-file-text" style="color: var(--primary-blue);"></i>
                    Descripción del Proyecto
                </h3>
                <div style="white-space: pre-wrap;">
                    <?php echo nl2br(htmlspecialchars($proyecto['descripcion'])); ?>
                </div>
            </div>

            <!-- Tecnologías Utilizadas -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">
                        <i class="bi bi-gear-fill" style="color: var(--primary-blue);"></i>
                        Tecnologías Utilizadas
                    </h5>
                    <div>
                        <?php
                        $tecnologias->data_seek(0);
                        while ($tech = $tecnologias->fetch_assoc()):
                            ?>
                            <span class="tech-badge me-2 mb-2"
                                style="background-color: <?php echo $tech['color']; ?>; font-size: 1.1rem; padding: 8px 15px;">
                                <?php echo htmlspecialchars($tech['nombre']); ?>
                            </span>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Información del Proyecto -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-4">
                        <i class="bi bi-info-circle-fill" style="color: var(--primary-blue);"></i>
                        Información
                    </h5>

                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">
                            <i class="bi bi-calendar"></i> Fecha de publicación
                        </small>
                        <strong><?php echo date('d/m/Y', strtotime($proyecto['fecha_creacion'])); ?></strong>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">
                            <i class="bi bi-clock-history"></i> Última actualización
                        </small>
                        <strong><?php echo date('d/m/Y', strtotime($proyecto['fecha_actualizacion'])); ?></strong>
                    </div>

                    <?php
                    // Contar visitas
                    $visitas_query = $conn->query("SELECT COUNT(*) as total FROM visitas_proyectos WHERE proyecto_id = {$proyecto['id']}");
                    $visitas = $visitas_query->fetch_assoc()['total'];
                    ?>

                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">
                            <i class="bi bi-eye"></i> Visitas
                        </small>
                        <strong><?php echo $visitas; ?> visualizaciones</strong>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-4">
                        <i class="bi bi-link-45deg" style="color: var(--primary-blue);"></i>
                        Enlaces
                    </h5>

                    <div class="d-grid gap-3">
                        <?php if ($proyecto['url_demo']): ?>
                            <a href="<?php echo htmlspecialchars($proyecto['url_demo']); ?>" target="_blank"
                                class="btn btn-primary btn-accion">
                                <i class="bi bi-box-arrow-up-right"></i> Ver Demo en Vivo
                            </a>
                        <?php endif; ?>

                        <?php if ($proyecto['url_github']): ?>
                            <a href="<?php echo htmlspecialchars($proyecto['url_github']); ?>" target="_blank"
                                class="btn btn-dark btn-accion">
                                <i class="bi bi-github"></i> Ver en GitHub
                            </a>
                        <?php endif; ?>

                        <?php if ($proyecto['url_repo']): ?>
                            <a href="<?php echo htmlspecialchars($proyecto['url_repo']); ?>" target="_blank"
                                class="btn btn-outline-primary btn-accion">
                                <i class="bi bi-code-slash"></i> Ver Repositorio
                            </a>
                        <?php endif; ?>

                        <?php if (!$proyecto['url_demo'] && !$proyecto['url_github'] && !$proyecto['url_repo']): ?>
                            <p class="text-muted text-center mb-0">No hay enlaces disponibles</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Compartir -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="mb-3">
                        <i class="bi bi-share-fill" style="color: var(--primary-blue);"></i>
                        Compartir
                    </h5>

                    <div class="d-flex gap-2 justify-content-center">
                        <?php
                        $url_actual = urlencode("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                        $titulo_encoded = urlencode($proyecto['titulo']);
                        ?>

                        <a href="https://twitter.com/intent/tweet?url=<?php echo $url_actual; ?>&text=<?php echo $titulo_encoded; ?>"
                            target="_blank" class="btn btn-outline-info btn-sm" title="Compartir en Twitter">
                            <i class="bi bi-twitter"></i>
                        </a>

                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $url_actual; ?>"
                            target="_blank" class="btn btn-outline-primary btn-sm" title="Compartir en Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>

                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $url_actual; ?>"
                            target="_blank" class="btn btn-outline-primary btn-sm" title="Compartir en LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>

                        <button onclick="copiarURL()" class="btn btn-outline-secondary btn-sm" title="Copiar enlace">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Proyectos Relacionados -->
    <?php if ($proyectos_relacionados->num_rows > 0): ?>
        <div class="mt-5">
            <h3 class="mb-4">
                <i class="bi bi-grid-3x3-gap-fill" style="color: var(--primary-blue);"></i>
                Proyectos Relacionados
            </h3>

            <div class="row g-4">
                <?php while ($relacionado = $proyectos_relacionados->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <a href="detalle-proyecto.php?id=<?php echo $relacionado['id']; ?>" style="text-decoration: none;">
                            <div class="project-card proyecto-relacionado-card">
                                <?php if ($relacionado['imagen']): ?>
                                    <img src="includes/conexion.php?img=proyecto&id=<?php echo $relacionado['id']; ?>"
                                        alt="<?php echo htmlspecialchars($relacionado['titulo']); ?>">
                                <?php else: ?>
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='200'%3E%3Crect fill='%23e9ecef' width='400' height='200'/%3E%3Ctext fill='%236c757d' font-family='Arial' font-size='20' x='50%25' y='50%25' text-anchor='middle' dy='.3em'%3ESin imagen%3C/text%3E%3C/svg%3E"
                                        alt="Sin imagen">
                                <?php endif; ?>

                                <div class="project-card-body">
                                    <h5 class="project-title"><?php echo htmlspecialchars($relacionado['titulo']); ?></h5>

                                    <span class="badge bg-secondary mb-2">
                                        <?php echo htmlspecialchars($relacionado['categoria_nombre']); ?>
                                    </span>

                                    <p class="project-description">
                                        <?php
                                        $desc = $relacionado['descripcion_corta'] ?? $relacionado['descripcion'];
                                        echo htmlspecialchars(substr($desc, 0, 80)) . '...';
                                        ?>
                                    </p>

                                    <div class="mb-3">
                                        <?php
                                        $techs_rel = obtener_tecnologias($relacionado['id']);
                                        $count = 0;
                                        while ($tech = $techs_rel->fetch_assoc()):
                                            if ($count < 3):
                                                ?>
                                                <span class="tech-badge" style="background-color: <?php echo $tech['color']; ?>">
                                                    <?php echo htmlspecialchars($tech['nombre']); ?>
                                                </span>
                                                <?php
                                                $count++;
                                            endif;
                                        endwhile;
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Botón Volver -->
    <div class="text-center mt-5">
        <a href="<?php echo $proyecto['categoria_slug']; ?>.php" class="btn btn-outline-primary btn-lg">
            <i class="bi bi-arrow-left"></i> Volver a <?php echo htmlspecialchars($proyecto['categoria_nombre']); ?>
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

        <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($nombre_sitio); ?>. Todos los
            derechos reservados.</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function copiarURL() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(function () {
            alert('¡Enlace copiado al portapapeles!');
        }, function () {
            alert('Error al copiar el enlace');
        });
    }
</script>
</body>

</html>