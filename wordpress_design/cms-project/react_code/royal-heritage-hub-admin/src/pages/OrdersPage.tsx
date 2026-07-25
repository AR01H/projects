import { useEffect, useState } from 'react';
import { ordersApi } from '@/api/orders';
import { Table } from '@/components/Table';
import { Modal } from '@/components/Modal';
import { Badge } from '@/components/Badge';
import { formatCurrency, formatDate } from '@/utils/format';
import type { Order } from '@/types';

const STATUS_OPTIONS: Order['status'][] = ['placed', 'confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'];

export default function OrdersPage() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [filter, setFilter] = useState<string>('all');
  const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);

  useEffect(() => { ordersApi.getAll().then(({ data }) => setOrders(data || [])); }, []);

  const filtered = filter === 'all' ? orders : orders.filter((o) => o.status === filter);

  async function updateStatus(orderId: string, status: Order['status']) {
    await ordersApi.updateStatus(orderId, status);
    setOrders((prev) => prev.map((o) => o.id === orderId ? { ...o, status } : o));
    setSelectedOrder(null);
  }

  const statusColors: Record<string, string> = { placed: 'warning', confirmed: 'info', processing: 'primary', shipped: 'info', delivered: 'success', cancelled: 'danger' };

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        {['all', ...STATUS_OPTIONS].map((s) => (
          <button key={s} onClick={() => setFilter(s)} className={cn('rounded-lg px-3 py-1.5 text-xs font-medium transition-colors', filter === s ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200')}>
            {s === 'all' ? 'All' : s.replace('_', ' ')}
          </button>
        ))}
      </div>

      <Table
        columns={[
          { key: 'id', label: 'Order ID', render: (o: Order) => <span className="font-mono text-xs">{o.id}</span> },
          { key: 'total', label: 'Total', render: (o: Order) => formatCurrency(o.total) },
          { key: 'items', label: 'Items', render: (o: Order) => `${o.items.length} items` },
          { key: 'paymentMethod', label: 'Payment', render: (o: Order) => <Badge variant="neutral">{o.paymentMethod.toUpperCase()}</Badge> },
          { key: 'status', label: 'Status', render: (o: Order) => <Badge variant={statusColors[o.status]}>{o.status.replace('_', ' ')}</Badge> },
          { key: 'createdAt', label: 'Date', render: (o: Order) => formatDate(o.createdAt) },
          { key: 'actions', label: '', render: (o: Order) => <button onClick={() => setSelectedOrder(o)} className="text-xs text-indigo-600 hover:underline">View</button> },
        ]}
        data={filtered}
        onRowClick={(o) => setSelectedOrder(o)}
      />

      {/* Order Detail Modal */}
      <Modal isOpen={!!selectedOrder} onClose={() => setSelectedOrder(null)} title={`Order ${selectedOrder?.id}`} maxWidth="max-w-2xl">
        {selectedOrder && (
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-4 text-sm">
              <div><p className="text-gray-500">Customer</p><p className="font-medium">{selectedOrder.address.name}</p><p className="text-gray-500">{selectedOrder.address.email}</p></div>
              <div><p className="text-gray-500">Payment</p><p className="font-medium">{selectedOrder.paymentMethod.toUpperCase()}</p>{selectedOrder.couponCode && <p className="text-xs text-green-600">Coupon: {selectedOrder.couponCode}</p>}</div>
            </div>
            <div className="text-sm"><p className="text-gray-500">Address</p><p>{selectedOrder.address.line1}, {selectedOrder.address.city}, {selectedOrder.address.state} - {selectedOrder.address.pincode}</p></div>
            <div className="border-t pt-4 text-sm">
              <p className="font-medium text-gray-700">Items</p>
              {selectedOrder.items.map((item, i) => (
                <div key={i} className="flex items-center gap-3 py-2">
                  <img src={item.product.thumbnail} alt="" className="h-10 w-10 rounded object-cover" />
                  <div className="flex-1"><p>{item.product.name}</p><p className="text-xs text-gray-500">Qty: {item.quantity}</p></div>
                  <p className="font-medium">{formatCurrency(item.product.price * item.quantity)}</p>
                </div>
              ))}
            </div>
            <div className="border-t pt-4 text-sm"><p>Total: <span className="font-bold">{formatCurrency(selectedOrder.total)}</span></p></div>
            <div className="border-t pt-4">
              <p className="mb-2 text-xs font-medium text-gray-500">Update Status</p>
              <div className="flex flex-wrap gap-2">
                {STATUS_OPTIONS.map((s) => (
                  <button key={s} onClick={() => updateStatus(selectedOrder.id, s)} className={cn('rounded-lg px-3 py-1.5 text-xs font-medium transition-colors', selectedOrder.status === s ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200')}>
                    {s.replace('_', ' ')}
                  </button>
                ))}
              </div>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
}

function cn(...classes: (string | boolean | undefined | null)[]) { return classes.filter(Boolean).join(' '); }
