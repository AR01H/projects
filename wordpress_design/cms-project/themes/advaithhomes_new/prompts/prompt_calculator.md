You are an expert front-end developer.

Build a HTML calculator body code only for:
**[TOPIC] — e.g. UK First-Time Buyer Mortgage Calculator**

---

## Design System
Use these CSS variables exactly as given — no hardcoded colours:

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
    --radius-sm: 8px; --radius-md: 12px; --radius-lg: 16px;
    --space-2: 8px; --space-4: 16px; --space-6: 24px; --space-8: 32px;
}

---

## Requirements
- Output: <body> content only — no <!DOCTYPE>, no <html>, no <head>
- No headings, titles, or descriptive text — calculation fields and results only
- All inputs have a paired range slider that syncs both ways in real time
- Show min/max labels on each slider end
- All results update instantly on every input change — no submit button
- Fully responsive (single column mobile, two column desktop)
- Inputs left, results panel right (--section-bg-color background)
- End every content section with <br/>
- Include all CSS in a <style> block and JS in a <script> block inside the body
- Vanilla JS only — no frameworks or libraries
- No explanation, no markdown — raw code only