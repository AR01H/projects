/**
 * calculators.js — Shared utility library for all iframe calculators.
 * Loaded before every per-calc JS file (see calculators.php).
 *
 * Exposes a single global:  window.AHCalc
 */
(function (root) {
	'use strict';

	/* ═══════════════════════════════════════════════════════
	   DOM HELPERS
	   ═══════════════════════════════════════════════════════ */

	/**
	 * querySelector shorthand.
	 * @param {string} sel  CSS selector
	 * @param {Element} [ctx=document]
	 * @returns {Element|null}
	 */
	function qs(sel, ctx) { return (ctx || document).querySelector(sel); }

	/**
	 * querySelectorAll → Array.
	 * @param {string} sel
	 * @param {Element} [ctx=document]
	 * @returns {Element[]}
	 */
	function qsa(sel, ctx) {
		return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
	}

	/**
	 * Add an event listener; returns a remove function.
	 * @param {EventTarget} el
	 * @param {string} event
	 * @param {Function} fn
	 * @param {boolean|object} [opts]
	 * @returns {Function} call to remove listener
	 */
	function on(el, event, fn, opts) {
		el.addEventListener(event, fn, opts || false);
		return function () { el.removeEventListener(event, fn, opts || false); };
	}

	/**
	 * Set element visibility.
	 * @param {Element} el
	 * @param {boolean} show
	 */
	function toggle(el, show) {
		if (!el) { return; }
		el.style.display = show ? '' : 'none';
	}

	/**
	 * Add / remove a CSS class.
	 * @param {Element} el
	 * @param {string} cls
	 * @param {boolean} add
	 */
	function cls(el, clsName, add) {
		if (!el) { return; }
		el.classList[add ? 'add' : 'remove'](clsName);
	}

	/* ═══════════════════════════════════════════════════════
	   FORMATTING
	   ═══════════════════════════════════════════════════════ */

	/**
	 * Format a number as GBP currency (£1,234.56).
	 * @param {number} n
	 * @param {number} [decimals=2]
	 * @param {string} [symbol='£']
	 * @returns {string}
	 */
	function formatCurrency(n, decimals, symbol) {
		var d = (decimals === undefined) ? 2 : decimals;
		var s = (symbol === undefined) ? '£' : symbol;
		if (isNaN(n) || n === null) { return s + '0.00'; }
		return s + Number(n).toLocaleString('en-GB', {
			minimumFractionDigits: d,
			maximumFractionDigits: d
		});
	}

	/**
	 * Format a number with thousands separators.
	 * @param {number} n
	 * @param {number} [decimals=0]
	 * @returns {string}
	 */
	function formatNumber(n, decimals) {
		var d = (decimals === undefined) ? 0 : decimals;
		if (isNaN(n) || n === null) { return '0'; }
		return Number(n).toLocaleString('en-GB', {
			minimumFractionDigits: d,
			maximumFractionDigits: d
		});
	}

	/**
	 * Format a number as a percentage string.
	 * @param {number} n
	 * @param {number} [decimals=2]
	 * @returns {string}
	 */
	function formatPercent(n, decimals) {
		var d = (decimals === undefined) ? 2 : decimals;
		if (isNaN(n) || n === null) { return '0%'; }
		return Number(n).toFixed(d) + '%';
	}

	/**
	 * Strip non-numeric characters (keeps decimal point and minus).
	 * @param {string} str
	 * @returns {string}
	 */
	function stripNonNumeric(str) {
		return String(str).replace(/[^0-9.\-]/g, '');
	}

	/* ═══════════════════════════════════════════════════════
	   FORM HELPERS
	   ═══════════════════════════════════════════════════════ */

	/**
	 * Get a numeric value from an input element, or null if invalid.
	 * @param {string|Element} elOrSel
	 * @returns {number|null}
	 */
	function getVal(elOrSel) {
		var el = (typeof elOrSel === 'string') ? qs(elOrSel) : elOrSel;
		if (!el) { return null; }
		var raw = stripNonNumeric(el.value);
		var n = parseFloat(raw);
		return isNaN(n) ? null : n;
	}

	/**
	 * Get raw string value from an input.
	 * @param {string|Element} elOrSel
	 * @returns {string}
	 */
	function getStr(elOrSel) {
		var el = (typeof elOrSel === 'string') ? qs(elOrSel) : elOrSel;
		return el ? el.value : '';
	}

	/**
	 * Set the value of an input element.
	 * @param {string|Element} elOrSel
	 * @param {string|number} val
	 */
	function setVal(elOrSel, val) {
		var el = (typeof elOrSel === 'string') ? qs(elOrSel) : elOrSel;
		if (el) { el.value = val; }
	}

	/**
	 * Set inner text of an element (safe, no HTML).
	 * @param {string|Element} elOrSel
	 * @param {string} text
	 */
	function setText(elOrSel, text) {
		var el = (typeof elOrSel === 'string') ? qs(elOrSel) : elOrSel;
		if (el) { el.textContent = text; }
	}

	/**
	 * Set inner HTML of an element.
	 * @param {string|Element} elOrSel
	 * @param {string} html
	 */
	function setHtml(elOrSel, html) {
		var el = (typeof elOrSel === 'string') ? qs(elOrSel) : elOrSel;
		if (el) { el.innerHTML = html; }
	}

	/**
	 * Clear all inputs / selects inside a container.
	 * @param {string|Element} containerOrSel
	 */
	function clearForm(containerOrSel) {
		var el = (typeof containerOrSel === 'string') ? qs(containerOrSel) : containerOrSel;
		if (!el) { return; }
		qsa('input, select, textarea', el).forEach(function (inp) {
			inp.value = '';
			inp.checked = false;
		});
	}

	/* ═══════════════════════════════════════════════════════
	   VALIDATION
	   ═══════════════════════════════════════════════════════ */

	/**
	 * Returns true if value is a finite number greater than 0.
	 * @param {*} v
	 * @returns {boolean}
	 */
	function isPositive(v) { return typeof v === 'number' && isFinite(v) && v > 0; }

	/**
	 * Clamp a number between min and max.
	 * @param {number} n
	 * @param {number} min
	 * @param {number} max
	 * @returns {number}
	 */
	function clamp(n, min, max) { return Math.min(Math.max(n, min), max); }

	/**
	 * Show an error message on an input field.
	 * Adds an .ah-field-error sibling element; adds .ah-input-error to the input.
	 * @param {string|Element} elOrSel
	 * @param {string} message
	 */
	function showFieldError(elOrSel, message) {
		var el = (typeof elOrSel === 'string') ? qs(elOrSel) : elOrSel;
		if (!el) { return; }
		cls(el, 'ah-input-error', true);
		var err = el.parentNode.querySelector('.ah-field-error');
		if (!err) {
			err = document.createElement('span');
			err.className = 'ah-field-error';
			el.parentNode.appendChild(err);
		}
		err.textContent = message;
	}

	/**
	 * Clear error state from an input field.
	 * @param {string|Element} elOrSel
	 */
	function clearFieldError(elOrSel) {
		var el = (typeof elOrSel === 'string') ? qs(elOrSel) : elOrSel;
		if (!el) { return; }
		cls(el, 'ah-input-error', false);
		var err = el.parentNode.querySelector('.ah-field-error');
		if (err) { err.remove(); }
	}

	/**
	 * Clear all field errors inside a container.
	 * @param {string|Element} [containerOrSel=document]
	 */
	function clearAllErrors(containerOrSel) {
		var ctx = containerOrSel
			? ((typeof containerOrSel === 'string') ? qs(containerOrSel) : containerOrSel)
			: document;
		qsa('.ah-input-error', ctx).forEach(function (el) { cls(el, 'ah-input-error', false); });
		qsa('.ah-field-error', ctx).forEach(function (el) { el.remove(); });
	}

	/* ═══════════════════════════════════════════════════════
	   RESULT PANEL HELPERS
	   ═══════════════════════════════════════════════════════ */

	/**
	 * Show a result panel element.
	 * @param {string|Element} elOrSel
	 */
	function showResult(elOrSel) {
		var el = (typeof elOrSel === 'string') ? qs(elOrSel) : elOrSel;
		if (!el) { return; }
		el.style.display = '';
		cls(el, 'ah-result-visible', true);
	}

	/**
	 * Hide a result panel element.
	 * @param {string|Element} elOrSel
	 */
	function hideResult(elOrSel) {
		var el = (typeof elOrSel === 'string') ? qs(elOrSel) : elOrSel;
		if (!el) { return; }
		el.style.display = 'none';
		cls(el, 'ah-result-visible', false);
	}

	/**
	 * Animate a number counting up from 0 to target in an element.
	 * @param {string|Element} elOrSel
	 * @param {number} target
	 * @param {object} [opts]
	 * @param {number}   [opts.duration=600]   ms
	 * @param {string}   [opts.prefix='']      prepended to number (e.g. '£')
	 * @param {string}   [opts.suffix='']      appended to number (e.g. '%')
	 * @param {number}   [opts.decimals=0]
	 */
	function animateNumber(elOrSel, target, opts) {
		var el = (typeof elOrSel === 'string') ? qs(elOrSel) : elOrSel;
		if (!el) { return; }
		opts = opts || {};
		var duration = opts.duration || 600;
		var prefix   = opts.prefix   || '';
		var suffix   = opts.suffix   || '';
		var decimals = opts.decimals !== undefined ? opts.decimals : 0;
		var start    = 0;
		var startTime = null;

		function step(timestamp) {
			if (!startTime) { startTime = timestamp; }
			var progress = Math.min((timestamp - startTime) / duration, 1);
			var ease = 1 - Math.pow(1 - progress, 3); // ease-out cubic
			var current = start + (target - start) * ease;
			el.textContent = prefix + formatNumber(current, decimals) + suffix;
			if (progress < 1) { requestAnimationFrame(step); }
		}
		requestAnimationFrame(step);
	}

	/* ═══════════════════════════════════════════════════════
	   MATH UTILITIES
	   ═══════════════════════════════════════════════════════ */

	/**
	 * Round a number to n decimal places.
	 * @param {number} n
	 * @param {number} [places=2]
	 * @returns {number}
	 */
	function round(n, places) {
		var p = Math.pow(10, places === undefined ? 2 : places);
		return Math.round(n * p) / p;
	}

	/**
	 * Monthly mortgage repayment (standard annuity formula).
	 * @param {number} principal   Loan amount
	 * @param {number} annualRate  Annual interest rate (e.g. 5 for 5%)
	 * @param {number} years       Loan term in years
	 * @returns {number} monthly repayment amount
	 */
	function calcMortgagePayment(principal, annualRate, years) {
		if (annualRate === 0) { return principal / (years * 12); }
		var r = annualRate / 100 / 12;
		var n = years * 12;
		return principal * (r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
	}

	/**
	 * Stamp Duty Land Tax (England & NI) — 2024/25 rates.
	 * @param {number} price       Property price
	 * @param {boolean} [firstBuy] First-time buyer?
	 * @param {boolean} [addl]     Additional property (3% surcharge)?
	 * @returns {{ tax: number, effective: number, breakdown: Array }}
	 */
	function calcSDLT(price, firstBuy, addl) {
		var bands;
		var surcharge = addl ? 0.03 : 0;

		if (firstBuy && price <= 625000) {
			bands = [
				{ limit: 425000, rate: 0 },
				{ limit: 625000, rate: 0.05 }
			];
		} else {
			bands = [
				{ limit: 250000, rate: 0 },
				{ limit: 925000, rate: 0.05 },
				{ limit: 1500000, rate: 0.10 },
				{ limit: Infinity, rate: 0.12 }
			];
		}

		var tax = 0;
		var prev = 0;
		var breakdown = [];

		for (var i = 0; i < bands.length; i++) {
			if (price <= prev) { break; }
			var slice = Math.min(price, bands[i].limit) - prev;
			var rate  = bands[i].rate + surcharge;
			var chunk = round(slice * rate);
			if (slice > 0) {
				breakdown.push({ from: prev, to: Math.min(price, bands[i].limit), rate: rate, tax: chunk });
			}
			tax += chunk;
			prev = bands[i].limit;
		}

		return {
			tax: round(tax),
			effective: price > 0 ? round((tax / price) * 100, 4) : 0,
			breakdown: breakdown
		};
	}

	/**
	 * Affordability — maximum borrowing (income multiple).
	 * @param {number} income1   Primary income
	 * @param {number} [income2] Secondary income (0 if single)
	 * @param {number} [multiple=4.5]
	 * @returns {number}
	 */
	function calcAffordability(income1, income2, multiple) {
		var m = multiple || 4.5;
		return round((income1 + (income2 || 0)) * m, 0);
	}

	/* ═══════════════════════════════════════════════════════
	   STORAGE HELPERS (persist form values across sessions)
	   ═══════════════════════════════════════════════════════ */

	/**
	 * Save key/value pair to localStorage (silent on error).
	 * @param {string} key
	 * @param {*} value
	 */
	function saveToStorage(key, value) {
		try { localStorage.setItem('ahCalc_' + key, JSON.stringify(value)); } catch (e) {}
	}

	/**
	 * Load value from localStorage; returns defaultVal if missing.
	 * @param {string} key
	 * @param {*} [defaultVal]
	 * @returns {*}
	 */
	function loadFromStorage(key, defaultVal) {
		try {
			var raw = localStorage.getItem('ahCalc_' + key);
			return raw !== null ? JSON.parse(raw) : (defaultVal !== undefined ? defaultVal : null);
		} catch (e) { return defaultVal !== undefined ? defaultVal : null; }
	}

	/**
	 * Auto-save all named inputs in a form and restore on next load.
	 * @param {string|Element} formOrSel
	 * @param {string} storageKey  Unique key prefix for this calculator
	 */
	function autoPersist(formOrSel, storageKey) {
		var form = (typeof formOrSel === 'string') ? qs(formOrSel) : formOrSel;
		if (!form) { return; }

		var saved = loadFromStorage(storageKey, {});
		qsa('input[name], select[name], textarea[name]', form).forEach(function (inp) {
			if (inp.name && saved[inp.name] !== undefined) {
				inp.value = saved[inp.name];
			}
			inp.addEventListener('change', function () {
				saved[inp.name] = inp.value;
				saveToStorage(storageKey, saved);
			});
		});
	}

	/* ═══════════════════════════════════════════════════════
	   FUNCTION UTILITIES
	   ═══════════════════════════════════════════════════════ */

	/**
	 * Debounce — delays fn until after wait ms have elapsed since last call.
	 * @param {Function} fn
	 * @param {number} wait  ms
	 * @returns {Function}
	 */
	function debounce(fn, wait) {
		var timer;
		return function () {
			var args = arguments;
			var ctx  = this;
			clearTimeout(timer);
			timer = setTimeout(function () { fn.apply(ctx, args); }, wait);
		};
	}

	/**
	 * Throttle — calls fn at most once per wait ms.
	 * @param {Function} fn
	 * @param {number} wait  ms
	 * @returns {Function}
	 */
	function throttle(fn, wait) {
		var last = 0;
		return function () {
			var now = Date.now();
			if (now - last >= wait) { last = now; fn.apply(this, arguments); }
		};
	}

	/* ═══════════════════════════════════════════════════════
	   INPUT FORMATTING (live masking)
	   ═══════════════════════════════════════════════════════ */

	/**
	 * Attach a live currency formatter to a text input.
	 * Displays commas while typing; raw numeric value available via AHCalc.getVal().
	 * @param {string|Element} elOrSel
	 */
	function maskCurrency(elOrSel) {
		var el = (typeof elOrSel === 'string') ? qs(elOrSel) : elOrSel;
		if (!el) { return; }
		el.addEventListener('input', function () {
			var raw = stripNonNumeric(el.value);
			var n = parseFloat(raw);
			if (!isNaN(n)) {
				var pos = el.selectionStart;
				var oldLen = el.value.length;
				el.value = formatNumber(n, 0);
				var diff = el.value.length - oldLen;
				try { el.setSelectionRange(pos + diff, pos + diff); } catch (e) {}
			}
		});
	}

	/**
	 * Restrict an input to numeric characters only (allows single decimal point).
	 * @param {string|Element} elOrSel
	 * @param {boolean} [allowDecimal=true]
	 */
	function numericOnly(elOrSel, allowDecimal) {
		var el = (typeof elOrSel === 'string') ? qs(elOrSel) : elOrSel;
		if (!el) { return; }
		var pattern = allowDecimal === false ? /[^0-9]/g : /[^0-9.]/g;
		el.addEventListener('input', function () {
			var val = el.value.replace(pattern, '');
			// allow only one decimal point
			var parts = val.split('.');
			if (parts.length > 2) { val = parts[0] + '.' + parts.slice(1).join(''); }
			if (el.value !== val) { el.value = val; }
		});
	}

	/* ═══════════════════════════════════════════════════════
	   FIELD-ERROR STYLES (injected once into document head)
	   ═══════════════════════════════════════════════════════ */
	(function injectErrorStyles() {
		if (document.getElementById('ah-calc-err-styles')) { return; }
		var s = document.createElement('style');
		s.id = 'ah-calc-err-styles';
		s.textContent = [
			'.ah-input-error{border-color:#b91c1c!important;box-shadow:0 0 0 3px rgba(185,28,28,.12)!important;}',
			'.ah-field-error{display:block;font-size:.76rem;color:#b91c1c;margin-top:4px;}'
		].join('');
		document.head.appendChild(s);
	}());

	/* ═══════════════════════════════════════════════════════
	   PUBLIC API
	   ═══════════════════════════════════════════════════════ */
	root.AHCalc = {
		/* DOM */
		qs:              qs,
		qsa:             qsa,
		on:              on,
		toggle:          toggle,
		cls:             cls,

		/* Formatting */
		formatCurrency:  formatCurrency,
		formatNumber:    formatNumber,
		formatPercent:   formatPercent,
		stripNonNumeric: stripNonNumeric,

		/* Form */
		getVal:          getVal,
		getStr:          getStr,
		setVal:          setVal,
		setText:         setText,
		setHtml:         setHtml,
		clearForm:       clearForm,

		/* Validation / errors */
		isPositive:      isPositive,
		clamp:           clamp,
		showFieldError:  showFieldError,
		clearFieldError: clearFieldError,
		clearAllErrors:  clearAllErrors,

		/* Results */
		showResult:      showResult,
		hideResult:      hideResult,
		animateNumber:   animateNumber,

		/* Math */
		round:                round,
		calcMortgagePayment:  calcMortgagePayment,
		calcSDLT:             calcSDLT,
		calcAffordability:    calcAffordability,

		/* Storage */
		saveToStorage:   saveToStorage,
		loadFromStorage: loadFromStorage,
		autoPersist:     autoPersist,

		/* Utilities */
		debounce:        debounce,
		throttle:        throttle,
		maskCurrency:    maskCurrency,
		numericOnly:     numericOnly,
	};

}(window));
