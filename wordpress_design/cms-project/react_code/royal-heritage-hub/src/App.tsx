import { lazy, Suspense } from 'react';
import { Routes, Route } from 'react-router-dom';
import { Layout } from '@/components/layout/Layout';
import { ROUTES } from '@/config/routes';

// Eagerly load the homepage for instant first paint
import HomePage from '@/pages/HomePage';

// Lazy-load all other routes
const ShopPage = lazy(() => import('@/pages/ShopPage'));
const ProductDetailPage = lazy(() => import('@/pages/ProductDetailPage'));
const CollectionsPage = lazy(() => import('@/pages/CollectionsPage'));
const CollectionDetailPage = lazy(() => import('@/pages/CollectionDetailPage'));
const CategoriesPage = lazy(() => import('@/pages/CategoriesPage'));
const CategoryLandingPage = lazy(() => import('@/pages/CategoryLandingPage'));
const TagsPage = lazy(() => import('@/pages/TagsPage'));
const TagCollectionPage = lazy(() => import('@/pages/TagCollectionPage'));
const ReviewsPage = lazy(() => import('@/pages/ReviewsPage'));
const BlogListingPage = lazy(() => import('@/pages/BlogListingPage'));
const BlogPostPage = lazy(() => import('@/pages/BlogPostPage'));
const GovtCertificationsPage = lazy(() => import('@/pages/GovtCertificationsPage'));
const CustomContentPage = lazy(() => import('@/pages/CustomContentPage'));
const WishlistPage = lazy(() => import('@/pages/WishlistPage'));
const CartPage = lazy(() => import('@/pages/CartPage'));
const CheckoutPage = lazy(() => import('@/pages/CheckoutPage'));
const AboutPage = lazy(() => import('@/pages/AboutPage'));
const ArtisansPage = lazy(() => import('@/pages/ArtisansPage'));
const CraftRegionsPage = lazy(() => import('@/pages/CraftRegionsPage'));
const ContactPage = lazy(() => import('@/pages/ContactPage'));
const FAQsPage = lazy(() => import('@/pages/FAQsPage'));
const ShippingPolicyPage = lazy(() => import('@/pages/ShippingPolicyPage'));
const ReturnPolicyPage = lazy(() => import('@/pages/ReturnPolicyPage'));
const PrivacyPolicyPage = lazy(() => import('@/pages/PrivacyPolicyPage'));
const TermsPage = lazy(() => import('@/pages/TermsPage'));
const LoginPage = lazy(() => import('@/pages/LoginPage'));
const RegisterPage = lazy(() => import('@/pages/RegisterPage'));
const ProfilePage = lazy(() => import('@/pages/ProfilePage'));
const OrderDetailPage = lazy(() => import('@/pages/OrderDetailPage'));
const NotFoundPage = lazy(() => import('@/pages/NotFoundPage'));

function PageLoader() {
  return (
    <div className="flex min-h-[50vh] items-center justify-center">
      <div className="h-8 w-8 animate-spin rounded-full border-2 border-[var(--color-border)] border-t-[var(--color-primary)]" />
    </div>
  );
}

function App() {
  return (
    <Layout>
      <Suspense fallback={<PageLoader />}>
        <Routes>
          <Route path={ROUTES.home} element={<HomePage />} />
          <Route path={ROUTES.shop} element={<ShopPage />} />
          <Route path={ROUTES.category} element={<ShopPage />} />
          <Route path={ROUTES.product} element={<ProductDetailPage />} />
          <Route path={ROUTES.collections} element={<CollectionsPage />} />
          <Route path={ROUTES.collection} element={<CollectionDetailPage />} />
          <Route path={ROUTES.categories} element={<CategoriesPage />} />
          <Route path={ROUTES.categoryLanding} element={<CategoryLandingPage />} />
          <Route path={ROUTES.tags} element={<TagsPage />} />
          <Route path={ROUTES.tag} element={<TagCollectionPage />} />
          <Route path={ROUTES.reviews} element={<ReviewsPage />} />
          <Route path={ROUTES.blog} element={<BlogListingPage />} />
          <Route path={ROUTES.blogCategory} element={<BlogListingPage />} />
          <Route path={ROUTES.blogPost} element={<BlogPostPage />} />
          <Route path={ROUTES.govtCertifications} element={<GovtCertificationsPage />} />
          <Route path={ROUTES.page} element={<CustomContentPage />} />
          <Route path={ROUTES.wishlist} element={<WishlistPage />} />
          <Route path={ROUTES.cart} element={<CartPage />} />
          <Route path={ROUTES.checkout} element={<CheckoutPage />} />
          <Route path={ROUTES.about} element={<AboutPage />} />
          <Route path={ROUTES.artisans} element={<ArtisansPage />} />
          <Route path={ROUTES.craftRegions} element={<CraftRegionsPage />} />
          <Route path={ROUTES.contact} element={<ContactPage />} />
          <Route path={ROUTES.faqs} element={<FAQsPage />} />
          <Route path="/shipping-policy" element={<ShippingPolicyPage />} />
          <Route path="/return-policy" element={<ReturnPolicyPage />} />
          <Route path="/privacy-policy" element={<PrivacyPolicyPage />} />
          <Route path="/terms" element={<TermsPage />} />
          <Route path={ROUTES.login} element={<LoginPage />} />
          <Route path={ROUTES.register} element={<RegisterPage />} />
          <Route path={ROUTES.profile} element={<ProfilePage />} />
          <Route path={ROUTES.orderDetail} element={<OrderDetailPage />} />
          <Route path="*" element={<NotFoundPage />} />
        </Routes>
      </Suspense>
    </Layout>
  );
}

export default App;
