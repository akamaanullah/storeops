<?php
/**
 * Global Footer View Template (layout/footer.php)
 */
$currentUser = Auth::user();
?>
    </main>

    <!-- Footer Copyright panel -->
    <footer class="bg-white border-t border-natural-border py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center text-natural-muted text-xs">
            <?php include dirname(__DIR__, 2) . '/partials/layout-site-footer.php'; ?>
        </div>
    </footer>
    </div> <!-- Closes the flex-1 layout frame wrapper opened in header.php -->

    <!-- Global Application Scripts -->
    <?php if ($currentUser): ?>
    <script>
        const CSRF_TOKEN = <?= json_encode(CSRF::generateToken()) ?>;
    </script>
    <?php include dirname(__DIR__, 2) . '/partials/realtime-page-config.php'; ?>
    <?php include dirname(__DIR__, 2) . '/partials/realtime-polling-scripts.php'; ?>
    <?php include dirname(__DIR__, 2) . '/partials/browser-notification-scripts.php'; ?>
    <?php include dirname(__DIR__, 2) . '/partials/notification-bell-scripts.php'; ?>
    <?php include dirname(__DIR__, 2) . '/partials/password-toggle-scripts.php'; ?>
    <?php endif; ?>

</body>
</html>
