/**
 * VintageSoulTheme - Cinematic Sugarcane Plantation Forest Gates Page Loader Script
 *
 * Coordinates the grand wooden plantation double doors opening, the sunburst flare,
 * and seamless revelation of the website on initial page load.
 */
(function() {
  'use strict';

  function initPlantationLoader() {
    var loader = document.getElementById('cane-plantation-loader');
    if (!loader) return;

    var minDisplayTime = 950; // Minimum duration to show doors & crest
    var startTime = Date.now();
    var hasOpened = false;

    function openGates() {
      if (hasOpened) return;
      hasOpened = true;

      // 1. Trigger Door Parting & Sunbeam Flare
      loader.classList.add('is-opening');

      // 2. Trigger Smooth Fade-Out after doors swing open
      setTimeout(function() {
        loader.classList.add('is-loaded');

        // 3. Clean up display after animation finishes
        setTimeout(function() {
          loader.style.display = 'none';
        }, 950);
      }, 1250);
    }

    function checkReadyAndOpen() {
      var elapsedTime = Date.now() - startTime;
      var remainingTime = Math.max(0, minDisplayTime - elapsedTime);

      setTimeout(function() {
        if (document.readyState === 'complete') {
          openGates();
        } else {
          window.addEventListener('load', openGates);
          // Safety fallback timeout
          setTimeout(openGates, 2200);
        }
      }, remainingTime);
    }

    if (document.readyState === 'complete') {
      checkReadyAndOpen();
    } else {
      window.addEventListener('DOMContentLoaded', checkReadyAndOpen);
      window.addEventListener('load', openGates);
      // Absolute safety fallback (in case slow network)
      setTimeout(openGates, 3200);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPlantationLoader);
  } else {
    initPlantationLoader();
  }
})();
