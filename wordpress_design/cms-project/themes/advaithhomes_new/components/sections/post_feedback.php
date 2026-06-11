<?php
/**
 * components/sections/post_feedback.php
 *
 * Article feedback row: "Was this helpful?" buttons + social share links.
 *
 * Props (via extract):
 *   $share = [
 *       'url'   => string,  canonical post URL (esc'd before passing to share links)
 *       'title' => string,  post title for Twitter/WhatsApp text
 *   ]
 */

defined( 'ABSPATH' ) || exit;

$_share = isset( $share ) ? (array) $share : array();
$_url   = esc_url( isset( $_share['url'] ) ? (string) $_share['url'] : '' );
$_title = rawurlencode( isset( $_share['title'] ) ? (string) $_share['title'] : '' );

$_fb  = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $_url );
$_tw  = 'https://twitter.com/intent/tweet?url=' . rawurlencode( $_url ) . '&text=' . $_title;
$_li  = 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $_url );
$_wa  = 'https://wa.me/?text=' . $_title . '%20' . rawurlencode( $_url );
?>
<div class="article-feedback-row">

	<div class="feedback-question">
		<span><?php esc_html_e( 'Was this guide helpful?', ADN_TEXT_DOMAIN ); ?></span>
		<button class="feedback-btn" type="button" data-feedback="yes" aria-pressed="false">
			👍 <?php esc_html_e( 'Yes', ADN_TEXT_DOMAIN ); ?>
		</button>
		<button class="feedback-btn" type="button" data-feedback="no" aria-pressed="false">
			👎 <?php esc_html_e( 'No', ADN_TEXT_DOMAIN ); ?>
		</button>
	</div>

	<?php if ( '' !== $_url ) : ?>
	<div class="share-guide">
		<span><?php esc_html_e( 'Share this guide', ADN_TEXT_DOMAIN ); ?></span>
		<div class="share-btns">
			<a href="<?php echo esc_url( $_fb ); ?>" class="share-btn share-fb" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on Facebook', ADN_TEXT_DOMAIN ); ?>">f</a>
			<a href="<?php echo esc_url( $_tw ); ?>" class="share-btn share-tw" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on Twitter / X', ADN_TEXT_DOMAIN ); ?>">𝕏</a>
			<a href="<?php echo esc_url( $_li ); ?>" class="share-btn share-li" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on LinkedIn', ADN_TEXT_DOMAIN ); ?>">in</a>
			<a href="<?php echo esc_url( $_wa ); ?>" class="share-btn share-wa" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on WhatsApp', ADN_TEXT_DOMAIN ); ?>">💬</a>
		</div>
	</div>
	<?php endif; ?>

</div>
