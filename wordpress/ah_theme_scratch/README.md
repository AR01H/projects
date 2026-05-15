# 🏗️ Advaith Homes — Elite WordPress Theme Engine
> **A High-Performance Content Command Center for Premium Real Estate.**

This theme is a custom-built, enterprise-grade WordPress engine designed for the UK's leading buyer agency. It combines **MoveIQ-style aesthetics** with a unique **2-Way Administration** system.

---

## 🚀 1. Core Mission
To provide a seamless, non-destructive administrative experience where every visual element (Logos, Menus, Cards, Styles) is **100% manageable** from the dashboard without touching code.

---

## 🛠️ 2. The Content Command Center
Located at **Advaith Homes → Manage Articles**. This is the heart of the site.

### Key Features:
- **AJAX-Powered Editing:** Save changes instantly without page reloads.
- **Media Library Integration:** Change featured images directly within the Quick-Edit popup.
- **Style Switcher:** Toggle between **Standard Blog**, **Podcast/Rich Card**, and **Mini Hint** styles.
- **Tag Branding:** Set custom text and hex-colors for badges on the fly.

---

## 📂 3. End-to-End Data Flow

### A. The Journey of a Podcast Card
1. **Input:** Admin uses the Command Center to select "Podcast" style.
2. **Database:** `update_post_meta` saves to `wp_postmeta` (Key: `ah_card_style`).
3. **Logic:** The `podcasts.php` component runs a query for that style.
4. **Render:** The front-end generates a rich card with icons, episode IDs, and custom buttons.

### B. The Mega Menu Flow
1. **Input:** Admin types `🔍 | Buying | Find a Home | /buying` in Site Settings.
2. **Logic:** `nav-menu.php` fetches the `ah_mega_menu_json` option and parses it.
3. **Render:** A premium 3-column mega-menu with icons appears in the header.

---

## 📊 4. Database Schema

| Table | Usage | Primary Keys / Meta Keys |
|---|---|---|
| `wp_options` | Global Branding | `ah_site_logo`, `ah_contact_phone`, `ah_mega_menu_json` |
| `wp_posts` | Core Content | Types: `post`, `property`, `service`, `inquiry` |
| `wp_postmeta` | Card Styling | `ah_card_style`, `ah_tag_text`, `ah_tag_color`, `ah_mini_info` |

---

## 🛡️ 5. Security & Integrity
- **Nonces:** Every AJAX request is protected by a security nonce (`ah_admin_nonce`).
- **Permissions:** Admin pages are restricted via `manage_options` capability checks.
- **Validation:** Data is sanitized (e.g., `sanitize_hex_color`, `sanitize_text_field`) before saving.

---

## 🎨 6. Style & Design Principles
- **Aesthetic:** Clean "MoveIQ" white-space, bold typography, and soft shadows.
- **Variables:** Global CSS variables are defined in `main.css` for easy site-wide changes.
- **Modular:** All front-end sections are stored in `/pages/components/` for easy reuse.

---

## 📋 7. Admin "How-To" Summary
- **Add News:** Create a post, set type to "News". It appears on the `/news` page.
- **Add a Guide:** Add a line to the "Buying Hub Manager" in Site Settings.
- **Track Leads:** Check the "Dashboard" for live counters or "All Messages" for deep details.

---

**Developed for Advaith Homes — Your Dedicated Buyer's Agent.**
