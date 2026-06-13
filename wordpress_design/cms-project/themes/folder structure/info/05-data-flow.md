# 05 - Data Flow: Fetcher → Model → Component → Template

## The Core Principle

Every request that needs data follows the same pipeline:

```
Query (data_fetcher) → Shape (model) → Display (component) → Page (template)
```

Each layer has one job and knows nothing about the others except its immediate neighbour.

---

## Layer 1 - Data Fetcher (`includes/data_fetcher/`)

**Job:** Run WP_Query / get_posts calls and return a consistent array shape.
**Rule:** Never output HTML. Never register hooks. Only query and return.

### Base Function

```php
function npt_fetch_posts( string $post_type, array $args = [], callable $formatter = null ): array {
    $query = new WP_Query( array_merge( [
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => NPT_POSTS_PER_PAGE,
        'no_found_rows'  => false,
    ], $args ) );

    $items = $formatter
        ? array_map( $formatter, $query->posts )
        : $query->posts;

    return [
        'items' => $items,     // array of posts (or shaped arrays if formatter given)
        'total' => $query->found_posts,
        'pages' => $query->max_num_pages,
    ];
}
```

### Why Always Return `['items', 'total', 'pages']`?

Consistent shape means every caller knows exactly what to expect:
```php
$result = npt_fetch_blog_posts();
$items  = $result['items'];   // always here
$total  = $result['total'];   // always here
$pages  = $result['pages'];   // always here
```

No need to check if the query returned one post vs. many, or remember WP_Query properties.

### Named Fetchers

Named fetchers wrap `npt_fetch_posts()` with sensible defaults:

```php
function npt_fetch_blog_posts( int $page = 1, int $per_page = 10 ): array {
    return npt_fetch_posts( 'post', [
        'paged'          => $page,
        'posts_per_page' => $per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ], 'npt_model_post' );       // ← passes model as formatter
}
```

**Note the third argument:** `'npt_model_post'` is the formatter.
This means `npt_fetch_posts()` calls `array_map( 'npt_model_post', $query->posts )`,
so `$result['items']` contains shaped arrays, not raw `WP_Post` objects.

### Single-Item Fetcher

```php
function npt_fetch_single( int|string $identifier, string $post_type = 'post' ): ?array {
    // accepts ID (int) or slug (string)
    …
    return $result['items'][0] ?? null;   // null if not found
}
```

Always returns `null` or a single shaped array - never a `WP_Post`.

---

## Layer 2 - Model (`apis/models/page-sample.php`)

**Job:** Transform a raw `WP_Post` object into a clean, typed PHP array.
**Rule:** Accept `WP_Post`, return `array`. No queries, no HTML, no hooks.

### Pattern

```php
function npt_model_post( WP_Post $post ): array {
    return [
        'id'        => $post->ID,
        'title'     => get_the_title( $post ),
        'slug'      => $post->post_name,
        'excerpt'   => get_the_excerpt( $post ),
        'thumbnail' => get_the_post_thumbnail_url( $post, NPT_IMG_CARD ) ?: null,
        'urls'      => npt_model_urls( $post ),   // ← shared URL block
    ];
}
```

### The Shared URL Block (`npt_model_urls`)

Every model calls `npt_model_urls()` to build a standard `urls` key:

```
'urls' => [
    'url'          → primary permalink
    'canonical'    → SEO canonical (overridable via meta)
    'redirect_url' → 301 destination (or null)
    'has_redirect' → true/false shortcut
    'alternate'    → {
        'url'   → alias URL
        'title' → alternate display name
        'slug'  → alternate slug for lookup
    }
]
```

All URL-related meta keys come from constants:
- `NPT_META_REDIRECT` → `npt_redirect_url`
- `NPT_META_ALT_URL` → `npt_alternate_url`
- `NPT_META_CANONICAL` → `npt_canonical_url`

### Why Shape Data in Models?

Without models, every template and API callback would contain duplicate formatting logic:
```php
// Bad: duplicated in 5 different files
$thumbnail = get_the_post_thumbnail_url( $post, 'large' ) ?: null;
$categories = get_the_terms( $post->ID, 'category' );
```

With models, you write it once:
```php
// Good: call the model once, get everything
$model = npt_model_post( $post );
$thumbnail  = $model['thumbnail'];
$categories = $model['categories'];
```

---

## Layer 3 - Component (`components/`)

**Job:** Receive an array of data, output HTML.
**Rule:** Accept context via `$context`, output HTML. No queries, no hooks.

### How Components Receive Data

`npt_component()` in `common/common_functions.php` uses `extract()`:

```php
function npt_component( string $name, array $context = [] ): void {
    $file = get_template_directory() . "/components/{$name}.php";
    extract( $context, EXTR_SKIP );
    include $file;
}
```

Inside the component file, the context keys become local variables:
```php
// Called with: npt_component( 'cards/page-sample', [ 'post' => $model ] )
// Inside component - $post is available as a local variable
$title = $post['title'] ?? 'Untitled';
```

### Safe Defaults Pattern

Every component variable should have a safe default:
```php
$post      = $post ?? [];
$title     = $post['title']     ?? 'Untitled';
$thumbnail = $post['thumbnail'] ?? null;
```
This means a component never crashes even if called with missing data.

### Component File Structure

```
components/
└── cards/
    ├── page-sample.php   ← template (HTML)
    └── style.css         ← scoped CSS (enqueued separately)
```

---

## Layer 4 - Page Template (`pages/`)

**Job:** Fetch data, check redirects, render components.
**Rule:** The template is the orchestrator - it connects the layers.

### Standard Template Pattern

```php
<?php
// 1. Fetch
$result = npt_fetch_portfolios( $category_slug, NPT_POSTS_PER_PAGE );
$items  = $result['items'];

// 2. Redirect check (for singular views)
// npt_maybe_redirect( $single_item, 'template' );

// 3. Render
get_header();
?>
<main>
    <?php foreach ( $items as $item ) : ?>
        <?php npt_component( 'cards/page-sample', [ 'post' => $item ] ); ?>
    <?php endforeach; ?>
</main>
<?php get_footer(); ?>
```

---

## The Redirect Flow

When a post has `npt_redirect_url` meta set:

```
Template calls npt_fetch_single()
       │
       ▼
npt_fetch_single() returns model array
       │
       ▼
npt_maybe_redirect( $model, 'template' )
       │
       ├─ model['urls']['has_redirect'] === false → continue rendering
       │
       └─ model['urls']['has_redirect'] === true
              │
              ▼
          wp_redirect( $destination, 301 )
          exit;
```

Same flow for REST, but returns a `WP_REST_Response` with `Location` header instead.

---

## Complete Data Flow Diagram

```
WordPress Request
      │
      ▼
pages/page-sample.php   (template)
      │
      │── npt_fetch_portfolios()
      │         │
      │         ▼
      │   data_fetcher/page-sample.php
      │         │── new WP_Query(…)
      │         │── array_map( 'npt_model_portfolio', $posts )
      │         │         │
      │         │         ▼
      │         │   apis/models/page-sample.php
      │         │         │── npt_model_portfolio( WP_Post )
      │         │         │── npt_model_urls( WP_Post )
      │         │         └── returns array
      │         │
      │         └── returns ['items'=>[], 'total'=>N, 'pages'=>N]
      │
      │── npt_maybe_redirect( $model, 'template' )
      │         │── checks $model['urls']['has_redirect']
      │         └── wp_redirect() + exit  OR  continues
      │
      │── foreach $items
      │       └── npt_component( 'cards/page-sample', ['post'=>$item] )
      │                 │
      │                 ▼
      │           components/cards/page-sample.php
      │                 └── outputs HTML card
      │
      └── HTML sent to browser
```
