import { useEffect, useState } from 'react';
import { settingsApi } from '@/api/settings';
import { footerApi } from '@/api/footer';
import type { StoreSettings, FooterData } from '@/types';

export default function SettingsPage() {
  const [settings, setSettings] = useState<StoreSettings | null>(null);
  const [footer, setFooter] = useState<FooterData | null>(null);
  const [loading, setLoading] = useState(true);
  const [saved, setSaved] = useState(false);

  useEffect(() => { load(); }, []);

  async function load() {
    setLoading(true);
    const [s, f] = await Promise.all([settingsApi.getAll(), footerApi.getAll()]);
    if (s.data) setSettings(s.data);
    if (f.data) setFooter(f.data);
    setLoading(false);
  }

  async function handleSave() {
    if (settings) await settingsApi.update(settings);
    if (footer) await footerApi.update(footer);
    setSaved(true);
    setTimeout(() => setSaved(false), 2000);
  }

  if (loading) return <p className="text-gray-500">Loading...</p>;
  if (!settings) return <p className="text-red-500">Failed to load settings</p>;

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h2 className="text-xl font-semibold">Store Settings</h2>
        <button onClick={handleSave} className="rounded bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">
          {saved ? 'Saved!' : 'Save All'}
        </button>
      </div>

      {/* Brand */}
      <section className="rounded-lg border bg-white p-6 shadow-sm">
        <h3 className="mb-4 font-medium">Brand Identity</h3>
        <div className="grid gap-3 sm:grid-cols-2">
          <input placeholder="Brand Name" value={settings.brand.name} onChange={(e) => setSettings({ ...settings, brand: { ...settings.brand, name: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
          <input placeholder="Tagline" value={settings.brand.tagline} onChange={(e) => setSettings({ ...settings, brand: { ...settings.brand, tagline: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
          <input placeholder="Short Name" value={settings.brand.shortName} onChange={(e) => setSettings({ ...settings, brand: { ...settings.brand, shortName: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
          <input placeholder="Logo URL" value={settings.brand.logo} onChange={(e) => setSettings({ ...settings, brand: { ...settings.brand, logo: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
        </div>
      </section>

      {/* Contact */}
      <section className="rounded-lg border bg-white p-6 shadow-sm">
        <h3 className="mb-4 font-medium">Contact</h3>
        <div className="grid gap-3 sm:grid-cols-2">
          <input placeholder="Phone" value={settings.contact.phone} onChange={(e) => setSettings({ ...settings, contact: { ...settings.contact, phone: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
          <input placeholder="Email" value={settings.contact.email} onChange={(e) => setSettings({ ...settings, contact: { ...settings.contact, email: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
          <input placeholder="Address" value={settings.contact.address} onChange={(e) => setSettings({ ...settings, contact: { ...settings.contact, address: e.target.value } })} className="col-span-2 rounded border px-3 py-2 text-sm" />
        </div>
      </section>

      {/* Shipping */}
      <section className="rounded-lg border bg-white p-6 shadow-sm">
        <h3 className="mb-4 font-medium">Shipping</h3>
        <div className="grid gap-3 sm:grid-cols-3">
          <div><label className="mb-1 block text-xs text-gray-500">Free Shipping Threshold</label><input type="number" value={settings.shipping.freeShippingThreshold} onChange={(e) => setSettings({ ...settings, shipping: { ...settings.shipping, freeShippingThreshold: Number(e.target.value) } })} className="w-full rounded border px-3 py-2 text-sm" /></div>
          <div><label className="mb-1 block text-xs text-gray-500">Default Shipping Charge</label><input type="number" value={settings.shipping.defaultShippingCharge} onChange={(e) => setSettings({ ...settings, shipping: { ...settings.shipping, defaultShippingCharge: Number(e.target.value) } })} className="w-full rounded border px-3 py-2 text-sm" /></div>
          <div><label className="mb-1 block text-xs text-gray-500">COD Charge</label><input type="number" value={settings.shipping.codCharge} onChange={(e) => setSettings({ ...settings, shipping: { ...settings.shipping, codCharge: Number(e.target.value) } })} className="w-full rounded border px-3 py-2 text-sm" /></div>
          <div><label className="mb-1 block text-xs text-gray-500">Min Delivery (days)</label><input type="number" value={settings.shipping.estimatedDeliveryMin} onChange={(e) => setSettings({ ...settings, shipping: { ...settings.shipping, estimatedDeliveryMin: Number(e.target.value) } })} className="w-full rounded border px-3 py-2 text-sm" /></div>
          <div><label className="mb-1 block text-xs text-gray-500">Max Delivery (days)</label><input type="number" value={settings.shipping.estimatedDeliveryMax} onChange={(e) => setSettings({ ...settings, shipping: { ...settings.shipping, estimatedDeliveryMax: Number(e.target.value) } })} className="w-full rounded border px-3 py-2 text-sm" /></div>
        </div>
      </section>

      {/* Currency */}
      <section className="rounded-lg border bg-white p-6 shadow-sm">
        <h3 className="mb-4 font-medium">Currency</h3>
        <div className="grid gap-3 sm:grid-cols-3">
          <input placeholder="Code" value={settings.currency.code} onChange={(e) => setSettings({ ...settings, currency: { ...settings.currency, code: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
          <input placeholder="Symbol" value={settings.currency.symbol} onChange={(e) => setSettings({ ...settings, currency: { ...settings.currency, symbol: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
          <input placeholder="Locale" value={settings.currency.locale} onChange={(e) => setSettings({ ...settings, currency: { ...settings.currency, locale: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
        </div>
      </section>

      {/* Social */}
      <section className="rounded-lg border bg-white p-6 shadow-sm">
        <h3 className="mb-4 font-medium">Social Links</h3>
        <div className="grid gap-3 sm:grid-cols-2">
          <input placeholder="Instagram" value={settings.social.instagram} onChange={(e) => setSettings({ ...settings, social: { ...settings.social, instagram: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
          <input placeholder="Facebook" value={settings.social.facebook} onChange={(e) => setSettings({ ...settings, social: { ...settings.social, facebook: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
          <input placeholder="Pinterest" value={settings.social.pinterest} onChange={(e) => setSettings({ ...settings, social: { ...settings.social, pinterest: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
          <input placeholder="YouTube" value={settings.social.youtube} onChange={(e) => setSettings({ ...settings, social: { ...settings.social, youtube: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
          <input placeholder="WhatsApp" value={settings.social.whatsapp} onChange={(e) => setSettings({ ...settings, social: { ...settings.social, whatsapp: e.target.value } })} className="rounded border px-3 py-2 text-sm" />
        </div>
      </section>
    </div>
  );
}
