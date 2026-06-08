<?php
/**
 * Channel avatar + name overlay for a mini-video card.
 * Expects $c (the current card array) to be in scope (required from carousel_mini_video_scroll.php).
 */
defined( 'ABSPATH' ) || exit;

if ( empty( $c['channel'] ) && empty( $c['avatar'] ) ) return;
?>
<span class="ch-mvs-channel">
	<?php if ( ! empty( $c['avatar'] ) ) : ?>
		<img class="ch-mvs-avatar" src="<?php echo esc_url( $c['avatar'] ); ?>" alt="" loading="lazy">
	<?php endif; ?>
	<span class="ch-mvs-channel-text">
		<?php if ( ! empty( $c['channel'] ) ) : ?><strong><?php echo esc_html( $c['channel'] ); ?></strong><?php endif; ?>
		<?php if ( ! empty( $c['handle'] ) ) : ?><span><?php echo esc_html( $c['handle'] ); ?></span><?php endif; ?>
	</span>
</span>
