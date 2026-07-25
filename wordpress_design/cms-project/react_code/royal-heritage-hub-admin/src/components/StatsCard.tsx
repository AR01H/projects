interface StatsCardProps {
  title: string;
  value: string | number;
  change?: string;
  changeType?: 'up' | 'down' | 'neutral';
  icon: React.ReactNode;
}

export function StatsCard({ title, value, change, changeType = 'neutral', icon }: StatsCardProps) {
  return (
    <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-xs font-medium uppercase tracking-wider text-gray-500">{title}</p>
          <p className="mt-1 text-2xl font-bold text-gray-800">{value}</p>
          {change && (
            <p className={cn('mt-1 text-xs font-medium', changeType === 'up' && 'text-green-600', changeType === 'down' && 'text-red-600', changeType === 'neutral' && 'text-gray-500')}>
              {change}
            </p>
          )}
        </div>
        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
          {icon}
        </div>
      </div>
    </div>
  );
}

function cn(...classes: (string | boolean | undefined | null)[]) { return classes.filter(Boolean).join(' '); }
