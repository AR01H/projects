---
description: Run the full verification pass for a change - theme-check + accessibility-check + performance-check + seo-check together.
---

Run the complete verification pass used before considering a change done
(see the `testing` skill - this project has no automated test suite, so
this command IS the test suite):

1. Run everything `/theme-check` does.
2. Run everything `/accessibility-check` does.
3. Run everything `/performance-check` does.
4. Run everything `/seo-check` does.
5. If the user named specific files/a specific feature, scope all four
   passes to that area first, then note anything project-wide worth a
   separate look.

Summarize results as one report: a short pass/fail-style summary per
category, then the specific findings grouped underneath. Do not fix
anything unless asked - this command reports.
