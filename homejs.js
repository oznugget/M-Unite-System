(function() {
  'use strict';

  document.addEventListener('DOMContentLoaded', function() {
    const slidesContainer = document.getElementById('slides');
    const originalSlides = Array.from(slidesContainer.children);
    const totalSlides = originalSlides.length;
    const AUTOPLAY_DELAY = 5000; // 5 seconds per slide

 
    const firstClone = originalSlides[0].cloneNode(true);
    const lastClone = originalSlides[totalSlides - 1].cloneNode(true);
    firstClone.classList.add('clone');
    lastClone.classList.add('clone');

    slidesContainer.appendChild(firstClone);           // append clone of first slide at the end
    slidesContainer.insertBefore(lastClone, originalSlides[0]); // prepend clone of last slide at the start

    // currentIndex is offset by 1 because of the prepended clone
    let currentIndex = 1;
    let autoplayTimer = null;
    let isTransitioning = false;

    // Position instantly at the real first slide (no animation on load)
    slidesContainer.style.transition = 'none';
    slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;

    // ---- Dots (still reflect only the REAL slides, not clones) ----
    const dotsContainer = document.getElementById('dotsContainer');
    for (let i = 0; i < totalSlides; i++) {
      const dot = document.createElement('button');
      dot.className = 'dot' + (i === 0 ? ' active' : '');
      dot.setAttribute('data-index', i);
      dot.addEventListener('click', () => {
        goTo(i + 1); // +1 to account for the prepended clone offset
        resetAutoplay();
      });
      dotsContainer.appendChild(dot);
    }
    const dots = dotsContainer.querySelectorAll('.dot');

    function updateDots() {
      // Map the real (non-clone) slide index back to 0..totalSlides-1
      const realIndex = (currentIndex - 1 + totalSlides) % totalSlides;
      dots.forEach((dot, i) => dot.classList.toggle('active', i === realIndex));
    }

    function goTo(index) {
      if (isTransitioning) return;
      isTransitioning = true;

      currentIndex = index;
      slidesContainer.style.transition = 'transform 0.5s ease-in-out';
      slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
      updateDots();
    }

    // After each transition, check if we've landed on a clone and silently
    // jump to the matching real slide with no animation.
    slidesContainer.addEventListener('transitionend', () => {
      isTransitioning = false;

      if (currentIndex === 0) {
        // Landed on the prepended "last" clone -> jump to real last slide
        slidesContainer.style.transition = 'none';
        currentIndex = totalSlides;
        slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
      } else if (currentIndex === totalSlides + 1) {
        // Landed on the appended "first" clone -> jump to real first slide
        slidesContainer.style.transition = 'none';
        currentIndex = 1;
        slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
      }
    });

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