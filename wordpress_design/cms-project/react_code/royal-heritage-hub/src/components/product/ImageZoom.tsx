import { useState, useRef } from 'react';

interface ImageZoomProps {
  src: string;
  alt: string;
  className?: string;
}

export function ImageZoom({ src, alt, className = '' }: ImageZoomProps) {
  const [zooming, setZooming] = useState(false);
  const [position, setPosition] = useState({ x: 50, y: 50 });
  const containerRef = useRef<HTMLDivElement>(null);

  function handleMouseMove(e: React.MouseEvent<HTMLDivElement>) {
    if (!containerRef.current) return;
    const rect = containerRef.current.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    setPosition({ x, y });
  }

  return (
    <div
      ref={containerRef}
      className={`group relative overflow-hidden ${className}`}
      onMouseEnter={() => setZooming(true)}
      onMouseLeave={() => setZooming(false)}
      onMouseMove={handleMouseMove}
    >
      <img
        src={src}
        alt={alt}
        className="h-full w-full object-cover transition-transform duration-300 ease-out"
        style={{
          transform: zooming ? 'scale(2)' : 'scale(1)',
          transformOrigin: `${position.x}% ${position.y}%`,
        }}
      />
      {/* Zoom hint */}
      {!zooming && (
        <div className="absolute bottom-3 right-3 flex items-center gap-1.5 rounded-full bg-[var(--color-dark)]/60 px-2.5 py-1 text-[0.6rem] text-[var(--color-bg-light)] opacity-0 transition-opacity group-hover:opacity-100">
          <svg viewBox="0 0 24 24" className="h-3 w-3" fill="none" stroke="currentColor" strokeWidth="2">
            <circle cx="11" cy="11" r="7" />
            <path d="M21 21l-4.3-4.3" strokeLinecap="round" />
          </svg>
          Hover to zoom
        </div>
      )}
    </div>
  );
}
