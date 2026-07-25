import { useEffect, useState } from 'react';
import { bannersApi } from '@/api/banners';
import { Table } from '@/components/Table';
import { Modal } from '@/components/Modal';
import { Badge } from '@/components/Badge';
import type { Banner } from '@/types';

export default function BannersPage() {
  const [banners, setBanners] = useState<Banner[]>([]);
  const [showAdd, setShowAdd] = useState(false);

  useEffect(() => { bannersApi.getAll().then(({ data }) => setBanners(data || [])); }, []);

  async function handleDelete(id: string) {
    if (!confirm('Delete this banner?')) return;
    await bannersApi.delete(id);
    setBanners((b) => b.filter((x) => x.id !== id));
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold">{banners.length} Banners</h2>
        <button onClick={() => setShowAdd(true)} className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Add Banner</button>
      </div>
      <Table
        columns={[
          { key: 'title', label: 'Title', render: (b: Banner) => <span className="font-medium">{b.title}</span> },
          { key: 'subtitle', label: 'Subtitle' },
          { key: 'theme', label: 'Theme', render: (b: Banner) => <Badge variant={b.theme === 'dark' ? 'neutral' : 'info'}>{b.theme}</Badge> },
          { key: 'ctaLabel', label: 'CTA' },
          { key: 'actions', label: '', render: (b: Banner) => <button onClick={() => handleDelete(b.id)} className="text-xs text-red-600 hover:underline">Delete</button> },
        ]}
        data={banners}
      />

      <Modal isOpen={showAdd} onClose={() => setShowAdd(false)} title="Add Banner">
        <BannerForm onSave={async (data) => {
          await bannersApi.create(data);
          bannersApi.getAll().then(({ data }) => setBanners(data || []));
          setShowAdd(false);
        }} onCancel={() => setShowAdd(false)} />
      </Modal>
    </div>
  );
}

function BannerForm({ onSave, onCancel }: { onSave: (data: Partial<Banner>) => void; onCancel: () => void }) {
  const [form, setForm] = useState({ title: '', subtitle: '', ctaLabel: 'Shop Now', ctaLink: '/shop', image: '', theme: 'dark' as 'light' | 'dark' });
  return (
    <form onSubmit={(e) => { e.preventDefault(); onSave(form); }} className="flex flex-col gap-4">
      <div className="grid grid-cols-2 gap-4">
        <div className="col-span-2"><label className="mb-1 block text-xs font-medium text-gray-600">Title</label><input required value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
        <div className="col-span-2"><label className="mb-1 block text-xs font-medium text-gray-600">Subtitle</label><input value={form.subtitle} onChange={(e) => setForm({ ...form, subtitle: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
        <div><label className="mb-1 block text-xs font-medium text-gray-600">CTA Label</label><input value={form.ctaLabel} onChange={(e) => setForm({ ...form, ctaLabel: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
        <div><label className="mb-1 block text-xs font-medium text-gray-600">CTA Link</label><input value={form.ctaLink} onChange={(e) => setForm({ ...form, ctaLink: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
        <div><label className="mb-1 block text-xs font-medium text-gray-600">Image URL</label><input value={form.image} onChange={(e) => setForm({ ...form, image: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
        <div><label className="mb-1 block text-xs font-medium text-gray-600">Theme</label><select value={form.theme} onChange={(e) => setForm({ ...form, theme: e.target.value as any })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none"><option value="dark">Dark</option><option value="light">Light</option></select></div>
      </div>
      <div className="flex justify-end gap-2">
        <button type="button" onClick={onCancel} className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Create</button>
      </div>
    </form>
  );
}
