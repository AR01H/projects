/* ============================================================
   calculators.js
   UK Property Calculator Suite

   STRUCTURE OF THIS FILE:
   ─────────────────────────────────────────────────────────
   1.  SHARED UTILITIES         — formatters, SDLT bands, math helpers
   2.  CALCULATORS CONFIG       — one object per calculator containing:
         • nav        — sidebar label & icon
         • header     — eyebrow / title / description
         • inputs[]   — every input field (type, id, label, defaults…)
         • compute()  — all calculations → returns output structure
   3.  ENGINE                   — reads config, builds DOM, wires events
   ─────────────────────────────────────────────────────────

   TO ADD A NEW CALCULATOR:
     Push one new object into CALCULATORS below. The engine does
     the rest — no HTML or engine code needs to change.

   TO EDIT AN INPUT:
     Find the calculator object, find the field in inputs[],
     change default / min / max / label etc.

   TO EDIT A CALCULATION:
     Find the calculator object, edit its compute() function.
     All values come in via the `v` argument (flat key:value map).
     Return the same shape: { primaryLbl, primary, primarySub,
     chips[], alerts[], sections[], totalLbl, total, totalSub }
   ============================================================ */


/* ============================================================
   1. SHARED UTILITIES
   ============================================================ */

/* ── SDLT Tax Bands ── */
const SDLT_STD = [
  { min: 0,       max: 125000,   rate: 0.00 },
  { min: 125000,  max: 250000,   rate: 0.02 },
  { min: 250000,  max: 925000,   rate: 0.05 },
  { min: 925000,  max: 1500000,  rate: 0.10 },
  { min: 1500000, max: Infinity, rate: 0.12 },
];
const SDLT_FTB = [
  { min: 0,      max: 300000, rate: 0.00 },
  { min: 300000, max: 500000, rate: 0.05 },
];

/* ── Calculate SDLT ──
   buyerType: 'ftb' | 'homemover' | 'additional'
   Returns { total, isFTBRelief, ftbOver }                     */
function calcSDLT(price, buyerType) {
  const isFTBEligible = (buyerType === 'ftb' && price <= 500000);
  const bands = isFTBEligible ? SDLT_FTB : SDLT_STD;
  let total = 0;
  for (const b of bands) {
    if (price <= b.min) break;
    total += (Math.min(price, b.max) - b.min) * b.rate;
  }
  return {
    total,
    isFTBRelief : isFTBEligible,
    ftbOver     : buyerType === 'ftb' && price > 500000,
  };
}

/* ── Formatters ── */
const gbp = n  => '£' + Math.round(n).toLocaleString('en-GB');
const pct = n  => (Math.round(n * 10) / 10) + '%';

/* ── Monthly mortgage payment ── */
function monthlyPayment(loan, annualRatePct, termYears) {
  const monR = (annualRatePct / 100) / 12;
  const n    = termYears * 12;
  if (loan <= 0) return 0;
  if (monR === 0) return loan / n;
  return loan * monR * Math.pow(1 + monR, n) / (Math.pow(1 + monR, n) - 1);
}


/* ============================================================
   2. CALCULATORS CONFIG
   ============================================================

   INPUT FIELD TYPES
   ─────────────────
   { type:'number',  id, label, prefix?, suffix?, hint?,
                     min, max, step, default, showWhen? }
   { type:'slider',  sliderFor: <id of paired number input>,
                     min, max, step }
   { type:'segment', id, label,
                     options:[{val,lbl}], default }
   { type:'select',  id, label,
                     options:[{val,lbl}], default }
   { type:'section', label, showWhen? }   ← visual divider only

   showWhen: { id: 'some_input_id', val: 'some_value' }
   → hides this field unless the named input equals that value

   COMPUTE() RETURN SHAPE
   ──────────────────────
   {
     primaryLbl : string,
     primary    : string,          ← e.g. gbp(12345)
     primarySub : string,

     chips: [
       { lbl: string, val: string, cls?: 'good'|'warn'|'bad' }
     ],

     alerts: [
       { id: string, cls: 'good'|'warn'|'bad', msg: string }
     ],

     sections: [
       {
         title: string,
         rows: [
           {
             id      : string,
             label   : string,
             dot     : '#hexcolor',
             tag?    : string,
             tagCls? : 'auto'|'good'|'warn'|'bad'|'info',
             val     : string,
             valCls? : 'good'|'warn'|'bad'|'zero',
           }
         ]
       }
     ],

     totalLbl? : string,
     totalSub? : string,
     total?    : string,
   }
   ============================================================ */

const CALCULATORS = [

  /* ══════════════════════════════════════════════════════════
     CALCULATOR 1 — TOTAL COST TO BUY
     ══════════════════════════════════════════════════════════ */
  {
    id  : 'total-cost',
    nav : { label: 'Total Cost to Buy', icon: '🏠' },

    header: {
      eyebrow : 'Complete Purchase',
      title   : 'Total Cost to Buy',
      desc    : 'Every cost from deposit to moving day — edit any figure to match your situation.',
    },

    /* ── INPUTS ── */
    inputs: [
      /* Purchase */
      { type:'number',  id:'tc_price',     label:'Property Purchase Price',       prefix:'£', min:0, max:10000000, step:1000, default:500000 },
      { type:'slider',  sliderFor:'tc_price',   min:50000,  max:2000000, step:5000 },
      { type:'number',  id:'tc_dep',       label:'Deposit',                       suffix:'%', min:0, max:100, step:1, default:20 },
      { type:'slider',  sliderFor:'tc_dep',     min:0,      max:100,     step:1   },
      { type:'segment', id:'tc_buyer',     label:'Buyer Type',
        options:[{val:'ftb',lbl:'First-Time Buyer'},{val:'homemover',lbl:'Home Mover'}],
        default:'ftb' },
      { type:'segment', id:'tc_prop',      label:'House Type',
        options:[{val:'house',lbl:'House'},{val:'flat',lbl:'Flat / Apartment'}],
        default:'house' },

      /* Professional Fees */
      { type:'section', label:'Professional Fees' },
      { type:'number',  id:'tc_solicitor', label:'Solicitor / Conveyancer',       prefix:'£', min:0, max:20000,  step:50,  default:1500 },
      { type:'number',  id:'tc_rics',      label:'RICS Surveyor',                 prefix:'£', min:0, max:5000,   step:50,  default:750  },
      { type:'number',  id:'tc_surveys',   label:'Other Surveys',                 prefix:'£', min:0, max:5000,   step:50,  default:1000 },
      { type:'number',  id:'tc_agent',     label:'Buying Agent Fee',              prefix:'£', min:0, max:10000,  step:50,  default:1000 },
      { type:'number',  id:'tc_broker',    label:'Mortgage Broker Fee',           prefix:'£', min:0, max:5000,   step:50,  default:500  },
      { type:'number',  id:'tc_lender',    label:'Lender Valuation Fee',          prefix:'£', min:0, max:2000,   step:10,  default:100  },

      /* Moving & Setup */
      { type:'section', label:'Moving & Setup' },
      { type:'number',  id:'tc_removals',  label:'Removals',                      prefix:'£', min:0, max:20000,  step:50,  default:1000  },
      { type:'number',  id:'tc_furniture', label:'Furniture',                     prefix:'£', min:0, max:50000,  step:100, default:5000  },
      { type:'number',  id:'tc_repairs',   label:'Immediate Repairs',             prefix:'£', min:0, max:20000,  step:100, default:1000  },
      { type:'number',  id:'tc_emergency', label:'Emergency Fund',                prefix:'£', min:0, max:50000,  step:500, default:10000 },

      /* Insurance */
      { type:'section', label:'Insurance' },
      { type:'number',  id:'tc_buyins',    label:'Buying Protection Insurance',   prefix:'£', min:0, max:5000,   step:10,  default:0 },
      { type:'number',  id:'tc_propins',   label:'Property Insurance',            prefix:'£', min:0, max:5000,   step:10,  default:0 },

      /* Leasehold — flat only */
      { type:'section', label:'Leasehold (Flat Only)', showWhen:{ id:'tc_prop', val:'flat' } },
      { type:'number',  id:'tc_ground',    label:'Ground Rent (Annual)',          prefix:'£', min:0, max:5000,   step:10,  default:200,  showWhen:{ id:'tc_prop', val:'flat' } },
      { type:'number',  id:'tc_service',   label:'Service Charge (Annual)',       prefix:'£', min:0, max:20000,  step:100, default:3000, showWhen:{ id:'tc_prop', val:'flat' } },
    ],

    /* ── CALCULATION ── */
    compute(v) {
      /* ─ Variables ─ */
      const price    = v.tc_price     || 0;
      const depPct   = v.tc_dep       || 0;
      const buyer    = v.tc_buyer     || 'ftb';
      const isFlat   = v.tc_prop      === 'flat';

      const depAmt   = price * depPct / 100;
      const mortgage = Math.max(0, price - depAmt);
      const ltv      = 100 - depPct;

      /* ─ SDLT ─ */
      const sdlt     = calcSDLT(price, buyer);
      let sdltTag    = 'Standard'; let sdltTagCls = 'auto';
      if (sdlt.isFTBRelief) { sdltTag = 'FTB Relief'; sdltTagCls = 'good'; }
      if (sdlt.ftbOver)     { sdltTag = 'Std Rates';  sdltTagCls = 'warn'; }

      /* ─ Fee subtotals ─ */
      const profFees = (v.tc_solicitor||0) + (v.tc_rics||0) + (v.tc_surveys||0)
                     + (v.tc_agent||0)    + (v.tc_broker||0) + (v.tc_lender||0);
      const moveFees = (v.tc_removals||0) + (v.tc_furniture||0)
                     + (v.tc_repairs||0)  + (v.tc_emergency||0);
      const insFees  = (v.tc_buyins||0)   + (v.tc_propins||0);
      const lhFees   = isFlat ? ((v.tc_ground||0) + (v.tc_service||0)) : 0;
      const totalFees= sdlt.total + profFees + moveFees + insFees + lhFees;

      /* ─ Grand total ─ */
      const total    = depAmt + totalFees;

      /* ─ Output ─ */
      return {
        primaryLbl : 'Total Cash Required to Buy',
        primary    : gbp(total),
        primarySub : 'deposit + stamp duty + all fees',

        chips: [
          { lbl:'Deposit',   val: gbp(depAmt)    },
          { lbl:'Mortgage',  val: gbp(mortgage)  },
          { lbl:'LTV',       val: pct(ltv)       },
          { lbl:'All Fees',  val: gbp(totalFees) },
        ],

        alerts: sdlt.isFTBRelief && sdlt.total === 0
          ? [{ id:'al-ftb', cls:'good', msg:'No stamp duty — first-time buyer relief covers this purchase.' }]
          : [],

        sections: [
          {
            title: 'Purchase Details',
            rows: [
              { id:'r-dep',  label:'Deposit Amount',    dot:'#9bbbc0', tag:'Auto', tagCls:'auto', val: gbp(depAmt)    },
              { id:'r-mtg',  label:'Mortgage Required', dot:'#7c9ea1', tag:'Auto', tagCls:'auto', val: gbp(mortgage)  },
              { id:'r-sdlt', label:'Stamp Duty (SDLT)', dot: sdlt.isFTBRelief ? '#86efac' : '#9bbbc0',
                tag: sdltTag, tagCls: sdltTagCls,
                val: gbp(sdlt.total), valCls: sdlt.isFTBRelief && sdlt.total === 0 ? 'good' : '' },
            ],
          },
          {
            title: 'Professional Fees',
            rows: [
              { id:'r-sol',  label:'Solicitor / Conveyancer', dot:'#a78fa8', val: gbp(v.tc_solicitor||0) },
              { id:'r-ric',  label:'RICS Surveyor',           dot:'#9b8ea0', val: gbp(v.tc_rics||0)      },
              { id:'r-srv',  label:'Other Surveys',           dot:'#8f87a0', val: gbp(v.tc_surveys||0)   },
              { id:'r-agt',  label:'Buying Agent Fee',        dot:'#8094a5', val: gbp(v.tc_agent||0)     },
              { id:'r-brk',  label:'Mortgage Broker Fee',     dot:'#758ea0', val: gbp(v.tc_broker||0)    },
              { id:'r-lnd',  label:'Lender Valuation Fee',    dot:'#6a859a', val: gbp(v.tc_lender||0)    },
              { id:'r-psub', label:'Subtotal',                dot:'#B08D57', val: gbp(profFees), valCls:'warn' },
            ],
          },
          {
            title: 'Moving & Setup',
            rows: [
              { id:'r-rem', label:'Removals',          dot:'#8aa07c', val: gbp(v.tc_removals||0)  },
              { id:'r-fur', label:'Furniture',         dot:'#96a87a', val: gbp(v.tc_furniture||0) },
              { id:'r-rep', label:'Immediate Repairs', dot:'#a09670', val: gbp(v.tc_repairs||0)   },
              { id:'r-eme', label:'Emergency Fund',    dot:'#b0a068', val: gbp(v.tc_emergency||0) },
            ],
          },
          {
            title: 'Insurance',
            rows: [
              { id:'r-bi', label:'Buying Protection', dot:'#7a90b0', val: gbp(v.tc_buyins||0)  },
              { id:'r-pi', label:'Property Insurance',dot:'#6a80a8', val: gbp(v.tc_propins||0) },
            ],
          },
          ...(isFlat ? [{
            title: 'Leasehold Costs (Annual)',
            rows: [
              { id:'r-gr', label:'Ground Rent',    dot:'#c8a85a', tag:'Annual', tagCls:'warn', val: gbp(v.tc_ground||0)  },
              { id:'r-sc', label:'Service Charge', dot:'#b89840', tag:'Annual', tagCls:'warn', val: gbp(v.tc_service||0) },
            ],
          }] : []),
        ],

        totalLbl : 'Total Cash Required to Buy',
        totalSub : isFlat
          ? 'deposit + stamp duty + all fees + annual leasehold'
          : 'deposit + stamp duty + all purchase fees',
        total    : gbp(total),
      };
    },
  },


  /* ══════════════════════════════════════════════════════════
     CALCULATOR 2 — MORTGAGE
     ══════════════════════════════════════════════════════════ */
  {
    id  : 'mortgage',
    nav : { label: 'Mortgage', icon: '📊' },

    header: {
      eyebrow : 'Borrowing',
      title   : 'Mortgage Calculator',
      desc    : 'Monthly repayments, total interest, and stress-test at +3%.',
    },

    /* ── INPUTS ── */
    inputs: [
      { type:'number',  id:'mg_price', label:'Property Value',    prefix:'£', min:0, max:10000000, step:1000, default:500000 },
      { type:'slider',  sliderFor:'mg_price', min:50000, max:2000000, step:5000 },
      { type:'number',  id:'mg_dep',   label:'Deposit',           suffix:'%', min:0, max:100, step:1, default:20 },
      { type:'slider',  sliderFor:'mg_dep',   min:0,    max:100,   step:1 },
      { type:'number',  id:'mg_rate',  label:'Interest Rate',     suffix:'%', min:0.1, max:15, step:0.05, default:4.5,
        hint:'Annual rate — try 4–6% for current market' },
      { type:'slider',  sliderFor:'mg_rate',  min:0.5,  max:10,    step:0.05 },
      { type:'number',  id:'mg_term',  label:'Mortgage Term',     suffix:'yrs', min:5, max:40, step:1, default:25 },
      { type:'slider',  sliderFor:'mg_term',  min:5,    max:40,    step:1 },
      { type:'segment', id:'mg_type',  label:'Repayment Type',
        options:[{val:'repay',lbl:'Repayment'},{val:'io',lbl:'Interest Only'}],
        default:'repay' },
    ],

    /* ── CALCULATION ── */
    compute(v) {
      /* ─ Variables ─ */
      const price     = v.mg_price || 0;
      const depPct    = v.mg_dep   || 0;
      const dep       = price * depPct / 100;
      const loan      = Math.max(0, price - dep);
      const rate      = v.mg_rate  || 0;
      const term      = v.mg_term  || 25;
      const isIO      = v.mg_type  === 'io';
      const monR      = (rate / 100) / 12;
      const n         = term * 12;
      const ltv       = 100 - depPct;

      /* ─ Monthly payment ─ */
      let monthly = 0;
      if (loan > 0) {
        monthly = isIO
          ? loan * monR
          : monthlyPayment(loan, rate, term);
      }

      /* ─ Totals ─ */
      const totalPaid = monthly * n;
      const totalInt  = isIO ? totalPaid : Math.max(0, totalPaid - loan);

      /* ─ Stress test (+3%) ─ */
      const stressMonthly = loan > 0
        ? monthlyPayment(loan, rate + 3, term)
        : 0;

      /* ─ LTV class ─ */
      let ltvCls = 'good';
      if (ltv > 90) ltvCls = 'bad';
      else if (ltv > 75) ltvCls = 'warn';

      /* ─ Output ─ */
      return {
        primaryLbl : 'Monthly Payment',
        primary    : gbp(monthly),
        primarySub : isIO
          ? 'interest only — capital not reducing'
          : `over ${term} year term at ${rate}%`,

        chips: [
          { lbl:'Loan Amount',    val: gbp(loan) },
          { lbl:'LTV',            val: pct(ltv),       cls: ltvCls },
          { lbl:'Total Paid',     val: gbp(totalPaid) },
          { lbl:'Total Interest', val: gbp(totalInt),  cls:'warn'  },
        ],

        alerts: isIO
          ? [{ id:'al-io', cls:'warn',
               msg: `Interest Only: you are not reducing the loan. You will still owe ${gbp(loan)} at the end of the term.` }]
          : [],

        sections: [
          {
            title: 'Monthly Breakdown',
            rows: [
              { id:'r-mon',    label:'Monthly Repayment',    dot:'#9bbbc0', tag:'Auto', tagCls:'auto', val: gbp(monthly)       },
              { id:'r-ann',    label:'Annual Repayments',    dot:'#7c9ea1', tag:'Auto', tagCls:'auto', val: gbp(monthly * 12)  },
              { id:'r-stress', label:'Stress Test (+3%)',    dot:'#c8a85a', tag:'Auto', tagCls:'warn', val: gbp(stressMonthly) },
            ],
          },
          {
            title: 'Full Term Summary',
            rows: [
              { id:'r-loan',  label:'Loan Amount',       dot:'#8094a5', val: gbp(loan)      },
              { id:'r-tpaid', label:'Total Amount Paid', dot:'#7a90b0', val: gbp(totalPaid) },
              { id:'r-tint',  label:'Total Interest',    dot:'#e87060', val: gbp(totalInt),  valCls:'bad' },
              { id:'r-ltv',   label:'Loan-to-Value',     dot:'#86efac', val: pct(ltv),       valCls: ltvCls },
            ],
          },
        ],

        totalLbl : 'Total Cost of Mortgage',
        totalSub : `${term} year term`,
        total    : gbp(totalPaid),
      };
    },
  },


  /* ══════════════════════════════════════════════════════════
     CALCULATOR 3 — STAMP DUTY
     ══════════════════════════════════════════════════════════ */
  {
    id  : 'stamp-duty',
    nav : { label: 'Stamp Duty', icon: '🏛️' },

    header: {
      eyebrow : 'SDLT',
      title   : 'Stamp Duty Calculator',
      desc    : 'Band-by-band breakdown with first-time buyer relief and additional dwelling surcharge.',
    },

    /* ── INPUTS ── */
    inputs: [
      { type:'number',  id:'sd_price', label:'Property Purchase Price', prefix:'£', min:0, max:10000000, step:1000, default:500000 },
      { type:'slider',  sliderFor:'sd_price', min:50000, max:2000000, step:5000 },
      { type:'segment', id:'sd_buyer', label:'Buyer Type',
        options:[
          {val:'ftb',        lbl:'First-Time Buyer'},
          {val:'homemover',  lbl:'Home Mover'},
          {val:'additional', lbl:'Additional Property'},
        ],
        default:'ftb' },
    ],

    /* ── CALCULATION ── */
    compute(v) {
      /* ─ Variables ─ */
      const price   = v.sd_price || 0;
      const buyer   = v.sd_buyer || 'ftb';

      /* ─ SDLT ─ */
      const sdlt    = calcSDLT(price, buyer === 'additional' ? 'homemover' : buyer);
      const addlSurcharge = buyer === 'additional' ? price * 0.03 : 0;
      const totalSDLT     = sdlt.total + addlSurcharge;
      const effRate       = price > 0 ? totalSDLT / price * 100 : 0;

      /* ─ Band rows ─ */
      const activeBands = sdlt.isFTBRelief ? SDLT_FTB : SDLT_STD;
      const bandRows = activeBands.map((b, i) => {
        const taxable = Math.max(0, Math.min(price, b.max) - b.min);
        const tax     = taxable * b.rate;
        const maxLbl  = b.max === Infinity ? '∞' : '£' + (b.max / 1000).toFixed(0) + 'k';
        const label   = `£${(b.min/1000).toFixed(0)}k – ${maxLbl} @ ${(b.rate*100).toFixed(0)}%`;
        return {
          id     : 'band-' + i,
          label,
          dot    : tax === 0 ? '#86efac' : '#b0a068',
          val    : tax === 0 ? '£0 — nil' : gbp(tax),
          valCls : tax === 0 ? 'good' : '',
        };
      });

      /* ─ Colour class ─ */
      let sdltCls = 'good';
      if (totalSDLT > price * 0.08) sdltCls = 'bad';
      else if (totalSDLT > 0)       sdltCls = 'warn';

      /* ─ Output ─ */
      return {
        primaryLbl : 'Total Stamp Duty (SDLT)',
        primary    : gbp(totalSDLT),
        primarySub : buyer === 'ftb'
          ? 'First-Time Buyer rates'
          : buyer === 'additional'
          ? 'Including +3% additional dwelling surcharge'
          : 'Standard rates',

        chips: [
          { lbl:'Base SDLT',     val: gbp(sdlt.total) },
          { lbl:'Surcharge',     val: buyer === 'additional' ? gbp(addlSurcharge) : '£0',
                                 cls: buyer === 'additional' ? 'bad' : '' },
          { lbl:'Effective Rate',val: pct(effRate), cls: sdltCls },
        ],

        alerts: sdlt.isFTBRelief && sdlt.total === 0
          ? [{ id:'al-ftb',    cls:'good', msg:'First-time buyer relief: no SDLT due on purchases up to £300,000. Relief tapers on the next £200,000.' }]
          : sdlt.ftbOver
          ? [{ id:'al-ftbovr', cls:'warn', msg:'Purchase exceeds £500,000 — first-time buyer relief does not apply. Standard rates used.' }]
          : [],

        sections: [
          {
            title: 'Band-by-Band Breakdown',
            rows : bandRows,
          },
          ...(buyer === 'additional' ? [{
            title: 'Additional Property Surcharge',
            rows: [
              { id:'r-base', label:'Base SDLT',              dot:'#9bbbc0', val: gbp(sdlt.total) },
              { id:'r-add',  label:'3% Additional Surcharge',dot:'#e87060', tag:'+3%', tagCls:'bad', val: gbp(addlSurcharge), valCls:'bad' },
            ],
          }] : []),
        ],

        totalLbl : 'Total SDLT Payable',
        totalSub : `effective rate: ${pct(effRate)}`,
        total    : gbp(totalSDLT),
      };
    },
  },


  /* ══════════════════════════════════════════════════════════
     CALCULATOR 4 — AFFORDABILITY
     ══════════════════════════════════════════════════════════ */
  {
    id  : 'affordability',
    nav : { label: 'Affordability', icon: '✅' },

    header: {
      eyebrow : 'Can You Afford It?',
      title   : 'Affordability Calculator',
      desc    : 'Maximum borrowing based on income multiple and debt service ratio.',
    },

    /* ── INPUTS ── */
    inputs: [
      { type:'number',  id:'af_inc1',   label:'Applicant 1 — Annual Income',             prefix:'£', min:0, max:500000, step:1000, default:60000 },
      { type:'number',  id:'af_inc2',   label:'Applicant 2 — Annual Income (optional)',   prefix:'£', min:0, max:500000, step:1000, default:0,
        hint:'Leave at 0 for single applicant' },
      { type:'number',  id:'af_commit', label:'Monthly Committed Outgoings',              prefix:'£', min:0, max:10000,  step:50,   default:500,
        hint:'Loans, car finance, credit cards etc.' },
      { type:'slider',  sliderFor:'af_commit', min:0, max:5000, step:50 },
      { type:'number',  id:'af_rate',   label:'Expected Interest Rate',                  suffix:'%', min:0.1, max:15, step:0.05, default:4.5 },
      { type:'number',  id:'af_term',   label:'Mortgage Term',                           suffix:'yrs', min:5, max:40, step:1, default:25 },
      { type:'segment', id:'af_mult',   label:'Income Multiple',
        options:[
          {val:'4',   lbl:'4× Cautious'},
          {val:'4.5', lbl:'4.5× Typical'},
          {val:'5',   lbl:'5× Maximum'},
        ],
        default:'4.5' },
    ],

    /* ── CALCULATION ── */
    compute(v) {
      /* ─ Variables ─ */
      const inc1       = v.af_inc1   || 0;
      const inc2       = v.af_inc2   || 0;
      const totalInc   = inc1 + inc2;
      const mult       = parseFloat(v.af_mult || '4.5');
      const commit     = v.af_commit || 0;
      const rate       = v.af_rate   || 4.5;
      const term       = v.af_term   || 25;
      const monthlyInc = totalInc / 12;

      /* ─ Max by income multiple ─ */
      const maxByMult = totalInc * mult;

      /* ─ Max by Debt Service Ratio (40% of gross monthly) ─ */
      const maxPayment = (monthlyInc * 0.40) - commit;
      const monR       = (rate / 100) / 12;
      const n          = term * 12;
      const maxByDSR   = maxPayment > 0 && monR > 0
        ? maxPayment * (Math.pow(1+monR,n) - 1) / (monR * Math.pow(1+monR,n))
        : 0;

      /* ─ Result: lower of the two ─ */
      const maxLoan = Math.max(0, Math.min(maxByMult, Math.max(0, maxByDSR)));
      const monthlyOnMax = maxLoan > 0 ? monthlyPayment(maxLoan, rate, term) : 0;
      const dsr = monthlyInc > 0 ? (monthlyOnMax + commit) / monthlyInc * 100 : 0;

      /* ─ Colour classes ─ */
      let dsrCls = 'good';
      if (dsr > 50) dsrCls = 'bad';
      else if (dsr > 38) dsrCls = 'warn';

      /* ─ Output ─ */
      return {
        primaryLbl : 'Maximum Borrowing',
        primary    : gbp(maxLoan),
        primarySub : `${mult}× income, adjusted for commitments`,

        chips: [
          { lbl:'Joint Income', val: gbp(totalInc)              },
          { lbl:`${mult}× Multi`, val: gbp(maxByMult)           },
          { lbl:'DSR 40%',      val: gbp(Math.max(0,maxByDSR)) },
          { lbl:'Debt Ratio',   val: pct(dsr), cls: dsrCls      },
        ],

        alerts: dsr > 50
          ? [{ id:'al-dsr',    cls:'bad',  msg:`Debt service ratio ${pct(dsr)} exceeds 50% — lenders typically want below 40–45%. Reduce commitments or lower the loan.` }]
          : maxByDSR < maxByMult
          ? [{ id:'al-capped', cls:'warn', msg:`Borrowing capped by monthly affordability, not the income multiple. Monthly commitments of ${gbp(commit)} are limiting capacity.` }]
          : [],

        sections: [
          {
            title: 'Income Assessment',
            rows: [
              { id:'r-i1',  label:'Applicant 1 Income',       dot:'#9bbbc0', val: gbp(inc1) },
              { id:'r-i2',  label:'Applicant 2 Income',       dot:'#7c9ea1', val: inc2 > 0 ? gbp(inc2) : '—', valCls: inc2 === 0 ? 'zero' : '' },
              { id:'r-tot', label:'Total Income',             dot:'#B08D57', val: gbp(totalInc) },
              { id:'r-mm',  label:`Max at ${mult}× Multiple`, dot:'#b0a068', tag:'Lender', tagCls:'auto', val: gbp(maxByMult) },
            ],
          },
          {
            title: 'Affordability Test (40% DSR)',
            rows: [
              { id:'r-mi',  label:'Monthly Income',           dot:'#8094a5', val: gbp(monthlyInc) },
              { id:'r-com', label:'Monthly Commitments',      dot:'#e87060', val: gbp(commit), valCls: commit > 0 ? 'warn' : '' },
              { id:'r-avl', label:'Available for Mortgage',   dot:'#86efac', val: gbp(Math.max(0,maxPayment)), valCls: maxPayment > 0 ? 'good' : 'bad' },
              { id:'r-dsr', label:'Debt Service Ratio',       dot:'#c8a85a', val: pct(dsr), valCls: dsrCls },
            ],
          },
          {
            title: 'Result',
            rows: [
              { id:'r-max', label:'Maximum Loan',             dot:'#86efac', tag:'Result', tagCls:'good', val: gbp(maxLoan), valCls: maxLoan > 0 ? 'good' : 'bad' },
              { id:'r-mpm', label:'Monthly Payment at Max',   dot:'#9bbbc0', val: gbp(monthlyOnMax) },
            ],
          },
        ],

        totalLbl : 'Maximum Mortgage',
        totalSub : 'lower of income multiple and DSR tests',
        total    : gbp(maxLoan),
      };
    },
  },


  /* ══════════════════════════════════════════════════════════
     CALCULATOR 5 — RENT VS BUY
     ══════════════════════════════════════════════════════════ */
  {
    id  : 'rent-vs-buy',
    nav : { label: 'Rent vs Buy', icon: '⚖️' },

    header: {
      eyebrow : 'Decision Tool',
      title   : 'Rent vs Buy',
      desc    : 'Net cost comparison over your chosen period — equity growth vs invested deposit.',
    },

    /* ── INPUTS ── */
    inputs: [
      { type:'number',  id:'rv_price', label:'Purchase Price',             prefix:'£', min:0, max:5000000, step:1000, default:400000 },
      { type:'slider',  sliderFor:'rv_price', min:50000, max:1500000, step:5000 },
      { type:'number',  id:'rv_dep',   label:'Deposit',                   suffix:'%', min:5, max:50, step:1, default:20 },
      { type:'number',  id:'rv_rate',  label:'Mortgage Rate',             suffix:'%', min:0.5, max:12, step:0.05, default:4.5 },
      { type:'number',  id:'rv_term',  label:'Mortgage Term',             suffix:'yrs', min:5, max:40, step:1, default:25 },
      { type:'section', label:'Rental Alternative' },
      { type:'number',  id:'rv_rent',  label:'Monthly Rent',              prefix:'£', min:0, max:10000, step:50, default:1800 },
      { type:'number',  id:'rv_rinc',  label:'Annual Rent Increase',      suffix:'%', min:0, max:10, step:0.5, default:3.5 },
      { type:'section', label:'Assumptions' },
      { type:'number',  id:'rv_hpa',   label:'House Price Growth p.a.',   suffix:'%', min:-5, max:15, step:0.5, default:3.5 },
      { type:'number',  id:'rv_inv',   label:'Investment Return on Deposit', suffix:'%', min:0, max:12, step:0.5, default:5,
        hint:'If you rented and invested the deposit elsewhere' },
      { type:'number',  id:'rv_years', label:'Comparison Period',         suffix:'yrs', min:1, max:30, step:1, default:5 },
    ],

    /* ── CALCULATION ── */
    compute(v) {
      /* ─ Variables ─ */
      const price   = v.rv_price || 0;
      const depPct  = (v.rv_dep  || 20) / 100;
      const dep     = price * depPct;
      const loan    = price - dep;
      const rate    = v.rv_rate  || 4.5;
      const term    = v.rv_term  || 25;
      const years   = v.rv_years || 5;
      const months  = years * 12;
      const hpa     = (v.rv_hpa  || 3.5) / 100;
      const invRate = (v.rv_inv  || 5)   / 100;
      const rinc    = (v.rv_rinc || 3.5) / 100;
      const monR    = (rate / 100) / 12;
      const n       = term * 12;

      /* ─ Monthly mortgage ─ */
      const monthly = monthlyPayment(loan, rate, term);

      /* ─ Buying costs ─ */
      const sdlt      = calcSDLT(price, 'homemover');
      const buyCosts  = sdlt.total + 3000;          // SDLT + est. fees
      const maintPa   = price * 0.01;               // 1% maintenance/yr
      const mortPaid  = monthly * months;
      const maintCost = maintPa * years;

      /* ─ Future property value & remaining loan ─ */
      const futureVal = price * Math.pow(1 + hpa, years);
      let remLoan = loan;
      for (let m = 0; m < months; m++) {
        const interest = remLoan * monR;
        remLoan = Math.max(0, remLoan - (monthly - interest));
      }
      const equity = futureVal - remLoan;

      /* ─ Net buy cost (what you "spend" net of gained equity) ─ */
      const netBuyCost = mortPaid + buyCosts + maintCost - (futureVal - loan);

      /* ─ Rental costs ─ */
      let totalRentPaid = 0;
      let rentNow = v.rv_rent || 0;
      for (let y = 0; y < years; y++) {
        totalRentPaid += rentNow * 12;
        rentNow *= (1 + rinc);
      }

      /* ─ Opportunity cost: what deposit+costs grows to if invested ─ */
      const depInvested = (dep + buyCosts) * Math.pow(1 + invRate, years);
      const depGrowth   = depInvested - dep - buyCosts;

      /* ─ Net rent cost ─ */
      const netRentCost = totalRentPaid - depGrowth;

      /* ─ Winner ─ */
      const diff     = netRentCost - netBuyCost;
      const buyWins  = diff > 0;
      const saving   = Math.abs(diff);

      /* ─ Output ─ */
      return {
        primaryLbl : buyWins ? 'Buying is Better' : 'Renting is Better',
        primary    : gbp(saving) + ' cheaper',
        primarySub : `over ${years} years — accounting for equity and opportunity cost`,

        chips: [
          { lbl:'Net Buy Cost',   val: gbp(netBuyCost),  cls: buyWins ? 'good' : 'bad' },
          { lbl:'Net Rent Cost',  val: gbp(netRentCost), cls: buyWins ? 'bad'  : 'good' },
          { lbl:'Future Equity',  val: gbp(equity) },
          { lbl:'Deposit Grows to', val: gbp(depInvested) },
        ],

        alerts: [
          buyWins
            ? { id:'al-buy',  cls:'good', msg:`Buying wins by ${gbp(saving)} — your equity of ${gbp(equity)} outweighs higher upfront costs over ${years} years.` }
            : { id:'al-rent', cls:'warn', msg:`Renting wins by ${gbp(saving)} — opportunity cost of ${gbp(dep+buyCosts)} tied up in the purchase outweighs equity growth over ${years} years.` }
        ],

        sections: [
          {
            title: 'Buying Scenario',
            rows: [
              { id:'r-dep',    label:'Deposit',                    dot:'#7c9ea1', val: gbp(dep) },
              { id:'r-bsdlt',  label:'Stamp Duty + Est. Fees',     dot:'#e87060', val: gbp(buyCosts), valCls:'bad' },
              { id:'r-bmtg',   label:`${years}yr Mortgage Payments`,dot:'#9bbbc0', val: gbp(mortPaid) },
              { id:'r-maint',  label:`${years}yr Maintenance (1%)`, dot:'#b0a068', val: gbp(maintCost) },
              { id:'r-fval',   label:`Home Value in ${years} Years`, dot:'#86efac', val: gbp(futureVal), valCls:'good' },
              { id:'r-equity', label:'Equity (Value − Loan)',       dot:'#86efac', tag:'Asset', tagCls:'good', val: gbp(equity), valCls:'good' },
            ],
          },
          {
            title: 'Renting Scenario',
            rows: [
              { id:'r-rnt',    label:`${years}yr Total Rent Paid`,  dot:'#e87060', val: gbp(totalRentPaid), valCls:'bad' },
              { id:'r-dep-i',  label:'Deposit + Costs Invested',    dot:'#9bbbc0', val: gbp(dep + buyCosts) },
              { id:'r-grwth',  label:`Investment Growth (${(v.rv_inv||5)}% pa)`, dot:'#86efac', val: gbp(depGrowth), valCls:'good' },
              { id:'r-inv-f',  label:'Investment Fund Value',       dot:'#86efac', tag:'Asset', tagCls:'good', val: gbp(depInvested), valCls:'good' },
            ],
          },
        ],

        totalLbl : buyWins ? 'Buying Saves You' : 'Renting Saves You',
        totalSub : `net of equity and opportunity cost over ${years} years`,
        total    : gbp(saving),
      };
    },
  },


  /* ══════════════════════════════════════════════════════════
     CALCULATOR 6 — MONTHLY COSTS
     ══════════════════════════════════════════════════════════ */
  {
    id  : 'monthly-cost',
    nav : { label: 'Monthly Costs', icon: '📅' },

    header: {
      eyebrow : 'Ongoing Outgoings',
      title   : 'Monthly Cost Calculator',
      desc    : 'Total monthly ownership cost — mortgage, bills, insurance, maintenance, and lifestyle.',
    },

    /* ── INPUTS ── */
    inputs: [
      { type:'number',  id:'mc_mtg',     label:'Monthly Mortgage Payment', prefix:'£', min:0, max:20000, step:10, default:1800,
        hint:'From the Mortgage Calculator above' },
      { type:'section', label:'Bills & Utilities' },
      { type:'number',  id:'mc_council', label:'Council Tax',        prefix:'£', min:0, max:500,  step:5,  default:180 },
      { type:'number',  id:'mc_elec',    label:'Electricity',        prefix:'£', min:0, max:500,  step:5,  default:80  },
      { type:'number',  id:'mc_gas',     label:'Gas',                prefix:'£', min:0, max:500,  step:5,  default:60  },
      { type:'number',  id:'mc_water',   label:'Water',              prefix:'£', min:0, max:200,  step:5,  default:35  },
      { type:'number',  id:'mc_broad',   label:'Broadband',          prefix:'£', min:0, max:100,  step:1,  default:35  },
      { type:'section', label:'Insurance & Protection' },
      { type:'number',  id:'mc_bldg',    label:'Buildings Insurance',prefix:'£', min:0, max:200,  step:5,  default:40  },
      { type:'number',  id:'mc_cont',    label:'Contents Insurance', prefix:'£', min:0, max:100,  step:5,  default:20  },
      { type:'number',  id:'mc_life',    label:'Life / Protection',  prefix:'£', min:0, max:300,  step:5,  default:60  },
      { type:'section', label:'Property Costs' },
      { type:'number',  id:'mc_maint',   label:'Maintenance / Repairs', prefix:'£', min:0, max:1000, step:10, default:100,
        hint:'~1% of property value ÷ 12 months' },
      { type:'number',  id:'mc_grnd',    label:'Ground Rent (monthly)',  prefix:'£', min:0, max:500,  step:5,  default:0,
        hint:'Flats only' },
      { type:'number',  id:'mc_svc',     label:'Service Charge (monthly)',prefix:'£', min:0, max:1000, step:10, default:0,
        hint:'Flats only' },
      { type:'section', label:'Lifestyle' },
      { type:'number',  id:'mc_groc',    label:'Groceries',          prefix:'£', min:0, max:1500, step:10, default:400 },
      { type:'number',  id:'mc_trans',   label:'Transport / Commute',prefix:'£', min:0, max:1000, step:10, default:150 },
      { type:'number',  id:'mc_other',   label:'Other Monthly Costs',prefix:'£', min:0, max:5000, step:10, default:200 },
      { type:'section', label:'Income' },
      { type:'number',  id:'mc_net',     label:'Net Monthly Income', prefix:'£', min:0, max:30000, step:100, default:4000 },
    ],

    /* ── CALCULATION ── */
    compute(v) {
      /* ─ Variables ─ */
      const mtg   = v.mc_mtg     || 0;
      const bills = (v.mc_council||0) + (v.mc_elec||0) + (v.mc_gas||0)
                  + (v.mc_water||0)   + (v.mc_broad||0);
      const ins   = (v.mc_bldg||0) + (v.mc_cont||0) + (v.mc_life||0);
      const prop  = (v.mc_maint||0) + (v.mc_grnd||0) + (v.mc_svc||0);
      const life  = (v.mc_groc||0)  + (v.mc_trans||0) + (v.mc_other||0);
      const total = mtg + bills + ins + prop + life;
      const net   = v.mc_net || 0;
      const left  = net - total;
      const pctInc= net > 0 ? total / net * 100 : 100;

      /* ─ Colour classes ─ */
      const leftCls = left < 0 ? 'bad' : left < 200 ? 'warn' : 'good';
      const pctCls  = pctInc > 85 ? 'bad' : pctInc > 70 ? 'warn' : 'good';

      /* ─ Output ─ */
      return {
        primaryLbl : 'Total Monthly Outgoings',
        primary    : gbp(total),
        primarySub : net > 0
          ? `${pct(pctInc)} of income — ${gbp(left)} remaining`
          : 'enter your income to see surplus',

        chips: [
          { lbl:'Mortgage',  val: gbp(mtg)   },
          { lbl:'Bills',     val: gbp(bills) },
          { lbl:'Insurance', val: gbp(ins)   },
          { lbl:'Left Over', val: gbp(left),  cls: leftCls },
        ],

        alerts: left < 0
          ? [{ id:'al-neg',   cls:'bad',  msg:`Outgoings exceed income by ${gbp(Math.abs(left))}/month. Review fixed costs or consider a lower mortgage.` }]
          : pctInc > 70
          ? [{ id:'al-tight', cls:'warn', msg:`${pct(pctInc)} of income committed. Aim for under 70% for financial comfort and emergency headroom.` }]
          : [],

        sections: [
          {
            title: 'Mortgage',
            rows: [{ id:'r-m', label:'Monthly Repayment', dot:'#7c9ea1', val: gbp(mtg) }],
          },
          {
            title: 'Bills & Utilities',
            rows: [
              { id:'r-ct',   label:'Council Tax', dot:'#8094a5', val: gbp(v.mc_council||0) },
              { id:'r-el',   label:'Electricity', dot:'#758ea0', val: gbp(v.mc_elec||0)    },
              { id:'r-ga',   label:'Gas',         dot:'#6a859a', val: gbp(v.mc_gas||0)     },
              { id:'r-wa',   label:'Water',       dot:'#607a93', val: gbp(v.mc_water||0)   },
              { id:'r-bb',   label:'Broadband',   dot:'#56718c', val: gbp(v.mc_broad||0)   },
              { id:'r-bsub', label:'Subtotal',    dot:'#B08D57', val: gbp(bills), valCls:'warn' },
            ],
          },
          {
            title: 'Insurance & Protection',
            rows: [
              { id:'r-bi', label:'Buildings',         dot:'#9b8ea0', val: gbp(v.mc_bldg||0) },
              { id:'r-ci', label:'Contents',          dot:'#8f87a0', val: gbp(v.mc_cont||0) },
              { id:'r-li', label:'Life / Protection', dot:'#7c7e9c', val: gbp(v.mc_life||0) },
            ],
          },
          {
            title: 'Property Costs',
            rows: [
              { id:'r-ma',  label:'Maintenance',  dot:'#a09670', val: gbp(v.mc_maint||0) },
              { id:'r-gr2', label:'Ground Rent',  dot:'#c8a85a', val: v.mc_grnd > 0 ? gbp(v.mc_grnd) : '—', valCls: !v.mc_grnd ? 'zero' : '' },
              { id:'r-sc2', label:'Service Charge',dot:'#b89840', val: v.mc_svc  > 0 ? gbp(v.mc_svc)  : '—', valCls: !v.mc_svc  ? 'zero' : '' },
            ],
          },
          {
            title: 'Lifestyle',
            rows: [
              { id:'r-gr', label:'Groceries', dot:'#8aa07c', val: gbp(v.mc_groc||0)  },
              { id:'r-tr', label:'Transport', dot:'#96a87a', val: gbp(v.mc_trans||0) },
              { id:'r-ot', label:'Other',     dot:'#a09670', val: gbp(v.mc_other||0) },
            ],
          },
          {
            title: 'Income vs Outgoings',
            rows: [
              { id:'r-inc',  label:'Net Monthly Income', dot:'#86efac', val: net > 0 ? gbp(net) : '—' },
              { id:'r-tot2', label:'Total Outgoings',    dot:'#e87060', val: gbp(total), valCls:'bad' },
              { id:'r-left', label:'Monthly Surplus',    dot: left >= 0 ? '#86efac' : '#e87060',
                             tag: left >= 0 ? 'Surplus' : 'Deficit', tagCls: leftCls,
                             val: gbp(left), valCls: leftCls },
              { id:'r-pct',  label:'% of Income Spent', dot:'#c8a85a', val: net > 0 ? pct(pctInc) : '—', valCls: pctCls },
            ],
          },
        ],

        totalLbl : 'Monthly Surplus / Deficit',
        totalSub : net > 0 ? `${pct(pctInc)} of income committed` : '',
        total    : gbp(left),
      };
    },
  },

];


/* ============================================================
   3. ENGINE
   Reads CALCULATORS config — builds DOM, wires all events.
   You should not need to edit anything below this line.
   ============================================================ */

let currentCalc = CALCULATORS[0];
const state = {};                        // flat { inputId: value }

const $     = id => document.getElementById(id);
const setFP = sl => {
  const p = ((+sl.value - +sl.min) / (+sl.max - +sl.min)) * 100;
  sl.style.setProperty('--fp', p + '%');
};
function animEl(el) {
  el.classList.remove('animate');
  void el.offsetWidth;
  el.classList.add('animate');
}

/* ── Build nav ── */
function buildNav() {
  const rail = document.querySelector('.nav-rail');
  CALCULATORS.forEach(calc => {
    const item = document.createElement('div');
    item.className  = 'nav-item' + (calc.id === currentCalc.id ? ' active' : '');
    item.dataset.id = calc.id;
    item.innerHTML  = `<div class="nav-icon">${calc.nav.icon}</div>
                       <div class="nav-text">${calc.nav.label}</div>`;
    item.addEventListener('click', () => switchCalc(calc.id));
    rail.appendChild(item);
  });
}

/* ── Switch calculator ── */
function switchCalc(id) {
  currentCalc = CALCULATORS.find(c => c.id === id);
  document.querySelectorAll('.nav-item').forEach(el =>
    el.classList.toggle('active', el.dataset.id === id));
  buildInputs();
  renderOutputs();
}

/* ── Build input fields from config ── */
function buildInputs() {
  const calc = currentCalc;

  $('hdrEyebrow').textContent = calc.header.eyebrow;
  $('hdrTitle').textContent   = calc.header.title;
  $('hdrDesc').textContent    = calc.header.desc;

  const body = $('inputBody');
  body.innerHTML = '';

  // Seed defaults into state if not already set
  calc.inputs.forEach(f => {
    if (f.type !== 'slider' && f.type !== 'section' && !(f.id in state)) {
      state[f.id] = f.default ?? 0;
    }
  });

  calc.inputs.forEach(f => {

    /* SECTION DIVIDER */
    if (f.type === 'section') {
      const el = document.createElement('div');
      el.className = 'input-section-label';
      el.textContent = f.label;
      if (f.showWhen) {
        el.dataset.showWhen    = f.showWhen.id;
        el.dataset.showWhenVal = f.showWhen.val;
      }
      body.appendChild(el);
      return;
    }

    /* SLIDER — must come after its paired number input in the inputs[] array */
    if (f.type === 'slider') {
      const paired = calc.inputs.find(x => x.id === f.sliderFor);
      if (!paired) return;
      const wrap = document.createElement('div');
      wrap.className = 'sl-wrap';
      if (paired.showWhen) {
        wrap.dataset.showWhen    = paired.showWhen.id;
        wrap.dataset.showWhenVal = paired.showWhen.val;
      }
      const sl = document.createElement('input');
      sl.type  = 'range'; sl.id = 'sl-' + paired.id;
      sl.min = f.min; sl.max = f.max; sl.step = f.step;
      sl.value = Math.min(+(state[paired.id] ?? paired.default ?? f.min), +f.max);
      setFP(sl);
      const fmt = val => paired.prefix === '£'
        ? '£' + (+val).toLocaleString('en-GB')
        : (val + (paired.suffix || ''));
      const mm = document.createElement('div');
      mm.className = 'sl-minmax';
      mm.innerHTML = `<span>${fmt(f.min)}</span><span>${fmt(f.max)}</span>`;
      wrap.appendChild(sl); wrap.appendChild(mm);
      body.appendChild(wrap);
      sl.addEventListener('input', () => {
        state[paired.id] = +sl.value;
        const num = $('num-' + paired.id);
        if (num) num.value = sl.value;
        setFP(sl); renderOutputs();
      });
      return;
    }

    /* SEGMENT */
    if (f.type === 'segment') {
      const wrap = document.createElement('div');
      wrap.className = 'field';
      if (f.showWhen) { wrap.dataset.showWhen = f.showWhen.id; wrap.dataset.showWhenVal = f.showWhen.val; }
      wrap.innerHTML = `<div class="field-label">${f.label}</div>`;
      const grp = document.createElement('div');
      grp.className = 'seg-group'; grp.setAttribute('role','radiogroup');
      f.options.forEach((opt, i) => {
        const rid = `seg-${f.id}-${i}`;
        const inp = document.createElement('input');
        inp.type = 'radio'; inp.name = f.id; inp.id = rid; inp.value = opt.val;
        if ((state[f.id] ?? f.default) === opt.val) inp.checked = true;
        const lbl = document.createElement('label');
        lbl.htmlFor = rid; lbl.textContent = opt.lbl;
        inp.addEventListener('change', () => {
          state[f.id] = opt.val;
          applyShowWhen(); renderOutputs();
        });
        grp.appendChild(inp); grp.appendChild(lbl);
      });
      wrap.appendChild(grp); body.appendChild(wrap);
      return;
    }

    /* SELECT */
    if (f.type === 'select') {
      const wrap = document.createElement('div');
      wrap.className = 'field';
      if (f.showWhen) { wrap.dataset.showWhen = f.showWhen.id; wrap.dataset.showWhenVal = f.showWhen.val; }
      wrap.innerHTML = `<div class="field-label">${f.label}</div>`;
      const sel = document.createElement('select');
      sel.className = 'field-select';
      f.options.forEach(opt => {
        const o = document.createElement('option');
        o.value = opt.val; o.textContent = opt.lbl;
        if ((state[f.id] ?? f.default) === opt.val) o.selected = true;
        sel.appendChild(o);
      });
      sel.addEventListener('change', () => { state[f.id] = sel.value; renderOutputs(); });
      wrap.appendChild(sel); body.appendChild(wrap);
      return;
    }

    /* NUMBER (default) */
    const wrap = document.createElement('div');
    wrap.className = 'field';
    if (f.showWhen) { wrap.dataset.showWhen = f.showWhen.id; wrap.dataset.showWhenVal = f.showWhen.val; }

    const lbl = document.createElement('label');
    lbl.className = 'field-label'; lbl.htmlFor = 'num-' + f.id;
    lbl.textContent = f.label;
    wrap.appendChild(lbl);

    if (f.hint) {
      const hint = document.createElement('div');
      hint.className = 'field-hint'; hint.textContent = f.hint;
      wrap.appendChild(hint);
    }

    const numWrap = document.createElement('div');
    numWrap.className = 'num-wrap';

    if (f.prefix) {
      const pre = document.createElement('span');
      pre.className = 'inp-pre'; pre.textContent = f.prefix;
      numWrap.appendChild(pre);
    }

    const inp = document.createElement('input');
    inp.type = 'number'; inp.id = 'num-' + f.id;
    inp.min  = f.min ?? 0; inp.max = f.max ?? 9999999; inp.step = f.step ?? 1;
    inp.value = state[f.id] ?? f.default ?? 0;

    numWrap.appendChild(inp);

    if (f.suffix) {
      const suf = document.createElement('span');
      suf.className = 'inp-suf'; suf.textContent = f.suffix;
      numWrap.appendChild(suf);
    }

    inp.addEventListener('input', () => {
      state[f.id] = parseFloat(inp.value) || 0;
      const sl = $('sl-' + f.id);
      if (sl) { sl.value = Math.min(state[f.id], +sl.max); setFP(sl); }
      applyShowWhen(); renderOutputs();
    });

    wrap.appendChild(numWrap);
    body.appendChild(wrap);
  });

  applyShowWhen();
}

/* ── Show / hide conditional fields ── */
function applyShowWhen() {
  document.querySelectorAll('[data-show-when]').forEach(el => {
    const match = state[el.dataset.showWhen] === el.dataset.showWhenVal;
    el.classList.toggle('hidden', !match);
  });
}

/* ── Render outputs from compute() result ── */
function renderOutputs() {
  const data = currentCalc.compute(state);

  animEl($('primVal'));
  $('primLbl').textContent = data.primaryLbl;
  $('primVal').textContent = data.primary;
  $('primSub').textContent = data.primarySub || '';

  // Chips
  const chipsEl = $('metricChips');
  chipsEl.innerHTML = '';
  (data.chips || []).forEach(c => {
    const chip = document.createElement('div');
    chip.className = 'metric-chip';
    chip.innerHTML = `<div class="chip-lbl">${c.lbl}</div>
                      <div class="chip-val ${c.cls||''}">${c.val}</div>`;
    chipsEl.appendChild(chip);
  });

  // Output list
  const list = $('outputList');
  list.innerHTML = '';

  // Alerts
  (data.alerts || []).forEach(a => {
    const el = document.createElement('div');
    el.className = `out-alert ${a.cls}`;
    el.textContent = a.msg;
    list.appendChild(el);
  });

  // Sections + rows
  (data.sections || []).forEach(sec => {
    const secEl = document.createElement('div');
    secEl.className = 'out-section';
    secEl.innerHTML = `<div class="out-section-title">${sec.title}</div>`;
    sec.rows.forEach(row => {
      const rowEl = document.createElement('div');
      rowEl.className = 'out-row';
      const tag = row.tag
        ? `<span class="or-tag ${row.tagCls||'auto'}">${row.tag}</span>`
        : '';
      rowEl.innerHTML = `
        <div class="or-left">
          <div class="or-dot" style="background:${row.dot||'#7c9ea1'}"></div>
          <span class="or-name">${row.label}</span>
          ${tag}
        </div>
        <div class="or-val ${row.valCls||''}">${row.val}</div>`;
      secEl.appendChild(rowEl);
    });
    list.appendChild(secEl);
  });

  // Total row
  if (data.totalLbl) {
    const tot = document.createElement('div');
    tot.className = 'total-row';
    tot.innerHTML = `
      <div>
        <div class="total-lbl">${data.totalLbl}</div>
        ${data.totalSub ? `<div class="total-sub">${data.totalSub}</div>` : ''}
      </div>
      <div class="total-val">${data.total}</div>`;
    list.appendChild(tot);
  }
}

/* ── Init ── */
buildNav();
buildInputs();
renderOutputs();
