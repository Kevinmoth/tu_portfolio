<?php
require_once 'includes/header.php';

// Configuración de paginación
$proyectos_por_pagina = intval(obtener_config('proyectos_por_pagina')) ?: 9;
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina - 1) * $proyectos_por_pagina;

// Búsqueda
$busqueda = isset($_GET['buscar']) ? limpiar_entrada($_GET['buscar']) : '';

// Construir consulta
$where = "WHERE p.activo = TRUE";
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

?>

<div class="container my-5">
    <h1 class="text-center mb-4">Todos los Proyectos</h1>
    
    <!-- Barra de búsqueda -->
    <div class="search-box">
        <form method="GET" action="proyectos.php">
            <div class="input-group">
                <input type="text" 
                       class="form-control" 
                       name="buscar" 
                       placeholder="Buscar proyectos..." 
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
                (<?php echo $total_proyectos; ?> proyecto<?php echo $total_proyectos != 1 ? 's' : ''; ?>)
                <a href="proyectos.php" class="btn btn-sm btn-outline-secondary ms-2">Limpiar</a>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Grid de proyectos -->
    <?php if ($proyectos->num_rows > 0): ?>
        <div class="row g-4 mb-5">
            <?php while ($proyecto = $proyectos->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <?php echo renderizar_tarjeta_proyecto($proyecto); ?>
            </div>
            <?php endwhile; ?>
        </div>

        <?php echo renderizar_paginacion($pagina, $total_paginas, $busqueda); ?>
        
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
            <h3 class="mt-3">No se encontraron proyectos</h3>
            <p class="text-muted">
                <?php echo $busqueda ? 'Intenta con otros términos de búsqueda' : 'Aún no hay proyectos publicados'; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>