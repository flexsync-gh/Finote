document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('[data-theme-toggle], #appDarkToggle, #darkToggle');

    function applyTheme(isDark) {
        document.body.classList.toggle('dark-mode', isDark);

        buttons.forEach(function (button) {
            button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        });
    }

    applyTheme(localStorage.getItem('darkMode') === 'enabled');

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            const isDark = !document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
            applyTheme(isDark);
        });
    });
});
