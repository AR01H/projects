# 🏠 Advaith Homes — Theme Engine Master Guide
> **The Complete Source of Truth for Developers & AI Agents**

This theme is a high-end, custom WordPress engine built for premium real estate services. It prioritizes **Admin Flexibility** (everything is editable) and **Premium UX** (MoveIQ-style aesthetics).

---

## 🚀 1. The Project "Quick Start"
If you are new to this project, here is the 10-second summary:
- **Branding:** Managed via `Advaith Homes -> Site Settings`.
- **Navigation:** Controlled by a dynamic JSON-style manager in Site Settings.
- **Articles & Podcasts:** Managed via a **2-Way System** (Standard Editor OR the Popup Quick-Manager).
- **Leads:** Captured as `Inquiry` posts and viewed in a custom high-info admin table.

---

## 🛠️ 2. "How Do I...?" (Common Tasks)

### How to add a Podcast to the Homepage?
1. Create a new Post.
2. In the sidebar, set **Display Style** to `Podcast / Tip Card`.
3. Add a **Tag** (e.g., MOVING) and an **Episode ID** (e.g., S9E6).
4. Publish. It will automatically appear in the home page's rich-UI section.

### How to change the Navigation Dropdowns?
1. Go to `Advaith Homes -> Site Settings`.
2. Edit the **Mega Menu Manager** text area.
3. Format: `Icon | Title | Subtitle | URL` (One per line).

### How to track new Leads?
1. Go to `Advaith Homes -> Dashboard` to see the counters.
2. Go to `Advaith Homes -> All Messages` to see the full list with phone numbers and emails.

---

## 🏗️ 3. Core Architecture & Files

| System | Primary File | Purpose |
|---|---|---|
| **Admin Hub** | `/function-helpers/theme-settings.php` | The main dashboard, popup manager, and settings. |
| **Data Sync** | `/function-helpers/meta-boxes.php` | Links the standard editor to the premium card styles. |
| **Database** | `/database/schema.php` | Registers Properties, Services, and Inquiries. |
| **Navigation**| `/pages/components/header/nav-menu.php`| Converts admin text into a premium mega-menu. |
| **Podcasts**  | `/pages/components/home/podcasts.php`| The dynamic homepage section for rich-UI cards. |

---

## 📊 4. Database & Data Flow (The Engine)

### Storage Mapping:
- **Global Data:** Stored in `wp_options` (Keys: `ah_site_logo`, `ah_contact_phone`, `ah_mega_menu_json`).
- **Post Data:** Stored in `wp_postmeta` (Keys: `ah_card_style`, `ah_tag_text`, `ah_mini_info`, `ah_sort_order`).

### The "2-Way" Editing Flow:
Both the **Quick Manager Popup** and the **Full Editor Sidebar** save to the **exact same Meta Keys**. This allows an admin to switch between "Fast Field Editing" and "Deep Content Writing" seamlessly.

---

## 🎨 5. Design & Aesthetic Principles
- **Style:** Clean white cards, soft shadows (`--shadow-lg`), and bold typography.
- **Colors:** Deep slates (`--slate-900`) for text, vibrant accents for status badges.
- **Icons:** Uses standard Emojis in the admin for speed, rendered as premium UI elements on the front-end.

---

## 🛡️ 6. Developer Integrity
- **DO NOT** hardcode branding (Logo/Phone). Always use `get_option()`.
- **DO NOT** hardcode navigation. Always use the Mega Menu loop.
- **DO** keep components modular in the `/pages/components/` directory.

---

## 📬 7. Lead & Inquiry Lifecycle (Operations)
1.  **Submission:** When a user submits a contact form, the theme runs `wp_insert_post()` with type `inquiry`.
2.  **Meta Capture:** Customer email and phone are saved via `update_post_meta()`.
3.  **Dash Alert:** The `ah_theme_dashboard_page()` runs a query on `inquiry` posts, instantly updating the "New Leads" counter.
4.  **Admin View:** The customized `schema.php` logic displays these leads in a high-information table for immediate action.

---

## 🎨 8. Post-Style Logic Breakdown
The theme uses a "Switch" logic in `podcasts.php` and `page-blog.php`:
- If `ah_card_style == 'podcast'`: The theme renders a **Rich Card** (Large icon, Colored Tag, Episode ID, Action Button).
- If `ah_card_style == 'standard'`: The theme renders a **Classic Card** (Featured Image, Title, Snippet).
- If `ah_post_type == 'news'`: The theme adds a **"🚨 BREAKING NEWS"** overlay to any card style.

---

## 🛠️ 9. Maintenance & Operations
- **Bulk Updates:** Use the **Manage Articles** popup for fast changes.
- **Deep Edits:** Use the **Full Editor** sidebar for content writing.
- **Site Relaunch:** If the site moves to a new domain, update the `ah_site_logo` and `ah_consultation_url` in Site Settings.

