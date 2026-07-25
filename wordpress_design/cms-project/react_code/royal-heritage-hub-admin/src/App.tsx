import { lazy, Suspense, useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { useAuthStore } from '@/store/useAuthStore';
import { AdminLayout } from '@/components/Layout';

const BASE = '/admin';

const LoginPage = lazy(() => import('@/pages/LoginPage'));
const DashboardPage = lazy(() => import('@/pages/DashboardPage'));
const ProductsPage = lazy(() => import('@/pages/ProductsPage'));
const OrdersPage = lazy(() => import('@/pages/OrdersPage'));
const CustomersPage = lazy(() => import('@/pages/CustomersPage'));
const BlogPage = lazy(() => import('@/pages/BlogPage'));
const CategoriesPage = lazy(() => import('@/pages/CategoriesPage'));
const BannersPage = lazy(() => import('@/pages/BannersPage'));
const CouponsPage = lazy(() => import('@/pages/CouponsPage'));
const CollectionsPage = lazy(() => import('@/pages/CollectionsPage'));
const TagsPage = lazy(() => import('@/pages/TagsPage'));
const CertificationsPage = lazy(() => import('@/pages/CertificationsPage'));
const ReviewsPage = lazy(() => import('@/pages/ReviewsPage'));
const SettingsPage = lazy(() => import('@/pages/SettingsPage'));

function RequireAuth({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuthStore();
  return isAuthenticated ? <>{children}</> : <Navigate to={`${BASE}/login`} replace />;
}

function Loader() {
  return <div className="flex min-h-screen items-center justify-center"><div className="h-8 w-8 animate-spin rounded-full border-2 border-gray-300 border-t-indigo-600" /></div>;
}

export default function App() {
  const init = useAuthStore((s) => s.init);
  useEffect(() => { init(); }, [init]);

  return (
    <BrowserRouter>
      <Suspense fallback={<Loader />}>
        <Routes>
          <Route path={`${BASE}/login`} element={<LoginPage />} />
          <Route element={<RequireAuth><AdminLayout /></RequireAuth>}>
            <Route path={`${BASE}/dashboard`} element={<DashboardPage />} />
            <Route path={`${BASE}/products`} element={<ProductsPage />} />
            <Route path={`${BASE}/orders`} element={<OrdersPage />} />
            <Route path={`${BASE}/customers`} element={<CustomersPage />} />
            <Route path={`${BASE}/blog`} element={<BlogPage />} />
            <Route path={`${BASE}/categories`} element={<CategoriesPage />} />
            <Route path={`${BASE}/banners`} element={<BannersPage />} />
            <Route path={`${BASE}/coupons`} element={<CouponsPage />} />
            <Route path={`${BASE}/collections`} element={<CollectionsPage />} />
            <Route path={`${BASE}/tags`} element={<TagsPage />} />
            <Route path={`${BASE}/certifications`} element={<CertificationsPage />} />
            <Route path={`${BASE}/reviews`} element={<ReviewsPage />} />
            <Route path={`${BASE}/settings`} element={<SettingsPage />} />
            <Route path={`${BASE}`} element={<Navigate to={`${BASE}/dashboard`} replace />} />
          </Route>
          <Route path="*" element={<Navigate to={`${BASE}/dashboard`} replace />} />
        </Routes>
      </Suspense>
    </BrowserRouter>
  );
}
