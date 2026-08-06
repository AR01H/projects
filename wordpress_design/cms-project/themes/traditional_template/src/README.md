# `src/` — Feature classes (OOP)

Each **feature** lives in its own folder here as one or more classes. A feature
class is the **intermediate layer**: it holds the logic that decides *what* to
render and *how*, sitting between the page templates (pure UI) and the JSON data
(pure content). Templates stay dumb, content stays in JSON, and the wiring lives
in exactly one place — the class.

```
Page template (UI)  ──calls──▶  src/<Feature>/ class (logic)  ──reads──▶  admin/data/*.json (content)
     page-home.php                 NT_Section_Renderer                      page_sections.json
```

## Conventions

- **One folder per feature**: `src/Sections/`, `src/Ui/`, `src/Dialogs/`, …
- **Class file naming**: `class-<kebab-name>.php` defining `NT_<StudlyName>`
  (matches the theme's existing `class-*.php` style in `includes/`).
- **Prefix**: classes `NT_`, methods lowercase_snake, constants `UPPER_CASE`.
- **Loading**: register the class file in `config/files.php` under `always`,
  *above* anything that uses it. No autoloader needed.
- **Thin wrappers**: expose a short procedural helper (e.g. `nt_render_sections()`,
  `nt_icon()`, `nt_alert()` in `includes/site-helpers.php`) so templates read
  cleanly; the helper just delegates to the class.
- **Keep default/shared helper functions** in `core/helpers.php` (framework) and
  `includes/site-helpers.php` (this site) — not scattered across features.

## Features

| Folder | Class | Does | Data |
|---|---|---|---|
| `Ui/` | `NT_Icons` | The one inline-SVG icon set (90 icons), painted with `currentColor` | *(in the class)* |
| `Ui/` | `NT_Ui` | Tones, dialog sizes, and **every shared label** — plus the `window.ntUi` payload | `ui.json` |
| `Dialogs/` | `NT_Dialog` | The dialog registry. Ships data; the **browser builds the markup** | `dialogs.json` |
| `Dialogs/` | `NT_Alert` | Inline alerts, and which site notices apply to this page today | `site_notices.json` |
| `Content/` | `NT_Blocks` | The shared "say this, link there" message library | `blocks.json` |
| `Consent/` | `NT_Consent` | Cookie consent config: categories, versions, every word | `cookies.json` |
| `Sections/` | `NT_Section_Renderer` | Renders a page's ordered section list | `page_sections.json` |

## The one rule worth knowing: data on the server, rendering in the browser

Dialogs, the notice strip and the cookie banner have **no PHP template**. The
server decides *what exists* — which dialogs this page can open, which notices
apply today, what every button is called — and hands it over as
`window.ntUi` / `window.ntConsent`. `assets/js/ui-kit.js` and
`assets/js/cookie-consent.js` build the DOM when it is actually needed.

Why:

- a dialog nobody opens costs **zero bytes of markup**;
- there is **one renderer** instead of a PHP one and a JS one that have to be
  kept in step by hand;
- a dialog can be created at runtime from an AJAX response using the *exact*
  same object shape as `dialogs.json` — `NT.dialog.show({ … })`;
- a visitor who already answered the cookie banner never sees it flash up and
  disappear, because it was never in the HTML.

Inline content — an alert *inside* an article, a section, a page — is still
rendered by PHP. The line is: **overlay chrome is built in the browser, page
content is rendered on the server.**

`assets/js/ui-kit.js` mirrors this structure: every behaviour is an ES6 class
(`NTDialogView`, `NTDialog`, `NTDialogs`, `NTToaster`, `NTAlerts`, `NTNotices`,
`NTTabs`, `NTPaperStory`, `NTLeafDrift`, `NTSideDock`, `NTShare`, `NTCopy`),
with `NTUiKit` as the façade exported to `window.NT`.

## Add a new feature

1. `src/<Feature>/class-<name>.php` → `class NT_<Name> { public static function ... }`.
2. Register it in `config/files.php` (`always`), below anything it depends on.
3. (optional) Add a thin wrapper in `includes/site-helpers.php`.
4. Put its content in `admin/data/<feature>.json`, read via `nt_data('<feature>')`.
5. Document it in the table above.
