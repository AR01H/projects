# Theme Migration Guide

> How to add new pages or migrate existing pages to the reusable component system.

---

## Architecture Overview

```
data/advaith/json/terms.json     → Site terminology (adn_term() reads from here)
data/advaith/json/{slug}.json    → Page-specific content
apis/services.php                → Data loading functions
src/Feature/{Name}/Service/      → Context classes (data fetching, shaping, caching)
src/Feature/{Name}/Controller/   → Controllers
pages/Page*.php                  → Page containers (structure only)
components/sections/*.php        → Section rendering (receives props)
components/parts/*.php           → Reusable parts (sidebars, widgets)
components/cards/*.php           → Repeated card items
```

**Data flow:** JSON → Service → Context → Page → Components

---

## How to Add a New Page

### Step 1: Add Terminology to terms.json

Add any page-specific terms to `data/advaith/json/terms.json`:

```json
{
    "my_page": {
        "hero_title": "My Page Title",
        "hero_desc": "Description text.",
        "cta_button": "Get Started"
    }
}
```

### Step 2: Create JSON Data File

Create `data/advaith/json/{slug}.json`:

```json
{
    "meta": {
        "slug": "my-page",
        "page_title": "My Page - Site Name",
        "meta_description": "SEO description."
    },
    "breadcrumb": [
        { "label": "Home", "url": "/" },
        { "label": "My Page", "url": null }
    ],
    "hero": {
        "title": "My Page Title",
        "description": "Page description."
    }
}
```

### Step 3: Create Context Class

Create `src/Feature/MyPage/Service/MyPageContext.php`:

```php
<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

class MyPageContext {

    // Cache helpers
    private static function cacheGet( string $key ) {
        if ( class_exists( 'ADN_Cache' ) ) {
            return \ADN_Cache::get( $key, 'pages' );
        }
        return false;
    }

    private static function cacheSet( string $key, array $ctx ): void {
        if ( class_exists( 'ADN_Cache' ) ) {
            \ADN_Cache::set( $key, $ctx, 'pages', get_option( 'ah_cache_expiry', 3600 ) );
        }
    }

    // Split into small build* methods (max 50 lines each)
    public static function buildHero( array $data ): array {
        return array(
            'title'       => $data['hero']['title'] ?? '',
            'description' => $data['hero']['description'] ?? '',
        );
    }

    public static function buildContent( array $data ): array {
        return $data['content'] ?? array();
    }

    // Main getContext calls build* methods
    public static function getContext() {
        $slug = self::resolveSlug();
        $cache_key = 'page_my_page_' . $slug;

        $cached = self::cacheGet( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $data = adn_service_my_page_data( $slug );

        $ctx = array(
            'hero'    => self::buildHero( $data ),
            'content' => self::buildContent( $data ),
        );

        self::cacheSet( $cache_key, $ctx );
        return $ctx;
    }
}
```

### Step 4: Create Page Template

Create `pages/PageMyPage.php`:

```php
<?php
/**
 * Template Name: My Page
 */
defined( 'ABSPATH' ) || exit;

$ctx = \Adn\Theme\Service\MyPageContext::getContext();

get_header();

adn_seo_register( array(
    'title'       => $ctx['meta']['page_title'] ?? '',
    'description' => $ctx['meta']['meta_description'] ?? '',
    'breadcrumb'  => $ctx['breadcrumb'],
) );

adn_page_open( $ctx );
?>

<section class="page-section">
    <div class="container">
        <?php adn_component( 'sections/page_hero', array( 'hero' => $ctx['hero'] ) ); ?>
        <?php adn_component( 'sections/guides_grid', array( 'guides' => $ctx['content'] ) ); ?>
    </div>
</section>

<?php get_footer(); ?>
```

---

## How to Reuse Components

### Available Components

| Component | Purpose | Props |
|-----------|---------|-------|
| `sections/hero_home` | Home page hero | `$hero { title_lines, actions, trust_items }` |
| `sections/page_hero` | Generic page hero | `$hero { title, description }` |
| `sections/guides_hero` | Guides listing hero | `$hero { eyebrow, title, description }` |
| `sections/guides_grid` | Guide cards grid | `$guides { items, pagination }` |
| `sections/journey` | Journey cards | `$cards { items }` |
| `parts/breadcrumb` | Breadcrumb navigation | `$breadcrumb { items }` |
| `parts/cta_banner` | CTA section | `$cta { title, description, button }` |
| `parts/faq_list` | FAQ accordion | `$faqs { items }` |

### Using Components
```php
adn_component( 'sections/page_hero', array(
    'hero' => array(
        'title'       => 'Page Title',
        'description' => 'Page description.',
    ),
) );
```

---

## Common Patterns

### Pattern 1: Page with Hero + Content
```php
adn_component( 'sections/page_hero', array( 'hero' => $ctx['hero'] ) );
adn_component( 'sections/guides_grid', array( 'guides' => $ctx['guides'] ) );
```

### Pattern 2: Conditional Section
```php
<?php if ( ! empty( $ctx['faq']['items'] ) ) : ?>
    <section class="faq-section">
        <?php adn_component( 'parts/faq_list', array( 'faqs' => $ctx['faq'] ) ); ?>
    </section>
<?php endif; ?>
```

---

## Checklist for New Pages

- [ ] Terms added to `terms.json` (if needed)
- [ ] JSON data file created in `data/advaith/json/`
- [ ] Context class created in `src/Feature/{Name}/Service/`
- [ ] Context uses caching
- [ ] Context methods are < 100 lines each
- [ ] Page template created in `pages/`
- [ ] No hardcoded content in page template
- [ ] SEO registered with `adn_seo_register()`
- [ ] Page opened with `adn_page_open()`
- [ ] Components receive data via props
- [ ] All text uses `esc_html()` for escaping
