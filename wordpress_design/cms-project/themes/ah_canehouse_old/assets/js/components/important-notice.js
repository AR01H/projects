/**
 * Important Notice Dialog Component
 * Handles visibility, content-based tracking, and user interactions
 */

(function() {
	'use strict';

	const dialog = document.getElementById('ch-notice');
	const overlay = document.getElementById('ch-notice-overlay');

	if (!dialog || !overlay) return;

	/**
	 * Generate hash from notice content
	 * If content changes, hash changes → shows again even same day
	 */
	function hashNotice() {
		const titleEl = dialog.querySelector('.ch-notice-title');
		const msgEl = dialog.querySelector('.ch-notice-message');
		const btnEl = dialog.querySelector('.ch-notice-btn');
		const imgEl = dialog.querySelector('.ch-notice-image img');

		const content = [
			titleEl ? titleEl.textContent : '',
			msgEl ? msgEl.textContent : '',
			btnEl ? btnEl.textContent : '',
			imgEl ? imgEl.src : ''
		].join('|');

		// Simple djb2-like hash function
		let hash = 5381;
		for (let i = 0; i < content.length; i++) {
			hash = ((hash << 5) + hash) + content.charCodeAt(i);
		}
		return Math.abs(hash).toString(36).substring(0, 12);
	}

	/**
	 * Close dialog with animation
	 */
	function closeDialog() {
		dialog.close();
		overlay.classList.remove('show');
	}

	/**
	 * Initialize notice display
	 */
	function init() {
		// Get unique hash of current notice content
		const noticeHash = hashNotice();
		const today = new Date().toISOString().split('T')[0];
		const storageKey = 'ch_notice_' + noticeHash + '_' + today;

		// Check if this exact notice was already shown today (across all tabs)
		if (localStorage.getItem(storageKey)) {
			return;
		}

		// Show dialog after short delay (let page load first)
		setTimeout(() => {
			dialog.showModal();
			overlay.classList.add('show');
			// Store in localStorage so all tabs share the same "shown today" state
			localStorage.setItem(storageKey, '1');
		}, 500);

		// Attach close handlers
		attachCloseHandlers(closeDialog);
	}

	/**
	 * Attach event listeners for closing dialog
	 */
	function attachCloseHandlers(callback) {
		// Close button click
		const closeBtn = dialog.querySelector('.ch-notice-close');
		if (closeBtn) {
			closeBtn.addEventListener('click', callback);
		}

		// Overlay click
		overlay.addEventListener('click', callback);

		// Escape key
		dialog.addEventListener('cancel', callback);
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
