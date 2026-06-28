You are an expert front-end developer.

Build a **complete HTML calculator** for:

**[TOPIC]** *(e.g. UK First-Time Buyer Mortgage Calculator)*

---

## Design System

Use these CSS variables exactly as provided. **Do not hardcode any colours.**

```css
:root {
    --client-color: #203c3e;
    --secondary-color: #B08D57;
    --bg-color-1: #ded5c1;
    --section-bg-color: #3c4748;
    --gold-700: #b45309;
    --slate-500: #64748b;
    --slate-600: #475569;
    --slate-900: #0f172a;
    --text-primary: var(--slate-900);
    --text-secondary: var(--slate-600);
    --text-muted: var(--slate-500);
    --important: rgb(168, 3, 3);
    --font-display: 'Cormorant Garamond', Georgia, serif;
    --font-body: 'DM Sans', system-ui, sans-serif;
    --font-accent: 'Instrument Serif', Georgia, serif;
    --card-bg: white;
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --space-2: 8px;
    --space-4: 16px;
    --space-6: 24px;
    --space-8: 32px;
}
```

---

# Output Requirements

* Output **`<body>` content only**.
* Do **not** include:

  * `<!DOCTYPE>`
  * `<html>`
  * `<head>`
* Include all CSS inside a `<style>` block.
* Include all JavaScript inside a `<script>` block.
* Use **Vanilla JavaScript only**.
* No external libraries or frameworks.
* Return only raw HTML/CSS/JS.

---

# Layout Requirements

Create a professional calculator layout.

### Desktop

Use a **two-column layout**.

**Left panel**

* All user input controls.
* Numeric inputs.
* Matching range sliders.
* Labels.
* Units.
* Validation messages if needed.

**Right panel**

* Live calculation results.
* Summary cards.
* Key metrics.
* Monthly/annual values where appropriate.
* Highlight the primary result.

The right panel should use:

```css
background: var(--section-bg-color);
```

Both panels should always have equal height.

---

### Mobile

The mobile experience is extremely important.

Do **NOT** simply stack the entire page into one very long layout.

Requirements:

* Fit naturally within the viewport.
* Minimize scrolling.
* Reduce unnecessary spacing.
* Compact cards.
* Compact typography.
* Compact paddings.
* Compact margins.
* Results should remain visible as quickly as possible.
* Avoid huge vertical gaps.
* No oversized sliders.
* No oversized cards.
* No excessive whitespace.

The calculator should feel like a mobile app rather than a long webpage.

---

### Mobile Sticky Result Bar

On mobile (max-width: 720px), include a **fixed sticky bar** pinned to the bottom of the viewport at all times.

This bar must:

* Display the **primary result** (e.g. monthly payment) in large display type using `--font-display`.
* Display **2–3 secondary metrics** as compact pills (e.g. LTV, total interest).
* Use `background: var(--section-bg-color)` with a border radius of 18px and a strong box shadow.
* Be positioned `fixed`, `bottom: 16px`, `left: 16px`, `right: 16px`, `z-index: 100`.
* Add `padding-bottom: 90px` to `body` on mobile so content is never hidden behind the bar.
* Be **hidden on desktop** (`display: none` above 720px).

When any input changes and the result updates, the bar must:

1. **Flip animate** — each updating number slides up from below using a keyframe animation (`translateY(6px) → translateY(0)`, opacity `0.3 → 1`, duration ~220ms).
2. **Gold flash** — the bar briefly pulses with a gold glow using `box-shadow` keyframes (`rgba(176,141,87,0.35)` expanding then fading), making it obvious the value has updated.

Both animations must re-trigger on every change, not just the first. Use `el.classList.remove()` + `void el.offsetWidth` (reflow trick) before re-adding the class to force re-animation.

---

# Spacing Rules

Do **NOT** use:

```html
<br>
<br/>
```

for layout spacing.

Do **NOT** separate sections using `<br>` tags.

Instead use proper CSS:

* margin
* padding
* gap
* flex
* grid

Every section should have consistent spacing controlled entirely with CSS.

The finished calculator should fit comfortably on the screen without unnecessary empty space.

---

# Input Controls

Every numeric input must include:

* number input
* matching range slider

Both must stay synchronized in real time.

Changing either control immediately updates the other.

Each slider must display:

* minimum value label
* maximum value label

---

# Live Calculations

* No submit button.
* No calculate button.
* Every result updates instantly on every input change.
* Results should animate subtly when values change.

---

# Results Panel

Display results inside attractive cards.

Include:

* Primary highlighted result
* Secondary metrics
* Percentage values
* Currency values (if applicable)
* Totals
* Breakdowns

Use good visual hierarchy.

---

# Responsive Behaviour

Desktop:

* Two equal columns.

Tablet:

* Two columns if space allows.

Mobile:

* Single-column layout.
* Inputs appear first.
* Results immediately below.
* Keep the total page height as short as possible.
* Avoid requiring excessive scrolling to reach the results.
* Sticky bottom bar always visible — the user should never need to scroll to see the primary result.

---

# UI Requirements

* Modern card design.
* Rounded corners.
* Soft shadows.
* Consistent spacing.
* Smooth transitions.
* Accessible labels.
* Proper focus states.
* Responsive typography.
* Inputs should span full width.
* Sliders should align perfectly with their inputs.

---

# Code Quality

* Semantic HTML.
* Clean CSS.
* Modular JavaScript.
* Meaningful variable names.
* Well-organized functions.
* No duplicated logic.
* Fully responsive.
* Production-quality code.

Generate a polished calculator that feels like a premium financial tool rather than a basic form.