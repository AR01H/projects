import { useEffect, useState } from 'react';
import { blogApi } from '@/api/blog';
import { Table } from '@/components/Table';
import { Modal } from '@/components/Modal';
import { formatDate, slugify } from '@/utils/format';
import type { BlogPost } from '@/types';

export default function BlogPage() {
  const [posts, setPosts] = useState<BlogPost[]>([]);
  const [editPost, setEditPost] = useState<BlogPost | null>(null);
  const [showAdd, setShowAdd] = useState(false);

  useEffect(() => { blogApi.getAllPosts().then(({ data }) => setPosts(data || [])); }, []);

  async function handleDelete(id: string) {
    if (!confirm('Delete this post?')) return;
    await blogApi.deletePost(id);
    setPosts((p) => p.filter((x) => x.id !== id));
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold">{posts.length} Posts</h2>
        <button onClick={() => setShowAdd(true)} className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ New Post</button>
      </div>
      <Table
        columns={[
          { key: 'title', label: 'Title', render: (p: BlogPost) => <span className="font-medium">{p.title}</span> },
          { key: 'categorySlug', label: 'Category' },
          { key: 'author', label: 'Author' },
          { key: 'date', label: 'Date', render: (p: BlogPost) => formatDate(p.date) },
          { key: 'readMinutes', label: 'Read', render: (p: BlogPost) => `${p.readMinutes} min` },
          { key: 'actions', label: '', render: (p: BlogPost) => (
            <div className="flex gap-2">
              <button onClick={() => setEditPost(p)} className="text-xs text-indigo-600 hover:underline">Edit</button>
              <button onClick={() => handleDelete(p.id)} className="text-xs text-red-600 hover:underline">Delete</button>
            </div>
          )},
        ]}
        data={posts}
      />

      <Modal isOpen={showAdd || !!editPost} onClose={() => { setShowAdd(false); setEditPost(null); }} title={editPost ? 'Edit Post' : 'New Post'} maxWidth="max-w-2xl">
        <PostForm post={editPost} onSave={async (data) => {
          if (editPost) await blogApi.updatePost(editPost.id, data);
          else await blogApi.createPost(data);
          blogApi.getAllPosts().then(({ data }) => setPosts(data || []));
          setShowAdd(false); setEditPost(null);
        }} onCancel={() => { setShowAdd(false); setEditPost(null); }} />
      </Modal>
    </div>
  );
}

function PostForm({ post, onSave, onCancel }: { post: BlogPost | null; onSave: (data: Partial<BlogPost>) => void; onCancel: () => void }) {
  const [form, setForm] = useState({
    title: post?.title || '', slug: post?.slug || '', excerpt: post?.excerpt || '', author: post?.author || '', categorySlug: post?.categorySlug || '',
    coverImage: post?.coverImage || '', date: post?.date || new Date().toISOString().split('T')[0], readMinutes: post?.readMinutes || 5,
  });
  return (
    <form onSubmit={(e) => { e.preventDefault(); onSave({ ...form, content: post?.content || [form.excerpt] }); }} className="flex flex-col gap-4">
      <div className="grid grid-cols-2 gap-4">
        <div className="col-span-2"><label className="mb-1 block text-xs font-medium text-gray-600">Title</label><input required value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value, slug: slugify(e.target.value) })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
        <div><label className="mb-1 block text-xs font-medium text-gray-600">Slug</label><input value={form.slug} onChange={(e) => setForm({ ...form, slug: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
        <div><label className="mb-1 block text-xs font-medium text-gray-600">Author</label><input value={form.author} onChange={(e) => setForm({ ...form, author: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
        <div><label className="mb-1 block text-xs font-medium text-gray-600">Category</label><input value={form.categorySlug} onChange={(e) => setForm({ ...form, categorySlug: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
        <div><label className="mb-1 block text-xs font-medium text-gray-600">Date</label><input type="date" value={form.date} onChange={(e) => setForm({ ...form, date: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
      </div>
      <div><label className="mb-1 block text-xs font-medium text-gray-600">Excerpt</label><textarea value={form.excerpt} onChange={(e) => setForm({ ...form, excerpt: e.target.value })} rows={3} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
      <div className="flex justify-end gap-2">
        <button type="button" onClick={onCancel} className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Save</button>
      </div>
    </form>
  );
}
