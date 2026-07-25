import { PageHero } from '@/components/common/PageHero';
import { CraftsmanshipStory } from '@/components/home/CraftsmanshipStory';
import { WhyChooseUs } from '@/components/home/WhyChooseUs';

export default function AboutPage() {
  return (
    <div>
      <PageHero pageKey="about" fallbackTitle="Our Story" />
      <CraftsmanshipStory />
      <WhyChooseUs />
    </div>
  );
}
