---
description: Run a structural health check across the whole theme - PHP syntax, layering violations, JSON validity, and dead-reference sweep.
---

Run a full structural check of the VintageSoulTheme codebase and report
findings, grouped by severity. Do not fix anything unless asked - report first.

1. **PHP syntax**: run `php -l` on every `.php` file in the theme. Report any failure verbatim.
2. **JSON validity**: decode every `.json` file under `config/` and `data/`; report any parse error.
3. **Layering violations** (see `ARCHITECTURE.md`): search for
   - a `components/` or `template-parts/` file calling a class under `src/Repositories/` or `src/DataProviders/` directly (should go through a Service);
   - a root template file (`front-page.php`, `page.php`, etc.) containing a `new WP_Query`, direct `$wpdb` usage, or more than a Controller call + render;
   - any file outside `src/Services/AssetService.php` calling `wp_enqueue_style`/`wp_enqueue_script` directly.
4. **Raw values in CSS**: grep `assets/css/components/`, `assets/css/pages/` (not `variables.css` itself) for hex colors (`#[0-9a-fA-F]{3,8}`) or bare pixel values outside `var(--...)` - flag any found as a design-system violation.
5. **Dead references**: for any file recently renamed/moved, grep the whole theme for the old path/class name.
6. **`.gitkeep` audit**: list any `.gitkeep` file whose directory now contains a real file - flag it as removable.

Report a short summary (counts per category) followed by the specific
findings with file:line references.
