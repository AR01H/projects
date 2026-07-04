# Prompt: Add New Calculator to calculators.js

Copy this prompt, replace `[TOPIC]`, and paste it to any AI.
The AI will return a single object — paste it into the `CALCULATORS` array in
`static/calculator_json/calculators.js` before the closing `];`.

---

You are a JavaScript developer. Generate a single calculator config object to push into the CALCULATORS array in calculators.js for a UK property tool.

Calculator topic: [TOPIC]
e.g. "Rental Yield Calculator" or "Buy-to-Let Profit Calculator"

OUTPUT: Return only the JavaScript object — no explanation, no extra text, no CALCULATORS = [...] wrapper.

════════════════════════════════════════
EXACT STRUCTURE TO FOLLOW
════════════════════════════════════════

```js
{
  id  : 'kebab-id',              // unique, e.g. 'rental-yield'
  nav : { label: 'Nav Label', icon: '🏡' },

  header: {
    eyebrow : 'Short Category',
    title   : 'Calculator Title',
    desc    : 'One-sentence description.',
  },

  inputs: [
    // ── INPUT TYPES ──

    // Number box
    { type:'number', id:'xx_field', label:'Field Label',
      prefix:'£',       // OR suffix:'%' / suffix:'yrs' / omit both
      min:0, max:100000, step:100, default:50000,
      hint:'Optional helper text' },           // hint is optional

    // Slider — must come right after its paired number input
    { type:'slider', sliderFor:'xx_field', min:10000, max:500000, step:1000 },

    // Segmented toggle (2–4 options)
    { type:'segment', id:'xx_type', label:'Toggle Label',
      options:[{val:'a',lbl:'Option A'},{val:'b',lbl:'Option B'}],
      default:'a' },

    // Dropdown
    { type:'select', id:'xx_pick', label:'Dropdown Label',
      options:[{val:'x',lbl:'Choice X'},{val:'y',lbl:'Choice Y'}],
      default:'x' },

    // Visual section divider (no value, just a heading)
    { type:'section', label:'Section Heading' },

    // Conditional field — hidden unless another field equals a value
    { type:'number', id:'xx_cond', label:'Conditional Field',
      prefix:'£', min:0, max:10000, step:100, default:0,
      showWhen:{ id:'xx_type', val:'b' } },
  ],

  compute(v) {
    // v is a flat object: { fieldId: numericOrStringValue }
    // Read inputs with: const price = v.xx_field || 0;

    // ── AVAILABLE HELPERS (already defined in the file) ──
    //   gbp(n)                                    → '£12,345'
    //   pct(n)                                    → '4.5%'
    //   monthlyPayment(loan, annualRatePct, years) → number
    //   calcSDLT(price, 'ftb'|'homemover'|'additional')
    //     → { total, isFTBRelief, ftbOver }

    // ── your maths here ──

    return {
      primaryLbl : 'Main Metric Name',
      primary    : gbp(someValue),       // or pct() or a plain string
      primarySub : 'subtitle / context',

      chips: [
        { lbl:'Label', val: gbp(x) },
        { lbl:'Label', val: pct(y),  cls:'good' },   // cls: 'good'|'warn'|'bad'
        { lbl:'Label', val: gbp(z),  cls:'warn' },
      ],

      alerts: [
        // Conditional — return [] when no alert is needed
        { id:'al-id', cls:'good', msg:'Alert message text.' },
        // cls: 'good' | 'warn' | 'bad'
      ],

      sections: [
        {
          title: 'Section Heading',
          rows: [
            {
              id     : 'r-unique',
              label  : 'Row Label',
              dot    : '#9bbbc0',          // colour dot — see palette below
              tag    : 'Auto',             // optional badge text
              tagCls : 'auto',             // 'auto'|'good'|'warn'|'bad'|'info'
              val    : gbp(someVal),
              valCls : 'good',             // optional: 'good'|'warn'|'bad'|'zero'
            },
          ],
        },
      ],

      totalLbl : 'Total Row Label',
      totalSub : 'context line',
      total    : gbp(grandTotal),
    };
  },
},
```

════════════════════════════════════════
DOT COLOUR PALETTE
════════════════════════════════════════

Teal/blue:  #9bbbc0  #7c9ea1  #8094a5  #758ea0  #6a859a  #607a93
Purple:     #a78fa8  #9b8ea0  #8f87a0  #7c7e9c
Gold/amber: #B08D57  #b0a068  #c8a85a  #b89840
Green:      #86efac  #8aa07c  #96a87a
Red:        #e87060

════════════════════════════════════════
RULES
════════════════════════════════════════

- Prefix every input id with a 2-letter calculator code (e.g. ry_ for rental-yield)
- Every row id must be unique across the whole file (e.g. r-ry-1, r-ry-2 …)
- slider sliderFor must match the id of the immediately preceding number input
- alerts: [] is valid — return an empty array when no alert applies
- compute() must be pure: no DOM access, no globals, only v, the helpers, and Math.*
- All currency values must use gbp(), all percentages must use pct()

════════════════════════════════════════
WHERE TO PASTE THE RESULT
════════════════════════════════════════

Open:  static/calculator_json/calculators.js
Find:  the closing ];  (last line of the CALCULATORS array)
Paste: the new object above that line, after the last existing calculator's closing },

The engine builds the nav item, input panel, and output panel automatically —
no HTML or engine code needs to change.
