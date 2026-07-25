import { useEffect, useState } from 'react';
import { collectionsApi } from '@/api/collections';
import type { Collection } from '@/types';

export default function CollectionsPage() {
  const [collections, setCollections] = useState<Collection[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editItem, setEditItem] = useState<Collection | null>(null);
  const [form, setForm] = useState({ name: '', slug: '', description: '', image: '', productIds: '' as string });

  useEffect(() => { load(); }, []);

  async function load() {
    setLoading(true);
    const { data } = await collectionsApi.getAll();
    if (data) setCollections(data);
    setLoading(false);
  }

  function openCreate() { setEditItem(null); setForm({ name: '', slug: '', description: '', image: '', productIds: '' }); setShowModal(true); }
  function openEdit(c: Collection) { setEditItem(c); setForm({ name: c.name, slug: c.slug, description: c.description, image: c.image, productIds: c.productIds.join(', ') }); setShowModal(true); }

  async function handleSave() {
    const payload = { ...form, productIds: form.productIds.split(',').map((s) => s.trim()).filter(Boolean) };
    if (editItem) { await collectionsApi.update(editItem.id, payload); }
    else { await collectionsApi.create(payload); }
    setShowModal(false);
    load();
  }

  async function handleDelete(id: string) {
    if (!confirm('Delete this collection?')) return;
    await collectionsApi.delete(id);
    load();
  }

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <h2 className="text-xl font-semibold">Collections</h2>
        <button onClick={openCreate} className="rounded bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">+ New Collection</button>
      </div>

      {loading ? <p className="text-gray-500">Loading...</p> : (
        <div className="overflow-x-auto rounded-lg border bg-white">
          <table className="w-full text-sm">
            <thead className="border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
              <tr><th className="px-4 py-3">Name</th><th className="px-4 py-3">Slug</th><th className="px-4 py-3">Products</th><th className="px-4 py-3">Actions</th></tr>
            </thead>
            <tbody className="divide-y">
              {collections.map((c) => (
                <tr key={c.id} className="hover:bg-gray-50">
                  <td className="px-4 py-3 font-medium">{c.name}</td>
                  <td className="px-4 py-3 text-gray-500">{c.slug}</td>
                  <td className="px-4 py-3">{c.productIds.length}</td>
                  <td className="px-4 py-3 space-x-2">
                    <button onClick={() => openEdit(c)} className="text-indigo-600 hover:underline">Edit</button>
                    <button onClick={() => handleDelete(c.id)} className="text-red-600 hover:underline">Delete</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
          <div className="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
            <h3 className="mb-4 text-lg font-semibold">{editItem ? 'Edit Collection' : 'New Collection'}</h3>
            <div className="space-y-3">
              <input placeholder="Name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" />
              <input placeholder="Slug" value={form.slug} onChange={(e) => setForm({ ...form, slug: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" />
              <textarea placeholder="Description" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" rows={3} />
              <input placeholder="Image URL" value={form.image} onChange={(e) => setForm({ ...form, image: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" />
              <input placeholder="Product IDs (comma-separated)" value={form.productIds} onChange={(e) => setForm({ ...form, productIds: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" />
            </div>
            <div className="mt-4 flex justify-end gap-2">
              <button onClick={() => setShowModal(false)} className="rounded border px-4 py-2 text-sm hover:bg-gray-50">Cancel</button>
              <button onClick={handleSave} className="rounded bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">Save</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
