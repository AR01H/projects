<?php
/**
 * components/parts/hero_share.php
 *
 * Renders the floating share buttons (native, Twitter, LinkedIn, WhatsApp)
 *
 * Props:
 *   $share  array { url, title }
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $share ) ) {
	return;
}

$_url   = esc_url( isset( $share['url'] ) ? (string) $share['url'] : '' );
$_title = rawurlencode( isset( $share['title'] ) ? (string) $share['title'] : '' );
$_tw    = 'https://twitter.com/intent/tweet?url=' . rawurlencode( $_url ) . '&text=' . $_title;
$_li    = 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $_url );
$_wa    = 'https://wa.me/?text=' . $_title . '%20' . rawurlencode( $_url );
?>
<div class="hero-share-corner" style="padding: 6px; border-radius: 50%; gap: 0;">
	<div class="hero-share-btns">
		<button type="button" class="pf-share-btn pf-share-native" aria-label="Share options" data-title="<?php echo esc_attr( isset( $share['title'] ) ? (string) $share['title'] : '' ); ?>" data-url="<?php echo esc_url( $_url ); ?>" onclick="if(navigator.share){navigator.share({title:this.dataset.title, url:this.dataset.url}).catch(console.error);}else{navigator.clipboard.writeText(this.dataset.url); alert('Link copied to clipboard!');}">
			<?php echo file_get_contents( get_theme_file_path( 'assets/images/icons/share.svg' ) ); ?>
		</button>
	</div>
</div>
