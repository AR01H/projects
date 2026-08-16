# VintageSoulTheme

A WordPress theme foundation built as a **reusable base architecture**, not a
single-purpose theme. This repository currently holds the base setup only -
folder structure, a layered OOP architecture, a design-token system, and the
Claude Code development configuration (skills/agents/commands). No page
designs, components, or final visual identity have been built yet; those are
project-specific decisions made *from* this base, not part of it.

> **Copying this for a new project?** Rename the `VintageSoul` PHP namespace
> (`src/`, `functions.php`'s autoloader prefix), the `vintagesoul-`
> asset-handle prefix in `config/assets.php`, and the theme header in
> `style.css`. CSS/component classes are unprefixed (`.btn`, `.card`,
> `.dialog`, ...) - see `docs/components.md` on avoiding one-off class
> names instead. Everything else - the layering, the folder roles, the
> token structure - travels as-is.

## What's here

| Path | Purpose |
|---|---|
| `functions.php` | Bootstrap only - constants, autoloader, hands off to `src/Bootstrap/Theme.php` |
| `config/` | Plain data: WordPress registration calls, routes, and small JSON settings (terminology, navigation, SEO) |
| `data/` | Content records (e.g. `data/content/*.json`) - distinct from `config/` |
| `src/` | All OOP code: `Controllers/` -> `Services/` -> `Repositories/` -> `DataProviders/`, plus `Support/` helpers |
| `pages/<key>/` | One folder per named page (`home`, `about`, `game`, `contact` - structural examples), dispatched via `config/routes.php` + `page.php`/`front-page.php` |
| `components/` | Presentation - a working example set already exists (button, card, form, badge, navigation, table, list, alert, dialog, toast, dropdown, tooltip) |
| `template-parts/` | `get_template_part()` partials |
| `assets/` | `css/` (tokens -> base -> layout -> components -> pages -> utilities) and `js/` (`core/` -> `components/` -> `pages/`) |
| `docs/` | Deep-dive conventions - see below |
| `.claude/` | `CLAUDE.md`, plus skills/agents/commands for working on this codebase |

## Start here

1. **`ARCHITECTURE.md`** - the full layered architecture, folder ownership table, and data-flow example.
2. **`docs/development.md`** - how to add a controller/service/repository/component/page.
3. **`docs/design-system.md`** - the token system and CSS/JS file conventions.
4. **`docs/wordpress.md`** - WordPress-specific conventions (template hierarchy, hooks, security).
5. **`docs/components.md`** - component authoring rules.
6. **`docs/data-flow.md`** - the Presentation -> Controller -> Service -> Repository -> Provider pipeline, worked example.
7. **`docs/performance-seo.md`** - performance and SEO conventions.
8. **`.claude/CLAUDE.md`** - the non-negotiable rules Claude Code follows in this repo.

## Status

Base architecture: **in place**. Theme implementation (design system values,
components, pages): **not started** - see `docs/development.md` for how to
begin building the actual theme from this foundation.
