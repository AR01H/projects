/**
 * Site Mode Manager - Admin JS
 *
 * Handles click-to-toggle on mode cards via AJAX.
 * No build step required - vanilla jQuery (bundled with WP).
 */
/* global smmAdmin, jQuery */

( function ( $ ) {
	'use strict';

	var $grid    = $( '.smm-mode-grid' );
	var $feedback = $( '#smm-save-feedback' );

	// ── Click handler ──────────────────────────────────────────────────────

	$grid.on( 'click', '.smm-mode-card', function () {
		var $card    = $( this );
		var newMode  = $card.data( 'mode' );

		// Already active - do nothing.
		if ( $card.hasClass( 'smm-mode-card--active' ) ) {
			return;
		}

		// UI: loading state.
		$grid.find( '.smm-mode-card' ).addClass( 'smm-mode-card--loading' );
		setFeedback( smmAdmin.i18n.saving, 'saving' );

		$.ajax( {
			url:    smmAdmin.ajaxUrl,
			method: 'POST',
			data:   {
				action: 'smm_toggle_mode',
				nonce:  smmAdmin.nonce,
				mode:   newMode,
			},
		} )
		.done( function ( response ) {
			if ( response.success ) {
				// Update card states.
				$grid.find( '.smm-mode-card' )
					.removeClass( 'smm-mode-card--active' )
					.attr( 'aria-checked', 'false' )
					.find( '.smm-active-badge' ).remove();

				$card
					.addClass( 'smm-mode-card--active' )
					.attr( 'aria-checked', 'true' )
					.append(
						$( '<span class="smm-active-badge">' ).text( '✓ Active' )
					);

				setFeedback( smmAdmin.i18n.saved, 'success' );

				// Update admin bar label if present.
				var modeIcons = {
					normal:       '🟢',
					coming_soon:  '🟡',
					maintenance:  '🔴',
				};
				var icon  = modeIcons[ response.data.mode ] || '⚙️';
				var label = icon + ' ' + response.data.label;
				$( '#wp-admin-bar-smm-status .ab-item' ).text( label );

			} else {
				setFeedback( response.data.message || smmAdmin.i18n.error, 'error' );
			}
		} )
		.fail( function () {
			setFeedback( smmAdmin.i18n.error, 'error' );
		} )
		.always( function () {
			$grid.find( '.smm-mode-card' ).removeClass( 'smm-mode-card--loading' );
		} );
	} );

	// ── Keyboard support (Enter / Space on cards) ──────────────────────────

	$grid.on( 'keydown', '.smm-mode-card', function ( e ) {
		if ( e.key === 'Enter' || e.key === ' ' ) {
			e.preventDefault();
			$( this ).trigger( 'click' );
		}
	} );

	// ── Custom HTML Save Handler ───────────────────────────────────────────

	$( '.smm-save-custom-html' ).on( 'click', function () {
		var $button = $( this );
		var type = $button.data( 'type' );
		var $textarea = $( '.smm-custom-html-textarea[data-type="' + type + '"]' );
		var $feedback = $( '.smm-custom-html-feedback[data-type="' + type + '"]' );
		var customHtml = $textarea.val();
		var nonce = $button.data( 'nonce' );

		// Map types to AJAX actions
		var actionMap = {
			coming_soon: 'smm_save_coming_soon_html',
			maintenance: 'smm_save_maintenance_html',
			page: 'smm_save_page_html'
		};
		var action = actionMap[ type ] || '';

		if ( ! action ) {
			setCustomHtmlFeedback( $feedback, 'Invalid type', 'error' );
			return;
		}

		// Show loading state.
		$button.prop( 'disabled', true );
		setCustomHtmlFeedback( $feedback, 'Saving...', 'saving' );

		$.ajax( {
			url:    smmAdmin.ajaxUrl,
			method: 'POST',
			data:   {
				action: action,
				nonce:  nonce,
				html:   customHtml,
			},
		} )
		.done( function ( response ) {
			if ( response.success ) {
				setCustomHtmlFeedback( $feedback, response.data.message, 'success' );
			} else {
				setCustomHtmlFeedback( $feedback, response.data.message || 'Error saving HTML', 'error' );
			}
		} )
		.fail( function () {
			setCustomHtmlFeedback( $feedback, 'An error occurred. Please try again.', 'error' );
		} )
		.always( function () {
			$button.prop( 'disabled', false );
		} );
	} );

	// ── Helpers ────────────────────────────────────────────────────────────

	/**
	 * Shows a feedback message and clears it after a short delay.
	 *
	 * @param {string} msg   Message text.
	 * @param {string} type  'saving' | 'success' | 'error'
	 */
	function setFeedback( msg, type ) {
		$feedback
			.text( msg )
			.attr( 'class', 'smm-feedback smm-feedback--' + type );

		if ( type !== 'saving' ) {
			clearTimeout( $feedback.data( 'timer' ) );
			$feedback.data(
				'timer',
				setTimeout( function () {
					$feedback.text( '' ).attr( 'class', 'smm-feedback' );
				}, 2800 )
			);
		}
	}

	/**
	 * Shows a feedback message for custom HTML (with custom timeout).
	 *
	 * @param {jQuery} $feedback  Feedback element.
	 * @param {string} msg        Message text.
	 * @param {string} type       'saving' | 'success' | 'error'
	 */
	function setCustomHtmlFeedback( $feedback, msg, type ) {
		$feedback
			.text( msg )
			.attr( 'class', 'smm-custom-html-feedback smm-feedback smm-feedback--' + type );

		if ( type !== 'saving' ) {
			clearTimeout( $feedback.data( 'timer' ) );
			$feedback.data(
				'timer',
				setTimeout( function () {
					$feedback.text( '' ).attr( 'class', 'smm-custom-html-feedback smm-feedback' );
				}, 3500 )
			);
		}
	}

} )( jQuery );
