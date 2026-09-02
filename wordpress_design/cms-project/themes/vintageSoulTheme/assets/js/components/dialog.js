
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;
	var events = window.VintageSoul.events;
	var FOCUSABLE = 'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])';

	var openDialogs = [];

	function getDialog(id) {
		return document.getElementById(id);
	}

	function trapFocus(panel, event) {
		var focusable = dom.qsa(FOCUSABLE, panel);
		if (!focusable.length) return;
		var first = focusable[0];
		var last = focusable[focusable.length - 1];

		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	}

	function open(id) {
		var dialog = getDialog(id);
		if (!dialog) return;

		// Ensure dialog is a direct child of document.body so it is never trapped in any transformed or filtered parent
		if (dialog.parentElement !== document.body) {
			document.body.appendChild(dialog);
		}

		dialog.hidden = false;
		dialog.style.display = 'flex';
		dialog.dataset.vsLastFocus = 'true';
		dialog._lastFocused = document.activeElement;
		openDialogs.push(dialog);

		document.body.style.overflow = 'hidden';

		var panel = dom.qs('.dialog__panel', dialog);
		var focusable = dom.qsa(FOCUSABLE, panel);
		if (focusable.length) focusable[0].focus();

		events.emit('dialog:open', { id: id });
	}

	function close(id) {
		var dialog = getDialog(id);
		if (!dialog) return;

		// Stop any playing video or audio on close
		var videoEl = dialog.querySelector('#vsm-video');
		var iframeEl = dialog.querySelector('#vsm-iframe');
		var imgEl = dialog.querySelector('#vsm-img');
		var mediaContainer = dialog.querySelector('#vsm-media-container');

		if (videoEl) {
			try { videoEl.pause(); videoEl.currentTime = 0; } catch(e) {}
			videoEl.src = '';
		}
		if (iframeEl) {
			iframeEl.src = '';
		}
		if (imgEl) {
			imgEl.src = '';
			imgEl.style.display = 'none';
		}
		if (mediaContainer) {
			mediaContainer.style.display = 'none';
		}

		dialog.hidden = true;
		openDialogs = openDialogs.filter(function (d) {
			return d !== dialog;
		});
		if (!openDialogs.length) {
			document.body.style.overflow = '';
		}
		if (dialog._lastFocused && typeof dialog._lastFocused.focus === 'function') {
			dialog._lastFocused.focus();
		}

		events.emit('dialog:close', { id: id });
	}

	function openStoryModal(el) {
		var modal = document.getElementById('vintage-story-modal');
		if (!modal) return;

		var quote = el.getAttribute('data-story-quote') || '';
		var author = el.getAttribute('data-story-author') || '';
		var meta = el.getAttribute('data-story-meta') || '';
		var rating = el.getAttribute('data-story-rating') || '';
		var badge = el.getAttribute('data-story-badge') || '';
		var title = el.getAttribute('data-story-title') || '';
		var img = el.getAttribute('data-story-image') || '';
		var video = el.getAttribute('data-story-video') || '';
		var platform = el.getAttribute('data-story-platform') || '';
		var link = el.getAttribute('data-story-link') || '';
		var likes = el.getAttribute('data-story-likes') || '';
		var comments = el.getAttribute('data-story-comments') || '';

		// Fallbacks from child elements if data attributes are missing
		if (!quote) {
			var quoteNode = el.querySelector('.review-box__quote, .event-review-card__quote, .franchise-review-card__quote, .memory-card-vintage__caption, .social-card__caption, p');
			if (quoteNode) quote = quoteNode.textContent.trim();
		}
		if (!author) {
			var authorNode = el.querySelector('.review-box__name, .event-review-card__meta strong, .franchise-review-card__author, .social-card__handle, strong');
			if (authorNode) author = authorNode.textContent.trim().replace(/^[—\-–]\s*/, '');
		}
		if (!meta) {
			var metaNode = el.querySelector('.review-box__location, .event-review-card__meta span, .franchise-review-card__city, .event-gallery-card__tag');
			if (metaNode) meta = metaNode.textContent.trim();
		}
		if (!title) {
			var titleNode = el.querySelector('.event-gallery-card__title, .franchise-gallery-card__title, h3, h4');
			if (titleNode) title = titleNode.textContent.trim();
		}
		if (!badge) {
			var badgeNode = el.querySelector('.social-card__platform-badge, .review-box__badge');
			if (badgeNode) badge = badgeNode.textContent.trim();
		}
		if (!rating && !video && !platform) {
			var starsNode = el.querySelector('.review-box__stars, .event-review-card__rating, .franchise-review-card__rating');
			if (starsNode) rating = starsNode.textContent.trim();
			else rating = '';
		}
		if (!img) {
			var imgNode = el.querySelector('img');
			if (imgNode && imgNode.src) {
				img = imgNode.src;
			} else {
				var bgVar = el.style.getPropertyValue('--card-bg-img');
				if (bgVar) {
					var match = bgVar.match(/url\(['"]?([^'"]+)['"]?\)/);
					if (match) img = match[1];
				}
			}
		}

		var quoteEl = document.getElementById('vsm-quote');
		var authorEl = document.getElementById('vsm-author-name');
		var metaEl = document.getElementById('vsm-meta');
		var starsEl = document.getElementById('vsm-stars');
		var badgeEl = document.getElementById('vsm-badge');
		var titleEl = document.getElementById('vsm-title');
		var imgEl = document.getElementById('vsm-img');
		var mediaContainer = document.getElementById('vsm-media-container');
		var videoContainer = document.getElementById('vsm-video-container');
		var videoEl = document.getElementById('vsm-video');
		var iframeEl = document.getElementById('vsm-iframe');
		var socialStatsEl = document.getElementById('vsm-social-stats');
		var likesEl = document.getElementById('vsm-likes');
		var commentsEl = document.getElementById('vsm-comments');
		var platformLink = document.getElementById('vsm-platform-link');
		var platformText = document.getElementById('vsm-platform-btn-text');

		var quoteIconEl = modal.querySelector('.vintage-story-panel__quote-icon');

		function normalizeVideoEmbed(url) {
			if (!url) return '';
			url = url.trim();
			var ytMatch = url.match(/(?:youtube\.com\/(?:watch\?v=|shorts\/|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i);
			if (ytMatch) {
				return 'https://www.youtube-nocookie.com/embed/' + ytMatch[1] + '?autoplay=0&rel=0&modestbranding=1';
			}
			var igMatch = url.match(/instagram\.com\/(?:reel|p)\/([a-zA-Z0-9_-]+)/i);
			if (igMatch) {
				return 'https://www.instagram.com/' + (url.includes('/p/') ? 'p' : 'reel') + '/' + igMatch[1] + '/embed/';
			}
			var vimeoMatch = url.match(/vimeo\.com\/(?:video\/)?([0-9]+)/i);
			if (vimeoMatch) {
				return 'https://player.vimeo.com/video/' + vimeoMatch[1] + '?autoplay=0&title=0&byline=0';
			}
			if (url.indexOf('facebook.com') !== -1 && (url.indexOf('/videos/') !== -1 || url.indexOf('/watch/') !== -1)) {
				return 'https://www.facebook.com/plugins/video.php?href=' + encodeURIComponent(url) + '&show_text=0&autoplay=0';
			}
			return url;
		}

		// 1. Handle Video / Media Embed
		if (video) {
			if (mediaContainer) mediaContainer.style.display = 'none';
			if (videoContainer) videoContainer.style.display = 'block';

			var embedUrl = normalizeVideoEmbed(video);
			var isIframe = embedUrl.includes('youtube') || embedUrl.includes('youtu.be') || embedUrl.includes('instagram.com') || embedUrl.includes('vimeo') || embedUrl.includes('facebook.com/plugins') || embedUrl.includes('embed');

			if (isIframe) {
				if (videoEl) {
					try { videoEl.pause(); } catch(e) {}
					videoEl.style.display = 'none';
					videoEl.src = '';
					videoEl.removeAttribute('src');
					videoEl.load();
				}
				if (iframeEl) {
					iframeEl.src = embedUrl;
					iframeEl.style.display = 'block';
				}
			} else {
				if (iframeEl) {
					iframeEl.style.display = 'none';
					iframeEl.src = '';
					iframeEl.removeAttribute('src');
				}
				if (videoEl) {
					videoEl.src = embedUrl;
					videoEl.style.display = 'block';
					var playPromise = videoEl.play();
					if (playPromise && playPromise.catch) playPromise.catch(function() {});
				}
			}
		} else {
			if (videoContainer) videoContainer.style.display = 'none';
			if (iframeEl) { iframeEl.src = ''; iframeEl.removeAttribute('src'); iframeEl.style.display = 'none'; }
			if (videoEl) { try { videoEl.pause(); } catch(e){} videoEl.src = ''; videoEl.removeAttribute('src'); videoEl.style.display = 'none'; }

			if (mediaContainer && imgEl) {
				imgEl.onerror = function () {
					imgEl.style.display = 'none';
					imgEl.src = '';
					if (mediaContainer) mediaContainer.style.display = 'none';
				};

				var cleanImg = (img || '').trim();
				if (cleanImg && cleanImg !== window.location.href && !cleanImg.endsWith('/') && !cleanImg.includes('data:image/svg+xml;utf8,<svg') && cleanImg !== 'about:blank') {
					imgEl.src = cleanImg;
					imgEl.style.display = 'block';
					mediaContainer.style.display = 'flex';
				} else {
					imgEl.src = '';
					imgEl.style.display = 'none';
					mediaContainer.style.display = 'none';
				}
			}
		}

		// 2. Handle Text & Quotes (Clean Presentation)
		var isReview = Boolean(author) && Boolean(rating);

		if (quoteEl) {
			if (quote) {
				quoteEl.textContent = isReview ? '“' + quote.replace(/^[“"']+|[”"']+$/g, '') + '”' : quote.replace(/^[“"']+|[”"']+$/g, '');
				quoteEl.style.display = 'block';
			} else {
				quoteEl.textContent = '';
				quoteEl.style.display = 'none';
			}
		}

		if (quoteIconEl) {
			quoteIconEl.style.display = isReview ? 'block' : 'none';
		}

		if (authorEl) {
			authorEl.textContent = author ? (author.startsWith('@') ? author : '— ' + author) : '';
			authorEl.style.display = author ? 'block' : 'none';
		}
		if (metaEl) {
			metaEl.textContent = meta;
			metaEl.style.display = meta ? 'block' : 'none';
		}

		if (starsEl) {
			if (rating && rating !== 'none' && isReview) {
				starsEl.textContent = rating;
				starsEl.style.display = 'block';
			} else {
				starsEl.style.display = 'none';
			}
		}

		if (badgeEl) {
			if (badge) {
				badgeEl.textContent = badge;
				badgeEl.style.display = 'inline-block';
			} else {
				badgeEl.style.display = 'none';
			}
		}

		if (titleEl) {
			if (title && title !== author) {
				titleEl.textContent = title;
				titleEl.style.display = 'block';
			} else {
				titleEl.style.display = 'none';
			}
		}

		// 3. Handle Social Engagement Stats (ONLY when explicit social platform is provided)
		if (socialStatsEl) {
			var hasRealSocial = (Boolean(platform) && Boolean(link));
			if (hasRealSocial) {
				socialStatsEl.style.display = 'flex';
				if (likesEl) {
					var spanL = likesEl.querySelector('span');
					if (spanL) spanL.textContent = likes || '1.2k';
					likesEl.style.display = likes ? 'inline-flex' : 'none';
				}
				if (commentsEl) {
					var spanC = commentsEl.querySelector('span');
					if (spanC) spanC.textContent = comments || '45';
					commentsEl.style.display = comments ? 'inline-flex' : 'none';
				}
				if (platformLink) {
					if (link) {
						platformLink.href = link;
						platformLink.style.display = 'inline-flex';
						if (platformText) {
							var platName = platform ? platform.toUpperCase() : 'SOCIAL';
							platformText.textContent = 'OPEN ON ' + platName + ' ↗';
						}
					} else {
						platformLink.style.display = 'none';
					}
				}
			} else {
				socialStatsEl.style.display = 'none';
			}
		}

		open('vintage-story-modal');
	}

	function init() {
		dom.on(document, 'click', '[data-vs-dialog-open]', function (event) {
			event.preventDefault();
			open(this.getAttribute('data-vs-dialog-open'));
		});

		dom.on(document, 'click', '[data-vs-dialog-close]', function () {
			var dialog = this.closest('[data-vs-dialog]');
			if (dialog) close(dialog.id);
		});

		dom.on(document, 'click', '[data-story-modal]', function (event) {
			event.preventDefault();
			openStoryModal(this);
		});

		dom.on(document, 'keydown', '[data-story-modal]', function (event) {
			if (event.key === 'Enter' || event.key === ' ') {
				event.preventDefault();
				openStoryModal(this);
			}
		});

		document.addEventListener('keydown', function (event) {
			if (!openDialogs.length) return;
			var current = openDialogs[openDialogs.length - 1];

			if (event.key === 'Escape') {
				close(current.id);
			} else if (event.key === 'Tab') {
				trapFocus(dom.qs('.dialog__panel', current), event);
			}
		});
	}

	var dynamicCount = 0;

	function create(options) {
		options = options || {};
		var id = 'dialog-dynamic-' + (++dynamicCount);

		var dialog = document.createElement('div');
		dialog.className = 'dialog' + (options.fullscreen ? ' dialog--fullscreen' : '');
		dialog.id = id;
		dialog.setAttribute('data-vs-dialog', '');
		dialog.hidden = true;

		var backdrop = document.createElement('div');
		backdrop.className = 'dialog__backdrop';
		backdrop.setAttribute('data-vs-dialog-close', '');
		dialog.appendChild(backdrop);

		var panel = document.createElement('div');
		panel.className = 'dialog__panel';
		panel.setAttribute('role', 'dialog');
		panel.setAttribute('aria-modal', 'true');
		panel.setAttribute('aria-labelledby', id + '-title');
		dialog.appendChild(panel);

		var header = document.createElement('header');
		header.className = 'dialog__header';
		var title = document.createElement('h2');
		title.className = 'dialog__title';
		title.id = id + '-title';
		title.textContent = options.title || '';
		header.appendChild(title);
		var closeBtn = document.createElement('button');
		closeBtn.type = 'button';
		closeBtn.className = 'dialog__close';
		closeBtn.setAttribute('aria-label', 'Close');
		closeBtn.innerHTML = '&times;';
		closeBtn.setAttribute('data-vs-dialog-close', '');
		header.appendChild(closeBtn);
		panel.appendChild(header);

		if (options.body) {
			var body = document.createElement('div');
			body.className = 'dialog__body';
			body.textContent = options.body;
			panel.appendChild(body);
		}

		if (options.buttons && options.buttons.length) {
			var footer = document.createElement('footer');
			footer.className = 'dialog__footer';
			options.buttons.forEach(function (btnOptions) {
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'btn' + (btnOptions.variant ? ' btn--' + btnOptions.variant : '');
				btn.textContent = btnOptions.label;
				btn.addEventListener('click', function (event) {
					if (typeof btnOptions.onClick === 'function') {
						btnOptions.onClick(event);
					}
					if (btnOptions.close !== false) {
						close(id);
					}
				});
				footer.appendChild(btn);
			});
			panel.appendChild(footer);
		}

		document.body.appendChild(dialog);

		var handleClose = function (detail) {
			if (detail.id !== id) return;
			window.VintageSoul.events.off('dialog:close', handleClose);
			dialog.remove();
		};
		window.VintageSoul.events.on('dialog:close', handleClose);

		open(id);
		return id;
	}

	window.VintageSoul.dialog = { open: open, close: close, create: create, openStoryModal: openStoryModal };
	window.VintageSoul.app.register('dialog', init);
})(window, document);
