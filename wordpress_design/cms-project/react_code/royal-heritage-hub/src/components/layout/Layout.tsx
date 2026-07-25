import { useEffect } from 'react';
import type { ReactNode } from 'react';
import { Header } from './Header';
import { Footer } from './Footer';
import { CartDrawer } from './CartDrawer';
import { ScrollToTop } from '@/components/common/ScrollToTop';
import { SocialProofToast } from '@/components/common/SocialProofToast';
import { useCartStore } from '@/store/useCartStore';
import { useWishlistStore } from '@/store/useWishlistStore';

export function Layout({ children }: { children: ReactNode }) {
  const initCart = useCartStore((s) => s.init);
  const initWishlist = useWishlistStore((s) => s.init);

  useEffect(() => {
    initCart();
    initWishlist();
  }, [initCart, initWishlist]);

  return (
    <div className="flex min-h-screen flex-col bg-[var(--color-bg)]">
      <ScrollToTop />
      <Header />
      <main className="flex-1">{children}</main>
      <Footer />
      <CartDrawer />
      <SocialProofToast />
    </div>
  );
}
