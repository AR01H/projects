# 2. Migrate a Static HTML File & Create a Custom URL

## Goal
Take any `.html` file you have and serve it at a clean URL like `/user/login` or `/menu` inside your WordPress site.

---

## Step 1 — Create a Page Template File

Create a new file in your theme folder:
```
canehouse-theme/page-YOURNAME.php
```

Example for a login page → `page-user-login.php`
Example for a menu page  → `page-menu.php`

The filename **must start with `page-`** — WordPress uses this to find templates.

---

## Step 2 — Template Structures

### Option A — Standalone (no header/footer, fully isolated)
Use this for login, register, landing pages.

```php
<?php
/**
 * Template Name: User Login Page
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>

  <!-- PASTE YOUR <head> CSS LINKS HERE -->
  <style>
    /* PASTE YOUR CSS HERE */
  </style>
</head>
<body>

  <!-- PASTE YOUR FULL HTML BODY HERE -->

  <script>
    /* PASTE YOUR JS HERE */

    /* Add redirect after success: */
    function redirectAfterSuccess() {
      setTimeout(function() {
        window.location.href = 'https://app.otherdomain.com/dashboard';
      }, 1500);
    }

    /* In your fetch/AJAX success handler add: */
    /* redirectAfterSuccess();                  */
  </script>
</body>
</html>
```

### Option B — With site header and footer
Use this for a menu page, about page, any page that should look like the rest of the site.

```php
<?php
/**
 * Template Name: Menu Page
 */
get_header(); ?>

<main style="padding-top:100px;">
  <!-- PASTE YOUR HTML CONTENT HERE -->
</main>

<?php get_footer(); ?>
```

---

## Step 3 — Paste Your HTML

Open your `.html` file and copy:

| From your HTML file | Paste into template |
|---|---|
| Everything inside `<head>` | Inside `<head>` of template |
| Everything inside `<body>` | Inside `<body>` of template |
| `<link>` CSS tags | Inside `<head>` |
| `<script>` tags | Before `</body>` |

---

## Step 4 — Create a WordPress Page

- Go to **WP Admin → Pages → Add New**
- Title: `User Login` (or whatever your page is)
- On the right side → **Page Attributes → Template**
- Select your template name (e.g. **User Login Page**)
- Click **Publish** ✅

---

## Step 5 — Set the Clean URL

**Enable clean URLs first:**
- Go to **Settings → Permalinks → Post name → Save**

**Then set your slug:**
- Edit your page
- On the right side find **Permalink**
- Click **Edit**
- Type your slug: `user/login` or `menu` or `join`
- Click **Update** ✅

Your page is now live at:
```
localhost/yoursite/user/login
yourdomain.com/user/login
```

---

## Step 6 — Add Redirect After Success (for login/register)

Find your existing fetch/AJAX success handler and add one line:

```javascript
// Your existing code probably looks like this:
fetch('https://api.otherdomain.com/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email, password })
})
.then(res => res.json())
.then(data => {
  if (data.success || data.token) {
    redirectAfterSuccess(); // ← ADD THIS LINE
  }
});

// Or with XMLHttpRequest:
xhr.onload = function() {
  const data = JSON.parse(xhr.responseText);
  if (data.success) {
    redirectAfterSuccess(); // ← ADD THIS LINE
  }
}
```

---

## Moving Static Assets (images, fonts, extra CSS)

If your HTML file has local images or CSS files:

```
Your HTML folder/
├── index.html          ← becomes the template
├── style.css           ← move to canehouse-theme/assets/css/yourpage.css
├── app.js              ← move to canehouse-theme/assets/js/yourpage.js
└── images/
    └── logo.png        ← move to canehouse-theme/assets/images/logo.png
```

Then in your template update paths:
```php
<!-- Old path -->
<link rel="stylesheet" href="style.css">

<!-- New path -->
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/yourpage.css">
```

---

## Quick Reference

| Goal | What to do |
|---|---|
| Isolated page (own design) | Template with full `<!DOCTYPE html>` |
| Page with site nav/footer | Template with `get_header()` / `get_footer()` |
| Clean URL like `/user/login` | Set slug in page permalink settings |
| Redirect after login/register | Add `redirectAfterSuccess()` in JS |
| Use local images/CSS | Move to assets folder, update paths |