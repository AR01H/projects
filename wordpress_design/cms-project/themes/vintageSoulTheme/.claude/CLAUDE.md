# VintageSoulTheme — Project Rules

This is a **reusable WordPress theme base**, not a finished theme. Full
architecture: `../ARCHITECTURE.md`. Day-to-day conventions: `../docs/`.
This file holds only the rules that must never be silently broken - it
does not repeat what the docs already explain in depth.

## Non-negotiable

1. **Layering is one-directional and interface-bound.** Templates/components
   never call a Repository or Data Provider directly - only a Service, only
   through a Controller. Services depend on Repository *interfaces*, never
   a concrete Repository class. See `ARCHITECTURE.md`.
2. **No custom routing.** Use WordPress's own template hierarchy
   (`front-page.php`, `page.php`, `single.php`, `archive.php`, `search.php`,
   `404.php`, `index.php`). Do not build a virtual-page/router system.
3. **Page templates stay thin.** A root template file's job: build one data
   array via a Controller, render it. No queries, no business rules, no
   data transformation inline in a template.
4. **`functions.php` never grows.** It bootstraps and nothing else. New
   code goes in `src/` or `config/` per `docs/development.md`'s table.
5. **Generic internal naming, configurable display text.** Never name a
   file/class after today's business term. Display labels come from
   `config/terminology.json` via `TerminologyService`, not hard-coded
   strings in templates/components.
5b. **URLs are centralized too.** Never hard-code a path like `/contact/`
   in a template/component - add it to `config/routes.php` and use
   `RouteService::url('key')`. `Services/` stay interface-agnostic (plain
   PHP in, plain PHP out) specifically so a future REST/MCP/admin
   interface can call the same ones a web page Controller calls today -
   see `ARCHITECTURE.md` "Future interfaces".
6. **No raw values in CSS.** Every color/spacing/radius/shadow value comes
   from a token in `assets/css/variables.css`. If the token doesn't exist
   yet, add it there first.
7. **One component, one file, all states.** A component's CSS includes its
   own hover/focus/active/disabled/responsive behavior in the same file -
   never a separate "hover" or "mobile" stylesheet per component.
8. **Escape on output, sanitize on input, always.** No exceptions, no
   "I'll add it later." Nonces + capability checks on every privileged
   action. See `docs/wordpress.md`.
9. **Do not create a folder without a stated purpose.** Before adding one,
   answer: why does it exist, what belongs in it, what doesn't, which
   layer owns it. `.gitkeep` only where a directory is genuinely empty and
   structurally required right now - never added automatically.
10. **Do not over-engineer.** A Repository/Service pair is for content
    whose source could plausibly change. Something used once, with no
    swap scenario, does not need the full layering - see
    `docs/data-flow.md` "When not to do this".

## Current phase

**Base architecture only.** No real components, pages, business logic, or
final visual design exist yet - see `README.md` "Status" before assuming
any theme-specific behavior is intended to stay as-is. The one worked
example (`Testimonial*` classes) is a reference pattern, explicitly safe
to delete once its purpose (demonstrating the data-flow layers) has been
understood.

## Where things live (quick reference - full table in `docs/development.md`)

`config/` data & WP registration · `data/` content · `src/Controllers`
page data prep · `src/Services` business rules · `src/Repositories`
swappable data access · `src/Support` named cross-cutting helpers ·
`components/` presentation · `template-parts/` loop fragments ·
`assets/css|js` per `docs/design-system.md`.
