<?php
require_once 'includes/header.php';

$slug = isset($_GET['slug']) ? limpiar_entrada($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: proyectos.php');
    exit;
}

// Obtener la categoria por slug
$stmt_cat = $conn->prepare("SELECT id, nombre, descripcion, icono FROM categorias WHERE slug = ?");
$stmt_cat->bind_param("s", $slug);
$stmt_cat->execute();
$cat_result = $stmt_cat->get_result();

if ($cat_result->num_rows === 0) {
    header('Location: proyectos.php');
    exit;
}

$categoria = $cat_result->fetch_assoc();
$categoria_id = $categoria['id'];
$categoria_nombre = $categoria['nombre'];
$categoria_icono = $categoria['icono'] ?? 'bi-folder';
$categoria_desc = $categoria['descripcion'] ?? '';

// Paginacion
$proyectos_por_pagina = intval(obtener_config('proyectos_por_pagina')) ?: 9;
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina - 1) * $proyectos_por_pagina;

// Busqueda
$busqueda = isset($_GET['buscar']) ? limpiar_entrada($_GET['buscar']) : '';

$where = "WHERE p.activo = TRUE AND p.categoria_id = $categoria_id";
if ($busqueda) {
    $where .= " AND (p.titulo LIKE '%$busqueda%' OR p.descripcion LIKE '%$busqueda%')";
}

// Contar proyectos
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
    <div class="text-center mb-5">
        <h1 class="display-4">
            <i class="bi <?php echo htmlspecialchars($categoria_icono); ?>" style="color: var(--primary-blue);"></i>
            <?php echo htmlspecialchars($categoria_nombre); ?>
        </h1>
        <?php if ($categoria_desc): ?>
            <p class="lead text-muted"><?php echo htmlspecialchars($categoria_desc); ?></p>
        <?php endif; ?>
    </div>

    <!-- Barra de busqueda -->
    <div class="search-box">
        <form method="GET" action="categoria.php">
            <input type="hidden" name="slug" value="<?php echo htmlspecialchars($slug); ?>">
            <div class="input-group">
                <input type="text"
                       class="form-control"
                       name="buscar"
                       placeholder="Buscar en <?php echo htmlspecialchars($categoria_nombre); ?>..."
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
                <a href="categoria.php?slug=<?php echo htmlspecialchars($slug); ?>" class="btn btn-sm btn-outline-secondary ms-2">Limpiar</a>
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
            <i class="bi <?php echo htmlspecialchars($categoria_icono); ?>" style="font-size: 4rem; color: #ccc;"></i>
            <h3 class="mt-3">No se encontraron proyectos</h3>
            <p class="text-muted">
                <?php echo $busqueda ? 'Intenta con otros terminos de busqueda' : 'Aun no hay proyectos en esta categoria'; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
