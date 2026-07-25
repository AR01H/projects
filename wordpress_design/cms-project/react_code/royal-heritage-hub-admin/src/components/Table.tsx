import { cn } from '@/utils/format';

interface Column<T> {
  key: string;
  label: string;
  render?: (item: T) => React.ReactNode;
  className?: string;
}

interface TableProps<T> {
  columns: Column<T>[];
  data: T[];
  onRowClick?: (item: T) => void;
  emptyMessage?: string;
}

export function Table<T extends { id: string }>({ columns, data, onRowClick, emptyMessage = 'No data found' }: TableProps<T>) {
  if (data.length === 0) {
    return (
      <div className="rounded-lg border border-gray-200 bg-white p-12 text-center text-sm text-gray-500">
        {emptyMessage}
      </div>
    );
  }

  return (
    <div className="overflow-hidden rounded-lg border border-gray-200 bg-white">
      <table className="w-full text-sm">
        <thead className="bg-gray-50 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
          <tr>
            {columns.map((col) => (
              <th key={col.key} className={cn('px-4 py-3', col.className)}>{col.label}</th>
            ))}
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-100">
          {data.map((item) => (
            <tr
              key={item.id}
              onClick={() => onRowClick?.(item)}
              className={cn('transition-colors hover:bg-gray-50', onRowClick && 'cursor-pointer')}
            >
              {columns.map((col) => (
                <td key={col.key} className={cn('px-4 py-3 text-gray-700', col.className)}>
                  {col.render ? col.render(item) : (item as any)[col.key]}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
