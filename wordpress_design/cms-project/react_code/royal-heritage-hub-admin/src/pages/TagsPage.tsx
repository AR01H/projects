import { useEffect, useState } from 'react';
import { tagsApi } from '@/api/tags';
import type { TagMeta } from '@/types';

export default function TagsPage() {
  const [tags, setTags] = useState<TagMeta[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [form, setForm] = useState({ tag: '', label: '', parentTag: '' });

  useEffect(() => { load(); }, []);

  async function load() {
    setLoading(true);
    const { data } = await tagsApi.getAll();
    if (data) setTags(data);
    setLoading(false);
  }

  async function handleSave() {
    await tagsApi.create({ tag: form.tag, label: form.label, parentTag: form.parentTag || null });
    setShowModal(false);
    setForm({ tag: '', label: '', parentTag: '' });
    load();
  }

  async function handleDelete(tag: string) {
    if (!confirm(`Delete tag "${tag}"?`)) return;
    await tagsApi.delete(tag);
    load();
  }

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <h2 className="text-xl font-semibold">Tags</h2>
        <button onClick={() => setShowModal(true)} className="rounded bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">+ New Tag</button>
      </div>

      {loading ? <p className="text-gray-500">Loading...</p> : (
        <div className="overflow-x-auto rounded-lg border bg-white">
          <table className="w-full text-sm">
            <thead className="border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
              <tr><th className="px-4 py-3">Tag</th><th className="px-4 py-3">Label</th><th className="px-4 py-3">Parent</th><th className="px-4 py-3">Actions</th></tr>
            </thead>
            <tbody className="divide-y">
              {tags.map((t) => (
                <tr key={t.tag} className="hover:bg-gray-50">
                  <td className="px-4 py-3 font-mono text-xs">{t.tag}</td>
                  <td className="px-4 py-3">{t.label}</td>
                  <td className="px-4 py-3 text-gray-500">{t.parentTag || '—'}</td>
                  <td className="px-4 py-3">
                    <button onClick={() => handleDelete(t.tag)} className="text-red-600 hover:underline">Delete</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
          <div className="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 className="mb-4 text-lg font-semibold">New Tag</h3>
            <div className="space-y-3">
              <input placeholder="Tag (slug)" value={form.tag} onChange={(e) => setForm({ ...form, tag: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" />
              <input placeholder="Label (display name)" value={form.label} onChange={(e) => setForm({ ...form, label: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" />
              <input placeholder="Parent tag (optional)" value={form.parentTag} onChange={(e) => setForm({ ...form, parentTag: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" />
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
