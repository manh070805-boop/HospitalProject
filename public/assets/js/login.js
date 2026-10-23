(function () {
    const roleInput = document.getElementById('roleInput');
    const roleChips = document.querySelectorAll('[data-role-chip]');
    const passwordInput = document.getElementById('password');
    const toggleButton = document.querySelector('[data-toggle-password]');
    const eyeIcon = document.getElementById('eyeIcon');
    const loginForm = document.getElementById('loginForm');
    const loginButton = document.getElementById('loginBtn');

    const eyeOpen = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    const eyeClosed = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';

    function selectRole(role) {
        roleChips.forEach(function (chip) {
            chip.classList.toggle('active', chip.dataset.role === role);
        });

        if (roleInput) {
            roleInput.value = role;
        }
    }

    roleChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            selectRole(chip.dataset.role || 'admin');
        });
    });

    if (toggleButton && passwordInput && eyeIcon) {
        toggleButton.addEventListener('click', function () {
            const showPassword = passwordInput.type === 'password';
            passwordInput.type = showPassword ? 'text' : 'password';
            eyeIcon.innerHTML = showPassword ? eyeClosed : eyeOpen;
        });
    }

    if (loginForm && loginButton) {
        loginForm.addEventListener('submit', function () {
            loginButton.disabled = true;
            loginButton.classList.add('is-loading');
            loginButton.innerHTML = [
                '<svg class="btn-login-spinner" viewBox="0 0 24 24">',
                '<path d="M21 12a9 9 0 1 1-6.219-8.56"/>',
                '</svg>',
                'Đang xác thực...'
            ].join('');
        });
    }

    if (roleInput) {
        selectRole(roleInput.value || 'admin');
    }
})();
