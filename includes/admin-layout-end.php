    </div>
</main>
<?php renderUiShell(); renderUiScripts(); ?>
<script>
(function () {
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('admin-drawer-overlay');
    const openBtn = document.getElementById('admin-menu-btn');
    const closeBtn = document.getElementById('admin-menu-close');

    const isMobile = () => window.matchMedia('(max-width: 767px)').matches;

    const openMenu = () => {
        if (!isMobile()) return;
        sidebar?.classList.remove('-translate-x-full');
        sidebar?.style.setProperty('transform', 'translateX(0)');
        overlay?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeMenu = () => {
        if (!isMobile()) return;
        sidebar?.style.setProperty('transform', 'translateX(-100%)');
        overlay?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    openBtn?.addEventListener('click', openMenu);
    closeBtn?.addEventListener('click', closeMenu);
    overlay?.addEventListener('click', closeMenu);
    sidebar?.querySelectorAll('nav a').forEach((link) => link.addEventListener('click', closeMenu));

    window.addEventListener('resize', () => {
        if (!isMobile()) {
            sidebar?.style.removeProperty('transform');
            overlay?.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        } else if (overlay?.classList.contains('hidden')) {
            sidebar?.style.setProperty('transform', 'translateX(-100%)');
        }
    });
})();
</script>
</body>
</html>
