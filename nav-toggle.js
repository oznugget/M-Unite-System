document.addEventListener('DOMContentLoaded', function () {
  const hamburger = document.getElementById('hamburger-menu');
  const navMenu = document.getElementById('nav-menu');
  const hamburgerIcon = hamburger.querySelector('i');

  function toggleMenu() {
    const isOpen = navMenu.classList.toggle('active');
    hamburger.classList.toggle('active', isOpen);
    hamburgerIcon.classList.toggle('fa-bars', !isOpen);
    hamburgerIcon.classList.toggle('fa-xmark', isOpen);
  }

  function closeMenu() {
    navMenu.classList.remove('active');
    hamburger.classList.remove('active');
    hamburgerIcon.classList.add('fa-bars');
    hamburgerIcon.classList.remove('fa-xmark');
  }

  hamburger.addEventListener('click', toggleMenu);

  navMenu.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', closeMenu);
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth > 768) closeMenu();
  });

  document.addEventListener('click', function (event) {
    const clickedInsideMenu = navMenu.contains(event.target) || hamburger.contains(event.target);
    if (!clickedInsideMenu && navMenu.classList.contains('active')) {
      closeMenu();
    }
  });
});