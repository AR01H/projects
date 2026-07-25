# Theme Structure Overview

> Complete folder structure and file organization for the theme.

---

## Folder Structure

```
themes/advaithhomes_new/
├── data/advaith/json/           → JSON data files
│   ├── terms.json               → Site terminology (adn_term() reads from here)
│   ├── home_page.json           → Home page content
│   ├── buying.json              → Category page content
│   ├── buying-guides.json       → Guides listing content
│   ├── calculators.json         → Tools page content
│   ├── contact.json             → Contact page content
│   ├── guidance.json            → Guidance page content
│   ├── news.json                → News page content
│   └── ...                      → Other page content
│
├── src/
│   ├── Feature/                 → Feature modules (one per page type)
│   │   ├── Home/
│   │   │   ├── HomeFeature.php
│   │   │   ├── Controller/HomeController.php
│   │   │   └── Service/HomeContext.php
│   │   ├── CategoryGuide/
│   │   │   ├── CategoryGuideFeature.php
│   │   │   ├── Controller/CategoryGuideController.php
│   │   │   └── Service/CategoryContext.php
│   │   ├── Guides/
│   │   │   └── Service/GuidesContext.php
│   │   ├── News/
│   │   │   ├── NewsFeature.php
│   │   │   └── Service/NewsContext.php
│   │   ├── Tools/
│   │   │   ├── ToolsFeature.php
│   │   │   └── Service/ToolsContext.php
│   │   └── ...                  → Other features
│   │
│   ├── Helper/                  → Utility classes
│   │   ├── ComponentRenderer.php
│   │   ├── IconHelper.php
│   │   ├── MediaHelper.php
│   │   └── ...
│   │
│   └── Bootstrap/               → Theme setup
│       ├── ThemeBootstrap.php
│       └── HookRegistrar.php
│
├── components/
│   ├── sections/                → Section components (51 files)
│   │   ├── hero_home.php
│   │   ├── page_hero.php
│   │   ├── guides_hero.php
│   │   ├── guides_grid.php
│   │   └── ...
│   │
│   ├── parts/                   → Reusable parts (26 files)
│   │   ├── breadcrumb.php
│   │   ├── cta_banner.php
│   │   ├── faq_list.php
│   │   └── ...
│   │
│   └── cards/                   → Card components (15 files)
│       ├── guide_card.php
│       ├── news_card.php
│       └── ...
│
├── pages/                       → Page templates (14 files)
│   ├── PageHome.php
│   ├── PageCategoryGuide.php
│   ├── PageTopicCategoryGuide.php
│   ├── PageGuides.php
│   ├── PageNewsall.php
│   ├── PageTools.php
│   └── ...
│
├── apis/                        → API layer
│   ├── services.php
│   ├── services_cms.php
│   └── ThemeRestRoutes.php
│
├── common/                      → Shared utilities
│   ├── ajax/
│   ├── cache/
│   ├── helpers/
│   └── shortcodes/
│
├── includes/                    → Core includes
│   ├── core_terms.php           → Terminology (adn_term)
│   ├── core_routing.php
│   └── seo.php
│
└── assets/
    ├── css/
    ├── js/
    └── images/
```

---

## Key Concepts

### 1. Terminology System
All site text lives in `data/advaith/json/terms.json`. Use `adn_term()` to access:

```php
echo adn_term( 'brand.name', 'Default Value' );
echo adn_term( 'buttons.explore_all', 'Explore all' );
```

### 2. Context Classes
Each page type has a Context class that:
- Fetches data from JSON/DB
- Shapes it into a standard array structure
- Caches results for performance
- Splits into small `build*` methods

### 3. Component Rendering
Use `adn_component()` to render sections, parts, and cards:

```php
adn_component( 'sections/hero_home', array( 'hero' => $ctx['hero'] ) );
```

### 4. Data Flow
```
JSON → Service → Context → Page → Components
```

---

## Quick Reference

| Task | Where |
|------|-------|
| Change site name | `data/advaith/json/terms.json` → `brand.name` |
| Change URLs | `data/advaith/json/terms.json` → `urls.*` |
| Change button text | `data/advaith/json/terms.json` → `buttons.*` |
| Add new page | Create JSON + Context + Page template |
| Add new component | `components/sections/` or `components/parts/` |
| Modify hero | `components/sections/hero_home.php` |
| Modify navigation | `components/parts/main_header.php` |
