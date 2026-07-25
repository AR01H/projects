import { Routes, Route } from 'react-router-dom';
import { Layout } from '@/components/layout/Layout';
import { ROUTES } from '@/config/routes';

import HomePage from '@/pages/HomePage';
import ShopPage from '@/pages/ShopPage';
import ProductDetailPage from '@/pages/ProductDetailPage';
import CollectionsPage from '@/pages/CollectionsPage';
import CollectionDetailPage from '@/pages/CollectionDetailPage';
import CategoriesPage from '@/pages/CategoriesPage';
import CategoryLandingPage from '@/pages/CategoryLandingPage';
import TagsPage from '@/pages/TagsPage';
import TagCollectionPage from '@/pages/TagCollectionPage';
import ReviewsPage from '@/pages/ReviewsPage';
import BlogListingPage from '@/pages/BlogListingPage';
import BlogPostPage from '@/pages/BlogPostPage';
import GovtCertificationsPage from '@/pages/GovtCertificationsPage';
import CustomContentPage from '@/pages/CustomContentPage';
import WishlistPage from '@/pages/WishlistPage';
import CartPage from '@/pages/CartPage';
import CheckoutPage from '@/pages/CheckoutPage';
import AboutPage from '@/pages/AboutPage';
import ContactPage from '@/pages/ContactPage';
import FAQsPage from '@/pages/FAQsPage';
import ShippingPolicyPage from '@/pages/ShippingPolicyPage';
import ReturnPolicyPage from '@/pages/ReturnPolicyPage';
import PrivacyPolicyPage from '@/pages/PrivacyPolicyPage';
import TermsPage from '@/pages/TermsPage';
import NotFoundPage from '@/pages/NotFoundPage';

function App() {
  return (
    <Layout>
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
        <Route path={ROUTES.contact} element={<ContactPage />} />
        <Route path={ROUTES.faqs} element={<FAQsPage />} />
        <Route path="/shipping-policy" element={<ShippingPolicyPage />} />
        <Route path="/return-policy" element={<ReturnPolicyPage />} />
        <Route path="/privacy-policy" element={<PrivacyPolicyPage />} />
        <Route path="/terms" element={<TermsPage />} />
        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </Layout>
  );
}

export default App;
