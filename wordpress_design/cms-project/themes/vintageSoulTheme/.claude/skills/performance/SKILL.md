---
name: performance
description: Use when adding assets, images, or queries, or when auditing this theme for load-time/runtime performance.
---

# Performance (VintageSoulTheme)

Full conventions: `../../../docs/performance-seo.md` ("Performance").

## Before adding any asset

- Does it belong in the global `config/assets.php` registry, or should it
  be enqueued only on the specific page/Controller that needs it? Global
  registration is for things every page genuinely uses.
- Is there already a handle for this in `config/assets.php`? Never enqueue
  the same library/script twice under different handles.
- Images: `ImageHelper`/`wp_get_attachment_image()`, not a raw `<img src>`
  - this is what provides `srcset`/lazy-loading automatically.

## Before adding a dependency

Does WordPress core or this theme's existing `src/Support/` classes
already do this? A new JS library or PHP package needs a real
justification beyond convenience - see `CLAUDE.md` rule on avoiding
unnecessary dependencies.

## Before adding a query

- Route it through a Repository/`WpQueryProvider`, not an inline
  `new WP_Query()` in a template or component.
- `WpQueryProvider::posts()` already sets `no_found_rows` - don't remove
  it without a real need for pagination counts.
- Cache via `JsonFileProvider`'s pattern (decode once per request) for any
  new file-backed Data Provider.

## Auditing

`/performance-check` runs a repeatable pass: duplicate asset handles,
un-sized images, inline `<script>`/`<style>` in templates, and direct
`WP_Query`/`wp_enqueue_*` calls outside `AssetService`/a Repository.
