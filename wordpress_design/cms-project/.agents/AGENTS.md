# Generic Naming Convention

The project must **not contain business-specific names anywhere** in the codebase. Treat this as a reusable website framework rather than a specific business theme (e.g., Sugarcane).

## Architecture Rule
If every image, logo, JSON file, text, and configuration were replaced tomorrow, the entire website should become a completely different business (Mango, Restaurant, Coffee Shop, Bakery, Real Estate, Electronics, etc.) without changing any PHP templates, components, CSS architecture, JavaScript modules, or helper classes.

The core codebase should remain completely generic, reusable, and independent of any specific industry.

## Rules

### 1. Avoid Business-Specific File Names
Do not create files or folders with business-specific names (e.g., `sugarcane.php`, `mango.php`, `juice-card.php`, `cane-banner.php`, `home-sugar.json`, `sugar-products.json`).

**Use Generic Names Instead:**
- **Templates**: `page-home.php`, `page-about.php`, `page-contact.php`, `page-listing.php`, `page-details.php`, `page-category.php`, `page-search.php`, `page-faq.php`
- **Components**: `hero.php`, `banner.php`, `card.php`, `grid.php`, `slider.php`, `carousel.php`, `gallery.php`, `listing.php`, `details.php`, `section.php`, `form.php`, `popup.php`, `modal.php`, `accordion.php`, `tabs.php`, `navigation.php`, `footer.php`, `header.php`, `button.php`, `badge.php`, `tag.php`, `breadcrumb.php`
- **JSON**: `home.json`, `about.json`, `listing.json`, `details.json`, `categories.json`, `items.json`, `gallery.json`, `reviews.json`, `faq.json`, `team.json`, `statistics.json`, `settings.json`, `navigation.json`, `footer.json`
- **Images**: `hero-01.webp`, `banner-01.webp`, `item-01.webp`, `category-01.webp`, `gallery-01.webp`

### 2. CSS
Avoid business-specific class names (e.g., `.sugar-product`, `.cane-item`, `.mango-card`, `.orange-banner`).
Use generic class names: `.section`, `.card`, `.item`, `.media`, `.banner`, `.grid`, `.list`, `.content`, `.wrapper`, `.container`, `.feature`, `.collection`.

### 3. JavaScript
Avoid business-specific function names (e.g., `loadSugarProducts()`, `showCanePopup()`, `renderMangoCards()`).
Use generic function names: `loadItems()`, `renderListing()`, `renderCards()`, `openModal()`, `loadContent()`, `renderSection()`, `initializeCarousel()`.

### 4. PHP
Avoid business-specific class names (e.g., `SugarProduct`, `MangoGallery`, `OrangeHelper`).
Use generic class names: `Item`, `Collection`, `Content`, `Gallery`, `Media`, `Section`, `Component`, `Renderer`, `Manager`, `Repository`, `Provider`, `Helper`, `Utility`, `Service`.

### 5. Variables
Avoid business-specific variable names (e.g., `$sugarProducts`, `$mangoItems`, `$caneGallery`, `$juiceBanner`).
Use generic variable names: `$items`, `$listing`, `$content`, `$sections`, `$media`, `$gallery`, `$collection`, `$cards`, `$entries`, `$data`, `$config`.

### 6. Configuration
Business-specific values should exist **only** in configuration or JSON (e.g., Business Name, Business Logo, Brand Colors, Navigation, Products, Categories, Images, Content). The application should never assume its business type.
