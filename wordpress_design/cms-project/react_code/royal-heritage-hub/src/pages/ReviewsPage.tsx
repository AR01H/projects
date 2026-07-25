import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { reviewsApi, type AggregatedReview } from '@/api/reviews';
import { Rating } from '@/components/common/Rating';
import { Reveal } from '@/components/common/Reveal';
import { HorizontalScroller } from '@/components/common/HorizontalScroller';
import { buildRoute, ROUTES } from '@/config/routes';
import { SITE_CONFIG } from '@/config/site';
import { SEO } from '@/components/common/SEO';

const VIDEO_REVIEWS = [
  { id: 'vr-1', author: 'Priya M.', location: 'Mumbai', thumbnail: 'https://picsum.photos/seed/vrev-1/400/300', videoUrl: 'https://www.youtube.com/embed/dQw4w9WgXcQ', rating: 5, product: 'Kondapalli Dancing Couple', comment: 'Unboxing was an experience in itself! The craftsmanship is even better in person. The natural pigments give it such warmth.' },
  { id: 'vr-2', author: 'Arjun N.', location: 'Delhi', thumbnail: 'https://picsum.photos/seed/vrev-2/400/300', videoUrl: 'https://www.youtube.com/embed/dQw4w9WgXcQ', rating: 5, product: 'Brass Ganesha Idol', comment: 'The weight and finish of this Ganesha is incredible. Lost-wax casting at its finest. Worth every rupee.' },
  { id: 'vr-3', author: 'Meera J.', location: 'Bengaluru', thumbnail: 'https://picsum.photos/seed/vrev-3/400/300', videoUrl: 'https://www.youtube.com/embed/dQw4w9WgXcQ', rating: 5, product: 'Channapatna Toy Set', comment: 'My daughter absolutely loves these! Non-toxic, colourful, and beautifully made. Already bought a second set.' },
  { id: 'vr-4', author: 'Vikram S.', location: 'Pune', thumbnail: 'https://picsum.photos/seed/vrev-4/400/300', videoUrl: 'https://www.youtube.com/embed/dQw4w9WgXcQ', rating: 5, product: 'Wooden Temple', comment: 'The jaali work on the temple is mesmerising. Perfect for our pooja room. Great packaging too.' },
  { id: 'vr-5', author: 'Neha T.', location: 'Kolkata', thumbnail: 'https://picsum.photos/seed/vrev-5/400/300', videoUrl: 'https://www.youtube.com/embed/dQw4w9WgXcQ', rating: 5, product: 'Elephant Pair', comment: 'These elephants are stunning. The mango wood grain is gorgeous. They sit perfectly at our entrance.' },
  { id: 'vr-6', author: 'Sanjay K.', location: 'Chennai', thumbnail: 'https://picsum.photos/seed/vrev-6/400/300', videoUrl: 'https://www.youtube.com/embed/dQw4w9WgXcQ', rating: 4, product: 'Peacock Wall Panel', comment: 'The hand-painted peacock is vibrant. Great wall art for our living room. Delivery was fast.' },
];

const CUSTOMER_IMAGES = [
  { id: 'ci-1', src: 'https://picsum.photos/seed/cust-1/400/500', author: 'Ananya R.', product: 'Dancing Couple', caption: 'Living room centerpiece' },
  { id: 'ci-2', src: 'https://picsum.photos/seed/cust-2/400/400', author: 'Karan D.', product: 'Elephant Pair', caption: 'Entryway decor' },
  { id: 'ci-3', src: 'https://picsum.photos/seed/cust-3/500/400', author: 'Priya M.', product: 'Brass Diya Set', caption: 'Diwali setup' },
  { id: 'ci-4', src: 'https://picsum.photos/seed/cust-4/400/600', author: 'Rohit P.', product: 'Toy Set', caption: 'Playtime!' },
  { id: 'ci-5', src: 'https://picsum.photos/seed/cust-5/400/400', author: 'Deepa S.', product: 'Jewellery Box', caption: 'Gift for wife' },
  { id: 'ci-6', src: 'https://picsum.photos/seed/cust-6/500/400', author: 'Sanjay K.', product: 'Wall Panel', caption: 'Office wall art' },
  { id: 'ci-7', src: 'https://picsum.photos/seed/cust-7/400/500', author: 'Neha T.', product: 'Name Plate', caption: 'Home entrance' },
  { id: 'ci-8', src: 'https://picsum.photos/seed/cust-8/400/400', author: 'Amit V.', product: 'Serving Tray', caption: 'Festive hosting' },
  { id: 'ci-9', src: 'https://picsum.photos/seed/cust-9/500/500', author: 'Lakshmi R.', product: 'Village Set', caption: 'Kids love it' },
  { id: 'ci-10', src: 'https://picsum.photos/seed/cust-10/400/400', author: 'Vikram S.', product: 'Temple', caption: 'Pooja room' },
  { id: 'ci-11', src: 'https://picsum.photos/seed/cust-11/500/400', author: 'Divya R.', product: 'Toy Set', caption: 'Birthday gift' },
  { id: 'ci-12', src: 'https://picsum.photos/seed/cust-12/400/500', author: 'Rahul G.', product: 'Name Plate', caption: 'New home' },
];

const WHATSAPP_MESSAGES = [
  { id: 'wa-1', author: 'Rahul G., Jaipur', text: 'Just received my order! The Kondapalli toys are absolutely beautiful. My kids are playing with them non-stop. The painting quality is incredible — you can see each brush stroke. Thank you RHH!', time: '2:34 PM', product: 'Kondapalli Dancing Couple', hasImage: true },
  { id: 'wa-2', author: 'Sneha P., Hyderabad', text: 'The wooden temple is even more gorgeous than the photos. The jaali work is mesmerising. We placed it in our pooja room and it looks divine. Will definitely order more!', time: '11:20 AM', product: 'Hand-Carved Temple', hasImage: true },
  { id: 'wa-3', author: 'Vikash M., Lucknow', text: 'Ordered corporate gifts for our team of 50. Everyone loved them! The packaging was premium with custom gift tags. Great for Diwali gifting. Will order again next year.', time: '4:15 PM', product: 'Pen Stand', hasImage: false },
  { id: 'wa-4', author: 'Pooja S., Chennai', text: 'The brass Ganesha is stunning! Heavy, well-cast, and arrived perfectly packed. The lost-wax detail is museum quality. Will order the diya set next for Diwali.', time: '9:45 AM', product: 'Brass Ganesha Idol', hasImage: true },
  { id: 'wa-5', author: 'Manish K., Kolkata', text: 'Bought the name plate as a housewarming gift. The engraving quality is top-notch — the teak wood grain shows through beautifully. Friends kept asking where I got it!', time: '6:30 PM', product: 'Name Plate', hasImage: false },
  { id: 'wa-6', author: 'Divya R., Goa', text: 'These Channapatna toys are amazing! Safe for my toddler and so colourful. The vegetable-dye finish is vibrant. Already ordered a second set as a birthday gift.', time: '1:10 PM', product: 'Channapatna Toy Set', hasImage: true },
  { id: 'wa-7', author: 'Amit V., Mumbai', text: 'The serving tray set is perfect for festive hosting. Hand-painted floral motifs are gorgeous. Used them for Diwali dinner party — guests were impressed!', time: '8:20 PM', product: 'Serving Tray Set', hasImage: false },
  { id: 'wa-8', author: 'Deepa S., Bengaluru', text: 'Bought the jewellery box for my mother-in-law. She was moved to tears! The brass inlay work is exquisite. Best gift I have ever given.', time: '3:45 PM', product: 'Jewellery Box', hasImage: true },
];

const BIG_REVIEWS = [
  {
    id: 'br-1',
    author: 'Ananya R.',
    location: 'Bengaluru',
    rating: 5,
    product: 'Kondapalli Dancing Couple',
    productImage: 'https://picsum.photos/seed/prod-001/200/200',
    title: 'A masterpiece of Indian craftsmanship',
    text: 'I ordered the Kondapalli dancing couple for our living room and I am absolutely blown away. The attention to detail in each figure is remarkable — from the intricate costume patterns to the natural pigment colours. You can feel the hand of the artisan in every brushstroke. The packaging was premium and the piece arrived in perfect condition. This is not just a decorative item — it is a piece of Indian heritage that tells a story. My entire family admires it every day.',
    images: ['https://picsum.photos/seed/br1-1/400/300', 'https://picsum.photos/seed/br1-2/400/300', 'https://picsum.photos/seed/br1-3/400/300'],
    date: '2026-05-12',
    verified: true,
  },
  {
    id: 'br-2',
    author: 'Vikram S.',
    location: 'Mumbai',
    rating: 5,
    product: 'Brass Ganesha Idol',
    productImage: 'https://picsum.photos/seed/prod-006/200/200',
    title: 'Museum-quality lost-wax casting',
    text: 'The brass Ganesha is the most beautiful idol I have ever owned. The lost-wax casting technique gives it such character — each curve and detail feels alive. It has real weight to it, which adds to the premium feel. I placed it in our pooja room and it has become the centrepiece. The golden sheen catches light beautifully throughout the day. I have received so many compliments from guests. Highly recommend for anyone looking for authentic Indian brass work.',
    images: ['https://picsum.photos/seed/br2-1/400/300', 'https://picsum.photos/seed/br2-2/400/300'],
    date: '2026-05-30',
    verified: true,
  },
  {
    id: 'br-3',
    author: 'Priya M.',
    location: 'Delhi',
    rating: 5,
    product: 'Hand-Carved Wooden Temple',
    productImage: 'https://picsum.photos/seed/prod-003/200/200',
    title: 'Our pooja room is complete now',
    text: 'Fast delivery, careful packaging, and the wooden temple looks even better in person than in photos. The jaali work is incredibly fine — you can see through the lattice patterns. The teak finish is rich and warm. It fits perfectly in our pooja corner. My mother was very impressed with the craftsmanship, and she is someone who has seen a lot of traditional woodwork. The small shelf for diyas is a thoughtful touch. Five stars without hesitation.',
    images: ['https://picsum.photos/seed/br3-1/400/300', 'https://picsum.photos/seed/br3-2/400/300', 'https://picsum.photos/seed/br3-3/400/300'],
    date: '2026-04-18',
    verified: true,
  },
  {
    id: 'br-4',
    author: 'Rohit P.',
    location: 'Pune',
    rating: 5,
    product: 'Channapatna Wooden Toy Set',
    productImage: 'https://picsum.photos/seed/prod-010/200/200',
    title: 'Safe, colourful, and absolutely charming',
    text: 'My daughter chews on everything so the non-toxic finish mattered a lot. These Channapatna toys are vibrant, safe, and beautifully made. The vegetable-dye lacquer is genuinely non-toxic — I tested it! Each animal figure has its own personality. The set came in a nice cotton pouch. My daughter plays with them every single day. This is what toys should be — handmade, safe, and full of character. Already ordered a second set for my niece.',
    images: ['https://picsum.photos/seed/br4-1/400/300', 'https://picsum.photos/seed/br4-2/400/300'],
    date: '2026-06-01',
    verified: true,
  },
];

const TESTIMONIALS_VIDEO = [
  { id: 'tv-1', author: 'Ananya R., Bengaluru', text: 'The Kondapalli dancing couple sits proudly in our living room now. The detail is unreal for something entirely hand-painted.', rating: 5 },
  { id: 'tv-2', author: 'Vikram S., Mumbai', text: 'Ordered brass diyas for Diwali gifting — every single recipient asked where I bought them from. Beautiful packaging too.', rating: 5 },
  { id: 'tv-3', author: 'Priya M., Delhi', text: 'Fast delivery, careful packaging, and the wooden temple looks even better in person than in photos.', rating: 5 },
  { id: 'tv-4', author: 'Rohit P., Pune', text: 'My daughter chews on everything so the non-toxic finish mattered a lot. These toys are safe and gorgeous.', rating: 5 },
  { id: 'tv-5', author: 'Deepa S., Chennai', text: 'The brass Ganesha is even more beautiful in person. Perfect for our pooja room. Exceeded expectations!', rating: 5 },
  { id: 'tv-6', author: 'Karan D., Jaipur', text: 'Beautiful craftsmanship but check dimensions carefully before ordering. The quality is outstanding though.', rating: 4 },
];

/* ─── Component: Video Card ─── */
function VideoCard({ video }: { video: typeof VIDEO_REVIEWS[0] }) {
  const [playing, setPlaying] = useState(false);
  return (
    <div className="flex-shrink-0 w-72 sm:w-80">
      <div className="relative overflow-hidden rounded-[var(--radius-card)] bg-[var(--color-dark)] shadow-[var(--shadow-card)]">
        {playing ? (
          <div className="aspect-video">
            <iframe src={`${video.videoUrl}?autoplay=1`} className="h-full w-full" allow="autoplay; encrypted-media" allowFullScreen />
          </div>
        ) : (
          <button onClick={() => setPlaying(true)} className="group relative aspect-video w-full">
            <img src={video.thumbnail} alt={video.author} className="h-full w-full object-cover" />
            <div className="absolute inset-0 bg-[var(--color-dark)]/40 transition-colors group-hover:bg-[var(--color-dark)]/50" />
            <div className="absolute inset-0 flex items-center justify-center">
              <div className="flex h-14 w-14 items-center justify-center rounded-full bg-white/90 shadow-lg transition-transform group-hover:scale-110">
                <svg viewBox="0 0 24 24" className="ml-1 h-6 w-6" fill="var(--color-dark)"><path d="M8 5v14l11-7z" /></svg>
              </div>
            </div>
            <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-[var(--color-dark)]/80 to-transparent p-3">
              <p className="text-xs font-medium text-white">{video.product}</p>
            </div>
          </button>
        )}
      </div>
      <div className="mt-3 px-1">
        <div className="flex items-center gap-2"><Rating value={video.rating} /><span className="text-xs text-[var(--color-text-muted)]">{video.location}</span></div>
        <p className="mt-1 text-sm font-medium text-[var(--color-text-primary)]">{video.author}</p>
        <p className="mt-0.5 text-xs text-[var(--color-text-secondary)] line-clamp-2">{video.comment}</p>
      </div>
    </div>
  );
}

/* ─── Component: Image Gallery Card ─── */
function ImageGalleryCard({ image }: { image: typeof CUSTOMER_IMAGES[0] }) {
  const [expanded, setExpanded] = useState(false);
  return (
    <>
      <button onClick={() => setExpanded(true)} className="group relative flex-shrink-0 overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-card)] transition-shadow hover:shadow-[var(--shadow-hover)]">
        <div className="h-48 w-36 overflow-hidden sm:h-56 sm:w-40">
          <img src={image.src} alt={image.caption} className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
        </div>
        <div className="absolute inset-0 bg-gradient-to-t from-[var(--color-dark)]/70 to-transparent opacity-0 transition-opacity group-hover:opacity-100" />
        <div className="absolute bottom-0 left-0 right-0 p-2.5 opacity-0 transition-opacity group-hover:opacity-100">
          <p className="text-[0.65rem] font-medium text-white">{image.author}</p>
          <p className="text-[0.6rem] text-white/70">{image.caption}</p>
        </div>
      </button>
      {expanded && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-[var(--color-dark)]/80 p-4 backdrop-blur-sm" onClick={() => setExpanded(false)}>
          <div className="animate-scale-in relative max-h-[85vh] max-w-lg" onClick={(e) => e.stopPropagation()}>
            <img src={image.src} alt={image.caption} className="max-h-[75vh] rounded-[var(--radius-card)] object-contain shadow-2xl" />
            <div className="mt-3 text-center">
              <p className="text-sm font-medium text-white">{image.author}</p>
              <p className="text-xs text-white/70">{image.product} — {image.caption}</p>
            </div>
            <button onClick={() => setExpanded(false)} className="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-white text-[var(--color-dark)] shadow-lg">
              <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2"><path d="M6 6l12 12M18 6L6 18" strokeLinecap="round" /></svg>
            </button>
          </div>
        </div>
      )}
    </>
  );
}

/* ─── Component: Big Review Card ─── */
function BigReviewCard({ review }: { review: typeof BIG_REVIEWS[0] }) {
  const [expanded, setExpanded] = useState(false);
  return (
    <Reveal>
      <div className="overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] shadow-[var(--shadow-card)]">
        <div className="flex flex-col sm:flex-row">
          {/* Product image */}
          <div className="flex-shrink-0 sm:w-48">
            <img src={review.productImage} alt={review.product} className="h-40 w-full object-cover sm:h-full" />
          </div>
          {/* Content */}
          <div className="flex flex-1 flex-col p-5 sm:p-6">
            <div className="flex items-center justify-between">
              <Rating value={review.rating} />
              {review.verified && <span className="text-[0.6rem] font-semibold uppercase text-[var(--color-success)]">Verified Purchase</span>}
            </div>
            <h3 className="mt-2 font-display text-lg text-[var(--color-text-primary)]">{review.title}</h3>
            <p className={`mt-2 text-sm leading-relaxed text-[var(--color-text-secondary)] ${!expanded ? 'line-clamp-3' : ''}`}>
              {review.text}
            </p>
            {review.text.length > 200 && (
              <button onClick={() => setExpanded(!expanded)} className="mt-1 text-xs font-medium text-[var(--color-primary)]">
                {expanded ? 'Show less' : 'Read more'}
              </button>
            )}
            {/* Review images */}
            {review.images.length > 0 && (
              <div className="mt-3 flex gap-2">
                {review.images.map((img) => (
                  <img key={img} src={img} alt="" className="h-16 w-16 rounded-[var(--radius-btn)] object-cover" />
                ))}
              </div>
            )}
            <div className="mt-3 flex items-center gap-2 text-xs text-[var(--color-text-muted)]">
              <span className="font-medium text-[var(--color-text-primary)]">{review.author}</span>
              <span>·</span>
              <span>{review.location}</span>
              <span>·</span>
              <span>{review.date}</span>
            </div>
          </div>
        </div>
      </div>
    </Reveal>
  );
}

/* ─── Component: WhatsApp Message ─── */
function WhatsAppMessage({ msg }: { msg: typeof WHATSAPP_MESSAGES[0] }) {
  return (
    <div className="flex-shrink-0 w-72 sm:w-80">
      <div className="flex h-full flex-col rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-4 shadow-sm transition-shadow hover:shadow-[var(--shadow-card)]">
        <div className="mb-3 flex items-center gap-2.5">
          <div className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-[var(--color-primary)] text-xs font-bold text-[var(--color-bg-light)]">
            {msg.author[0]}
          </div>
          <div className="min-w-0">
            <p className="truncate text-xs font-semibold text-[var(--color-text-primary)]">{msg.author}</p>
            <p className="truncate text-[0.6rem] text-[var(--color-text-muted)]">{msg.product}</p>
          </div>
        </div>
        <p className="flex-1 text-sm leading-relaxed text-[var(--color-text-secondary)] line-clamp-4">{msg.text}</p>
        {msg.hasImage && (
          <div className="mt-3 overflow-hidden rounded-[var(--radius-btn)]">
            <img src={`https://picsum.photos/seed/${msg.id}/400/200`} alt="Customer photo" className="h-28 w-full object-cover" />
          </div>
        )}
        <div className="mt-3 flex items-center justify-between">
          <p className="text-[0.6rem] text-[var(--color-text-muted)]">{msg.time}</p>
          <div className="flex items-center gap-1">
            <svg viewBox="0 0 16 16" className="h-3 w-3 text-[var(--color-success)]" fill="currentColor"><path d="M13.5 4.5l-7 7L3 8" stroke="currentColor" strokeWidth="1.5" fill="none" /></svg>
            <span className="text-[0.55rem] text-[var(--color-text-muted)]">Delivered</span>
          </div>
        </div>
      </div>
    </div>
  );
}

/* ═══ MAIN PAGE ═══ */
export default function ReviewsPage() {
  const [reviews, setReviews] = useState<AggregatedReview[] | null>(null);
  const [stats, setStats] = useState<Awaited<ReturnType<typeof reviewsApi.getStats>> | null>(null);
  const [starFilter, setStarFilter] = useState<number | null>(null);
  const [activeTestimonial, setActiveTestimonial] = useState(0);

  useEffect(() => {
    reviewsApi.getAll().then(setReviews);
    reviewsApi.getStats().then(setStats);
  }, []);

  useEffect(() => {
    const t = setInterval(() => setActiveTestimonial((a) => (a + 1) % TESTIMONIALS_VIDEO.length), 5000);
    return () => clearInterval(t);
  }, []);

  const filtered = reviews?.filter((r) => (starFilter ? Math.round(r.rating) === starFilter : true)) ?? [];

  return (
    <div className="pb-12">
      <SEO title="Customer Reviews" description="Read real stories and reviews from customers across India about our handcrafted products." />
      {/* ═══ HERO ═══ */}
      <section className="relative overflow-hidden bg-[var(--color-dark)]">
        <div className="absolute inset-0 opacity-10"><img src="https://picsum.photos/seed/reviews-hero/1800/600" alt="" className="h-full w-full object-cover" /></div>
        <div className="relative z-10 mx-auto max-w-3xl px-4 py-20 text-center sm:py-24">
          <Reveal>
            <p className="mb-3 text-xs font-semibold uppercase tracking-[0.3em] text-[var(--color-secondary)]">Real Stories</p>
            <h1 className="font-display text-3xl text-[var(--color-bg-light)] sm:text-4xl lg:text-5xl">What Our Customers Say</h1>
            <p className="mx-auto mt-4 max-w-lg text-sm text-[var(--color-bg-light)]/70">See how our handcrafted pieces find a place in homes across India.</p>
          </Reveal>
        </div>
      </section>

      {/* ═══ STATS ═══ */}
      <section className="mx-auto max-w-6xl px-4 pt-10 sm:px-6 lg:px-8">
        {stats && (
          <Reveal>
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-3">
              <div className="flex flex-col items-center rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-6 text-center shadow-[var(--shadow-card)]">
                <span className="font-display text-5xl text-[var(--color-primary)]">{stats.avgRating.toFixed(1)}</span>
                <Rating value={stats.avgRating} size="md" />
                <p className="mt-2 text-xs text-[var(--color-text-muted)]">{stats.totalReviews} reviews</p>
              </div>
              <div className="rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-6 shadow-[var(--shadow-card)]">
                <p className="mb-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Rating Breakdown</p>
                {stats.distribution.map((d) => (
                  <button key={d.star} onClick={() => setStarFilter(starFilter === d.star ? null : d.star)} className="mb-1.5 flex w-full items-center gap-2 text-xs">
                    <span className="w-8 text-[var(--color-text-secondary)]">{d.star}★</span>
                    <div className="h-1.5 flex-1 overflow-hidden rounded-full bg-[var(--color-border-soft)]">
                      <div className="h-full rounded-full bg-[var(--color-secondary)] transition-all duration-500" style={{ width: `${stats.totalReviews ? (d.count / stats.totalReviews) * 100 : 0}%` }} />
                    </div>
                    <span className="w-5 text-right text-[var(--color-text-muted)]">{d.count}</span>
                  </button>
                ))}
              </div>
              <div className="flex flex-col gap-3 rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-6 shadow-[var(--shadow-card)]">
                <p className="text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Highlights</p>
                <div className="flex items-center gap-3">
                  <div className="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--color-success)]/10 text-[var(--color-success)]"><svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" strokeLinecap="round" /><path d="M22 4L12 14.01l-3-3" strokeLinecap="round" strokeLinejoin="round" /></svg></div>
                  <div><p className="text-sm font-semibold text-[var(--color-text-primary)]">98% Satisfaction</p><p className="text-[0.65rem] text-[var(--color-text-muted)]">Based on verified purchases</p></div>
                </div>
                <div className="flex items-center gap-3">
                  <div className="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--color-secondary)]/10 text-[var(--color-secondary-dark)]"><svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" /></svg></div>
                  <div><p className="text-sm font-semibold text-[var(--color-text-primary)]">4.8 Average</p><p className="text-[0.65rem] text-[var(--color-text-muted)]">Across all products</p></div>
                </div>
                <div className="flex items-center gap-3">
                  <div className="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--color-primary)]/10 text-[var(--color-primary)]"><svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z" /></svg></div>
                  <div><p className="text-sm font-semibold text-[var(--color-text-primary)]">Fast Replies</p><p className="text-[0.65rem] text-[var(--color-text-muted)]">Within 2 hours on WhatsApp</p></div>
                </div>
              </div>
            </div>
          </Reveal>
        )}
      </section>

      {/* ═══ FEATURED TESTIMONIAL ═══ */}
      <section className="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div className="relative overflow-hidden rounded-[var(--radius-card)] bg-[var(--color-dark)] p-8 text-center sm:p-12">
          <div className="absolute inset-0 opacity-5"><img src="https://picsum.photos/seed/testi-bg/1800/600" alt="" className="h-full w-full object-cover" /></div>
          <div className="relative">
            <svg viewBox="0 0 24 24" className="mx-auto mb-4 h-8 w-8 text-[var(--color-secondary)]" fill="currentColor"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" /></svg>
            <p className="mx-auto max-w-2xl font-serif-accent text-lg italic text-[var(--color-bg-light)]/90 sm:text-xl">{TESTIMONIALS_VIDEO[activeTestimonial].text}</p>
            <div className="mt-6"><Rating value={TESTIMONIALS_VIDEO[activeTestimonial].rating} /><p className="mt-2 text-sm font-medium text-[var(--color-bg-light)]">{TESTIMONIALS_VIDEO[activeTestimonial].author}</p></div>
            <div className="mt-6 flex justify-center gap-2">
              {TESTIMONIALS_VIDEO.map((_, i) => (<button key={i} onClick={() => setActiveTestimonial(i)} className={`h-2 rounded-full transition-all duration-300 ${i === activeTestimonial ? 'w-6 bg-[var(--color-secondary)]' : 'w-2 bg-white/30'}`} />))}
            </div>
          </div>
        </div>
      </section>

      {/* ═══ BIG CLIENT REVIEWS ═══ */}
      <section className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <Reveal className="mb-8"><p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">Detailed Reviews</p><h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">What Customers Really Think</h2></Reveal>
        <div className="flex flex-col gap-6">
          {BIG_REVIEWS.map((r) => <BigReviewCard key={r.id} review={r} />)}
        </div>
      </section>

      {/* ═══ VIDEO REVIEWS ═══ */}
      <section className="bg-[var(--color-bg-cream)] py-10">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="mb-6"><p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">Watch & Decide</p><h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">Video Reviews</h2></Reveal>
          <HorizontalScroller gap={20}>{VIDEO_REVIEWS.map((v) => <VideoCard key={v.id} video={v} />)}</HorizontalScroller>
        </div>
      </section>

      {/* ═══ CUSTOMER IMAGE GALLERY ═══ */}
      <section className="py-10">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="mb-6"><p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">Customer Gallery</p><h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">How You Style It</h2></Reveal>
          <HorizontalScroller gap={12}>{CUSTOMER_IMAGES.map((img) => <ImageGalleryCard key={img.id} image={img} />)}</HorizontalScroller>
        </div>
      </section>

      {/* ═══ WHATSAPP MESSAGES ═══ */}
      <section className="bg-[var(--color-bg-cream)] py-10">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <Reveal className="mb-6"><p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">Real Conversations</p><h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">Messages from Happy Customers</h2></Reveal>
          <HorizontalScroller gap={16}>{WHATSAPP_MESSAGES.map((msg) => <WhatsAppMessage key={msg.id} msg={msg} />)}</HorizontalScroller>
        </div>
      </section>

      {/* ═══ ALL TEXT REVIEWS ═══ */}
      <section className="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
        <Reveal className="mb-8 text-center">
          <p className="mb-2 text-xs font-semibold uppercase tracking-[0.25em] text-[var(--color-secondary-dark)]">All Reviews</p>
          <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">Every Voice Matters</h2>
          <div className="mx-auto mt-3 h-0.5 w-16 bg-gradient-to-r from-transparent via-[var(--color-secondary)] to-transparent" />
        </Reveal>

        {starFilter && (
          <div className="mb-6 text-center">
            <button onClick={() => setStarFilter(null)} className="inline-flex items-center gap-2 rounded-full border border-[var(--color-primary)]/30 bg-[var(--color-primary)]/10 px-4 py-2 text-xs font-medium text-[var(--color-primary)] transition-colors hover:bg-[var(--color-primary)]/20">
              <span>Filtered: {starFilter} star{starFilter > 1 ? 's' : ''}</span>
              <svg viewBox="0 0 16 16" className="h-3 w-3" fill="none" stroke="currentColor" strokeWidth="2"><path d="M4 4l8 8M12 4l-8 8" strokeLinecap="round" /></svg>
            </button>
          </div>
        )}

        {reviews === null ? (
          <div className="py-12 text-center">
            <div className="mx-auto h-8 w-8 animate-spin rounded-full border-2 border-[var(--color-border)] border-t-[var(--color-primary)]" />
            <p className="mt-4 text-sm text-[var(--color-text-muted)]">Loading reviews...</p>
          </div>
        ) : filtered.length === 0 ? (
          <div className="py-12 text-center">
            <svg viewBox="0 0 24 24" className="mx-auto h-12 w-12 text-[var(--color-text-muted)]" fill="none" stroke="currentColor" strokeWidth="1">
              <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
            </svg>
            <p className="mt-4 text-sm text-[var(--color-text-muted)]">No reviews match this filter.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            {filtered.map((r, i) => (
              <Reveal key={r.id} delay={Math.min(i * 50, 300)}>
                <div className="group relative h-full rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-5 transition-all duration-300 hover:border-[var(--color-secondary)]/30 hover:shadow-[var(--shadow-hover)]">
                  {/* Top row: product + rating */}
                  <div className="flex items-start gap-3">
                    <Link to={buildRoute(ROUTES.product, { productSlug: r.product.slug })} className="flex-shrink-0">
                      <img src={r.product.thumbnail} alt={r.product.name} className="h-12 w-12 rounded-[var(--radius-btn)] object-cover shadow-sm transition-shadow group-hover:shadow-md" />
                    </Link>
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2">
                        <Rating value={r.rating} />
                        {r.verified && (
                          <span className="inline-flex items-center gap-0.5 rounded-full bg-[var(--color-success)]/10 px-1.5 py-0.5 text-[0.55rem] font-semibold uppercase text-[var(--color-success)]">
                            <svg viewBox="0 0 12 12" className="h-2.5 w-2.5" fill="none" stroke="currentColor" strokeWidth="2"><path d="M10 3L4.5 8.5 2 6" strokeLinecap="round" strokeLinejoin="round" /></svg>
                            Verified
                          </span>
                        )}
                      </div>
                      <p className="mt-1 truncate text-[0.65rem] text-[var(--color-text-muted)]">
                        on <Link to={buildRoute(ROUTES.product, { productSlug: r.product.slug })} className="font-medium text-[var(--color-primary)] transition-colors hover:text-[var(--color-primary-dark)]">{r.product.name}</Link>
                      </p>
                    </div>
                  </div>

                  {/* Review content */}
                  <h3 className="mt-3 font-display text-sm font-semibold text-[var(--color-text-primary)]">{r.title}</h3>
                  <p className="mt-1.5 text-sm leading-relaxed text-[var(--color-text-secondary)]">{r.comment}</p>

                  {/* Footer */}
                  <div className="mt-4 flex items-center justify-between border-t border-[var(--color-border)]/50 pt-3">
                    <div className="flex items-center gap-2">
                      <div className="flex h-6 w-6 items-center justify-center rounded-full bg-[var(--color-primary)]/10 text-[0.6rem] font-bold text-[var(--color-primary)]">
                        {r.author[0]}
                      </div>
                      <span className="text-xs font-medium text-[var(--color-text-primary)]">{r.author}</span>
                    </div>
                    <span className="text-[0.65rem] text-[var(--color-text-muted)]">{r.date}</span>
                  </div>
                </div>
              </Reveal>
            ))}
          </div>
        )}
      </section>

      {/* ═══ CTA ═══ */}
      <section className="mx-auto max-w-3xl px-4 py-12 text-center sm:px-6 lg:px-8">
        <Reveal>
          <h2 className="font-display text-2xl text-[var(--color-text-primary)] sm:text-3xl">Share Your Experience</h2>
          <p className="mx-auto mt-3 max-w-md text-sm text-[var(--color-text-secondary)]">Bought something from us? We'd love to hear about it — and see how you've styled it in your home.</p>
          <div className="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <Link to={ROUTES.shop} className="rounded-[var(--radius-btn)] bg-[var(--color-primary)] px-6 py-3 text-sm font-semibold text-[var(--color-bg-light)] transition-colors hover:bg-[var(--color-primary-dark)]">Shop Now</Link>
            <a href="https://wa.me/917887699208" target="_blank" rel="noreferrer" className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-6 py-3 text-sm font-medium text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-bg-cream)]">Send us a message</a>
          </div>
        </Reveal>
      </section>
    </div>
  );
}
