# Performance & SEO Conventions

## Performance

- No dependency (JS library, PHP package) is added unless it does
  something this theme genuinely cannot do simply on its own - see
  `functions.php`'s own note on skipping Composer.
- Every asset goes through `AssetService`/`config/assets.php` - this is
  what prevents the same stylesheet/script being enqueued twice from two
  different components.
- Page-specific CSS/JS is enqueued only on the page that needs it, never
  added to the global `config/assets.php` registry.
- Images: use `wp_get_attachment_image()`/`ImageHelper` (srcset + lazy
  loading come from WordPress core automatically) rather than a raw `<img
  src>` built by hand.
- `JsonFileProvider` caches a decoded file for the life of the request -
  any new Data Provider should do the same rather than re-reading/
  re-decoding on every call.

## SEO

- `SeoService` centralizes meta description and title-separator logic -
  don't `printf` a `<meta>` tag from a template; add a method to
  `SeoService` and hook it from `Theme::init()`.
- `config/seo.json` holds site-wide fallbacks only; a real per-post
  override (once a meta box or admin API field exists) must always be
  checked first and win over the fallback.
- Heading hierarchy: one `<h1>` per page (the page/post title), component
  headings step down from there - never skip a level for a visual-size
  reason (control heading *size* via CSS tokens, not by picking the wrong
  heading level).
- Structured data (schema.org) is intentionally not implemented yet - it
  should be added as its own `SeoService` method once real content/types
  exist to describe, not scaffolded speculatively now.
