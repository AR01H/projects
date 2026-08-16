# Component Conventions

## What a component is

A `.php` file under `components/<category>/` that renders **prepared data**
into markup. A component:
- never queries WordPress, never calls a Service or Repository directly;
- never receives more than it needs to render (a Controller/Service decides what data to prepare, not the component);
- receives data as **bare variables**, extracted by `VintageSoul\Support\View::component()` from the array passed to it - a component file reads `$title`, `$items`, etc. directly, never a `$data` array.

```php
// Called from a template:
View::component( 'testimonial/card', [ 'name' => $item['name'], 'quote' => $item['quote'] ] );

// components/testimonial/card.php:
<article class="testimonial-card">
    <p><?php echo esc_html( $quote ); ?></p>
    <span><?php echo esc_html( $name ); ?></span>
</article>
```

## Naming

Named for what the component **is**, not what it currently displays:
`testimonial/card.php`, `profile/card.php`, `content/section.php` -
never `reviews.php`, `client-stories.php`. Display text comes from
`config/terminology.json`/content, not from the filename or the markup.

## `components/` vs `template-parts/`

- `components/` - a genuinely reusable UI piece, potentially used on
  several different pages/contexts, with its own visual identity.
- `template-parts/` - a fragment specific to the WordPress loop (e.g. one
  post card inside `home.php`/`archive.php`/`search.php`) that isn't meant
  to be reused as a general-purpose component elsewhere.

If in doubt, start in `template-parts/`; promote to `components/` the
moment a second, unrelated context needs the same piece.

## CSS/JS colocation

- CSS: `assets/css/components/<name>.css`, self-contained (base, variants,
  sizes, every interaction state, responsive behavior together).
- JS: `assets/js/components/<name>.js`, only if the component has real
  interactivity beyond CSS `:hover`/`:focus`. Registered via
  `VintageSoul.app.register()`, not a standalone `DOMContentLoaded` listener.

## Seeing everything at once

`pages/elements/view.php` renders every component this theme ships (all
button/card/form/alert/dialog/dropdown/tooltip/table/list/navigation
states and variants) on one page, for visual + interaction testing - point
a WordPress Page with the slug `elements` at it. Dev-only; not a content
page, safe to delete once a real project doesn't need a live checklist
anymore. Add a new component to that page when you add the component.

## Do not create a component when...

- The markup is used exactly once and unlikely to be reused - it can stay
  inline in its template/template-part until a second use case appears.
- It's really a layout primitive (a grid, a spacing wrapper) - that
  belongs in `assets/css/layout.css`, not `components/`.
