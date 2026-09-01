/**
 * VintageSoulTheme - Interactive Infinite Card Stream Controller
 * 
 * Enables smooth mouse-wheel scrolling and click-and-drag panning
 * over all infinite card streams (Gallery, Social Stream, Memories, Reviews, Products)
 * while preserving seamless infinite loop wraparound.
 */
(function() {
  'use strict';

  function initCardStreamInteractions() {
    var wrappers = document.querySelectorAll('.social-stream__track-wrap');
    if (!wrappers.length) return;

    wrappers.forEach(function(wrap) {
      if (wrap.getAttribute('data-stream-interactive') === 'true') return;
      wrap.setAttribute('data-stream-interactive', 'true');

      var track = wrap.querySelector('.social-stream__track');
      if (!track) return;

      var isRTL = track.classList.contains('social-stream__track--rtl');
      var isDragging = false;
      var isWheelActive = false;
      var startX = 0;
      var currentTranslateX = 0;
      var targetTranslateX = 0;
      var initialAnimationX = 0;
      var isInteracting = false;
      var resumeTimer = null;
      var animFrame = null;
      var hasMovedSignificantly = false;

      // Ensure grab cursor
      wrap.style.cursor = 'grab';
      wrap.style.userSelect = 'none';
      wrap.style.touchAction = 'pan-y';

      function getTrackHalfWidth() {
        return track.scrollWidth / 2 || 1000;
      }

      function getCurrentTranslateX() {
        var style = window.getComputedStyle(track);
        var transform = style.transform || style.webkitTransform;
        if (transform && transform !== 'none') {
          try {
            var matrix = new DOMMatrixReadOnly(transform);
            return matrix.m41;
          } catch (e) {
            var values = transform.split('(')[1].split(')')[0].split(',');
            return parseFloat(values[4]) || 0;
          }
        }
        return 0;
      }

      function clampWrapOffset(val) {
        var half = getTrackHalfWidth();
        if (half <= 0) return val;
        while (val > 0) val -= half;
        while (val < -half) val += half;
        return val;
      }

      function startManualControl() {
        if (!isInteracting) {
          isInteracting = true;
          initialAnimationX = getCurrentTranslateX();
          currentTranslateX = initialAnimationX;
          targetTranslateX = initialAnimationX;
          track.style.animationPlayState = 'paused';
          track.style.animation = 'none';
          track.style.transform = 'translateX(' + currentTranslateX + 'px)';
        }
        if (resumeTimer) {
          clearTimeout(resumeTimer);
          resumeTimer = null;
        }
      }

      function updateSmoothSpring() {
        if (!isInteracting) return;

        // Smooth easing towards targetTranslateX
        var diff = targetTranslateX - currentTranslateX;
        if (Math.abs(diff) > 0.1) {
          currentTranslateX += diff * 0.18;
          currentTranslateX = clampWrapOffset(currentTranslateX);
          targetTranslateX = clampWrapOffset(targetTranslateX);
          track.style.transform = 'translateX(' + currentTranslateX + 'px)';
          animFrame = requestAnimationFrame(updateSmoothSpring);
        } else {
          currentTranslateX = targetTranslateX;
          track.style.transform = 'translateX(' + currentTranslateX + 'px)';
          if (!isDragging && !isWheelActive) {
            scheduleResume();
          }
        }
      }

      function scheduleResume() {
        if (resumeTimer) clearTimeout(resumeTimer);
        resumeTimer = setTimeout(function() {
          if (isDragging || isWheelActive) return;
          isInteracting = false;
          // Re-enable smooth CSS marquee from current progress
          var half = getTrackHalfWidth();
          var pct = Math.abs(currentTranslateX % half) / half;
          var duration = isRTL ? 38 : 42;
          var delay = -1 * pct * duration;

          var animName = isRTL ? 'social-marquee-rtl' : 'social-marquee-ltr';
          track.style.transform = '';
          track.style.animation = animName + ' ' + duration + 's linear infinite';
          track.style.animationDelay = delay + 's';
          track.style.animationPlayState = wrap.matches(':hover') ? 'paused' : 'running';
        }, 1800);
      }

      // 1. Mouse Wheel / Trackpad Scroll Support (Allow vertical page scroll naturally)
      wrap.addEventListener('wheel', function(e) {
        // Only intercept when horizontal intent is strictly dominant (e.g. horizontal trackpad pan or Shift + Wheel)
        if (Math.abs(e.deltaX) > Math.abs(e.deltaY) && Math.abs(e.deltaX) > 2) {
          e.preventDefault();

          startManualControl();
          isWheelActive = true;

          var factor = e.deltaMode === 1 ? 30 : 1.25;
          targetTranslateX -= e.deltaX * factor;

          cancelAnimationFrame(animFrame);
          animFrame = requestAnimationFrame(updateSmoothSpring);

          if (wrap._wheelEndTimer) clearTimeout(wrap._wheelEndTimer);
          wrap._wheelEndTimer = setTimeout(function() {
            isWheelActive = false;
            scheduleResume();
          }, 150);
        }
        // Vertical wheel scrolling (deltaY) is intentionally passed through to scroll the page!
      }, { passive: false });

      // 2. Pointer Drag / Swipe Support
      var pointerDownTarget = null;
      var pointerDownId = null;

      wrap.addEventListener('pointerdown', function(e) {
        if (e.button !== 0 && e.pointerType === 'mouse') return; // Left click only
        pointerDownTarget = e.target.closest('[data-story-modal]');
        pointerDownId = e.pointerId;
        isDragging = false;
        hasMovedSignificantly = false;
        startX = e.clientX;
      });

      wrap.addEventListener('pointermove', function(e) {
        var diffX = e.clientX - startX;
        if (!isDragging && Math.abs(diffX) > 6) {
          startManualControl();
          isDragging = true;
          hasMovedSignificantly = true;
          wrap.style.cursor = 'grabbing';
          try {
            wrap.setPointerCapture(pointerDownId);
          } catch(err) {}
        }

        if (isDragging) {
          targetTranslateX += diffX * 1.1;
          currentTranslateX = targetTranslateX;
          targetTranslateX = clampWrapOffset(targetTranslateX);
          currentTranslateX = clampWrapOffset(currentTranslateX);
          track.style.transform = 'translateX(' + currentTranslateX + 'px)';
          startX = e.clientX;
        }
      });

      function endDrag(e) {
        if (isDragging) {
          isDragging = false;
          wrap.style.cursor = 'grab';
          try {
            wrap.releasePointerCapture(pointerDownId);
          } catch(err) {}
          scheduleResume();
        }
      }

      wrap.addEventListener('pointerup', endDrag);
      wrap.addEventListener('pointercancel', endDrag);

      // Handle card clicks & open modal cleanly
      wrap.addEventListener('click', function(e) {
        if (hasMovedSignificantly) {
          e.preventDefault();
          e.stopPropagation();
          hasMovedSignificantly = false;
          return;
        }

        var card = e.target.closest('[data-story-modal]') || pointerDownTarget;
        if (card) {
          if (window.VintageSoul && window.VintageSoul.dialog && typeof window.VintageSoul.dialog.openStoryModal === 'function') {
            e.preventDefault();
            e.stopPropagation();
            window.VintageSoul.dialog.openStoryModal(card);
          }
        }
      });

      // Resume animation when hover leaves
      wrap.addEventListener('mouseleave', function() {
        if (!isDragging && !isWheelActive) {
          scheduleResume();
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCardStreamInteractions);
  } else {
    initCardStreamInteractions();
  }

  // Also re-init on dynamic content updates
  window.addEventListener('load', initCardStreamInteractions);
})();
