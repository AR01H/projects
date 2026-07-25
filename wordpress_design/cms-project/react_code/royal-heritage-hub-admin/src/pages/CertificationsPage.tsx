import { useEffect, useState } from 'react';
import { certificationsApi } from '@/api/certifications';
import type { CertificationEntry } from '@/types';

export default function CertificationsPage() {
  const [certs, setCerts] = useState<CertificationEntry[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editItem, setEditItem] = useState<CertificationEntry | null>(null);
  const [form, setForm] = useState({ title: '', issuedBy: '', certificateNumber: '', date: '', description: '', image: '', imageSide: 'left' as 'left' | 'right' });

  useEffect(() => { load(); }, []);

  async function load() {
    setLoading(true);
    const { data } = await certificationsApi.getAll();
    if (data) setCerts(data);
    setLoading(false);
  }

  function openCreate() { setEditItem(null); setForm({ title: '', issuedBy: '', certificateNumber: '', date: '', description: '', image: '', imageSide: 'left' }); setShowModal(true); }
  function openEdit(c: CertificationEntry) { setEditItem(c); setForm({ title: c.title, issuedBy: c.issuedBy, certificateNumber: c.certificateNumber || '', date: c.date || '', description: c.description, image: c.image, imageSide: c.imageSide || 'left' }); setShowModal(true); }

  async function handleSave() {
    if (editItem) { await certificationsApi.update(editItem.id, form); }
    else { await certificationsApi.create(form); }
    setShowModal(false);
    load();
  }

  async function handleDelete(id: string) {
    if (!confirm('Delete this certification?')) return;
    await certificationsApi.delete(id);
    load();
  }

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <h2 className="text-xl font-semibold">Certifications</h2>
        <button onClick={openCreate} className="rounded bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">+ New Certification</button>
      </div>

      {loading ? <p className="text-gray-500">Loading...</p> : (
        <div className="grid gap-4 md:grid-cols-2">
          {certs.map((c) => (
            <div key={c.id} className="rounded-lg border bg-white p-4 shadow-sm">
              <div className="flex items-start justify-between">
                <div className="flex-1">
                  <h3 className="font-medium">{c.title}</h3>
                  <p className="mt-1 text-xs text-gray-500">{c.issuedBy}</p>
                  {c.certificateNumber && <p className="mt-1 text-xs text-gray-400">#{c.certificateNumber}</p>}
                  <p className="mt-2 text-sm text-gray-600 line-clamp-2">{c.description}</p>
                </div>
                {c.image && <img src={c.image} alt="" className="ml-3 h-16 w-16 rounded object-cover" />}
              </div>
              <div className="mt-3 flex gap-2">
                <button onClick={() => openEdit(c)} className="text-xs text-indigo-600 hover:underline">Edit</button>
                <button onClick={() => handleDelete(c.id)} className="text-xs text-red-600 hover:underline">Delete</button>
              </div>
            </div>
          ))}
        </div>
      )}

      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
          <div className="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
            <h3 className="mb-4 text-lg font-semibold">{editItem ? 'Edit Certification' : 'New Certification'}</h3>
            <div className="space-y-3">
              <input placeholder="Title" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" />
              <input placeholder="Issued By" value={form.issuedBy} onChange={(e) => setForm({ ...form, issuedBy: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" />
              <input placeholder="Certificate Number" value={form.certificateNumber} onChange={(e) => setForm({ ...form, certificateNumber: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" />
              <input type="date" value={form.date} onChange={(e) => setForm({ ...form, date: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" />
              <textarea placeholder="Description" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" rows={3} />
              <input placeholder="Image URL" value={form.image} onChange={(e) => setForm({ ...form, image: e.target.value })} className="w-full rounded border px-3 py-2 text-sm" />
              <select value={form.imageSide} onChange={(e) => setForm({ ...form, imageSide: e.target.value as 'left' | 'right' })} className="w-full rounded border px-3 py-2 text-sm">
                <option value="left">Image Left</option>
                <option value="right">Image Right</option>
              </select>
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
