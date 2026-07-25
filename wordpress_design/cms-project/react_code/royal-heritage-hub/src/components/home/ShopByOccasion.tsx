import { Link } from 'react-router-dom';
import { SectionHeading } from '@/components/common/SectionHeading';
import { SITE_CONFIG } from '@/config/site';

const OCCASIONS = [
  { name: 'Housewarming', link: '/collections/gift-collections', image: 'https://picsum.photos/seed/rhh-occ-housewarming/500/650' },
  { name: 'Wedding', link: '/collections/gift-collections', image: 'https://picsum.photos/seed/rhh-occ-wedding/500/650' },
  { name: 'Diwali', link: '/collections/festive-collections', image: 'https://picsum.photos/seed/rhh-occ-diwali/500/650' },
  { name: 'Corporate', link: '/collections/corporate-gifts', image: 'https://picsum.photos/seed/rhh-occ-corporate/500/650' },
  { name: 'Birthday', link: '/collections/gift-collections', image: 'https://picsum.photos/seed/rhh-occ-birthday/500/650' },
];

export function ShopByOccasion() {
  return (
    <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Gifting Made Easy" title="Shop by Occasion" description={SITE_CONFIG.microcopy.shopByOccasionDescription} />
      <div className="flex gap-3 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] sm:grid sm:grid-cols-5 sm:gap-4 [&::-webkit-scrollbar]:hidden">
        {OCCASIONS.map((o, i) => (
          <Link
            key={o.name}
            to={o.link}
            className="group relative w-40 flex-shrink-0 overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-card)] sm:w-auto"
            style={{ marginTop: i % 2 === 1 ? '1.25rem' : 0 }}
          >
            <div className="aspect-[3/4] overflow-hidden">
              <img
                src={o.image}
                alt={o.name}
                loading="lazy"
                className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
              />
              <div className="absolute inset-0 bg-gradient-to-t from-[var(--color-dark)]/80 to-transparent" />
            </div>
            <div className="absolute inset-x-0 bottom-0 p-3">
              <span className="font-display text-sm text-[var(--color-bg-light)] sm:text-base">{o.name}</span>
            </div>
          </Link>
        ))}
      </div>
    </section>
  );
}
