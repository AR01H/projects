import { useEffect, useState } from 'react';
import { customersApi } from '@/api/customers';
import { Table } from '@/components/Table';
import { formatCurrency, formatDate } from '@/utils/format';
import type { Customer } from '@/types';

export default function CustomersPage() {
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [search, setSearch] = useState('');

  useEffect(() => { customersApi.getAll().then(({ data }) => setCustomers(data || [])); }, []);

  const filtered = customers.filter((c) => c.name.toLowerCase().includes(search.toLowerCase()) || c.email.toLowerCase().includes(search.toLowerCase()));

  return (
    <div className="space-y-4">
      <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search customers..." className="w-72 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" />
      <Table
        columns={[
          { key: 'name', label: 'Customer', render: (c: Customer) => <div><p className="font-medium">{c.name}</p><p className="text-xs text-gray-500">{c.email}</p></div> },
          { key: 'phone', label: 'Phone', render: (c: Customer) => c.phone || '—' },
          { key: 'ordersCount', label: 'Orders', render: (c: Customer) => c.ordersCount },
          { key: 'totalSpent', label: 'Total Spent', render: (c: Customer) => formatCurrency(c.totalSpent) },
          { key: 'lastOrderAt', label: 'Last Order', render: (c: Customer) => c.lastOrderAt ? formatDate(c.lastOrderAt) : '—' },
          { key: 'createdAt', label: 'Joined', render: (c: Customer) => formatDate(c.createdAt) },
        ]}
        data={filtered}
      />
    </div>
  );
}
