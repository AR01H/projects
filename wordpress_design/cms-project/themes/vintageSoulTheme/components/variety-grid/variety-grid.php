<?php
/**
 * VintageSoulTheme - Reusable Variety Specimen Grid Component
 *
 * Renders heirloom varietal showcase cards with images, badges,
 * origin pins, Brix gauges, and modal story data-attributes.
 *
 * Props:
 *   items (array) - Array of { name, species, origin, brix, profile, badge, image } objects
 */

use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) ? (array) $items : array();

if ( empty( $items ) ) {
	return;
}
?>
<div class="varieties-showcase-grid">
	<?php foreach ( $items as $v_item ) :
		$v_name    = (string) ( $v_item['name'] ?? '' );
		$v_species = (string) ( $v_item['species'] ?? '' );
		$v_origin  = (string) ( $v_item['origin'] ?? '' );
		$v_brix    = (string) ( $v_item['brix'] ?? '' );
		$v_prof    = (string) ( $v_item['profile'] ?? '' );
		$v_badge   = (string) ( $v_item['badge'] ?? '' );
		$v_img     = UrlHelper::resolve( (string) ( $v_item['image'] ?? '' ) );
	?>
		<div class="variety-specimen-card frame--ornate" 
			 tabindex="0" 
			 role="button" 
			 aria-haspopup="dialog"
			 aria-label="<?php echo esc_attr( $v_name ); ?>"
			 data-story-modal="true"
			 data-story-title="<?php echo esc_attr( $v_name . ' (' . $v_species . ')' ); ?>"
			 data-story-badge="<?php echo esc_attr( $v_badge ); ?>"
			 data-story-meta="<?php echo esc_attr( $v_origin . ' • ' . $v_brix ); ?>"
			 data-story-quote="<?php echo esc_attr( $v_prof ); ?>"
			 data-story-image="<?php echo esc_url( $v_img ); ?>">
			<div class="variety-specimen-card__media">
				<img src="<?php echo esc_url( $v_img ); ?>" alt="<?php echo esc_attr( $v_name ); ?>" loading="lazy">
				<?php if ( '' !== $v_badge ) : ?>
					<span class="variety-specimen-card__badge"><?php echo esc_html( $v_badge ); ?></span>
				<?php endif; ?>
			</div>
			<div class="variety-specimen-card__body">
				<?php if ( '' !== $v_origin ) : ?>
					<span class="variety-specimen-card__origin">📍 <?php echo esc_html( $v_origin ); ?></span>
				<?php endif; ?>
				<h3 class="variety-specimen-card__title"><?php echo esc_html( $v_name ); ?></h3>
				<?php if ( '' !== $v_species ) : ?>
					<span class="variety-specimen-card__species"><em><?php echo esc_html( $v_species ); ?></em></span>
				<?php endif; ?>
				<?php if ( '' !== $v_brix ) : ?>
					<div class="variety-specimen-card__brix-gauge">
						<span class="brix-gauge__label">Natural Sweetness:</span>
						<span class="brix-gauge__val">⚡ <?php echo esc_html( $v_brix ); ?></span>
					</div>
				<?php endif; ?>
				<?php if ( '' !== $v_prof ) : ?>
					<p class="variety-specimen-card__desc"><?php echo esc_html( $v_prof ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
