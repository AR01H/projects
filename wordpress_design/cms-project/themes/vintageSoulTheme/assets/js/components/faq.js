
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;

	function closeButtonFaq(item) {
		item.classList.remove('is-open');
		var trigger = dom.qs('.faq__trigger', item);
		if (trigger) trigger.setAttribute('aria-expanded', 'false');
	}

	function updateUrlHash(id) {
		if (id && window.history && window.history.replaceState) {
			window.history.replaceState(null, null, '#' + id);
		}
	}

	function checkHashAndOpen() {
		var hash = window.location.hash;
		if (!hash || hash.length < 2) return;

		try {
			var target = document.querySelector(hash);
			if (!target) return;

			// If target is <details> inside an accordion
			if (target.tagName === 'DETAILS') {
				var accordion = target.closest('.faq-accordion');
				if (accordion) {
					accordion.querySelectorAll('details[open]').forEach(function (other) {
						if (other !== target) other.removeAttribute('open');
					});
				}
				target.setAttribute('open', '');
				setTimeout(function () {
					target.scrollIntoView({ behavior: 'smooth', block: 'center' });
				}, 150);
			} else if (target.classList.contains('faq__item')) {
				var list = target.closest('.faq__list');
				if (list) {
					list.querySelectorAll('.faq__item.is-open').forEach(function (other) {
						if (other !== target) closeButtonFaq(other);
					});
				}
				target.classList.add('is-open');
				var trig = dom.qs('.faq__trigger', target);
				if (trig) trig.setAttribute('aria-expanded', 'true');
				setTimeout(function () {
					target.scrollIntoView({ behavior: 'smooth', block: 'center' });
				}, 150);
			}
		} catch (e) {
			// Ignore invalid selector
		}
	}

	function init() {
		// 1. Button-based FAQ Accordions (.faq__item)
		dom.on(document, 'click', '.faq__trigger', function () {
			var item = this.closest('.faq__item');
			if (!item) return;

			var willOpen = !item.classList.contains('is-open');

			var list = item.closest('.faq__list');
			if (list) {
				dom.qsa('.faq__item.is-open', list).forEach(function (other) {
					if (other !== item) closeButtonFaq(other);
				});
			}

			item.classList.toggle('is-open', willOpen);
			this.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
			if (willOpen && item.id) {
				updateUrlHash(item.id);
			}
		});

		// 2. Details/Summary-based FAQ Accordions (.faq-accordion details)
		dom.on(document, 'click', '.faq-accordion__summary', function (e) {
			var currentDetails = this.parentElement;
			if (!currentDetails || currentDetails.tagName !== 'DETAILS') return;

			var accordion = currentDetails.closest('.faq-accordion');
			if (!accordion) return;

			// If current is about to open, close all other open siblings immediately
			if (!currentDetails.open) {
				var openDetails = accordion.querySelectorAll('details[open]');
				openDetails.forEach(function (other) {
					if (other !== currentDetails) {
						other.removeAttribute('open');
					}
				});
				if (currentDetails.id) {
					updateUrlHash(currentDetails.id);
				}
			}
		});

		// Handle keyboard toggle & accessibility
		dom.on(document, 'toggle', '.faq-accordion details', function () {
			if (this.open) {
				var accordion = this.closest('.faq-accordion');
				if (!accordion) return;
				var self = this;
				accordion.querySelectorAll('details[open]').forEach(function (other) {
					if (other !== self) {
						other.removeAttribute('open');
					}
				});
				if (this.id) {
					updateUrlHash(this.id);
				}
			}
		});

		// Check hash on load and hash changes
		checkHashAndOpen();
		window.addEventListener('hashchange', checkHashAndOpen);
	}

	if (window.VintageSoul && window.VintageSoul.app) {
		window.VintageSoul.app.register('faq', init);
	} else {
		document.addEventListener('DOMContentLoaded', init);
	}
})(window, document);

