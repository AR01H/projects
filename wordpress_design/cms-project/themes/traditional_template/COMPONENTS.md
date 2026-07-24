# Component Catalogue

Every visible band on this site is one file in `components/` reading one file in
`admin/data/`. Nothing is hardcoded in a template.

**To change the site you edit JSON. To re-arrange the site you edit one JSON.**

- *What a section says* → `admin/data/<its data file>.json`
- *Which sections a page has, in what order* → `admin/data/page_sections.json`
- *Whether a section shows at all* → `admin/data/sections.json` (`"key": false`)

See `ARCHITECTURE.md` for the engine behind this. This file is the parts list.

---

## 1. The three knobs on every section

A section entry in `page_sections.json` looks like this:

```jsonc
{
  "component": "reviews",              // required → components/reviews.php
  "key": "franchise_reviews",          // optional → toggle in sections.json
  "args": { "source": "reviews_franchise" },  // optional → which JSON to read
  "variant": "dark"                    // optional → which look to wear
}
```

### `args.source` — same component, different content

Most components accept `source`. It swaps which `admin/data/*.json` they read,
so one component can appear on five pages saying five different things:

```jsonc
{ "component": "faqs", "args": { "source": "faqs_franchise" } }   // franchise page
{ "component": "faqs", "args": { "source": "faqs_events" } }      // events page
```

To give a page its own copy: duplicate the JSON file, rename it, point `source`
at it. No PHP is touched.

### `variant` — same component, different look

`NT_Section_Renderer` wraps the section in `.nt-variant.nt-variant--<name>`.
The variants are defined once in `assets/css/vintage.css` (SECTION VARIANTS
block) and work on **any** component:

| Variant | Effect |
|---|---|
| *(none)* | Standard parchment band |
| `soft` | Lighter fill, softer separation from the band above |
| `dark` | Deep-green band, cream text, gold accents |
| `flat` | No frame or shadow — blends into the page |
| `compact` | Reduced vertical padding for a tight rhythm |
| `roomy` | Extra vertical padding for a hero-weight band |
| `split` | Two-column layout where the component supports it |

Alternating variants down a page is what stops eight parchment sections in a row
from reading as one flat wall.

### `key` — the on/off switch

Any `key` absent from `sections.json` **defaults to visible**. Add it with
`false` to hide the section without deleting the entry.

---

## 2. Catalogue

`source?` = accepts `args.source` to read a different JSON file.

### Structural / hero

| Component | Reads | source? | Used on |
|---|---|:--:|---|
| `parts/page_header` | `page_headers.json[<header>]` | via `header` | products, events, contact |
| `home-banner` | `home_banner.json` | – | home |
| `media-carousel` | `home_media.json` | – | home |
| `ticker` | `ticker.json` | ✅ | home, contact |

### Story & credibility

| Component | Reads | source? | Used on |
|---|---|:--:|---|
| `our-story-home` | `content.json` | – | home |
| `company-history` | `history.json` | – | about |
| `milestones` | `milestones.json` | ✅ | about *(soft)* |
| `values` | `values.json` | ✅ | about, products *(compact)* |
| `stats-bar` | `stats.json` | ✅ | home |
| `feature-badges` | `feature_badges.json` | ✅ | home, products *(compact)* |
| `features-certifications` | `certifications.json` | – | about, franchise |
| `team` | `team.json` | ✅ | about *(soft)*, events *(soft)* |
| `reviews` | `reviews.json` | ✅ | home, gallery *(flat)*, franchise *(dark)*, events *(soft)*, order *(dark)* |

### Product & menu

| Component | Reads | source? | Used on |
|---|---|:--:|---|
| `our-drinks` | `content.json` | – | home |
| `signature-flavours` | `signature_flavours.json` | ✅ | home, products |
| `product-menu` | `flavours.json` | – | products |
| `products-list` | `flavours.json` | – | products |
| `product-benefits` | `benefits_items.json` | – | products, order |
| `product-experience` | `experience_data.json` | – | about |
| `filter-cards` | `filter_cards.json` | ✅ | products |

### Commercial

| Component | Reads | source? | Used on |
|---|---|:--:|---|
| `pricing-tiers` | `pricing_tiers.json` | ✅ | franchise *(roomy)* |
| `compare-table` | `compare_table.json` | ✅ | franchise *(soft)* → `compare_franchise` |
| `franchise-section` | `franchise.json`, `form_franchise.json` | – | franchise |
| `events-preview` | `hire_packages.json`, `form_events.json` | – | events |
| `events-catering` | `content.json` | – | home |
| `order-to-deliver` | `delivery_products.json`, `form_order.json` | – | order |
| `cta-banner` | `cta_default.json` | ✅ | every page — `cta_products`, `cta_events`, `cta_franchise` |

### Media & editorial

| Component | Reads | source? | Used on |
|---|---|:--:|---|
| `gallery-grid` | `gallery.json` | ✅ | gallery |
| `photo-carousel` | `photo_carousel.json` | – | home |
| `video-feature` | `video_feature.json` | ✅ | home *(dark)*, franchise *(dark)* |
| `posts-preview` | `posts_preview.json` | ✅ | home *(soft)*, gallery *(soft)* |

### Contact & conversion

| Component | Reads | source? | Used on |
|---|---|:--:|---|
| `contact-section` | `content.json`, `site.json` | – | home, contact |
| `locations` | `locations.json` | ✅ | home *(split)*, order *(split)*, contact *(split)* |
| `opening-hours` | `opening_hours.json` | ✅ | contact |
| `newsletter` | `newsletter.json` | ✅ | home, about, products, gallery *(compact)*, events |
| `faqs` | `faqs.json` | ✅ | home, franchise *(soft)*, events *(dark)*, order *(soft)*, contact *(soft)* |

### Available but not currently placed

`events-quote`, `features-in`, `franchise-enquiry`, `spotlights`, `newsbar`,
`floating-popup` (the latter two are chrome, rendered by header/footer).

---

## 3. Data shapes worth knowing

Most components take the same three heading fields — `tag` (small caps kicker),
`title` (may contain a single `<em>` for the script-font word), `sub` (one-line
lead) — then a list. The ones with their own rules:

**`opening_hours.json`** — the open/closed badge is computed **on the server**
from the WordPress timezone, so it is right in the HTML with no JavaScript.
`day` is `0` = Sunday … `6` = Saturday and drives the "Today" highlight. A
`close` earlier than `open` means it runs past midnight.

```jsonc
{ "label": "Friday", "day": 5, "open": "10:00", "close": "23:00" }
{ "label": "Sunday", "day": 0, "closed": true }
{ "label": "Monday", "day": 1, "text": "By appointment" }   // free text wins
```

**`compare_table.json`** — column count comes from `plans`; every row's
`values` is padded/trimmed to match, so a half-filled row can't break alignment.
`yes`/`true`/`1` → tick, `no`/`false`/`0`/`""` → dash, anything else prints as
text. `"featured": true` on a plan highlights that whole column.

**`filter_cards.json`** — `filters[].key` must match the strings in each
`items[].tags`. The key `all` (or an empty key) shows everything. With JS off
every card stays visible.

**`ticker.json`** — `items` is a flat array of short strings; `speed` is seconds
per loop. Motion stops under `prefers-reduced-motion`.

**`faqs.json`** — accepts either a flat array of `{q, a}` or
`{heading, items[]}`.

---

## 4. Behaviour that comes free

These are wired by `assets/js/common.js` from data attributes. No component has
its own script, and every one degrades to working HTML with JS off.

| Feature | Opt in with | Notes |
|---|---|---|
| Lightbox | `data-nt-lightbox` on a container | every `<img>` inside becomes clickable; arrows + Esc; caption from `data-caption` or `alt` |
| Filter tabs | `data-nt-filter` scope + `data-nt-filter-btn` / `data-nt-filter-item[data-tags]` | `data-nt-filter-empty` element shows when a filter matches nothing |
| AJAX forms | `data-nt-ajax-form` | posts to a registered AJAX action; nonce supplied by `window.ntSite` |
| Reading progress + back to top | *(nothing)* | injected on every page by `initScrollUI()` |

---

## 5. Adding a new section — the whole recipe

1. `components/my-thing.php` — copy `components/feature-badges.php`. Read your
   own JSON via the `source` pattern, escape every output, `return` early when
   there is no data.
2. `admin/data/my_thing.json` — the content. UTF-8, **no BOM**.
3. `assets/css/vintage.css` — a block at the bottom with a banner comment
   naming the component. Use `--trad-*` variables, never raw hex.
4. `admin/data/page_sections.json` — one entry on whichever pages want it.

No route, no enqueue, no template edit. Step 4 is the only thing you repeat to
put it on a second page.
