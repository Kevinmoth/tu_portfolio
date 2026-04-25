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

        <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($nombre_sitio); ?>. <?php echo htmlspecialchars($footer_texto ?? 'Todos los derechos reservados.'); ?></p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
