import { useState, useCallback, createContext, useContext, useRef } from 'react';
import { cn } from '@/utils/cn';

interface Toast {
  id: number;
  message: string;
  type: 'success' | 'info' | 'error';
}

interface ToastContextValue {
  addToast: (message: string, type?: Toast['type']) => void;
}

const ToastContext = createContext<ToastContextValue | null>(null);

export function useToast() {
  const ctx = useContext(ToastContext);
  if (!ctx) throw new Error('useToast must be used within ToastProvider');
  return ctx;
}

export function ToastProvider({ children }: { children: React.ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);
  const nextId = useRef(0);

  const addToast = useCallback((message: string, type: Toast['type'] = 'info') => {
    const id = nextId.current++;
    setToasts((prev) => [...prev, { id, message, type }]);
    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 3500);
  }, []);

  return (
    <ToastContext.Provider value={{ addToast }}>
      {children}
      {/* Toast container */}
      <div className="fixed bottom-20 right-4 z-[60] flex flex-col gap-2 lg:bottom-6">
        {toasts.map((toast) => (
          <div
            key={toast.id}
            className={cn(
              'animate-slide-in-right flex items-center gap-3 rounded-[var(--radius-card)] px-4 py-3 text-sm shadow-[var(--shadow-hover)] backdrop-blur-xl',
              toast.type === 'success' && 'border border-[var(--color-success)]/30 bg-[var(--color-success)]/10 text-[var(--color-success)]',
              toast.type === 'error' && 'border border-[var(--color-danger)]/30 bg-[var(--color-danger)]/10 text-[var(--color-danger)]',
              toast.type === 'info' && 'border border-[var(--color-primary)]/30 bg-[var(--color-primary)]/10 text-[var(--color-primary)]'
            )}
          >
            {toast.type === 'success' && (
              <svg viewBox="0 0 24 24" className="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M20 6L9 17l-5-5" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
            )}
            {toast.type === 'error' && (
              <svg viewBox="0 0 24 24" className="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" strokeWidth="2">
                <circle cx="12" cy="12" r="10" />
                <path d="M15 9l-6 6M9 9l6 6" strokeLinecap="round" />
              </svg>
            )}
            {toast.type === 'info' && (
              <svg viewBox="0 0 24 24" className="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" strokeWidth="2">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 16v-4M12 8h.01" strokeLinecap="round" />
              </svg>
            )}
            <span>{toast.message}</span>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
}
