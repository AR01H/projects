import { useEffect, useState } from 'react';
import { ordersApi } from '@/api/orders';
import { productsApi } from '@/api/products';
import { StatsCard } from '@/components/StatsCard';
import { Table } from '@/components/Table';
import { Badge } from '@/components/Badge';
import { cn, formatCurrency, formatDate } from '@/utils/format';
import type { Product, Order, DashboardStats } from '@/types';

export default function DashboardPage() {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [products, setProducts] = useState<Product[]>([]);
  const [orders, setOrders] = useState<Order[]>([]);

  useEffect(() => {
    ordersApi.getStats().then(({ data }) => setStats(data as DashboardStats | null));
    productsApi.getAll().then(({ data }) => setProducts(data || []));
    ordersApi.getAll().then(({ data }) => setOrders(data || []));
  }, []);

  const statusColors: Record<string, string> = {
    placed: 'warning', confirmed: 'info', processing: 'primary', shipped: 'info', delivered: 'success', cancelled: 'danger',
  };

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatsCard title="Total Products" value={products.length} change="+2 this week" changeType="up" icon={<svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.5"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>} />
        <StatsCard title="Total Orders" value={stats?.totalOrders || 0} change={`${stats?.recentOrders?.length || 0} recent`} changeType="neutral" icon={<svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>} />
        <StatsCard title="Total Customers" value={stats?.totalCustomers || 0} change="+3 this month" changeType="up" icon={<svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.5"><path d="M12 4.354a4 4 0 110 7.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>} />
        <StatsCard title="Revenue" value={formatCurrency(stats?.totalRevenue || 0)} change="+12% from last month" changeType="up" icon={<svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.5"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>} />
      </div>

      {/* Revenue Chart */}
      <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 className="mb-4 text-sm font-semibold text-gray-700">Revenue by Month</h3>
        <div className="flex items-end gap-3 h-48">
          {stats?.revenueByMonth?.map((m) => {
            const maxRev = Math.max(...(stats?.revenueByMonth?.map((x) => x.revenue) || [1]));
            const height = maxRev > 0 ? (m.revenue / maxRev) * 100 : 0;
            return (
              <div key={m.month} className="flex flex-1 flex-col items-center gap-1">
                <span className="text-[0.65rem] text-gray-500">{formatCurrency(m.revenue)}</span>
                <div className="w-full rounded-t bg-indigo-500 transition-all" style={{ height: `${height}%` }} />
                <span className="text-[0.65rem] text-gray-500">{m.month}</span>
              </div>
            );
          })}
        </div>
      </div>

      {/* Recent Orders */}
      <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 className="mb-4 text-sm font-semibold text-gray-700">Recent Orders</h3>
        <Table
          columns={[
            { key: 'id', label: 'Order ID', render: (o: Order) => <span className="font-mono text-xs">{o.id}</span> },
            { key: 'total', label: 'Total', render: (o: Order) => formatCurrency(o.total) },
            { key: 'status', label: 'Status', render: (o: Order) => <Badge variant={statusColors[o.status] || 'neutral'}>{o.status}</Badge> },
            { key: 'createdAt', label: 'Date', render: (o: Order) => formatDate(o.createdAt) },
          ]}
          data={orders.slice(0, 5)}
        />
      </div>

      {/* Low Stock Alert */}
      <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 className="mb-4 text-sm font-semibold text-gray-700">Low Stock Alert</h3>
        <Table
          columns={[
            { key: 'name', label: 'Product', render: (p: Product) => <span className="font-medium">{p.name}</span> },
            { key: 'stock', label: 'Stock', render: (p: Product) => <span className={cn('font-semibold', p.stock <= 5 ? 'text-red-600' : 'text-yellow-600')}>{p.stock}</span> },
            { key: 'lowStockThreshold', label: 'Threshold' },
          ]}
          data={products.filter((p) => p.stock <= p.lowStockThreshold).slice(0, 5)}
          emptyMessage="All products well stocked"
        />
      </div>
    </div>
  );
}
