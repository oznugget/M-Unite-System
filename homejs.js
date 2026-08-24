(function() {
  'use strict';

  document.addEventListener('DOMContentLoaded', function() {
    const slides = document.getElementById('slides');
    const totalSlides = slides.children.length;
    let currentIndex = 0;
    let autoplayTimer = null;
    const AUTOPLAY_DELAY = 5000; // 5 seconds per slide

    const dotsContainer = document.getElementById('dotsContainer');
    for (let i = 0; i < totalSlides; i++) {
      const dot = document.createElement('button');
      dot.className = 'dot' + (i === 0 ? ' active' : '');
      dot.setAttribute('data-index', i);
      dot.addEventListener('click', () => {
        goTo(i);
        resetAutoplay();
      });
      dotsContainer.appendChild(dot);
    }
    const dots = dotsContainer.querySelectorAll('.dot');

    function goTo(index) {
      if (index < 0) index = totalSlides - 1;
      if (index >= totalSlides) index = 0;
      currentIndex = index;
      slides.style.transform = `translateX(-${currentIndex * 100}%)`;
      dots.forEach((dot, i) => dot.classList.toggle('active', i === currentIndex));
    }

    function startAutoplay() {
      autoplayTimer = setInterval(() => goTo(currentIndex + 1), AUTOPLAY_DELAY);
    }

    function resetAutoplay() {
      clearInterval(autoplayTimer);
      startAutoplay();
    }

    document.getElementById('next').addEventListener('click', () => {
      goTo(currentIndex + 1);
      resetAutoplay();
    });
    document.getElementById('prev').addEventListener('click', () => {
      goTo(currentIndex - 1);
      resetAutoplay();
    });

    startAutoplay();
  });

})();