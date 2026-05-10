# 🌿 The Cane House — WordPress Theme

## 📦 What's Inside This Theme

```
canehouse/
├── style.css           ← Theme identity (required by WordPress)
├── functions.php       ← All theme setup + editable meta boxes
├── index.php           ← Homepage with ALL sections
├── front-page.php      ← Tells WordPress to use index.php as homepage
├── page.php            ← Inner pages template
├── header.php          ← Navigation header
├── footer.php          ← Footer + WhatsApp button
├── 404.php             ← 404 error page
├── screenshot.png      ← Theme preview image
└── assets/
    ├── css/main.css    ← All your original CSS (unchanged)
    ├── js/script.js    ← All your original JS (unchanged)
    └── images/
        └── thecanehouselogo.png
```

---

## 🚀 HOW TO INSTALL

### STEP 1 — Upload Theme
1. Zip the entire `canehouse` folder → `canehouse.zip`
2. Go to **WP Admin → Appearance → Themes → Add New → Upload Theme**
3. Upload `canehouse.zip`
4. Click **Activate** ✅

### STEP 2 — Set Homepage
1. Go to **WP Admin → Pages → Add New**
2. Title: `Home`
3. Click **Publish**
4. Go to **Settings → Reading**
5. Select: **A static page → Homepage: Home**
6. Click **Save Changes** ✅

### STEP 3 — Set Up Navigation Menu
1. Go to **Appearance → Menus**
2. Click **Create a new menu** → Name: `Primary Navigation`
3. Add pages/custom links:
   - How to Order → `#how-to-order`
   - Reviews → `#reviews`
   - Our Juices → `#build`
   - FAQ → `#faq`
   - Events → `#hire`
   - Franchise → `#franchise`
   - Contact → `#contact` (this gets the green button style)
4. Under **Menu Settings** → tick **Primary Navigation**
5. Click **Save Menu** ✅

### STEP 4 — Upload Logo
1. Go to **Appearance → Customize → Site Identity**
2. Upload your logo image
3. Click **Publish** ✅

---

## ✏️ HOW TO EDIT CONTENT (No Coding Needed!)

### Edit Hero, Reviews, FAQ, Contact etc.
1. Go to **WP Admin → Pages**
2. Click **Edit** on your **Home** page
3. Scroll down — you will see these edit boxes:
   - 🏠 **Hero Section** — edit title, subtitle, description, buttons
   - 📋 **How To Order** — edit all 5 steps
   - ⭐ **Reviews** — edit names, text, avatar images
   - 🎪 **Events & Hire** — edit cards and descriptions
   - ❓ **FAQ** — edit questions and answers
   - 📞 **Contact Details** — edit phone, website, WhatsApp
   - 🔻 **Footer** — edit copyright, social links
4. Make your changes
5. Click **Update** ✅ — live instantly!

### Edit Marquee & Franchise Locations
1. Go to **WP Admin → 🌿 Cane House** (left menu)
2. Edit the scrolling marquee text
3. Edit franchise locations (one per line)
4. Click **Save Settings** ✅

### Change Logo
- Go to **Appearance → Customize → Site Identity → Logo**

### Change Contact Form API
- Go to **Pages → Home → Edit → Contact Details box**
- Paste your Google Apps Script URL in **Google Form API URL**

---

## 🌐 PUSH TO GODADDY

### Method A — Full Migration (Recommended)
1. Install **All-in-One WP Migration** plugin locally
2. Export → Download `.wpress` file
3. On GoDaddy WP Admin → Install same plugin
4. Import your `.wpress` file ✅

### Method B — Theme Only (if GoDaddy already has WordPress)
1. Zip the `canehouse` folder
2. Go to GoDaddy **WP Admin → Appearance → Themes → Upload**
3. Upload and activate ✅

### Method C — FTP Upload
1. Open FileZilla
2. Connect to GoDaddy FTP
3. Upload `canehouse` folder to: `/public_html/wp-content/themes/`
4. Activate in WP Admin ✅

---

## 🐛 BUG FIX WORKFLOW

```
Bug found on live site
        ↓
Fix locally in XAMPP
        ↓
Test it works locally
        ↓
Export with All-in-One Migration
        ↓
Import to GoDaddy ✅
```

---

## 📋 QUICK REFERENCE

| What to change | Where to go |
|---|---|
| Hero title/text/buttons | Pages → Home → Hero Section box |
| Steps (How to Order) | Pages → Home → How To Order box |
| Reviews | Pages → Home → Reviews box |
| FAQ | Pages → Home → FAQ box |
| Phone / WhatsApp | Pages → Home → Contact Details box |
| Footer copyright | Pages → Home → Footer Settings box |
| Marquee strip text | 🌿 Cane House menu |
| Franchise locations | 🌿 Cane House menu |
| Navigation links | Appearance → Menus |
| Logo | Appearance → Customize → Site Identity |
| New pages | Pages → Add New |
