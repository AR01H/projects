import { useEffect, useState } from 'react';
import { couponsApi } from '@/api/coupons';
import type { Coupon, CouponType, CouponAppliesTo } from '@/types';

const COUPON_TYPES: { value: CouponType; label: string; icon: string }[] = [
  { value: 'percentage', label: '% Off', icon: '%' },
  { value: 'fixed', label: '₹ Off', icon: '₹' },
  { value: 'free_shipping', label: 'Free Shipping', icon: '🚚' },
  { value: 'buy_x_get_y', label: 'Buy X Get Y', icon: '🎁' },
  { value: 'buy_x_get_percent', label: 'Buy X Get % Off', icon: '🏷️' },
  { value: 'bundle', label: 'Bundle Deal', icon: '📦' },
  { value: 'first_order', label: 'First Order', icon: '🆕' },
  { value: 'referral', label: 'Referral', icon: '🤝' },
  { value: 'loyalty', label: 'Loyalty Points', icon: '⭐' },
  { value: 'category_percent', label: 'Category % Off', icon: '📂' },
  { value: 'flash_sale', label: 'Flash Sale', icon: '⚡' },
  { value: 'tiered', label: 'Tiered Discount', icon: '📊' },
  { value: 'cart_threshold', label: 'Cart Threshold', icon: '🛒' },
  { value: 'free_gift', label: 'Free Gift', icon: '🎁' },
  { value: 'volume', label: 'Volume Discount', icon: '📈' },
  { value: 'repeat_purchase', label: 'Repeat Purchase', icon: '🔄' },
  { value: 'birthday', label: 'Birthday', icon: '🎂' },
  { value: 'seasonal', label: 'Seasonal', icon: '🎄' },
  { value: 'clearance', label: 'Clearance', icon: '🏷️' },
];

const APPLIES_TO: { value: CouponAppliesTo; label: string }[] = [
  { value: 'all', label: 'All Products' },
  { value: 'products', label: 'Specific Products' },
  { value: 'categories', label: 'Specific Categories' },
  { value: 'collections', label: 'Specific Collections' },
  { value: 'tags', label: 'Specific Tags' },
  { value: 'variants', label: 'Specific Variants' },
];

function getStatusColor(c: Coupon) {
  if (c.status === 'expired') return 'bg-gray-100 text-gray-600';
  if (c.status === 'scheduled') return 'bg-blue-100 text-blue-700';
  if (!c.active) return 'bg-red-100 text-red-600';
  return 'bg-green-100 text-green-700';
}

function isExpired(c: Coupon) {
  if (!c.validUntil) return false;
  return new Date(c.validUntil) < new Date();
}

function isScheduled(c: Coupon) {
  if (!c.validFrom) return false;
  return new Date(c.validFrom) > new Date();
}

function daysRemaining(c: Coupon) {
  if (!c.validUntil) return null;
  const diff = new Date(c.validUntil).getTime() - Date.now();
  return Math.max(0, Math.ceil(diff / 86400000));
}

export default function CouponsPage() {
  const [coupons, setCoupons] = useState<Coupon[]>([]);
  const [showAdd, setShowAdd] = useState(false);
  const [editCoupon, setEditCoupon] = useState<Coupon | null>(null);
  const [filterType, setFilterType] = useState('');
  const [filterStatus, setFilterStatus] = useState('');
  const [search, setSearch] = useState('');

  useEffect(() => { couponsApi.getAll().then(({ data }) => setCoupons(data || [])); }, []);

  const filtered = coupons.filter((c) => {
    const matchSearch = c.code.toLowerCase().includes(search.toLowerCase()) || c.description.toLowerCase().includes(search.toLowerCase());
    const matchType = !filterType || c.type === filterType;
    const matchStatus = !filterStatus ||
      (filterStatus === 'active' && c.active && !isExpired(c)) ||
      (filterStatus === 'inactive' && !c.active) ||
      (filterStatus === 'expired' && isExpired(c)) ||
      (filterStatus === 'scheduled' && isScheduled(c));
    return matchSearch && matchType && matchStatus;
  });

  async function handleDelete(code: string) {
    if (!confirm('Delete this coupon?')) return;
    await couponsApi.delete(code);
    setCoupons((c) => c.filter((x) => x.code !== code));
  }

  async function toggleActive(code: string) {
    await couponsApi.toggleActive(code);
    setCoupons((c) => c.map((x) => x.code === code ? { ...x, active: !x.active } : x));
  }

  function getDiscountLabel(c: Coupon) {
    switch (c.type) {
      case 'percentage': return `${c.discount * 100}% off`;
      case 'fixed': return `₹${c.discount} off`;
      case 'free_shipping': return 'Free Shipping';
      case 'buy_x_get_y': return `Buy ${c.buyQuantity} Get ${c.getQuantity} Free`;
      case 'buy_x_get_percent': return `Buy ${c.buyQuantity} Get ${c.getQuantity} at ${c.getDiscount * 100}% off`;
      case 'flash_sale': return `${c.discount * 100}% off (Flash)`;
      case 'tiered': return `${c.tiers.length} tiers`;
      case 'cart_threshold': return `₹${c.discount} off`;
      case 'free_gift': return `Free ${c.giftProductName}`;
      case 'volume': return `${c.discount * 100}% off (${c.volumeThreshold}+ items)`;
      case 'referral': return `₹${c.referralReward} for both`;
      case 'loyalty': return `${c.loyaltyPointsMultiplier}x points`;
      case 'birthday': return `${c.discount * 100}% off`;
      case 'clearance': return `${c.discount * 100}% off`;
      case 'seasonal': return `${c.discount * 100}% off`;
      default: return `${c.discount * 100}% off`;
    }
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold">{filtered.length} Coupons</h2>
        <button onClick={() => setShowAdd(true)} className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ New Coupon</button>
      </div>

      {/* Filters */}
      <div className="flex flex-wrap gap-3">
        <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search code or description..." className="w-64 rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-indigo-500" />
        <select value={filterType} onChange={(e) => setFilterType(e.target.value)} className="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none">
          <option value="">All Types</option>
          {COUPON_TYPES.map((t) => <option key={t.value} value={t.value}>{t.icon} {t.label}</option>)}
        </select>
        <select value={filterStatus} onChange={(e) => setFilterStatus(e.target.value)} className="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="expired">Expired</option>
          <option value="scheduled">Scheduled</option>
        </select>
      </div>

      {/* Coupons Grid */}
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {filtered.map((c) => {
          const days = daysRemaining(c);
          const typeInfo = COUPON_TYPES.find((t) => t.value === c.type);
          return (
            <div key={c.code} className="rounded-xl border bg-white shadow-sm overflow-hidden">
              {/* Badge header */}
              <div className="relative px-4 py-3" style={{ backgroundColor: c.bgColor || '#6366f1' }}>
                <span className="text-xs font-bold tracking-wider" style={{ color: c.textColor || '#fff' }}>
                  {c.badge || typeInfo?.icon || '🏷️'} {c.code}
                </span>
                <span className="absolute right-3 top-3 text-xs font-bold" style={{ color: c.textColor || '#fff' }}>
                  {getDiscountLabel(c)}
                </span>
              </div>

              <div className="p-4 space-y-3">
                <p className="text-sm text-gray-600 line-clamp-2">{c.description}</p>

                {/* Stats row */}
                <div className="grid grid-cols-3 gap-2 text-center text-xs">
                  <div className="rounded-lg bg-gray-50 p-2">
                    <p className="font-bold text-gray-800">{c.usedCount}</p>
                    <p className="text-gray-500">Used</p>
                  </div>
                  <div className="rounded-lg bg-gray-50 p-2">
                    <p className="font-bold text-gray-800">{c.usageLimit || '∞'}</p>
                    <p className="text-gray-500">Limit</p>
                  </div>
                  <div className="rounded-lg bg-gray-50 p-2">
                    <p className="font-bold text-gray-800">{c.minOrder ? `₹${c.minOrder}` : '—'}</p>
                    <p className="text-gray-500">Min</p>
                  </div>
                </div>

                {/* Details */}
                <div className="space-y-1 text-xs text-gray-500">
                  <div className="flex justify-between">
                    <span>Type</span>
                    <span className="font-medium text-gray-700">{typeInfo?.label || c.type}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Applies to</span>
                    <span className="font-medium text-gray-700">{APPLIES_TO.find((a) => a.value === c.appliesTo)?.label || c.appliesTo}</span>
                  </div>
                  {c.validFrom && c.validUntil && (
                    <div className="flex justify-between">
                      <span>Valid</span>
                      <span className="font-medium text-gray-700">{c.validFrom} → {c.validUntil}</span>
                    </div>
                  )}
                  {days !== null && days > 0 && (
                    <div className="flex justify-between">
                      <span>Expires in</span>
                      <span className={`font-medium ${days <= 7 ? 'text-red-600' : 'text-gray-700'}`}>{days} days</span>
                    </div>
                  )}
                  {c.perUserLimit > 0 && (
                    <div className="flex justify-between">
                      <span>Per user</span>
                      <span className="font-medium text-gray-700">{c.perUserLimit}x</span>
                    </div>
                  )}
                  {c.stackable && (
                    <div className="flex justify-between">
                      <span>Stackable</span>
                      <span className="font-medium text-green-600">Yes</span>
                    </div>
                  )}
                  {c.totalDiscountGiven > 0 && (
                    <div className="flex justify-between">
                      <span>Total discount given</span>
                      <span className="font-medium text-gray-700">₹{c.totalDiscountGiven.toLocaleString()}</span>
                    </div>
                  )}
                </div>

                {/* Status badge */}
                <div className="flex items-center justify-between">
                  <span className={`rounded-full px-2.5 py-0.5 text-xs font-medium ${getStatusColor(c)}`}>
                    {isExpired(c) ? 'Expired' : isScheduled(c) ? 'Scheduled' : c.active ? 'Active' : 'Inactive'}
                  </span>
                  <div className="flex gap-2">
                    <button onClick={() => { setEditCoupon(c); setShowAdd(true); }} className="text-xs text-indigo-600 hover:underline">Edit</button>
                    <button onClick={() => toggleActive(c.code)} className="text-xs text-gray-600 hover:underline">{c.active ? 'Disable' : 'Enable'}</button>
                    <button onClick={() => handleDelete(c.code)} className="text-xs text-red-600 hover:underline">Delete</button>
                  </div>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {filtered.length === 0 && <p className="text-center text-gray-400 py-8">No coupons found</p>}

      {/* Add/Edit Modal */}
      <CouponModal
        isOpen={showAdd}
        coupon={editCoupon}
        onClose={() => { setShowAdd(false); setEditCoupon(null); }}
        onSave={async (data) => {
          if (editCoupon) { await couponsApi.update(editCoupon.code, data); }
          else { await couponsApi.create(data); }
          couponsApi.getAll().then(({ data }) => setCoupons(data || []));
          setShowAdd(false); setEditCoupon(null);
        }}
      />
    </div>
  );
}

function CouponModal({ isOpen, coupon, onClose, onSave }: { isOpen: boolean; coupon: Coupon | null; onClose: () => void; onSave: (data: Partial<Coupon>) => void }) {
  const [tab, setTab] = useState<'basic' | 'rules' | 'restrictions' | 'display'>('basic');
  const [form, setForm] = useState<Partial<Coupon>>(() => coupon ? { ...coupon } : {
    code: '', description: '', type: 'percentage' as CouponType, status: 'active', active: true,
    discount: 10, discountType: 'percentage',
    buyQuantity: 0, getQuantity: 1, getDiscount: 1,
    minOrder: 0, maxDiscount: 0, maxOrder: 0,
    usageLimit: 0, usedCount: 0, perUserLimit: 0,
    validFrom: '', validUntil: '', validDays: [], validTimeFrom: '', validTimeTo: '',
    isSeasonal: false, seasonTag: '',
    appliesTo: 'all' as CouponAppliesTo, productIds: [], categoryIds: [], collectionIds: [], tags: [],
    excludeProductIds: [], excludeCategoryIds: [],
    customerEmails: [], customerGroups: [], isFirstOrderOnly: false, isRepeatOnly: false,
    minCustomerOrders: 0, minCustomerSpent: 0,
    tiers: [], volumeThreshold: 3,
    giftProductId: '', giftProductName: '', giftQuantity: 1,
    referralReward: 500, refereeDiscount: 500, loyaltyPointsMultiplier: 2,
    badge: '', bgColor: '#6366f1', textColor: '#ffffff', bannerImage: '',
    stackable: false, priority: 10,
  });

  if (!isOpen) return null;

  const showBOGO = form.type === 'buy_x_get_y' || form.type === 'buy_x_get_percent';
  const showTiered = form.type === 'tiered';
  const showGift = form.type === 'free_gift';
  const showReferral = form.type === 'referral';
  const showLoyalty = form.type === 'loyalty';
  const showVolume = form.type === 'volume';

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div className="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-2xl">
        <div className="sticky top-0 z-10 border-b bg-white px-6 py-4">
          <h3 className="text-lg font-semibold">{coupon ? 'Edit' : 'Create'} Coupon</h3>
        </div>

        {/* Tabs */}
        <div className="flex gap-1 border-b px-6">
          {(['basic', 'rules', 'restrictions', 'display'] as const).map((t) => (
            <button key={t} onClick={() => setTab(t)} className={`px-4 py-2.5 text-sm font-medium border-b-2 transition-colors ${tab === t ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'}`}>
              {t === 'basic' ? 'Basic' : t === 'rules' ? 'Discount Rules' : t === 'restrictions' ? 'Restrictions' : 'Display'}
            </button>
          ))}
        </div>

        <div className="px-6 py-4 space-y-4">
          {/* Basic */}
          {tab === 'basic' && (
            <>
              <div className="grid grid-cols-2 gap-4">
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Code *</label><input required value={form.code || ''} onChange={(e) => setForm({ ...form, code: e.target.value.toUpperCase() })} className="w-full rounded-lg border px-3 py-2 text-sm font-mono uppercase" /></div>
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Type *</label>
                  <select value={form.type} onChange={(e) => setForm({ ...form, type: e.target.value as CouponType })} className="w-full rounded-lg border px-3 py-2 text-sm">
                    {COUPON_TYPES.map((t) => <option key={t.value} value={t.value}>{t.icon} {t.label}</option>)}
                  </select>
                </div>
              </div>
              <div><label className="mb-1 block text-xs font-medium text-gray-600">Description</label><input value={form.description || ''} onChange={(e) => setForm({ ...form, description: e.target.value })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>

              {/* Type-specific discount fields */}
              {(form.type === 'percentage' || form.type === 'flash_sale' || form.type === 'seasonal' || form.type === 'birthday' || form.type === 'clearance' || form.type === 'category_percent') && (
                <div className="grid grid-cols-2 gap-4">
                  <div><label className="mb-1 block text-xs font-medium text-gray-600">Discount %</label><input type="number" value={(form.discount || 0) * 100} onChange={(e) => setForm({ ...form, discount: Number(e.target.value) / 100 })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                  <div><label className="mb-1 block text-xs font-medium text-gray-600">Max Discount (₹)</label><input type="number" value={form.maxDiscount || 0} onChange={(e) => setForm({ ...form, maxDiscount: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                </div>
              )}
              {(form.type === 'fixed' || form.type === 'cart_threshold' || form.type === 'referral') && (
                <div className="grid grid-cols-2 gap-4">
                  <div><label className="mb-1 block text-xs font-medium text-gray-600">Discount Amount (₹)</label><input type="number" value={form.discount || 0} onChange={(e) => setForm({ ...form, discount: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                  <div><label className="mb-1 block text-xs font-medium text-gray-600">Min Order (₹)</label><input type="number" value={form.minOrder || 0} onChange={(e) => setForm({ ...form, minOrder: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                </div>
              )}

              {/* BOGO fields */}
              {showBOGO && (
                <div className="rounded-lg border border-amber-200 bg-amber-50 p-4 space-y-3">
                  <h4 className="text-sm font-semibold text-amber-800">Buy X Get Y Rules</h4>
                  <div className="grid grid-cols-3 gap-4">
                    <div><label className="mb-1 block text-xs font-medium text-gray-600">Buy Quantity</label><input type="number" min="1" value={form.buyQuantity || 1} onChange={(e) => setForm({ ...form, buyQuantity: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                    <div><label className="mb-1 block text-xs font-medium text-gray-600">Get Quantity</label><input type="number" min="1" value={form.getQuantity || 1} onChange={(e) => setForm({ ...form, getQuantity: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                    <div><label className="mb-1 block text-xs font-medium text-gray-600">Get Discount %</label><input type="number" min="0" max="100" value={(form.getDiscount || 0) * 100} onChange={(e) => setForm({ ...form, getDiscount: Number(e.target.value) / 100 })} className="w-full rounded-lg border px-3 py-2 text-sm" placeholder="100 = free" /></div>
                  </div>
                </div>
              )}

              {/* Volume fields */}
              {showVolume && (
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Minimum Quantity for Discount</label><input type="number" min="2" value={form.volumeThreshold || 3} onChange={(e) => setForm({ ...form, volumeThreshold: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
              )}

              {/* Free Gift fields */}
              {showGift && (
                <div className="rounded-lg border border-pink-200 bg-pink-50 p-4 space-y-3">
                  <h4 className="text-sm font-semibold text-pink-800">Free Gift Settings</h4>
                  <div className="grid grid-cols-2 gap-4">
                    <div><label className="mb-1 block text-xs font-medium text-gray-600">Gift Product Name</label><input value={form.giftProductName || ''} onChange={(e) => setForm({ ...form, giftProductName: e.target.value })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                    <div><label className="mb-1 block text-xs font-medium text-gray-600">Gift Quantity</label><input type="number" min="1" value={form.giftQuantity || 1} onChange={(e) => setForm({ ...form, giftQuantity: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                  </div>
                </div>
              )}

              {/* Referral fields */}
              {showReferral && (
                <div className="rounded-lg border border-green-200 bg-green-50 p-4 space-y-3">
                  <h4 className="text-sm font-semibold text-green-800">Referral Rewards</h4>
                  <div className="grid grid-cols-2 gap-4">
                    <div><label className="mb-1 block text-xs font-medium text-gray-600">Referrer Reward (₹)</label><input type="number" value={form.referralReward || 0} onChange={(e) => setForm({ ...form, referralReward: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                    <div><label className="mb-1 block text-xs font-medium text-gray-600">Referee Discount (₹)</label><input type="number" value={form.refereeDiscount || 0} onChange={(e) => setForm({ ...form, refereeDiscount: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                  </div>
                </div>
              )}

              {/* Loyalty fields */}
              {showLoyalty && (
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Points Multiplier (e.g. 2 = double points)</label><input type="number" min="1" step="0.5" value={form.loyaltyPointsMultiplier || 2} onChange={(e) => setForm({ ...form, loyaltyPointsMultiplier: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
              )}
            </>
          )}

          {/* Rules */}
          {tab === 'rules' && (
            <>
              <div className="grid grid-cols-2 gap-4">
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Min Order (₹)</label><input type="number" value={form.minOrder || 0} onChange={(e) => setForm({ ...form, minOrder: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Max Discount (₹)</label><input type="number" value={form.maxDiscount || 0} onChange={(e) => setForm({ ...form, maxDiscount: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
              </div>
              <div className="grid grid-cols-3 gap-4">
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Usage Limit (0=unlimited)</label><input type="number" value={form.usageLimit || 0} onChange={(e) => setForm({ ...form, usageLimit: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Per User Limit (0=unlimited)</label><input type="number" value={form.perUserLimit || 0} onChange={(e) => setForm({ ...form, perUserLimit: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Priority (higher = first)</label><input type="number" value={form.priority || 10} onChange={(e) => setForm({ ...form, priority: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
              </div>
              <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={form.stackable || false} onChange={(e) => setForm({ ...form, stackable: e.target.checked })} className="rounded" /> Stackable with other coupons</label>

              <div className="grid grid-cols-2 gap-4">
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Valid From</label><input type="date" value={form.validFrom || ''} onChange={(e) => setForm({ ...form, validFrom: e.target.value })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Valid Until</label><input type="date" value={form.validUntil || ''} onChange={(e) => setForm({ ...form, validUntil: e.target.value })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Time From</label><input type="time" value={form.validTimeFrom || ''} onChange={(e) => setForm({ ...form, validTimeFrom: e.target.value })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Time To</label><input type="time" value={form.validTimeTo || ''} onChange={(e) => setForm({ ...form, validTimeTo: e.target.value })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
              </div>

              {/* Tiered discount tiers */}
              {showTiered && (
                <div className="rounded-lg border border-cyan-200 bg-cyan-50 p-4 space-y-3">
                  <div className="flex items-center justify-between">
                    <h4 className="text-sm font-semibold text-cyan-800">Discount Tiers</h4>
                    <button type="button" onClick={() => setForm({ ...form, tiers: [...(form.tiers || []), { minAmount: 0, discount: 0.1, label: '' }] })} className="text-xs text-cyan-700 hover:underline">+ Add Tier</button>
                  </div>
                  {(form.tiers || []).map((tier, i) => (
                    <div key={i} className="flex items-center gap-2">
                      <input placeholder="Min ₹" type="number" value={tier.minAmount} onChange={(e) => { const next = [...(form.tiers || [])]; next[i] = { ...next[i], minAmount: Number(e.target.value) }; setForm({ ...form, tiers: next }); }} className="w-24 rounded border px-2 py-1.5 text-xs" />
                      <input placeholder="Discount %" type="number" value={tier.discount * 100} onChange={(e) => { const next = [...(form.tiers || [])]; next[i] = { ...next[i], discount: Number(e.target.value) / 100 }; setForm({ ...form, tiers: next }); }} className="w-20 rounded border px-2 py-1.5 text-xs" />
                      <input placeholder="Label" value={tier.label} onChange={(e) => { const next = [...(form.tiers || [])]; next[i] = { ...next[i], label: e.target.value }; setForm({ ...form, tiers: next }); }} className="flex-1 rounded border px-2 py-1.5 text-xs" />
                      <button type="button" onClick={() => setForm({ ...form, tiers: (form.tiers || []).filter((_, j) => j !== i) })} className="text-xs text-red-500">X</button>
                    </div>
                  ))}
                </div>
              )}
            </>
          )}

          {/* Restrictions */}
          {tab === 'restrictions' && (
            <>
              <div><label className="mb-1 block text-xs font-medium text-gray-600">Applies To</label>
                <select value={form.appliesTo || 'all'} onChange={(e) => setForm({ ...form, appliesTo: e.target.value as CouponAppliesTo })} className="w-full rounded-lg border px-3 py-2 text-sm">
                  {APPLIES_TO.map((a) => <option key={a.value} value={a.value}>{a.label}</option>)}
                </select>
              </div>
              {form.appliesTo === 'products' && <div><label className="mb-1 block text-xs font-medium text-gray-600">Product IDs (comma separated)</label><input value={(form.productIds || []).join(', ')} onChange={(e) => setForm({ ...form, productIds: e.target.value.split(',').map((s) => s.trim()).filter(Boolean) })} className="w-full rounded-lg border px-3 py-2 text-sm" placeholder="p1, p2, p3" /></div>}
              {form.appliesTo === 'categories' && <div><label className="mb-1 block text-xs font-medium text-gray-600">Category IDs (comma separated)</label><input value={(form.categoryIds || []).join(', ')} onChange={(e) => setForm({ ...form, categoryIds: e.target.value.split(',').map((s) => s.trim()).filter(Boolean) })} className="w-full rounded-lg border px-3 py-2 text-sm" placeholder="cat-toys, cat-brass" /></div>}
              {form.appliesTo === 'tags' && <div><label className="mb-1 block text-xs font-medium text-gray-600">Tags (comma separated)</label><input value={(form.tags || []).join(', ')} onChange={(e) => setForm({ ...form, tags: e.target.value.split(',').map((s) => s.trim()).filter(Boolean) })} className="w-full rounded-lg border px-3 py-2 text-sm" placeholder="handmade, traditional" /></div>}

              <div><label className="mb-1 block text-xs font-medium text-gray-600">Exclude Product IDs (comma separated)</label><input value={(form.excludeProductIds || []).join(', ')} onChange={(e) => setForm({ ...form, excludeProductIds: e.target.value.split(',').map((s) => s.trim()).filter(Boolean) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>

              <div className="grid grid-cols-2 gap-4">
                <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={form.isFirstOrderOnly || false} onChange={(e) => setForm({ ...form, isFirstOrderOnly: e.target.checked })} className="rounded" /> First order only</label>
                <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={form.isRepeatOnly || false} onChange={(e) => setForm({ ...form, isRepeatOnly: e.target.checked })} className="rounded" /> Repeat purchase only</label>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Min Customer Orders</label><input type="number" value={form.minCustomerOrders || 0} onChange={(e) => setForm({ ...form, minCustomerOrders: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Min Customer Spent (₹)</label><input type="number" value={form.minCustomerSpent || 0} onChange={(e) => setForm({ ...form, minCustomerSpent: Number(e.target.value) })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>
              </div>
              <div><label className="mb-1 block text-xs font-medium text-gray-600">Customer Groups (comma separated)</label><input value={(form.customerGroups || []).join(', ')} onChange={(e) => setForm({ ...form, customerGroups: e.target.value.split(',').map((s) => s.trim()).filter(Boolean) })} className="w-full rounded-lg border px-3 py-2 text-sm" placeholder="first_time, loyal, vip" /></div>
            </>
          )}

          {/* Display */}
          {tab === 'display' && (
            <>
              <div className="grid grid-cols-3 gap-4">
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Badge Text</label><input value={form.badge || ''} onChange={(e) => setForm({ ...form, badge: e.target.value })} className="w-full rounded-lg border px-3 py-2 text-sm" placeholder="HOT, NEW USER, etc." /></div>
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Badge BG Color</label><input type="color" value={form.bgColor || '#6366f1'} onChange={(e) => setForm({ ...form, bgColor: e.target.value })} className="h-10 w-full rounded-lg border px-1 py-1" /></div>
                <div><label className="mb-1 block text-xs font-medium text-gray-600">Badge Text Color</label><input type="color" value={form.textColor || '#ffffff'} onChange={(e) => setForm({ ...form, textColor: e.target.value })} className="h-10 w-full rounded-lg border px-1 py-1" /></div>
              </div>
              <div><label className="mb-1 block text-xs font-medium text-gray-600">Banner Image URL</label><input value={form.bannerImage || ''} onChange={(e) => setForm({ ...form, bannerImage: e.target.value })} className="w-full rounded-lg border px-3 py-2 text-sm" /></div>

              {/* Preview */}
              <div className="rounded-lg border bg-gray-50 p-4">
                <p className="mb-2 text-xs font-medium text-gray-500">Preview</p>
                <div className="flex items-center gap-3">
                  <span className="rounded-full px-3 py-1 text-xs font-bold" style={{ backgroundColor: form.bgColor || '#6366f1', color: form.textColor || '#fff' }}>
                    {form.badge || 'BADGE'} {form.code}
                  </span>
                  <span className="text-sm font-semibold">{form.description}</span>
                </div>
              </div>
            </>
          )}
        </div>

        {/* Actions */}
        <div className="sticky bottom-0 border-t bg-white px-6 py-4 flex justify-end gap-2">
          <button onClick={onClose} className="rounded-lg border px-4 py-2 text-sm">Cancel</button>
          <button onClick={() => onSave(form)} className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Save Coupon</button>
        </div>
      </div>
    </div>
  );
}
