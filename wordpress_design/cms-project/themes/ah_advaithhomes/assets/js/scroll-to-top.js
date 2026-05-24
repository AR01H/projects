var SCROLL_BTN = {
  showAfterPx: 300,
  scrollTarget: null /* null = window; or CSS selector e.g. "#mhRoot" */,
  smoothScroll: true,
};

(function () {
  var btn = document.getElementById("scrollTopBtn");
  var ring = document.getElementById("scrollRingFg");
  if (ring && btn) {
    var tgt = SCROLL_BTN.scrollTarget
      ? document.querySelector(SCROLL_BTN.scrollTarget)
      : window;

    /* Set up ring circumference */
    var r = 21;
    var circ = 2 * Math.PI * r;
    ring.style.strokeDasharray = circ;
    ring.style.strokeDashoffset = circ; /* fully hidden at start */

    function getScroll() {
      return tgt === window ? window.scrollY : tgt.scrollTop;
    }
    function getMaxScroll() {
      return tgt === window
        ? document.body.scrollHeight - window.innerHeight
        : tgt.scrollHeight - tgt.clientHeight;
    }

    function onScroll() {
      var scrolled = getScroll();
      var maxScroll = getMaxScroll();
      var pct = maxScroll > 0 ? scrolled / maxScroll : 0;

      /* Update ring */
      ring.style.strokeDashoffset = circ - pct * circ;

      /* Show / hide button */
      if (scrolled > SCROLL_BTN.showAfterPx) {
        btn.classList.add("visible");
      } else {
        btn.classList.remove("visible");
      }
    }

    btn.addEventListener("click", function () {
      var target = tgt === window ? window : tgt;
      target.scrollTo({
        top: 0,
        behavior: SCROLL_BTN.smoothScroll ? "smooth" : "auto",
      });
    });

    tgt.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
  }
})();
