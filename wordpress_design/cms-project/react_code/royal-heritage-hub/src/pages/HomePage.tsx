import { Hero } from '@/components/home/Hero';
import { CategoryStrip } from '@/components/home/CategoryStrip';
import { FeaturedCategories } from '@/components/home/FeaturedCategories';
import { ProductRail } from '@/components/home/ProductRail';
import { TrendingMasonry } from '@/components/home/TrendingMasonry';
import { DealOfTheDay } from '@/components/home/DealOfTheDay';
import { ShopByMaterial } from '@/components/home/ShopByMaterial';
import { ShopByOccasion } from '@/components/home/ShopByOccasion';
import { RecentlyViewed } from '@/components/home/RecentlyViewed';
import { PromoBannerSlot } from '@/components/home/PromoBannerSlot';
import { WhyChooseUs } from '@/components/home/WhyChooseUs';
import { CraftsmanshipStory } from '@/components/home/CraftsmanshipStory';
import { Testimonials } from '@/components/home/Testimonials';
import { InstagramGallery } from '@/components/home/InstagramGallery';
import { Newsletter } from '@/components/home/Newsletter';
import { FAQSection } from '@/components/home/FAQSection';
import { ArtisanSpotlight } from '@/components/home/ArtisanSpotlight';
import { CraftRegions } from '@/components/home/CraftRegions';
import { GITaggedShowcase } from '@/components/home/GITaggedShowcase';
import { SEO } from '@/components/common/SEO';
import { productsApi } from '@/api/products';
import { ROUTES } from '@/config/routes';
import { SITE_CONFIG } from '@/config/site';

export default function HomePage() {
  return (
    <>
      <SEO
        title={SITE_CONFIG.brand.name}
        description={SITE_CONFIG.brand.tagline + ' — ' + SITE_CONFIG.microcopy.footerDescription}
      />
      <Hero />
      <CategoryStrip />

      <ProductRail
        eyebrow="Just In"
        title="Newly Arrived"
        description={SITE_CONFIG.microcopy.newArrivalsDescription}
        viewAllLink={`${ROUTES.shop}?sort=newest`}
        fetcher={() => productsApi.getNewArrivals(8)}
      />

      <PromoBannerSlot index={0} />

      <TrendingMasonry />

      <ProductRail
        eyebrow="Customer Favourites"
        title="Best Sellers"
        description="The pieces our customers keep coming back for."
        viewAllLink={`${ROUTES.shop}?sort=best-selling`}
        fetcher={() => productsApi.getBestSellers(8)}
      />

      <FeaturedCategories />

      <GITaggedShowcase />

      <DealOfTheDay />

      <ShopByOccasion />

      <PromoBannerSlot index={2} />

      <ProductRail
        eyebrow="Rare & Small-Batch"
        title="Limited Time Offers"
        description="Once these sell out, they won't be made again."
        viewAllLink="/collections/limited-edition"
        fetcher={() => productsApi.getLimitedEdition(8)}
      />

      <ShopByMaterial />

      <CraftRegions />

      <WhyChooseUs />

      <ProductRail
        eyebrow="Editor's Picks"
        title={`${SITE_CONFIG.terminology.qualityAdjective} Picks`}
        description={`${SITE_CONFIG.terminology.productUnitPlural.charAt(0).toUpperCase() + SITE_CONFIG.terminology.productUnitPlural.slice(1)} our team can't stop recommending.`}
        viewAllLink="/collections/handpicked-picks"
        fetcher={() => productsApi.getFeatured(8)}
      />

      <PromoBannerSlot index={4} />

      <ProductRail
        eyebrow="Celebration Ready"
        title="Festival Collections"
        description="Curated for Diwali, weddings, and every celebration in between."
        viewAllLink="/collections/festive-collections"
        fetcher={() => productsApi.getFestive(8)}
      />

      <CraftsmanshipStory />

      <ArtisanSpotlight />

      <ProductRail
        eyebrow="Thoughtfully Curated"
        title="Gift Collections"
        description="Beautifully packaged gifts for every relationship."
        viewAllLink="/collections/gift-collections"
        fetcher={() => productsApi.getByIds(['prod-005', 'prod-009', 'prod-012', 'prod-006'])}
      />

      <ProductRail
        eyebrow="Just for You"
        title="Recommended Products"
        description="Hand-picked based on our top-rated pieces."
        fetcher={() => productsApi.getRecommended(8)}
      />

      <RecentlyViewed />

      <Testimonials />
      <InstagramGallery />
      <Newsletter />
      <FAQSection />
    </>
  );
}
