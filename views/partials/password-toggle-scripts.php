<script>
(function() {
    function setToggleState(btn, visible) {
        const showIcon = btn.querySelector('.password-toggle-icon--show');
        const hideIcon = btn.querySelector('.password-toggle-icon--hide');
        btn.setAttribute('aria-pressed', visible ? 'true' : 'false');
        btn.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
        if (showIcon) showIcon.classList.toggle('hidden', visible);
        if (hideIcon) hideIcon.classList.toggle('hidden', !visible);
    }

    function initPasswordToggles(root) {
        (root || document).querySelectorAll('.password-toggle-btn[data-target]').forEach(function(btn) {
            if (btn.dataset.pwToggleInit === '1') return;
            btn.dataset.pwToggleInit = '1';

            btn.addEventListener('click', function() {
                const input = document.getElementById(btn.dataset.target || '');
                if (!input) return;
                const visible = input.type === 'password';
                input.type = visible ? 'text' : 'password';
                setToggleState(btn, visible);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { initPasswordToggles(); });
    } else {
        initPasswordToggles();
    }

    window.initPasswordToggles = initPasswordToggles;
})();
</script>
