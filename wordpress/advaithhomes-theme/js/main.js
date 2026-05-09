// ============================================
// ADVAITH HOMES — Shared Components
// ============================================

const NAV_HTML = `
<nav class="nav" id="mainNav">
  <div class="container">
    <div class="nav__inner">
      <a href="index.html" class="nav__logo">
           <img src="logo.png" style='height:40px;'/>
        <span>Advaith <em style="font-style:italic;font-family:var(--font-accent)">Homes</em></span>
      </a>

      <ul class="nav__menu">
        <li><a href="index.html" class="nav__link" data-page="home">Home</a></li>
        <li><a href="services.html" class="nav__link" data-page="services">Services</a></li>
        <li><a href="about.html" class="nav__link" data-page="about">About Us</a></li>

        <li class="nav__dropdown">
          <button class="nav__link nav__dropdown-toggle">
            Buying Guides
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="nav__dropdown-menu">
            <a href="pages/property-research.html" class="nav__dropdown-item">
              <div class="nav__dropdown-item-icon">🔍</div>
              <div>
                <div style="font-weight:600;color:var(--slate-800);font-size:.85rem;width: max-content;">Property Research Report</div>
                <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px">Deep analysis before you buy</div>
              </div>
            </a>
            <a href="pages/legal-search.html" class="nav__dropdown-item">
              <div class="nav__dropdown-item-icon">⚖️</div>
              <div>
                <div style="font-weight:600;color:var(--slate-800);font-size:.85rem;width: max-content;">Legal Search Packs</div>
                <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px">What's hidden in the paperwork</div>
              </div>
            </a>
            <a href="pages/buyers-guide.html" class="nav__dropdown-item">
              <div class="nav__dropdown-item-icon">📋</div>
              <div>
                <div style="font-weight:600;color:var(--slate-800);font-size:.85rem">Buyer's Guide</div>
                <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px">Complete buying process</div>
              </div>
            </a>
            <a href="pages/deposit-guide.html" class="nav__dropdown-item">
              <div class="nav__dropdown-item-icon">💰</div>
              <div>
                <div style="font-weight:600;color:var(--slate-800);font-size:.85rem">Deposit Guide</div>
                <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px">How much you really need</div>
              </div>
            </a>
            <a href="pages/mortgage-guide.html" class="nav__dropdown-item">
              <div class="nav__dropdown-item-icon">🏦</div>
              <div>
                <div style="font-weight:600;color:var(--slate-800);font-size:.85rem">Mortgage Guide</div>
                <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px">Navigate rates & lenders</div>
              </div>
            </a>
            <a href="pages/moving-guide.html" class="nav__dropdown-item">
              <div class="nav__dropdown-item-icon">🚛</div>
              <div>
                <div style="font-weight:600;color:var(--slate-800);font-size:.85rem">Moving Guide</div>
                <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px">Stress-free moving day</div>
              </div>
            </a>
          </div>
        </li>

        <li><a href="contact.html" class="nav__link" data-page="contact">Contact</a></li>
        <li><a href="previous-clients.html" class="nav__link" data-page="clients">Client Stories</a></li>
      </ul>

      <div class="nav__actions">
        <a href="tel:+447747223762" class="btn btn-secondary btn-sm">📞 Call Us</a>
        <a href="free-consultation.html" class="btn btn-primary btn-sm">Free Consultation</a>
        <button class="nav__hamburger" id="hamburger" aria-label="Menu">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </div>
</nav>

<div class="nav__mobile" id="mobileNav">
  <a href="index.html" class="nav__mobile-link">🏠 Home</a>
  <a href="services.html" class="nav__mobile-link">✨ Services</a>
  <a href="about.html" class="nav__mobile-link">👥 About Us</a>
  <div style="padding:13px 16px;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted)">Buying Guides</div>
  <a href="pages/property-research.html" class="nav__mobile-link" style="padding-left:28px">🔍 Property Research Report</a>
  <a href="pages/legal-search.html" class="nav__mobile-link" style="padding-left:28px">⚖️ Legal Search Packs</a>
  <a href="pages/buyers-guide.html" class="nav__mobile-link" style="padding-left:28px">📋 Buyer's Guide</a>
  <a href="pages/deposit-guide.html" class="nav__mobile-link" style="padding-left:28px">💰 Deposit Guide</a>
  <a href="pages/mortgage-guide.html" class="nav__mobile-link" style="padding-left:28px">🏦 Mortgage Guide</a>
  <a href="pages/moving-guide.html" class="nav__mobile-link" style="padding-left:28px">🚛 Moving Guide</a>
  <a href="free-consultation.html" class="nav__mobile-link" style="padding-left:28px">☎️ Free Consultation Guide</a>
  <a href="contact.html" class="nav__mobile-link">📬 Contact Us</a>
  <a href="previous-clients.html" class="nav__mobile-link">⭐ Client Stories</a>
  <div style="padding:16px;">
    <a href="free-consultation.html" class="btn btn-primary" style="width:100%;justify-content:center">Book Free Consultation</a>
  </div>
</div>
`;

const FOOTER_HTML = `
<footer class="footer">
  <div class="container">
    <div class="footer__grid">
      <div>
        <a href="index.html" class="nav__logo" style="margin-bottom:0">
          <div class="nav__logo-mark">AH</div>
          <span style="color:white;font-size:1.4rem">Advaith <em style="font-style:italic;font-family:var(--font-accent)">Homes</em></span>
        </a>
        <p class="footer__brand-desc">
          The UK's dedicated buyer's agent — we work exclusively for you, not the seller.
          Saving you time, stress, and thousands of pounds on your most important purchase.
        </p>
        <div class="footer__badge">🇬🇧 Proudly serving UK home buyers</div>
        <div class="footer__socials" style="margin-top:16px">
          <div class="footer__social">📘</div>
          <div class="footer__social">🐦</div>
          <div class="footer__social">📸</div>
          <div class="footer__social">▶️</div>
          <div class="footer__social">💼</div>
        </div>
      </div>

      <div>
        <div class="footer__col-title">Buying Guides</div>
        <div class="footer__links">
          <a href="pages/property-research.html" class="footer__link">Property Research Report</a>
          <a href="pages/legal-search.html" class="footer__link">Legal Search Packs</a>
          <a href="pages/buyers-guide.html" class="footer__link">Complete Buyer's Guide</a>
          <a href="pages/deposit-guide.html" class="footer__link">Deposit Guide</a>
          <a href="pages/mortgage-guide.html" class="footer__link">Mortgage Guide</a>
          <a href="pages/moving-guide.html" class="footer__link">Moving Guide</a>
          <a href="free-consultation.html" class="footer__link">Free Consultation Guide</a>
        </div>
      </div>

      <div>
        <div class="footer__col-title">Company</div>
        <div class="footer__links">
          <a href="index.html" class="footer__link">Home</a>
          <a href="services.html" class="footer__link">Our Services</a>
          <a href="about.html" class="footer__link">About & Our Story</a>
          <a href="previous-clients.html" class="footer__link">Client Stories</a>
          <a href="contact.html" class="footer__link">Contact Us</a>
          <a href="pages/privacy-policy.html" class="footer__link">Privacy Policy</a>
          <a href="pages/terms.html" class="footer__link">Terms & Conditions</a>
          <a href="pages/refund-policy.html" class="footer__link">Refund Policy</a>
        </div>
      </div>

      <div>
        <div class="footer__col-title">Get In Touch</div>
        <div class="footer__contact-item">
          <span class="footer__contact-icon">📞</span>
          <div>
            <div style="color:white;font-weight:600">+44 774 722 3762</div>
            <div style="font-size:.78rem;margin-top:2px">Mon–Sat, 9am–6pm</div>
          </div>
        </div>
        <div class="footer__contact-item">
          <span class="footer__contact-icon">✉️</span>
          <div>
            <div style="color:white;font-weight:600">contact@advaithhomes.co.uk</div>
            <div style="font-size:.78rem;margin-top:2px">We reply within 2 hours</div>
          </div>
        </div>
        <div class="footer__contact-item">
          <span class="footer__contact-icon">📍</span>
          <div>
            <div style="color:white;font-weight:600">London & Nationwide</div>
            <div style="font-size:.78rem;margin-top:2px">Covering all of England & Wales</div>
          </div>
        </div>
        <div style="margin-top:20px">
          <a href="free-consultation.html" class="btn btn-gold btn-sm" style="width:100%;justify-content:center">
            Book Free Consultation →
          </a>
        </div>
      </div>
    </div>

    <div class="footer__bottom">
      <div style="font-size:.8rem">© 2026 Advaith Homes Ltd. All rights reserved. Registered in England & Wales.</div>
      <div class="footer__legal">
        <a href="pages/privacy-policy.html" class="footer__link">Privacy Policy</a>
        <a href="pages/terms.html" class="footer__link">Terms & Conditions</a>
        <a href="pages/refund-policy.html" class="footer__link">Refund Policy</a>
        <a href="contact.html" class="footer__link">Contact</a>
      </div>
    </div>
  </div>
</footer>
`;

// ── Inject shared components ─────────────────
function initComponents() {
  const isPagesDir = window.location.pathname.includes('/pages/');
  const rootPrefix = isPagesDir ? '../' : '';

  let finalNav = NAV_HTML;
  let finalFooter = FOOTER_HTML;

  if (isPagesDir) {
    finalNav = finalNav.replace(/href="([a-zA-Z0-9-]+\.html)"/g, 'href="../$1"');
    finalNav = finalNav.replace(/href="pages\/([a-zA-Z0-9-]+\.html)"/g, 'href="$1"');
    finalNav = finalNav.replace(/src="logo\.png"/g, 'src="../logo.png"');

    finalFooter = finalFooter.replace(/href="([a-zA-Z0-9-]+\.html)"/g, 'href="../$1"');
    finalFooter = finalFooter.replace(/href="pages\/([a-zA-Z0-9-]+\.html)"/g, 'href="$1"');
  }

  // Nav
  const navTarget = document.getElementById('nav-placeholder');
  if (navTarget) navTarget.innerHTML = finalNav;

  let favicon = document.querySelector('link[rel="icon"]');
  if (!favicon) {
    favicon = document.createElement('link');
    favicon.rel = 'icon';
    favicon.type = 'image/png';
    favicon.href = rootPrefix + 'logo.png';
    document.head.appendChild(favicon);
  }

  // Footer
  const footerTarget = document.getElementById('footer-placeholder');
  if (footerTarget) footerTarget.innerHTML = finalFooter;

  // WhatsApp Floating Button
  let waFloat = document.querySelector('.floating-chat');
  if (!waFloat) {
    waFloat = document.createElement('a');
    waFloat.className = 'floating-chat';
    waFloat.href = 'https://wa.me/447747223762?text=Hi%20Advaith%20Homes,%20I%20would%20like%20to%20learn%20more%20about%20your%20services...';
    waFloat.target = '_blank';
    waFloat.rel = 'noopener noreferrer';
    waFloat.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
        <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157.1zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
      </svg>
      <span>Chat with us</span>`;
    document.body.appendChild(waFloat);
  }

  // Scroll effect
  const nav = document.getElementById('mainNav');
  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 20);
    });
  }

  // Hamburger
  const hamburger = document.getElementById('hamburger');
  const mobileNav = document.getElementById('mobileNav');
  if (hamburger && mobileNav) {
    hamburger.addEventListener('click', () => {
      mobileNav.classList.toggle('open');
    });
  }

  // Active page
  const page = document.body.dataset.page;
  document.querySelectorAll(`[data-page="${page}"]`).forEach(el => el.classList.add('active'));

  // Reveal on scroll
  const reveals = document.querySelectorAll('.reveal');
  if (reveals.length) {
    const revealOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const obs = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          // Add visible class
          e.target.classList.add('visible');
          
          // Handle delays if specified
          if (e.target.classList.contains('reveal-delay-1')) e.target.style.transitionDelay = '0.2s';
          if (e.target.classList.contains('reveal-delay-2')) e.target.style.transitionDelay = '0.4s';
          if (e.target.classList.contains('reveal-delay-3')) e.target.style.transitionDelay = '0.6s';
          
          obs.unobserve(e.target);
        }
      });
    }, revealOptions);

    reveals.forEach(el => {
      // Check if already in viewport
      const rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight) {
        el.classList.add('visible');
      } else {
        obs.observe(el);
      }
    });
  }

  // FAQ
  document.querySelectorAll('.faq-q').forEach(q => {
    q.addEventListener('click', () => {
      const item = q.closest('.faq-item');
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });

  // Counter animation
  document.querySelectorAll('.count-up').forEach(el => {
    const target = +el.dataset.target;
    const obs2 = new IntersectionObserver(entries => {
      if (entries[0].isIntersecting) {
        let current = 0;
        const inc = target / 60;
        const t = setInterval(() => {
          current = Math.min(current + inc, target);
          el.textContent = Math.floor(current).toLocaleString();
          if (current >= target) clearInterval(t);
        }, 16);
        obs2.unobserve(el);
      }
    });
    obs2.observe(el);
  });
}

document.addEventListener('DOMContentLoaded', initComponents);