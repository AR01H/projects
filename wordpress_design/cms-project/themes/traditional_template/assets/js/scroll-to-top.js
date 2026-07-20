/**
 * assets/js/scroll-to-top.js - Floating "back to top" button.
 * Registered in config/assets.php 'js' (loads on every page).
 * Creates its own button, so no template markup is needed.
 * Styles: .nt-scroll-top in assets/css/components.css.
 */
(function () {
	'use strict';

	var btn = document.createElement('button');
	btn.type = 'button';
	btn.className = 'nt-scroll-top';
	btn.setAttribute('aria-label', 'Scroll to top');
	btn.innerHTML = '&#8679;';
	document.body.appendChild(btn);

	var ticking = false;
	function onScroll() {
		if (ticking) { return; }
		ticking = true;
		window.requestAnimationFrame(function () {
			btn.classList.toggle('is-visible', window.scrollY > 400);
			ticking = false;
		});
	}
	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	btn.addEventListener('click', function () {
		var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' });
	});
}());
