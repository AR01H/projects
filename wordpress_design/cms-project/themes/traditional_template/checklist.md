# Content Population Checklist

Use this checklist to track your progress as you populate the JSON data files. **Note: Do not change the JSON file names, section keys, or element structures, as the theme relies on them.**

---

## 1. Home Page
| Done | JSON File | Component/Section | Elements/Fields to Populate |
| :---: | :--- | :--- | :--- |
| [ ] | `home_banner.json` | Home Hero Banner | `title`, `description`, `btn_text`, `btn_url`, `image` |
| [ ] | `hero_checks.json` | Hero Checkmarks | Array of checkmark strings |
| [ ] | `stats.json` | Stats Bar | Array of stats (`number`, `label`, `icon`) |
| [ ] | `home.json` / `paper_story.json` | Paper Story & Our Story | Section titles, `description`, `image` |
| [ ] | `signature_flavours.json` | Signature Bottles | Flavour cards (`title`, `desc`, `image`) |
| [ ] | `reviews.json` | Reviews / Testimonials | Array of reviews (`name`, `text`, `rating`) |
| [ ] | `logo_strip.json` | Featured Logos | Array of client/partner logo URLs |
| [ ] | `faqs.json` | FAQs | Array of Q&A (`q`, `a`) |

## 2. History of Sugarcane Page (`/about/`)
| Done | JSON File | Component/Section | Elements/Fields to Populate |
| :---: | :--- | :--- | :--- |
| [ ] | `page_headers.json` | About Hero | `title`, `subtitle`, `bg_image` |
| [ ] | `history.json` | Company History | `title`, `paragraphs`, `image` |
| [ ] | `milestones.json` | Milestones | Timeline items (`year`, `title`, `desc`) |
| [ ] | `values.json` | Core Values | Value blocks (`title`, `desc`, `icon`) |
| [ ] | `team.json` | Team Members | Array of profiles (`name`, `role`, `image`) |

## 3. FAQS Page (`/faq/`)
| Done | JSON File | Component/Section | Elements/Fields to Populate |
| :---: | :--- | :--- | :--- |
| [ ] | `page_headers.json` | FAQ Hero | `title`, `subtitle`, `bg_image` |
| [ ] | `quick_links.json` | Quick Links | Support blocks (Help Centre, Call Us, etc.) |
| [ ] | `faqs.json` | General FAQs | Array of Q&A (`q`, `a`) |
| [ ] | `faqs_order.json` | Order FAQs | Array of Q&A (`q`, `a`) |
| [ ] | `faqs_events.json` | Event FAQs | Array of Q&A (`q`, `a`) |
| [ ] | `downloads.json` | Downloads | Brochures/PDF links (`title`, `url`) |

## 4. Contact Page (`/contact/`)
| Done | JSON File | Component/Section | Elements/Fields to Populate |
| :---: | :--- | :--- | :--- |
| [ ] | `page_headers.json` | Contact Hero | `title`, `subtitle`, `bg_image` |
| [ ] | `info_cards.json` | Contact Info Cards | Email, Phone, Address cards |
| [ ] | `opening_hours.json` | Opening Hours | `title`, `days` array |
| [ ] | `map.json` | Map Embed | `iframe_url` or `lat`/`lng` |
| [ ] | `locations.json` | Locations Split | Array of physical store locations |

## 5. Event Page (`/events/`)
| Done | JSON File | Component/Section | Elements/Fields to Populate |
| :---: | :--- | :--- | :--- |
| [ ] | `page_headers.json` | Events Hero | `title`, `subtitle`, `bg_image` |
| [ ] | `cta_events.json` | Events CTA | Call to action block for catering |
| [ ] | `reviews_events.json` | Event Reviews | Testimonials specific to catering |
| [ ] | `gallery_events.json` | Events Gallery | Array of event photos |

## 6. Franchise Page (`/franchise/`)
| Done | JSON File | Component/Section | Elements/Fields to Populate |
| :---: | :--- | :--- | :--- |
| [ ] | `page_headers.json` | Franchise Hero | `title`, `subtitle`, `bg_image` |
| [ ] | `franchise.json` | Franchise Info | Intro text, benefits list |
| [ ] | `pricing_tiers.json` | Pricing Tiers | Investment packages (`title`, `price`, `features`) |
| [ ] | `compare_franchise.json` | Compare Table | Feature comparison matrix |
| [ ] | `reviews_franchise.json` | Franchise Reviews | Testimonials from franchisees |
| [ ] | `gallery_franchise.json` | Franchise Gallery | Store/Kiosk setup photos |
| [ ] | `form_franchise.json` | Franchise Form | Form fields configuration |

## 7. Order Page (`/order/`)
| Done | JSON File | Component/Section | Elements/Fields to Populate |
| :---: | :--- | :--- | :--- |
| [ ] | `page_headers.json` | Order Hero | `title`, `subtitle`, `bg_image` |
| [ ] | `delivery_products.json`| Order/Delivery Info | Delivery zones, minimum order |
| [ ] | `reviews_order.json` | Order Reviews | Testimonials from bulk buyers |
| [ ] | `form_order.json` | Order Form | Custom form fields for orders |
| [ ] | `gallery_order.json` | Order Gallery | Photos of packaged products |

## 8. What We Do (`/services/`)
| Done | JSON File | Component/Section | Elements/Fields to Populate |
| :---: | :--- | :--- | :--- |
| [ ] | `page_headers.json` | Services Hero | `title`, `subtitle`, `bg_image` |
| [ ] | `split_feature.json` | Split Feature | Side-by-side text and image block |
| [ ] | `tabs_services.json` | Services Tabs | Categorized services (`tab_name`, `content`) |
| [ ] | `process_steps.json` | Process Steps | Step-by-step breakdown |

## 9. Work With Us (`/careers/`)
| Done | JSON File | Component/Section | Elements/Fields to Populate |
| :---: | :--- | :--- | :--- |
| [ ] | `page_headers.json` | Careers Hero | `title`, `subtitle`, `bg_image` |
| [ ] | `careers.json` | Job Listings | Array of open roles (`title`, `desc`, `link`) |
| [ ] | `form_apply.json` | Application Form | Form configuration for job applicants |

## 10. Global & Navigation
| Done | JSON File | Component/Section | Elements/Fields to Populate |
| :---: | :--- | :--- | :--- |
| [ ] | `nav.json` | Header Menu | Main navigation links |
| [ ] | `footer.json` | Footer | Footer links, description |
| [ ] | `site.json` / `settings.json`| Site Info | Phone, Email, Tagline, Social Links |
| [ ] | `newsletter.json` | Newsletter | Signup form configuration |
| [ ] | `ticker.json` | Ticker | Global announcement bar |
