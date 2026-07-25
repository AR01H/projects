# Theme Code Standards

> Coding standards for humans and AI to follow when writing or modifying theme code.

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

---

## Component Structure Rules

### Props
- Components receive data via `$props` array or extracted variables
- Always validate with `isset()` and provide defaults
- Use `esc_html()` for text, `esc_url()` for URLs, `esc_attr()` for attributes

```php
<?php
$hero = isset( $hero ) && is_array( $hero ) ? $hero : array();
$title = isset( $hero['title'] ) ? (string) $hero['title'] : '';
?>
<h1><?php echo esc_html( $title ); ?></h1>
```

### Rendering
- Components output HTML directly
- No data fetching inside components
- No database queries inside components
- Use `adn_component()` to render child components

```php
<?php adn_component( 'parts/faq_list', array( 'faqs' => $ctx['faq'] ) ); ?>
```

### Error Handling
- Check array keys with `isset()` before accessing
- Provide fallback values for missing data
- Never output empty elements

---

## JSON Data Structure Standards

### File Location
- `data/advaith/json/{slug}.json` for page-specific data
- `data/config/*.json` for site-wide config

### Required Fields
Every page JSON must have:
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

### Naming Conventions
- Use `snake_case` for keys: `page_title`, `meta_description`
- Use `lowercase` for slugs: `buying-guide`
- Use `PascalCase` for component names in PHP

---

## PHP Coding Standards

### No Hardcoded Content
```php
// BAD - hardcoded content
<h1>Welcome to Our Site</h1>
<p>We are the best.</p>

// GOOD - from JSON data
<h1><?php echo esc_html( $ctx['hero']['title'] ); ?></h1>
<p><?php echo esc_html( $ctx['hero']['description'] ); ?></p>
```

### Proper Escaping
```php
// Text output
echo esc_html( $variable );

// URLs
echo esc_url( $url );

// HTML attributes
echo esc_attr( $value );

// HTML content (use sparingly)
echo wp_kses_post( $html_content );
```

### Context Classes
- Always extend or follow the pattern of existing Context classes
- Cache expensive queries with `ADN_Cache`
- Return array with consistent structure

```php
class MyContext {
    public static function getContext(): array {
        $cache_key = 'my_context_' . $slug;
        $cached = \ADN_Cache::get( $cache_key, 'pages' );
        if ( false !== $cached ) return $cached;
        
        // ... fetch and shape data ...
        
        \ADN_Cache::set( $cache_key, $ctx, 'pages', 3600 );
        return $ctx;
    }
}
```

---

## Reusability Guidelines

### When to Create a New Component
- Same UI pattern appears in 2+ places
- Component has clear input/output contract
- Component is self-contained (no external dependencies)

### When NOT to Create a New Component
- One-off UI elements
- Page-specific layouts that won't be reused
- Simple wrappers with no logic

### Component Naming
- Use descriptive names: `guide_hero`, `faq_list`, `cta_banner`
- Prefix with section type: `sections/`, `parts/`, `cards/`
- Keep names short but clear

---

## Do's and Don'ts

### Do's
- Use `adn_component()` to render components
- Use `adn_term()` for site terminology
- Use `adn_link()` for internal URLs
- Use `adn_icon()` for SVG icons
- Register SEO with `adn_seo_register()`
- Open pages with `adn_page_open()`
- Cache expensive queries
- Validate all input data

### Don'ts
- Don't hardcode content in PHP files
- Don't make database queries in components
- Don't create duplicate components
- Don't skip HTML escaping
- Don't use `extract()` on user data
- Don't ignore WordPress coding standards
