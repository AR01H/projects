
(function (window, document) {
	'use strict';

	var registry = [];

	function register(name, init) {
		registry.push({ name: name, init: init });
	}

	function boot() {
		registry.forEach(function (entry) {
			try {
				entry.init();
			} catch (error) {
				if (window.console) {
					console.error('[VintageSoul] ' + entry.name + ' failed to init:', error);
				}
			}
		});
	}

	function enhanceArticleCards() {
		var containers = document.querySelectorAll('.single-article__content, .page-standard-content, .entry-content');
		if (!containers.length) return;

		var emojiRegex = /^(\uD83C[\uDF00-\uDFFF]|\uD83D[\uDC00-\uDE4F]|\uD83D[\uDE80-\uDEFF]|[\u2600-\u27BF]|\p{Extended_Pictographic})/u;

		containers.forEach(function (content) {
			var children = Array.prototype.slice.call(content.children);
			for (var i = 0; i < children.length; i++) {
				var el = children[i];
				if (!el || el.nodeName !== 'P') continue;

				var raw = el.textContent.trim();

				// Case 1: Paragraph is just an emoji icon
				if (emojiRegex.test(raw) && raw.length <= 4) {
					var emoji = raw;
					var titleEl = children[i + 1];
					var descEl = children[i + 2];

					if (titleEl && titleEl.nodeName === 'P') {
						var card = document.createElement('div');
						card.className = 'term-card';
						card.innerHTML =
							'<div class="term-card__header">' +
							'<span class="term-card__icon">' + emoji + '</span>' +
							'<strong class="term-card__title">' + titleEl.innerHTML + '</strong>' +
							'</div>' +
							(descEl && descEl.nodeName === 'P' ? '<div class="term-card__desc">' + descEl.innerHTML + '</div>' : '');

						content.insertBefore(card, el);
						el.remove();
						titleEl.remove();
						if (descEl && descEl.nodeName === 'P') {
							descEl.remove();
							i += 2;
						} else {
							i += 1;
						}
						continue;
					}
				}

				// Case 2: Paragraph starts with an emoji symbol
				if (emojiRegex.test(raw) && raw.length < 500 && !el.classList.contains('term-card')) {
					el.classList.add('term-card');
				}
			}
		});
	}

	register('article-cards', enhanceArticleCards);

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}

	window.VintageSoul = window.VintageSoul || {};
	window.VintageSoul.app = { register: register };
})(window, document);
