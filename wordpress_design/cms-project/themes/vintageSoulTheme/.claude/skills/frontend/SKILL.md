---
name: frontend
description: Use when writing JavaScript for this theme - deciding whether a component needs a script at all, and where core/ vs components/ vs pages/ code belongs.
---

# Frontend / JavaScript Architecture (VintageSoulTheme)

Full conventions: `../../../docs/design-system.md` ("JavaScript" section).

## First question: does this need JS at all?

Most components are pure CSS/markup (`:hover`, `:focus-visible`,
`<details>`, CSS transitions). Only add a script for real interactivity
(open/close state beyond `<details>`, fetch, dynamic DOM changes,
animation CSS can't express).

## Where the code goes

- `assets/js/core/utils.js` - pure functions, no DOM access.
- `assets/js/core/dom.js` - DOM query/manipulation helpers.
- `assets/js/core/events.js` - cross-component pub/sub.
- `assets/js/core/app.js` - the boot sequence.
- `assets/js/components/<name>.js` - one file per interactive component,
  registered via `VintageSoul.app.register('name', initFn)` - never a
  standalone `DOMContentLoaded` listener.
- `assets/js/pages/<page>.js` - page-specific behavior, enqueued only on
  that page.

## Conventions

- No framework/bundler in this base - plain, dependency-free JS (see
  `docs/performance-seo.md` on avoiding unnecessary dependencies). If a
  real project needs a build step later, that's an explicit, documented
  decision, not a default.
- A component script never reaches into another component's DOM directly -
  use `VintageSoul.events` if cross-component communication is needed.
- Respect `prefers-reduced-motion` for any JS-driven animation, matching
  the CSS convention in `variables.css`.

## Verifying a change

No test runner is configured for JS in this base. Manual verification: load
the page, exercise the interaction, check the console for errors from
`app.js`'s try/catch wrapper around each registered component.
