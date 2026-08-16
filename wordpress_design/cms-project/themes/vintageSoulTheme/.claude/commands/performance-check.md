---
description: Audit asset loading, queries, and dependencies for performance issues.
---

Use the `performance` skill's checklist. Audit the theme and report
findings - do not fix unless asked.

1. **Duplicate/unnecessary assets**: check `config/assets.php` for duplicate handles, and grep the theme for any `wp_enqueue_style`/`wp_enqueue_script` call outside `AssetService` (a sign of an asset loaded twice, or outside the registry).
2. **Unsized/unoptimized images**: grep for `<img` usage that bypasses `ImageHelper`/`wp_get_attachment_image()` (loses automatic `srcset`/`width`/`height`/lazy-loading).
3. **Direct queries**: grep for `new WP_Query(` outside `src/DataProviders/WpQueryProvider.php`, and any raw `$wpdb` usage outside a Repository.
4. **Inline assets**: grep templates/components for inline `<script>`/`<style>` blocks that should be a file under `assets/`.
5. **Dependencies**: check for any `composer.json`/`package.json`/CDN `<script src="https://...">` and confirm each has a genuine justification documented (per `CLAUDE.md`'s "avoid unnecessary dependencies" rule).

Report findings with file:line references, grouped by the checklist item above.
