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

document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.getElementById('hamburger-menu');
  const navMenu   = document.getElementById('nav-menu');
  if (!hamburger || !navMenu) return;

  const icon = hamburger.querySelector('i');

  const toggleMenu = () => {
    const isOpen = navMenu.classList.toggle('active');
    hamburger.classList.toggle('active', isOpen);
    icon.classList.toggle('fa-bars', !isOpen);
    icon.classList.toggle('fa-xmark', isOpen);
  };

  hamburger.addEventListener('click', toggleMenu);

  navMenu.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
    navMenu.classList.remove('active');
    hamburger.classList.remove('active');
    icon.classList.add('fa-bars');
    icon.classList.remove('fa-xmark');
  }));
});