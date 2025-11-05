<?php
require_once 'includes/header.php';

$mensaje_exito = '';
$mensaje_error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = limpiar_entrada($_POST['nombre']);
    $email = limpiar_entrada($_POST['email']);
    $asunto = limpiar_entrada($_POST['asunto']);
    $mensaje = limpiar_entrada($_POST['mensaje']);
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($mensaje)) {
        $mensaje_error = 'Por favor, completa todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = 'Por favor, ingresa un email válido.';
    } else {
        // Insertar en base de datos
        $stmt = $conn->prepare("INSERT INTO mensajes_contacto (nombre, email, asunto, mensaje) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $asunto, $mensaje);
        
        if ($stmt->execute()) {
            // Enviar email (opcional)
            $email_destino = obtener_config('email_contacto');
            if ($email_destino) {
                $headers = "From: $email\r\n";
                $headers .= "Reply-To: $email\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                
                $asunto_email = "Contacto desde Portfolio: " . $asunto;
                $cuerpo_email = "
                    <h2>Nuevo mensaje de contacto</h2>
                    <p><strong>Nombre:</strong> $nombre</p>
                    <p><strong>Email:</strong> $email</p>
                    <p><strong>Asunto:</strong> $asunto</p>
                    <p><strong>Mensaje:</strong></p>
                    <p>$mensaje</p>
                ";
                
                @mail($email_destino, $asunto_email, $cuerpo_email, $headers);
            }
            
            $mensaje_exito = '¡Mensaje enviado con éxito! Te responderé pronto.';
            
            // Limpiar campos
            $nombre = $email = $asunto = $mensaje = '';
        } else {
            $mensaje_error = 'Hubo un error al enviar el mensaje. Intenta nuevamente.';
        }
    }
}

$email_contacto = obtener_config('email_contacto');
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="display-4">Contacto</h1>
                <p class="lead text-muted">¿Tienes alguna pregunta o propuesta? ¡Escríbeme!</p>
                <hr class="w-25 mx-auto" style="height: 3px; background-color: var(--primary-blue); opacity: 1;">
            </div>
            
            <!-- Mensajes de alerta -->
            <?php if ($mensaje_exito): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i> <?php echo $mensaje_exito; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($mensaje_error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $mensaje_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Formulario de contacto -->
            <div class="card shadow-sm border-0 mb-5">
                <div class="card-body p-5">
                    <form method="POST" action="contacto.php">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="nombre" class="form-label">
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="<?php echo htmlspecialchars($nombre ?? ''); ?>"
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label for="email" class="form-label">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control form-control-lg" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="asunto" class="form-label">Asunto</label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="asunto" 
                                   name="asunto" 
                                   value="<?php echo htmlspecialchars($asunto ?? ''); ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="mensaje" class="form-label">
                                Mensaje <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control form-control-lg" 
                                      id="mensaje" 
                                      name="mensaje" 
                                      rows="6" 
                                      required><?php echo htmlspecialchars($mensaje ?? ''); ?></textarea>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary-custom btn-lg px-5">
                                <i class="bi bi-send-fill"></i> Enviar Mensaje
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Información adicional -->
            <div class="row g-4">
                <?php if ($email_contacto): ?>
                <div class="col-md-4">
                    <div class="card text-center shadow-sm border-0 h-100">
                        <div class="card-body py-4">
                            <i class="bi bi-envelope-fill" style="font-size: 2.5rem; color: var(--primary-blue);"></i>
                            <h5 class="mt-3">Email</h5>
                            <p class="text-muted mb-0">
                                <a href="mailto:<?php echo htmlspecialchars($email_contacto); ?>" 
                                   style="color: var(--primary-blue); text-decoration: none;">
                                    <?php echo htmlspecialchars($email_contacto); ?>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($github_url): ?>
                <div class="col-md-4">
                    <div class="card text-center shadow-sm border-0 h-100">
                        <div class="card-body py-4">
                            <i class="bi bi-github" style="font-size: 2.5rem; color: #333;"></i>
                            <h5 class="mt-3">GitHub</h5>
                            <p class="text-muted mb-0">
                                <a href="<?php echo htmlspecialchars($github_url); ?>" 
                                   target="_blank"
                                   style="color: var(--primary-blue); text-decoration: none;">
                                    Ver mi perfil
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($linkedin_url): ?>
                <div class="col-md-4">
                    <div class="card text-center shadow-sm border-0 h-100">
                        <div class="card-body py-4">
                            <i class="bi bi-linkedin" style="font-size: 2.5rem; color: #0077b5;"></i>
                            <h5 class="mt-3">LinkedIn</h5>
                            <p class="text-muted mb-0">
                                <a href="<?php echo htmlspecialchars($linkedin_url); ?>" 
                                   target="_blank"
                                   style="color: var(--primary-blue); text-decoration: none;">
                                    Conectar
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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