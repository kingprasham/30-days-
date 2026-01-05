        </div><!-- End content-wrapper -->
    </div><!-- End main-content -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom JS -->
    <script src="<?= ASSETS_URL ?>/js/main.js"></script>

    <script>
        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');

            // Check if desktop or mobile
            if (window.innerWidth > 991) {
                // Desktop: collapse sidebar
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('sidebar-collapsed');

                // Save preference to localStorage
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            } else {
                // Mobile: slide sidebar
                sidebar.classList.toggle('active');
                document.querySelector('.sidebar-overlay').classList.toggle('active');
            }
        }

        // Restore sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth > 991) {
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (isCollapsed) {
                    document.getElementById('sidebar').classList.add('collapsed');
                    document.querySelector('.main-content').classList.add('sidebar-collapsed');
                }
            }
        });

        // Initialize DataTables
        $(document).ready(function() {
            if ($('.data-table').length) {
                $('.data-table').DataTable({
                    responsive: true,
                    pageLength: 25,
                    language: {
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "<i class='fas fa-chevron-right'></i>",
                            previous: "<i class='fas fa-chevron-left'></i>"
                        }
                    }
                });
            }

            // Initialize Select2
            if ($('.select2').length) {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }

            // Initialize Flatpickr
            if ($('.datepicker').length) {
                flatpickr('.datepicker', {
                    dateFormat: 'd/m/Y',
                    allowInput: true
                });
            }

            // CSRF Token for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '<?= generateCSRFToken() ?>'
                }
            });
        });

        // Confirm Delete
        function confirmDelete(message) {
            return confirm(message || 'Are you sure you want to delete this item?');
        }

        // Format currency
        function formatCurrency(amount) {
            return '<?= CURRENCY_SYMBOL ?> ' + parseFloat(amount).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Format number
        function formatNumber(num) {
            return parseFloat(num).toLocaleString('en-IN');
        }
    </script>
</body>
</html>
<?php ob_end_flush(); ?>
