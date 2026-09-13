// Toggle password visibility
function togglePassword(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);

  const openEye = `
    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
    <circle cx="12" cy="12" r="3"></circle>
  `;

  const slashedEye = `
    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
    <line x1="1" y1="1" x2="23" y2="23"></line>
  `;

  if (input.type === "password") {
    input.type = "text";
    icon.innerHTML = openEye;
  } else {
    input.type = "password";
    icon.innerHTML = slashedEye;
  }
}


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

