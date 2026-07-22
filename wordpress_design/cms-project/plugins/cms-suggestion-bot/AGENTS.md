# CMS Suggestion Bot — architecture notes for AI sessions

This file exists so a future session can pick this plugin up cheaply,
without re-deriving the architecture from scratch. Read this before editing.

## What this is

A fully independent WordPress plugin (no dependency on any theme or other
plugin — everything it needs lives inside this folder). It reads WordPress
content (and .txt/.md files dropped in `/resources`), caches it with a
FULLTEXT-searchable schema, and answers visitor questions through a
floating chat widget — without requiring AI. An AI provider can optionally
be plugged in later for unmatched questions.

## Composition root

`app/Core/Plugin.php` is the *only* place `new` gets called on a
Service/Repository/Admin page. Everything else declares its dependencies as
constructor arguments (readonly promoted properties) and gets them resolved
through `Core\Container` (a plain lazy-singleton container — no
autowiring by design, so the object graph in `Plugin::registerServices()` is
the single source of truth for how everything connects).

**Adding a new class that needs dependencies?** Bind it in
`Plugin::registerServices()`, don't `new` it elsewhere.

## Data flow (the "Cache System")

```
WordPress content / /resources files
        │  (Contracts\ReaderInterface — one class per source type)
        ▼
   Readers\PageReader / PostReader / FileReader
        │  (Cache\ChunkHasher — skip unchanged content)
        ▼
   Cache\CacheBuilder  ──►  Contracts\CacheStorageInterface (Repositories\CacheRepository)
        │
        ▼
   cms_sug_bot_cache (one row per source) + cms_sug_bot_chunks (FULLTEXT-indexed slices)
        │
        ▼
   Bot\AnswerResolver  ──►  Bot\BotEngine  ──►  Ajax\BotAjaxController  ──►  Front\ChatWidget
```

Adding a new content type (products, a custom post type, ...) means writing
one new class implementing `Contracts\ReaderInterface` and adding it to the
`ReaderManager` binding in `Plugin.php` — nothing else in the pipeline changes.

## Answer resolution order (`Bot\AnswerResolver::resolve()`)

Checked fastest/cheapest first — most requests never touch a FULLTEXT query:

1. Restricted words (in-memory reject, no DB hit)
2. Greetings — small talk ("hi", "thanks"); in-memory match against
   `config/greetings.php` via `Services\GreetingsService`, only considered for
   short messages (≤5 words) so it can never hijack a real question that
   happens to contain a greeting word
3. Common Questions (object-cache-backed, `Services\CommonQuestionsService`)
4. Knowledge base (FULLTEXT on `cms_sug_bot_knowledge`)
5. Cached content (FULLTEXT on `cms_sug_bot_chunks`) — also returns a
   `suggestion` (title+url) for the front-end to render as a link
6. AI provider (only if `ai_approach.enabled` and a provider is registered
   + configured — see below)
7. Fallback — and the question gets logged to
   `cms_sug_bot_knowledge` with `status = 'unanswered'` for admin review
   (Knowledge Base → Unanswered tab)

Conversation memory: `Bot\BotEngine::ask()` pulls the last few messages
*before* recording the new one and hands them to `AnswerResolver::resolve()`
as `$history`. Steps 4/5 always try the **current message alone first** —
only if that finds nothing does a second pass fold recent history in via
`buildContextualQuery()` (so a follow-up like "where is it" still matches),
meaning older context can never outweigh what's actually being asked right now.

Greeting phrase → response text lives in its own file, `config/greetings.php`
— not inlined in `config/defaults.php` — and is editable per-response from
Configuration → Greetings (the form only edits existing phrases' responses,
it doesn't add/remove trigger phrases).

## Adding an AI provider

1. Write a class implementing `Contracts\AiProviderInterface` (id, isConfigured, answer).
2. Register it: `$c->get(AiProviderRegistry::class)->register(new YourProvider(...))`
   — add this call in `Plugin::registerServices()` or `boot()`.
3. The admin picks it from Configuration → AI Approach → Active Provider
   (per-provider `api_key`/`model`/`endpoint` fields already exist in
   `config/defaults.php` → `ai_approach.providers` — add a new key there for
   a new provider so the Configuration UI shows fields for it).

No other file needs to change — `AnswerResolver` and `AiProviderRegistry`
are already provider-agnostic.

## Reusable admin UI

`Admin\View::cardGrid()` / `::table()` / `::notice()` render
`templates/admin/partials/{card-grid,table,notice}.php`. Every admin page
template uses these instead of hand-rolling markup — do the same for new
admin pages rather than duplicating `<table class="widefat">` etc.

## Database

Every table is prefixed `cms_sug_bot_` (via `Database\DB::table()`), defined
in `Database\Schema.php`, created/upgraded by `Installer\Installer` (runs on
activation, and again on any `CSB_DB_VERSION` bump via `maybeUpgrade()` on
`plugins_loaded`). `cache`, `chunks`, and `knowledge` have `FULLTEXT` indexes
— that's the primary speed mechanism (a single indexed `MATCH AGAINST` query
instead of scanning/string-matching rows in PHP), so keep using them rather
than `LIKE '%...%'` when adding new search paths.

Repositories only do CRUD/queries (never business logic); Services hold the
logic and are what Controllers/Admin pages call. `Repositories\AbstractRepository`
gives every repository `find/all/insert/update/delete/count` for free —
concrete repositories only add query methods beyond that baseline.

## What's fully wired vs. still a stub

**Fully working:** Reader → Cache pipeline (pages/posts/files), Admin Tools
(generate/destroy/rebuild cache, clear knowledge, repair/optimize tables,
export/import cache), Configuration (all groups), Knowledge Base CRUD +
Unanswered review, Logs, API key issuing, Settings (uninstall data-wipe
opt-in), the front-end chat widget end-to-end, cron-driven cache
regeneration (daily/weekly/monthly).

**Scaffolded but not built out:**
- `app/API/` — no public REST endpoint yet consumes the issued API keys;
  `Services\ApiKeyService` and the admin UI for keys exist and are ready for one.
- `app/Models/` — repositories return plain associative arrays
  (`ARRAY_A`), not typed value objects. Intentional simplification; revisit
  only if array-shape bugs start showing up.
- `app/Storage/` — cache storage is handled directly by
  `Repositories\CacheRepository` implementing `Contracts\CacheStorageInterface`;
  this folder is a placeholder if a non-DB storage backend is ever needed.
- `app/CLI/`, `app/Updater/` — empty, per the original spec's folder list;
  nothing currently needs them.
- No concrete `Contracts\AiProviderInterface` implementation ships — see
  "Adding an AI provider" above.

## Conventions

- `declare(strict_types=1)` + PSR-4 namespaces in every class file.
- Every class has one job (SOLID) — if a class starts doing two unrelated
  things, split it rather than growing it.
- Comments explain *why*, not *what* — the code should read clearly enough
  that a "what" comment would be redundant.
- No dependency on `cms-plugin`, `ecommerce-plugin`, or any theme in this
  codebase — this plugin must keep working if copied into an unrelated
  WordPress install on its own.
