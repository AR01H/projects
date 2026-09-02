/**
 * VintageSoul - High Performance Hardware-Accelerated Sticky Sidebar
 */
(function () {
  'use strict';

  function initStickySidebar() {
    var sidebar = document.querySelector('.single-article__sidebar');
    var stickyWrap = document.querySelector('.single-article__sidebar-sticky');
    var articleCard = document.querySelector('.single-article-card');
    var layout = document.querySelector('.single-article-layout');

    if (!sidebar || !stickyWrap || !articleCard || !layout) return;

    var ticking = false;

    function onScroll() {
      if (!ticking) {
        window.requestAnimationFrame(function () {
          updateSticky();
          ticking = false;
        });
        ticking = true;
      }
    }

    function updateSticky() {
      // Only apply on desktop / laptop viewports (> 880px)
      if (window.innerWidth <= 880) {
        stickyWrap.style.transform = '';
        return;
      }

      var headerOffset = 100;
      var cardRect = articleCard.getBoundingClientRect();
      var stickyHeight = stickyWrap.offsetHeight;

      // Available vertical travel distance: stops exactly at the bottom edge of .single-article-card
      var maxTranslate = Math.max(0, articleCard.offsetHeight - stickyHeight);

      if (cardRect.top < headerOffset) {
        var distance = headerOffset - cardRect.top;
        var translate = Math.min(distance, maxTranslate);
        stickyWrap.style.transform = 'translate3d(0, ' + Math.round(translate) + 'px, 0)';
      } else {
        stickyWrap.style.transform = 'translate3d(0, 0, 0)';
      }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    // Run on initial load and after images finish loading
    updateSticky();
    window.addEventListener('load', updateSticky, { once: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStickySidebar);
  } else {
    initStickySidebar();
  }

  if (window.VintageSoul && window.VintageSoul.app) {
    window.VintageSoul.app.register('sticky-sidebar', initStickySidebar);
  }
})();
