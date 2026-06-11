/* ============================================
   ADVAITH HOMES - Main JavaScript
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {

  // --- Mobile Menu ---
  const mobileBtn = document.querySelector('.mobile-menu-btn');
  const mobileMenu = document.querySelector('.mobile-menu-overlay');
  if (mobileBtn && mobileMenu) {
    mobileBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('open');
      mobileBtn.innerHTML = mobileMenu.classList.contains('open') ? '✕' : '☰';
    });
    document.addEventListener('click', (e) => {
      if (!mobileBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
        mobileMenu.classList.remove('open');
        mobileBtn.innerHTML = '☰';
      }
    });
  }

  // --- Active Nav Link ---
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-link, .mobile-nav-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href && href.includes(currentPage)) {
      link.classList.add('active');
    }
  });

  // --- Scroll Progress Bar ---
  const progressBar = document.querySelector('.scroll-progress');
  if (progressBar) {
    window.addEventListener('scroll', () => {
      const scrollTop = window.scrollY;
      const docHeight = document.body.scrollHeight - window.innerHeight;
      const pct = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
      progressBar.style.width = pct + '%';
    });
  }

  // --- TOC Active State on Scroll ---
  const tocItems = document.querySelectorAll('.toc-item');
  const articleSections = document.querySelectorAll('.article-section');
  if (tocItems.length && articleSections.length) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          tocItems.forEach(t => t.classList.remove('active'));
          const id = entry.target.id;
          const active = document.querySelector(`.toc-item[data-target="${id}"]`);
          if (active) active.classList.add('active');
        }
      });
    }, { rootMargin: '-20% 0px -70% 0px' });
    articleSections.forEach(s => observer.observe(s));
  }

  // --- Smooth Scroll for TOC ---
  document.querySelectorAll('.toc-item').forEach(item => {
    item.addEventListener('click', () => {
      const target = item.dataset.target;
      const el = document.getElementById(target);
      if (el) {
        const offset = 90;
        const top = el.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });
      }
    });
  });

  // --- Stamp Duty Calculator ---
  const calcForm = document.getElementById('stamp-duty-form');
  if (calcForm) {
    const locationBtns = calcForm.querySelectorAll('.location-btn');
    const ynBtns = calcForm.querySelectorAll('.yn-btn');
    let selectedLocation = 'england';
    let isFirstTimeBuyer = false;
    let isReplacing = false;

    locationBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        locationBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedLocation = btn.dataset.loc;
        calculateStampDuty();
      });
    });

    ynBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const group = btn.dataset.group;
        calcForm.querySelectorAll(`.yn-btn[data-group="${group}"]`).forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        if (group === 'replacing') isReplacing = btn.dataset.val === 'no';
        calculateStampDuty();
      });
    });

    const buyerSelect = document.getElementById('buyer-type');
    const priceInput = document.getElementById('property-price');

    if (buyerSelect) buyerSelect.addEventListener('change', calculateStampDuty);
    if (priceInput) priceInput.addEventListener('input', calculateStampDuty);

    function calculateStampDuty() {
      const price = parseFloat(priceInput ? priceInput.value.replace(/,/g, '') : 0) || 0;
      const buyerType = buyerSelect ? buyerSelect.value : 'standard';
      const ftb = buyerType === 'first-time';

      let tax = 0, breakdown = [], relief = 0;

      if (selectedLocation === 'england') {
        // SDLT England 2024/25
        const bands = ftb
          ? [
              { from: 0, to: 425000, rate: 0 },
              { from: 425000, to: 625000, rate: 0.05 },
              { from: 625000, to: Infinity, rate: null } // loses FTB relief above 625k
            ]
          : [
              { from: 0, to: 250000, rate: 0 },
              { from: 250000, to: 925000, rate: 0.05 },
              { from: 925000, to: 1500000, rate: 0.10 },
              { from: 1500000, to: Infinity, rate: 0.12 }
            ];

        if (ftb && price > 625000) {
          // Revert to standard
          const stdBands = [
            { from: 0, to: 250000, rate: 0 },
            { from: 250000, to: 925000, rate: 0.05 },
            { from: 925000, to: 1500000, rate: 0.10 },
            { from: 1500000, to: Infinity, rate: 0.12 }
          ];
          tax = calcSDLT(price, stdBands);
          breakdown = getBreakdown(price, stdBands);
        } else {
          const activeBands = ftb ? bands.slice(0, 2) : bands;
          tax = calcSDLT(price, activeBands);
          breakdown = getBreakdown(price, activeBands);
          if (ftb && price <= 625000) {
            const stdTax = calcSDLT(price, [
              { from: 0, to: 250000, rate: 0 },
              { from: 250000, to: 925000, rate: 0.05 },
              { from: 925000, to: 1500000, rate: 0.10 },
              { from: 1500000, to: Infinity, rate: 0.12 }
            ]);
            relief = stdTax - tax;
          }
        }
      } else {
        // Scotland LBTT
        const bands = [
          { from: 0, to: 145000, rate: 0 },
          { from: 145000, to: 250000, rate: 0.02 },
          { from: 250000, to: 325000, rate: 0.05 },
          { from: 325000, to: 750000, rate: 0.10 },
          { from: 750000, to: Infinity, rate: 0.12 }
        ];
        tax = calcSDLT(price, bands);
        breakdown = getBreakdown(price, bands);
      }

      updateResults(price, tax, relief, breakdown);
    }

    function calcSDLT(price, bands) {
      let total = 0;
      bands.forEach(band => {
        if (price > band.from && band.rate !== null) {
          const taxable = Math.min(price, band.to === Infinity ? price : band.to) - band.from;
          total += taxable * band.rate;
        }
      });
      return Math.max(0, total);
    }

    function getBreakdown(price, bands) {
      return bands.map(band => {
        if (price <= band.from || band.rate === null) return null;
        const taxable = Math.min(price, band.to === Infinity ? price : band.to) - band.from;
        const tax = taxable * band.rate;
        return { band: `${band.rate * 100}% on next £${formatNum(Math.min(price, band.to === Infinity ? price : band.to) - band.from)}`, tax };
      }).filter(Boolean);
    }

    function updateResults(price, tax, relief, breakdown) {
      const resultAmountEl = document.getElementById('result-amount');
      const totalPayableEl = document.getElementById('result-payable');
      const breakdownEl = document.getElementById('result-breakdown-rows');

      if (resultAmountEl) resultAmountEl.textContent = '£' + formatNum(Math.round(tax));
      if (totalPayableEl) totalPayableEl.textContent = '£' + formatNum(Math.round(tax - relief));

      if (breakdownEl) {
        let html = '';
        // Always show first band
        if (price <= 250000 || price <= 425000) {
          html += `<div class="result-row"><span class="result-row-label">0% on first £${formatNum(Math.min(price, 250000))}</span><span class="result-row-value">£0</span></div>`;
        } else {
          html += `<div class="result-row"><span class="result-row-label">0% on first £250,000</span><span class="result-row-value">£0</span></div>`;
          if (price > 250000) {
            const band2 = Math.min(price, 925000) - 250000;
            html += `<div class="result-row"><span class="result-row-label">5% on next £${formatNum(band2)}</span><span class="result-row-value">£${formatNum(Math.round(band2 * 0.05))}</span></div>`;
          }
        }
        html += `<div class="result-row total"><span class="result-row-label">Total SDLT</span><span class="result-row-value">£${formatNum(Math.round(tax))}</span></div>`;
        if (relief > 0) {
          html += `<div class="result-row relief"><span class="result-row-label">First-time buyer relief</span><span class="result-row-value">−£${formatNum(Math.round(relief))}</span></div>`;
        }
        html += `<div class="result-row payable"><span class="result-row-label">Total Payable</span><span class="result-row-value">£${formatNum(Math.round(tax - relief))}</span></div>`;
        breakdownEl.innerHTML = html;
      }
    }

    function formatNum(n) {
      return n.toLocaleString('en-GB');
    }

    // Init
    calculateStampDuty();
  }

  // --- Journey Step Interaction ---
  const journeySteps = document.querySelectorAll('.journey-step');
  journeySteps.forEach((step, i) => {
    step.addEventListener('click', () => {
      journeySteps.forEach(s => s.classList.remove('active'));
      step.classList.add('active');
    });
  });
  if (journeySteps.length) journeySteps[0].classList.add('active');

  // --- Guide Cards Hover Reveal ---
  // Just CSS handles hover, but add keyboard accessibility
  document.querySelectorAll('.guide-card, .guide-listing-card, .journey-card').forEach(card => {
    card.setAttribute('tabindex', '0');
    card.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        const link = card.querySelector('a');
        if (link) link.click();
      }
    });
  });

  // --- Feedback Buttons ---
  document.querySelectorAll('.feedback-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.feedback-btn').forEach(b => b.style.background = '');
      btn.style.background = 'var(--color-tag-bg)';
      btn.style.borderColor = 'var(--color-primary)';
    });
  });

  // --- Newsletter form ---
  document.querySelectorAll('.newsletter-form, .stay-informed-form').forEach(form => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const input = form.querySelector('input');
      if (input && input.value) {
        const btn = form.querySelector('button');
        if (btn) { btn.textContent = 'Subscribed ✓'; btn.disabled = true; }
        input.value = '';
      }
    });
  });

  // --- Calc Tab Buttons ---
  document.querySelectorAll('.calc-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const group = btn.closest('.calc-filter-tabs');
      if (group) {
        group.querySelectorAll('.calc-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
      }
    });
  });

  // --- Filter Sidebar Categories ---
  document.querySelectorAll('.sidebar-cat-item').forEach(item => {
    item.addEventListener('click', () => {
      const parent = item.closest('.guides-sidebar-box');
      if (parent) {
        parent.querySelectorAll('.sidebar-cat-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');
      }
    });
  });

  // --- Animate sections on scroll ---
  const animateEls = document.querySelectorAll('.guide-card, .calc-big-card, .journey-card, .guide-listing-card');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, { threshold: 0.08 });
    animateEls.forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(12px)';
      el.style.transition = 'opacity 0.4s ease, transform 0.4s ease, box-shadow 0.2s ease, border-color 0.2s ease';
      io.observe(el);
    });
  }

  // --- Location buttons in calc (toggle active) ---
  document.querySelectorAll('.yn-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const group = this.dataset.group;
      document.querySelectorAll(`.yn-btn[data-group="${group}"]`).forEach(b => b.classList.remove('active'));
      this.classList.add('active');
    });
  });

});
