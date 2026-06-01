<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const toggleBtn = document.getElementById('appDarkToggle');

    if (!toggleBtn) {
        return;
    }

    function syncTheme(isDark) {
        document.body.classList.toggle('dark-mode', isDark);
        toggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
    }

    syncTheme(localStorage.getItem('darkMode') === 'enabled');

    toggleBtn.addEventListener('click', function () {
        const isDark = !document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
        syncTheme(isDark);
    });
})();
</script>
</body>
</html>
