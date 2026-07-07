/**
 * assets/js/main.js - Site chrome behaviour: mobile nav toggle + header
 * live search (NT.ajax 'search_posts'). Loaded on every page after common.js.
 */
(function () {
	'use strict';

	/* Mobile nav toggle */
	var toggle = document.querySelector('[data-nt-nav-toggle]');
	var nav = document.getElementById('nt-nav');
	if (toggle && nav) {
		toggle.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

	/* Header live search */
	var box = document.querySelector('[data-nt-search]');
	if (!box || !window.NT) { return; }
	var input = box.querySelector('[data-nt-search-input]');
	var panel = box.querySelector('[data-nt-search-results]');
	if (!input || !panel) { return; }

	function render(results) {
		panel.innerHTML = '';
		if (!results.length) {
			panel.hidden = true;
			return;
		}
		results.forEach(function (item) {
			var link = NT.el('a', 'nt-search-result');
			link.href = item.url;
			link.appendChild(NT.el('span', 'nt-search-result-title', item.title));
			link.appendChild(NT.el('span', 'nt-search-result-meta', item.type + ' - ' + item.date));
			panel.appendChild(link);
		});
		panel.hidden = false;
	}

	input.addEventListener('input', NT.debounce(function () {
		var q = input.value.trim();
		if (q.length < 2) {
			panel.hidden = true;
			return;
		}
		NT.ajax('search_posts', { q: q }).then(function (res) {
			render((res && res.success && res.data.results) || []);
		}).catch(function () { panel.hidden = true; });
	}, 250));

	document.addEventListener('click', function (e) {
		if (!box.contains(e.target)) { panel.hidden = true; }
	});
}());
