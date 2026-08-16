# Architecture

## Goal

Content sources change over a project's life (JSON during early build, the
WordPress database once a client is editing content, a future admin API
after that). The frontend should not have to change when that happens. This
theme is built in layers specifically so that swap is cheap:

```
Presentation (templates, components)
        |
Application (Controllers - one per template context)
        |
Service (business rules, terminology, formatting)
        |
Repository (interface + swappable implementation, one per content domain)
        |
Data Provider (raw fetch + cache, domain-agnostic)
        |
JSON file / WordPress DB / future admin API
```

**The rule that makes this real:** Controllers and Services depend on a
Repository *interface*, never a concrete class. Components render prepared
data and never fetch anything themselves. Swapping a data source means
writing one new Repository class and changing one line that decides which
implementation is active - zero template or component changes.

**Where the layering stops:** not everything needs five layers. A theme
constant, an `add_theme_support()` call, a static settings value - none of
that is "content with a swappable source," so it's a plain `config/*.php` or
`config/*.json` file with no Repository wrapping it. Reach for a Repository
only when you can genuinely imagine the source changing later. See
`docs/development.md` for the concrete "where does this go" decision list.

## Worked example: data flow

```
front-page.php                                (thin - build data, render it)
    |  (new SomeController())->prepare()
    v
SomeController                                (Application)
    |  calls a Service, does nothing else
    v
SomeService::method()                         (Service)
    |  asks the Repository for raw records, applies any business rule
    |  (e.g. "featured = rating >= 4, limit 3"), pulls display labels
    |  from TerminologyService
    v
SomeRepositoryInterface::all()                (Repository - the swap boundary)
    |  today:  JsonSomeRepository        reads data/content/*.json
    |  later:  WpSomeRepository          runs a WP_Query against a CPT
    |  later:  ApiSomeRepository         calls a future admin API
    v
JsonFileProvider / WpQueryProvider            (Data Provider - fetch + cache only)
    v
View::component('some/card', $preparedItem)   (Presentation - renders only)
```

A working reference implementation of this exact chain exists in the
codebase today (`TestimonialRepositoryInterface` -> `JsonTestimonialRepository`
-> `TestimonialService` -> `HomeController`) - **it is an example to copy and
rename for a real feature, not a real requirement of this theme.** Delete it
once you've built your first real feature from the pattern, or keep it as a
living reference; either is fine.

## Folder ownership

| Folder | Owns | Never contains | Evolves by |
|---|---|---|---|
| `config/` | WP registration (`*.php`) + small structural settings (`*.json`: terminology, navigation, SEO) | content records, business logic, queries | adding a key/registration call, never a query |
| `data/` | actual content records (`pages/`, `sections/`, `content/`) | code | swapping which Repository reads it |
| `src/Controllers` | preparing one template's data | markup, direct data-source calls | one class per template context |
| `src/Services` | business rules, terminology, formatting, cross-cutting orchestration (asset enqueue, SEO) | raw fetch/cache, markup | new methods as rules grow |
| `src/Repositories` | mapping raw data -> a domain shape, one interface per domain | markup, leaking the raw source's shape past the interface | new implementation class; zero callers change |
| `src/DataProviders` | raw fetch + cache, domain-agnostic | any knowledge of what a "testimonial" or "page" is | reused as-is across repositories |
| `src/Support` | one named cross-cutting concern per class (post data, images, URLs, formatting, view rendering) | a grab-bag/misc dumping ground | split further only when a class earns a second responsibility |
| `components/` | rendering prepared data | fetching, business rules | created the moment a real, reusable UI piece is needed |
| `template-parts/` | `get_template_part()` partials (loop content, etc.) | full page layout decisions | one partial per reused markup chunk |
| `admin/` | future theme options-page UI code | the content data itself (that's `data/`) | swappable independently of what reads `data/` |
| `apis/` | REST route registrations, AJAX handlers | business logic (delegate to a Service) | one file per route group |

## Terminology strategy

Internal code is named for technical responsibility (`TestimonialRepository`,
`ProfileCard`, `ContentSection`), never for a current business label. Display
text that could plausibly be renamed later lives in `config/terminology.json`
as a stable key -> label map, read through `TerminologyService::label('key')`.
Rename "Reviews" to "Client Stories" tomorrow: edit one JSON file, touch zero
templates.

This is a different axis from WordPress's own translation system
(`__()` / `languages/*.po`): that answers *what language*; terminology.json
answers *what do we call this concept at all*. They compose - a terminology
value can still be run through `__()`.

## OOP conventions

- Namespace root: `VintageSoul\` (rename when copying this base for a new project).
- Autoloading: a small `spl_autoload_register` in `functions.php`, no Composer - avoids an unnecessary dependency for a theme this size. Revisit only if a real external package is ever needed.
- One class per file, filename matches class name (PSR-4-shaped, without requiring Composer).
- `final class` by default; only remove `final` when a class is genuinely designed to be extended.
- Static-only classes (`AssetService`, `TerminologyService`, etc.) for stateless operations; instantiated classes (`TestimonialService`, Repositories) when there's real per-instance state (an injected dependency).

## Routing & pages

WordPress resolves every URL - there is no custom router in this codebase,
and there should never be one (see `docs/wordpress.md`). Two things exist
on top of that, purely for organization and to avoid hard-coded paths:

- **`config/routes.php`** - a stable-key -> path map, read through
  `RouteService`. Exactly the same principle as `terminology.json`, applied
  to URLs: `RouteService::url('game')` instead of hard-coding `/game`
  everywhere a link to it is needed. It does not control what WordPress
  serves at that path - it has to be kept in sync with the real Page's
  slug in wp-admin deliberately. What it buys: renaming a path is a
  one-line config change instead of a grep across every template/component
  that ever linked to it.
- **`pages/<key>/view.php`** - one folder per named page, holding only its
  presentation glue (Controller call -> render), exactly like a root
  template file. `front-page.php` includes `pages/home/view.php` directly
  (it's always "home" by WordPress's own definition). `page.php` is the
  dispatcher for every other named page: it asks `RouteService::key_for_current_page()`
  whether the current Page's slug matches a route, and includes that page's
  `view.php` if so - any ordinary Page that isn't one of the named routes
  falls through to `page.php`'s own generic rendering, unchanged.

Adding a new named page: add a key to `config/routes.php`, create
`pages/<key>/view.php`, create the matching WordPress Page in wp-admin with
a matching slug. No routing code to write.

## Layer names vs. this codebase's folders

The brief that produced this architecture describes layers as
`Routing -> Application -> Services -> Repository -> Data Provider`. Mapped
onto what actually exists here, so the same request isn't implemented
twice under two names:

| Described layer | This codebase | Why no separate folder |
|---|---|---|
| Routing | WordPress + `config/routes.php`/`RouteService` | WordPress already does this; no custom router (see `docs/wordpress.md`) |
| Application | `src/Controllers/` | Per-request orchestration for exactly one template context - this already is the "application layer" |
| Services | `src/Services/` | **Interface-agnostic** - takes plain PHP values, returns plain PHP arrays, never touches `$_GET`/`$_POST`/a template. This is what makes it safe for a future REST controller or MCP tool handler to call the exact same class a web Controller calls today, with zero duplicated business logic |
| Repository / Data | `src/Repositories/` | Already the swap boundary described in "Worked example" above |
| Data Provider | `src/DataProviders/` | Already exists, already domain-agnostic |

`Core`/`Domain`/`Infrastructure` were considered and deliberately not
added as separate folders - each would either duplicate one of the rows
above or hold nothing yet. Add one later only when a real need for that
exact responsibility shows up, not speculatively.

## Future interfaces (API, admin, MCP) - not built, but not blocked

Because `src/Services/` never assumes it's being called from a web
request, a future REST endpoint or MCP tool handler is architecturally
just another caller of the same Service methods a Controller calls today:

```
Web Page   -> src/Controllers/*Controller.php  --\
REST API   -> (future) apis/rest/*.php           --> src/Services/*Service.php -> Repositories -> Data
Admin      -> (future) admin/*.php               --/
MCP        -> (future) an MCP tool handler      --/
```

None of the future rows are implemented in this base setup - only the
Services layer that makes adding them later a matter of writing a thin
adapter, not restructuring business logic.

## Future admin/CMS integration

Because every content-bearing feature is meant to go through a Repository
interface, moving from JSON to a WordPress-managed content type - or to a
future custom admin API - never touches:
- Controllers
- Services (unless a business rule needs to change)
- Components
- Templates

It touches exactly one thing: a new class implementing the same interface,
and the single line that decides which implementation is constructed.
