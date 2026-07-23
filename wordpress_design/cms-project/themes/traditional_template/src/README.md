# `src/` — Feature classes (OOP)

Each **feature** lives in its own folder here as one or more classes. A feature
class is the **intermediate layer**: it holds the logic that decides *what* to
render and *how*, sitting between the page templates (pure UI) and the JSON data
(pure content). Templates stay dumb, content stays in JSON, and the wiring lives
in exactly one place — the class.

```
Page template (UI)  ──calls──▶  src/<Feature>/ class (logic)  ──reads──▶  admin/data/*.json (content)
     page-home.php                 NT_Section_Renderer                      page_sections.json
```

## Conventions

- **One folder per feature**: `src/Sections/`, `src/<NextFeature>/`, …
- **Class file naming**: `class-<kebab-name>.php` defining `NT_<StudlyName>`
  (matches the theme's existing `class-*.php` style in `includes/`).
- **Prefix**: classes `NT_`, methods lowercase_snake, constants `UPPER_CASE`.
- **Loading**: register the class file in `config/files.php` under `always`
  (before the thin wrapper that uses it). No autoloader needed.
- **Thin wrappers**: expose a short procedural helper (e.g. `nt_render_sections()`
  in `includes/site-helpers.php`) so templates read cleanly; the helper just
  delegates to the class.
- **Keep default/shared helper functions** in `core/helpers.php` (framework) and
  `includes/site-helpers.php` (this site) — not scattered across features.

## Features

| Folder | Class | Does | Data |
|---|---|---|---|
| `Sections/` | `NT_Section_Renderer` | Renders a page's ordered section list | `admin/data/page_sections.json` |

## Add a new feature

1. `src/<Feature>/class-<name>.php` → `class NT_<Name> { public static function ... }`.
2. Register it in `config/files.php` (`always`).
3. (optional) Add a thin wrapper in `includes/site-helpers.php`.
4. Put its content in `admin/data/<feature>.json`, read via `nt_data('<feature>')`.
5. Document it in the table above.
