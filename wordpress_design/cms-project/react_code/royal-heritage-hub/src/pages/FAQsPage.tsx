import { PageHero } from '@/components/common/PageHero';
import { FAQSection } from '@/components/home/FAQSection';

export default function FAQsPage() {
  return (
    <div>
      <PageHero pageKey="faqs" fallbackTitle="Frequently Asked Questions" />
      <FAQSection />
    </div>
  );
}
