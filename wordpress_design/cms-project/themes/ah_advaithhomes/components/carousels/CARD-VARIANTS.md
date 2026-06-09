# Carousel Card Variants

Professional card designs for different content types. Mix and match based on your needs.

## Available Variants

### 1. `image-overlay` - Image with text overlay
Large image background with title and text overlaid. Perfect for hero/showcase content.

**Data Structure:**
```php
[
    'image' => 'https://example.com/image.jpg',
    'tag'   => 'Optional tag',
    'title' => 'Card Title',
    'text'  => 'Description text',
]
```

**Use Cases:** Hero sections, featured products, event showcases

---

### 2. `image-split` - Image left, content right
Side-by-side layout with image on left, content on right. Great for detailed product/service cards.

**Data Structure:**
```php
[
    'image'     => 'https://example.com/image.jpg',
    'title'     => 'Service Title',
    'text'      => 'Description',
    'checklist' => [
        'Feature 1',
        'Feature 2',
        'Feature 3',
    ],
]
```

**Use Cases:** Service cards, product features, detailed offerings

---

### 3. `stat` - Large number + label
Emphasis on statistics or metrics with icon, big number, and description.

**Data Structure:**
```php
[
    'stat'       => '500+',
    'stat_label' => 'Happy Customers',
    'icon'       => '⭐',  // emoji
    'title'      => 'Customer Satisfaction',
    'text'       => 'Trusted by hundreds of clients',
]
```

**Use Cases:** Statistics, achievements, success metrics, experience counts

---

### 4. `testimonial` - Quote + author + rating
Customer review/testimonial card with quote, author, role, and star rating.

**Data Structure:**
```php
[
    'quote'  => '"The best service we\'ve ever experienced!"',
    'author' => 'Jane Doe',
    'role'   => 'Event Planner',
    'rating' => 5,  // 1-5 stars
]
```

**Use Cases:** Customer testimonials, reviews, case studies, success stories

---

### 5. `minimal` - Title + description (clean)
Simplest design - just title and text. Perfect for minimalist layouts.

**Data Structure:**
```php
[
    'title' => 'Card Title',
    'text'  => 'Brief description or body text.',
]
```

**Use Cases:** Blog cards, simple lists, clean minimal design

---

### 6. `feature-detailed` - Icon + title + text + checklist
Icon-based card with title, description, and feature list. Current default.

**Data Structure:**
```php
[
    'icon'              => '🌿',  // emoji or image URL
    'icon_type'         => 'emoji',  // 'emoji' or 'img'
    'title'             => 'Feature Title',
    'text'              => 'Description text',
    'border_top_color'  => '#4a8c2a',  // optional accent
    'checklist'         => [
        'Item 1',
        'Item 2',
        'Item 3',
    ],
]
```

**Use Cases:** Feature lists, service cards, benefit highlights

---

## Usage Examples

### Using in Carousel Dots with variants

Update your carousel to include the card variants file:

```php
<?php
require_once get_template_directory() . '/components/carousels/_card-variants.php';

// In your component, use cc_render_card_variant()
echo cc_render_card_variant( $item, 'image-overlay' );
echo cc_render_card_variant( $item, 'testimonial' );
echo cc_render_card_variant( $item, 'stat' );
?>
```

### Example: Carousel with Mixed Card Types

```php
get_template_part( 'components/carousels/carousel-dots', null, [
    'items' => [
        [
            'variant' => 'image-overlay',
            'image'   => get_template_directory_uri() . '/assets/images/hero.jpg',
            'tag'     => 'Featured',
            'title'   => 'Our Premium Service',
            'text'    => 'Experience excellence...',
        ],
        [
            'variant' => 'testimonial',
            'quote'   => '"Amazing service and great people!"',
            'author'  => 'John Smith',
            'role'    => 'CEO, Tech Company',
            'rating'  => 5,
        ],
        [
            'variant'    => 'stat',
            'icon'       => '🎯',
            'stat'       => '98%',
            'stat_label' => 'Success Rate',
            'title'      => 'Industry Leading',
            'text'       => 'Trusted by top brands',
        ],
    ],
    'type' => 'variant',  // NEW: use 'variant' instead of specific type
] );
```

---

## Color & Spacing

All variants respect theme CSS variables:
- `--client-color-1` - primary text
- `--client-color-4` - borders/secondary
- `--client-color-7` - accent/highlight
- `--client-color-11` - card background
- `--client-color-16` - muted text

Customize via carousel's `css_vars` parameter if needed.

---

## Responsive Behavior

All variants automatically:
- Scale icons on mobile
- Stack split cards vertically on mobile
- Adjust typography sizes
- Maintain readability on all screens

Test on: Desktop (900px+), Tablet (641-900px), Mobile (≤640px)

---

## Mixing Old & New Cards

You can still use the old card renderer (`cc_render_card()`) alongside new variants. Just update your carousel logic to detect the `variant` field and call the appropriate renderer.
