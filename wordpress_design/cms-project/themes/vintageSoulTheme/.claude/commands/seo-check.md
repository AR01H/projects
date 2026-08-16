---
description: Audit SEO fundamentals across templates and components - headings, metadata, alt text, title handling.
---

Use the `seo` skill's checklist. Audit the theme (or the file(s)/URL the
user names as an argument, if given) and report findings - do not fix
unless asked.

1. **Heading hierarchy**: for each root template + the components it renders, confirm exactly one `<h1>` and no skipped levels.
2. **Meta description**: confirm `SeoService::meta_description()` / the `wp_head` hook is wired for every context that should have one, and that no template hand-writes a `<meta name="description">` tag.
3. **Title tag**: confirm nothing hand-writes a `<title>` tag or calls `wp_title()` directly outside WordPress core's own `title-tag` support + `Theme::title_separator()`.
4. **Alt text**: grep for `<img` usage that bypasses `ImageHelper`/`wp_get_attachment_image()`, and any `alt=""` that isn't clearly decorative.
5. **Internal linking**: check `UrlHelper::resolve()` is used for content-sourced links rather than raw string concatenation.

Report findings with file:line references, grouped by the checklist item above.
