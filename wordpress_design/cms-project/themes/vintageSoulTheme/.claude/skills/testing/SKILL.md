---
name: testing
description: Use when verifying a change is safe before considering it done - this project has no automated test suite, so this skill defines what "verified" actually means here.
---

# Testing (VintageSoulTheme)

**Honest starting point:** there is no PHPUnit/Jest/automated test suite in
this base setup. Verification is structured manual checking. If a real
project adds an automated suite later, document its location and how to
run it here - this file should stop being honest about "no suite" the
moment that changes.

## After any PHP change

1. `php -l path/to/file.php` on every touched file - zero tolerance for
   syntax errors reaching a commit.
2. Confirm the layering rule wasn't crossed (a component didn't start
   calling a Repository directly, a template didn't gain a query) - see
   the `php` skill.
3. If a Repository/Service changed shape, check every caller still passes/
   expects the right array keys - there's no type-checker enforcing this
   automatically yet.

## After any CSS/JS change

1. No raw values snuck in outside `variables.css` - grep for hex codes/
   arbitrary px in the changed file.
2. Load the actual page in a browser; exercise every state a component
   claims to support (hover, focus via Tab, disabled, the state that
   trigged the change).
3. Resize to the documented breakpoints (`docs/design-system.md`) and
   check nothing breaks between them, not just at the extremes.

## Before considering a batch of changes "done"

Run `/theme-check` (php -l sweep + the structural checks below) and
`/test` (the full checklist: this file + `accessibility`/`performance`/
`seo` skills' checklists) rather than eyeballing a single change in
isolation - most real bugs in a layered architecture show up at the
boundary between two changes, not inside either one alone.
