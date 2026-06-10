# CMS Project - Complete Architecture Reference

> Theme: `ah_canehouse` | Plugin: `plugin1 (CMS ADMIN)` | Tables: 57 custom

---

## 1. HOW EVERYTHING CONNECTS (Big Picture)

```
ADMIN FORM
   |
   v
admin/pages/*.php  ---- POST ---->  AH_Admin_Bootstrap::handle_*()
                                           |
                                           v
                                    Model (e.g. AH_Reviews_Model)
                                           |
                                           v
                                    AH_DB_Helper -> $wpdb -> DB TABLE
                                           |
                                           v
                             Theme Helper (ch_get_reviews())
                                           |
                                           v
                               Component (components/review-carousel.php)
                                           |
                                           v
                                    HTML -> Browser
```

---

## 2. FOLDER STRUCTURE

### Plugin (`plugins/plugin1/`)

```
ah-cms.php                    <- Main entry point (defines constants, loads autoloader)
inc/
  class-autoloader.php        <- Maps class names to file paths
  class-asset-loader.php      <- Enqueues frontend CSS/JS
  class-form-builder.php      <- Renders [ah_form id="N"] shortcode
  class-rules-engine.php      <- Automation triggers/actions
  class-theme-setup.php       <- Theme init

database/
  class-db-installer.php      <- ALL 57 CREATE TABLE statements + migrations
  class-db-helper.php         <- Generic query helpers (insert, update, get, etc.)

models/                       <- One file per data entity
  class-model-base.php        <- Abstract: find, all, create, update, delete
  class-reviews-model.php
  class-services-model.php
  class-posts-model.php
  class-faqs-model.php
  class-events-model.php
  class-team-model.php
  class-home-model.php
  class-about-model.php
  class-nav-model.php
  class-footer-model.php
  class-newsbar-model.php
  class-media-model.php
  class-settings-model.php
  class-taxonomy-model.php
  class-taxonomy-parent-model.php
  class-content-taxonomy-model.php  <- Universal pivot (review/service/faq -> taxonomy)
  class-pages-model.php
  class-audit-model.php

admin/
  class-admin-bootstrap.php   <- Registers all hooks, handles form POSTs
  menus/
    class-admin-menus.php     <- Registers all menu items (25 submenus)
  pages/                      <- One PHP file per admin page (CRUD UI)
    dashboard.php
    reviews.php
    services.php
    posts.php
    faqs.php
    events.php
    team.php
    about.php
    home-sections.php
    navigation.php
    taxonomy.php
    settings.php
    media.php
    notices.php
    news-bar.php
    pages.php
    static-pages.php
    page-builder.php
    form-builder.php
    rules-engine.php
    client-stories.php
    audit-log.php
    import.php
    admin-actions.php
    help.php
  ajax/
    class-ajax-handlers.php   <- All wp_ajax_* handlers
  import/
    class-csv-importer.php    <- CSV upload -> model create()

helper/
  class-notice-helper.php     <- Site-wide notice banner (DB: wp options)
  class-slug-helper.php       <- Auto-generate slugs
  class-validator.php         <- Input validation
  class-uploader.php          <- File uploads -> media library
  class-pagination-helper.php <- Page numbers HTML
```

### Theme (`themes/ah_canehouse/`)

```
functions.php                 <- Load order: common_terms -> helpers -> schema -> admin
includes/
  common_terms.php            <- Site-wide PHP constants
  helpers.php                 <- ALL ch_get_*() functions (200+)
  mock-data.php               <- Fallback test data
  class-theme-admin.php       <- Theme-level admin settings
schema/
  class-schema.php            <- Schema.org markup
  class-data.php              <- Data aggregation for templates
assets/
  css/
    variables.css             <- CSS custom properties (--ch-lime, etc.)
    animations.css            <- @keyframes
    base.css                  <- Reset, typography
    layout.css                <- Grid/flex
    components.css
    components/
      important-notice.css
  js/
    main.js                   <- Carousel, nav toggle, smooth scroll
    forms.js                  <- Form validation + AJAX submit
    history-info.js
    components/
      important-notice.js

parts/
  header.php
  footer.php

components/                   <- 40+ reusable template parts
  hero.php
  review-carousel.php
  events-packages.php
  faq-section.php
  important-notice.php
  origins-showcase.php
  ... (40+ files)

admin/                        <- Theme-level admin pages
  theme-content-settings.php
  theme-settings.php
  theme-dashboard.php
  ...

page templates:
  front-page.php
  page-about.php
  page-services.php
  page-events.php
  page-faqs.php
  page-blog.php
  ... (13 total)
```

---

## 3. DATABASE TABLES (All 57)

**Table prefix**: `wp_ah_cms_plug_` (set by constant `TABLE_MID_FIX = '_cms_plug_'`)

### Group 1 - Taxonomy System
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_taxonomy_types` | Category types (Review-cat, FAQ-tag, etc.) | id, name, slug |
| `ah_taxonomies` | Individual terms | id, type_id, parent_id, name, slug, status, sort_order |

### Group 2 - Media
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_media` | Custom media library | id, file_url, mime_type, alt_text, width, height |

### Group 3 - Admin/Auth
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_admin_roles` | Role definitions | id, name, permissions (JSON) |
| `ah_admin_users` | CMS users | id, role_id, email, password_hash, status |

### Group 4 - Site Config
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_site_settings` | All site settings | setting_key (unique), setting_val, field_type, group_name |
| `ah_builder_pages` | Page builder saved states | id, title, slug, blocks (LONGTEXT), status |
| `ah_news_bar_items` | Scrolling ticker messages | id, text, link_url, start_date, end_date, sort_order |

### Group 5 - Page Structure
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_pages` | All CMS pages | id, title, slug, page_type, status |
| `ah_page_sections` | Section visibility per page | page_id, section_key, is_visible, sort_order |

### Group 6 - Homepage Sections (parent + child pairs)
| Tables | Purpose |
|--------|---------|
| `ah_section_hero` | Hero/banner |
| `ah_section_highlights` | Icon + text highlights |
| `ah_section_why_us` + `ah_section_why_us_cards` | Why choose us |
| `ah_section_guide_through` + `ah_section_guide_through_points` | Steps/process |
| `ah_section_stack_items` | Vertical stack items |
| `ah_section_difference` + `ah_section_difference_table` | Comparison table |
| `ah_section_featured_properties` + `_items` | Featured grid |
| `ah_section_experience` + `ah_section_experience_cards` | Gallery/experience |
| `ah_section_why_required` + `_cards` | Info cards |
| `ah_section_reviews_header` | Reviews section header |
| `ah_section_faq_header` | FAQs section header |
| `ah_services_page_header` | Services page header |
| `ah_about_page_header` | About page header |

### Group 7 - About Page
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_about_story` | Brand story block | page_id, image_id, heading, subheading |
| `ah_about_story_points` | Story bullet points | story_id, point_text, sort_order |
| `ah_about_values` | Values cards | page_id, image_id, heading, information |

### Group 8 - Content
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_posts` | Blog/news articles | id, post_type, title, slug, content (LONGTEXT), status, is_featured |
| `ah_post_taxonomies` | Post -> taxonomy | post_id, taxonomy_id |
| `ah_post_links` | CTA links in posts | post_id, label, url, link_type |
| `ah_post_stack_items` | Tech stack in posts | post_id, name, icon_id |
| `ah_post_table_blocks` | Tables in posts | post_id, heading, table_data (JSON) |
| `ah_news_detail_big_cards` + `_links` | Large info cards | post_id, heading, information |
| `ah_post_listing_page_header` | Blog listing header | page_id, main_heading |
| `ah_related_posts` | Post-to-post relations | post_id, related_post_id |
| `ah_random_blog_card_configs` | Featured posts block | page_id, heading, post_type_filter |

### Group 9 - Services
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_services` | Service offerings | id, title, slug, short_desc, full_desc, status |
| `ah_service_bullet_points` | Feature list per service | service_id, point_text, sort_order |
| `ah_service_taxonomies` | Service -> taxonomy | service_id, taxonomy_id |

### Group 10 - Reviews
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_reviews` | Customer testimonials | id, reviewer_name, review_text, rating, source, is_featured |
| `ah_review_images` | Occasion/gallery photos per review | review_id, image_id, caption, sort_order |

### Group 11 - FAQs
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_faqs` | Questions & Answers | id, question, answer, link_text, link_url, sort_order |

### Group 12 - Team
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_team_members` | Staff directory | id, name, designation, bio, photo_id, email, linkedin_url, is_featured |

### Group 13 - Events / Hire
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_events` | Event packages | id, icon (emoji), title, description, items (JSON), color, is_featured |

### Group 14 - Client Stories
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_client_stories_header` | Page header | page_id, heading, information |
| `ah_client_story_images` | Gallery images | page_id, image_id, review_text |
| `ah_client_users_journey` | User journey cards | page_id, heading, image_id, user_name |
| `ah_client_gallery` | Gallery grid | page_id, image_id, width_class |
| `ah_client_video_links` | Video embeds | page_id, heading, video_url |

### Group 15 - Contact
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_contact_page_config` | Contact page settings | page_id, email, whatsapp_number, maps_embed_url |
| `ah_contact_form_submissions` | Form submissions | full_name, email, phone, message, status, is_read |

### Group 16 - Footer / Navigation
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_footer_config` | Footer settings | logo_id, site_name, tagline, copyright_text |
| `ah_footer_contact_links` | Contact info in footer | label, value, link_url, icon_class |
| `ah_footer_social_links` | Social media links | platform, url, icon_class |

### Group 17 - Universal Pivot
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_content_taxonomies` | Any content -> any taxonomy | object_type, object_id, taxonomy_id |

### Group 18 - Utilities
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_floating_widgets` | Sticky WhatsApp/chat buttons | widget_type, link_url, position, is_visible |
| `ah_audit_logs` | Change history | action, table_name, record_id, old_values (JSON), new_values (JSON) |

### Group 19 - Automation
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `ah_rules` | Trigger/condition/action rules | name, trigger_name, conditions (JSON), actions (JSON), status |
| `ah_trigger_logs` | Rule execution logs | rule_id, action_type, status, scheduled_at, sent_at |

---

## 4. ADMIN MENUS (All 25)

All registered in `admin/menus/class-admin-menus.php`:

```
CMS ADMIN (top-level)
  +-- Dashboard
  +-- Site Notices          -> admin/pages/notices.php
  +-- Media Library         -> admin/pages/media.php
  +-- File Links
  +-- News Bar              -> admin/pages/news-bar.php
  +-- Navigation Editor     -> admin/pages/navigation.php
  +-- Home Sections         -> admin/pages/home-sections.php
  +-- Services              -> admin/pages/services.php
  +-- About Page            -> admin/pages/about.php
  +-- Reviews               -> admin/pages/reviews.php
  +-- Events / Hire         -> admin/pages/events.php
  +-- Client Stories        -> admin/pages/client-stories.php
  +-- FAQs                  -> admin/pages/faqs.php
  +-- Posts / Blog          -> admin/pages/posts.php
  +-- Page Builder          -> admin/pages/page-builder.php
  +-- Static Pages          -> admin/pages/static-pages.php
  +-- Pages Manager         -> admin/pages/pages.php
  +-- Form Builder          -> admin/pages/form-builder.php
  +-- Triggers Maker        -> admin/pages/rules-engine.php
  +-- Team Members          -> admin/pages/team.php
  +-- Taxonomies            -> admin/pages/taxonomy.php
  +-- Data Import           -> admin/pages/import.php
  +-- Site Settings         -> admin/pages/settings.php
  +-- Audit Log             -> admin/pages/audit-log.php
  +-- Admin Actions         -> admin/pages/admin-actions.php
  +-- Help & Guide          -> admin/pages/help.php
```

---

## 5. FORM SUBMIT FLOW (Step by Step)

### Standard Admin Form (e.g. Reviews)

```
1. User fills form at: /wp-admin/admin.php?page=ah-reviews

2. Form POSTs to: /wp-admin/admin-post.php
   action = "ah_save_review"   <- hidden input

3. WordPress fires: add_action('admin_post_ah_save_review', ...)
   Registered in: admin/class-admin-bootstrap.php

4. Handler runs:
   - wp_verify_nonce()          <- security check
   - current_user_can()         <- permission check
   - sanitize_text_field()      <- clean inputs
   - new AH_Reviews_Model()     <- load model
   - model->create($data)       <- insert to DB
   - model->save_images()       <- insert to ah_review_images
   - ct_model->sync_terms()     <- insert to ah_content_taxonomies

5. Redirect back:
   wp_safe_redirect( add_query_arg('saved', 1, ...) )

6. Page reloads -> shows "Saved!" notice
```

### AJAX Request (e.g. Toggle Status)

```
1. User clicks toggle button in admin list

2. JavaScript sends:
   $.post( ajaxUrl, {
     action: 'ah_toggle_status',
     id: 42,
     table: 'ah_reviews',
     nonce: '...'
   })

3. WordPress fires: wp_ajax_ah_toggle_status
   Registered in: admin/ajax/class-ajax-handlers.php

4. Handler runs:
   - check_ajax_referer()
   - current_user_can()
   - AH_DB_Helper::set_status( $table, $id, $new_status )

5. Returns JSON: { success: true, new_status: 'inactive' }

6. JS updates button state in DOM without page reload
```

### Frontend Form (Contact / Franchise)

```
1. Visitor fills contact form on site

2. forms.js sends AJAX:
   $.post( ahTheme.ajaxUrl, {
     action: 'ah_contact_submit',
     nonce: ahTheme.nonce,
     name: '...', email: '...', message: '...'
   })

3. Handler saves to: ah_contact_form_submissions

4. Rules engine evaluates trigger:
   AH_Rules_Engine::evaluate('sugarcane_contact_form', $context)

5. Matching rules execute actions:
   - send_email -> SMTP / wp_mail()
   - whatsapp -> API call
   - wait -> schedules next action via cron

6. Returns JSON: { success: true, message: 'Thank you!' }
```

---

## 6. ALL AJAX ACTIONS

Registered in `admin/ajax/class-ajax-handlers.php`:

| Action | Who Calls | What It Does |
|--------|-----------|-------------|
| `ah_toggle_status` | Admin list toggle buttons | Flip active/inactive in any table |
| `ah_delete_item` | Admin list delete buttons | Delete row from allowed table |
| `ah_update_sort_order` | Drag-drop reorder | Update sort_order for multiple rows |
| `ah_get_media` | Media picker modal | Paginated list of media items |
| `ah_upload_media` | Media picker upload | Upload file -> insert to ah_media |
| `ah_delete_media` | Media picker delete | Remove from ah_media |
| `ah_mark_submission` | Submissions list | Update status/is_read on form submission |
| `ah_save_nav_item` | Navigation editor | Save nav item (add/edit) |
| `ah_delete_nav_item` | Navigation editor | Remove nav item |
| `ah_save_static_page` | Static pages editor | Save HTML page content |
| `ah_flush_rewrites` | Admin actions page | flush_rewrite_rules() |
| `ah_clear_transients` | Admin actions page | Delete all transients |
| `ah_clear_audit_log` | Admin actions page | Truncate audit_logs table |
| `ah_db_health_check` | Admin actions page | Check all tables exist |
| `ah_clear_form_submissions` | Admin actions page | Truncate submissions table |
| `ah_rebuild_schema` | Admin actions page | Re-run AH_DB_Installer::install() |
| `ah_quick_save_post_meta` | WP post editor metabox | Save CMS meta from post editor sidebar |

---

## 7. COMPLETE DATA FLOW - REVIEWS EXAMPLE

```
+----------------------------------+
|  ADMIN: admin/pages/reviews.php  |
|  Form fields:                    |
|  - reviewer_name                 |
|  - review_text (WP editor)       |
|  - rating (1-5)                  |
|  - source (manual/google)        |
|  - is_featured (checkbox)        |
|  - reviewer_image_id             |
|  - taxonomy_ids[] (tags)         |
|  - review_image_ids[] (gallery)  |
+---------------+------------------+
                | POST admin-post.php
                | action=ah_save_review
                v
+----------------------------------+
|  admin/class-admin-bootstrap.php |
|  handle_review_save()            |
|  1. verify nonce                 |
|  2. check manage_options         |
|  3. sanitize all inputs          |
+------+----------+----------------+
       |          |                |
       v          v                v
AH_Reviews_Model  AH_Content_Tax  AH_Reviews_Model
->create($data)   ->sync_terms(   ->save_images(
                  'review',$id,    $id, $image_ids)
-> ah_reviews     $taxonomy_ids)
                  -> ah_content_taxonomies
                  -> ah_review_images
                |
                v  (redirect with ?saved=1)
+----------------------------------+
|  THEME: includes/helpers.php     |
|  ch_get_reviews($limit)          |
|  -> new AH_Reviews_Model()       |
|  -> model->all([is_featured=1])  |
|  -> returns array of reviews     |
+---------------+------------------+
                |
                v
+----------------------------------+
|  COMPONENT:                      |
|  components/review-carousel.php  |
|  - ch_get_reviews(6)             |
|  - ch_highlight_text($text)      |
|  - ch_get_review_image($r)       |
|  - star ratings output           |
+---------------+------------------+
                |
                v
           HTML -> Browser
      (carousel + JS in main.js)
```

---

## 8. RULES ENGINE

Used for automations (send email, WhatsApp, webhook) when something happens.

```
TRIGGER -> CONDITIONS -> ACTIONS

Example:
  Trigger: form_submit (contact form)
  Condition: email contains "@gmail.com"
  Action 1: send_email to admin
  Action 2: wait 1 hour
  Action 3: send_email to visitor
```

**Tables**: `ah_rules`, `ah_trigger_logs`

**Available Triggers**:
- `form_submit` - any form builder form submitted
- `sugarcane_contact_form` - contact/franchise form
- `custom` - manually called via `AH_Rules_Engine::evaluate()`

**Available Actions**:
- `send_email` - with template variables like `{name}`, `{email}`
- `whatsapp` - send WhatsApp message
- `http_request` - POST webhook to external URL
- `wait` - delay N minutes/hours/days before next action

**Condition Operators**:
`equals`, `not_equals`, `contains`, `starts_with`, `ends_with`, `in`, `gt`, `lt`, `gte`, `lte`

**Deduplication**: Set `dedup_key = "email"` to prevent firing twice for the same email within `dedup_window_hours`.

---

### HOW FORM SUBMIT TRIGGERS THE RULES ENGINE

When a visitor submits any `[ah_form id="N"]` form:

```
1. Frontend JS (forms.js) sends AJAX:
   action = 'ah_form_submit'
   data   = { form_id: 1, name: 'Akhilesh', email: 'a@gmail.com', ... }

2. class-ajax-handlers.php -> handle_form_submit()
   - Validates fields
   - Saves to ah_form_submissions table
   - Calls: AH_Rules_Engine::evaluate('form_submit', [
       'form_id' => 1,
       'name'    => 'Akhilesh',
       'email'   => 'a@gmail.com',
       ...all other field keys...
     ])

3. Rules Engine checks all active rules with trigger = 'form_submit'
   - Matches conditions
   - Executes matching actions in order
```

---

### HOW TO CONFIGURE A RULE FOR A FORM (Step by Step)

Go to: **CMS Admin -> Triggers Maker -> New Rule**

**Step 1 - Set Trigger**
```
Trigger Name: form_submit
```

**Step 2 - Add Condition to target one specific form**
```
Field:    form_id
Operator: equals
Value:    1          <- your form ID (shown in Form Builder as #1)
```

**Step 3 - Add more conditions (optional)**
```
Field:    email
Operator: contains
Value:    @gmail.com
```

**Step 4 - Add Action: Send email to admin**
```
Type:    send_email
To:      admin@yoursite.com
Subject: New submission from {name}
Body:
  Name:  {name}
  Email: {email}
  (add any other field keys from your form)
```

**Step 5 - Add Action: Auto-reply to customer (optional)**
```
Type:    send_email
To:      {email}          <- uses submitted email
Subject: Thank you {name}!
Body:    We received your message and will reply shortly.
```

**Step 6 - Save Rule**

---

### TOKEN REFERENCE (Form Submit)

Use `{field_key}` in action Subject/Body to insert submitted values:

| Token | Example Value | Notes |
|-------|--------------|-------|
| `{form_id}` | 1 | The form that was submitted |
| `{name}` | Akhilesh | Field key from Form Builder |
| `{email}` | a@gmail.com | Field key from Form Builder |
| `{phone}` | 9876543210 | If field exists in form |
| `{message}` | Hello... | If field exists in form |

**Field keys come from whatever you named the fields in Form Builder.**
Check actual keys in: **Form Builder -> Build Form tab** (field key shown below each field label).

---

### FORM FIELD REFERENCE HELPER (in UI)

When editing a rule in Triggers Maker, if trigger = `form_submit`:
- A blue **"Form Field Reference"** panel appears
- Select any form from the dropdown
- All field keys display as clickable chips
- Click a chip -> inserts into last focused condition field
- Or copies to clipboard if no field is focused

---

## 9. CSV IMPORTER

Admin page: `admin/pages/import.php`
Handler: `admin/import/class-csv-importer.php`

| Type | Required Columns | Optional Columns |
|------|-----------------|-----------------|
| `services` | title | slug, short_description, full_description, sort_order, status |
| `reviews` | reviewer_name, review_text, rating | reviewer_title, source, is_featured, categories (semicolon-sep) |
| `faqs` | question, answer | link_text, link_url, sort_order, tags (semicolon-sep) |
| `posts` | title, post_type | slug, excerpt, content, is_featured, status |
| `team` | name, designation | bio, email, linkedin_url, is_featured, sort_order |
| `taxonomies` | name, type_slug | slug, parent_slug, description, sort_order |
| `news_bar` | text | link_url, start_date (YYYY-MM-DD), end_date, sort_order |
| `events` | title | icon (emoji), description, items (pipe-separated), color |

**Flow**: Upload CSV -> `parse_file()` -> validate rows -> `Model->create()` per row -> report errors

---

## 10. THEME HELPER FUNCTIONS (Key Ones)

All in `themes/ah_canehouse/includes/helpers.php`:

```php
// Settings
ch_get_settings(): array          // All site settings from DB
ch_section_visible($key): bool    // Is this page section visible?

// Navigation
ch_get_theme_navigation(): array  // Primary nav (DB first, fallback to mock)
ch_get_nav_cta(): array           // Header CTA button (Get Help / Hire Us)
ch_get_theme_footer(): array      // Footer columns, contact, social

// Content
ch_get_reviews($limit): array     // Featured reviews
ch_get_posts($type, $limit): array // Blog posts
ch_get_faqs($limit): array        // Active FAQs
ch_get_services($limit): array    // Active services
ch_get_events($limit): array      // Active event packages
ch_get_team_members($limit): array // Staff members

// Reviews
ch_highlight_text($text, $names): string  // Bold-mark specific names in review
ch_get_review_image($r, $index): string   // Get reviewer avatar URL

// Notice
ch_get_important_notice(): array  // Active notice from AH_Notice_Helper
ch_has_notice(): bool             // Quick check if notice is enabled

// Menus & Products
ch_get_menu_sizes(): array
ch_get_cane_types(): array
ch_get_flavours(): array
ch_get_order_steps(): array
```

---

## 11. IMPORTANT NOTICE COMPONENT

### Data Flow
```
Admin: /wp-admin/admin.php?page=ah-notices
  -> saves to WordPress options: 'ah_important_notice'

AH_Notice_Helper::get_notice()
  -> reads wp option, decodes JSON

ch_get_important_notice() in helpers.php
  -> calls AH_Notice_Helper::get_notice()

components/important-notice.php
  -> renders HTML <dialog> popup

assets/js/components/important-notice.js
  -> hashes content (title+message+button+image)
  -> checks localStorage: "ch_notice_{HASH}_{DATE}"
  -> if not shown today -> showModal() after 500ms delay
  -> stores key so all tabs share "shown today" state
```

### Session Tracking
- Uses **localStorage** (shared across all browser tabs)
- Key: `ch_notice_{CONTENT_HASH}_{DATE}` (e.g. `ch_notice_abc123_2026-06-03`)
- Auto-expires at midnight (date changes = new key)
- Content change -> hash changes -> shows again even same day

---

## 12. SECURITY PATTERN (Applied Everywhere)

```php
// 1. Nonce check
if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'ah_save_review' ) ) {
    wp_die( 'Security check failed' );
}

// 2. Capability check
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'No permission' );
}

// 3. Sanitize inputs
$name = sanitize_text_field( $_POST['reviewer_name'] ?? '' );
$text = wp_kses_post( $_POST['review_text'] ?? '' );
$url  = esc_url_raw( $_POST['link_url'] ?? '' );
$id   = absint( $_POST['id'] ?? 0 );

// 4. Prepared statements (via AH_DB_Helper)
$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id )

// 5. Escape output
echo esc_html( $name );
echo esc_url( $url );
echo esc_attr( $value );
```

---

## 13. ASSET LOADING

### Admin Assets (only on `ah-*` pages)
```
wp-color-picker          <- color inputs
media-upload + thickbox  <- image picker
jquery-ui-sortable       <- drag-drop reorder
ah-admin-script.js       <- custom admin JS
```

### Frontend Assets (all pages)
```
variables.css     <- loaded first (CSS vars: --ch-lime, --ch-dark, etc.)
animations.css    <- depends on variables.css
base.css
layout.css
components.css
main.js           <- carousel, nav toggle, form submission
```

Localized data passed to JS:
```js
ahTheme = {
  ajaxUrl: '/wp-admin/admin-ajax.php',
  nonce: '...'
}
```

---

## 14. WORDPRESS HOOKS REGISTERED

### Plugin (`ah-cms.php` + `AH_Admin_Bootstrap`)
```php
register_activation_hook  -> AH_DB_Installer::install()
wp_loaded                 -> AH_DB_Installer::maybe_upgrade()
cron_schedules            -> register 'ah_every_minute'
ah_rules_cron_process     -> AH_Rules_Engine::cron_process()

admin_menu                -> AH_Admin_Menus::register()
admin_enqueue_scripts     -> AH_Admin_Bootstrap::enqueue_assets()
admin_bar_menu            -> AH_Admin_Bootstrap::clean_admin_bar()
admin_post_ah_cms_nav     -> AH_Admin_Bootstrap::handle_navigation()
admin_post_ah_save_notice -> AH_Admin_Bootstrap::handle_notice_save()
add_meta_boxes            -> AH_Admin_Bootstrap::register_post_metaboxes()
save_post                 -> AH_Admin_Bootstrap::save_post_metabox()
```

### Theme (`functions.php`)
```php
wp_enqueue_scripts  -> load frontend CSS/JS
wp_head             -> SEO meta tags, schema.org
init                -> register shortcode [ah_form id="N"]
```

---

*Last updated: 2026-06-03 | Plugin version: 1.0.3 | Tables: 57 | Added: Rules Engine form config guide*
