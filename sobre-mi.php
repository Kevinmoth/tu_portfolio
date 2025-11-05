<?php
require_once 'includes/header.php';

// Obtener contenido de "Sobre Mí"
$sobre_mi_titulo = obtener_config('sobre_mi_titulo') ?? 'Sobre Mí';
$sobre_mi_contenido = obtener_config('sobre_mi_contenido') ?? 'Información no disponible.';
$email_contacto = obtener_config('email_contacto');

// Obtener estadísticas
$total_proyectos_query = $conn->query("SELECT COUNT(*) as total FROM proyectos WHERE activo = TRUE");
$total_proyectos = $total_proyectos_query->fetch_assoc()['total'];

$total_tecnologias_query = $conn->query("SELECT COUNT(DISTINCT tecnologia_id) as total FROM proyectos_tecnologias");
$total_tecnologias = $total_tecnologias_query->fetch_assoc()['total'];

$total_categorias_query = $conn->query("SELECT COUNT(*) as total FROM categorias");
$total_categorias = $total_categorias_query->fetch_assoc()['total'];
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="display-4"><?php echo htmlspecialchars($sobre_mi_titulo); ?></h1>
                <hr class="w-25 mx-auto" style="height: 3px; background-color: var(--primary-blue); opacity: 1;">
            </div>
            
            <!-- Contenido principal -->
            <div class="card shadow-sm border-0 mb-5">
                <div class="card-body p-5">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <img src="assets/img/logo.png" 
                                 alt="Perfil" 
                                 class="img-fluid rounded-circle shadow" 
                                 style="max-width: 250px; border: 5px solid var(--primary-blue);"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22250%22 height=%22250%22%3E%3Ccircle fill=%22%234a90e2%22 cx=%22125%22 cy=%22125%22 r=%22125%22/%3E%3Ctext fill=%22white%22 font-family=%22Arial%22 font-size=%2280%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EK%3C/text%3E%3C/svg%3E'">
                        </div>
                        <div class="col-md-8">
                            <div class="sobre-mi-content" style="font-size: 1.1rem; line-height: 1.8; color: #555;">
                                <?php echo nl2br(htmlspecialchars($sobre_mi_contenido)); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card text-center shadow-sm border-0 h-100" style="background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue)); color: white;">
                        <div class="card-body py-4">
                            <i class="bi bi-folder-fill" style="font-size: 3rem;"></i>
                            <h2 class="display-4 fw-bold mt-3"><?php echo $total_proyectos; ?></h2>
                            <p class="lead mb-0">Proyectos</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card text-center shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #6c757d, #495057); color: white;">
                        <div class="card-body py-4">
                            <i class="bi bi-gear-fill" style="font-size: 3rem;"></i>
                            <h2 class="display-4 fw-bold mt-3"><?php echo $total_tecnologias; ?></h2>
                            <p class="lead mb-0">Tecnologías</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card text-center shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #28a745, #20c997); color: white;">
                        <div class="card-body py-4">
                            <i class="bi bi-grid-fill" style="font-size: 3rem;"></i>
                            <h2 class="display-4 fw-bold mt-3"><?php echo $total_categorias; ?></h2>
                            <p class="lead mb-0">Categorías</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Habilidades/Tecnologías -->
            <div class="card shadow-sm border-0 mb-5">
                <div class="card-body p-5">
                    <h3 class="mb-4 text-center">
                        <i class="bi bi-code-square" style="color: var(--primary-blue);"></i> 
                        Tecnologías que manejo
                    </h3>
                    
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <?php
                        $tecnologias_query = $conn->query("SELECT nombre, color FROM tecnologias ORDER BY nombre");
                        while ($tech = $tecnologias_query->fetch_assoc()):
                        ?>
                            <span class="badge px-4 py-2" style="background-color: <?php echo $tech['color']; ?>; font-size: 1rem;">
                                <?php echo htmlspecialchars($tech['nombre']); ?>
                            </span>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            
            <!-- Redes sociales -->
            <div class="text-center mb-5">
                <h3 class="mb-4">Encuéntrame en</h3>
                <div class="d-flex justify-content-center gap-3">
                    <?php if ($github_url): ?>
                        <a href="<?php echo htmlspecialchars($github_url); ?>" 
                           target="_blank" 
                           class="btn btn-outline-dark btn-lg">
                            <i class="bi bi-github"></i> GitHub
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($linkedin_url): ?>
                        <a href="<?php echo htmlspecialchars($linkedin_url); ?>" 
                           target="_blank" 
                           class="btn btn-lg"
                           style="background-color: #0077b5; color: white;">
                            <i class="bi bi-linkedin"></i> LinkedIn
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($twitter_url): ?>
                        <a href="<?php echo htmlspecialchars($twitter_url); ?>" 
                           target="_blank" 
                           class="btn btn-lg"
                           style="background-color: #1da1f2; color: white;">
                            <i class="bi bi-twitter"></i> Twitter
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($email_contacto): ?>
                        <a href="mailto:<?php echo htmlspecialchars($email_contacto); ?>" 
                           class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-envelope-fill"></i> Email
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Call to Action -->
            <div class="text-center py-5" style="background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue)); border-radius: 15px; color: white;">
                <h3 class="mb-3">¿Quieres colaborar en un proyecto?</h3>
                <p class="lead mb-4">Estoy disponible para nuevas oportunidades y colaboraciones</p>
                <a href="contacto.php" class="btn btn-light btn-lg px-5">
                    <i class="bi bi-envelope-fill"></i> Contáctame
                </a>
            </div>
            
        </div>
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