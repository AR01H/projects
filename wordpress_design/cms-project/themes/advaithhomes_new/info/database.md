# Database & Hardcoding Audit

---

## Section 1 — Plugin DB Tables

All tables use the `wp_ah_` prefix (WordPress prefix + `ah_`).

| Table | Purpose | Key Columns | Theme Function(s) That Read It |
|-------|---------|-------------|-------------------------------|
| `wp_ah_taxonomy_types` | Classifies term hierarchies (e.g. "Category", "Guide", "Review Type"). One row per domain. | `id`, `name`, `slug`, `description` | `adn_cms_guide_type_id()` |
| `wp_ah_taxonomy_parent_terms` | Top-level journey buckets (Buying, Selling, Moving…). Created by migration, not original schema. | `id`, `name`, `slug`, `description`, `icon_emoji`, `image_id`, `color`, `status`, `sort_order` | `adn_cms_guide_parents()`, `adn_cms_parent_by_slug()`, `adn_category_parent_term()`, `adn_news_cms_categories()`, `page_topic_category_logical.php`, `page_category_logical.php` |
| `wp_ah_taxonomies` | Child topic terms under each parent (e.g. "Mortgages", "Legal & Conveyancing"). Also holds `parent_term_id` FK added by migration. | `id`, `type_id`, `parent_id`, `parent_term_id`, `name`, `slug`, `description`, `status`, `sort_order`, `icon_emoji`, `image_id` | `adn_cms_topics()`, `adn_cms_guides_by_category()`, `adn_cms_term_by_slug()`, `adn_cms_taxonomy_term_by_slug()`, `adn_cms_posts_for_term_slug()`, `adn_cms_post_breadcrumb()` |
| `wp_ah_content_taxonomies` | Pivot table: links WP posts (`object_type='wp_post'`, `object_id=post.ID`) to taxonomy terms. | `id`, `object_type`, `object_id`, `taxonomy_id` | `adn_cms_articles()`, `adn_cms_guides_by_category()`, `adn_cms_posts_for_term_slug()`, `adn_cms_post_breadcrumb()` |
| `wp_ah_news_bar_items` | Editor-managed news/announcement items with date scheduling. Primary news source on the front end. | `id`, `text`, `content`, `image_id`, `link_url`, `link_target`, `status`, `sort_order`, `start_date`, `end_date` | `adn_cms_newsbar_items()` — consumed by home, guides, category, news, ask-expert, and topic-category pages |
| `wp_ah_posts` | Plugin's own post table (blog/article/news/guide). **Note: the theme now reads `wp_posts` (native WP posts), not this table.** `adn_cms_articles()` queries `wp_posts` via `object_type='wp_post'` in content_taxonomies. | `id`, `post_type`, `title`, `slug`, `excerpt`, `content`, `status`, `is_featured`, `published_at` | Referenced in schema but theme bypasses it — `adn_cms_articles()` reads `wp_posts` directly. |
| `wp_ah_site_settings` | Key/value store for phone, whatsapp, email, address, consultation_url, youtube_url. Managed via plugin settings UI. | `setting_key`, `setting_val`, `field_type`, `group_name` | Not yet read by theme (no `adn_cms_*` wrapper exists for it). |
| `wp_ah_news_detail_big_cards` / `wp_ah_post_links` / `wp_ah_post_table_blocks` | Rich-content blocks for plugin posts. | varies | Not read by theme — plugin's own post detail pages only. |
| `wp_ah_related_links` | Polymorphic related-content links (source object → target). | `object_type`, `object_id`, `link_type`, `url`, `label`, `container` | Not yet read by theme. |

### WordPress Options (read via `get_option()`)

These are not tables but are the DB backing for several theme sections:

| Option Key | What it stores | Read by |
|-----------|---------------|---------|
| `ah_cms_navigation` | JSON — nav links with dropdowns (plugin Navigation Editor) | `adn_chrome_plugin_nav()` in `services.php` |
| `ah_cms_nav_cta` | JSON — header CTA label + URL | `adn_chrome_plugin_cta()` in `services.php` |
| `ah_cms_footer` | JSON — footer columns, brand description, legal links | `adn_chrome_plugin_footer()` in `services.php` |
| `adn_home_hero` | Array — hero heading lines, description, CTAs | `adn_home_apply_hero_overrides()` in `page_home_logical.php` |
| `adn_home_sections` | Array — marquee bar items, section toggles | `adn_home_section_visible()`, `adn_parse_marquee_settings()` |
| `adn_home_featured` | Array — featured topic IDs + count for Guides section | `adn_home_cms_guide_items()` |
| `adn_home_newsblocks` | Array — admin-selected WP posts for Regulations and Hot Topics | `adn_home_cms_regulations_items()`, `adn_home_cms_hot_topics_items()` |
| `adn_calculators_meta` | Array keyed by calc slug — label, URL, is_popular, thumbnail_id | All pages that render calculator cards |
| `adn_calculators_page` | Array — sidebar help title/text/CTA for calculators page | `page_calculators_logical.php`, `page_topic_category_logical.php` |
| `adn_calculators_general` | Array — marquee settings for calculators page | `page_calculators_logical.php` |
| `adn_expert_banner` | Array — heading, info, marquee_items for Ask an Expert page | `page_ask_expert_logical.php` |

### AH_Expert_DB (custom class, not in schema above)

The `AH_Expert_DB` class is referenced in `page_ask_expert_logical.php` and `page_expert_single_logical.php`. It manages an experts table (likely `wp_ah_experts`) with columns: `expert_slug`, `name`, `title`, `category`, `bio`, `rating`, `reviews_count`, `location`, `phone`, `email`, `photo_id`, `bullets` (JSON), `client_images` (JSON), `status`. Not found in `class-db-schema.php` — it appears to be defined elsewhere in the plugin (possibly a separate module not present in this repo snapshot).

---

## Section 2 — Hardcoded → DB Migration Targets

Items that are hardcoded in JSON/PHP but have DB backing and should be removed.

### Nav & Header CTA — `site_chrome.json`

- **File:** `data/json/site_chrome.json`
- **What's hardcoded:** `nav: []` (empty array), `header_cta: { label: "Get Guidance", url: "/ask-expert/" }`
- **DB backing:** `ah_cms_navigation` and `ah_cms_nav_cta` WordPress options (set via plugin Navigation Editor). Already read by `adn_chrome_plugin_nav()` and `adn_chrome_plugin_cta()` which override the JSON when the plugin is active.
- **Action:** JSON `nav` and `header_cta` are already redundant when the plugin is configured. The JSON acts as the empty-default fallback only — this is intentional but the hardcoded fallback CTA label ("Get Guidance") and URL ("/ask-expert/") should become a `core_terms.php` constant so re-theming doesn't require a JSON edit.

### Footer Columns & Legal Links — `site_chrome.json`

- **File:** `data/json/site_chrome.json`
- **What's hardcoded:** `footer.columns: []` (empty), `footer.bottom_links: [Terms /terms/, Privacy /privacy/]`, `footer.copyright: "© 2024 ADVAITH HOMES. All rights reserved."`, `footer.brand.description`, `footer.brand.name`, `footer.brand.icon`
- **DB backing:** `ah_cms_footer` option (plugin Footer Editor) manages `brand_description`, `columns`, `legal_links`. Already overlaid by `adn_chrome_plugin_footer()`.
- **Action:** `footer.bottom_links` (Terms / Privacy) is redundant once the plugin footer has legal_links. `footer.copyright` and `footer.brand.name` remain hardcoded site-identity values — move these to `core_terms.php` constants (`SITE_BRAND_NAME`, `SITE_COPYRIGHT`). `footer.social` (Facebook/Instagram/YouTube all `#`) should be replaced by `ah_cms_footer`'s social links when the plugin exposes them, or moved to a new WP option.

### Home Page Journey Cards — `home_page.json`

- **File:** `data/json/home_page.json`
- **What's hardcoded:** `journey.cards` contains one static card: "I need Professional Help" pointing to `/ask-expert/`.
- **DB backing:** `wp_ah_taxonomy_parent_terms` — all dynamic journey cards (Buying, Selling…) come from there via `adn_home_cms_journey_cards()`. The static card is prepended/merged from JSON.
- **Action:** The static "I need Professional Help" card should be stored as a special parent term in `wp_ah_taxonomy_parent_terms` (with `slug='help'` and `status='active'`), or added as a CMS-admin-configurable "extra card" option. Remove the JSON hardcoding.

### Home Page News Items — `home_page.json`

- **File:** `data/json/home_page.json`
- **What's hardcoded:** `news.items: []` (empty, populated by static mock in `buying.json`)
- **DB backing:** `wp_ah_news_bar_items` (primary) or `wp_posts` via WP_Query (fallback). `adn_home_cms_news_items()` fully replaces this with live data when the plugin is active.
- **Action:** `home_page.json` `news.items` is already empty — nothing to remove. Confirm no template reads it directly. The `buying.json` news items (3 hardcoded news articles with dates like "May 20, 2024") are the real problem — see below.

### Buying Category News Items — `buying.json`

- **File:** `data/json/buying.json`  (lines 84–99)
- **What's hardcoded:** Three news items with specific UK property headlines, dates (May 2024) and tag labels ("MARKET NEWS", "MORTGAGE NEWS", "GOV.UK NEWS").
- **DB backing:** `wp_ah_news_bar_items` and `wp_posts`. `page_category_logical.php` reads live news via `adn_category_cms_news()` — but `buying.json` is still loaded by `adn_service_category_data()` as a fallback.
- **Action:** `page_category_logical.php` (`adn_category_get_context()`) already builds all sections from DB — it does NOT use `adn_service_category_data()`. The `buying.json` file is read only by `page_guides_listing_logical.php` for the `/buying-guides/` listing page. Remove `news.items` from `buying.json` (already DB-driven there). The 3 mock news items in `buying.json` are never shown on the category page — but left in the file they create confusion. Delete them.

### Buying Guides Listing — `buying-guides.json`

- **File:** `data/json/buying-guides.json`
- **What's hardcoded:** Full `guides.items` array (12 articles with specific titles, dates, categories, icons, gradient classes). `sidebar.browse_cats` (9 category labels with article counts).
- **DB backing:** `wp_ah_taxonomies` + `wp_ah_taxonomy_parent_terms` + `wp_posts` via `adn_cms_articles_for_parent()` and `adn_cms_guide_parents()`. `page_guides_listing_logical.php` already replaces `guides.items` and `sidebar.browse_cats` with live DB data when the plugin is active.
- **Action:** Remove `guides.items` from `buying-guides.json` (entire array, 12 hardcoded articles). Remove `sidebar.browse_cats` (9 hardcoded category entries). Replace with live DB data already wired in `page_guides_listing_logical.php`. Keep `hero`, `meta`, `breadcrumb`, `sidebar.level_filters`, `sidebar.format_filters`, `sidebar.help_cta` as JSON since these have no DB backing yet.

### News Page — `news.json`

- **File:** `data/json/news.json`
- **What's hardcoded:** `featured` (one specific news article about UK house prices, May 2024). All `sections[*].items` (full grid of 13 mock news articles with real-looking dates). `categories` (with hardcoded `count` numbers). `sidebar.trending` (5 articles with reader counts). `sidebar.market_snapshot` (6 financial metrics with specific values).
- **DB backing:** `wp_ah_news_bar_items` (primary), then `wp_posts` via WP_Query. `page_news_logical.php` `adn_news_get_context()` overwrites `featured` and `sections` from the DB; `categories` is rebuilt from CMS taxonomy parent topics.
- **Action:** Remove `featured`, `sections`, and `categories` from `news.json` (DB-driven). `sidebar.trending` and `sidebar.market_snapshot` have no DB backing — they should move to a WP option (`adn_news_sidebar_trending`, `adn_news_market_snapshot`) or be removed entirely. `sidebar.market_snapshot` data (house prices, BoE rate, etc.) goes stale — needs an external data source or manual option panel. The `hero`, `breadcrumb`, `meta`, and `bottom_newsletter` sections in `news.json` are legitimate JSON-managed static copy.

### Post Sidebar Calculators — `post_sidebar.json`

- **File:** `data/json/post_sidebar.json`
- **What's hardcoded:** `calculators.items` (5 calculator links with icons and URLs).
- **DB backing:** `adn_calculators()` registry + `adn_calculators_meta` option. `adn_service_post_sidebar_data()` already replaces `calculators.items` with the live registry.
- **Action:** Remove `calculators.items` from `post_sidebar.json`. The file currently only contains `calculators` (already overridden) and `newsletter` (no DB backing — keep in JSON). After removing, `post_sidebar.json` only needs to hold the `newsletter` block.

### Guidance Page — `guidance.json`

- **File:** `data/json/guidance.json`
- **What's hardcoded:** `form.help_options` (8 service types e.g. "Buying a Home", "Mortgage Advice"), `form.iam_options` (6 user types), `form.time_options` (4 timeframes). `services.items` (6 service cards with hardcoded URLs). `why_choose.items` (4 trust items with hardcoded copy including "UK Property Experts").
- **DB backing:** `wp_ah_taxonomy_parent_terms` for `services.items` — `page_contact_logical.php` already replaces the contact form `enquiry_types` and `resources` with live parent terms. However `guidance.json` is served by `page_guidance_logical.php` which reads `adn_service_guidance_data()` directly from JSON with no DB overlay.
- **Action:** Wire `page_guidance_logical.php` to replace `form.help_options` with live CMS parent term names (same pattern as `page_contact_logical.php`). Replace `services.items` with live parent terms. Move "UK Property Experts" label and domain-specific copy to `core_terms.php` constants.

### Ask-Expert Page — `ask-expert.json`

- **File:** `data/json/ask-expert.json`
- **What's hardcoded:** `stats` (4 stats: "500+ Vetted Experts", "20+ Specialisms", "24h Response", "100% Free"). `categories` (7 expert categories). `experts: []` (empty but defines the shape).
- **DB backing:** `AH_Expert_DB::get_all('active')` provides live experts. `page_ask_expert_logical.php` builds `categories` from DB experts and `stats` from DB counts when experts exist.
- **Action:** `ask-expert.json` is completely bypassed by `page_ask_expert_logical.php` which builds everything dynamically. The JSON file is never read by the logical layer — delete it or keep as documentation only. Stats ("500+ Vetted Experts") should not be hardcoded; they are computed from `AH_Expert_DB` row counts in the logical file.

### Buying Category Journey Steps — `buying.json`

- **File:** `data/json/buying.json`  (lines 21–33)
- **What's hardcoded:** `journey.steps` (5 buying journey steps with icons, descriptions) and `journey.tip` (timeline tip with hardcoded URL `/guides/buying-step-by-step/`).
- **DB backing:** `AH_Category_Settings` DB model (plugin) — `page_category_logical.php` reads `$_cs_all['journey']` from the DB when set via the admin Category Settings UI.
- **Action:** `buying.json` is not used by `page_category_logical.php` (that page builds everything from DB + `AH_Category_Settings`). But `buying.json` IS loaded by `adn_service_category_data('buying')` from `page_guides_listing_logical.php`. Remove `journey` from `buying.json` entirely — the guides listing page doesn't render a journey section.

### Calculators Page — `calculators.json`

- **File:** `data/json/calculators.json`
- **What's hardcoded:** `all_calcs` (16 calculator cards with icons, descriptions, categories), `popular_calcs` (4 highlighted calculators), `sidebar.categories` (7 categories with counts), `filter_tabs` (7 tabs), `trust_items` (4 items), `find_cta`.
- **DB backing:** `adn_calculators()` registry + `adn_calculators_meta` option. `page_calculators_logical.php` reads the live registry and completely replaces `all_calcs`, `popular_calcs`, `sidebar.categories`, `filter_tabs`.
- **Action:** Remove `all_calcs`, `popular_calcs`, `sidebar.categories`, `filter_tabs` from `calculators.json` — all are overridden by the logical file. Keep `meta`, `breadcrumb`, `hero`, `trust_items` (unless admin configures them via `adn_calculators_page`). Keep `find_cta` as JSON since it has a DB fallback option (`adn_calculators_page`). The JSON file is effectively a stub — consider removing it entirely and moving remaining static copy into `page_calculators_logical.php` hardcoded defaults (as that file already does for hero/trust).

### Individual Guide Articles — `guide-buying-step-by-step.json`

- **File:** `data/json/guide-buying-step-by-step.json`
- **What's hardcoded:** Full article content: `article`, `key_takeaways`, `toc`, `sections` (including step-by-step process, costs grid with UK-specific figures), `author`, `sidebar` (with 5 hardcoded calculator links and 3 mock news items), `feedback`.
- **DB backing:** `wp_posts` (article content lives in WordPress). The sidebar calculators are from `adn_calculators()` registry. Sidebar news comes from `wp_ah_news_bar_items`.
- **Action:** This represents a real WP post that should be authored in WordPress. `page_guide_logical.php` reads this JSON via `adn_service_guide_data()` — once the article exists as a WP post, replace `adn_service_guide_data()` to read from `wp_posts` instead. The sidebar should be built via `adn_service_post_sidebar_data()` (already exists). The UK-specific figures in `sections` (deposit %, legal fees £800-£2000, etc.) are domain content — they should live in the WP post body, not a JSON file.

### Contact Page — `contact.json`

- **File:** `data/json/contact.json`
- **What's hardcoded:** `contact_sidebar.whatsapp.number: "+44 7747 223 762"`, `contact_sidebar.email.address: "contact@advaithhomes.co.uk"`, `form.enquiry_types` (8 types), `resources.items` (6 resource links).
- **DB backing:** `wp_ah_site_settings` table has `whatsapp` and `email` keys. `page_contact_logical.php` replaces `form.enquiry_types` and `resources` with live CMS parent terms.
- **Action:** Replace `contact_sidebar.whatsapp.number` and `contact_sidebar.email.address` with reads from `wp_ah_site_settings` (`adn_cms_site_setting('whatsapp')` — function doesn't exist yet, needs to be created). This is the most critical removal since phone/email in a JSON file will never auto-update.

---

## Section 3 — Constants to Add to `core_terms.php`

Simple site-identity constants that would make re-theming trivial. Currently scattered across JSON files and PHP strings.

```php
// ── Site identity ──────────────────────────────────────────────────
// Change these to re-theme for a different industry/domain.
define( 'SITE_BRAND_NAME',     'ADVAITH HOMES' );        // e.g. "THE ORGANIC FARM"
define( 'SITE_BRAND_ICON',     '🏠' );                   // e.g. "🌿"
define( 'SITE_INDUSTRY',       'UK Property' );           // e.g. "Organic Farming"
define( 'SITE_LOCATION',       'UK' );                    // e.g. "Tamil Nadu"
define( 'SITE_DOMAIN_NOUN',    'Property' );              // e.g. "Farm Produce"
define( 'SITE_COPYRIGHT_YEAR', '2024' );                  // used in footer

// ── Default CTA copy (used when DB option is empty) ─────────────
define( 'SITE_HERO_CTA_PRIMARY',   'Start Your Journey →' );
define( 'SITE_HERO_CTA_SECONDARY', 'Ask an Expert' );
define( 'SITE_NAV_CTA_LABEL',      'Get Guidance' );
define( 'SITE_NAV_CTA_URL',        '/ask-expert/' );

// ── Expert / guidance copy ───────────────────────────────────────
define( 'SITE_EXPERT_NOUN',    'Property Expert' );       // e.g. "Farm Advisor"
define( 'SITE_EXPERT_URL',     '/ask-expert/' );
define( 'SITE_GUIDANCE_URL',   '/guidance/' );
define( 'SITE_CALCULATORS_URL','/calculators/' );
define( 'SITE_NEWS_URL',       '/news/' );
define( 'SITE_GUIDES_URL',     '/guides/' );

// ── Content hierarchy labels (already exist - reference only) ───
// PARENT_TERM  = 'Guide'     → top-level term bucket label
// SECTION_TERM = 'Category'  → child topic term label
// CONTENT_TERM = 'Article'   → individual post label
```

**Priority additions (most impactful for re-theming):**
1. `SITE_BRAND_NAME` — referenced in `site_chrome.json`, `news.json` newsletter copy, `guidance.json` why_choose section, and `page_guides_logical.php` hardcoded meta strings.
2. `SITE_INDUSTRY` / `SITE_DOMAIN_NOUN` — replaces "UK Property", "property", "UK house prices" scattered across logical files and JSON.
3. `SITE_NAV_CTA_LABEL` / `SITE_NAV_CTA_URL` — replaces `site_chrome.json` `header_cta` hardcoded values.
4. `SITE_EXPERT_URL` — `/ask-expert/` appears in at least 8 JSON files and 3 logical PHP files.
5. `SITE_BRAND_ICON` — `🏠` appears in `site_chrome.json` logo, `home_page.json` diagram, `contact.json` hero, and `guidance.json` hero.
