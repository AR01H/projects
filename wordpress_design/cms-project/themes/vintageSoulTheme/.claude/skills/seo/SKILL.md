---
name: seo
description: Use when implementing or checking SEO - metadata, heading structure, structured data - for a page, post type, or component in this theme.
---

# SEO (VintageSoulTheme)

Full conventions: `../../../docs/performance-seo.md`.

## Implementing SEO for a new page/context

1. Meta description: does this context have real content to summarize
   (an excerpt, a post)? Pass it through `SeoService::meta_description()`
   as the override - never invent one, never leave the config fallback to
   describe a specific page.
2. Title: WordPress core's `document_title_parts` filter + the theme's
   `title-tag` support (see `config/theme-support.php`) handle the base
   `<title>`; `Theme::title_separator()` controls the separator only.
   Don't hand-write a `<title>` tag anywhere.
3. Heading hierarchy: exactly one `<h1>` per page (the page/post title).
   Every component heading below it steps down logically - if a component
   needs a *visually* large heading that isn't semantically an `<h2>`,
   that's a CSS font-size token question, not a reason to use the wrong
   heading level.
4. Canonical/OG tags: add via a new `SeoService` method + a `wp_head`
   hook registered from `Theme::init()` - never inline in a template.

## Auditing existing SEO

Use the `/seo-check` command for a repeatable pass: heading hierarchy,
missing alt text, meta description presence, title length, and confirms
nothing calls `wp_title()`/hand-writes a `<title>` tag outside core.

## Structured data

Not implemented in this base - add it as a `SeoService` method scoped to
a real content type once one exists, not speculatively now (see
`docs/performance-seo.md`).
