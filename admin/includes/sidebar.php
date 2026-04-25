<?php
if (!isset($mensajes_no_leidos)) {
    $mensajes_no_leidos = $conn->query("SELECT COUNT(*) as total FROM mensajes_contacto WHERE leido = FALSE")->fetch_assoc()['total'];
}
$pagina_activa = $pagina_activa ?? '';
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h4>TU portfolio</h4>
        <small><?php echo htmlspecialchars($_SESSION['admin_usuario']); ?></small>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo $pagina_activa === 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="subir-proyecto.php" class="<?php echo $pagina_activa === 'subir' ? 'active' : ''; ?>">
                <i class="bi bi-plus-circle"></i> Subir Proyecto
            </a>
        </li>
        <li>
            <a href="editar-proyecto.php" class="<?php echo $pagina_activa === 'editar' ? 'active' : ''; ?>">
                <i class="bi bi-pencil-square"></i> Editar Proyectos
            </a>
        </li>
        <li>
            <a href="categorias.php" class="<?php echo $pagina_activa === 'categorias' ? 'active' : ''; ?>">
                <i class="bi bi-tags"></i> Categorias
            </a>
        </li>
        <li>
            <a href="mensajes.php" class="<?php echo $pagina_activa === 'mensajes' ? 'active' : ''; ?>">
                <i class="bi bi-envelope"></i> Mensajes
                <?php if ($mensajes_no_leidos > 0): ?>
                    <span class="badge bg-danger"><?php echo $mensajes_no_leidos; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="tecnologias.php" class="<?php echo $pagina_activa === 'tecnologias' ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i> Tecnologias
            </a>
        </li>
        <li>
            <a href="configuracion.php" class="<?php echo $pagina_activa === 'configuracion' ? 'active' : ''; ?>">
                <i class="bi bi-sliders"></i> Configuracion
            </a>
        </li>
        <li>
            <hr style="border-color: rgba(255,255,255,0.2);">
        </li>
        <li>
            <a href="../index.php" target="_blank">
                <i class="bi bi-globe"></i> Ver Sitio
            </a>
        </li>
        <li>
            <a href="logout.php">
                <i class="bi bi-box-arrow-right"></i> Cerrar Sesion
            </a>
        </li>
    </ul>
</div>
