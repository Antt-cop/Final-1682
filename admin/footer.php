            </div> <!-- End container-fluid -->
        </div> <!-- End Page Content -->
    </div> <!-- End Admin Wrapper -->

    <!-- jQuery -->
    <script src="js/jquery-3.2.1.min.js"></script>
    <!-- Popper.js -->
    <script src="js/popper.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.min.js"></script>
    
    <!-- Admin Custom Scripts -->
    <script>
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
    <?php
    if (isset($extra_scripts)) {
        foreach ($extra_scripts as $script) {
            echo '    <script src="' . htmlspecialchars($script) . '"></script>' . "\n";
        }
    }
    ?>
</body>
</html>
