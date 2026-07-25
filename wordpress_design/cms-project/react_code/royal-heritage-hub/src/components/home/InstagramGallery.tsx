import { SectionHeading } from '@/components/common/SectionHeading';

const GALLERY_IMAGES = [
  'https://picsum.photos/seed/rhh-insta-1/500/500',
  'https://picsum.photos/seed/rhh-insta-2/500/500',
  'https://picsum.photos/seed/rhh-insta-3/500/500',
  'https://picsum.photos/seed/rhh-insta-4/500/500',
  'https://picsum.photos/seed/rhh-insta-5/500/500',
  'https://picsum.photos/seed/rhh-insta-6/500/500',
];

export function InstagramGallery() {
  return (
    <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
      <SectionHeading
        eyebrow="@royalheritagehub"
        title="Follow Our Journey"
        description="Tag us in your home for a chance to be featured."
        align="center"
      />
      <div className="grid grid-cols-3 gap-2 sm:gap-4 lg:grid-cols-6">
        {GALLERY_IMAGES.map((src, i) => (
          <a
            key={i}
            href="https://instagram.com/royalheritagehub"
            target="_blank"
            rel="noreferrer"
            className="group relative aspect-square overflow-hidden rounded-[var(--radius-card)]"
          >
            <img
              src={src}
              alt="Instagram post"
              loading="lazy"
              className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
            />
            <div className="absolute inset-0 flex items-center justify-center bg-[var(--color-dark)]/0 transition-colors group-hover:bg-[var(--color-dark)]/30">
              <svg
                viewBox="0 0 24 24"
                className="h-6 w-6 opacity-0 transition-opacity group-hover:opacity-100"
                fill="none"
                stroke="var(--color-bg-light)"
                strokeWidth="1.8"
              >
                <rect x="3" y="3" width="18" height="18" rx="5" />
                <circle cx="12" cy="12" r="4" />
                <circle cx="17.5" cy="6.5" r="0.6" fill="var(--color-bg-light)" />
              </svg>
            </div>
          </a>
        ))}
      </div>
    </section>
  );
}
