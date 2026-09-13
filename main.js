

window.addEventListener('load', () => {
  const loadingScreen = document.getElementById('loading-screen');
  
  // Add the hidden class to trigger the CSS fade out
  if (loadingScreen) {
    loadingScreen.classList.add('hidden');
  }
});

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

      // Close the menu after a nav link is tapped
      navMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', closeMenu);
      });

      // Close the menu automatically if the viewport grows past the mobile breakpoint
      window.addEventListener('resize', () => {
        if (window.innerWidth > 768) closeMenu();
      });

      // Close the menu if the user taps/clicks outside of it
      document.addEventListener('click', (event) => {
        const clickedInsideMenu = navMenu.contains(event.target) || hamburger.contains(event.target);
        if (!clickedInsideMenu && navMenu.classList.contains('active')) {
          closeMenu();
        }
      });