# Blog & Post Style Guide

Complete styling and component reference for The Cane House blog posts.

## Post Content Elements

All post content is styled automatically through `.ch-single-content` class. The following elements are professionally styled:

### Headings
```html
<h1>Main Title</h1>
<h2>Section Heading</h2>
<h3>Subsection</h3>
<h4>Minor Heading</h4>
```
- **H1** - 2rem, deep green, 2.5rem top margin
- **H2** - 1.65rem, deep green, 2.2rem top margin  
- **H3** - 1.3rem, mid green, 1.8rem top margin
- **H4-H6** - Smaller variants with reduced margins

### Paragraphs & Text
```html
<p>Regular paragraph text flows naturally with 1.8 line-height for readability.</p>
<strong>Bold text</strong> for emphasis
<em>Italic text</em> for highlights
```

### Lists

#### Unordered Lists
```html
<ul>
  <li>First bullet point</li>
  <li>Second bullet point</li>
  <li>Third bullet point</li>
</ul>
```
- Custom lime-green triangular bullets (▸)
- 0.8rem spacing between items
- 2rem left margin with padding

#### Ordered Lists
```html
<ol>
  <li>First step</li>
  <li>Second step</li>
  <li>Final step</li>
</ol>
```
- Numbered with green markers
- Same spacing as unordered lists

#### Definition Lists
```html
<dl>
  <dt>Term Name</dt>
  <dd>Definition of the term goes here with detailed explanation.</dd>
  <dt>Another Term</dt>
  <dd>Another definition.</dd>
</dl>
```

### Blockquotes
```html
<blockquote>
  <p>This is an inspiring or important quote that stands out from regular text.</p>
</blockquote>
```
- Lime-green left border (5px)
- Light green background
- Italic text with decorative quotation mark
- 1.5rem padding, 2rem vertical margins

### Code Blocks
```html
<pre><code>function makeJuice() {
  return 'Fresh pressed sugarcane!';
}</code></pre>
```
- Green background with subtle border
- Monospace font (Courier New)
- Auto-scrolling for wide code
- Rounded corners

#### Inline Code
```html
Use the <code>ch_get_events()</code> function to retrieve events.
```
- Inline green background
- Rounded padding

### Horizontal Rules
```html
<hr>
```
- Gradient line from transparent through lime to transparent

### Links
```html
<a href="/about">Read more about us</a>
```
- Green color with underline
- Hover effect: darker green + bold
- Works naturally in paragraphs

## Post Gallery Component

The `post-gallery` component creates a responsive image gallery/carousel within post content.

### Basic Usage
```php
<?php
get_template_part( 'components/post-gallery', null, [
    'title'  => 'Production Process',
    'id'     => 'gallery-process-steps',
    'images' => [
        [
            'src'   => 'https://example.com/img1.jpg',
            'label' => 'Sugarcane Selection',
            'desc'  => 'Choosing the finest fresh sugarcane stalks'
        ],
        [
            'src'   => 'https://example.com/img2.jpg',
            'label' => 'Pressing',
            'desc'  => 'Live pressing with traditional equipment'
        ],
        [
            'src'   => 'https://example.com/img3.jpg',
            'label' => 'Serving Fresh',
            'desc'  => 'Served immediately at peak freshness'
        ]
    ]
] );
?>
```

### Gallery Features

#### Desktop View
- All images in a single horizontal row
- Silent overflow with hidden scrollbar
- Hover effect on images (slight zoom)
- Full navigation dots and arrows visible

#### Mobile View (≤767px)
- Single image at a time
- Carousel dots at bottom
- Previous/Next arrows on right
- Full swipe gesture support
- Touch-friendly navigation

### Component Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `title` | string | '' | Optional title shown in green header bar |
| `images` | array | [] | Array of image objects |
| `id` | string | 'post-gal-{random}' | Unique ID for JS carousel control |

### Image Object Structure
```php
[
    'src'   => 'https://example.com/image.jpg',  // Required: image URL
    'label' => 'Image Title',                      // Optional: label shown in caption
    'desc'  => 'Detailed description',             // Optional: description text
]
```

### Example: Food Journey
```php
get_template_part( 'components/post-gallery', null, [
    'title'  => 'From Farm to Glass',
    'images' => [
        [ 'src' => '/uploads/sugarcane-field.jpg', 'label' => 'Growing', 'desc' => 'Lush sugarcane fields' ],
        [ 'src' => '/uploads/harvesting.jpg', 'label' => 'Harvest', 'desc' => 'Fresh hand-picked stalks' ],
        [ 'src' => '/uploads/juicing.jpg', 'label' => 'Juice', 'desc' => 'Fresh pressed daily' ],
        [ 'src' => '/uploads/served.jpg', 'label' => 'Served', 'desc' => 'Cold and refreshing' ],
    ]
] );
?>
```

## Tables

Tables are fully styled with professional formatting:

```html
<table>
  <thead>
    <tr>
      <th>Header 1</th>
      <th>Header 2</th>
      <th>Header 3</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Row 1, Col 1</td>
      <td>Row 1, Col 2</td>
      <td>Row 1, Col 3</td>
    </tr>
    <tr>
      <td>Row 2, Col 1</td>
      <td>Row 2, Col 2</td>
      <td>Row 2, Col 3</td>
    </tr>
  </tbody>
</table>
```

### Table Styling Features
- Green header background with white text
- Zebra striping (alternating row colors)
- Hover effect on rows
- Border radius with box shadow
- Responsive scrolling on mobile
- Rounded corners

### Example: Nutritional Info
```html
<table>
  <thead>
    <tr>
      <th>Nutrient</th>
      <th>Per 250ml Glass</th>
      <th>% Daily Value</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Calories</td>
      <td>180 kcal</td>
      <td>9%</td>
    </tr>
    <tr>
      <td>Carbohydrates</td>
      <td>45g</td>
      <td>15%</td>
    </tr>
    <tr>
      <td>Calcium</td>
      <td>120mg</td>
      <td>12%</td>
    </tr>
  </tbody>
</table>
```

## Images & Figures

### Single Image
```html
<img src="/uploads/sugarcane.jpg" alt="Fresh sugarcane">
```
- Rounded corners (12px)
- Box shadow for depth
- 2rem vertical margins
- Hover zoom effect (1.02x)
- Responsive sizing

### Image with Caption
```html
<figure>
  <img src="/uploads/event.jpg" alt="Event scene">
  <figcaption>Our team serving at the Summer Festival 2024</figcaption>
</figure>
```

## Complete Post Example

```html
<h1>The Art of Fresh Sugarcane Juice</h1>

<p>Sugarcane juice is one of the world's most refreshing beverages, enjoyed across continents and cultures.</p>

<h2>History & Origins</h2>

<p>The practice of juicing sugarcane dates back thousands of years in South Asia. Traditional methods have been refined but never abandoned.</p>

<blockquote>
  <p>Fresh pressed sugarcane is not just a drink—it's a connection to centuries of tradition and natural wellness.</p>
</blockquote>

<h3>Why Fresh Matters</h3>

<ul>
  <li>Maximum nutrient retention</li>
  <li>Superior taste and texture</li>
  <li>No additives or preservatives</li>
  <li>Immediate consumption ensures peak freshness</li>
</ul>

<figure>
  <img src="/uploads/sugarcane-farm.jpg" alt="Sugarcane field">
  <figcaption>Our partner farms in South Asia</figcaption>
</figure>

<h2>The Process</h2>

<!-- Image Gallery Component -->

<h3>Step 1: Selection</h3>
<p>We choose only the finest, most mature stalks...</p>

<h3>Step 2: Washing</h3>
<p>Fresh stalks are thoroughly cleaned...</p>

<h2>Nutritional Benefits</h2>

<table>
  <thead>
    <tr>
      <th>Benefit</th>
      <th>Details</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Instant Energy</td>
      <td>180 kcal of natural carbohydrates per glass</td>
    </tr>
    <tr>
      <td>Antioxidants</td>
      <td>Rich in phenolic compounds</td>
    </tr>
  </tbody>
</table>

<hr>

<p>Experience the difference fresh pressed makes. Book a tasting at your next event!</p>
```

## Best Practices

1. **Keep paragraphs readable** - Aim for 2-4 sentences per paragraph
2. **Use headings hierarchically** - One H1, multiple H2s/H3s as needed
3. **Break up long content** - Use images, quotes, or lists every few paragraphs
4. **Make lists meaningful** - 3-6 items per list is optimal
5. **Caption your images** - Helps accessibility and understanding
6. **Use tables for data** - Not for layout; tables are for actual tabular data
7. **Bold key terms** - Helps scannability
8. **Test on mobile** - The responsive styles work great on small screens

## Mobile Responsiveness

All elements automatically adapt to smaller screens:
- Reduced font sizes while maintaining readability
- Narrower content width (1rem padding on each side)
- Stacked table layouts on very small screens
- Touch-friendly carousel navigation for galleries
- Larger tap targets for links and buttons

## Accessibility Features

- Semantic HTML structure (headings, lists, tables)
- Image alt text support
- High color contrast
- Keyboard navigable carousels
- ARIA labels on interactive elements
- Proper heading hierarchy for screen readers

---

**Last Updated:** 2026-05-31  
**Theme:** The Cane House  
**Version:** 1.0
