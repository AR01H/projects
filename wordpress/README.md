# WordPress Themes — Complete End-to-End Guide

> **Two fully independent, production-ready WordPress themes:**
> - 🌿 **The Cane House** — Fresh live-pressed sugarcane juice business
> - 🏠 **Advaith Homes** — UK dedicated buyer's agent

This guide takes you from zero (no WordPress, no server) all the way to a live, fully editable website. Read it top to bottom if you are setting up for the first time.

---

## 📋 Table of Contents

1. [What You Need (Prerequisites)](#1-what-you-need)
2. [Setting Up WordPress Locally (Development)](#2-local-development-setup)
3. [Database Setup](#3-database-setup)
4. [Installing WordPress](#4-installing-wordpress)
5. [Installing the Themes](#5-installing-the-themes)
6. [One Theme Active at a Time — How it Works](#6-switching-themes)
7. [Using the Customizer (Drag & Drop Editing)](#7-customizer--live-editing)
8. [How to Change Any Text, Colour, or Image](#8-changing-content)
9. [Creating New Pages](#9-creating-new-pages)
10. [Contact Form & Admin Dashboard](#10-contact-form--admin-dashboard)
11. [SEO — How It Works](#11-seo)
12. [Going Live — Hosting & Domain Setup](#12-going-live)
13. [Deploying to Live Server](#13-deploying-to-live-server)
14. [Project-Specific Guide: The Cane House](#14-the-cane-house-specific-guide)
15. [Project-Specific Guide: Advaith Homes](#15-advaith-homes-specific-guide)
16. [Full File Reference](#16-full-file-reference)
17. [Troubleshooting](#17-troubleshooting)

---

## 1. What You Need

Before anything, make sure you have these:

| Tool | What it is | Download |
|------|-----------|----------|
| **XAMPP** | Runs PHP + MySQL on your computer | [apachefriends.org](https://www.apachefriends.org/) |
| **WordPress** | The website platform | [wordpress.org/download](https://wordpress.org/download/) |
| **A browser** | Chrome or Firefox | Already have it |
| **A text editor** | To edit config files | [VS Code](https://code.visualstudio.com/) (free) |
| **A hosting account** | To put the site online | [Hostinger](https://hostinger.com), [SiteGround](https://siteground.com), or [Namecheap](https://namecheap.com) |
| **A domain name** | e.g. `thecanehouse.co.uk` | From your hosting provider |

> **If you already have WordPress installed on hosting, skip to Step 5.**

---

## 2. Local Development Setup

Running WordPress on your own computer lets you test changes before going live.

### Install XAMPP

1. Download XAMPP for your OS from [apachefriends.org](https://www.apachefriends.org/)
2. Run the installer → accept all defaults
3. Open **XAMPP Control Panel**
4. Click **Start** next to **Apache** and **MySQL**
5. Both should turn green — you now have a local PHP server + database

### Verify It Works

Open your browser and go to: `http://localhost`
You should see the XAMPP welcome page. ✅

---

## 3. Database Setup

WordPress stores all content (pages, settings, contacts) in a MySQL database.

### Create a Database for The Cane House

1. In your browser go to: `http://localhost/phpmyadmin`
2. Click **New** on the left sidebar
3. Enter database name: `thecanehouse_wp`
4. Select collation: `utf8mb4_unicode_ci`
5. Click **Create**

### Create a Database for Advaith Homes

1. Click **New** again
2. Enter database name: `advaithhomes_wp`
3. Select collation: `utf8mb4_unicode_ci`
4. Click **Create**

> **Note down these values — you will need them during WordPress installation:**
> - Database name: `thecanehouse_wp` or `advaithhomes_wp`
> - Username: `root`
> - Password: *(leave blank for XAMPP)*
> - Host: `localhost`

---

## 4. Installing WordPress

You need **two separate WordPress installations** — one per project.

### Install WordPress for The Cane House

1. Download WordPress from [wordpress.org/download](https://wordpress.org/download/)
2. Extract the zip — you get a folder called `wordpress`
3. **Rename** it to `thecanehouse`
4. Move it to: `C:\xampp\htdocs\thecanehouse\`
5. Open browser → go to: `http://localhost/thecanehouse`
6. Click **Let's go!**
7. Fill in:
   - **Database Name:** `thecanehouse_wp`
   - **Username:** `root`
   - **Password:** *(leave blank)*
   - **Database Host:** `localhost`
   - **Table Prefix:** `tch_` *(optional, helps keep it tidy)*
8. Click **Submit → Run the installation**
9. Fill in site details:
   - **Site Title:** `The Cane House`
   - **Username:** `admin` (or your preferred username)
   - **Password:** Choose a strong password — **write it down**
   - **Email:** your admin email
10. Click **Install WordPress**
11. Log in at: `http://localhost/thecanehouse/wp-admin`

### Install WordPress for Advaith Homes

Repeat the same steps above but:
- Folder: `C:\xampp\htdocs\advaithhomes\`
- URL: `http://localhost/advaithhomes`
- Database: `advaithhomes_wp`
- Table Prefix: `ah_`
- Site Title: `Advaith Homes`

---

## 5. Installing the Themes

Each WordPress installation gets **its own theme**.

### Method A — Upload via WordPress Admin (Easiest)

**For The Cane House:**
1. Right-click the folder `wordpress/thecanehouse-theme` → **Send to → Compressed (zip)**
   - Name it `thecanehouse-theme.zip`
2. Open: `http://localhost/thecanehouse/wp-admin`
3. Go to **Appearance → Themes → Add New → Upload Theme**
4. Click **Choose File** → select `thecanehouse-theme.zip`
5. Click **Install Now**
6. Click **Activate** ✅

**For Advaith Homes:**
1. Zip `wordpress/advaithhomes-theme` → `advaithhomes-theme.zip`
2. Open: `http://localhost/advaithhomes/wp-admin`
3. **Appearance → Themes → Add New → Upload Theme**
4. Upload and activate `advaithhomes-theme.zip` ✅

### Method B — Copy via File Manager (Faster for Development)

1. Copy `wordpress/thecanehouse-theme` folder to:
   `C:\xampp\htdocs\thecanehouse\wp-content\themes\`
2. Log in to WP Admin → **Appearance → Themes** → you'll see it listed
3. Click **Activate**

> **The contact form database table is created automatically on first activation.** No manual SQL needed.

---

## 6. Switching Themes

> **WordPress only runs ONE theme at a time per installation.**
> The two projects (The Cane House and Advaith Homes) are completely **separate WordPress installations** in separate folders, each with their own database. They do not interfere with each other.

```
http://localhost/thecanehouse   ← runs thecanehouse-theme
http://localhost/advaithhomes   ← runs advaithhomes-theme
```

**To switch to a different theme:**
1. Go to WP Admin → **Appearance → Themes**
2. Hover over the theme you want → click **Activate**
3. The old theme is deactivated — its settings are preserved but not active

> **Warning:** If you switch away from a theme and switch back, any Customizer settings you saved will still be there. Contact form data in the database is permanent regardless of which theme is active.

---

## 7. Customizer — Live Editing

The Customizer lets the client change text, colours, logo, and more — **without touching any code** — and see the changes live on screen before saving.

### How to Open the Customizer

1. Log in to **WP Admin**
2. Go to **Appearance → Customize**
3. The left panel shows editing options; the right panel shows the live website preview

### What You Can Edit (The Cane House)

| Panel | What you can change |
|-------|-------------------|
| 🌿 **Branding & Logo** | Upload logo image, change site name |
| 🎨 **Brand Colours** | Primary green, lime accent, text colour — live colour picker |
| 🏠 **Hero Section** | Headline line 1 & 2, description text, button labels |
| 📞 **Contact Information** | Phone, WhatsApp number, email, address |
| 🔍 **SEO Settings** | Google page title, meta description |

### What You Can Edit (Advaith Homes)

| Panel | What you can change |
|-------|-------------------|
| 🏠 **Branding & Logo** | Upload logo image, site name |
| 🎨 **Brand Colours** | Purple accent, gold highlight, dark background |
| 🏠 **Hero Section** | Headline, description, CTA button text |
| 📞 **Contact Information** | Phone, WhatsApp, email, office address |
| 🔍 **SEO Settings** | Google page title, meta description |

### How to Change the Logo

1. Open Customizer → **Branding & Logo**
2. Click **Select logo**
3. Upload your logo image (PNG with transparent background recommended, min 200×200px)
4. Crop if prompted → Select
5. Click **Publish** to save

### How to Change Colours (Live Preview)

1. Open Customizer → **Brand Colours**
2. Click any colour swatch — a colour picker opens
3. Drag the picker or type a hex code (e.g. `#2d5a1b`)
4. The preview on the right updates **instantly**
5. Click **Publish** to save permanently

### How to Save Changes

> ⚠️ **Always click the blue "Publish" button** at the top of the Customizer panel.
> Just closing the panel does NOT save your changes.

---

## 8. Changing Content

There are two ways to change content:

### Option 1 — Customizer (for text, colours, logo, contact info)
Use the Customizer as described in Section 7. Best for: headlines, colours, phone numbers, buttons.

### Option 2 — config.php (for developers, master defaults)

Every detail has a single master definition in `config.php`. Edit this file to change defaults that the Customizer falls back to.

**The Cane House** — edit `wordpress/thecanehouse-theme/config.php`:
```php
define('TCH_PHONE',          '+447887699208');      // ← Change phone
define('TCH_EMAIL',          'hello@thecanehouse.co.uk'); // ← Change email
define('TCH_HERO_TITLE_LINE1', 'Fresh Live-Pressed');  // ← Change hero text
define('TCH_COLOR_ACCENT',   '#c8e830');            // ← Change lime colour
define('TCH_SEO_TITLE',      'The Cane House | ...');  // ← Change Google title
```

**Advaith Homes** — edit `wordpress/advaithhomes-theme/config.php`:
```php
define('AH_PHONE',           '+447887699208');
define('AH_EMAIL',           'hello@advaithhomes.co.uk');
define('AH_HERO_TITLE_LINE1','Find Your Dream Home');
define('AH_COLOR_PRIMARY',   '#6d28d9');
define('AH_SEO_TITLE',       'Advaith Homes | ...');
```

> **Rule of thumb:** Customizer overrides `config.php`. So if you set a colour in Customizer, `config.php` becomes the fallback only if Customizer is reset.

### How to Change Images in Sections

Images used inside section content are in `index.php`. To change them:

1. Open `index.php` in VS Code
2. Find the `<img src="...">` tag for the image you want to replace
3. Upload your new image to WP Admin → **Media → Add New**
4. Copy the URL of the uploaded image
5. Paste it in as the `src` attribute

Or, for Unsplash URLs (placeholders), just replace the URL:
```html
<!-- Before -->
<img src="https://images.unsplash.com/photo-1546173..." alt="...">

<!-- After — your own image -->
<img src="https://yourdomain.com/wp-content/uploads/2026/your-image.jpg" alt="...">
```

---

## 9. Creating New Pages

### Create a New Page in WordPress

1. Go to WP Admin → **Pages → Add New**
2. Give it a title (e.g. "About Us", "Menu", "Services")
3. Add content in the block editor
4. Click **Publish**

### Create a Custom Page Template (for styled pages)

If you want a page to use the full theme design, create a template file:

1. Create a new file in the theme folder, e.g. `page-about.php`
2. Add this at the very top:
```php
<?php
/**
 * Template Name: About Page
 */
get_header();
?>

<!-- Your HTML content here -->
<section>
    <h1>About Us</h1>
    <p>Your content...</p>
</section>

<?php get_footer(); ?>
```
3. In WP Admin → Pages → Edit your page → right sidebar → **Page Attributes → Template** → select "About Page"
4. Publish

### Add a Page to the Navigation Menu

1. Go to WP Admin → **Appearance → Menus**
2. Create a menu if you haven't already → name it "Primary"
3. In "Pages", check the page you want to add → **Add to Menu**
4. Drag to reorder
5. Set **Display location** to "Primary Navigation"
6. Click **Save Menu**

---

## 10. Contact Form & Admin Dashboard

### How the Form Works

When a visitor submits the form:
1. The data is **saved to the WordPress database** (table: `wp_theme_contacts`)
2. An **email notification** is sent to the admin email in `config.php`
3. The submission appears in the WP Admin → **📋 Contacts** dashboard

### Viewing Contact Submissions

1. Log in to WP Admin
2. Click **📋 Contacts** in the left sidebar
3. You see a full table of all submissions with:
   - Name, email, phone
   - Enquiry type (Wedding, Buyer, General, etc.)
   - Message preview
   - Date & time submitted

### Updating Contact Status

For each submission, you can:

1. **Change the status** using the dropdown:
   - `New` — fresh submission, not yet actioned
   - `Called` — you've spoken to them by phone
   - `In Progress` — actively working with them
   - `Not Interested` — they're no longer relevant
   - `Converted` — became a paying client ✅

2. **Add private notes** — e.g.:
   - "Called Monday, said they're interested in a May wedding"
   - "Follow up in 2 weeks"
   - "Booked for corporate event July 15"

3. Click **Save** — the record is updated instantly

### Filter by Status

At the top of the Contacts dashboard, click any status badge to filter:
- **All** — shows every submission
- **New** — only new ones you haven't actioned
- **Converted** — your wins 🏆

### Changing the Admin Notification Email

In `config.php`:
```php
// The Cane House:
define('TCH_ADMIN_NOTIFY_EMAIL', 'your-email@example.com');

// Advaith Homes:
define('AH_ADMIN_NOTIFY_EMAIL', 'your-email@example.com');
```

---

## 11. SEO

All SEO output is **fully automated**. You do not need any SEO plugin. The theme outputs:

- `<title>` tag — shown on browser tab and Google results
- `<meta name="description">` — the snippet under your link on Google
- `<meta name="keywords">` — secondary keywords
- `<link rel="canonical">` — prevents duplicate content
- **Open Graph tags** — what Facebook/WhatsApp shows when you share your link
- **Twitter Card tags** — what Twitter shows when you share your link
- **JSON-LD structured data** — machine-readable info that gives you Google Rich Results (star ratings, business info, etc.)

### How to Update SEO

**Quick way** (no code):
- WP Admin → **Appearance → Customize → 🔍 SEO Settings**
- Edit the title and meta description
- Click **Publish**

**Full control** (via `config.php`):
```php
// Google title (keep under 60 characters):
define('TCH_SEO_TITLE', 'The Cane House | Fresh Sugarcane Juice UK');

// Google description (keep under 160 characters):
define('TCH_SEO_DESC', 'Fresh live-pressed sugarcane juice for weddings and events. 100% natural, no added sugar. Book your stall today.');

// Image shown when sharing on social media (1200x630px recommended):
define('TCH_OG_IMAGE', 'https://thecanehouse.co.uk/wp-content/uploads/og-image.jpg');
```

---

## 12. Going Live — Hosting & Domain Setup

### Choose a Hosting Plan

We recommend **Hostinger Business** or **SiteGround GrowBig** — both include:
- PHP 8.x ✅
- MySQL database ✅
- SSL certificate (HTTPS) ✅
- WordPress auto-installer ✅

**Cost:** Approximately £3–8/month.

### Register Your Domain

If you don't have a domain, buy one during hosting signup:
- `thecanehouse.co.uk` — from Hostinger or Namecheap (~£10/year)
- `advaithhomes.co.uk` — same

### Install WordPress on Hosting

Most hosts provide a **1-Click WordPress Installer**:

1. Log in to your hosting control panel (cPanel or hPanel)
2. Find **WordPress** or **Auto Installer**
3. Click **Install**
4. Fill in:
   - **Domain:** your domain name
   - **Site Title:** The Cane House (or Advaith Homes)
   - **Admin Username & Password**
5. Click **Install** — WordPress is set up in ~30 seconds
6. Access your admin at: `https://yourdomain.co.uk/wp-admin`

---

## 13. Deploying to Live Server

### Option A — Upload Theme via WP Admin (Recommended)

1. Zip your theme folder:
   - `thecanehouse-theme.zip` → for The Cane House
   - `advaithhomes-theme.zip` → for Advaith Homes
2. Log in to your live site's WP Admin
3. **Appearance → Themes → Add New → Upload Theme**
4. Upload the zip → **Install → Activate**

### Option B — Upload via FTP

1. Download **FileZilla** (free FTP client) from [filezilla-project.org](https://filezilla-project.org/)
2. Get your FTP credentials from your hosting panel
3. Connect and navigate to: `/public_html/wp-content/themes/`
4. Drag your theme folder from your computer into this location
5. Go to WP Admin → **Appearance → Themes** → Activate

### After Deploying — Update config.php URLs

Change the site URL in `config.php` from localhost to your real domain:

**The Cane House:**
```php
define('TCH_SITE_URL', 'https://thecanehouse.co.uk');
```

**Advaith Homes:**
```php
define('AH_SITE_URL', 'https://advaithhomes.co.uk');
```

Also update in WP Admin → **Settings → General**:
- WordPress Address (URL): `https://thecanehouse.co.uk`
- Site Address (URL): `https://thecanehouse.co.uk`

### Enable HTTPS (SSL)

1. In your hosting panel, find **SSL Certificate** → enable **Let's Encrypt** (free)
2. In WP Admin → **Settings → General**, update URLs to use `https://` (not `http://`)
3. Install the plugin **Really Simple SSL** for automatic redirects

---

## 14. The Cane House — Specific Guide

### Brand Identity
- **Name:** The Cane House
- **Colours:** Deep Green `#2d5a1b`, Lime `#c8e830`, Yellow `#f5e642`
- **Fonts:** Nunito (headings), Poppins (body)
- **Tone:** Natural, fresh, energetic

### Sections in index.php

| Section ID | What it is |
|-----------|-----------|
| `#hero` | Main hero with headline and CTA buttons |
| `#how-to-order` | 5-step ordering process |
| `#build` | Juice menu with prices |
| `#reviews` | Customer reviews carousel |
| `#hire` | Event hire cards (Wedding, Corporate, Private) |
| `#faq` | Frequently asked questions |
| `#franchise` | Franchise opportunity section |
| `#contact` | Contact form |

### Enquiry Types (Contact Form)

The contact form dropdown includes:
- General Enquiry
- Wedding
- Corporate Event
- Private Party
- Festival / Market
- Franchise

To add a new type, in `index.php` add inside the `<select>`:
```html
<option value="Birthday Party">Birthday Party</option>
```

### WhatsApp Connection

```php
// In config.php — digits only, no + sign:
define('TCH_WHATSAPP', '447887699208');
```

This updates ALL WhatsApp buttons on the page automatically.

---

## 15. Advaith Homes — Specific Guide

### Brand Identity
- **Name:** Advaith Homes
- **Colours:** Purple `#6d28d9`, Gold `#facc15`, Dark `#0f172a`
- **Fonts:** Playfair Display (headings), Inter (body)
- **Tone:** Professional, trustworthy, expert

### Sections in index.php

| Section | What it is |
|---------|-----------|
| `#hero` | Hero with badge, headline, CTA |
| `#stats` | Key stats (clients saved, savings, ratings) |
| `#services` | Service cards |
| `#why-us` | Comparison table vs estate agents |
| `#properties` | 3D coverflow property carousel |
| `#testimonials` | Success stories slider |
| `#resources` | Video guides grid |
| `#contact` | Free consultation form |

### Enquiry Types (Contact Form)

- General Enquiry
- First-Time Buyer
- Upsizing / Family Home
- Investment Property
- Relocation
- Off-Market Search

### Multiple Pages

Advaith Homes has additional HTML pages in the original project:
- `about.html` → create `page-about.php` in the theme
- `services.html` → create `page-services.php`
- `contact.html` → create `page-contact.php`
- `previous-clients.html` → create `page-clients.php`

For each page template, follow the pattern in Section 9 above.

---

## 16. Full File Reference

### The Cane House Theme

| File | Purpose | Edit? |
|------|---------|-------|
| `style.css` | WordPress theme identity header | ❌ Never |
| `config.php` | ⭐ ALL site details in one place | ✅ Yes |
| `functions.php` | Loads all modules, registers hooks | ❌ Rarely |
| `header.php` | `<head>`, nav bar | ✅ If adding nav links |
| `footer.php` | Footer, social links | ✅ If adding footer links |
| `index.php` | Main page template | ✅ To add/remove sections |
| `inc/seo.php` | Auto-outputs meta tags | ❌ Auto |
| `inc/contact-form.php` | Handles form + admin UI | ✅ To add form fields |
| `inc/customizer.php` | Customizer panel definitions | ✅ To add new controls |
| `css/theme.css` | All visual styles | ✅ For design changes |
| `js/main.js` | Interactions, animations, carousel | ✅ For behaviour changes |
| `assets/thecanehouselogo.png` | Logo file | ✅ Replace with your logo |

### Advaith Homes Theme

| File | Purpose | Edit? |
|------|---------|-------|
| `style.css` | WordPress theme identity header | ❌ Never |
| `config.php` | ⭐ ALL site details in one place | ✅ Yes |
| `functions.php` | Loads all modules, registers hooks | ❌ Rarely |
| `header.php` | `<head>`, nav bar | ✅ If adding nav links |
| `footer.php` | Footer, social links | ✅ If adding footer links |
| `index.php` | Main page template | ✅ To add/remove sections |
| `inc/seo.php` | Auto-outputs meta tags | ❌ Auto |
| `inc/contact-form.php` | Handles form + admin UI | ✅ To add form fields |
| `inc/customizer.php` | Customizer panel definitions | ✅ To add new controls |
| `css/theme.css` | All visual styles | ✅ For design changes |
| `js/main.js` | Interactions, testimonial slider, carousel | ✅ For behaviour changes |
| `assets/logo.png` | Logo file | ✅ Replace with your logo |

---

## 17. Troubleshooting

| Problem | Cause | Solution |
|---------|-------|----------|
| White screen after activating theme | PHP error in theme files | Check WP Admin → Tools → Site Health or enable WP_DEBUG |
| Theme doesn't appear in Appearance → Themes | Missing `style.css` header or zip was wrong | Re-zip just the theme folder (not a parent folder) |
| Contact form not saving submissions | DB table not created | Deactivate theme → reactivate to re-run table creation |
| Contact form not sending email notifications | Server email config | Install **WP Mail SMTP** plugin and configure with Gmail |
| Customizer changes not visible | Browser cache | Press Ctrl+Shift+R to hard refresh |
| Customizer changes not saving | Forgot to click Publish | Always click the blue Publish button |
| Colours not updating on site | Customizer CSS not outputting | Check `inc/customizer.php` is included in `functions.php` |
| WhatsApp button opens wrong number | Wrong value in config.php | Remove `+` and spaces from `TCH_WHATSAPP` |
| SEO title not updating on Google | Google hasn't re-crawled yet | Submit URL to Google Search Console |
| SEO plugin overriding theme title | Yoast/RankMath installed | Either use the plugin for SEO or the theme, not both |
| Site not working on HTTPS | SSL not enabled | Enable Let's Encrypt SSL in hosting panel |
| Images not loading on live site | Wrong image URLs | Re-upload images to WP Media Library and use the WP URL |
| `http://localhost` shows blank | Apache not started | Open XAMPP Control Panel → Start Apache |
| Can't log into WP Admin | Forgot password | Go to `http://localhost/thecanehouse/wp-login.php` → Lost password |

### Enable Debugging (For Developers)

In `C:\xampp\htdocs\thecanehouse\wp-config.php`, find and change:
```php
define( 'WP_DEBUG', false );
// Change to:
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Error logs will be saved to `wp-content/debug.log`.

---

## 📦 Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | May 2026 | Initial release — both themes complete |

---

## 🙋 Getting Help

If you get stuck:
1. Check the Troubleshooting table above
2. Search [wordpress.org/support](https://wordpress.org/support/) — most issues are already answered
3. Contact your developer with a description of the problem + any error message you see

---

*Built with ❤️ by Antigravity AI · Two premium WordPress themes for real-world clients*
