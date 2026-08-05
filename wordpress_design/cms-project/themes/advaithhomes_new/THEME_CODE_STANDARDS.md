# Theme Code Standards

> Coding standards for humans and AI to follow when writing or modifying theme code.

---

## Architecture: Data Flow

```
data/advaith/json/terms.json     → Site terminology (adn_term() reads from here)
data/advaith/json/{slug}.json    → Page-specific content
apis/services.php                → Data loading functions
src/Service/*Context.php         → Data fetching, shaping, caching
pages/Page*.php                  → Page containers (structure only)
components/sections/*.php        → Section rendering (receives props)
components/parts/*.php           → Reusable parts (sidebars, widgets)
components/cards/*.php           → Repeated card items
```

**Flow:** JSON → Service → Context → Page → Components

---

## File Naming Conventions

| Type | Pattern | Example |
|------|---------|---------|
| Page templates | `Page{Name}.php` | `PageHome.php`, `PageGuides.php` |
| Section components | `sections/{name}.php` | `sections/hero_home.php` |
| Part components | `parts/{name}.php` | `parts/breadcrumb.php` |
| Card components | `cards/{name}.php` | `cards/guide_card.php` |
| Context classes | `{Name}Context.php` | `GuideContext.php` |
| JSON data files | `{slug}.json` | `buying.json`, `home_page.json` |
| Feature modules | `src/Feature/{Name}/` | `src/Feature/Home/` |

---

## Terminology System (adn_term)

All site-specific text comes from `data/advaith/json/terms.json` via `adn_term()`:

```php
// GOOD - from terms.json
echo adn_term( 'buttons.explore_all', 'Explore all' );
echo adn_term( 'calculators_page.hero_title', 'All Calculators' );

// BAD - hardcoded text
echo 'Explore all';
echo 'All Calculators';
```

**Constants defined from terms.json:**
- `SITE_BRAND_NAME` → `adn_term( 'brand.name', 'MY SITE' )`
- `SITE_NEWS_URL` → `adn_term( 'urls.news', '/news/' )`
- `SITE_TOOLS_PLURAL` → `adn_term( 'features.tools_plural', 'Calculators' )`
- `PAGE_TITLE_HOME` → `adn_term( 'page_titles.home', 'Home' )`

---

## Component Structure Rules

### Props
- Components receive data via extracted variables
- Always validate with `isset()` and provide defaults
- Use `esc_html()` for text, `esc_url()` for URLs, `esc_attr()` for attributes

```php
<?php
$hero = isset( $hero ) && is_array( $hero ) ? $hero : array();
$title = isset( $hero['title'] ) ? (string) $hero['title'] : '';
?>
<h1><?php echo esc_html( $title ); ?></h1>
```

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

## Controller Rules

Controllers should be thin wrappers that delegate to Context classes:

### GOOD - Thin Controller
```php
class MyController {
    public static function getContext() {
        return MyContext::getContext();
    }
}
```

### BAD - Controller with too much logic
```php
class MyController {
    public static function getContext() {
        // 100+ lines of data fetching and shaping...
    }
}
```

---

## Context Classes: Split Big Functions

**Rule:** No function should exceed 100 lines. Split `getContext()` into small `build*` methods.

### BAD - 400+ line getContext()
```php
class MyContext {
    public static function getContext() {
        // 400 lines of mixed logic...
    }
}
```

### GOOD - Split into focused methods
```php
class MyContext {
    public static function getContext() {
        $ctx = array(
            'hero'       => self::buildHero( $data ),
            'guides'     => self::buildGuides( $data ),
            'sidebar'    => self::buildSidebar( $data ),
        );
        return $ctx;
    }
    
    public static function buildHero( array $data ): array { /* <50 lines */ }
    public static function buildGuides( array $data ): array { /* <50 lines */ }
    public static function buildSidebar( array $data ): array { /* <50 lines */ }
}
```

---

## Caching

All Context classes must cache their results:

```php
$cache_key = 'page_{name}_context_' . $slug;
$cached = self::cacheGet( $cache_key );
if ( false !== $cached ) {
    return $cached;
}

// ... build context ...

self::cacheSet( $cache_key, $ctx );
return $ctx;
```

---

## JSON Data Structure

### Page JSON Required Fields
```json
{
    "meta": {
        "slug": "page-slug",
        "page_title": "Page Title - Site Name",
        "meta_description": "SEO description."
    },
    "breadcrumb": [
        { "label": "Home", "url": "/" },
        { "label": "Page", "url": null }
    ]
}
```

### terms.json Structure
```json
{
    "brand": { "name": "...", "icon": "..." },
    "taxonomy": { "parent": "...", "section": "..." },
    "urls": { "home": "/", "guides": "/guides/" },
    "cta": { "nav_label": "...", "hero_primary": "..." },
    "buttons": { "explore_all": "...", "search": "..." },
    "labels": { "popular": "...", "latest_news": "..." },
    "sidebar": { "browse_category": "...", "related_guides": "..." }
}
```

---

## Do's and Don'ts

### Do's
- Use `adn_term()` for all site terminology
- Use `adn_component()` to render components
- Use `adn_link()` for every stored URL — it resolves site paths via `home_url()` and passes external URLs through untouched. Never wrap a stored URL in `home_url()` directly; that corrupts external links.
- Pair it with `adn_link_target_attr( $raw_url )` so external links open in a new tab with `rel="noopener noreferrer"`
- Cache Context results with `ADN_Cache`
- Split large functions into small `build*` methods
- Register SEO with `adn_seo_register()`
- Open pages with `adn_page_open()`

### Don'ts
- Don't hardcode text in PHP files
- Don't use raw `$wpdb` in Context classes
- Don't create duplicate components
- Don't skip HTML escaping
- Don't have functions over 100 lines
- Don't keep old duplicate files in `src/Service/` (use `src/Feature/*/Service/` instead)

---

## File Organization

### Feature Folders
Each feature has its own folder with Controller and Service:

```
src/Feature/{Name}/
├── {Name}Feature.php        → Feature registration
├── Controller/
│   └── {Name}Controller.php → Thin wrapper (calls Context)
└── Service/
    └── {Name}Context.php    → Data fetching, shaping, caching
```

### What Goes Where

| File | Purpose | Max Lines |
|------|---------|-----------|
| `Controller/*.php` | Thin wrapper, calls Context | 50 |
| `Service/*Context.php` | Data fetching, shaping, caching | 200 (split into build*) |
| `components/sections/*.php` | Section rendering | 100 |
| `components/parts/*.php` | Reusable parts | 80 |
| `pages/Page*.php` | Page containers | 100 |
