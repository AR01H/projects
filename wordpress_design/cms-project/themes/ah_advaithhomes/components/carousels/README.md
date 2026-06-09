# Carousel Components

A set of focused, responsive carousel variants for different use cases.

## Available Carousels

### 1. Carousel Dots (`carousel-dots.php`)
Clean horizontal scroll with dot pagination indicators.

**Best for:** Product cards, features, testimonials, gallery

```php
get_template_part( 'components/carousels/carousel-dots', null, [
    'items' => [
        [ 'image' => 'url', 'title' => 'Card 1', 'subtitle' => 'Subtitle' ],
        [ 'image' => 'url', 'title' => 'Card 2', 'subtitle' => 'Subtitle' ],
    ],
    'type' => 'image',  // 'image' | 'feature' | 'step'
    'class' => 'optional-css-class',
] );
```

**Responsive:** 3 items (desktop) → 2 items (tablet) → 1 item (mobile)

---

### 2. Carousel Arrows (`carousel-arrows.php`)
Left/right arrow buttons for navigation, minimal and clean.

**Best for:** Showcase sections, featured items, hero galleries

```php
get_template_part( 'components/carousels/carousel-arrows', null, [
    'items' => [
        [ 'icon' => '🌿', 'title' => 'Feature 1', 'text' => 'Description' ],
        [ 'icon' => '🌿', 'title' => 'Feature 2', 'text' => 'Description' ],
    ],
    'type' => 'feature',
    'visible' => 3,  // cards visible per page (default: 3)
] );
```

**Responsive:** Arrows scale down on smaller screens

---

### 3. Carousel Scroll (`carousel-scroll.php`)
Continuous auto-scrolling with play/pause toggle control.

**Best for:** News feeds, testimonials, featured content with auto-rotation

```php
get_template_part( 'components/carousels/carousel-scroll', null, [
    'items' => [
        [ 'icon' => '⭐', 'title' => 'Review 1', 'text' => 'Customer feedback' ],
        [ 'icon' => '⭐', 'title' => 'Review 2', 'text' => 'Customer feedback' ],
    ],
    'type' => 'feature',
    'autoplay' => true,  // auto-rotate on load (default: true)
    'speed' => 4500,     // ms between slides (default: 4500)
] );
```

**Controls:** Play/pause buttons, auto-pause on hover

---

## Card Types

All carousels support these card types:

### `image`
```php
[
    'image' => 'https://example.com/image.jpg',
    'title' => 'Card Title',
    'subtitle' => 'Optional subtitle',
]
```

### `feature`
```php
[
    'icon' => '🌿',              // emoji or image URL
    'icon_type' => 'emoji',      // 'emoji' (default) or 'img'
    'title' => 'Feature Title',
    'text' => 'Description text',
    'border_top_color' => '#4a8c2a',  // optional accent color
]
```

### `step`
```php
[
    'step' => '1',           // step number
    'icon' => '📞',          // emoji
    'title' => 'Step Title',
    'text' => 'Description',
]
```

### `selector`
```php
[
    'icon' => '🍋',
    'label' => 'Lemon',
    'value' => 'lemon',      // for form submission
]
```

---

## Theme Customization

All carousels use CSS custom properties for theming. Edit the style sections or override with:

```css
#carousel-id {
    --cc-gap: 16px;                    /* gap between items */
    --cc-visible: 3;                   /* items visible (dots carousel) */
}
```

**Color variables:**
- `--client-color-1` - text
- `--client-color-4` - borders
- `--client-color-7` - accent (active states)
- `--client-color-11` - card background
- `--ch-radius` - border radius

---

## Responsive Behavior

| Breakpoint | Dots | Arrows | Scroll |
|-----------|------|--------|--------|
| Desktop (900px+) | 3 visible | 3 visible | Full width |
| Tablet (641-900px) | 2 visible | 2 visible | Full width |
| Mobile (≤640px) | 1 visible | 1 visible | Full width |

---

## Deprecation Notice

The old `generic-carousel.php` is deprecated. Use the specific carousel variants instead for cleaner, more maintainable code.

**Old:** `components/generic-carousel.php`  
**New:** `components/carousels/carousel-[dots|arrows|scroll].php`
