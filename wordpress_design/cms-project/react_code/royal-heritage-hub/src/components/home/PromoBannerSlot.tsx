import { useEffect, useState } from 'react';
import { MiniPromoBanner } from './MiniPromoBanner';
import { bannersApi } from '@/api/banners';
import type { Banner } from '@/types/product';

export function PromoBannerSlot({ index = 0 }: { index?: number }) {
  const [banners, setBanners] = useState<Banner[]>([]);

  useEffect(() => {
    bannersApi.getPromo().then(setBanners);
  }, []);

  const banner = banners[index];
  if (!banner) return null;

  return <MiniPromoBanner banner={banner} />;
}
