# 1. Theme Structure, Database & File Guide

## Folder Structure

```
canehouse-theme/
├── functions.php          ← Bootstrap. Loads everything. Enqueues CSS/JS.
├── style.css              ← Theme identity (name, version). Required by WordPress.
├── index.php              ← Homepage. Reads from DB tables and renders sections.
├── front-page.php         ← Tells WordPress to use index.php as homepage.
├── header.php             ← Nav bar. Loaded on every page via get_header().
├── footer.php             ← Footer + WhatsApp button. Loaded via get_footer().
├── page.php               ← Default inner page template.
├── page-policy.php        ← Template for Privacy Policy / Terms / Refund pages.
├── page-user-login.php    ← Standalone login/register page (no header/footer).
├── 404.php                ← Page not found.
├── default-data.json      ← Default seed data. Edit this before first install.
│
├── inc/
│   ├── db-schema.php      ← Creates all DB tables. Seeds default data.
│   ├── site-settings.php  ← Global settings page (phone, social, footer etc.)
│   ├── content-manager.php← Tabbed admin UI. CRUD for all content types.
│   ├── contact-leads.php  ← Contact form handler. Lead management + CSV export.
│   └── meta-boxes.php     ← Hero section edit boxes on the Home page.
│
└── assets/
    ├── css/
    │   ├── main.css        ← All frontend styles (your original CSS).
    │   └── ch-admin.css    ← Admin panel styles.
    ├── js/
    │   ├── script.js       ← All frontend JS (sliders, FAQ, animations).
    │   ├── contact-form.js ← Contact form AJAX submission.
    │   └── ch-admin.js     ← Admin panel JS (modal, drag-drop, AJAX).
    └── images/
        └── thecanehouselogo.png
```

---

## How DB Tables Are Created

When you **activate the theme**, WordPress fires `after_switch_theme`.
`db-schema.php` catches this and runs two functions:

```
Activate theme
    ↓
ch_create_all_tables()   ← creates 10 tables using dbDelta()
    ↓
ch_seed_defaults()       ← inserts sample data if tables are empty
    ↓
saves ch_db_version = '2.0' in wp_options
saves ch_db_seeded  = '1'   in wp_options
```

On **every page load**, WordPress checks:
- If `ch_db_version !== '2.0'` → recreate tables
- If `ch_db_seeded !== '1'` → reseed data

This means tables are **never accidentally lost**.

---

## The 10 Database Tables

| Table | What it stores | Key columns |
|---|---|---|
| `wp_ch_reviews` | Customer testimonials | name, role, review_text, rating, image_url |
| `wp_ch_order_steps` | How To Order steps | step_number, emoji, title, description |
| `wp_ch_flavours` | Juice flavours + prices | emoji, name, price, flavour_type |
| `wp_ch_events` | Event hire cards | icon, title, description, list_items |
| `wp_ch_faqs` | FAQ questions | question, answer, category |
| `wp_ch_franchise_locs` | Franchise locations | name, city, description |
| `wp_ch_benefits` | Health benefits | icon, title, description |
| `wp_ch_showcase_slides` | Slider images | title, subtitle, image_url |
| `wp_ch_leads` | Contact form submissions | name, email, mobile, status, admin_comment |
| `wp_ch_leads_meta` | Auto-collected per lead | ip_address, country, city, device_type |

**Every content table has these columns:**
- `id` — auto number
- `status` — `active` (shows on site) or `inactive` (hidden)
- `image_url` — upload any image
- `sort_order` — drag to reorder
- `created_at` / `updated_at` — timestamps

---

## How Data Flows to the Page

```
Admin adds a Review in Content Manager
    ↓
INSERT into wp_ch_reviews (status='active')
    ↓
Visitor loads homepage
    ↓
index.php calls ch_get_active('reviews')
    ↓
SELECT * FROM wp_ch_reviews WHERE status='active' ORDER BY sort_order
    ↓
PHP loops through results and renders HTML
    ↓
Visitor sees the review ✅
```

---

## Where to Edit What

| You want to change | Go here |
|---|---|
| Reviews, FAQs, Flavours, Events, Steps, Benefits, Slides, Locations | WP Admin → 🌿 Cane House → Content Manager |
| Phone, WhatsApp, Social links, Footer, Marquee | WP Admin → 🌿 Cane House → Site Settings |
| Hero title, buttons, hero image | WP Admin → Pages → Home → Edit (scroll down) |
| Navigation menu links | WP Admin → Appearance → Menus |
| Logo | WP Admin → Appearance → Customize → Site Identity |
| Contact form leads | WP Admin → 🌿 Cane House → Contact Leads |