# ah_advaithhomes — WordPress Theme Documentation

Theme path: `WHOLE_THEME/ah_advaithhomes/`  
Plugin dependency: `ah_cms_plugin` (optional — falls back to mock data if absent)

---

## 1. File Structure

```
ah_advaithhomes/
├── style.css              ← WordPress theme header (required)
├── functions.php          ← Theme setup, asset enqueueing, helpers
├── index.php              ← Fallback template (blog loop)
├── front-page.php         ← Homepage template (used when a static front page is set)
├── page.php               ← Generic WordPress page template
├── single.php             ← Single blog post template
├── archive.php            ← Category / tag archive listing
├── search.php             ← Search results page
├── 404.php                ← 404 error page
├── header.php             ← Calls parts/header.php
├── footer.php             ← Calls parts/footer.php
│
├── parts/
│   ├── header.php         ← Full navigation (desktop + mobile) + news ticker
│   └── footer.php        ← 4-column footer + bottom bar
│
├── components/
│   ├── hero.php           ← Homepage hero with headline, stats, image frame
│   ├── trust-bar.php      ← Horizontal trust signals strip
│   ├── guide-cards.php    ← 4-column guide category cards grid
│   ├── services-section.php ← 3-column service card grid
│   ├── testimonials.php   ← 3-column review card grid
│   ├── team-section.php   ← 4-column team member cards
│   ├── faq-section.php    ← FAQ accordion (JS-powered)
│   ├── cta-section.php    ← Dark CTA band with two buttons
│   └── news-ticker.php    ← Animated scrolling news bar (above nav)
│
├── assets/
│   ├── css/
│   │   ├── variables.css  ← All CSS custom properties (design tokens)
│   │   ├── base.css       ← Reset, typography, layout utilities
│   │   ├── components.css ← All component styles (nav, cards, hero, footer…)
│   │   ├── layout.css     ← Sidebar, prose, steps, filters, stats strip
│   │   ├── forms.css      ← Form controls, validation, calculators
│   │   └── animations.css ← Intersection-observer fade-ins, skeleton, hover-lift
│   └── js/
│       ├── main.js        ← Nav, FAQ, AOS, filters, calculators, TOC
│       └── forms.js       ← AJAX form submission handler
│
├── includes/
│   ├── helpers.php        ← ah_get_settings(), ah_icon(), ah_stars(), ah_get_*()
│   └── mock-data.php      ← Mock services, team, reviews, FAQs, home sections
│
└── mail/
    └── common_contact.php ← All form AJAX handlers (contact, consultation, newsletter, valuation)
```

---

## 2. Design System

### Fonts (Google Fonts, loaded in functions.php)
| Role    | Family               | Usage |
|---------|----------------------|-------|
| Display | Cormorant Garamond   | All headings (h1–h6), `.section__title`, hero title |
| Body    | DM Sans              | All body text, labels, nav links |
| Accent  | Instrument Serif     | Italic emphasis `<em>`, logo tagline |

### Brand Colors (in `variables.css`)
| Token                   | Value     | Usage |
|-------------------------|-----------|-------|
| `--client-color-500`    | `#eab308` | Primary gold |
| `--client-color-700`    | `#b7791f` | Accent/brown — buttons, links, active states |
| `--bg`                  | `#faf9ff` | Page background |
| `--bg-alt`              | `#f4f2ff` | Section alt background, card hover bg |
| `--border`              | `#e8e4f3` | All borders |
| `--slate-900`           | `#0f172a` | Dark sections, footer background |

### CSS Class Conventions
- `.section` — standard vertical padding section
- `.section--alt` — uses `--bg-alt` background
- `.section--dark` — dark slate background, white text
- `.container` — max-width 1280px, centered, responsive padding
- `.container--sm` / `--md` / `--xl` — width variants
- `.grid-2/3/4` — responsive grid columns
- `data-aos="fade-up"` + `data-delay="100"` — scroll-triggered animations

---

## 3. Using Components

All components live in `components/` and are loaded with `get_template_part()`.

### Basic include (no args)
```php
get_template_part( 'components/hero' );
get_template_part( 'components/trust-bar' );
get_template_part( 'components/testimonials' );
```

### Include with args (WordPress 5.5+)
```php
get_template_part( 'components/cta-section', null, [
    'title'     => 'Custom CTA Title',
    'desc'      => 'Your description here.',
    'cta_label' => 'Book Now →',
    'cta_url'   => home_url( '/contact/' ),
    'sec_label' => 'Learn More',
    'sec_url'   => home_url( '/guides/' ),
] );

get_template_part( 'components/faq-section', null, [
    'topic' => 'Finance',  // filters FAQs by topic
    'limit' => 4,
] );
```

### Hero args
```php
get_template_part( 'components/hero', null, [
    'headline'  => 'Your Expert on the<br><em>Buying Side</em>',
    'subline'   => 'Subtitle text here.',
    'cta_label' => 'Book a Consultation',
    'cta_url'   => home_url( '/contact/' ),
] );
```

---

## 4. Data Layer

### With CMS Plugin Active
When `ah_cms_plugin` is installed and active, data comes from the custom tables:
- `ah_get_services()` → `AH_Model_Services::all()`
- `ah_get_team()` → `AH_Model_Team::all()`
- `ah_get_reviews()` → `AH_Model_Reviews::all()`
- `ah_get_faqs()` → `AH_Model_FAQs::all()`
- `ah_get_settings()` → `get_option('ah_site_settings')`

### Without Plugin (Mock Data)
If the plugin is absent or returns no rows, every function automatically falls back to `includes/mock-data.php`. The site always has realistic content — useful for development and staging.

### Updating Mock Data
Edit `includes/mock-data.php` — each function (`ah_mock_services()`, `ah_mock_team()`, etc.) returns a plain PHP array of `stdClass` objects that mirror the database model structure.

---

## 5. Forms & Email

### How forms work
1. Any `<form data-ah-form="TYPE">` is intercepted by `assets/js/forms.js`
2. Form data is POST'd via AJAX to `admin-ajax.php` with `action=ah_form_submit`
3. `mail/common_contact.php` handles the request, validates, emails admin, sends auto-reply
4. Response JSON `{success: true, data: {message: "…"}}` drives the UI

### Form types
| Type             | Fields                                               |
|------------------|------------------------------------------------------|
| `contact`        | name, email, phone, subject, message                 |
| `consultation`   | name, email, phone, budget, location, buyer_type, notes |
| `newsletter`     | email only (also via `data-ah-newsletter` attribute) |
| `valuation`      | name, email, phone, address                          |

### Example contact form HTML
```html
<form data-ah-form="contact">
  <?php wp_nonce_field(); ?>
  <div class="form-group">
    <label class="form-label">Name <span class="required">*</span></label>
    <input type="text" name="name" class="form-control" required>
    <div class="form-error"></div>
  </div>
  <div class="form-group">
    <label class="form-label">Email <span class="required">*</span></label>
    <input type="email" name="email" class="form-control" required>
    <div class="form-error"></div>
  </div>
  <div class="form-group">
    <label class="form-label">Message <span class="required">*</span></label>
    <textarea name="message" class="form-control" rows="5" required></textarea>
    <div class="form-error"></div>
  </div>
  <button type="submit" class="btn btn-primary">Send Message</button>
  <div class="form-notice form-notice--success"><span class="notice-text"></span></div>
  <div class="form-notice form-notice--error"><span class="notice-text"></span></div>
</form>
```

---

## 6. JavaScript API

### Scroll animations (`data-aos`)
Add `data-aos="fade-up"` to any element. It gets `opacity:0; transform:translateY(24px)` initially and transitions to visible on scroll.

```html
<div data-aos="fade-up" data-delay="200">Animated card</div>
```

Variants: `fade-up`, `fade-left`, `fade-right`, `zoom-in`

### FAQ accordion
Any element with class `.faq` containing `.faq__q` (button) and `.faq__a` (content) auto-works. Clicking `.faq__q` toggles `.is-open` on the `.faq` parent.

### Filter tabs
```html
<div data-filter-group>
  <button class="filter-tab is-active" data-filter="all">All</button>
  <button class="filter-tab" data-filter="finance">Finance</button>

  <div data-filter-item="finance,buying">Card shown for finance or buying</div>
  <div data-filter-item="legal">Card shown for legal only</div>
</div>
```

### Calculators
- `#ah-stamp-calc` — Stamp duty calculator. Inputs: `#sdlt-price`, `#sdlt-first-time` (checkbox), `#sdlt-additional` (checkbox). Outputs: `#sdlt-result-duty`, `#sdlt-result-total`
- `#ah-mortgage-calc` — Mortgage payment calculator. Inputs: `#mc-price`, `#mc-deposit`, `#mc-rate`, `#mc-term`. Outputs: `#mc-result-monthly`, `#mc-result-loan`, `#mc-result-ltv`

### Copy to clipboard
```html
<button data-copy="Text to copy">Copy</button>
```

---

## 7. Helper Functions

| Function | Returns | Description |
|----------|---------|-------------|
| `ah_get_settings()` | `array` | Site settings (phone, email, social URLs…) |
| `ah_buying_guides_nav()` | `array` | Guide nav items for header/footer |
| `ah_get_services( )` | `array` | Services (DB or mock) |
| `ah_get_team()` | `array` | Team members (DB or mock) |
| `ah_get_reviews( $limit )` | `array` | Reviews (DB or mock) |
| `ah_get_faqs( $topic )` | `array` | FAQs filtered by topic (DB or mock) |
| `ah_stars( $rating, $echo )` | `string` | Renders ★★★★★ HTML |
| `ah_icon( $key, $size, $class )` | `string` | Returns inline SVG by key |
| `ah_breadcrumb()` | `void` | Outputs breadcrumb nav |
| `ah_pagination()` | `void` | Outputs paginate_links() |
| `ah_excerpt( $length )` | `string` | Trimmed excerpt text |
| `ah_reading_time( $post_id )` | `string` | E.g. `"4 min read"` |

### Available icon keys for `ah_icon()`
`check`, `arrow`, `phone`, `mail`, `star`, `home`, `shield`, `key`, `chart`, `clock`, `users`, `search`, `plus`, `minus`

---

## 8. Adding a New Page Template

1. Create `templates/page-my-template.php`
2. Add the WordPress template header comment:
```php
<?php
/*
 * Template Name: My Custom Template
 */
get_header();
?>
<!-- your content here -->
<?php get_footer(); ?>
```
3. In WordPress admin: Pages → Edit → Page Attributes → Template → select "My Custom Template"

---

## 9. Common Patterns

### Section with eyebrow + title + centered description
```php
<section class="section">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">Category Label</span>
      <h2 class="section__title">Main Heading Here</h2>
      <p class="section__desc" style="margin-inline:auto">Short description, max ~600px wide.</p>
    </div>
    <!-- content below -->
  </div>
</section>
```

### Alternating section backgrounds
```php
<section class="section">          <!-- white -->
<section class="section section--alt">  <!-- purple-tinted -->
<section class="section section--dark"> <!-- slate-900 dark -->
```

### Content + sidebar layout
```php
<div class="content-layout">
  <main class="prose">
    <!-- article content -->
  </main>
  <aside class="sidebar">
    <div class="sidebar-card">
      <div class="sidebar-card__title">Title</div>
      <!-- content -->
    </div>
  </aside>
</div>
```

---

## 10. Deployment Checklist

- [ ] Upload theme to `wp-content/themes/ah_advaithhomes/`
- [ ] Activate theme in WordPress → Appearance → Themes
- [ ] Install and activate `ah_cms_plugin` plugin
- [ ] Go to CMS Portal → Site Settings and fill in phone, email, social links
- [ ] Set a static front page: Settings → Reading → "A static page" → select Homepage
- [ ] Upload logo to `assets/images/logo.png` (or set via Customizer)
- [ ] Add hero image to `assets/images/hero-home.jpg` for homepage visual
- [ ] Configure WordPress menus (Appearance → Menus) for Primary and Footer nav slots
- [ ] Test all contact forms — check admin receives email and sender gets auto-reply
- [ ] Run the stamp duty and mortgage calculators to confirm JS is loading
- [ ] Verify mobile nav opens/closes correctly on phone-sized viewport
