# Theme Migration Guide

> How to add new pages or migrate existing pages to use the reusable component system.

---

## Architecture Overview

```
data/advaith/json/*.json          → Content data (JSON files)
apis/services.php                 → Data loading functions
src/Service/*Context.php          → Data fetching & shaping into context
pages/Page*.php                   → Page containers (structure only, no content)
components/sections/*.php         → Section rendering (receives props from context)
components/parts/*.php            → Reusable parts (sidebars, widgets, etc.)
components/cards/*.php            → Repeated card items
```

**Data flow:** JSON → Service → Context → Page → Components

---

## How to Add a New Guide Page

### Step 1: Create JSON Data File

Create `data/advaith/json/{slug}.json` with the page content:

```json
{
    "meta": {
        "slug": "my-guide",
        "page_title": "My Guide - Site Name",
        "meta_description": "Description for SEO."
    },
    "breadcrumb": [
        { "label": "Home", "url": "/" },
        { "label": "My Guide", "url": null }
    ],
    "hero": {
        "title": "My Guide Title",
        "description": "Guide description text."
    },
    "sections": [],
    "sidebar": {}
}
```

### Step 2: Create Context Class

Create `src/Service/MyGuideContext.php`:

```php
<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

class MyGuideContext {

    public static function getContext(): array {
        $slug   = self::getSlug();
        $data   = adn_service_guide_data( $slug );
        $chrome = adn_service_site_chrome();

        return array(
            'slug'       => $slug,
            'meta'       => $data['meta'] ?? array(),
            'breadcrumb' => $data['breadcrumb'] ?? array(),
            'article'    => $data['article'] ?? array(),
            'sections'   => $data['sections'] ?? array(),
            'sidebar'    => $data['sidebar'] ?? array(),
            'chrome'     => $chrome,
        );
    }

    private static function getSlug(): string {
        $page = get_queried_object();
        return sanitize_key( $page->post_name ?? '' );
    }
}
```

### Step 3: Create Page Template

Create `pages/PageMyGuide.php`:

```php
<?php
/**
 * Template Name: My Guide
 */
defined( 'ABSPATH' ) || exit;

$ctx = \Adn\Theme\Service\MyGuideContext::getContext();

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
        <?php adn_component( 'sections/guides_grid', array( 'guides' => $ctx['guides'] ) ); ?>
    </div>
</section>

<?php get_footer(); ?>
```

---

## How to Reuse Existing Components

### Available Components

| Component | Purpose | Props |
|-----------|---------|-------|
| `sections/hero_home` | Home page hero | `$hero { title_lines, actions, trust_items }` |
| `sections/page_hero` | Generic page hero | `$hero { title, description }` |
| `sections/guides_hero` | Guides listing hero | `$hero { eyebrow, title, description }` |
| `sections/guides_grid` | Guide cards grid | `$guides { items, pagination, sort_options }` |
| `sections/journey` | Journey cards | `$cards { items }` |
| `sections/page_hero_bg_banner` | Hero with background image | `$hero_img, $media_type` |
| `parts/breadcrumb` | Breadcrumb navigation | `$breadcrumb { items }` |
| `parts/cta_banner` | CTA section | `$cta { title, description, button }` |
| `parts/faq_list` | FAQ accordion | `$faqs { items }` |

### Using Components

```php
// Single component
adn_component( 'sections/page_hero', array(
    'hero' => array(
        'title'       => 'Page Title',
        'description' => 'Page description.',
    ),
) );

// Component with sidebar layout
adn_component( 'sections/guides_grid', array(
    'guides' => $ctx['guides'],
) );
```

---

## Common Patterns

### Pattern 1: Page with Hero + Content + Sidebar

```php
$page_ctx = adn_page_open( $ctx );

// Hero
adn_component( 'sections/page_hero', array( 'hero' => $ctx['hero'] ) );

// Content with sidebar
adn_component( 'parts/page_with_sidebar', array(
    'main'    => function () use ( $ctx ) {
        adn_component( 'sections/guides_grid', array( 'guides' => $ctx['guides'] ) );
    },
    'sidebar' => function () use ( $ctx ) {
        adn_component( 'parts/sidebar_guide_parents', array( 'groups' => $ctx['sidebar']['groups'] ) );
    },
) );
```

### Pattern 2: Section with Dynamic Data

```php
<?php foreach ( $ctx['items'] as $item ) : ?>
    <div class="card">
        <h3><?php echo esc_html( $item['title'] ); ?></h3>
        <p><?php echo esc_html( $item['description'] ); ?></p>
    </div>
<?php endforeach; ?>
```

### Pattern 3: Conditional Section Rendering

```php
<?php if ( ! empty( $ctx['faq']['items'] ) ) : ?>
    <section class="faq-section">
        <?php adn_component( 'parts/faq_list', array( 'faqs' => $ctx['faq'] ) ); ?>
    </section>
<?php endif; ?>
```

---

## Common Mistakes to Avoid

1. **Don't hardcode content** in page templates - always use JSON data
2. **Don't read data directly** in page templates - use Context classes
3. **Don't create duplicate components** - reuse existing ones when possible
4. **Don't skip SEO registration** - always call `adn_seo_register()`
5. **Don't forget `adn_page_open()`** - required for page chrome (header/footer)

---

## Checklist for New Pages

- [ ] JSON data file created in `data/advaith/json/`
- [ ] Context class created in `src/Service/`
- [ ] Page template created in `pages/`
- [ ] No hardcoded content in page template
- [ ] SEO registered with `adn_seo_register()`
- [ ] Page opened with `adn_page_open()`
- [ ] Components receive data via props
- [ ] All text uses `esc_html()` for escaping
