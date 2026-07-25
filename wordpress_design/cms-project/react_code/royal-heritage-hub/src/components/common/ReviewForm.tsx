import { useState } from 'react';
import { Button } from './Button';

interface ReviewFormProps {
  onSubmitted: () => void;
}

export function ReviewForm({ onSubmitted }: ReviewFormProps) {
  const [rating, setRating] = useState(0);
  const [hoveredStar, setHoveredStar] = useState(0);
  const [title, setTitle] = useState('');
  const [comment, setComment] = useState('');
  const [author, setAuthor] = useState('');
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState('');

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (rating === 0) {
      setError('Please select a rating');
      return;
    }
    if (!title.trim() || !comment.trim() || !author.trim()) {
      setError('Please fill in all fields');
      return;
    }

    // Mock submission — in production this would POST to an API
    setSubmitted(true);
    setTimeout(() => onSubmitted(), 500);
  }

  if (submitted) {
    return (
      <div className="rounded-[var(--radius-card)] border border-[var(--color-success)]/30 bg-[var(--color-success)]/5 p-5 text-center">
        <svg viewBox="0 0 24 24" className="mx-auto h-8 w-8" fill="none" stroke="var(--color-success)" strokeWidth="2">
          <path d="M20 6L9 17l-5-5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
        <p className="mt-2 font-display text-sm text-[var(--color-text-primary)]">Thank you for your review!</p>
        <p className="mt-1 text-xs text-[var(--color-text-muted)]">It will appear after moderation.</p>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="rounded-[var(--radius-card)] border border-[var(--color-border)] p-5">
      <h3 className="font-display text-base text-[var(--color-text-primary)]">Write a Review</h3>

      {/* Star rating */}
      <div className="mt-3">
        <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-[var(--color-text-muted)]">Rating</p>
        <div className="flex gap-1">
          {[1, 2, 3, 4, 5].map((star) => (
            <button
              key={star}
              type="button"
              onClick={() => setRating(star)}
              onMouseEnter={() => setHoveredStar(star)}
              onMouseLeave={() => setHoveredStar(0)}
              className="transition-transform hover:scale-110"
            >
              <svg viewBox="0 0 24 24" className="h-7 w-7" fill={(hoveredStar || rating) >= star ? 'var(--color-primary)' : 'none'} stroke="var(--color-primary)" strokeWidth="1.5">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
              </svg>
            </button>
          ))}
        </div>
      </div>

      <div className="mt-4 flex flex-col gap-3">
        <input
          value={author}
          onChange={(e) => setAuthor(e.target.value)}
          placeholder="Your name"
          className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-2.5 text-sm outline-none transition-colors focus:border-[var(--color-primary)]"
        />
        <input
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          placeholder="Review title"
          className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-2.5 text-sm outline-none transition-colors focus:border-[var(--color-primary)]"
        />
        <textarea
          value={comment}
          onChange={(e) => setComment(e.target.value)}
          placeholder="Share your experience with this product..."
          rows={3}
          className="rounded-[var(--radius-btn)] border border-[var(--color-border)] px-4 py-2.5 text-sm outline-none transition-colors focus:border-[var(--color-primary)]"
        />
      </div>

      {error && <p className="mt-2 text-xs text-[var(--color-danger)]">{error}</p>}

      <Button type="submit" variant="primary" size="sm" className="mt-4">
        Submit Review
      </Button>
    </form>
  );
}
