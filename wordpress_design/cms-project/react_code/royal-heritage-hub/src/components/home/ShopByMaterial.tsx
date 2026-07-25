import { Link } from 'react-router-dom';
import { SectionHeading } from '@/components/common/SectionHeading';

const MATERIALS = [
  { name: 'Wood', query: 'wood', image: 'https://picsum.photos/seed/rhh-mat-wood/600/500' },
  { name: 'Brass', query: 'brass', image: 'https://picsum.photos/seed/rhh-mat-brass/600/500' },
  { name: 'Mango Wood', query: 'mango', image: 'https://picsum.photos/seed/rhh-mat-mango/600/500' },
  { name: 'Sheesham', query: 'sheesham', image: 'https://picsum.photos/seed/rhh-mat-sheesham/600/500' },
];

export function ShopByMaterial() {
  return (
    <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <SectionHeading eyebrow="Material Matters" title="Shop by Material" />
      <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
        {MATERIALS.map((m) => (
          <Link
            key={m.name}
            to={`/shop?search=${encodeURIComponent(m.query)}`}
            className="group relative overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-soft)]"
          >
            <div className="aspect-[6/5] overflow-hidden">
              <img
                src={m.image}
                alt={m.name}
                loading="lazy"
                className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
              />
              <div className="absolute inset-0 bg-[var(--color-dark)]/35 transition-colors group-hover:bg-[var(--color-dark)]/50" />
            </div>
            <div className="absolute inset-0 flex items-center justify-center">
              <span className="font-display text-lg text-[var(--color-bg-light)] sm:text-xl">{m.name}</span>
            </div>
          </Link>
        ))}
      </div>
    </section>
  );
}
