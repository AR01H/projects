# deploy.md — How to Use This Project for a New Site

This file explains how to take the AH CMS plugin and build a complete WordPress site with it.

---

## Table of Contents

1. [What This Project Is](#1-what-this-project-is)
2. [Deployment Modes](#2-deployment-modes)
3. [Mode 1: Plugin + Companion Theme (Recommended)](#3-mode-1-plugin--companion-theme-recommended)
4. [Mode 2: Standalone Theme](#4-mode-2-standalone-theme)
5. [Building the Companion Theme](#5-building-the-companion-theme)
6. [Design Spec → DB Model Mapping](#6-design-spec--db-model-mapping)
7. [Available Models — Quick Reference](#7-available-models--quick-reference)
8. [Frontend Template Examples](#8-frontend-template-examples)
9. [Companion Theme Checklist](#9-companion-theme-checklist)

---

## 1. What This Project Is

**AH CMS** is a WordPress plugin that handles all content management for a real estate / service business site.
It provides:

- A full admin portal (CMS Portal in WP admin sidebar)
- A structured database with 50+ custom tables (`wp_ah_*`)
- 17 PHP model classes for all data types
- CSV import, file links, form builder, audit log, static pages
- Zero dependency on `wp_posts` for custom content

A **companion frontend theme** is built separately and reads from the plugin's database using the model classes.
This separation means the CMS is reusable across different site designs.

---

## 2. Deployment Modes

| Mode | Use when | Plugin file |
|---|---|---|
| **Plugin + Theme** | New project — keep CMS and design separate | `ah-cms.php` (plugin) + separate theme |
| **Standalone Theme** | Legacy / single install — everything in one theme folder | `functions.php` bootstraps everything |

For any new project, **use Mode 1** (Plugin + Companion Theme).

---

## 3. Mode 1: Plugin + Companion Theme (Recommended)

### Step 1 — Copy and install the plugin

1. Copy the entire `ah_final_theme/` folder
2. Rename it to `ah-cms`
3. Move it to `wp-content/plugins/ah-cms/`
4. In WP Admin → Plugins → **Activate "AH CMS"**
5. On activation, all 50+ database tables are created and seeded automatically

### Step 2 — Verify the CMS works

1. Go to WP Admin sidebar → **CMS Portal**
2. Click **Admin Actions → DB Health Check** → should show all tables OK
3. Click **Admin Actions → Load Demo Data** → inserts sample content into all tables
4. Browse the admin pages (Services, Reviews, FAQs, etc.) to confirm data is there

### Step 3 — Create your companion theme

1. Create a new folder: `wp-content/themes/your-theme-name/`
2. Add `style.css` with the WordPress theme header:

```css
/*
Theme Name: Your Theme Name
Theme URI:  https://yoursite.com
Author:     Your Name
Version:    1.0.0
Text Domain: your-theme
*/
```

3. Add a minimal `functions.php`:

```php
<?php
defined( 'ABSPATH' ) || exit;

// Plugin must be active — all models and helpers are autoloaded by the plugin.
if ( ! defined( 'AH_PLUGIN_DIR' ) ) {
    add_action( 'admin_notices', function () {
        echo '<div class="notice notice-error"><p>AH CMS plugin must be active.</p></div>';
    } );
    return;
}

add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-list', 'gallery', 'caption' ) );
    add_theme_support( 'custom-logo' );
    load_theme_textdomain( 'your-theme', get_template_directory() . '/languages' );
    register_nav_menus( array(
        'primary' => 'Primary Menu',
        'footer'  => 'Footer Menu',
    ) );
} );

// Enqueue your theme's CSS/JS
AH_Asset_Loader::init();
```

4. Add `index.php` (required by WordPress):

```php
<?php
// Silence is golden.
```

5. Activate the theme in **WP Admin → Appearance → Themes**

### Step 4 — Set up WordPress pages

For each front-end page, create a WordPress page and assign the right template:

| WP Page title | Page slug | Template to assign |
|---|---|---|
| Home | `home` | `template-home.php` (build this) |
| About | `about` | `template-about.php` (build this) |
| Services | `services` | `template-services.php` (build this) |
| Contact | `contact` | `template-contact.php` (copy from plugin) |
| Client Stories | `client-stories` | `template-client-stories.php` (build this) |
| Blog | `blog` | `template-blog.php` (build this) |

### Step 5 — Set up navigation

1. WP Admin → Appearance → Menus
2. Create a menu named "Primary"
3. Add your pages to it
4. Assign it to the "Primary Menu" location

---

## 4. Mode 2: Standalone Theme

The same folder works as a standalone theme with zero changes.

1. Copy `ah_final_theme/` to `wp-content/themes/`
2. Activate in **WP Admin → Appearance → Themes**
3. Theme activation fires `after_switch_theme` → DB installer runs automatically
4. CMS Portal appears in the WP admin sidebar

In this mode, `functions.php` detects `AH_PLUGIN_DIR` is not defined and runs the full bootstrap itself. All constants, autoloader, admin portal, AJAX handlers, and DB installer are loaded from `functions.php`.

---

## 5. Building the Companion Theme

### Minimum file structure

```
your-theme/
├── style.css                   WP theme header
├── index.php                   WP fallback (required)
├── functions.php               Theme bootstrap (thin — plugin does the heavy lifting)
├── parts/
│   ├── header.php              Site header: logo, nav, news bar
│   └── footer.php              Site footer: links, social, copyright
├── assets/
│   ├── css/
│   │   ├── variables.css       CSS custom properties / design tokens
│   │   ├── animations.css      Keyframe utilities
│   │   └── main.css            All page CSS
│   └── js/
│       └── main.js             Frontend interactivity
├── template-home.php           Home page (12+ sections)
├── template-about.php          About page
├── template-services.php       Services listing
├── template-client-stories.php Gallery + videos + reviews
├── template-contact.php        Contact form + info
├── template-blog.php           Blog listing with filters
└── single-post.php             Single blog post
```

### Parts header pattern

```php
<?php // parts/header.php
$settings  = (new AH_Settings_Model())->get_all_by_group('general');
$nav_items = (new AH_Nav_Model())->get_menu_items('primary');
$news_items = (new AH_Newsbar_Model())->get_active();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if ( $news_items ) : ?>
<div class="news-bar">
    <?php foreach ( $news_items as $item ) : ?>
        <span><?php echo esc_html( $item->text ); ?></span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<header class="site-header">
    <a href="<?php echo esc_url( home_url('/') ); ?>" class="site-logo">
        <?php echo esc_html( $settings['site_name'] ?? get_bloginfo('name') ); ?>
    </a>
    <nav class="primary-nav">
        <?php foreach ( $nav_items as $item ) : ?>
        <a href="<?php echo esc_url( $item->url ); ?>"><?php echo esc_html( $item->label ); ?></a>
        <?php endforeach; ?>
    </nav>
</header>
```

### Parts footer pattern

```php
<?php // parts/footer.php
$footer   = (new AH_Footer_Model())->get_config();
$contacts = (new AH_Footer_Model())->get_contact_links();
$socials  = (new AH_Footer_Model())->get_social_links();
?>
<footer class="site-footer">
    <div class="footer-contacts">
        <?php foreach ( $contacts as $link ) : ?>
        <a href="<?php echo esc_url( $link->url ); ?>"><?php echo esc_html( $link->label ); ?></a>
        <?php endforeach; ?>
    </div>
    <div class="footer-social">
        <?php foreach ( $socials as $social ) : ?>
        <a href="<?php echo esc_url( $social->url ); ?>" target="_blank"><?php echo esc_html( $social->platform ); ?></a>
        <?php endforeach; ?>
    </div>
    <p>&copy; <?php echo esc_html( $footer->copyright ?? date('Y') ); ?></p>
</footer>
<?php wp_footer(); ?>
</body>
</html>
```

---

## 6. Design Spec → DB Model Mapping

Based on `ah_theme_scratch/makedesignthings.md`:

### Common (every page)

| Element | Model / Source |
|---|---|
| Header + Nav | `AH_Nav_Model::get_menu_items('primary')` |
| News Bar | `AH_Newsbar_Model::get_active()` |
| Footer | `AH_Footer_Model::get_config()`, `get_contact_links()`, `get_social_links()` |
| WhatsApp floating button | Site setting: `floating_whatsapp` or `AH_Settings_Model::get_value('whatsapp_number')` |
| Contact Us bar | Site setting or hardcoded |

### Home Page

| Section | Model call |
|---|---|
| Hero (left text, right image) | `AH_Home_Model::get_hero()` |
| Highlight ScrollBar | `AH_Home_Model::get_highlights()` |
| Why You Need Us | `AH_Home_Model::get_why_us()` + `get_why_us_cards()` |
| Guide Through | `AH_Home_Model::get_guide()` + `get_guide_points()` |
| Stack Details | `AH_Home_Model::get_stack_items()` |
| Difference From Others | `AH_Home_Model::get_difference()` + `get_difference_rows()` |
| Features Properties | `AH_Home_Model::get_featured()` + `get_featured_items()` |
| Our Experience | `AH_Home_Model::get_experience()` + `get_experience_cards()` |
| Some Blogs | `(new AH_Posts_Model())->all(['status' => 'active'], 'created_at DESC', 3)` |
| Why Required (YouTube) | `AH_Home_Model::get_why_req()` + `get_why_req_cards()` |
| Reviews Slider | `(new AH_Reviews_Model())->get_active()` |
| FAQ | `(new AH_Faqs_Model())->get_active()` |

### Services Page

| Section | Model call |
|---|---|
| Heading section | `(new AH_Services_Model())->get_page_header()` |
| Services list | `(new AH_Services_Model())->get_active()` |
| Per-service bullet points | `(new AH_Services_Model())->get_bullet_points($service->id)` |

### About Page

| Section | Model call |
|---|---|
| Heading section | `(new AH_About_Model())->get_page_header()` |
| Story section | `(new AH_About_Model())->get_story()` + `get_story_points()` |
| Team Experts | `(new AH_Team_Model())->get_active()` |
| Values cards | `(new AH_About_Model())->get_values()` |

### Client Stories Page

| Section | Model call |
|---|---|
| Header | `(new AH_About_Model())->get_client_stories_header()` |
| Gallery images | `(new AH_About_Model())->get_gallery()` |
| Video links | `(new AH_About_Model())->get_videos()` |
| Reviews | `(new AH_Reviews_Model())->get_active()` |

### Contact Page

| Section | Model call |
|---|---|
| Contact config | `(new AH_Contact_Model())->get_config()` |
| Form | `do_shortcode('[ah_form id="1"]')` or `AH_Form_Builder::render(['id' => 1])` |

### Blog / Posts

| Section | Model call |
|---|---|
| Listing header | `(new AH_Posts_Model())->get_listing_header()` |
| Posts | `(new AH_Posts_Model())->paginate($page, 12, ['status' => 'active'])` |
| Taxonomies for filter | `(new AH_Taxonomy_Model())->get_types_with_terms()` |

---

## 7. Available Models — Quick Reference

All models extend `AH_Model_Base` and are autoloaded by the plugin.

```php
// Instantiate any model anywhere in a theme template:
$m = new AH_Home_Model();

// Shared base methods on every model:
$m->find(int $id)                              // single row by ID
$m->all(array $where, string $order, int $limit) // multiple rows
$m->paginate($page, $per_page, $where, $search)  // paginated with meta
$m->count(array $where)                        // total count
```

| Model class | What it reads |
|---|---|
| `AH_Settings_Model` | Site settings (name, email, phone, socials, etc.) |
| `AH_Nav_Model` | Navigation menus + items |
| `AH_Newsbar_Model` | Active news ticker items |
| `AH_Home_Model` | All home page sections (9 section types) |
| `AH_Services_Model` | Services + bullet points + page header |
| `AH_About_Model` | About page header + story + values + client stories |
| `AH_Reviews_Model` | Client reviews + page header |
| `AH_Faqs_Model` | FAQ entries + page header |
| `AH_Posts_Model` | Blog/news posts + taxonomy links + post links |
| `AH_Team_Model` | Team member profiles |
| `AH_Contact_Model` | Contact config + submissions |
| `AH_Taxonomy_Model` | Categories, tags, subtags |
| `AH_Footer_Model` | Footer config + contact links + social links |
| `AH_Media_Model` | Media library — resolve image ID to URL |
| `AH_Pages_Model` | CMS page registry (slug, type, sections) |
| `AH_Form_Builder` | Forms + fields + submissions (static methods) |

### Resolving image URLs

Images are stored as integer IDs in the DB. Always resolve at render time:

```php
$media = new AH_Media_Model();
$url   = $media->get_url( (int) $row->image_id );
// Returns '' if ID is 0 or missing — always check before using in img src
```

---

## 8. Frontend Template Examples

### Home page template skeleton

```php
<?php
/**
 * Template Name: Home Page
 */
defined( 'ABSPATH' ) || exit;

$home    = new AH_Home_Model();
$hero    = $home->get_hero();
$media   = new AH_Media_Model();

get_header(); // or include( get_template_directory() . '/parts/header.php' );
?>

<!-- Hero Section -->
<?php if ( $hero ) : ?>
<section class="section-hero">
    <div class="hero-text">
        <h1><?php echo esc_html( $hero->headline ); ?></h1>
        <p><?php echo esc_html( $hero->subheadline ); ?></p>
        <a href="<?php echo esc_url( $hero->cta_url ); ?>" class="btn-primary">
            <?php echo esc_html( $hero->cta_text ); ?>
        </a>
    </div>
    <div class="hero-image">
        <?php if ( $hero->image_id ) : ?>
        <img src="<?php echo esc_url( $media->get_url( (int) $hero->image_id ) ); ?>"
             alt="<?php echo esc_attr( $hero->headline ); ?>">
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Why You Need Us -->
<?php
$why_us      = $home->get_why_us();
$why_us_cards = $home->get_why_us_cards();
?>
<?php if ( $why_us && $why_us_cards ) : ?>
<section class="section-why-us">
    <h2><?php echo esc_html( $why_us->heading ); ?></h2>
    <p><?php echo esc_html( $why_us->description ); ?></p>
    <div class="card-grid">
        <?php foreach ( $why_us_cards as $card ) : ?>
        <div class="card">
            <?php if ( $card->image_id ) : ?>
            <img src="<?php echo esc_url( $media->get_url( (int) $card->image_id ) ); ?>" alt="">
            <?php endif; ?>
            <h3><?php echo esc_html( $card->title ); ?></h3>
            <p><?php echo esc_html( $card->description ); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Reviews Slider -->
<?php $reviews = (new AH_Reviews_Model())->get_active(); ?>
<?php if ( $reviews ) : ?>
<section class="section-reviews">
    <div class="reviews-slider">
        <?php foreach ( $reviews as $review ) : ?>
        <div class="review-card">
            <?php if ( $review->image_id ) : ?>
            <img src="<?php echo esc_url( $media->get_url( (int) $review->image_id ) ); ?>" alt="">
            <?php endif; ?>
            <p><?php echo esc_html( $review->review_text ); ?></p>
            <strong><?php echo esc_html( $review->reviewer_name ); ?></strong>
            <span><?php echo esc_html( str_repeat('★', (int) $review->star_rating) ); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
```

### Services page template skeleton

```php
<?php
/**
 * Template Name: Services Page
 */
defined( 'ABSPATH' ) || exit;

$svc_model = new AH_Services_Model();
$header    = $svc_model->get_page_header();
$services  = $svc_model->get_active();
$media     = new AH_Media_Model();

get_header();
?>

<?php if ( $header ) : ?>
<section class="page-header">
    <h1><?php echo esc_html( $header->heading ); ?></h1>
    <p><?php echo esc_html( $header->description ); ?></p>
</section>
<?php endif; ?>

<?php foreach ( $services as $svc ) : ?>
<article class="service-block">
    <?php if ( $svc->image_id ) : ?>
    <img src="<?php echo esc_url( $media->get_url( (int) $svc->image_id ) ); ?>"
         alt="<?php echo esc_attr( $svc->title ); ?>">
    <?php endif; ?>
    <h2><?php echo esc_html( $svc->title ); ?></h2>
    <?php echo wp_kses_post( $svc->description ); ?>
    <?php $bullets = $svc_model->get_bullet_points( $svc->id ); ?>
    <?php if ( $bullets ) : ?>
    <ul>
        <?php foreach ( $bullets as $b ) : ?>
        <li><?php echo esc_html( $b->point ); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</article>
<?php endforeach; ?>

<?php get_footer(); ?>
```

---

## 9. Companion Theme Checklist

### Initial setup

- [ ] Plugin folder renamed to `ah-cms` and placed in `wp-content/plugins/`
- [ ] Plugin activated in WP Admin → Plugins
- [ ] DB Health Check: all tables OK
- [ ] Demo data loaded (Admin Actions → Load Demo Data)

### Theme files

- [ ] `style.css` — theme header with Theme Name, Version, Author
- [ ] `index.php` — WP fallback (can be empty silence block)
- [ ] `functions.php` — plugin check, `after_setup_theme`, `AH_Asset_Loader::init()`
- [ ] `parts/header.php` — logo, nav, news bar
- [ ] `parts/footer.php` — links, social, copyright
- [ ] `assets/css/variables.css` — color tokens, font sizes, spacing
- [ ] `assets/css/animations.css` — keyframes, transitions
- [ ] `assets/css/main.css` — all component and layout CSS
- [ ] `assets/js/main.js` — sliders, accordions, mobile nav, scroll effects

### Page templates (from `makedesignthings.md`)

- [ ] `template-home.php` — hero, highlights, why us, guide, stack, difference, featured, experience, blogs, why req, reviews, faqs
- [ ] `template-about.php` — header, story, team, values
- [ ] `template-services.php` — header, service cards with bullet points
- [ ] `template-client-stories.php` — header, gallery, videos, reviews
- [ ] `template-contact.php` — contact form (use `[ah_form id="1"]` shortcode) + info sidebar
- [ ] `template-blog.php` — listing header, post cards, taxonomy filter, pagination
- [ ] `single-post.php` — full post content, image banner, links, related posts

### WordPress setup

- [ ] WordPress pages created for: Home, About, Services, Contact, Client Stories, Blog
- [ ] Each page assigned the correct template
- [ ] Navigation menu created and assigned to "Primary Menu" location
- [ ] Home page set to static front page in WP Admin → Settings → Reading
- [ ] Permalink structure set to `/%postname%/` in WP Admin → Settings → Permalinks (then flush rewrite rules)
