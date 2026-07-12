<?php
/**
 * components/sections/post_feedback.php
 *
 * Article footer row: helpful counter + social share links.
 *
 * Props (via extract):
 *   $share        = [ 'url' => string, 'title' => string ]
 *   $hide_helpful = bool  - when true, omits the helpful counter (e.g. on newsbar single views)
 */

defined( 'ABSPATH' ) || exit;

$_share        = isset( $share ) ? (array) $share : array();
$_url          = esc_url( isset( $_share['url'] ) ? (string) $_share['url'] : '' );
$_title        = rawurlencode( isset( $_share['title'] ) ? (string) $_share['title'] : '' );
$_post         = get_the_ID();
$_count        = max( 0, (int) get_post_meta( $_post, '_adn_helpful_count', true ) );
$_hide_helpful = ! empty( $hide_helpful );

$_tw = 'https://twitter.com/intent/tweet?url=' . rawurlencode( $_url ) . '&text=' . $_title;
$_li = 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $_url );
$_wa = 'https://wa.me/?text=' . $_title . '%20' . rawurlencode( $_url );
$_em = 'mailto:?subject=' . $_title . '&body=' . rawurlencode( $_url );
?>
<div class="post-feedback-bar">

	<?php if ( ! $_hide_helpful ) : ?>
	<button class="pf-helpful-btn"
	        type="button"
	        id="pf-helpful-<?php echo esc_attr( (string) $_post ); ?>"
	        data-post="<?php echo esc_attr( (string) $_post ); ?>"
	        data-nonce="<?php echo esc_attr( wp_create_nonce( 'adn_post_helpful' ) ); ?>"
	        data-liked="0"
	        aria-pressed="false">
		<svg class="pf-heart" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
		</svg>
		<span class="pf-helpful-label"><?php esc_html_e( 'Helpful', ADN_TEXT_DOMAIN ); ?></span>
		<span class="pf-helpful-count"><?php echo esc_html( number_format_i18n( $_count ) ); ?></span>
	</button>
	<?php endif; ?>

	<?php if ( '' !== $_url ) : ?>
	<div class="pf-share">
		<span class="pf-share-label"><?php esc_html_e( 'Share', ADN_TEXT_DOMAIN ); ?></span>
		<div class="pf-share-btns">
			<a href="<?php echo esc_url( $_tw ); ?>" class="pf-share-btn pf-share-tw" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on X / Twitter', ADN_TEXT_DOMAIN ); ?>">
				<?php echo file_get_contents( get_theme_file_path( 'assets/images/icons/twitter.svg' ) ); ?>
			</a>
			<a href="<?php echo esc_url( $_li ); ?>" class="pf-share-btn pf-share-li" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on LinkedIn', ADN_TEXT_DOMAIN ); ?>">
				<?php echo file_get_contents( get_theme_file_path( 'assets/images/icons/linkedin.svg' ) ); ?>
			</a>
			<a href="<?php echo esc_url( $_wa ); ?>" class="pf-share-btn pf-share-wa" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on WhatsApp', ADN_TEXT_DOMAIN ); ?>">
				<?php echo file_get_contents( get_theme_file_path( 'assets/images/icons/whatsapp.svg' ) ); ?>
			</a>
			<a href="<?php echo esc_attr( $_em ); ?>" class="pf-share-btn pf-share-em" aria-label="<?php esc_attr_e( 'Share via Email', ADN_TEXT_DOMAIN ); ?>">
				<?php echo file_get_contents( get_theme_file_path( 'assets/images/icons/email.svg' ) ); ?>
			</a>
			<button type="button"
			        class="pf-share-btn pf-share-native"
			        id="pf-native-share-<?php echo esc_attr( (string) $_post ); ?>"
			        style="display:none"
			        data-url="<?php echo esc_attr( $_url ); ?>"
			        data-title="<?php echo esc_attr( isset( $share['title'] ) ? (string) $share['title'] : '' ); ?>"
			        aria-label="<?php esc_attr_e( 'Share', ADN_TEXT_DOMAIN ); ?>">
				<?php echo file_get_contents( get_theme_file_path( 'assets/images/icons/share.svg' ) ); ?>
			</button>
		</div>
	</div>
	<?php endif; ?>

</div>
<script>
(function () {
	'use strict';

	<?php if ( ! $_hide_helpful ) : ?>
	var helpBtn = document.getElementById( 'pf-helpful-<?php echo esc_js( (string) $_post ); ?>' );
	if ( helpBtn ) {
		var stored = sessionStorage.getItem( 'adn_helpful_<?php echo esc_js( (string) $_post ); ?>' );
		if ( stored === '1' ) {
			helpBtn.dataset.liked = '1';
			helpBtn.classList.add( 'pf-helpful-btn--active' );
			helpBtn.setAttribute( 'aria-pressed', 'true' );
		}
		helpBtn.addEventListener( 'click', function () {
			var liked = helpBtn.dataset.liked === '1';
			helpBtn.disabled = true;
			var fd = new FormData();
			fd.append( 'action',  'adn_post_helpful' );
			fd.append( 'nonce',   helpBtn.dataset.nonce );
			fd.append( 'post_id', helpBtn.dataset.post );
			fd.append( 'liked',   liked ? '1' : '0' );
			fetch( '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', { method: 'POST', body: fd } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( d ) {
					if ( d.success ) {
						var nowLiked = d.data.liked;
						helpBtn.dataset.liked = nowLiked ? '1' : '0';
						helpBtn.classList.toggle( 'pf-helpful-btn--active', nowLiked );
						helpBtn.setAttribute( 'aria-pressed', nowLiked ? 'true' : 'false' );
						helpBtn.querySelector( '.pf-helpful-count' ).textContent = d.data.count;
						sessionStorage.setItem( 'adn_helpful_<?php echo esc_js( (string) $_post ); ?>', nowLiked ? '1' : '0' );
					}
					helpBtn.disabled = false;
				} )
				.catch( function () { helpBtn.disabled = false; } );
		} );
	}
	<?php endif; ?>

	var nsBtn = document.getElementById( 'pf-native-share-<?php echo esc_js( (string) $_post ); ?>' );
	if ( nsBtn && navigator.share ) {
		nsBtn.style.display = '';
		nsBtn.addEventListener( 'click', function () {
			navigator.share( {
				title: nsBtn.dataset.title || document.title,
				url:   nsBtn.dataset.url   || location.href,
			} ).catch( function () {} );
		} );
	}
}());
</script>
