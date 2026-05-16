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

  // ── Intersection Observer — data-aos ──────────────────────────────────────
  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('[data-aos]').forEach(function (el) {
      observer.observe(el);
    });
  } else {
    // Fallback: just show everything
    document.querySelectorAll('[data-aos]').forEach(function (el) {
      el.classList.add('is-visible');
    });
  }

  // ── Table of contents — active link on scroll ─────────────────────────────
  var $tocLinks = $('.toc__item[href^="#"]');
  if ($tocLinks.length) {
    var headings = [];
    $tocLinks.each(function () {
      var id = $(this).attr('href').replace('#', '');
      var $h = $('#' + id);
      if ($h.length) headings.push({ $link: $(this), $h: $h });
    });

    $(window).on('scroll.toc', function () {
      var scrollY = window.scrollY + 100;
      var active  = null;
      headings.forEach(function (h) {
        if (h.$h.offset().top <= scrollY) active = h;
      });
      $tocLinks.removeClass('is-active');
      if (active) active.$link.addClass('is-active');
    });
  }

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
