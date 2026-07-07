/**
 * assets/js/pages/news.js - News grid: search + Load More through
 * NT.rest('posts'). Loaded only on the news page (config/pages.php).
 * Card markup mirrors components/cards/post_card.php.
 */
(function () {
	'use strict';

	var grid = document.querySelector('[data-nt-news-grid]');
	if (!grid || !window.NT) { return; }

	var moreBtn = document.querySelector('[data-nt-news-more]');
	var searchInput = document.querySelector('[data-nt-news-search]');
	var status = document.querySelector('[data-nt-news-status]');

	var state = {
		page: 1,
		search: '',
		perPage: parseInt(grid.getAttribute('data-per-page') || '6', 10),
		totalPages: parseInt(grid.getAttribute('data-total-pages') || '1', 10)
	};

	function say(message) {
		if (status) { status.textContent = message || ''; }
	}

	function card(item) {
		var article = NT.el('article', 'nt-card');
		var link = NT.el('a', 'nt-card-link');
		link.href = item.url;

		var media = NT.el('div', 'nt-card-media');
		if (item.thumb) {
			var img = document.createElement('img');
			img.src = item.thumb;
			img.alt = item.title;
			img.loading = 'lazy';
			media.appendChild(img);
		} else {
			media.appendChild(NT.el('span', 'nt-card-media-empty'));
		}

		var body = NT.el('div', 'nt-card-body');
		body.appendChild(NT.el('h3', 'nt-card-title', item.title));
		body.appendChild(NT.el('p', 'nt-card-excerpt', item.excerpt));
		body.appendChild(NT.el('span', 'nt-card-meta', item.date));

		link.appendChild(media);
		link.appendChild(body);
		article.appendChild(link);
		return article;
	}

	function load(replace) {
		say('Loading...');
		if (moreBtn) { moreBtn.disabled = true; }

		NT.rest('posts', { page: state.page, per_page: state.perPage, search: state.search })
			.then(function (res) {
				if (replace) { grid.innerHTML = ''; }
				(res.items || []).forEach(function (item) { grid.appendChild(card(item)); });
				state.totalPages = res.total_pages || 1;
				say(res.items && res.items.length ? '' : 'No news found.');
				if (moreBtn) { moreBtn.hidden = state.page >= state.totalPages; }
			})
			.catch(function () { say('Could not load news. Please try again.'); })
			.finally(function () { if (moreBtn) { moreBtn.disabled = false; } });
	}

	if (moreBtn) {
		moreBtn.addEventListener('click', function () {
			state.page += 1;
			load(false);
		});
	}

	if (searchInput) {
		searchInput.addEventListener('input', NT.debounce(function () {
			state.search = searchInput.value.trim();
			state.page = 1;
			load(true);
		}, 300));
	}
}());
