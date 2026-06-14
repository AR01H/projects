<?php
/**
 * components/parts/share_bar.php - Share this page: Copy link, WhatsApp, X/Twitter, Email.
 *
 * Props: $share { url, title }
 * Usage: adn_component( 'parts/share_bar', array( 'share' => array( 'url' => '...', 'title' => '...' ) ) );
 */

defined( 'ABSPATH' ) || exit;

$share = isset( $share ) && is_array( $share ) ? $share : array();
$url   = ! empty( $share['url'] )   ? (string) $share['url']   : home_url( '/' );
$title = ! empty( $share['title'] ) ? (string) $share['title'] : get_bloginfo( 'name' );

$wa_url    = 'https://wa.me/?text='                                        . rawurlencode( $title . ' ' . $url );
$x_url     = 'https://twitter.com/intent/tweet?url='                      . rawurlencode( $url ) . '&text=' . rawurlencode( $title );
$email_url = 'mailto:?subject=' . rawurlencode( $title ) . '&body=' . rawurlencode( $url );
?>
<div class="share-bar">
	<span class="share-bar-label"><?php esc_html_e( 'Share:', ADN_TEXT_DOMAIN ); ?></span>

	<button class="share-bar-btn share-bar-btn--copy" type="button"
	        data-copy-url="<?php echo esc_attr( $url ); ?>"
	        aria-label="<?php esc_attr_e( 'Copy link', ADN_TEXT_DOMAIN ); ?>">
		<span class="share-bar-icon" aria-hidden="true">🔗</span>
		<span class="share-bar-text"><?php esc_html_e( 'Copy link', ADN_TEXT_DOMAIN ); ?></span>
	</button>

	<a href="<?php echo esc_url( $wa_url ); ?>"
	   class="share-bar-btn share-bar-btn--whatsapp"
	   target="_blank" rel="noopener noreferrer"
	   aria-label="<?php esc_attr_e( 'Share on WhatsApp', ADN_TEXT_DOMAIN ); ?>">
		<span class="share-bar-icon" aria-hidden="true">📱</span>
		<span class="share-bar-text"><?php esc_html_e( 'WhatsApp', ADN_TEXT_DOMAIN ); ?></span>
	</a>

	<a href="<?php echo esc_url( $x_url ); ?>"
	   class="share-bar-btn share-bar-btn--x"
	   target="_blank" rel="noopener noreferrer"
	   aria-label="<?php esc_attr_e( 'Share on X / Twitter', ADN_TEXT_DOMAIN ); ?>">
		<span class="share-bar-icon share-bar-icon--x" aria-hidden="true">✕</span>
		<span class="share-bar-text"><?php esc_html_e( 'X / Twitter', ADN_TEXT_DOMAIN ); ?></span>
	</a>

	<a href="<?php echo esc_url( $email_url ); ?>"
	   class="share-bar-btn share-bar-btn--email"
	   aria-label="<?php esc_attr_e( 'Share by email', ADN_TEXT_DOMAIN ); ?>">
		<span class="share-bar-icon" aria-hidden="true">✉</span>
		<span class="share-bar-text"><?php esc_html_e( 'Email', ADN_TEXT_DOMAIN ); ?></span>
	</a>

	<span class="share-bar-copied" role="status" hidden><?php esc_html_e( '✓ Copied!', ADN_TEXT_DOMAIN ); ?></span>
</div>
<script>
(function(){
	'use strict';
	var btn = document.querySelector('.share-bar-btn--copy');
	if (!btn) return;
	btn.addEventListener('click', function(){
		var url = btn.getAttribute('data-copy-url');
		var bar = btn.closest('.share-bar');
		var msg = bar ? bar.querySelector('.share-bar-copied') : null;
		function _done(){
			if (!msg) return;
			msg.removeAttribute('hidden');
			setTimeout(function(){ msg.setAttribute('hidden', ''); }, 2500);
		}
		if (navigator.clipboard) {
			navigator.clipboard.writeText(url).then(_done).catch(function(){});
		} else {
			var ta = document.createElement('textarea');
			ta.value = url;
			ta.style.cssText = 'position:fixed;opacity:0;pointer-events:none;';
			document.body.appendChild(ta);
			ta.select();
			try { document.execCommand('copy'); _done(); } catch(e){}
			document.body.removeChild(ta);
		}
	});
}());
</script>
