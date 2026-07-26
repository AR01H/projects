/**
 * Centralized Callbacks — All shared callbacks in one place.
 * Import from here instead of defining callbacks in individual components.
 */

import { useCallback, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { useCartStore } from '@/store/useCartStore';
import { useWishlistStore } from '@/store/useWishlistStore';
import { useCouponStore } from '@/store/useCouponStore';
import { useAuthStore } from '@/store/useAuthStore';
import { ROUTES } from '@/config/routes';
import type { Product } from '@/types/product';

// ─── Mega Menu Callbacks ───

export function useMegaMenuCallbacks() {
  const megaMenuTimeout = useRef<ReturnType<typeof setTimeout>>(undefined);
  const setMegaMenuOpen = useRef<(val: string | null) => void>(() => {});

  const handleMegaEnter = useCallback((label: string) => {
    clearTimeout(megaMenuTimeout.current);
    setMegaMenuOpen.current(label);
  }, []);

  const handleMegaLeave = useCallback(() => {
    megaMenuTimeout.current = setTimeout(() => setMegaMenuOpen.current(null), 200);
  }, []);

  const handleMegaMenuEnter = useCallback(() => {
    clearTimeout(megaMenuTimeout.current);
  }, []);

  return { handleMegaEnter, handleMegaLeave, handleMegaMenuEnter, megaMenuTimeout, setMegaMenuOpen };
}

// ─── Cart Callbacks ───

export function useCartCallbacks() {
  const addItem = useCartStore((s) => s.addItem);
  const updateQuantity = useCartStore((s) => s.updateQuantity);
  const removeItem = useCartStore((s) => s.removeItem);
  const toggleCart = useCartStore((s) => s.toggleCart);

  const handleAddToCart = useCallback(async (product: Product, quantity = 1) => {
    await addItem(product, quantity);
  }, [addItem]);

  const handleRemoveFromCart = useCallback(async (itemId: string) => {
    await removeItem(itemId);
  }, [removeItem]);

  const handleUpdateQuantity = useCallback(async (itemId: string, quantity: number) => {
    await updateQuantity(itemId, quantity);
  }, [updateQuantity]);

  const handleClearCart = useCallback(async () => {
    await useCartStore.getState().removeItem;
  }, []);

  return { handleAddToCart, handleRemoveFromCart, handleUpdateQuantity, handleClearCart, toggleCart };
}

// ─── Wishlist Callbacks ───

export function useWishlistCallbacks() {
  const toggle = useWishlistStore((s) => s.toggle);
  const items = useWishlistStore((s) => s.items);

  const handleToggleWishlist = useCallback(async (product: Product) => {
    await toggle(product);
  }, [toggle]);

  const handleRemoveFromWishlist = useCallback(async (productId: string) => {
    const product = items.find((p) => p.id === productId);
    if (product) await toggle(product);
  }, [items, toggle]);

  const handleMoveToCart = useCallback(async (productId: string) => {
    const product = items.find((p) => p.id === productId);
    if (!product) return;
    const addItemCart = useCartStore.getState().addItem;
    await addItemCart(product, 1);
    await toggle(product);
  }, [items, toggle]);

  const isWishlisted = useCallback((productId: string) => {
    return items.some((p) => p.id === productId);
  }, [items]);

  return { handleToggleWishlist, handleRemoveFromWishlist, handleMoveToCart, isWishlisted };
}

// ─── Coupon Callbacks ───

export function useCouponCallbacks() {
  const validateCoupon = useCouponStore((s) => s.validate);
  const removeCoupon = useCouponStore((s) => s.remove);
  const appliedCoupon = useCouponStore((s) => s.appliedCoupon);
  const couponError = useCouponStore((s) => s.error);
  const couponLoading = useCouponStore((s) => s.loading);
  const discountAmount = useCouponStore((s) => s.discountAmount);
  const freeShipping = useCouponStore((s) => s.freeShipping);
  const freeGift = useCouponStore((s) => s.freeGift);

  const handleApplyCoupon = useCallback((code: string, subtotal: number, itemCount: number) => {
    validateCoupon(code, subtotal, itemCount);
  }, [validateCoupon]);

  const handleRemoveCoupon = useCallback(() => {
    removeCoupon();
  }, [removeCoupon]);

  return { handleApplyCoupon, handleRemoveCoupon, appliedCoupon, couponError, couponLoading, discountAmount, freeShipping, freeGift };
}

// ─── Auth Callbacks ───

export function useAuthCallbacks() {
  const login = useAuthStore((s) => s.login);
  const logout = useAuthStore((s) => s.logout);
  const register = useAuthStore((s) => s.register);
  const navigate = useNavigate();

  const handleLogin = useCallback(async (email: string, password: string) => {
    await login(email, password);
    navigate(ROUTES.home);
  }, [login, navigate]);

  const handleRegister = useCallback(async (name: string, email: string, password: string, phone?: string) => {
    await register(name, email, password, phone);
    navigate(ROUTES.home);
  }, [register, navigate]);

  const handleLogout = useCallback(async () => {
    await logout();
    navigate(ROUTES.home);
  }, [logout, navigate]);

  return { handleLogin, handleRegister, handleLogout };
}

// ─── Search Callbacks ───

export function useSearchCallbacks() {
  const navigate = useNavigate();

  const handleSearch = useCallback((query: string) => {
    if (query.trim()) {
      navigate(`${ROUTES.shop}?search=${encodeURIComponent(query.trim())}`);
    }
  }, [navigate]);

  const handleSearchSubmit = useCallback((e: React.FormEvent, query: string, setSearchOpen?: (v: boolean) => void, setQuery?: (v: string) => void) => {
    e.preventDefault();
    if (query.trim()) {
      navigate(`${ROUTES.shop}?search=${encodeURIComponent(query.trim())}`);
      setSearchOpen?.(false);
      setQuery?.('');
    }
  }, [navigate]);

  return { handleSearch, handleSearchSubmit };
}

// ─── Share Callbacks ───

export function useShareCallbacks() {
  const handleShare = useCallback(async (title: string, url?: string) => {
    const shareUrl = url || window.location.href;
    if (navigator.share) {
      await navigator.share({ title, url: shareUrl });
    } else {
      await navigator.clipboard?.writeText(shareUrl);
    }
  }, []);

  return { handleShare };
}
