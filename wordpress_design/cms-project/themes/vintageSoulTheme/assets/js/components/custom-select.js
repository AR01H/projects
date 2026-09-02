/**
 * VintageSoulTheme - Clean Custom Select Component
 * Replaces OS default blue highlight with theme botanical green & gold selection.
 */

(function (window, document) {
	'use strict';

	function initCustomSelect() {
		var selects = document.querySelectorAll('select.form-select, .form-group select');

		selects.forEach(function (select) {
			if (select.getAttribute('data-vst-select-enhanced') === 'true') {
				return;
			}
			select.setAttribute('data-vst-select-enhanced', 'true');

			// Hide native select visually while keeping it in DOM for form submission
			select.style.position = 'absolute';
			select.style.opacity = '0';
			select.style.pointerEvents = 'none';
			select.style.width = '1px';
			select.style.height = '1px';
			select.style.overflow = 'hidden';

			// Create wrapper
			var wrapper = document.createElement('div');
			wrapper.className = 'vst-select-wrapper';
			select.parentNode.insertBefore(wrapper, select);
			wrapper.appendChild(select);

			// Create trigger
			var trigger = document.createElement('button');
			trigger.type = 'button';
			trigger.className = 'vst-select-trigger';
			trigger.setAttribute('aria-haspopup', 'listbox');
			trigger.setAttribute('aria-expanded', 'false');

			var selectedOption = select.options[select.selectedIndex] || select.options[0];
			var labelSpan = document.createElement('span');
			labelSpan.className = 'vst-select-label';
			labelSpan.textContent = selectedOption ? selectedOption.text : 'Select...';

			var arrowSpan = document.createElement('span');
			arrowSpan.className = 'vst-select-arrow';
			arrowSpan.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';

			trigger.appendChild(labelSpan);
			trigger.appendChild(arrowSpan);
			wrapper.appendChild(trigger);

			// Create dropdown list
			var dropdown = document.createElement('div');
			dropdown.className = 'vst-select-dropdown';
			dropdown.setAttribute('role', 'listbox');
			dropdown.style.display = 'none';

			Array.prototype.forEach.call(select.options, function (opt, idx) {
				var item = document.createElement('div');
				item.className = 'vst-select-option' + (idx === select.selectedIndex ? ' is-selected' : '');
				item.setAttribute('role', 'option');
				item.setAttribute('data-value', opt.value);
				item.setAttribute('aria-selected', idx === select.selectedIndex ? 'true' : 'false');
				item.textContent = opt.text;

				item.addEventListener('click', function (e) {
					e.stopPropagation();
					select.value = opt.value;
					labelSpan.textContent = opt.text;

					dropdown.querySelectorAll('.vst-select-option').forEach(function (el) {
						el.classList.remove('is-selected');
						el.setAttribute('aria-selected', 'false');
					});
					item.classList.add('is-selected');
					item.setAttribute('aria-selected', 'true');

					closeDropdown();
					trigger.focus();

					var changeEvt = new Event('change', { bubbles: true });
					var inputEvt = new Event('input', { bubbles: true });
					select.dispatchEvent(changeEvt);
					select.dispatchEvent(inputEvt);
				});

				dropdown.appendChild(item);
			});

			wrapper.appendChild(dropdown);

			function openDropdown() {
				document.querySelectorAll('.vst-select-wrapper.is-open').forEach(function (other) {
					if (other !== wrapper) {
						other.classList.remove('is-open');
						var otherDrop = other.querySelector('.vst-select-dropdown');
						var otherTrig = other.querySelector('.vst-select-trigger');
						if (otherDrop) otherDrop.style.display = 'none';
						if (otherTrig) otherTrig.setAttribute('aria-expanded', 'false');
					}
				});

				wrapper.classList.add('is-open');
				dropdown.style.display = 'block';
				trigger.setAttribute('aria-expanded', 'true');
			}

			function closeDropdown() {
				wrapper.classList.remove('is-open');
				dropdown.style.display = 'none';
				trigger.setAttribute('aria-expanded', 'false');
			}

			trigger.addEventListener('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				if (wrapper.classList.contains('is-open')) {
					closeDropdown();
				} else {
					openDropdown();
				}
			});

			// Sync if select value changes programmatically
			select.addEventListener('change', function () {
				var opt = select.options[select.selectedIndex];
				if (opt) {
					labelSpan.textContent = opt.text;
					dropdown.querySelectorAll('.vst-select-option').forEach(function (el) {
						var isSel = el.getAttribute('data-value') === opt.value;
						el.classList.toggle('is-selected', isSel);
						el.setAttribute('aria-selected', isSel ? 'true' : 'false');
					});
				}
			});
		});
	}

	// Close on outside click
	document.addEventListener('click', function (e) {
		if (!e.target.closest('.vst-select-wrapper')) {
			document.querySelectorAll('.vst-select-wrapper.is-open').forEach(function (openWrapper) {
				openWrapper.classList.remove('is-open');
				var drop = openWrapper.querySelector('.vst-select-dropdown');
				var trig = openWrapper.querySelector('.vst-select-trigger');
				if (drop) drop.style.display = 'none';
				if (trig) trig.setAttribute('aria-expanded', 'false');
			});
		}
	});

	// Close on Escape key
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			document.querySelectorAll('.vst-select-wrapper.is-open').forEach(function (openWrapper) {
				openWrapper.classList.remove('is-open');
				var drop = openWrapper.querySelector('.vst-select-dropdown');
				var trig = openWrapper.querySelector('.vst-select-trigger');
				if (drop) drop.style.display = 'none';
				if (trig) trig.setAttribute('aria-expanded', 'false');
			});
		}
	});

	if (window.VintageSoul && window.VintageSoul.app) {
		window.VintageSoul.app.register('custom-select', initCustomSelect);
	} else if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCustomSelect);
	} else {
		initCustomSelect();
	}
})(window, document);
