---
name: php
description: Use when adding or changing PHP/OOP code in src/ - a Controller, Service, Repository, Data Provider, or Support class - to place it correctly in the layered architecture.
---

# PHP / OOP Architecture (VintageSoulTheme)

Full architecture: `../../../ARCHITECTURE.md`. Step-by-step recipe for a
new content domain: `../../../docs/data-flow.md`.

## Decide the layer before writing the class

Ask, in order:
1. **Does this prepare data for exactly one template context?** -> `src/Controllers/`.
2. **Is this a business rule, or orchestration across data + terminology/formatting?** -> `src/Services/`.
3. **Does this fetch+map one content domain, where the source could plausibly change later?** -> `src/Repositories/Contracts/*Interface.php` + an implementation.
4. **Is this a raw fetch/cache mechanism with zero domain knowledge?** -> check `src/DataProviders/JsonFileProvider.php` / `WpQueryProvider.php` first - it probably already does what's needed.
5. **Is this a named, reusable, cross-cutting helper** (post data, images, URLs, formatting, view rendering)? -> `src/Support/`, but only add a new class if none of the existing ones (`PostHelper`, `ImageHelper`, `UrlHelper`, `Formatter`, `View`) already own this responsibility.

If none of the above fit, stop and reconsider - the answer is not
`functions.php`.

## Rules

- Namespace `VintageSoul\`, one class per file, filename = class name.
- `final class` by default.
- A Service that depends on a Repository takes the **interface** as its
  constructor type, with a concrete default (`?Interface $x = null` ->
  `$x ?? new ConcreteImpl()`) - see `TestimonialService` for the pattern.
- Don't add an interface for something with no plausible second
  implementation - see `docs/data-flow.md` "When not to do this".
- No Composer/external packages without a genuine need the manual
  autoloader + WordPress core can't meet.

## Verifying a change

`php -l path/to/File.php` after every edit. There's no static analysis
tool configured yet in this base - if one is added later (PHPStan/Psalm),
document its config location here.
