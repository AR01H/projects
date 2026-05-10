# 3. Create a New Page — Code + UI + Header/Footer

## Two Ways to Create a Page

| Method | Use when |
|---|---|
| **Code (PHP template)** | Fixed layout, custom logic, special design |
| **WordPress UI editor** | Simple text/content pages, policy pages |

---

## METHOD A — Create Page with Code (PHP Template)

### Step 1 — Create the template file

```
canehouse-theme/page-yourpage.php
```

### Step 2 — Choose your header/footer option

**With header + footer (same as rest of site):**
```php
<?php
/**
 * Template Name: My New Page
 */
get_header(); ?>

<main class="inner-page">
  <div class="inner-container">

    <h1>Page Title</h1>
    <p>Your content here.</p>

    <!-- Any HTML you want -->

  </div>
</main>

<style>
.inner-page {
  padding-top: 100px;   /* space for fixed nav */
  min-height: 80vh;
  background: #fdfff8;
}
.inner-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 40px 24px;
}
</style>

<?php get_footer(); ?>
```

**Without header/footer (fully standalone):**
```php
<?php
/**
 * Template Name: My Standalone Page
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); /* loads WordPress CSS/JS */ ?>
</head>
<body>

  <!-- Your full page content -->

<?php wp_footer(); ?>
</body>
</html>
```

**With only header (no footer):**
```php
<?php
/**
 * Template Name: Header Only Page
 */
get_header(); ?>

<main>
  <!-- content -->
</main>

<!-- custom footer just for this page -->
<footer style="background:#111;color:#fff;padding:20px;text-align:center;">
  My custom footer
</footer>

<?php wp_footer(); ?>
</body>
</html>
```

---

### Step 3 — Read data from DB (if needed)

If your page needs to show dynamic data from the theme tables:

```php
<?php
/**
 * Template Name: Flavours Page
 */
get_header();

// Read active flavours from DB
$flavours = ch_get_active('flavours', 'sort_order ASC');
// Options: reviews, order_steps, flavours, events, faqs,
//          franchise_locs, benefits, showcase_slides
?>

<main class="inner-page">
  <div class="inner-container">
    <h1>Our Flavours</h1>
    <div class="flavour-grid">
      <?php foreach($flavours as $f): ?>
        <div class="flavour-card">
          <?php if($f->image_url): ?>
            <img src="<?php echo esc_url($f->image_url); ?>" alt="<?php echo esc_attr($f->name); ?>">
          <?php endif; ?>
          <h3><?php echo esc_html($f->name); ?></h3>
          <p><?php echo esc_html($f->price); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<?php get_footer(); ?>
```

---

### Step 4 — Read global site settings

```php
// Read any setting from Site Settings page
$phone   = ch_opt('phone');
$wa      = ch_opt('whatsapp');
$email   = ch_opt('email');
$ig      = ch_opt('social_ig');
```

---

## METHOD B — Create Page from WordPress UI

### Step 1
- **Pages → Add New**
- Give it a title

### Step 2 — Choose template
In **Page Attributes** on the right:
- **Default Template** → uses `page.php` (minimal, no special styling)
- **Policy Page** → uses `page-policy.php` (styled, breadcrumb, last updated date)
- **User Login Page** → uses `page-user-login.php` (standalone)
- **Any custom template you created** → shows up here automatically

### Step 3 — Write content
Use the WordPress block editor to add:
- Paragraphs, headings, images
- Lists, tables, buttons
- Any HTML via a "Custom HTML" block

### Step 4 — Set URL slug
- Right side → **Permalink → Edit**
- Type your slug e.g. `about-us` or `our-story`
- **Update** ✅

---

## Adding the Page to Navigation

After creating the page:
- Go to **Appearance → Menus**
- On the left find your new page
- Check the box → **Add to Menu**
- Drag it to the position you want
- **Save Menu** ✅

---

## Quick Reference

| Goal | Template line | File name |
|---|---|---|
| With header + footer | `get_header()` + `get_footer()` | `page-anything.php` |
| Standalone | Full `<!DOCTYPE html>` | `page-anything.php` |
| Header only | `get_header()` + manual footer | `page-anything.php` |
| Read DB data | `ch_get_active('table_name')` | any template |
| Read site settings | `ch_opt('key')` | any template |
| Policy-style page | Template: Policy Page | select in UI |

## Available ch_opt() Keys

```
phone         whatsapp      email         website
address       social_ig     social_fb     social_tt
social_yt     footer_copyright            footer_desc
marquee       maps_embed    header_notice header_notice_on
```

## Available ch_get_active() Tables

```
reviews        order_steps    flavours      events
faqs           franchise_locs benefits      showcase_slides
```