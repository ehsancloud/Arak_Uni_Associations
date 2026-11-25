</main>

<script>
(function() {
    var toggleBtn = document.getElementById('adminSidebarToggle');
    var closeBtn = document.getElementById('adminSidebarClose');
    var sidebar = document.getElementById('adminSidebar');
    var overlay = document.getElementById('adminSidebarOverlay');

    function openSidebar() {
        if (sidebar && overlay) {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.classList.add('admin-sidebar-open');
            if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
        }
    }

    function closeSidebar() {
        if (sidebar && overlay) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('admin-sidebar-open');
            if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
        }
    }

    if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSidebar();
    });

    if (sidebar) {
        var navLinks = sidebar.querySelectorAll('.sidebar-nav a');
        for (var i = 0; i < navLinks.length; i++) {
            navLinks[i].addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    closeSidebar();
                }
            });
        }
    }
})();
</script>
</body>
</html>
