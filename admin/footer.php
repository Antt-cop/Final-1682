            </div> <!-- End container-fluid -->
        </div> <!-- End Page Content -->
    </div> <!-- End Admin Wrapper -->

    <!-- jQuery -->
    <script src="<?= BASE_URL ?>/assets/js/jquery-3.2.1.min.js"></script>
    <!-- Popper.js -->
    <script src="<?= BASE_URL ?>/assets/js/popper.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.min.js"></script>
    
    <!-- Admin Custom Scripts -->
    <script>
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
    if (isset($extra_scripts)) {
        foreach ($extra_scripts as $script) {
            echo '    <script src="' . BASE_URL . '/assets/' . htmlspecialchars($script) . '"></script>' . "\n";
        }
    }
    ?>
</body>
</html>

