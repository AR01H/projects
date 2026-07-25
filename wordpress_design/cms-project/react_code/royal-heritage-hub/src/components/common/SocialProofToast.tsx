import { useState, useEffect, useRef } from 'react';
import { productsApi } from '@/api/products';

const CITIES = [
  'Mumbai', 'Delhi', 'Bengaluru', 'Hyderabad', 'Chennai',
  'Pune', 'Kolkata', 'Ahmedabad', 'Jaipur', 'Lucknow',
  'Kochi', 'Chandigarh', 'Indore', 'Bhopal', 'Mysuru',
];

const FIRST_NAMES = [
  'Priya', 'Rahul', 'Ananya', 'Vikram', 'Deepa',
  'Arjun', 'Meera', 'Karan', 'Nisha', 'Rohan',
  'Shreya', 'Aditya', 'Pooja', 'Sanjay', 'Divya',
];

function randomFrom<T>(arr: T[]): T {
  return arr[Math.floor(Math.random() * arr.length)];
}

export function SocialProofToast() {
  const [visible, setVisible] = useState(false);
  const [product, setProduct] = useState<{ name: string; thumbnail: string } | null>(null);
  const [buyer, setBuyer] = useState({ name: '', city: '' });
  const timeoutRef = useRef<ReturnType<typeof setTimeout>>(undefined);
  const intervalRef = useRef<ReturnType<typeof setInterval>>(undefined);

  function showNotification() {
    const name = randomFrom(FIRST_NAMES);
    const city = randomFrom(CITIES);

    productsApi.getBestSellers(1).then(([p]) => {
      if (p) {
        setProduct({ name: p.name, thumbnail: p.thumbnail });
        setBuyer({ name, city });
        setVisible(true);
        setTimeout(() => setVisible(false), 4000);
      }
    });
  }

  useEffect(() => {
    timeoutRef.current = setTimeout(showNotification, 15000 + Math.random() * 15000);
    intervalRef.current = setInterval(showNotification, 45000 + Math.random() * 45000);

    return () => {
      clearTimeout(timeoutRef.current);
      clearInterval(intervalRef.current);
    };
  }, []);

  if (!visible || !product) return null;

  return (
    <div className="animate-slide-in-right fixed bottom-20 left-4 z-50 flex max-w-xs items-center gap-3 rounded-[var(--radius-card)] border border-[var(--color-border)] bg-[var(--color-bg-light)] p-3 shadow-[var(--shadow-hover)] lg:bottom-6">
      <img
        src={product.thumbnail}
        alt={product.name}
        className="h-12 w-12 flex-shrink-0 rounded-[var(--radius-card)] object-cover"
      />
      <div className="min-w-0">
        <p className="truncate text-xs font-medium text-[var(--color-text-primary)]">
          {buyer.name} from {buyer.city}
        </p>
        <p className="truncate text-[0.65rem] text-[var(--color-text-muted)]">
          just purchased {product.name}
        </p>
      </div>
    </div>
  );
}
