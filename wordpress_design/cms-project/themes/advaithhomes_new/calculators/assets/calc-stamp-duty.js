/* ============================================================
   calc-stamp-duty.js — Stamp Duty (SDLT) calculator logic.
   Runs standalone inside the calculator iframe. No dependencies.
   ============================================================ */
(function () {
	'use strict';

	var loc = 'england';
	var replacing = false;

	function el(id) { return document.getElementById(id); }
	function gbp(n) { return '£' + Math.round(n).toLocaleString('en-GB'); }

	function setLocation(value) {
		loc = value;
		toggle('engBtn', value === 'england');
		toggle('scotBtn', value === 'scotland');
		calc();
	}

	function setReplacing(value) {
		replacing = value;
		toggle('yesBtn', value);
		toggle('noBtn', !value);
		calc();
	}

	function toggle(id, on) {
		var node = el(id);
		if (node) { node.classList.toggle('active', !!on); }
	}

	function calc() {
		var priceEl = el('propPrice');
		var buyerEl = el('buyerType');
		var rowsEl = el('breakdownRows');
		var amountEl = el('resultAmount');
		if (!priceEl || !buyerEl || !rowsEl || !amountEl) { return; }

		var price = parseFloat(priceEl.value) || 0;
		var buyer = buyerEl.value;
		var isFirstTime = buyer === 'first-time';
		var isAdditional = buyer === 'additional' || buyer === 'company';

		var bands, labels;
		if (loc === 'england') {
			if (isAdditional) {
				bands = [
					{ threshold: 250000, rate: 0.05 },
					{ threshold: 925000, rate: 0.10 },
					{ threshold: 1500000, rate: 0.15 },
					{ threshold: Infinity, rate: 0.17 }
				];
			} else {
				bands = [
					{ threshold: 250000, rate: 0.00 },
					{ threshold: 925000, rate: 0.05 },
					{ threshold: 1500000, rate: 0.10 },
					{ threshold: Infinity, rate: 0.12 }
				];
			}
			labels = ['£0–£250,000', '£250,001–£925,000', '£925,001–£1.5m', 'Over £1.5m'];
		} else {
			bands = [
				{ threshold: 145000, rate: 0.00 },
				{ threshold: 250000, rate: 0.02 },
				{ threshold: 325000, rate: 0.05 },
				{ threshold: 750000, rate: 0.10 },
				{ threshold: Infinity, rate: 0.12 }
			];
			labels = ['£0–£145,000', '£145,001–£250,000', '£250,001–£325,000', '£325,001–£750,000', 'Over £750,000'];
		}

		var prev = 0;
		var totalTax = 0;
		var html = '';

		bands.forEach(function (band, i) {
			var slice = Math.max(0, Math.min(price, band.threshold) - prev);
			var bandTax = slice * band.rate;
			totalTax += bandTax;
			if (price > prev && slice > 0) {
				var pct = Math.round(band.rate * 100);
				html += row(pct + '% on ' + (labels[i] || 'remainder'), gbp(bandTax));
			}
			prev = band.threshold;
		});

		// First-time buyer relief (England): 0% to £425k, 5% on £425k–£625k, none above.
		var relief = 0;
		if (isFirstTime && loc === 'england') {
			if (price <= 425000) {
				relief = totalTax;
			} else if (price <= 625000) {
				var relieved = Math.max(0, price - 425000) * 0.05;
				relief = totalTax - relieved;
			}
		}

		var payable = Math.max(0, totalTax - relief);

		html += row('Total SDLT', gbp(totalTax), 'total-row');
		if (relief > 0) {
			html += row('First-time buyer relief', '−' + gbp(relief), 'relief-row');
		}
		html += row('Total Payable', gbp(payable), 'final-row');

		amountEl.textContent = gbp(payable);
		rowsEl.innerHTML = html;
	}

	function row(label, value, extra) {
		return '<div class="breakdown-row' + (extra ? ' ' + extra : '') + '">' +
			'<span class="br-label">' + label + '</span>' +
			'<span class="br-val">' + value + '</span></div>';
	}

	document.addEventListener('DOMContentLoaded', function () {
		var eng = el('engBtn'), scot = el('scotBtn');
		var yes = el('yesBtn'), no = el('noBtn');
		var price = el('propPrice'), buyer = el('buyerType'), btn = el('calcBtn');

		if (eng) { eng.addEventListener('click', function () { setLocation('england'); }); }
		if (scot) { scot.addEventListener('click', function () { setLocation('scotland'); }); }
		if (yes) { yes.addEventListener('click', function () { setReplacing(true); }); }
		if (no) { no.addEventListener('click', function () { setReplacing(false); }); }
		if (price) { price.addEventListener('input', calc); }
		if (btn) { btn.addEventListener('click', calc); }
		if (buyer) {
			buyer.addEventListener('change', function () {
				var additional = this.value === 'additional' || this.value === 'company';
				var group = el('replacingGroup');
				if (group) { group.style.display = additional ? 'none' : 'block'; }
				calc();
			});
		}

		calc();
	});
})();
