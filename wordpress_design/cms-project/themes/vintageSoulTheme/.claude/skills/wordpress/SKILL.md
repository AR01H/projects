---
name: wordpress
description: Use when working with WordPress-specific APIs, the template hierarchy, hooks, or deciding how a piece of theme functionality should integrate with WordPress core in this codebase.
---

# WordPress Development (VintageSoulTheme)

Full conventions: `../../../docs/wordpress.md`. This skill is the workflow
for applying them.

## Before writing any WordPress-facing code

1. **Template hierarchy, not custom routing.** Confirm which of
   `front-page.php` / `home.php` / `page.php` / `single.php` / `archive.php`
   / `search.php` / `404.php` / `index.php` actually owns this context.
   Never introduce a virtual-page or router mechanism.
2. **Is this WordPress registration, or content, or a rule?**
   - Registration (`add_theme_support`, menus, image sizes) -> `config/theme-support.php`.
   - Content -> `data/`, read through a Repository.
   - A rule about that content -> a Service.
3. **Is this WP functionality needed in more than one place already, or
   will it be soon?** If yes, it belongs in `src/Support/` (PostHelper,
   ImageHelper, UrlHelper, Formatter) - check those files first before
   writing a new `get_post_meta()` call inline somewhere.

## Hooks

Register from `src/Bootstrap/Theme.php::init()` only, pointing at a
`Service` method (`array( SomeService::class, 'method' )`), never an
inline closure with logic. If `Theme::init()` is getting long, that's a
signal a new Service is needed, not a signal to add complexity to
`Theme::init()` itself.

## Security checklist (every time, no exceptions)

- [ ] Output escaped: `esc_html()`/`esc_attr()`/`esc_url()`/`wp_kses()`
- [ ] Input sanitized before use, never trusted from `$_GET`/`$_POST`
- [ ] Nonce verified on any form/AJAX handler
- [ ] `current_user_can()` checked before any privileged action
- [ ] Any file path built from a variable is realpath-guarded against the
      theme directory (see `JsonFileProvider`/`View` for the pattern)

## Verifying a change

Run `php -l` on every touched file (`/theme-check` command runs this
across the whole theme). There's no PHPUnit/test runner set up yet in this
base - see the `testing` skill for what "verified" means right now.
