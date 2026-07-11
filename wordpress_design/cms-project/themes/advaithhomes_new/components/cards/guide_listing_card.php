<?php
/**
 * components/cards/guide_listing_card.php - Guide / article listing card.
 *
 * Props: $item { img_class, icon, thumbnail, category, title, desc, date, read_time, url }
 */

defined( 'ABSPATH' ) || exit;

$item = isset( $item ) && is_array( $item ) ? $item : array();
$url  = esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) );
?>
<a href="<?php echo $url; ?>" class="glc">

	<?php /* ── Image / gradient area ── */ ?>
	<div class="glc-img">
		<?php
		$thumbnail = ! empty( $item['thumbnail'] ) ? (string) $item['thumbnail'] : '';
		if ( empty( $thumbnail ) ) {
			$thumbnail = get_template_directory_uri() . THEME_DEFAULT_TOPIC_IMG . '?v=' . LOCAL_CACHE_VERSION;
		}
		?>
		<img src="<?php echo esc_url( $thumbnail ); ?>"
		     alt="<?php echo esc_attr( isset( $item['title'] ) ? $item['title'] : '' ); ?>"
		     loading="lazy" class="glc-img-photo"
		     onerror="this.onerror=null;this.src='<?php echo esc_url( get_template_directory_uri() . THEME_DEFAULT_TOPIC_IMG . '?v=' . LOCAL_CACHE_VERSION ); ?>';" />

		<?php /* Category tag overlaid on image */ ?>
		<?php if ( ! empty( $item['category'] ) ) : ?>
			<span class="glc-cat"><?php echo esc_html( $item['category'] ); ?></span>
		<?php endif; ?>
	</div>

	<?php /* ── Body ── */ ?>
	<div class="glc-body">
		<?php if ( ! empty( $item['title'] ) ) : ?>
			<p class="glc-title"><?php echo esc_html( $item['title'] ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $item['desc'] ) ) : ?>
			<p class="glc-desc"><?php echo esc_html( $item['desc'] ); ?></p>
		<?php endif; ?>

		<div class="glc-meta">
			<?php if ( ! empty( $item['read_time'] ) ) : ?>
				<span class="glc-read">
					<svg class="glc-read-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.4"/><path d="M8 5v3.2l2 1.3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
					<?php echo esc_html( $item['read_time'] ); ?>
				</span>
			<?php endif; ?>
			<span class="glc-arrow">Read &rarr;</span>
		</div>
	</div>

</a>
