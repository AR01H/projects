interface StaticPageProps {
  title: string;
  children: React.ReactNode;
}

export function StaticPage({ title, children }: StaticPageProps) {
  return (
    <div className="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">
      <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">{title}</h1>
      <div className="prose prose-sm mt-8 flex flex-col gap-4 text-sm leading-relaxed text-[var(--color-text-secondary)]">
        {children}
      </div>
    </div>
  );
}
