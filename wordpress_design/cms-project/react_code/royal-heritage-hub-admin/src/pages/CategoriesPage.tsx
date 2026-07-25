import { useEffect, useState } from 'react';
import { categoriesApi } from '@/api/categories';
import { Table } from '@/components/Table';
import { Modal } from '@/components/Modal';
import { Badge } from '@/components/Badge';
import type { Category } from '@/types';

export default function CategoriesPage() {
  const [categories, setCategories] = useState<Category[]>([]);
  const [showAdd, setShowAdd] = useState(false);
  const [editCat, setEditCat] = useState<Category | null>(null);

  useEffect(() => { categoriesApi.getAll().then(({ data }) => setCategories(data || [])); }, []);

  async function handleDelete(id: string) {
    if (!confirm('Delete this category?')) return;
    await categoriesApi.delete(id);
    setCategories((c) => c.filter((x) => x.id !== id));
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold">{categories.length} Categories</h2>
        <button onClick={() => setShowAdd(true)} className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Add Category</button>
      </div>
      <Table
        columns={[
          { key: 'name', label: 'Name', render: (c: Category) => <span className="font-medium">{c.name}</span> },
          { key: 'slug', label: 'Slug', render: (c: Category) => <span className="font-mono text-xs">{c.slug}</span> },
          { key: 'productCount', label: 'Products' },
          { key: 'featured', label: 'Featured', render: (c: Category) => c.featured ? <Badge variant="success">Yes</Badge> : <Badge variant="neutral">No</Badge> },
          { key: 'parentSlug', label: 'Parent', render: (c: Category) => c.parentSlug || '—' },
          { key: 'actions', label: '', render: (c: Category) => (
            <div className="flex gap-2">
              <button onClick={() => setEditCat(c)} className="text-xs text-indigo-600 hover:underline">Edit</button>
              <button onClick={() => handleDelete(c.id)} className="text-xs text-red-600 hover:underline">Delete</button>
            </div>
          )},
        ]}
        data={categories}
      />

      <Modal isOpen={showAdd || !!editCat} onClose={() => { setShowAdd(false); setEditCat(null); }} title={editCat ? 'Edit Category' : 'Add Category'}>
        <CategoryForm category={editCat} onSave={async (data) => {
          if (editCat) await categoriesApi.update(editCat.id, data);
          else await categoriesApi.create(data);
          categoriesApi.getAll().then(({ data }) => setCategories(data || []));
          setShowAdd(false); setEditCat(null);
        }} onCancel={() => { setShowAdd(false); setEditCat(null); }} />
      </Modal>
    </div>
  );
}

function CategoryForm({ category, onSave, onCancel }: { category: Category | null; onSave: (data: Partial<Category>) => void; onCancel: () => void }) {
  const [form, setForm] = useState({ name: category?.name || '', slug: category?.slug || '', description: category?.description || '', featured: category?.featured || false });
  return (
    <form onSubmit={(e) => { e.preventDefault(); onSave(form); }} className="flex flex-col gap-4">
      <div className="grid grid-cols-2 gap-4">
        <div><label className="mb-1 block text-xs font-medium text-gray-600">Name</label><input required value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
        <div><label className="mb-1 block text-xs font-medium text-gray-600">Slug</label><input value={form.slug} onChange={(e) => setForm({ ...form, slug: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
      </div>
      <div><label className="mb-1 block text-xs font-medium text-gray-600">Description</label><textarea value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} rows={2} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
      <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={form.featured} onChange={(e) => setForm({ ...form, featured: e.target.checked })} className="rounded" /> Featured</label>
      <div className="flex justify-end gap-2">
        <button type="button" onClick={onCancel} className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Save</button>
      </div>
    </form>
  );
}
