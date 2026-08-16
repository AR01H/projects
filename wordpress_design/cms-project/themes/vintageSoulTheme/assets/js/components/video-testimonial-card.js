
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;
	var events = window.VintageSoul.events;

	function init() {
		dom.on(document, 'click', '.video-testimonial-card__media', function () {
			events.emit('video-testimonial:play', { url: this.dataset.videoUrl || '' });
		});
	}

	window.VintageSoul.app.register('video-testimonial-card', init);
})(window, document);
