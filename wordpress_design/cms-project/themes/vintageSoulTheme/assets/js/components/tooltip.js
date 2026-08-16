
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;
	var tooltipEl = null;

	function getTooltipEl() {
		if (tooltipEl) return tooltipEl;
		tooltipEl = document.createElement('div');
		tooltipEl.className = 'tooltip';
		tooltipEl.setAttribute('role', 'tooltip');
		tooltipEl.hidden = true;
		document.body.appendChild(tooltipEl);
		return tooltipEl;
	}

	function show(trigger) {
		var text = trigger.getAttribute('data-vs-tooltip');
		if (!text) return;

		var tooltip = getTooltipEl();
		tooltip.textContent = text;
		tooltip.hidden = false;

		var rect = trigger.getBoundingClientRect();
		var tipRect = tooltip.getBoundingClientRect();
		tooltip.style.top = window.scrollY + rect.top - tipRect.height - 8 + 'px';
		tooltip.style.left = window.scrollX + rect.left + (rect.width - tipRect.width) / 2 + 'px';
	}

	function hide() {
		if (tooltipEl) tooltipEl.hidden = true;
	}

	function init() {

		dom.on(document, 'mouseover', '[data-vs-tooltip]', function () {
			show(this);
		});
		dom.on(document, 'mouseout', '[data-vs-tooltip]', hide);
		dom.on(document, 'focusin', '[data-vs-tooltip]', function () {
			show(this);
		});
		dom.on(document, 'focusout', '[data-vs-tooltip]', hide);
		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') hide();
		});
	}

	window.VintageSoul.tooltip = { show: show, hide: hide };
	window.VintageSoul.app.register('tooltip', init);
})(window, document);
