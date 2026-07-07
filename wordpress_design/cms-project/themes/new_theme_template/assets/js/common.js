/**
 * assets/js/common.js - The NT helper. Loaded on every page.
 *
 * window.ntSite is injected by core/assets.php:
 *   { homeUrl, ajaxUrl, restUrl, restNonce, nonces: { action: nonce } }
 *
 * NT.ajax( action, data )  - POST to admin-ajax for an action registered in
 *                            config/ajax.php. The wp action name prefix and
 *                            per-action nonce are attached automatically.
 *                            Resolves to the JSON body ({ success, data }).
 *
 * NT.rest( route, params ) - GET a route registered in config/rest.php
 *                            (e.g. NT.rest('posts', { page: 2 })).
 */
(function () {
	'use strict';

	var site = window.ntSite || {};

	function ajax(action, data) {
		var body = new FormData();
		body.append('action', 'nt_' + action);
		if (site.nonces && site.nonces[action]) {
			body.append('nonce', site.nonces[action]);
		}
		Object.keys(data || {}).forEach(function (key) {
			body.append(key, data[key]);
		});
		return fetch(site.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		}).then(function (res) { return res.json(); });
	}

	function rest(route, params) {
		var url = new URL(String(site.restUrl).replace(/\/$/, '') + '/' + String(route).replace(/^\//, ''));
		Object.keys(params || {}).forEach(function (key) {
			url.searchParams.set(key, params[key]);
		});
		return fetch(url.toString(), {
			credentials: 'same-origin',
			headers: { 'X-WP-Nonce': site.restNonce || '' }
		}).then(function (res) { return res.json(); });
	}

	/** Tiny helpers used by the page scripts. */
	function el(tag, className, text) {
		var node = document.createElement(tag);
		if (className) { node.className = className; }
		if (text) { node.textContent = text; } // textContent = XSS-safe
		return node;
	}

	function debounce(fn, wait) {
		var t;
		return function () {
			var args = arguments, ctx = this;
			clearTimeout(t);
			t = setTimeout(function () { fn.apply(ctx, args); }, wait);
		};
	}

	window.NT = { ajax: ajax, rest: rest, el: el, debounce: debounce };
}());
