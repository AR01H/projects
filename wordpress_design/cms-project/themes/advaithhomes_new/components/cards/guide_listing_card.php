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
		<?php if ( ! empty( $item['thumbnail'] ) ) : ?>
			<img src="<?php echo esc_url( $item['thumbnail'] ); ?>"
			     alt="<?php echo esc_attr( isset( $item['title'] ) ? $item['title'] : '' ); ?>"
			     loading="lazy" class="glc-img-photo">
		<?php else : ?>
			<?php
			$_img_class = isset( $item['img_class'] ) ? ' ' . sanitize_html_class( $item['img_class'] ) : '';
			$_icon      = isset( $item['icon'] ) ? (string) $item['icon'] : '';
			?>
			<div class="glc-img-gradient<?php echo $_img_class; ?>">
				<?php if ( '' !== $_icon ) : ?>
					<span class="glc-img-icon" aria-hidden="true"><?php echo adn_icon( $_icon ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php /* Category tag overlaid on image */ ?>
		<?php if ( ! empty( $item['category'] ) ) : ?>
			<span class="glc-cat"><?php echo esc_html( $item['category'] ); ?></span>
		<?php endif; ?>

		<?php /* Bookmark button */ ?>
		<button class="glc-bookmark" type="button" onclick="event.preventDefault();" aria-label="<?php esc_attr_e( 'Bookmark', ADN_TEXT_DOMAIN ); ?>">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
			</svg>
		</button>
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
			<?php if ( ! empty( $item['date'] ) ) : ?>
				<span class="glc-date"><?php echo esc_html( $item['date'] ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $item['read_time'] ) ) : ?>
				<span class="glc-read"><?php echo esc_html( $item['read_time'] ); ?></span>
			<?php endif; ?>
		</div>
	</div>

</a>
