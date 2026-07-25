import { useEffect, useState } from 'react';
import { reviewsApi } from '@/api/reviews';
import type { Review } from '@/types';

function Stars({ rating }: { rating: number }) {
  return <span className="text-yellow-500">{'★'.repeat(rating)}{'☆'.repeat(5 - rating)}</span>;
}

export default function ReviewsPage() {
  const [reviews, setReviews] = useState<Review[]>([]);
  const [stats, setStats] = useState<{ totalReviews: number; avgRating: number; distribution: { star: number; count: number }[] } | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => { load(); }, []);

  async function load() {
    setLoading(true);
    const [reviewsRes, statsRes] = await Promise.all([reviewsApi.getAll(), reviewsApi.getStats()]);
    if (reviewsRes.data) setReviews(reviewsRes.data);
    if (statsRes.data) setStats(statsRes.data);
    setLoading(false);
  }

  async function handleDelete(id: string) {
    if (!confirm('Delete this review?')) return;
    await reviewsApi.delete(id);
    load();
  }

  return (
    <div>
      <h2 className="mb-6 text-xl font-semibold">Reviews</h2>

      {loading ? <p className="text-gray-500">Loading...</p> : (
        <>
          {stats && (
            <div className="mb-6 grid gap-4 sm:grid-cols-3">
              <div className="rounded-lg border bg-white p-4 text-center shadow-sm">
                <p className="text-3xl font-bold">{stats.totalReviews}</p>
                <p className="text-xs text-gray-500">Total Reviews</p>
              </div>
              <div className="rounded-lg border bg-white p-4 text-center shadow-sm">
                <p className="text-3xl font-bold">{stats.avgRating.toFixed(1)}</p>
                <p className="text-xs text-gray-500">Average Rating</p>
              </div>
              <div className="rounded-lg border bg-white p-4 shadow-sm">
                {stats.distribution.map((d) => (
                  <div key={d.star} className="flex items-center gap-2 text-xs">
                    <span className="w-8">{d.star}★</span>
                    <div className="h-2 flex-1 rounded-full bg-gray-200">
                      <div className="h-2 rounded-full bg-yellow-400" style={{ width: `${(d.count / (stats.totalReviews || 1)) * 100}%` }} />
                    </div>
                    <span className="w-6 text-right text-gray-500">{d.count}</span>
                  </div>
                ))}
              </div>
            </div>
          )}

          <div className="overflow-x-auto rounded-lg border bg-white">
            <table className="w-full text-sm">
              <thead className="border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                <tr><th className="px-4 py-3">Product</th><th className="px-4 py-3">Author</th><th className="px-4 py-3">Rating</th><th className="px-4 py-3">Title</th><th className="px-4 py-3">Date</th><th className="px-4 py-3">Actions</th></tr>
              </thead>
              <tbody className="divide-y">
                {reviews.map((r) => (
                  <tr key={r.id} className="hover:bg-gray-50">
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-2">
                        <img src={r.productThumbnail} alt="" className="h-8 w-8 rounded object-cover" />
                        <span className="max-w-[150px] truncate text-xs">{r.productName}</span>
                      </div>
                    </td>
                    <td className="px-4 py-3">{r.author}</td>
                    <td className="px-4 py-3"><Stars rating={r.rating} /></td>
                    <td className="px-4 py-3 max-w-[200px] truncate">{r.title}</td>
                    <td className="px-4 py-3 text-gray-500">{r.date}</td>
                    <td className="px-4 py-3">
                      <button onClick={() => handleDelete(r.id)} className="text-red-600 hover:underline">Delete</button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </>
      )}
    </div>
  );
}
