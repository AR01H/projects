(function ($) {
  'use strict';

  // ── Nav scroll state ──────────────────────────────────────────────────────
  var $nav = $('#mainNav');
  function updateNavScroll() {
    if (window.scrollY > 20) {
      $nav.addClass('scrolled');
    } else {
      $nav.removeClass('scrolled');
    }
  }
  $(window).on('scroll.nav', updateNavScroll);
  updateNavScroll();

  // ── Mobile hamburger ──────────────────────────────────────────────────────
  var $hamburger  = $('#ahHamburger');
  var $mobileNav  = $('#ahMobileNav');

  $hamburger.on('click', function () {
    var isOpen = $mobileNav.hasClass('is-open');
    $mobileNav.toggleClass('is-open', !isOpen);
    $hamburger.toggleClass('is-open', !isOpen);
    $hamburger.attr('aria-expanded', String(!isOpen));
    $('body').toggleClass('nav-open', !isOpen);
  });

  // Close mobile nav on outside click
  $(document).on('click', function (e) {
    if (!$(e.target).closest('#mainNav, #ahMobileNav').length) {
      $mobileNav.removeClass('is-open');
      $hamburger.removeClass('is-open').attr('aria-expanded', 'false');
      $('body').removeClass('nav-open');
    }
  });

  // ── FAQ accordion ─────────────────────────────────────────────────────────
  $(document).on('click', '.faq__q', function () {
    var $faq = $(this).closest('.faq');
    var isOpen = $faq.hasClass('is-open');

    // Close sibling FAQs in the same group
    $faq.siblings('.faq').removeClass('is-open');

    $faq.toggleClass('is-open', !isOpen);
  });

  // ── Intersection Observer - data-aos ──────────────────────────────────────
  var aosEls = document.querySelectorAll('[data-aos]');

  // Apply data-delay inline so all ms values work
  aosEls.forEach(function (el) {
    var delay = parseInt(el.getAttribute('data-delay') || 0, 10);
    if (delay > 0) el.style.transitionDelay = delay + 'ms';
  });

  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0, rootMargin: '0px 0px -60px 0px' });

    aosEls.forEach(function (el) { observer.observe(el); });
  } else {
    aosEls.forEach(function (el) { el.classList.add('is-visible'); });
  }

  // ── 3D Carousel ───────────────────────────────────────────────────────────
  function AHCarousel(wrapEl) {
    var track    = wrapEl.querySelector('.carousel-3d');
    if (!track) return;
    var slides   = Array.from(track.querySelectorAll('.carousel-3d__slide'));
    var details  = Array.from(wrapEl.querySelectorAll('[data-carousel-detail]'));
    var total    = slides.length;
    var current  = 0;

    function render() {
      slides.forEach(function (slide, i) {
        var pos = ((i - current) % total + total) % total;
        if (pos > Math.floor(total / 2)) pos -= total;
        slide.setAttribute('data-pos', String(pos));
        slide.classList.toggle('is-active', pos === 0);
      });
      details.forEach(function (el, i) {
        el.classList.toggle('is-active', i === current);
      });
      // update counter if present
      var counter = wrapEl.querySelector('[data-carousel-counter]');
      if (counter) counter.textContent = (current + 1) + ' / ' + total;
    }

    wrapEl.querySelectorAll('[data-carousel-prev]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        current = (current - 1 + total) % total;
        render();
      });
    });
    wrapEl.querySelectorAll('[data-carousel-next]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        current = (current + 1) % total;
        render();
      });
    });

    // Swipe support
    var startX = 0;
    track.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    track.addEventListener('touchend', function (e) {
      var diff = startX - e.changedTouches[0].clientX;
      if (Math.abs(diff) > 40) {
        current = diff > 0 ? (current + 1) % total : (current - 1 + total) % total;
        render();
      }
    }, { passive: true });

    // Auto-play (stories carousel only, 5s)
    if (wrapEl.dataset.autoplay) {
      setInterval(function () {
        current = (current + 1) % total;
        render();
      }, parseInt(wrapEl.dataset.autoplay, 10) || 5000);
    }

    render();
  }

  document.querySelectorAll('[data-carousel-wrap]').forEach(function (el) {
    AHCarousel(el);
  });

  // ── Dynamic Table of Contents ─────────────────────────────────────────────
  (function () {
    var article = document.getElementById('article-body');
    var tocCard  = document.getElementById('sp-toc');
    if (!article || !tocCard) return;

    var headings = Array.from(article.querySelectorAll('h2, h3'));
    if (headings.length < 2) return;

    var nav = tocCard.querySelector('.sp-toc__nav');
    if (!nav) return;

    // Ensure every heading has an id
    headings.forEach(function (h, i) {
      if (!h.id) h.id = 'sp-h-' + i;
    });

    // Build anchor links
    headings.forEach(function (h) {
      var a = document.createElement('a');
      a.href    = '#' + h.id;
      a.className = 'sp-toc__link sp-toc__link--' + h.tagName.toLowerCase();
      a.textContent = h.textContent;
      nav.appendChild(a);
    });

    tocCard.removeAttribute('hidden');

    // Scroll-spy: highlight the heading currently in view
    if ('IntersectionObserver' in window) {
      var activeLink = null;
      var spy = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            if (activeLink) activeLink.classList.remove('is-active');
            activeLink = nav.querySelector('[href="#' + entry.target.id + '"]');
            if (activeLink) activeLink.classList.add('is-active');
          }
        });
      }, { rootMargin: '-10% 0px -80% 0px', threshold: 0 });
      headings.forEach(function (h) { spy.observe(h); });
    }
  }());

  // ── Filter tabs ───────────────────────────────────────────────────────────
  $(document).on('click', '.filter-tab', function () {
    var $btn    = $(this);
    var $group  = $btn.closest('[data-filter-group]');
    var filter  = $btn.data('filter');

    $group.find('.filter-tab').removeClass('is-active');
    $btn.addClass('is-active');

    var $items = $group.find('[data-filter-item]');
    if (filter === 'all') {
      $items.show();
    } else {
      $items.each(function () {
        var cats = String($(this).data('filter-item') || '').split(',');
        $(this).toggle(cats.indexOf(filter) !== -1);
      });
    }
  });

  // ── Smooth scroll for anchor links ────────────────────────────────────────
  $(document).on('click', 'a[href^="#"]', function (e) {
    var target = $(this).attr('href');
    if (target === '#' || !$(target).length) return;
    e.preventDefault();
    $('html, body').animate({
      scrollTop: $(target).offset().top - 90
    }, 500);
  });

  // ── Stamp duty calculator ─────────────────────────────────────────────────
  function calcStampDuty(price, isFirstTime, isAdditional) {
    if (isAdditional) {
      // Additional property: 3% surcharge on all bands
      var surcharge = price * 0.03;
      var duty = calcMainSDLT(price, false);
      return duty + surcharge;
    }
    if (isFirstTime && price <= 625000) {
      // First-time buyer: 0% up to 425k, 5% 425k–625k
      if (price <= 425000) return 0;
      return (price - 425000) * 0.05;
    }
    return calcMainSDLT(price, false);
  }

  function calcMainSDLT(price) {
    var bands = [
      { from: 0,       to: 250000,  rate: 0    },
      { from: 250000,  to: 925000,  rate: 0.05 },
      { from: 925000,  to: 1500000, rate: 0.10 },
      { from: 1500000, to: Infinity, rate: 0.12 }
    ];
    var total = 0;
    bands.forEach(function (b) {
      if (price > b.from) {
        var taxable = Math.min(price, b.to) - b.from;
        total += taxable * b.rate;
      }
    });
    return total;
  }

  function formatGBP(n) {
    return '£' + Math.round(n).toLocaleString('en-GB');
  }

  $('#ah-stamp-calc').on('input change', function () {
    var price      = parseFloat($('#sdlt-price').val().replace(/[^0-9.]/g, '')) || 0;
    var isFirst    = $('#sdlt-first-time').is(':checked');
    var isAddit    = $('#sdlt-additional').is(':checked');
    var duty       = calcStampDuty(price, isFirst, isAddit);
    var total      = price + duty;
    $('#sdlt-result-duty').text(formatGBP(duty));
    $('#sdlt-result-total').text(formatGBP(total));
    $('#sdlt-result-wrapper').toggleClass('is-visible', price > 0);
  });

  // ── Mortgage calculator ───────────────────────────────────────────────────
  $('#ah-mortgage-calc').on('input change', function () {
    var price    = parseFloat($('#mc-price').val().replace(/[^0-9.]/g, '')) || 0;
    var deposit  = parseFloat($('#mc-deposit').val().replace(/[^0-9.]/g, '')) || 0;
    var rate     = parseFloat($('#mc-rate').val()) || 0;
    var term     = parseInt($('#mc-term').val()) || 25;
    var loan     = price - deposit;
    var ltv      = price > 0 ? Math.round((loan / price) * 100) : 0;

    var monthly = 0;
    if (loan > 0 && rate > 0) {
      var r = rate / 100 / 12;
      var n = term * 12;
      monthly = loan * (r * Math.pow(1+r, n)) / (Math.pow(1+r, n) - 1);
    } else if (loan > 0) {
      monthly = loan / (term * 12);
    }

    $('#mc-result-monthly').text(formatGBP(monthly));
    $('#mc-result-loan').text(formatGBP(loan));
    $('#mc-result-ltv').text(ltv + '%');
    $('#mc-result-wrapper').toggleClass('is-visible', price > 0);
  });

  // ── Adaptive Nav — desktop menu if items fit, hamburger (3-lines) if not ───
  (function () {
    var nav    = document.getElementById('mainNav');
    var menu   = nav && nav.querySelector('.nav__menu');
    var inner  = nav && nav.querySelector('.nav__inner');
    if (!menu || !inner) return;

    function adjust() {
      var body = document.body;

      // Reset to desktop state so we can measure accurately
      body.classList.remove('nav--collapsed');
      void inner.offsetWidth; // force reflow

      if (inner.scrollWidth <= inner.clientWidth + 2) {
        // All items fit — stay in desktop mode
        return;
      }

      // Doesn't fit — switch straight to hamburger
      body.classList.add('nav--collapsed');
      $mobileNav.removeClass('is-open');
      $hamburger.removeClass('is-open').attr('aria-expanded', 'false');
      $('body').removeClass('nav-open');
    }

    var timer;
    window.addEventListener('resize', function () {
      clearTimeout(timer);
      timer = setTimeout(adjust, 60);
    }, { passive: true });
    adjust();
  }());

  // ── Post share popover ────────────────────────────────────────────────────
  $(document).on('click', '.post-share__btn', function (e) {
    e.stopPropagation();
    var $popover = $(this).siblings('.post-share__popover');
    var isOpen   = $popover.hasClass('is-open');
    // Close any other open popovers
    $('.post-share__popover.is-open').not($popover).removeClass('is-open');
    $popover.toggleClass('is-open', !isOpen);
    $(this).attr('aria-expanded', String(!isOpen));
  });

  // Close share popover on outside click
  $(document).on('click', function (e) {
    if (!$(e.target).closest('.post-share').length) {
      $('.post-share__popover').removeClass('is-open');
      $('.post-share__btn').attr('aria-expanded', 'false');
    }
  });

  // Native share button (Web Share API)
  $(document).on('click', '.post-share__icon--native', function () {
    var url   = $(this).data('url');
    var title = $(this).data('title') || document.title;
    if (navigator.share) {
      navigator.share({ title: title, url: url }).catch(function () {});
    } else {
      // Fallback: open a new tab to the URL
      window.open(url, '_blank', 'noopener');
    }
  });

  // Copy link button inside share popover
  $(document).on('click', '.post-share__icon--copy', function () {
    var url = $(this).data('url');
    if (!url) return;
    if (navigator.clipboard) {
      navigator.clipboard.writeText(url);
    } else {
      var ta = document.createElement('textarea');
      ta.value = url;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
    }
    var $btn = $(this);
    $btn.addClass('copied');
    setTimeout(function () { $btn.removeClass('copied'); }, 1800);
  });

  // ── Copy to clipboard ─────────────────────────────────────────────────────
  $(document).on('click', '[data-copy]', function () {
    var text = $(this).data('copy') || $(this).text();
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text);
    }
    var $btn = $(this);
    var orig = $btn.text();
    $btn.text('Copied!');
    setTimeout(function () { $btn.text(orig); }, 1500);
  });

})(jQuery);
