# Architecture

Registry-driven WordPress theme with React SPA frontend.

---

## How It Works

```
config/*.php  →  core/*.php  →  WordPress  →  React (build/)
```

1. Config files return arrays (data)
2. Core classes loop over arrays (logic)
3. WordPress renders `<div id="root"></div>`
4. React mounts to `#root`, reads `window.rtSite`

---

## Boot Flow

```
functions.php → core/bootstrap.php
  ├─ Define RH_THEME_DIR, RH_THEME_URI
  ├─ Load core/*.php (9 engine classes)
  ├─ Load handlers/**/*.php
  └─ RH_Bootstrap::init()
       ├─ RH_Router::init()
       ├─ RH_Assets::init()
       ├─ RH_Ajax::init()
       ├─ RH_Rest::init()
       └─ RH_Admin::init()
```

---

## Classes

| Class | Purpose |
|-------|---------|
| `RH_Bootstrap` | Orchestrator |
| `RH_Config` | Load + cache config arrays |
| `RH_Helpers` | Utilities, options, data |
| `RH_Router` | Virtual routing |
| `RH_Assets` | CSS/JS loading, window.rtSite |
| `RH_Ajax` | AJAX dispatcher |
| `RH_Rest` | REST registration |
| `RH_Admin` | Admin panel |
| `RH_Database` | Custom tables |

---

## Adding Pages

React handles all routing. Pages are virtual.

1. Add to `config/pages.php`
2. React reads `window.rtSite.page` and renders matching component

---

## Adding AJAX

1. `config/ajax.php` — register action
2. `handlers/ajax/{name}.php` — write handler class
3. Self-register at bottom of file

---

## Adding REST Route

1. `config/rest.php` — register route
2. `handlers/rest/{name}.php` — write handler class
3. Self-register at bottom of file

---

## Admin Panel

```
Theme
├── Dashboard (Overview, Tools)
├── Settings (General)
```

---

## window.rtSite

```js
{
    ajaxUrl, restUrl, restNonce, themeUrl,
    brand: { name, tagline, logo },
    page: "home",
    nonces: { action: "nonce" }
}
```

---

## Prefix

| Find | Replace |
|------|---------|
| `rh_` | `xx_` |
| `RH_` | `XX_` |
| `rh-` | `xx-` |
| `rtSite` | `xxSite` |
