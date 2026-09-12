document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    if (params.get('registration') === 'success') {
        const toast = document.createElement('div');
        toast.textContent = 'Account created successfully! Please sign in below.';
        toast.className = 'login-toast';
        document.body.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.add('login-toast-visible');
        });

        setTimeout(() => {
            toast.classList.remove('login-toast-visible');
            toast.addEventListener('transitionend', () => toast.remove(), { once: true });
        }, 3000);

        // Clean the URL so refreshing doesn't re-trigger the popup
        const cleanUrl = window.location.origin + window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
});