import { useEffect, useState } from 'react';
import { productsApi } from '@/api/products';
import { categoriesApi } from '@/api/categories';
import { collectionsApi } from '@/api/collections';
import { tagsApi } from '@/api/tags';
import { Table } from '@/components/Table';
import { Modal } from '@/components/Modal';
import { Badge } from '@/components/Badge';
import { FileUpload } from '@/components/FileUpload';
import { formatCurrency } from '@/utils/format';
import type { Product, Category, Collection, TagMeta, UploadedFile, ProductCertificate, ProductExternalAttribute, ProductSection } from '@/types';

export default function ProductsPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [collections, setCollections] = useState<Collection[]>([]);
  const [tags, setTags] = useState<TagMeta[]>([]);
  const [search, setSearch] = useState('');
  const [filterCategory, setFilterCategory] = useState('');
  const [editProduct, setEditProduct] = useState<Product | null>(null);
  const [showAdd, setShowAdd] = useState(false);

  useEffect(() => {
    productsApi.getAll().then(({ data }) => setProducts(data || []));
    categoriesApi.getAll().then(({ data }) => setCategories(data || []));
    collectionsApi.getAll().then(({ data }) => setCollections(data || []));
    tagsApi.getAll().then(({ data }) => setTags(data || []));
  }, []);

  const filtered = products.filter((p) => {
    const matchSearch = p.name.toLowerCase().includes(search.toLowerCase()) || p.sku.toLowerCase().includes(search.toLowerCase()) || p.tags.some((t) => t.toLowerCase().includes(search.toLowerCase()));
    const matchCategory = !filterCategory || p.categorySlug === filterCategory;
    return matchSearch && matchCategory;
  });

  async function handleDelete(id: string) {
    if (!confirm('Delete this product?')) return;
    await productsApi.delete(id);
    setProducts((p) => p.filter((x) => x.id !== id));
  }

  async function handleStockUpdate(id: string, stock: number) {
    await productsApi.updateStock(id, stock);
    setProducts((p) => p.map((x) => x.id === id ? { ...x, stock } : x));
  }

  function getStatus(p: Product) {
    if (p.stock === 0) return <Badge variant="danger">Out of Stock</Badge>;
    if (p.stock <= p.lowStockThreshold) return <Badge variant="warning">Low Stock</Badge>;
    if (p.isBestSeller) return <Badge variant="success">Best Seller</Badge>;
    if (p.isNewArrival) return <Badge variant="info">New</Badge>;
    if (p.isFeatured) return <Badge variant="primary">Featured</Badge>;
    return <Badge variant="neutral">Standard</Badge>;
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center gap-3">
        <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search by name, SKU, or tag..." className="w-72 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" />
        <select value={filterCategory} onChange={(e) => setFilterCategory(e.target.value)} className="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500">
          <option value="">All Categories</option>
          {categories.map((c) => <option key={c.id} value={c.slug}>{c.name}</option>)}
        </select>
        <div className="flex-1" />
        <span className="text-xs text-gray-500">{filtered.length} products</span>
        <button onClick={() => setShowAdd(true)} className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Add Product</button>
      </div>

      <Table
        columns={[
          { key: 'thumbnail', label: '', render: (p: Product) => <img src={p.thumbnail} alt="" className="h-10 w-10 rounded object-cover" />, className: 'w-12' },
          { key: 'name', label: 'Name', render: (p: Product) => <span className="font-medium">{p.name}</span> },
          { key: 'sku', label: 'SKU', render: (p: Product) => <span className="font-mono text-xs">{p.sku}</span> },
          { key: 'price', label: 'Price', render: (p: Product) => (
            <div>
              <span className="font-semibold">{formatCurrency(p.price)}</span>
              {p.compareAtPrice && <span className="ml-1 text-xs text-gray-400 line-through">{formatCurrency(p.compareAtPrice)}</span>}
            </div>
          )},
          { key: 'stock', label: 'Stock', render: (p: Product) => (
            <div className="flex items-center gap-1" onClick={(e) => e.stopPropagation()}>
              <input
                type="number"
                defaultValue={p.stock}
                key={`${p.id}-${p.stock}`}
                onBlur={(e) => {
                  const val = Number(e.target.value);
                  if (val !== p.stock) handleStockUpdate(p.id, val);
                }}
                onKeyDown={(e) => {
                  if (e.key === 'Enter') {
                    const val = Number((e.target as HTMLInputElement).value);
                    if (val !== p.stock) handleStockUpdate(p.id, val);
                    (e.target as HTMLInputElement).blur();
                  }
                }}
                className={`w-16 rounded border px-2 py-1 text-xs font-semibold text-right ${p.stock <= 5 ? 'border-red-300 text-red-600 bg-red-50' : 'border-gray-200'}`}
              />
              {p.stock <= p.lowStockThreshold && p.stock > 0 && <span className="text-[0.6rem] text-yellow-600">⚠</span>}
              {p.stock === 0 && <span className="text-[0.6rem] text-red-600">✕</span>}
            </div>
          )},
          { key: 'status', label: 'Status', render: (p: Product) => getStatus(p) },
          { key: 'actions', label: '', render: (p: Product) => (
            <div className="flex gap-2">
              <button onClick={() => setEditProduct(p)} className="text-xs text-indigo-600 hover:underline">Edit</button>
              <button onClick={() => handleDelete(p.id)} className="text-xs text-red-600 hover:underline">Delete</button>
            </div>
          )},
        ]}
        data={filtered}
        emptyMessage="No products found"
      />

      <Modal isOpen={showAdd || !!editProduct} onClose={() => { setShowAdd(false); setEditProduct(null); }} title={editProduct ? 'Edit Product' : 'Add Product'} maxWidth="max-w-4xl">
        <ProductForm
          product={editProduct}
          categories={categories}
          collections={collections}
          tags={tags}
          onSave={async (data) => {
            if (editProduct) { await productsApi.update(editProduct.id, data); }
            else { await productsApi.create(data); }
            productsApi.getAll().then(({ data }) => setProducts(data || []));
            setShowAdd(false); setEditProduct(null);
          }}
          onCancel={() => { setShowAdd(false); setEditProduct(null); }}
        />
      </Modal>
    </div>
  );
}

function ProductForm({ product, categories, collections, tags, onSave, onCancel }: {
  product: Product | null;
  categories: Category[];
  collections: Collection[];
  tags: TagMeta[];
  onSave: (data: Partial<Product>) => void;
  onCancel: () => void;
}) {
  const [tab, setTab] = useState<'basic' | 'media' | 'specs' | 'variants' | 'certificates' | 'sections' | 'attributes' | 'flags'>('basic');

  const [form, setForm] = useState({
    name: product?.name || '',
    slug: product?.slug || '',
    sku: product?.sku || '',
    shortDescription: product?.shortDescription || '',
    description: product?.description || '',
    price: product?.price || 0,
    compareAtPrice: product?.compareAtPrice || 0,
    currency: product?.currency || 'INR',
    stock: product?.stock || 0,
    lowStockThreshold: product?.lowStockThreshold || 5,
    categoryId: product?.categoryId || '',
    categorySlug: product?.categorySlug || '',
    thumbnail: product?.thumbnail || '',
    images: product?.images?.join('\n') || '',
    videoUrl: product?.videoUrl || '',
    makerName: product?.makerName || '',
    rating: product?.rating || 0,
    reviewCount: product?.reviewCount || 0,
    tags: product?.tags?.join(', ') || '',
    qualityBadges: product?.qualityBadges?.join(', ') || '',
    collectionIds: product?.collectionIds?.join(', ') || '',
  });

  const [specs, setSpecs] = useState(product?.specs || [] as { key: string; label: string; value: string; highlight?: boolean }[]);
  const [variants, setVariants] = useState(product?.variants || [] as { id: string; label: string; type: string; value: string; priceModifier?: number; inStock: boolean }[]);
  const [isBestSeller, setIsBestSeller] = useState(product?.isBestSeller || false);
  const [isNewArrival, setIsNewArrival] = useState(product?.isNewArrival || false);
  const [isFeatured, setIsFeatured] = useState(product?.isFeatured || false);
  const [isLimitedEdition, setIsLimitedEdition] = useState(product?.isLimitedEdition || false);
  const [isFestive, setIsFestive] = useState(product?.isFestive || false);

  // Upload state
  const [thumbnailFiles, setThumbnailFiles] = useState<UploadedFile[]>([]);
  const [galleryFiles, setGalleryFiles] = useState<UploadedFile[]>([]);
  const [videoFiles, setVideoFiles] = useState<UploadedFile[]>([]);

  // Certificates
  const [certificates, setCertificates] = useState<ProductCertificate[]>([]);

  // External attributes
  const [extAttrs, setExtAttrs] = useState<ProductExternalAttribute[]>([]);

  // Custom sections
  const [sections, setSections] = useState<ProductSection[]>([]);

  function handleSave() {
    onSave({
      ...form,
      thumbnail: thumbnailFiles.length > 0 ? thumbnailFiles[0].url : form.thumbnail,
      images: galleryFiles.map((f) => f.url).concat(form.images.split('\n').map((s) => s.trim()).filter(Boolean)),
      videoUrl: videoFiles.length > 0 ? videoFiles[0].url : form.videoUrl,
      price: Number(form.price),
      compareAtPrice: form.compareAtPrice ? Number(form.compareAtPrice) : undefined,
      stock: Number(form.stock),
      lowStockThreshold: Number(form.lowStockThreshold),
      rating: Number(form.rating),
      reviewCount: Number(form.reviewCount),
      tags: form.tags.split(',').map((s) => s.trim()).filter(Boolean),
      qualityBadges: form.qualityBadges.split(',').map((s) => s.trim()).filter(Boolean),
      collectionIds: form.collectionIds.split(',').map((s) => s.trim()).filter(Boolean),
      specs,
      variants,
      isBestSeller, isNewArrival, isFeatured, isLimitedEdition, isFestive,
      reviews: product?.reviews || [],
    } as Partial<Product>);
  }

  const tabs = [
    { key: 'basic', label: 'Basic Info' },
    { key: 'media', label: 'Media' },
    { key: 'specs', label: 'Specs & Pricing' },
    { key: 'variants', label: 'Variants' },
    { key: 'certificates', label: 'Certificates' },
    { key: 'sections', label: 'Sections' },
    { key: 'attributes', label: 'Attributes' },
    { key: 'flags', label: 'Flags & Tags' },
  ] as const;

  return (
    <div>
      {/* Tab bar */}
      <div className="mb-4 flex gap-1 border-b overflow-x-auto">
        {tabs.map((t) => (
          <button key={t.key} onClick={() => setTab(t.key)} className={`whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 transition-colors ${tab === t.key ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'}`}>
            {t.label}
          </button>
        ))}
      </div>

      {/* Basic Info */}
      {tab === 'basic' && (
        <div className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div><label className="mb-1 block text-xs font-medium text-gray-600">Name *</label><input required value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
            <div><label className="mb-1 block text-xs font-medium text-gray-600">Slug</label><input value={form.slug} onChange={(e) => setForm({ ...form, slug: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" placeholder="auto-generated from name" /></div>
            <div><label className="mb-1 block text-xs font-medium text-gray-600">SKU *</label><input required value={form.sku} onChange={(e) => setForm({ ...form, sku: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
            <div><label className="mb-1 block text-xs font-medium text-gray-600">Currency</label><input value={form.currency} onChange={(e) => setForm({ ...form, currency: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
          </div>
          <div><label className="mb-1 block text-xs font-medium text-gray-600">Short Description</label><textarea value={form.shortDescription} onChange={(e) => setForm({ ...form, shortDescription: e.target.value })} rows={2} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
          <div><label className="mb-1 block text-xs font-medium text-gray-600">Full Description</label><textarea value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} rows={4} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
          <div className="grid grid-cols-2 gap-4">
            <div><label className="mb-1 block text-xs font-medium text-gray-600">Category</label><select value={form.categorySlug} onChange={(e) => { const cat = categories.find((c) => c.slug === e.target.value); setForm({ ...form, categorySlug: e.target.value, categoryId: cat?.id || '' }); }} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500"><option value="">Select category</option>{categories.map((c) => <option key={c.id} value={c.slug}>{c.name}</option>)}</select></div>
            <div><label className="mb-1 block text-xs font-medium text-gray-600">Maker / Artisan</label><input value={form.makerName} onChange={(e) => setForm({ ...form, makerName: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
          </div>
        </div>
      )}

      {/* Media — Upload */}
      {tab === 'media' && (
        <div className="space-y-6">
          <div>
            <h4 className="mb-2 text-sm font-semibold text-gray-700">Thumbnail Image</h4>
            <FileUpload type="image" label="Upload Thumbnail" description="Main product image (jpg, png, webp)" onUpload={(files) => setThumbnailFiles([...thumbnailFiles, ...files])} onRemove={(id) => setThumbnailFiles(thumbnailFiles.filter((f) => f.id !== id))} existingFiles={thumbnailFiles} />
            {form.thumbnail && thumbnailFiles.length === 0 && <img src={form.thumbnail} alt="" className="mt-2 h-20 w-20 rounded object-cover" />}
          </div>

          <div>
            <h4 className="mb-2 text-sm font-semibold text-gray-700">Gallery Images</h4>
            <FileUpload type="image" multiple label="Upload Gallery Images" description="Additional product photos (max 10)" maxFiles={10} onUpload={(files) => setGalleryFiles([...galleryFiles, ...files])} onRemove={(id) => setGalleryFiles(galleryFiles.filter((f) => f.id !== id))} existingFiles={galleryFiles} />
            {form.images && galleryFiles.length === 0 && (
              <div className="mt-2 flex gap-2 overflow-x-auto">
                {form.images.split('\n').filter(Boolean).map((url, i) => <img key={i} src={url.trim()} alt="" className="h-16 w-16 flex-shrink-0 rounded object-cover" />)}
              </div>
            )}
          </div>

          <div>
            <h4 className="mb-2 text-sm font-semibold text-gray-700">Product Video</h4>
            <FileUpload type="video" label="Upload Video" description="Product video (mp4, webm, max 100MB)" maxSizeMB={100} onUpload={(files) => setVideoFiles([...videoFiles, ...files])} onRemove={(id) => setVideoFiles(videoFiles.filter((f) => f.id !== id))} existingFiles={videoFiles} />
            {form.videoUrl && videoFiles.length === 0 && <p className="mt-2 text-xs text-gray-400">Current: {form.videoUrl}</p>}
          </div>

          <div>
            <h4 className="mb-2 text-sm font-semibold text-gray-700">Or Enter URLs Directly</h4>
            <div className="space-y-2">
              <input placeholder="Thumbnail URL" value={form.thumbnail} onChange={(e) => setForm({ ...form, thumbnail: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" />
              <textarea placeholder="Gallery image URLs (one per line)" value={form.images} onChange={(e) => setForm({ ...form, images: e.target.value })} rows={3} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" />
              <input placeholder="Video URL" value={form.videoUrl} onChange={(e) => setForm({ ...form, videoUrl: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" />
            </div>
          </div>
        </div>
      )}

      {/* Specs & Pricing */}
      {tab === 'specs' && (
        <div className="space-y-6">
          <div>
            <h4 className="mb-3 text-sm font-semibold text-gray-700">Pricing & Stock</h4>
            <div className="grid grid-cols-3 gap-4">
              <div><label className="mb-1 block text-xs font-medium text-gray-600">Price *</label><input required type="number" value={form.price} onChange={(e) => setForm({ ...form, price: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
              <div><label className="mb-1 block text-xs font-medium text-gray-600">Compare At Price</label><input type="number" value={form.compareAtPrice} onChange={(e) => setForm({ ...form, compareAtPrice: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
              <div /><div><label className="mb-1 block text-xs font-medium text-gray-600">Stock *</label><input required type="number" value={form.stock} onChange={(e) => setForm({ ...form, stock: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
              <div><label className="mb-1 block text-xs font-medium text-gray-600">Low Stock Threshold</label><input type="number" value={form.lowStockThreshold} onChange={(e) => setForm({ ...form, lowStockThreshold: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
              <div><label className="mb-1 block text-xs font-medium text-gray-600">Rating</label><input type="number" step="0.1" min="0" max="5" value={form.rating} onChange={(e) => setForm({ ...form, rating: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" /></div>
            </div>
          </div>
          <div>
            <div className="mb-3 flex items-center justify-between">
              <h4 className="text-sm font-semibold text-gray-700">Specifications</h4>
              <button type="button" onClick={() => setSpecs([...specs, { key: '', label: '', value: '', highlight: false }])} className="text-xs text-indigo-600 hover:underline">+ Add Spec</button>
            </div>
            <div className="space-y-2">
              {specs.map((spec, i) => (
                <div key={i} className="flex items-center gap-2">
                  <input placeholder="Key" value={spec.key} onChange={(e) => { const next = [...specs]; next[i] = { ...next[i], key: e.target.value }; setSpecs(next); }} className="w-28 rounded border border-gray-300 px-2 py-1.5 text-xs outline-none focus:border-indigo-500" />
                  <input placeholder="Label" value={spec.label} onChange={(e) => { const next = [...specs]; next[i] = { ...next[i], label: e.target.value }; setSpecs(next); }} className="w-28 rounded border border-gray-300 px-2 py-1.5 text-xs outline-none focus:border-indigo-500" />
                  <input placeholder="Value" value={spec.value} onChange={(e) => { const next = [...specs]; next[i] = { ...next[i], value: e.target.value }; setSpecs(next); }} className="flex-1 rounded border border-gray-300 px-2 py-1.5 text-xs outline-none focus:border-indigo-500" />
                  <label className="flex items-center gap-1 text-xs text-gray-500"><input type="checkbox" checked={spec.highlight || false} onChange={(e) => { const next = [...specs]; next[i] = { ...next[i], highlight: e.target.checked }; setSpecs(next); }} />Show</label>
                  <button type="button" onClick={() => setSpecs(specs.filter((_, j) => j !== i))} className="text-xs text-red-500 hover:underline">X</button>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {/* Variants */}
      {tab === 'variants' && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h4 className="text-sm font-semibold text-gray-700">Product Variants</h4>
            <button type="button" onClick={() => setVariants([...variants, { id: `v-${Date.now()}`, label: '', type: 'size', value: '', priceModifier: 0, inStock: true }])} className="text-xs text-indigo-600 hover:underline">+ Add Variant</button>
          </div>
          {variants.length === 0 && <p className="text-xs text-gray-400">No variants yet.</p>}
          <div className="space-y-2">
            {variants.map((v, i) => (
              <div key={v.id} className="flex items-center gap-2 rounded-lg border border-gray-200 p-2">
                <input placeholder="Label" value={v.label} onChange={(e) => { const next = [...variants]; next[i] = { ...next[i], label: e.target.value }; setVariants(next); }} className="w-28 rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                <select value={v.type} onChange={(e) => { const next = [...variants]; next[i] = { ...next[i], type: e.target.value }; setVariants(next); }} className="rounded border border-gray-300 px-2 py-1.5 text-xs outline-none"><option value="size">Size</option><option value="color">Color</option><option value="finish">Finish</option><option value="weight">Weight</option><option value="set">Set</option></select>
                <input placeholder="Value" value={v.value} onChange={(e) => { const next = [...variants]; next[i] = { ...next[i], value: e.target.value }; setVariants(next); }} className="w-20 rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                <input placeholder="Price ±" type="number" value={v.priceModifier || 0} onChange={(e) => { const next = [...variants]; next[i] = { ...next[i], priceModifier: Number(e.target.value) }; setVariants(next); }} className="w-20 rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                <label className="flex items-center gap-1 text-xs text-gray-500"><input type="checkbox" checked={v.inStock} onChange={(e) => { const next = [...variants]; next[i] = { ...next[i], inStock: e.target.checked }; setVariants(next); }} />In Stock</label>
                <button type="button" onClick={() => setVariants(variants.filter((_, j) => j !== i))} className="text-xs text-red-500 hover:underline">X</button>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Certificates */}
      {tab === 'certificates' && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h4 className="text-sm font-semibold text-gray-700">Product Certificates</h4>
            <button type="button" onClick={() => setCertificates([...certificates, { id: `cert-${Date.now()}`, productId: product?.id || '', title: '', issuer: '', description: '', verified: false }])} className="text-xs text-indigo-600 hover:underline">+ Add Certificate</button>
          </div>
          {certificates.length === 0 && <p className="text-xs text-gray-400">No certificates added. Add GI tags, quality certificates, etc.</p>}
          <div className="space-y-3">
            {certificates.map((cert, i) => (
              <div key={cert.id} className="rounded-lg border border-gray-200 p-3 space-y-2">
                <div className="flex items-center justify-between">
                  <span className="text-xs font-medium text-gray-500">Certificate {i + 1}</span>
                  <button type="button" onClick={() => setCertificates(certificates.filter((_, j) => j !== i))} className="text-xs text-red-500 hover:underline">Remove</button>
                </div>
                <div className="grid grid-cols-2 gap-2">
                  <input placeholder="Title (e.g. GI Tag)" value={cert.title} onChange={(e) => { const next = [...certificates]; next[i] = { ...next[i], title: e.target.value }; setCertificates(next); }} className="rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                  <input placeholder="Issuer" value={cert.issuer} onChange={(e) => { const next = [...certificates]; next[i] = { ...next[i], issuer: e.target.value }; setCertificates(next); }} className="rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                  <input placeholder="Certificate Number" value={cert.certificateNumber || ''} onChange={(e) => { const next = [...certificates]; next[i] = { ...next[i], certificateNumber: e.target.value }; setCertificates(next); }} className="rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                  <input type="date" value={cert.issueDate || ''} onChange={(e) => { const next = [...certificates]; next[i] = { ...next[i], issueDate: e.target.value }; setCertificates(next); }} className="rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                </div>
                <textarea placeholder="Description" value={cert.description} onChange={(e) => { const next = [...certificates]; next[i] = { ...next[i], description: e.target.value }; setCertificates(next); }} rows={2} className="w-full rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                <div className="flex gap-2">
                  <FileUpload type="image" label="Upload Certificate Image" maxFiles={1} onUpload={(files) => { const next = [...certificates]; next[i] = { ...next[i], imageUrl: files[0]?.url }; setCertificates(next); }} existingFiles={cert.imageUrl ? [{ id: 'existing', name: 'Certificate', url: cert.imageUrl, type: 'image', mimeType: 'image/jpeg', size: 0, uploadedAt: '' }] : []} />
                  <FileUpload type="document" label="Upload Certificate PDF" maxFiles={1} onUpload={(files) => { const next = [...certificates]; next[i] = { ...next[i], documentUrl: files[0]?.url }; setCertificates(next); }} existingFiles={cert.documentUrl ? [{ id: 'existing', name: 'Document', url: cert.documentUrl, type: 'document', mimeType: 'application/pdf', size: 0, uploadedAt: '' }] : []} />
                </div>
                <label className="flex items-center gap-2 text-xs text-gray-500"><input type="checkbox" checked={cert.verified} onChange={(e) => { const next = [...certificates]; next[i] = { ...next[i], verified: e.target.checked }; setCertificates(next); }} />Verified</label>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Custom Sections */}
      {tab === 'sections' && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h4 className="text-sm font-semibold text-gray-700">Custom Sections</h4>
            <button type="button" onClick={() => setSections([...sections, { id: `sec-${Date.now()}`, title: '', content: '', type: 'text', order: sections.length }])} className="text-xs text-indigo-600 hover:underline">+ Add Section</button>
          </div>
          {sections.length === 0 && <p className="text-xs text-gray-400">No custom sections. Add rich content sections to the product page.</p>}
          <div className="space-y-3">
            {sections.map((sec, i) => (
              <div key={sec.id} className="rounded-lg border border-gray-200 p-3 space-y-2">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <span className="text-xs font-medium text-gray-500">Section {i + 1}</span>
                    <select value={sec.type} onChange={(e) => { const next = [...sections]; next[i] = { ...next[i], type: e.target.value as ProductSection['type'] }; setSections(next); }} className="rounded border border-gray-300 px-2 py-1 text-xs outline-none"><option value="text">Text</option><option value="image">Image</option><option value="video">Video</option><option value="gallery">Gallery</option><option value="specs">Specs</option></select>
                  </div>
                  <div className="flex items-center gap-2">
                    <button type="button" onClick={() => { const next = [...sections]; [next[i - 1], next[i]] = [next[i], next[i - 1]]; setSections(next); }} disabled={i === 0} className="text-xs text-gray-400 hover:text-gray-600 disabled:opacity-30">↑</button>
                    <button type="button" onClick={() => { const next = [...sections]; [next[i], next[i + 1]] = [next[i + 1], next[i]]; setSections(next); }} disabled={i === sections.length - 1} className="text-xs text-gray-400 hover:text-gray-600 disabled:opacity-30">↓</button>
                    <button type="button" onClick={() => setSections(sections.filter((_, j) => j !== i))} className="text-xs text-red-500 hover:underline">Remove</button>
                  </div>
                </div>
                <input placeholder="Section Title" value={sec.title} onChange={(e) => { const next = [...sections]; next[i] = { ...next[i], title: e.target.value }; setSections(next); }} className="w-full rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                <textarea placeholder="Section Content (supports markdown)" value={sec.content} onChange={(e) => { const next = [...sections]; next[i] = { ...next[i], content: e.target.value }; setSections(next); }} rows={4} className="w-full rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                {(sec.type === 'image' || sec.type === 'gallery') && (
                  <FileUpload type="image" multiple={sec.type === 'gallery'} label={`Upload ${sec.type === 'gallery' ? 'Gallery' : 'Image'}`} maxFiles={sec.type === 'gallery' ? 10 : 1} onUpload={(files) => { const next = [...sections]; next[i] = { ...next[i], imageUrl: files[0]?.url }; setSections(next); }} existingFiles={sec.imageUrl ? [{ id: 'existing', name: 'Section Image', url: sec.imageUrl, type: 'image', mimeType: 'image/jpeg', size: 0, uploadedAt: '' }] : []} />
                )}
                {sec.type === 'video' && (
                  <FileUpload type="video" label="Upload Video" maxFiles={1} onUpload={(files) => { const next = [...sections]; next[i] = { ...next[i], videoUrl: files[0]?.url }; setSections(next); }} existingFiles={sec.videoUrl ? [{ id: 'existing', name: 'Section Video', url: sec.videoUrl, type: 'video', mimeType: 'video/mp4', size: 0, uploadedAt: '' }] : []} />
                )}
              </div>
            ))}
          </div>
        </div>
      )}

      {/* External Attributes */}
      {tab === 'attributes' && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h4 className="text-sm font-semibold text-gray-700">External Attributes</h4>
            <button type="button" onClick={() => setExtAttrs([...extAttrs, { id: `attr-${Date.now()}`, key: '', label: '', value: '', type: 'text', section: 'General', highlighted: false }])} className="text-xs text-indigo-600 hover:underline">+ Add Attribute</button>
          </div>
          {extAttrs.length === 0 && <p className="text-xs text-gray-400">No external attributes. Add custom fields like certifications, origin, care instructions, etc.</p>}
          <div className="space-y-2">
            {extAttrs.map((attr, i) => (
              <div key={attr.id} className="flex items-center gap-2 rounded-lg border border-gray-200 p-2">
                <input placeholder="Key (e.g. origin)" value={attr.key} onChange={(e) => { const next = [...extAttrs]; next[i] = { ...next[i], key: e.target.value }; setExtAttrs(next); }} className="w-24 rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                <input placeholder="Label" value={attr.label} onChange={(e) => { const next = [...extAttrs]; next[i] = { ...next[i], label: e.target.value }; setExtAttrs(next); }} className="w-24 rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                <select value={attr.type} onChange={(e) => { const next = [...extAttrs]; next[i] = { ...next[i], type: e.target.value as ProductExternalAttribute['type'] }; setExtAttrs(next); }} className="rounded border border-gray-300 px-2 py-1.5 text-xs outline-none"><option value="text">Text</option><option value="number">Number</option><option value="boolean">Boolean</option><option value="url">URL</option><option value="date">Date</option><option value="select">Select</option></select>
                <input placeholder="Value" value={attr.value} onChange={(e) => { const next = [...extAttrs]; next[i] = { ...next[i], value: e.target.value }; setExtAttrs(next); }} className="flex-1 rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                <input placeholder="Section" value={attr.section} onChange={(e) => { const next = [...extAttrs]; next[i] = { ...next[i], section: e.target.value }; setExtAttrs(next); }} className="w-24 rounded border border-gray-300 px-2 py-1.5 text-xs outline-none" />
                <label className="flex items-center gap-1 text-xs text-gray-500"><input type="checkbox" checked={attr.highlighted} onChange={(e) => { const next = [...extAttrs]; next[i] = { ...next[i], highlighted: e.target.checked }; setExtAttrs(next); }} />★</label>
                <button type="button" onClick={() => setExtAttrs(extAttrs.filter((_, j) => j !== i))} className="text-xs text-red-500 hover:underline">X</button>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Flags & Tags */}
      {tab === 'flags' && (
        <div className="space-y-6">
          <div>
            <h4 className="mb-3 text-sm font-semibold text-gray-700">Product Flags</h4>
            <div className="grid grid-cols-3 gap-3">
              {[
                { label: 'Best Seller', checked: isBestSeller, set: setIsBestSeller },
                { label: 'New Arrival', checked: isNewArrival, set: setIsNewArrival },
                { label: 'Featured', checked: isFeatured, set: setIsFeatured },
                { label: 'Limited Edition', checked: isLimitedEdition, set: setIsLimitedEdition },
                { label: 'Festive', checked: isFestive, set: setIsFestive },
              ].map((f) => (
                <label key={f.label} className="flex items-center gap-2 rounded-lg border border-gray-200 p-3 text-sm cursor-pointer hover:bg-gray-50">
                  <input type="checkbox" checked={f.checked} onChange={(e) => f.set(e.target.checked)} className="rounded" />
                  {f.label}
                </label>
              ))}
            </div>
          </div>
          <div>
            <h4 className="mb-3 text-sm font-semibold text-gray-700">Tags & Badges</h4>
            <div className="space-y-3">
              <div><label className="mb-1 block text-xs font-medium text-gray-600">Tags (comma-separated)</label><input value={form.tags} onChange={(e) => setForm({ ...form, tags: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" placeholder="handmade, gift, traditional" /></div>
              <div><label className="mb-1 block text-xs font-medium text-gray-600">Quality Badges (comma-separated)</label><input value={form.qualityBadges} onChange={(e) => setForm({ ...form, qualityBadges: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" placeholder="Handmade, GI Tagged, Organic" /></div>
              <div><label className="mb-1 block text-xs font-medium text-gray-600">Collection IDs (comma-separated)</label><input value={form.collectionIds} onChange={(e) => setForm({ ...form, collectionIds: e.target.value })} className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" placeholder="col-best-sellers, col-festive" /></div>
            </div>
          </div>
        </div>
      )}

      {/* Save / Cancel */}
      <div className="mt-6 flex justify-end gap-2 border-t pt-4">
        <button type="button" onClick={onCancel} className="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="button" onClick={handleSave} className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Save Product</button>
      </div>
    </div>
  );
}
