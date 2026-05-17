# ah_advaithhomes — WordPress Theme Documentation
<!-- Living document — updated 2026-05-17 -->

Theme path: `WHOLE_THEME/ah_advaithhomes/`  
Plugin dependency: `ah_cms_plugin` (optional — falls back to mock data if absent)

---

## Table of Contents

1. [File Structure](#1-file-structure)
2. [Design System](#2-design-system)
3. [Using Components](#3-using-components)
4. [Data Layer — How Content Gets to the Page](#4-data-layer)
5. [Forms & Email](#5-forms--email)
6. [JavaScript API](#6-javascript-api)
7. [Helper Functions](#7-helper-functions)
8. [Page Templates — Complete Guide](#8-page-templates)
9. [CSS — Adding Styles](#9-css--adding-styles)
10. [Navigation System](#10-navigation-system)
11. [News Ticker](#11-news-ticker)
12. [Blog System](#12-blog-system)
13. [Static HTML Pages System](#13-static-html-pages-system)
14. [Theme Admin Portal](#14-theme-admin-portal)
15. [Mock Data & Seeder](#15-mock-data--seeder)
16. [Common Patterns](#16-common-patterns)
17. [Admin Bar & Layout Offsets](#17-admin-bar--layout-offsets)
18. [Deployment Checklist](#18-deployment-checklist)

---

## 1. File Structure

```
ah_advaithhomes/
├── style.css                    ← WordPress theme header (required)
├── functions.php                ← Theme setup, asset enqueueing, helpers
├── index.php                    ← Fallback template (blog loop, bare)
├── front-page.php               ← Homepage (used when static front page set)
├── page.php                     ← Generic WP page (auto-detects static HTML pages)
├── page-blog.php                ← Blog Listing template (Template Name: Blog Listing)
├── page-guides.php              ← Guides archive (Template Name: Guides Archive)
├── page-services.php            ← Services page
├── page-about.php               ← About page
├── page-contact.php             ← Contact page
├── page-faq.php                 ← FAQ page
├── page-client-stories.php      ← Client stories page
├── template-static-page.php     ← Static HTML Page template (iframe isolation)
├── single.php                   ← Single blog post
├── archive.php                  ← Category / tag archive
├── search.php                   ← Search results
├── 404.php                      ← 404 error page
├── header.php                   ← Calls parts/header.php
├── footer.php                   ← Calls parts/footer.php
│
├── parts/
│   ├── header.php               ← Nav (desktop + mobile) + news ticker
│   └── footer.php               ← 4-column footer + bottom bar
│
├── components/
│   ├── hero.php                 ← Homepage hero (headline, stats, image)
│   ├── trust-bar.php            ← Trust signals strip
│   ├── guide-cards.php          ← Guide category card grid
│   ├── services-section.php     ← Service card grid
│   ├── testimonials.php         ← Review card grid
│   ├── team-section.php         ← Team member cards
│   ├── faq-section.php          ← FAQ accordion
│   ├── cta-section.php          ← Dark CTA band
│   ├── news-ticker.php          ← Animated scrolling news bar
│   ├── review-carousel.php      ← Carousel reviews
│   └── property-showcase.php    ← Property cards
│
├── assets/
│   ├── css/
│   │   ├── variables.css        ← All CSS custom properties (design tokens)
│   │   ├── base.css             ← Reset, typography, utilities
│   │   ├── components.css       ← Nav, cards, hero, footer, post-card, news-ticker
│   │   ├── layout.css           ← Sidebar, prose, filters, pagination, admin-bar fix
│   │   ├── forms.css            ← Form controls, validation states
│   │   └── animations.css       ← AOS fade-ins, skeleton, hover-lift
│   └── js/
│       ├── main.js              ← Nav, FAQ, AOS, filters, calculators, TOC
│       └── forms.js             ← AJAX form handler
│
├── includes/
│   ├── helpers.php              ← All ah_get_*() functions + ah_theme_table()
│   ├── mock-data.php            ← Fallback mock arrays (no plugin needed)
│   └── class-theme-admin.php   ← Handles all Theme Admin form submissions
│
├── mock_data/
│   └── seeder.php               ← AH_Theme_Seeder class (create tables + seed data)
│
├── admin/
│   ├── theme-dashboard.php      ← Theme Admin → Dashboard
│   ├── theme-content.php        ← Theme Admin → Content Settings
│   ├── theme-nav.php            ← Legacy theme-only nav editor fallback
│   ├── theme-sections.php       ← Theme Admin → Section Visibility
│   ├── theme-mock-data.php      ← Theme Admin → Install Mock Data
│   └── theme-cleanup.php        ← Theme Admin → Cleanup Data
│
└── static/                      ← Raw .html files (served by template-static-page.php)
```

---

## 2. Design System

### Fonts (Google Fonts, loaded in functions.php)

| Role    | Family             | Usage                                          |
|---------|--------------------|------------------------------------------------|
| Display | Cormorant Garamond | All headings (h1-h6), `.section__title`, hero  |
| Body    | DM Sans            | Body text, labels, nav links                   |
| Accent  | Instrument Serif   | Italic `<em>`, logo tagline                    |

### Brand Colours (`variables.css`)

| Token                | Value     | Usage                                       |
|----------------------|-----------|---------------------------------------------|
| `--accent`           | `#b7791f` | Buttons, links, active states, badges       |
| `--accent-dark`      | `#7c4a08` | Button hover, dark accent                   |
| `--bg`               | `#faf9ff` | Page background                             |
| `--bg-alt`           | `#f4f2ff` | Alternate section bg, card hover            |
| `--border`           | `#e8e4f3` | All borders, dividers                       |
| `--slate-900`        | `#0f172a` | Footer, dark sections                       |
| `--text-primary`     | `#1e293b` | Main body text                              |
| `--text-secondary`   | `#64748b` | Helper text, meta, captions                 |
| `--nav-h`            | `76px`    | Fixed nav height (used for layout offsets)  |

### CSS Class Conventions

| Class                    | What it does                                      |
|--------------------------|---------------------------------------------------|
| `.section`               | Standard vertical padding section                 |
| `.section--alt`          | `--bg-alt` background tint                        |
| `.section--dark`         | Slate-900 background, white text                  |
| `.section--sm`           | Smaller vertical padding                          |
| `.container`             | Max-width 1280px, centered, responsive padding    |
| `.container--sm`         | Narrower container (~720px)                       |
| `.grid-2` / `.grid-3` / `.grid-4` | Responsive CSS grid columns             |
| `.section__eyebrow`      | Small uppercase label above a heading             |
| `.section__title`        | Section h2 heading                                |
| `.section__desc`         | Section subtitle paragraph                        |
| `.post-grid`             | Auto-fill card grid (`minmax(300px,1fr)`)         |
| `.post-card`             | Boxed blog/guide card with hover shadow           |
| `.post-card--featured`   | Gold accent border, dark title                    |
| `.post-card__cat`        | Category label pill inside a card                 |
| `.card__meta`            | Date · reading time row                           |
| `data-aos="fade-up"`     | Scroll-triggered animation (+ `data-delay="100"`) |

---

## 3. Using Components

All components live in `components/` and are loaded via `get_template_part()`.

### Basic include (no args)

```php
get_template_part( 'components/hero' );
get_template_part( 'components/trust-bar' );
get_template_part( 'components/testimonials' );
get_template_part( 'components/cta-section' );
```

### Include with args (WordPress 5.5+)

```php
get_template_part( 'components/cta-section', null, [
    'title'     => 'Ready to buy smarter?',
    'desc'      => 'Speak to a buyer\'s agent today.',
    'cta_label' => 'Book a Free Call →',
    'cta_url'   => home_url( '/contact/' ),
] );

get_template_part( 'components/faq-section', null, [
    'topic' => 'Finance',   // filter FAQs by topic
    'limit' => 4,
] );
```

### Hero args

```php
get_template_part( 'components/hero', null, [
    'headline'  => 'Your Expert on the<br><em>Buying Side</em>',
    'subline'   => 'The UK\'s buyer\'s agent.',
    'cta_label' => 'Book a Consultation',
    'cta_url'   => home_url( '/contact/' ),
] );
```

---

## 4. Data Layer

### With CMS Plugin Active

When `ah_cms_plugin` is installed and active, helpers query the custom DB tables:

| Helper function           | Where data comes from                        |
|---------------------------|----------------------------------------------|
| `ah_get_services()`       | `wp_ah_cms_plug_services` table              |
| `ah_get_team()`           | `wp_ah_cms_plug_team_members` table          |
| `ah_get_reviews($limit)`  | `wp_ah_cms_plug_reviews` table               |
| `ah_get_faqs($topic)`     | `wp_ah_cms_plug_faqs` table                  |
| `ah_get_news_bar_items()`  | `wp_ah_cms_plug_news_bar_items` table        |
| `ah_get_settings()`       | `get_option('ah_site_settings')`             |
| `ah_get_home_settings()`  | `get_option('ah_home_settings')`             |
| `ah_get_static_pages()`   | Scans `static/*.html` files                  |

### Without Plugin (Mock Data Fallback)

Every helper automatically falls back to `includes/mock-data.php` when the plugin is absent or the table returns no rows. The site always has realistic content.

### Table helper — never hardcode the prefix

```php
// Always use:
$table = ah_theme_table( 'services' );   // → wp_ah_services (or wp_ah_cms_plug_services)

// Never:
$table = 'wp_ah_services';   // breaks on custom prefix installs
```

`ah_theme_table()` reads `TABLE_MID_FIX` constant (set by the CMS plugin) and
prepends `$wpdb->prefix`. This keeps it multisite-compatible.

---

## 5. Forms & Email

### How forms work

1. Any `<form data-ah-form="TYPE">` is caught by `assets/js/forms.js`
2. Data is POST'd via AJAX to `admin-ajax.php?action=ah_form_submit`
3. `mail/common_contact.php` validates, emails admin, sends auto-reply
4. Response `{success:true, data:{message:"…"}}` drives the UI

### Form types

| Type           | Fields                                                   |
|----------------|----------------------------------------------------------|
| `contact`      | name, email, phone, subject, message                     |
| `consultation` | name, email, phone, budget, location, buyer_type, notes  |
| `newsletter`   | email only (also via `data-ah-newsletter` attribute)     |
| `valuation`    | name, email, phone, address                              |

### Newsletter form

```html
<form data-ah-newsletter class="ah-newsletter-form" novalidate>
  <div class="newsletter-inline">
    <input type="email" name="email" class="form-input" placeholder="Your email address" required>
    <button type="submit" class="btn btn-primary">Subscribe →</button>
  </div>
  <div class="ah-form__status" aria-live="polite"></div>
</form>
```

---

## 6. JavaScript API

### Scroll animations

```html
<div data-aos="fade-up" data-delay="200">Animated element</div>
```

Variants: `fade-up`, `fade-left`, `fade-right`, `zoom-in`

### FAQ accordion

Any `.faq` element with `.faq__q` (button) and `.faq__a` (content) auto-works.
Clicking `.faq__q` toggles `.is-open` on the parent `.faq`.

### Filter tabs

```html
<div data-filter-group>
  <button class="filter-tab filter-tab--active" data-filter="all">All</button>
  <button class="filter-tab" data-filter="finance">Finance</button>

  <div data-filter-item="finance,buying">Shown for finance or buying</div>
  <div data-filter-item="legal">Shown for legal only</div>
</div>
```

### Calculators

- `#ah-stamp-calc` — Stamp duty. Inputs: `#sdlt-price`, `#sdlt-first-time`, `#sdlt-additional`
- `#ah-mortgage-calc` — Mortgage. Inputs: `#mc-price`, `#mc-deposit`, `#mc-rate`, `#mc-term`

### Copy to clipboard

```html
<button data-copy="Text to copy here">Copy</button>
```

---

## 7. Helper Functions

All helpers are in `includes/helpers.php`.

| Function                        | Returns  | Description                              |
|---------------------------------|----------|------------------------------------------|
| `ah_get_settings()`             | `array`  | Site settings (phone, email, socials…)   |
| `ah_get_services()`             | `array`  | Services (DB or mock)                    |
| `ah_get_team()`                 | `array`  | Team members (DB or mock)                |
| `ah_get_reviews($limit)`        | `array`  | Reviews (DB or mock)                     |
| `ah_get_faqs($topic)`           | `array`  | FAQs filtered by topic                   |
| `ah_get_news_bar_items()`       | `array`  | News ticker items                        |
| `ah_get_home_settings()`        | `array`  | Homepage hero/stats settings             |
| `ah_get_static_pages()`         | `array`  | All static HTML pages (slug, label, url) |
| `ah_get_static_quick_links()`   | `array`  | Saved static page quick-link slugs       |
| `ah_get_nav_static_page_links()`| `array`  | Static pages wired into nav sections     |
| `ah_stars($rating, $echo)`      | `string` | Renders ★★★★★ HTML                       |
| `ah_icon($key, $size, $class)`  | `string` | Inline SVG by key                        |
| `ah_breadcrumb()`               | `void`   | Breadcrumb nav                           |
| `ah_pagination()`               | `void`   | paginate_links() for the global query    |
| `ah_excerpt($length)`           | `string` | Trimmed excerpt from current post        |
| `ah_reading_time($post_id)`     | `string` | e.g. `"4 min read"`                     |
| `ah_section_visible($key)`      | `bool`   | Whether a section is visible             |
| `ah_theme_table($name)`         | `string` | Full prefixed DB table name              |

---

## 8. Page Templates

### How WordPress chooses a template

WordPress looks for template files in this order (first match wins):

```
page-{slug}.php          → used if WP page slug matches (e.g. page-blog.php for /blog/)
page-{id}.php            → used if page ID matches
Template Name: header    → used if page has this template assigned in Page Attributes
page.php                 → generic fallback for all pages
index.php                → last resort fallback
```

### Template files in this theme

| File                     | Template Name        | Used for               |
|--------------------------|----------------------|------------------------|
| `front-page.php`         | (auto, homepage)     | Homepage               |
| `page-blog.php`          | Blog Listing         | `/blog/` page          |
| `page-guides.php`        | Guides Archive       | `/guides/` page        |
| `page-services.php`      | (auto by slug)       | `/services/` page      |
| `page-about.php`         | (auto by slug)       | `/about/` page         |
| `page-contact.php`       | (auto by slug)       | `/contact/` page       |
| `page-faq.php`           | (auto by slug)       | `/faq/` page           |
| `page-client-stories.php`| (auto by slug)       | `/client-stories/`     |
| `template-static-page.php` | Static HTML Page   | All static HTML pages  |
| `page.php`               | (generic fallback)   | Any other WP page      |
| `single.php`             | (auto)               | Single blog post       |
| `archive.php`            | (auto)               | Category/tag archives  |

### Creating a new page template

**Step 1** — Create the file (e.g. `page-team.php`):

```php
<?php
/**
 * Template Name: Team Page
 */
get_header();

$team = ah_get_team();
?>

<section class="page-hero page-hero--sm">
  <div class="container">
    <div class="page-hero__copy text-center">
      <span class="section__eyebrow">Who We Are</span>
      <h1 class="page-hero__title">Meet the <em>Team</em></h1>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="grid-4">
      <?php foreach ( $team as $member ) : ?>
      <div class="about-value-card" data-aos="fade-up">
        <div class="about-value-card__title"><?php echo esc_html( $member->name ); ?></div>
        <p class="about-value-card__desc"><?php echo esc_html( $member->role ?? '' ); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php get_footer(); ?>
```

**Step 2** — In WordPress Admin:
- Go to **Pages → Add New**
- Set the title (e.g. "Team")
- Under **Page Attributes → Template**, select "Team Page"
- Publish

**Step 3** — That's it. WordPress will use `page-team.php` automatically.

### Using `page-{slug}.php` (no Template Name needed)

If you name a file `page-blog.php`, WordPress automatically uses it for any page
whose slug is `blog` — no template selection needed.

---

## 9. CSS — Adding Styles

### File to edit

All frontend styles live in `assets/css/`. Pick the right file:

| File             | What goes in it                                    |
|------------------|----------------------------------------------------|
| `variables.css`  | Design tokens only (colours, spacing, fonts)       |
| `base.css`       | Global resets, typography, utility classes         |
| `components.css` | Named component styles (`.nav`, `.post-card`, etc.)|
| `layout.css`     | Page-level layout (sidebar, grid offsets, filters) |
| `forms.css`      | Form inputs, labels, validation states             |
| `animations.css` | Transitions, AOS, hover effects                    |

### Adding a new CSS variable (design token)

Open `variables.css` and add inside `:root {}`:

```css
:root {
    /* existing tokens ... */
    --my-new-color: #e74c3c;
    --my-spacing: 24px;
}
```

Use anywhere in any CSS file: `color: var(--my-new-color);`

### Adding a new component style

Open `components.css` and add at the bottom:

```css
/* ── My New Card ──────────────────────────────────────────── */
.my-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 24px;
    box-shadow: var(--shadow-sm);
}
.my-card__title {
    font-family: var(--font-display);
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 8px;
}
.my-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
    transition: box-shadow .25s, transform .25s;
}
```

### Adding page-specific styles

If styles only apply to one template, add a `<style>` block directly in that PHP file:

```php
<?php get_header(); ?>
<style>
.my-page-header { background: linear-gradient(135deg, var(--slate-900), #1e3a5f); }
</style>
<!-- rest of template -->
```

### Responsive breakpoints

```css
/* Tablet and below */
@media (max-width: 1024px) { }

/* Mobile */
@media (max-width: 768px) { }

/* Small mobile */
@media (max-width: 480px) { }
```

---

## 10. Navigation System

The nav is built in `parts/header.php`. The theme now renders shared CMS data first:
`ah_cms_navigation`, `ah_cms_nav_cta`, and `ah_cms_footer`.

If the CMS plugin is not active or those values are empty, the theme falls back to
legacy theme options like `ah_theme_navigation`, `ah_nav_cta`, and `ah_theme_footer`.

### CMS source of truth

Primary editing now happens in **CMS ADMIN → Navigation & Footer**.

- Top-level item with children:
  set **Type = Dropdown**
- Single link item:
  set **Type = Direct Link**
- Submenu links only appear for dropdown items
- The CMS builder supports:
  top-level collapse/expand, submenu row collapse/expand, and global expand/collapse buttons

### Legacy nav sections fallback

| Section slug | Label in nav      | Dropdown items source            |
|--------------|-------------------|----------------------------------|
| `buying`     | Buying            | `ah_get_nav_buying_topics()`     |
| `finance`    | Finance           | `ah_get_nav_finance_topics()`    |
| `legal`      | Legal & Surveys   | `ah_get_nav_legal_topics()`      |
| `news`       | News & Guides     | Static link (configurable)       |
| `services`   | Services          | Static link (configurable)       |

### Legacy static nav links

In the legacy theme-only flow, add a row in **Theme Admin → Navigation → Static Page Quick Links**:
- Section: `buying` / `finance` / `legal` / `footer`
- Slug: the page slug (e.g. `stamp-duty-calculator`)
- Label: display text
- Icon: emoji or text icon

### Legacy visibility toggles

Each legacy nav section can be shown/hidden via **Theme Admin → Navigation → Section Visibility**.
Stored as `ah_nav_visibility` WP option.

---

## 11. News Ticker

The dark scrolling ticker bar appears just below the nav on every page.

**Component file:** `components/news-ticker.php`  
**Data source:** `ah_get_news_bar_items()` — returns array of strings.

The ticker only renders if there are items. Items are duplicated automatically for seamless loop.

**Visibility toggle:** `ah_section_visible('global_news_ticker')` — controlled via Theme Admin → Sections.

**To add items without the plugin:** Run the seeder (Theme Admin → Install Mock Data),
which seeds 5 ticker items into the DB (or WP option fallback).

---

## 12. Blog System

### Templates involved

| File              | Purpose                                         |
|-------------------|-------------------------------------------------|
| `page-blog.php`   | Blog listing page — all published posts, boxed cards |
| `page-guides.php` | Guides archive — same posts, category filter, guide category cards |
| `single.php`      | Single post view — header, body, sidebar CTA, related articles |
| `archive.php`     | Category/tag archive listing                    |
| `index.php`       | Bare fallback blog loop                         |

### Setting up the Blog page

1. WordPress Admin → Pages → Add New
2. Title: "Blog" (slug becomes `blog`)
3. Template: **Blog Listing**
4. Publish → your blog listing is at `/blog/`

### Blog listing features (`page-blog.php`)

- **Category filter bar** — auto-generated from WP post categories
- **Featured post** — top post on page 1 shown as a wide 2-column card
- **Card grid** — remaining posts as `post-card` boxes (3 columns)
- **Pagination** — preserves category filter in URL
- **Empty state** — friendly message when no posts exist
- **Newsletter block** at the bottom

### Single post features (`single.php`)

- Full-width hero header (category, date, reading time, title, excerpt, author)
- Featured image below header
- Article body with tags and share row
- Sidebar: Free Consultation CTA + Useful Links
- **Related Articles** — full-width card section below the article (3 post-cards matching the current category). Only renders when related posts exist.

### Adding more blog posts

**Via seeder** (fastest for demo):
- Theme Admin → Install Mock Data → seeds 3 published posts

**Via WordPress Admin**:
- Posts → Add New → write content → Publish

### Seeder blog posts

The seeder creates three posts:
1. "How Long Does Buying a Home in the UK Really Take?"
2. "Off-Market Property: What It Is and How to Find It"
3. "Stamp Duty 2025: The Complete Guide for Buyers"

---

## 13. Static HTML Pages System

### What it is

Raw `.html` files stored in `static/` and served as WordPress pages inside an `<iframe>`.
The iframe gives complete CSS isolation — the theme's styles cannot reach the content.

### How it works end-to-end

```
CMS Plugin → Static Pages → write HTML in editor → Save
    ↓
Writes static/{slug}.html
Creates WP page at /{slug}/ if it doesn't exist
Sets _wp_page_template = 'template-static-page.php' on the WP page
    ↓
Visitor visits /{slug}/
    ↓
template-static-page.php loads
    → reads static/{slug}.html
    → outputs <iframe srcdoc="..."> with full HTML content
    → JS resizes iframe to fit content height
    ↓
No theme CSS bleeds in — the page looks exactly as coded
```

### Template file: `template-static-page.php`

```php
<?php
/*
Template Name: Static HTML Page
*/
$slug      = get_post_field( 'post_name', get_queried_object_id() );
$static_dir = trailingslashit( get_template_directory() ) . 'static/';
$real_dir   = realpath( $static_dir );
$file       = $real_dir ? realpath( $real_dir . DIRECTORY_SEPARATOR . sanitize_file_name( $slug ) . '.html' ) : false;

if ( $file && strpos( $file, $real_dir ) === 0 && file_exists( $file ) ) {
    $html_raw = file_get_contents( $file );
} else {
    $html_raw = '<h2 style="font-family:sans-serif;padding:40px">Page not found.</h2>';
}
get_header();
?>
<main style="margin:0;padding:0">
  <iframe id="ah-static-frame"
          srcdoc="<?php echo htmlspecialchars( $html_raw, ENT_QUOTES, 'UTF-8' ); ?>"
          style="width:100%;border:none;display:block;min-height:80vh"
          title="<?php echo esc_attr( get_the_title() ); ?>"></iframe>
</main>
<script>
(function(){
  var f = document.getElementById('ah-static-frame');
  function r(){try{f.style.height=f.contentDocument.documentElement.scrollHeight+'px';}catch(e){}}
  f.addEventListener('load',r);
  window.addEventListener('resize',r);
})();
</script>
<?php get_footer(); ?>
```

### Fallback in `page.php`

`page.php` checks for `_ah_static_page` post meta. If found, it renders the iframe
and exits — this covers pages seeded before `_wp_page_template` was set.

### Seeded static pages (7 pages)

| Slug                        | Title                               |
|-----------------------------|-------------------------------------|
| `stamp-duty-calculator`     | Stamp Duty Calculator (JS, 2025)    |
| `mortgage-calculator`       | Mortgage Calculator                 |
| `first-time-buyer-checklist`| First-Time Buyer Checklist (22 items)|
| `property-glossary`         | Property Glossary (A-Z, 14 terms)   |
| `conveyancing-explained`    | Conveyancing Explained              |
| `privacy-policy`            | Privacy Policy (GDPR)               |
| `cookie-policy`             | Cookie Policy                       |

Run **Theme Admin → Install Mock Data** to create these pages and their WP page records.

### Security

- Slug is sanitized to `[a-z0-9-]` only before use as filename
- `realpath()` check prevents path traversal (`../` attacks)
- `htmlspecialchars(ENT_QUOTES)` encodes the HTML inside `srcdoc` attribute safely

---

## 14. Theme Admin Portal

Located in WP Admin sidebar as "AH Theme". Registered via `includes/class-theme-admin.php`.

### Pages

| Admin URL slug         | File                      | What it manages                             |
|------------------------|---------------------------|---------------------------------------------|
| `ah-theme`             | `theme-dashboard.php`     | Status cards, data sources, quick actions   |
| `ah-theme-content`     | `theme-content.php`       | Site settings, home settings, static pages  |
| `ah-theme-nav`         | `theme-nav.php`           | Nav topics, visibility, static page links   |
| `ah-theme-sections`    | `theme-sections.php`      | Show/hide homepage and global sections      |
| `ah-theme-mock`        | `theme-mock-data.php`     | Install all mock/demo data                  |
| `ah-theme-cleanup`     | `theme-cleanup.php`       | Remove all seeded data                      |

### Dashboard status cards

The dashboard shows live counts for:
- CMS Plugin status (active / not active)
- Services, Team Members, Reviews, FAQs — row counts from DB tables
- Home Settings, Process Steps, Site Stats — WP option set/missing

`null` count means the DB table doesn't exist (plugin not active — seeder will create it).

### Installing mock data

**Theme Admin → Install Mock Data → "Install All Mock Data"**

The seeder:
1. Calls `AH_Theme_Seeder::create_tables()` — creates the 5 DB tables if missing
2. Runs all seed methods: services, team, reviews, FAQs, news bar, home settings, properties, blog posts, static pages
3. Sets `_wp_page_template` on all static page WP pages

After running, all dashboard counts show real data.

---

## 15. Mock Data & Seeder

**File:** `mock_data/seeder.php`  
**Class:** `AH_Theme_Seeder`

### Public methods

| Method                            | What it seeds                                  |
|-----------------------------------|------------------------------------------------|
| `seed_all()`                      | Everything — calls all methods below           |
| `create_tables()`                 | Creates 5 DB tables if they don't exist        |
| `seed_settings()`                 | `ah_site_settings` WP option                   |
| `seed_home_settings()`            | `ah_home_settings` WP option                   |
| `seed_guide_nav()`                | `ah_guide_nav` WP option                       |
| `seed_guide_categories()`         | `ah_guide_categories` WP option                |
| `seed_nav_topics()`               | `ah_nav_*_topics` WP options                   |
| `seed_process_steps()`            | `ah_process_steps` WP option                   |
| `seed_site_stats()`               | `ah_site_stats` WP option                      |
| `seed_news_bar()`                 | `news_bar` DB table (5 ticker items)           |
| `seed_services()`                 | `services` DB table (6 services)               |
| `seed_team()`                     | `team` DB table (4 members)                    |
| `seed_reviews()`                  | `reviews` DB table (6 reviews)                 |
| `seed_faqs()`                     | `faqs` DB table (10 FAQs)                      |
| `seed_properties()`               | `ah_featured_properties` WP option             |
| `seed_blog_posts()`               | 3 published WP posts                           |
| `seed_static_pages()`             | 7 HTML files + WP pages with template set      |
| `table_counts()`                  | Returns live counts for dashboard              |
| `cleanup_all()`                   | Deletes all seeded data (truncates + deletes)  |

### DB tables created by the seeder

These are **theme-specific** tables — simpler schema than the CMS plugin:

| Table (via `ah_theme_table()`) | Columns seeded                              |
|--------------------------------|---------------------------------------------|
| `services`                     | title, summary, icon, status, sort_order    |
| `team`                         | name, role, bio, photo_url, status          |
| `reviews`                      | author_name, location, review_text, rating  |
| `faqs`                         | topic, question, answer, status, sort_order |
| `news_bar`                     | message, status, sort_order                 |

### Running programmatically

```php
require_once get_template_directory() . '/mock_data/seeder.php';

// Everything:
AH_Theme_Seeder::seed_all();

// Just one:
AH_Theme_Seeder::seed_static_pages();
AH_Theme_Seeder::seed_blog_posts();
AH_Theme_Seeder::create_tables();   // safe to run multiple times
```

---

## 16. Common Patterns

### Page hero (header block)

```php
<section class="page-hero page-hero--sm" aria-label="Section Name">
  <div class="container">
    <div class="page-hero__copy text-center" style="max-width:640px;margin-inline:auto" data-aos="fade-up">
      <span class="section__eyebrow">Small Label</span>
      <h1 class="page-hero__title">Main <em>Heading</em></h1>
      <p class="page-hero__desc">Subtitle description text here.</p>
    </div>
  </div>
</section>
```

### Section with eyebrow + title

```php
<section class="section">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">Category Label</span>
      <h2 class="section__title">Main Heading Here</h2>
      <p class="section__desc" style="margin-inline:auto">Description, max ~600px wide.</p>
    </div>
    <!-- content below -->
  </div>
</section>
```

### Alternating section backgrounds

```php
<section class="section">           <!-- white -->
<section class="section section--alt">   <!-- purple tint -->
<section class="section section--dark">  <!-- slate-900 dark -->
<section class="section section--sm">    <!-- white, less padding -->
```

### Content + sidebar layout

```php
<div class="content-layout">
  <article class="prose">
    <!-- article body -->
  </article>
  <aside class="sidebar">
    <div class="sidebar-card">
      <div class="sidebar-card__title">Title</div>
      <!-- content -->
    </div>
    <div class="sidebar-card sidebar-card--accent">
      <!-- gold/accent card -->
    </div>
  </aside>
</div>
```

### Blog card grid

```php
<div class="post-grid">
  <article class="post-card">
    <a href="..." class="post-card__img-wrap">
      <?php the_post_thumbnail( 'ah-card' ); ?>
    </a>
    <div class="post-card__body">
      <div class="post-card__cat">Category</div>
      <div class="card__meta">
        <span>17 May 2026</span>
        <span>·</span>
        <span>4 min read</span>
      </div>
      <h2 class="post-card__title"><a href="...">Post Title</a></h2>
      <p class="post-card__excerpt">Short excerpt here…</p>
      <a href="..." class="btn btn-sm btn-ghost">Read More →</a>
    </div>
  </article>
</div>
```

### DB count for dashboard / admin pages

```php
global $wpdb;
$table = ah_theme_table( 'services' );
$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
echo $count . ' services in DB';
```

---

## 17. Admin Bar & Layout Offsets

The fixed nav (`position:fixed; height: var(--nav-h) = 76px`) requires a matching
`padding-top` on `#page-content` so content doesn't slide under it.

When logged in, WordPress injects `html { margin-top: 32px }` for the admin bar,
which pushes the fixed nav down 32px — but `#page-content` wouldn't know about it,
creating a 32px white gap.

**The fix** is in `assets/css/layout.css`:

```css
#page-content { padding-top: var(--nav-h); }

/* WordPress admin bar offset */
.admin-bar .nav             { top: 32px; }
.admin-bar .nav__mobile     { top: calc(32px + var(--nav-h)); }
.admin-bar #page-content    { padding-top: calc(var(--nav-h) + 32px); }

@media screen and (max-width: 782px) {
  .admin-bar .nav           { top: 46px; }
  .admin-bar .nav__mobile   { top: calc(46px + var(--nav-h)); }
  .admin-bar #page-content  { padding-top: calc(var(--nav-h) + 46px); }
}
```

**Rule:** The admin bar is 32px on desktop, 46px on mobile (below 782px).
Any new fixed element at `top:0` must also get `.admin-bar { top: 32px }`.

---

## 18. Deployment Checklist

- [ ] Upload theme to `wp-content/themes/ah_advaithhomes/`
- [ ] Activate in **Appearance → Themes**
- [ ] Install and activate `ah_cms_plugin`
- [ ] **Theme Admin → Install Mock Data** to seed demo content
- [ ] **Settings → Reading → Static front page** → select Homepage
- [ ] **Settings → Reading → Posts page** → select Blog (create if needed)
- [ ] Upload logo to `assets/images/logo.png`
- [ ] Add hero image to `assets/images/hero-home.jpg`
- [ ] **CMS Portal → Site Settings**: fill phone, email, social links
- [ ] **Theme Admin → Navigation**: configure nav topics and visibility
- [ ] Test all contact forms (admin email + auto-reply)
- [ ] Run stamp duty and mortgage calculators
- [ ] Verify mobile nav opens/closes
- [ ] Check "View Page" on all static pages in CMS Portal → Static Pages
- [ ] Confirm no white gap between nav and news ticker (admin bar fix)
