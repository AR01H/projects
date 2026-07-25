import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { APP_NAME, CONTACT, COPYRIGHT_YEAR } from '@/config/constants';
import { SITE_CONFIG } from '@/config/site';
import { footerApi, type FooterData } from '@/api/footer';

const ICONS: Record<string, string> = {
  instagram: 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z',
  facebook: 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z',
  pinterest: 'M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 01.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12.017 24 18.635 24 24 18.633 24 12.013 24 5.393 18.633.026 12.017.026z',
  youtube: 'M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z',
  whatsapp: 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z',
};

const PAYMENT_ICONS: Record<string, string> = {
  upi: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z',
  visa: 'M2 4h20v16H2V4zm10 12c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z',
  mastercard: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z',
  rupay: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z',
  netbanking: 'M4 6h16v2H4V6zm0 5h16v2H4v-2zm0 5h16v2H4v-2z',
  cod: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z',
};

const TRUST_ICONS: Record<string, JSX.Element> = {
  handcrafted: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" className="h-7 w-7">
      <path d="M18 8c0 3.31-2.69 6-6 6s-6-2.69-6-6 2.69-6 6-6 6 2.69 6 6z" />
      <path d="M12 14v8M8 18h8" />
      <circle cx="12" cy="8" r="2" />
    </svg>
  ),
  certified: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" className="h-7 w-7">
      <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
    </svg>
  ),
  shipping: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" className="h-7 w-7">
      <rect x="1" y="3" width="15" height="13" />
      <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
      <circle cx="5.5" cy="18.5" r="2.5" />
      <circle cx="18.5" cy="18.5" r="2.5" />
    </svg>
  ),
  secure: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" className="h-7 w-7">
      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
      <path d="M9 12l2 2 4-4" />
    </svg>
  ),
};

export function Footer() {
  const [email, setEmail] = useState('');
  const [subscribed, setSubscribed] = useState(false);
  const [footerData, setFooterData] = useState<FooterData | null>(null);

  useEffect(() => {
    footerApi.getAll().then((data) => setFooterData(data));
  }, []);

  const handleNewsletterSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (email.trim()) {
      setSubscribed(true);
      setEmail('');
      setTimeout(() => setSubscribed(false), 4000);
    }
  };

  if (!footerData) return null;

  return (
    <>
    {/* ═══ TRUST STRIP — brass-accented badges ═══ */}
    <div className="footer-trust-strip border-y border-[var(--color-secondary)]/20 bg-gradient-to-r from-[var(--color-bg-cream)] via-[var(--color-bg-light)] to-[var(--color-bg-cream)]">
      <div className="mx-auto grid max-w-7xl grid-cols-2 gap-4 px-4 py-8 sm:px-6 sm:grid-cols-4 lg:px-8">
        {footerData.trustBadges.map((badge) => (
          <div
            key={badge.label}
            className="footer-trust-badge group flex flex-col items-center gap-3 rounded-[var(--radius-card)] border border-[var(--color-secondary)]/15 bg-[var(--color-bg-light)] p-5 text-center shadow-sm transition-all duration-300 hover:border-[var(--color-secondary)]/40 hover:shadow-md"
          >
            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-[var(--color-primary)]/10 to-[var(--color-secondary)]/10 text-[var(--color-primary)] transition-colors duration-300 group-hover:from-[var(--color-primary)]/20 group-hover:to-[var(--color-secondary)]/20">
              {TRUST_ICONS[badge.icon] || TRUST_ICONS.handcrafted}
            </div>
            <span className="font-display text-sm font-semibold text-[var(--color-text-primary)]">
              {badge.label}
            </span>
            <span className="text-xs leading-relaxed text-[var(--color-text-muted)]">{badge.description}</span>
          </div>
        ))}
      </div>
    </div>

    {/* ═══ MAIN FOOTER — brass grain texture ═══ */}
    <footer className="footer-grain footer-rough-edge textured-bg">
      <div className="footer-grain-overlay" />
      <div className="footer-vignette" />
      <div className="mx-auto max-w-7xl px-4 pt-16 pb-10 sm:px-6 lg:px-8">

        {/* Brand + Links grid */}
        <div className="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-12">

          {/* ═══ Brand Column — spans 4 cols ═══ */}
          <div className="lg:col-span-4">
            <img src="/logo.svg" alt="Royal Heritage Hub" className="h-10 mb-2" />
            <div className="mb-5 h-0.5 w-12 bg-gradient-to-r from-[var(--color-secondary)] to-transparent" />
            <p className="text-sm leading-relaxed text-[var(--color-text-secondary)]">
              {SITE_CONFIG.microcopy.footerDescription}
            </p>

            {/* Social Links */}
            <div className="mt-5 flex gap-2.5">
              {footerData.socialLinks.map((s) => (
                <a
                  key={s.platform}
                  href={s.url}
                  target="_blank"
                  rel="noreferrer"
                  aria-label={s.platform}
                  className="footer-social-icon flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-secondary)]/20 bg-[var(--color-bg-light)] text-[var(--color-text-muted)] transition-all duration-300 hover:border-[var(--color-primary)] hover:bg-[var(--color-primary)] hover:text-[var(--color-bg-light)]"
                >
                  {ICONS[s.icon] ? (
                    <svg viewBox="0 0 24 24" fill="currentColor" className="h-4 w-4">
                      <path d={ICONS[s.icon]} />
                    </svg>
                  ) : (
                    <span className="text-xs font-semibold">{s.platform[0]}</span>
                  )}
                </a>
              ))}
            </div>

            {/* Certification Badges */}
            <div className="mt-5 flex flex-wrap gap-2">
              {footerData.certifications.map((cert) => (
                <span
                  key={cert.name}
                  title={cert.description}
                  className="footer-cert-pill cursor-default rounded-full border border-[var(--color-secondary)]/20 bg-[var(--color-secondary)]/5 px-3 py-1 text-[0.65rem] font-medium text-[var(--color-secondary-dark)] transition-all hover:bg-[var(--color-secondary)]/10"
                >
                  {cert.name}
                </span>
              ))}
            </div>
          </div>

          {/* ═══ Quick Links — spans 2 cols ═══ */}
          <div className="lg:col-span-2">
            <h4 className="mb-1 font-display text-sm font-semibold uppercase tracking-wider text-[var(--color-text-primary)]">Quick Links</h4>
            <div className="mb-4 h-0.5 w-8 bg-gradient-to-r from-[var(--color-secondary)] to-transparent" />
            <ul className="flex flex-col gap-2.5">
              {footerData.quickLinks.map((l) => (
                <li key={l.href}>
                  <Link
                    to={l.href}
                    className="group flex items-center gap-2 text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-primary)]"
                  >
                    <span className="inline-block h-px w-0 bg-[var(--color-primary)] transition-all duration-300 group-hover:w-2" />
                    {l.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* ═══ Customer Support — spans 3 cols ═══ */}
          <div className="lg:col-span-3">
            <h4 className="mb-1 font-display text-sm font-semibold uppercase tracking-wider text-[var(--color-text-primary)]">Customer Support</h4>
            <div className="mb-4 h-0.5 w-8 bg-gradient-to-r from-[var(--color-secondary)] to-transparent" />
            <ul className="flex flex-col gap-2.5">
              {footerData.policyLinks.map((l) => (
                <li key={l.href}>
                  <Link
                    to={l.href}
                    className="group flex items-center gap-2 text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-primary)]"
                  >
                    <span className="inline-block h-px w-0 bg-[var(--color-primary)] transition-all duration-300 group-hover:w-2" />
                    {l.label}
                  </Link>
                </li>
              ))}
            </ul>

            {/* Working Hours */}
            <div className="mt-5 rounded-[var(--radius-card)] border border-[var(--color-secondary)]/15 bg-gradient-to-br from-[var(--color-bg-light)] to-[var(--color-bg-cream)] p-4">
              <h5 className="mb-1.5 font-display text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">
                {footerData.workingHours.label}
              </h5>
              <p className="text-xs text-[var(--color-text-secondary)]">{footerData.workingHours.days}</p>
              <p className="text-xs font-medium text-[var(--color-primary)]">{footerData.workingHours.time}</p>
              <p className="mt-0.5 text-[0.65rem] text-[var(--color-text-muted)]">{footerData.workingHours.closed}</p>
            </div>
          </div>

          {/* ═══ Contact + Newsletter — spans 3 cols ═══ */}
          <div className="lg:col-span-3">
            <h4 className="mb-1 font-display text-sm font-semibold uppercase tracking-wider text-[var(--color-text-primary)]">Get in Touch</h4>
            <div className="mb-4 h-0.5 w-8 bg-gradient-to-r from-[var(--color-secondary)] to-transparent" />
            <ul className="flex flex-col gap-3 text-sm text-[var(--color-text-secondary)]">
              <li>
                <a href={CONTACT.phoneHref} className="flex items-center gap-2.5 transition-colors hover:text-[var(--color-primary)]">
                  <span className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" className="h-3.5 w-3.5">
                      <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z" />
                    </svg>
                  </span>
                  {CONTACT.phone}
                </a>
              </li>
              <li>
                <a href={CONTACT.emailHref} className="flex items-center gap-2.5 transition-colors hover:text-[var(--color-primary)]">
                  <span className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" className="h-3.5 w-3.5">
                      <rect x="2" y="4" width="20" height="16" rx="2" />
                      <path d="M22 4l-10 8L2 4" />
                    </svg>
                  </span>
                  {CONTACT.email}
                </a>
              </li>
            </ul>

            {/* Newsletter */}
            <div className="mt-5">
              <h4 className="mb-2 font-display text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Newsletter</h4>
              <p className="mb-2.5 text-[0.7rem] leading-relaxed text-[var(--color-text-muted)]">
                {SITE_CONFIG.microcopy.newsletterDescription}
              </p>
              {subscribed ? (
                <div className="footer-newsletter-success rounded-[var(--radius-btn)] border border-[var(--color-success)] bg-[color-mix(in_srgb,var(--color-success)_10%,transparent)] p-2.5 text-center text-xs text-[var(--color-success)]">
                  Thank you for subscribing!
                </div>
              ) : (
                <form
                  onSubmit={handleNewsletterSubmit}
                  className="flex overflow-hidden rounded-[var(--radius-btn)] border border-[var(--color-secondary)]/20"
                >
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="Your email"
                    required
                    className="w-full bg-[var(--color-bg-light)] px-3 py-2.5 text-xs outline-none placeholder:text-[var(--color-text-muted)]"
                  />
                  <button
                    type="submit"
                    className="bg-gradient-to-r from-[var(--color-primary)] to-[var(--color-primary-dark)] px-4 text-[0.65rem] font-semibold uppercase tracking-wider text-[var(--color-bg-light)] transition-all hover:from-[var(--color-primary-dark)] hover:to-[var(--color-primary)]"
                  >
                    Join
                  </button>
                </form>
              )}
            </div>
          </div>
        </div>

        {/* ═══ Bottom Bar ═══ */}
        <div className="mt-12 border-t border-[var(--color-secondary)]/15 pt-6">
          <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <p className="text-[0.7rem] text-[var(--color-text-muted)]">
              &copy; {APP_NAME} {COPYRIGHT_YEAR}. All Rights Reserved.
            </p>

            {/* Payment Methods */}
            <div className="flex items-center gap-1.5">
              {footerData.paymentMethods.map((p) => (
                <span
                  key={p.name}
                  title={p.name}
                  className="flex items-center gap-1 rounded border border-[var(--color-secondary)]/10 bg-[var(--color-bg-light)] px-2 py-1 text-[0.6rem] font-medium text-[var(--color-text-muted)]"
                >
                  <span className="h-3 w-3 text-[var(--color-text-muted)]">
                    {PAYMENT_ICONS[p.icon] ? (
                      <svg viewBox="0 0 24 24" fill="currentColor" className="h-3 w-3">
                        <path d={PAYMENT_ICONS[p.icon]} />
                      </svg>
                    ) : null}
                  </span>
                  {p.name}
                </span>
              ))}
            </div>
          </div>

          {/* Made with love */}
          <p className="mt-4 text-center text-[0.6rem] tracking-wider text-[var(--color-text-muted)]">
            Handcrafted with care by artisans across India &middot; Supporting traditional crafts since generations
          </p>
        </div>
      </div>
    </footer>

      {/* WhatsApp floating button */}
      <a
        href="https://wa.me/917887699208"
        target="_blank"
        rel="noreferrer"
        aria-label="Chat on WhatsApp"
        className="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg transition-transform duration-300 hover:scale-110 hover:shadow-xl"
      >
        <svg viewBox="0 0 24 24" fill="currentColor" className="h-7 w-7">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
        </svg>
      </a>
    </>
  );
}
