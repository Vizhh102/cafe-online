            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toggle = document.getElementById('adminMenuToggle');
            var sidebar = document.getElementById('adminSidebar');
            if (toggle && sidebar) {
                toggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                    toggle.classList.toggle('active');
                    document.body.classList.toggle('admin-sidebar-open');
                });
                document.addEventListener('click', function(e) {
                    if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                        sidebar.classList.remove('open');
                        toggle.classList.remove('active');
                        document.body.classList.remove('admin-sidebar-open');
                    }
                });
            }
        });
    </script>
</body>
</html>
