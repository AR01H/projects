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
{
    id  : 'survey-repair-renegotiation',
    nav : { label: 'Survey Repair & Renegotiation', icon: '🛠️' },

    header: {
      eyebrow : 'After the Survey Report',
      title   : 'Survey Repair & Renegotiation Calculator',
      desc    : 'Organises the repair findings from your survey and shows how different renegotiation outcomes \u2014 seller repairs vs. price reduction \u2014 would flow through to your deposit, mortgage and tax.',
    },

    /* ── INPUTS ── */
    inputs: [
      /* Offer & valuation */
      { type:'number',  id:'sr_offer',      label:'Original Accepted Offer',  prefix:'£', min:0, max:5000000, step:1000, default:300000 },
      { type:'slider',  sliderFor:'sr_offer', min:20000, max:1000000, step:5000 },
      { type:'number',  id:'sr_valuation',  label:'Lender\u2019s Valuation', prefix:'£', min:0, max:5000000, step:1000, default:295000 },

      /* Mortgage */
      { type:'section', label:'Mortgage' },
      { type:'segment', id:'sr_mortgagemode', label:'Mortgage Input Method',
        options:[{val:'amount',lbl:'Mortgage Amount'},{val:'ltv',lbl:'Loan-to-Value'}], default:'ltv' },
      { type:'number',  id:'sr_mortgageamt', label:'Mortgage Amount',    prefix:'£', min:0, max:5000000, step:1000, default:255000, showWhen:{ id:'sr_mortgagemode', val:'amount' } },
      { type:'number',  id:'sr_mortgageltv', label:'Mortgage LTV',       suffix:'%', min:0, max:100,     step:1,    default:85,     showWhen:{ id:'sr_mortgagemode', val:'ltv'    } },
      { type:'number',  id:'sr_rate',        label:'Mortgage Rate',      suffix:'%', min:0, max:15,      step:0.05, default:4.5  },
      { type:'number',  id:'sr_term',        label:'Mortgage Term',      suffix:'yrs', min:1, max:40,    step:1,    default:25   },

      /* Repair items */
      { type:'section', label:'Repair Findings' },
      { type:'segment', id:'sr_quotetype', label:'Contractor Quote Type',
        options:[{val:'fixed',lbl:'Fixed Quotes'},{val:'range',lbl:'Quote Ranges'}], default:'fixed' },

      { type:'number', id:'sr_immediate_fixed', label:'Immediate / Safety-Related Repairs (Fixed Quote)', prefix:'£', min:0, max:200000, step:100, default:4500, showWhen:{ id:'sr_quotetype', val:'fixed' } },
      { type:'number', id:'sr_immediate_low',   label:'Immediate / Safety-Related Repairs (Low Estimate)', prefix:'£', min:0, max:200000, step:100, default:3500, showWhen:{ id:'sr_quotetype', val:'range' } },
      { type:'number', id:'sr_immediate_high',  label:'Immediate / Safety-Related Repairs (High Estimate)',prefix:'£', min:0, max:200000, step:100, default:5500, showWhen:{ id:'sr_quotetype', val:'range' } },

      { type:'number', id:'sr_shortterm_fixed', label:'Work Needed Within 1\u20132 Years (Fixed Quote)', prefix:'£', min:0, max:200000, step:100, default:3000, showWhen:{ id:'sr_quotetype', val:'fixed' } },
      { type:'number', id:'sr_shortterm_low',   label:'Work Needed Within 1\u20132 Years (Low Estimate)', prefix:'£', min:0, max:200000, step:100, default:2200, showWhen:{ id:'sr_quotetype', val:'range' } },
      { type:'number', id:'sr_shortterm_high',  label:'Work Needed Within 1\u20132 Years (High Estimate)',prefix:'£', min:0, max:200000, step:100, default:3800, showWhen:{ id:'sr_quotetype', val:'range' } },

      { type:'number', id:'sr_maintenance_fixed', label:'Normal Future Maintenance (Fixed Quote)', prefix:'£', min:0, max:200000, step:100, default:1500, showWhen:{ id:'sr_quotetype', val:'fixed' } },
      { type:'number', id:'sr_maintenance_low',   label:'Normal Future Maintenance (Low Estimate)', prefix:'£', min:0, max:200000, step:100, default:1000, showWhen:{ id:'sr_quotetype', val:'range' } },
      { type:'number', id:'sr_maintenance_high',  label:'Normal Future Maintenance (High Estimate)',prefix:'£', min:0, max:200000, step:100, default:2000, showWhen:{ id:'sr_quotetype', val:'range' } },

      { type:'number', id:'sr_optional_fixed', label:'Optional Improvements (Fixed Quote)', prefix:'£', min:0, max:200000, step:100, default:2000, showWhen:{ id:'sr_quotetype', val:'fixed' } },
      { type:'number', id:'sr_optional_low',   label:'Optional Improvements (Low Estimate)', prefix:'£', min:0, max:200000, step:100, default:1200, showWhen:{ id:'sr_quotetype', val:'range' } },
      { type:'number', id:'sr_optional_high',  label:'Optional Improvements (High Estimate)',prefix:'£', min:0, max:200000, step:100, default:2800, showWhen:{ id:'sr_quotetype', val:'range' } },

      { type:'number', id:'sr_investigation', label:'Further Investigation Costs (e.g. specialist reports)', prefix:'£', min:0, max:20000, step:50, default:350 },
      { type:'number', id:'sr_contingency',   label:'Repair Contingency', suffix:'%', min:0, max:50, step:1, default:15 },

      /* Seller response */
      { type:'section', label:'Seller Response' },
      { type:'segment', id:'sr_seller_immediate',   label:'Seller Agrees to Complete: Immediate/Safety Repairs?',   options:[{val:'no',lbl:'No'},{val:'yes',lbl:'Yes'}], default:'no' },
      { type:'segment', id:'sr_seller_shortterm',   label:'Seller Agrees to Complete: 1\u20132 Year Repairs?',       options:[{val:'no',lbl:'No'},{val:'yes',lbl:'Yes'}], default:'no' },
      { type:'segment', id:'sr_seller_maintenance', label:'Seller Agrees to Complete: Routine Maintenance?',        options:[{val:'no',lbl:'No'},{val:'yes',lbl:'Yes'}], default:'no' },
      { type:'segment', id:'sr_seller_optional',    label:'Seller Agrees to Complete: Optional Improvements?',      options:[{val:'no',lbl:'No'},{val:'yes',lbl:'Yes'}], default:'no' },

      /* Negotiation */
      { type:'section', label:'Price Negotiation' },
      { type:'number', id:'sr_requested_reduction', label:'Requested Price Reduction', prefix:'£', min:0, max:500000, step:500, default:8000 },
      { type:'number', id:'sr_agreed_reduction',    label:'Agreed Price Reduction',    prefix:'£', min:0, max:500000, step:500, default:5000 },

      /* Tax */
      { type:'section', label:'Property Tax' },
      { type:'segment', id:'sr_taxregion', label:'Tax Regime',
        options:[{val:'sdlt',lbl:'England / NI (SDLT)'},{val:'ltt',lbl:'Wales (LTT)'},{val:'lbtt',lbl:'Scotland (LBTT)'}], default:'sdlt' },
      { type:'segment', id:'sr_buyerstatus', label:'Buyer Tax Status',
        options:[{val:'standard',lbl:'Standard'},{val:'additional',lbl:'Additional Property'},{val:'first-time',lbl:'First-Time Buyer'}], default:'standard' },
      { type:'segment', id:'sr_nonresident', label:'Non-UK Resident Surcharge Applies?',
        options:[{val:'no',lbl:'No'},{val:'yes',lbl:'Yes'}], default:'no' },
    ],

    /* ── CALCULATION ── */
    compute(v) {
      /* ─ Local helper: marginal (sliced) banded tax ─ */
      function bandedTax(price, bands) {
        let tax = 0, lower = 0;
        for (const b of bands) {
          if (price <= lower) break;
          const upper = Math.min(price, b.upTo);
          tax += (upper - lower) * b.rate;
          lower = upper;
        }
        return tax;
      }

      const SDLT_STD   = [ {upTo:125000,  rate:0   }, {upTo:250000,  rate:0.02 }, {upTo:925000,  rate:0.05 }, {upTo:1500000, rate:0.10}, {upTo:Infinity, rate:0.12} ];
      const SDLT_FTB   = [ {upTo:300000,  rate:0   }, {upTo:500000,  rate:0.05 } ];
      const LBTT_STD   = [ {upTo:145000,  rate:0   }, {upTo:250000,  rate:0.02 }, {upTo:325000,  rate:0.05 }, {upTo:750000,  rate:0.10}, {upTo:Infinity, rate:0.12} ];
      const LBTT_FTB   = [ {upTo:175000,  rate:0   }, {upTo:250000,  rate:0.02 }, {upTo:325000,  rate:0.05 }, {upTo:750000,  rate:0.10}, {upTo:Infinity, rate:0.12} ];
      const LTT_MAIN   = [ {upTo:225000,  rate:0   }, {upTo:400000,  rate:0.06 }, {upTo:750000,  rate:0.075}, {upTo:1500000, rate:0.10}, {upTo:Infinity, rate:0.12} ];
      const LTT_HIGHER = [ {upTo:180000,  rate:0.05}, {upTo:250000,  rate:0.085}, {upTo:400000,  rate:0.10 }, {upTo:750000,  rate:0.125}, {upTo:1500000, rate:0.15}, {upTo:Infinity, rate:0.17} ];

      function propertyTax(price, region, status, nonResident) {
        let tax = 0;
        if (region === 'sdlt') {
          tax = (status === 'first-time' && price <= 500000) ? bandedTax(price, SDLT_FTB) : bandedTax(price, SDLT_STD);
          if (status === 'additional' && price >= 40000) tax += price * 0.05;
          if (nonResident) tax += price * 0.02;
        } else if (region === 'lbtt') {
          tax = bandedTax(price, status === 'first-time' ? LBTT_FTB : LBTT_STD);
          if (status === 'additional' && price >= 40000) tax += price * 0.08;
        } else {
          tax = (status === 'additional' && price >= 40000) ? bandedTax(price, LTT_HIGHER) : bandedTax(price, LTT_MAIN);
        }
        return tax;
      }

      function mortgagePayment(principal, annualRatePct, years) {
        const r = (annualRatePct / 100) / 12;
        const n = years * 12;
        if (principal <= 0) return 0;
        if (r === 0) return principal / n;
        return principal * (r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
      }

      /* ─ Read inputs ─ */
      const offer      = v.sr_offer     || 0;
      const valuation  = v.sr_valuation || 0;

      const mortgageMode = v.sr_mortgagemode || 'ltv';
      const rate         = v.sr_rate ?? 4.5;
      const term         = v.sr_term ?? 25;

      const quoteType = v.sr_quotetype || 'fixed';
      const contingencyPct = v.sr_contingency ?? 15;
      const investigation  = v.sr_investigation || 0;

      const taxRegion   = v.sr_taxregion   || 'sdlt';
      const buyerStatus = v.sr_buyerstatus || 'standard';
      const nonResident = (v.sr_nonresident || 'no') === 'yes';

      /* ─ Repair categories ─ */
      const categories = [
        { key:'immediate',   label:'Immediate / Safety-Related Repairs', sellerFlag: v.sr_seller_immediate },
        { key:'shortterm',   label:'Work Needed Within 1\u20132 Years',   sellerFlag: v.sr_seller_shortterm },
        { key:'maintenance', label:'Normal Future Maintenance',           sellerFlag: v.sr_seller_maintenance },
        { key:'optional',    label:'Optional Improvements',               sellerFlag: v.sr_seller_optional },
      ].map(cat => {
        let low, high;
        if (quoteType === 'fixed') {
          const amt = v[`sr_${cat.key}_fixed`] || 0;
          low = amt; high = amt;
        } else {
          low  = v[`sr_${cat.key}_low`]  || 0;
          high = v[`sr_${cat.key}_high`] || 0;
        }
        const sellerAgreed = (cat.sellerFlag || 'no') === 'yes';
        return { ...cat, low, high, sellerAgreed };
      });

      const lowRepairEstimate  = categories.reduce((s, c) => s + c.low,  0) + investigation;
      const highRepairEstimate = categories.reduce((s, c) => s + c.high, 0) + investigation;

      const lowContingencyAdjusted  = lowRepairEstimate  * (1 + contingencyPct / 100);
      const highContingencyAdjusted = highRepairEstimate * (1 + contingencyPct / 100);

      const immediateLow  = categories.find(c => c.key === 'immediate').low;
      const immediateHigh = categories.find(c => c.key === 'immediate').high;
      const shortTermLow  = categories.find(c => c.key === 'shortterm').low;
      const shortTermHigh = categories.find(c => c.key === 'shortterm').high;
      const laterLow  = categories.filter(c => c.key === 'maintenance' || c.key === 'optional').reduce((s,c)=>s+c.low, 0);
      const laterHigh = categories.filter(c => c.key === 'maintenance' || c.key === 'optional').reduce((s,c)=>s+c.high, 0);

      /* ─ Repairs the seller will handle (removes cost from buyer's remaining budget, using midpoint) ─ */
      const sellerHandledLow  = categories.filter(c => c.sellerAgreed).reduce((s, c) => s + c.low,  0);
      const sellerHandledHigh = categories.filter(c => c.sellerAgreed).reduce((s, c) => s + c.high, 0);
      const buyerRemainingLow  = Math.max(lowContingencyAdjusted  - sellerHandledLow  * (1 + contingencyPct / 100), 0);
      const buyerRemainingHigh = Math.max(highContingencyAdjusted - sellerHandledHigh * (1 + contingencyPct / 100), 0);

      /* ─ Negotiation ─ */
      const requestedReduction = v.sr_requested_reduction || 0;
      const agreedReduction    = v.sr_agreed_reduction    || 0;

      const proposedRevisedOffer = offer - requestedReduction;
      const agreedRevisedPrice   = offer - agreedReduction;

      const originalTax = propertyTax(offer, taxRegion, buyerStatus, nonResident);
      const revisedTax  = propertyTax(agreedRevisedPrice, taxRegion, buyerStatus, nonResident);
      const taxSaving   = originalTax - revisedTax;

      /* ─ Mortgage: lender lends against the lower of price and valuation ─ */
      function mortgageFor(price) {
        const lendingBasis = Math.min(price, valuation || price);
        if (mortgageMode === 'amount') {
          const requested = v.sr_mortgageamt || 0;
          return Math.min(requested, lendingBasis); // can't lend more than basis allows in most cases
        } else {
          const ltv = v.sr_mortgageltv ?? 85;
          return (ltv / 100) * lendingBasis;
        }
      }

      const originalMortgage = mortgageFor(offer);
      const revisedMortgage  = mortgageFor(agreedRevisedPrice);

      const originalDeposit = offer - originalMortgage;
      const revisedDeposit  = agreedRevisedPrice - revisedMortgage;

      const originalPayment = mortgagePayment(originalMortgage, rate, term);
      const revisedPayment  = mortgagePayment(revisedMortgage, rate, term);
      const paymentChange   = revisedPayment - originalPayment;

      const originalLTV = offer > 0 ? (originalMortgage / offer) * 100 : 0;
      const revisedLTV  = agreedRevisedPrice > 0 ? (revisedMortgage / agreedRevisedPrice) * 100 : 0;

      /* ─ How the reduction splits between mortgage and deposit ─ */
      const mortgageReductionShare = originalMortgage - revisedMortgage;
      const depositReductionShare  = originalDeposit  - revisedDeposit;

      /* ─ Comparison: seller-repair route vs price-reduction route ─
             Both routes are priced at the SAME purchase price (original offer) so the only
             difference is who pays for repairs \u2014 the price-reduction figures elsewhere in
             this calculator already show what happens if the price itself is renegotiated.
             Seller-repair route: seller fixes the agreed items, buyer pays original deposit,
             and only funds their own remaining (non-seller-agreed) repairs.
             Price-reduction route: seller fixes nothing, buyer pays original deposit,
             and funds the full repair estimate themself. */
      const midRepairAdjusted        = (lowContingencyAdjusted + highContingencyAdjusted) / 2;
      const buyerRemainingMid        = (buyerRemainingLow + buyerRemainingHigh) / 2;
      const sellerRouteBuyerCash     = originalDeposit + buyerRemainingMid;
      const noRepairRouteBuyerCash   = originalDeposit + midRepairAdjusted;

      /* ─ Alerts ─ */
      const alerts = [];
      if (valuation < offer) {
        alerts.push({ id:'al-valuation', cls:'warn', msg:'The lender\u2019s valuation is below the original offer \u2014 your mortgage will be capped by the lower valuation figure, not the offer price.' });
      }
      if (agreedReduction > requestedReduction) {
        alerts.push({ id:'al-reduction', cls:'warn', msg:'The agreed reduction is larger than the amount you requested \u2014 double check these figures.' });
      }
      alerts.push({ id:'al-cash', cls:'auto', msg:'A price reduction does not convert pound-for-pound into cash in your pocket \u2014 with a mortgage in place, part of any reduction lowers the loan amount and only part reduces the cash deposit.' });
      alerts.push({ id:'al-scope', cls:'auto', msg:'This calculator presents negotiation scenarios only \u2014 there is no fixed rule requiring a seller to reduce the price by the full repair cost.' });
      alerts.push({ id:'al-rics', cls:'auto', msg:'RICS recommends obtaining written quotations from experienced contractors for any significant defects before making a legal commitment.' });
      if (quoteType === 'range') {
        alerts.push({ id:'al-range', cls:'auto', msg:'You\u2019re using quote ranges \u2014 figures below are shown as low/high scenarios, with the buyer-remaining-budget comparison using the range midpoint.' });
      }

      return {
        primaryLbl : 'Agreed Revised Purchase Price',
        primary    : gbp(agreedRevisedPrice),
        primarySub : `${gbp(agreedReduction)} reduction from the original ${gbp(offer)} offer \u00b7 revised deposit ${gbp(revisedDeposit)}`,

        chips: [
          { lbl:'Total Reported Repairs (mid)', val: gbp(midRepairAdjusted) },
          { lbl:'Agreed Revised Price',          val: gbp(agreedRevisedPrice) },
          { lbl:'Revised Deposit',               val: gbp(revisedDeposit) },
          { lbl:'Monthly Payment Change',        val: (paymentChange >= 0 ? '+' : '\u2212') + gbp(Math.abs(paymentChange)) },
        ],

        alerts,

        sections: [
          {
            title: 'Repair Cost Summary',
            rows: [
              { id:'r-immediate',   label:'Immediate / Safety-Related Repairs', dot:'#e07a7a', val: quoteType==='fixed' ? gbp(immediateLow) : `${gbp(immediateLow)} \u2013 ${gbp(immediateHigh)}` },
              { id:'r-shortterm',   label:'Work Needed Within 1\u20132 Years',    dot:'#e0b06a', val: quoteType==='fixed' ? gbp(shortTermLow) : `${gbp(shortTermLow)} \u2013 ${gbp(shortTermHigh)}` },
              { id:'r-later',       label:'Maintenance & Optional Improvements', dot:'#9bbbc0', val: quoteType==='fixed' ? gbp(laterLow) : `${gbp(laterLow)} \u2013 ${gbp(laterHigh)}` },
              { id:'r-investigate', label:'Further Investigation Costs',         dot:'#a78fa8', val: gbp(investigation) },
              { id:'r-total-low',   label:'Repair Estimate (before contingency)',dot:'#8f87a0', val: quoteType==='fixed' ? gbp(lowRepairEstimate) : `${gbp(lowRepairEstimate)} \u2013 ${gbp(highRepairEstimate)}` },
              { id:'r-total-cont',  label:`With ${contingencyPct}% Contingency`, dot:'#B08D57', tag:'Total', tagCls:'warn', val: quoteType==='fixed' ? gbp(lowContingencyAdjusted) : `${gbp(lowContingencyAdjusted)} \u2013 ${gbp(highContingencyAdjusted)}` },
            ],
          },
          {
            title: 'Seller-Agreed Repairs',
            rows: categories.map(c => ({
              id: `r-seller-${c.key}`,
              label: c.label,
              dot: c.sellerAgreed ? '#86efac' : '#9bbbc0',
              tag: c.sellerAgreed ? 'Seller Will Fix' : 'Buyer\u2019s Responsibility',
              tagCls: c.sellerAgreed ? 'good' : 'auto',
              val: quoteType==='fixed' ? gbp(c.low) : `${gbp(c.low)} \u2013 ${gbp(c.high)}`,
            })).concat([{
              id:'r-buyer-remaining', label:'Buyer\u2019s Remaining Repair Budget (with contingency)', dot:'#B08D57', tag:'Total', tagCls:'warn',
              val: quoteType==='fixed' ? gbp(buyerRemainingLow) : `${gbp(buyerRemainingLow)} \u2013 ${gbp(buyerRemainingHigh)}`,
            }]),
          },
          {
            title: 'Price Negotiation',
            rows: [
              { id:'r-original-offer', label:'Original Accepted Offer',       dot:'#9bbbc0', val: gbp(offer) },
              { id:'r-requested',      label:'Requested Price Reduction',     dot:'#e0b06a', val: gbp(requestedReduction) },
              { id:'r-proposed',       label:'Proposed Revised Offer',        dot:'#a78fa8', val: gbp(proposedRevisedOffer) },
              { id:'r-agreed-red',     label:'Agreed Price Reduction',        dot:'#e07a7a', val: gbp(agreedReduction) },
              { id:'r-agreed-price',   label:'Agreed Revised Purchase Price', dot:'#86efac', tag:'Result', tagCls:'good', val: gbp(agreedRevisedPrice) },
            ],
          },
          {
            title: 'Revised Tax, Deposit & Mortgage',
            rows: [
              { id:'r-tax-orig',    label:`Property Tax at Original Offer (${taxRegion.toUpperCase()})`, dot:'#9bbbc0', val: gbp(originalTax) },
              { id:'r-tax-rev',     label:'Property Tax at Revised Price',                                dot:'#a78fa8', val: gbp(revisedTax) },
              { id:'r-tax-saving',  label:'Tax Saving from Price Reduction',                               dot:'#86efac', tag:'Saving', tagCls:'good', val: gbp(taxSaving) },
              { id:'r-mortgage-orig', label:'Mortgage at Original Offer',      dot:'#8094a5', val: gbp(originalMortgage) },
              { id:'r-mortgage-rev',  label:'Mortgage at Revised Price',       dot:'#8f87a0', val: gbp(revisedMortgage) },
              { id:'r-ltv-rev',       label:'Revised Loan-to-Value',            dot:'#B08D57', val: `${revisedLTV.toFixed(1)}%` },
              { id:'r-deposit-orig',  label:'Deposit at Original Offer',        dot:'#9bbbc0', val: gbp(originalDeposit) },
              { id:'r-deposit-rev',   label:'Revised Deposit',                  dot:'#B08D57', tag:'Total', tagCls:'warn', val: gbp(revisedDeposit) },
            ],
          },
          {
            title: 'How the Reduction Splits (Mortgage vs Cash)',
            rows: [
              { id:'r-split-mortgage', label:'Reduction Applied to Mortgage (Reduced Borrowing)', dot:'#8094a5', val: gbp(mortgageReductionShare) },
              { id:'r-split-deposit',  label:'Reduction Applied to Cash Deposit',                  dot:'#86efac', tag:'Cash Benefit', tagCls:'good', val: gbp(depositReductionShare) },
            ],
          },
          {
            title: 'Monthly Mortgage Payment',
            rows: [
              { id:'r-payment-orig', label:'Monthly Payment at Original Offer', dot:'#9bbbc0', val: gbp(originalPayment) },
              { id:'r-payment-rev',  label:'Monthly Payment at Revised Price',  dot:'#a78fa8', val: gbp(revisedPayment) },
              { id:'r-payment-diff', label:'Monthly Payment Change',            dot: paymentChange <= 0 ? '#86efac' : '#e07a7a', tag: paymentChange <= 0 ? 'Lower' : 'Higher', tagCls: paymentChange <= 0 ? 'good' : 'warn', val: (paymentChange >= 0 ? '+' : '\u2212') + gbp(Math.abs(paymentChange)) },
            ],
          },
          {
            title: 'Seller-Repair Route vs Price-Reduction Route (both at original price)',
            rows: [
              { id:'r-route-repair',   label:'Seller Completes Agreed Repairs \u2014 Buyer Funds the Rest', dot:'#86efac', tag:'With Seller Repairs', tagCls:'good', val: gbp(sellerRouteBuyerCash) },
              { id:'r-route-none',     label:'Seller Completes No Repairs \u2014 Buyer Funds Everything',    dot:'#e07a7a', tag:'No Seller Repairs',    tagCls:'warn', val: gbp(noRepairRouteBuyerCash) },
              { id:'r-route-saving',   label:'Cash Saved by Seller-Agreed Repairs',                          dot:'#B08D57', tag:'Saving', tagCls:'good', val: gbp(noRepairRouteBuyerCash - sellerRouteBuyerCash) },
              { id:'r-route-reduction',label:'Alternative: Price Reduced Instead (deposit + full repairs)', dot:'#a78fa8', val: gbp(revisedDeposit + midRepairAdjusted) },
            ],
          },
        ],

        totalLbl : 'Buyer\u2019s Remaining Repair Budget (mid-estimate, with contingency)',
        totalSub : `after crediting seller-agreed repairs \u00b7 at revised price ${gbp(agreedRevisedPrice)}`,
        total    : quoteType==='fixed' ? gbp(buyerRemainingLow) : `${gbp(buyerRemainingLow)} \u2013 ${gbp(buyerRemainingHigh)}`,
      };
    },
  }
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
