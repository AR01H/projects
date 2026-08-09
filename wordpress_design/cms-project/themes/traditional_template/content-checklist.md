# Complete Site Content Migration Checklist

Since the site is now **100% Database-driven**, use this checklist to track the migration of content from the old JSON structure into the backend Database using your **TT Admin** and **CMS Plugin**. 

For every component, there are two steps to check off:
1. **JSON to DB**: `[ ]` -> `[x]` (You have entered and saved the data in the Admin Panel)
2. **Verified UI**: `[ ]` -> `[x]` (You have checked the live website and the component displays perfectly)

*(To add new features later, simply copy a table row `| [ ] | [ ] | Feature Name | ... |` and paste it under the relevant page group!)*

---

## Global Settings & Site-Wide

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | Main Navigation | `nav.json` | Configure in CMS Plugin |
| `[ ]` | `[ ]` | Footer Settings | `footer.json` | Configure in CMS Plugin |
| `[ ]` | `[ ]` | Site Info | `site.json`, `settings.json` | Phone, Email, Address, Tagline |
| `[ ]` | `[ ]` | Social Links | `site.json` | Facebook, Instagram, Twitter |
| `[ ]` | `[ ]` | Ticker | `ticker.json` | Global announcement bar |
| `[ ]` | `[ ]` | Newsbar | `newsbar.json` | Top-bar alerts (Advanced) |
| `[ ]` | `[ ]` | Newsletter | `newsletter.json` | Newsletter signup config |
| `[ ]` | `[ ]` | Decor | `decor.json` | Site-wide decorative elements |
| `[ ]` | `[ ]` | Dialogs | `dialogs.json` | Global modals and popups |
| `[ ]` | `[ ]` | Forms | `form_*.json` | Global contact/inquiry forms |
| `[ ]` | `[ ]` | UI Settings | `ui.json` | General UI strings and labels |


## Home Page (`/`)

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | Home Hero | `home.json`, `home_banner.json`| Headline, background image |
| `[ ]` | `[ ]` | Hero Checkmarks | `hero_checks.json` | Bullet points in hero section |
| `[ ]` | `[ ]` | Paper Story | `paper_story.json` | Paper-style story blurb |
| `[ ]` | `[ ]` | Signature Drinks | `signature_flavours.json`| Featured drinks |
| `[ ]` | `[ ]` | Home Media | `home_media.json` | Main promotional video |
| `[ ]` | `[ ]` | Featured In | `features_in.json` | "As Seen In" press logos |
| `[ ]` | `[ ]` | Logo Strip | `logo_strip.json` | Client or partner logos |


## Our Story / The Farm (`/about/`)

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | Page Header | `page_headers.json` | About page header |
| `[ ]` | `[ ]` | Main Story | `history.json` | Company background text |
| `[ ]` | `[ ]` | Milestones | `milestones.json`, `stats.json`| Key numerical stats |
| `[ ]` | `[ ]` | Core Values | `values.json` | Brand philosophy / missions |
| `[ ]` | `[ ]` | Team Members | `team.json` | Photos and bios of staff |


## What We Do (`/services/`)

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | Services Tabs | `tabs_services.json` | Categorized services info |
| `[ ]` | `[ ]` | Split Features | `split_feature.json` | Side-by-side text/image |
| `[ ]` | `[ ]` | Certifications | `feature_badges.json` | Hygiene and organic badges |


## Work With Us (`/careers/`)

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | Careers Intro | N/A | Introduction text |
| `[ ]` | `[ ]` | Application Form | `form_apply.json` | Job application form |


## Cane & Gur (`/products/`)

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | All Flavours | `flavours.json` | Detailed list of all drinks |
| `[ ]` | `[ ]` | Drink Experience | `experience_data.json` | "Our Drinks" blurb |
| `[ ]` | `[ ]` | Process Steps | `process_steps.json` | Step-by-step breakdown |
| `[ ]` | `[ ]` | Product FAQs | `faqs.json` | Ingredients and shelf life |
| `[ ]` | `[ ]` | Product Reviews | `reviews.json` | Customer testimonials |


## Order & Rates (`/order/`)

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | Delivery Info | `delivery_products.json` | How ordering works & zones |
| `[ ]` | `[ ]` | Order Form | `form_order.json` | Specific order inquiry form |
| `[ ]` | `[ ]` | Order FAQs | `faqs_order.json` | Order-specific questions |
| `[ ]` | `[ ]` | Order Reviews | `reviews_order.json` | Testimonials from bulk buyers|


## Franchise (`/franchise/`)

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | Franchise Hero | `franchise.json` | Introductory text and image |
| `[ ]` | `[ ]` | Hire Packages | `hire_packages.json` | Stall and cart hire details |
| `[ ]` | `[ ]` | Pricing / Tiers | `pricing_tiers.json` | Investment or hire tiers |
| `[ ]` | `[ ]` | Franchise Form | `form_franchise.json` | Franchise inquiry form |
| `[ ]` | `[ ]` | Franchise Gallery| `gallery_franchise.json`| Images of past setups |
| `[ ]` | `[ ]` | Franchise FAQs | `faqs_franchise.json` | Questions about franchising |
| `[ ]` | `[ ]` | Franchise Reviews| `reviews_franchise.json`| Testimonials from franchisees|


## The Journal (`/blog/`)

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | Blog Posts | `posts.json` | Publish articles via WP Posts |
| `[ ]` | `[ ]` | Filter Cards | `filter_cards.json` | Post filtering configuration |


## Gallery (`/gallery/`)

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | Global Gallery | `gallery.json` | Main event/product photos |
| `[ ]` | `[ ]` | Photo Carousel | `photo_carousel.json` | Slider gallery configuration |


## Events & Catering (`/events/`)

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | Event Info Cards | `info_cards.json` | Types of events catered |
| `[ ]` | `[ ]` | Event Form | `form_events.json` | Booking inquiry form |
| `[ ]` | `[ ]` | Events Gallery | `gallery_events.json` | Images of past events |
| `[ ]` | `[ ]` | Events FAQs | `faqs_events.json` | Booking logistics questions |
| `[ ]` | `[ ]` | Events Reviews | `reviews_events.json` | Testimonials from event hosts|


## Help & FAQs (`/faq/`)

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | Downloads | `downloads.json` | Upload downloadable PDFs |
| `[ ]` | `[ ]` | General FAQs | `faqs.json` | General questions |


## Contact (`/contact/`)

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | Store Locations | `locations.json` | Add physical locations |
| `[ ]` | `[ ]` | Interactive Map | `map.json` | Map configurations and pins |
| `[ ]` | `[ ]` | Opening Hours | `opening_hours.json` | Define hours of operation |
| `[ ]` | `[ ]` | Contact Form | `form_callback.json` | General inquiry form |


## Legal & Advanced

| JSON to DB | Verified UI | Component Name | JSON File(s) | Notes / Admin Location |
| :---: | :---: | :--- | :--- | :--- |
| `[ ]` | `[ ]` | Legal Text | `legal*.json`, `terms.json`| Terms and privacy statements |
| `[ ]` | `[ ]` | Cookie Banner | `legal_cookies.json` | Cookie consent text |
| `[ ]` | `[ ]` | Site Notices | `site_notices.json` | Administrative or temp notices |
| `[ ]` | `[ ]` | Spotlights | `spotlights.json` | Featured popup/modal content |
| `[ ]` | `[ ]` | Page Sections | `page_sections.json` | Structural layouts (Restored)|
| `[ ]` | `[ ]` | Quick Links | `quick_links.json` | Fast navigation items |
