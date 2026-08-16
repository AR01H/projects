---
description: Audit accessibility across templates and components - focus states, semantic HTML, alt text, keyboard operability, reduced motion.
---

Use the `accessibility` skill's checklist. Audit the theme (or the file(s)
the user names, if given) and report findings - do not fix unless asked.

1. **Focus states**: grep for `outline: none`/`outline:none` in CSS not immediately paired with a replacement focus style.
2. **Semantic HTML**: flag `<div>`/`<span>` with a `click` handler or `role="button"` where a real `<button>`/`<a>` would work.
3. **Keyboard operability**: for each interactive JS component (`assets/js/components/*.js`), confirm it responds to keyboard events, not only `click`/mouse events.
4. **Alt text**: same check as `/seo-check` step 4 - both skills care about this for different reasons.
5. **Reduced motion**: confirm any new CSS transition/animation is covered by the global `prefers-reduced-motion` override in `variables.css`, or has its own explicit handling.
6. **Contrast**: once real values replace the `--color-brand-*` placeholders, spot-check text/background combinations against WCAG AA (4.5:1 normal text, 3:1 large text/UI components).

Report findings with file:line references, grouped by the checklist item above.
