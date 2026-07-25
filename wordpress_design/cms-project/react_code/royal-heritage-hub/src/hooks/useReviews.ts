import { useState, useEffect } from 'react';
import { reviewsApi, type AggregatedReview } from '@/api/reviews';

interface ReviewStats {
  totalReviews: number;
  avgRating: number;
  distribution: { star: number; count: number }[];
}

export function useReviews() {
  const [data, setData] = useState<AggregatedReview[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    reviewsApi.getAll().then(setData).finally(() => setLoading(false));
  }, []);

  return { data, loading };
}

export function useReviewStats() {
  const [stats, setStats] = useState<ReviewStats | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    reviewsApi.getStats().then(setStats).finally(() => setLoading(false));
  }, []);

  return { stats, loading };
}
