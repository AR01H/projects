# IMPORTANT.md

Read this before building any React app for WordPress.

---

## Before `npm run build` — 2 Changes Required

### 1. BrowserRouter basename

In `src/main.tsx`:

```js
// ✅ Always use "/"
<BrowserRouter basename="/">
```

### 2. Vite base path

In `vite.config.ts`:

```js
// ✅ Always use "/"
base: '/',
```

---

## After Build

1. Copy `dist/` contents to `themes/react_template/build/`
2. Update `core/assets.php` — change the JS filename:
```php
private static string $react_js = '/build/assets/index-YOUR-NEW-HASH.js';
```

---

## Why

- WordPress serves at `/`, not `/your-app-name/`
- Vite outputs ESM modules — WordPress needs `type="module"` on the script tag
- The theme handles `type="module"` automatically via `RH_Assets`
