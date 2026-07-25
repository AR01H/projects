import { useEffect, useState } from 'react';
import { useLocation } from 'react-router-dom';

export function ScrollToTop() {
  const { pathname } = useLocation();
  const [visible, setVisible] = useState(false);

  // Auto-scroll to top on route change
  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);

  // Show button after scrolling down 400px
  useEffect(() => {
    const onScroll = () => setVisible(window.scrollY > 400);
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  if (!visible) return null;

  return (
    <button
      onClick={scrollToTop}
      aria-label="Scroll to top"
      className="fixed bottom-24 right-6 z-50 flex h-11 w-11 items-center justify-center rounded-full border border-[var(--color-border)] bg-[var(--color-bg-light)]/90 shadow-lg backdrop-blur-sm transition-all duration-300 hover:bg-[var(--color-bg-light)] hover:shadow-xl hover:scale-110 sm:bottom-28 sm:right-8"
    >
      <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="var(--color-primary)" strokeWidth="2.5">
        <path d="M18 15l-6-6-6 6" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    </button>
  );
}
