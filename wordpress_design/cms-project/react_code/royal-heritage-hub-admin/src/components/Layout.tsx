import { useState } from 'react';
import { Link, useLocation, Outlet } from 'react-router-dom';
import { useAuthStore } from '@/store/useAuthStore';
import { STORAGE_KEYS } from '@/config/storage';
import { ADMIN_CONFIG } from '@/config/admin-config';

function cn(...classes: (string | boolean | undefined | null)[]) { return classes.filter(Boolean).join(' '); }

export function AdminLayout() {
  const location = useLocation();
  const { user, logout } = useAuthStore();
  const [collapsed, setCollapsed] = useState(() => localStorage.getItem(STORAGE_KEYS.sidebarCollapsed) === 'true');
  const { brand, navigation, colors } = ADMIN_CONFIG;

  function toggleCollapse() {
    setCollapsed((c) => { localStorage.setItem(STORAGE_KEYS.sidebarCollapsed, String(!c)); return !c; });
  }

  return (
    <div className={cn('flex min-h-screen', colors.pageBg === '#f9fafb' ? 'bg-gray-50' : '')} style={{ backgroundColor: colors.pageBg }}>
      {/* Sidebar */}
      <aside className={cn(
        'fixed inset-y-0 left-0 z-40 flex flex-col text-white transition-all duration-300',
        collapsed ? 'w-16' : 'w-60'
      )} style={{ backgroundColor: colors.sidebar }}>
        <div className="flex h-14 items-center justify-between border-b border-white/10 px-4">
          {!collapsed && <span className="font-bold text-sm">{brand.name}</span>}
          <button onClick={toggleCollapse} className="rounded p-1 hover:bg-white/10">
            <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="2">
              {collapsed ? <path d="M9 5l7 7-7 7" /> : <path d="M15 19l-7-7 7-7" />}
            </svg>
          </button>
        </div>
        <nav className="flex-1 overflow-y-auto py-2">
          {navigation.map((item) => (
            <Link
              key={item.path}
              to={item.path}
              className={cn(
                'flex items-center gap-3 px-4 py-2.5 text-sm transition-colors',
                location.pathname.startsWith(item.path) ? 'text-white' : 'hover:text-white'
              )}
              style={{
                backgroundColor: location.pathname.startsWith(item.path) ? colors.sidebarActive : undefined,
                color: location.pathname.startsWith(item.path) ? 'white' : colors.sidebarText,
              }}
              onMouseEnter={(e) => { if (!location.pathname.startsWith(item.path)) e.currentTarget.style.backgroundColor = colors.sidebarHover; }}
              onMouseLeave={(e) => { if (!location.pathname.startsWith(item.path)) e.currentTarget.style.backgroundColor = 'transparent'; }}
            >
              <svg viewBox="0 0 24 24" className="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" strokeWidth="1.5">
                <path d={item.icon} strokeLinecap="round" strokeLinejoin="round" />
              </svg>
              {!collapsed && <span>{item.label}</span>}
            </Link>
          ))}
        </nav>
        <div className="border-t border-white/10 p-3">
          {!collapsed && (
            <div className="flex items-center gap-2 text-xs" style={{ color: colors.sidebarTextMuted }}>
              <div className="h-7 w-7 rounded-full bg-white/10 flex items-center justify-center text-xs font-bold">{user?.name?.[0]}</div>
              <div className="min-w-0 flex-1">
                <p className="truncate text-white">{user?.name}</p>
                <p className="truncate">{user?.role}</p>
              </div>
            </div>
          )}
        </div>
      </aside>

      {/* Main */}
      <div className={cn('flex-1 transition-all duration-300', collapsed ? 'ml-16' : 'ml-60')}>
        {/* Top bar */}
        <header className="sticky top-0 z-30 flex h-14 items-center justify-between border-b bg-white px-6" style={{ borderColor: colors.headerBorder, backgroundColor: colors.headerBg }}>
          <h1 className="text-lg font-semibold text-gray-800">
            {navigation.find((n) => location.pathname.startsWith(n.path))?.label || 'Dashboard'}
          </h1>
          <div className="flex items-center gap-4">
            <a href="/" target="_blank" className="text-xs text-gray-500 hover:text-gray-700">View Store</a>
            <button onClick={logout} className="text-xs text-red-500 hover:text-red-700">Logout</button>
          </div>
        </header>
        <main className="p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
