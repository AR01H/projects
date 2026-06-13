<?php
/**
 * components/sections/page_hero.php  ← ONE hero for every page.
 *
 * Full-bleed background image (via page_hero_bg_banner) with a left→right
 * gradient fade. Breadcrumb, eyebrow, title, description, and an optional
 * bottom bar (trust items OR stats) render on top at z-index 2.
 *
 * Props:
 *   $hero  array {
 *     eyebrow     string   optional tag/label above the title
 *     title       string   h1 text
 *     description string   paragraph
 *     trust_items array    string[]  OR  [{icon, title/label, subtitle/note}]
 *     stats       array    [{value, label}]  — merged with top-level $stats
 *   }
 *   $breadcrumb  array  [ { label, url } ]  — optional
 *   $stats       array  [ { value, label } ] — alternative way to pass stats
 *
 * Usage:
 *   adn_component( 'sections/page_hero', array(
 *       'hero'       => $ctx['hero'],
 *       'breadcrumb' => $ctx['breadcrumb'],
 *   ) );
 */

defined( 'ABSPATH' ) || exit;

$hero       = isset( $hero )       && is_array( $hero )       ? $hero       : array();
$breadcrumb = isset( $breadcrumb ) && is_array( $breadcrumb ) ? $breadcrumb : array();

// Stats: accept from hero['stats'] or top-level $stats prop.
$_stats = array_merge(
	isset( $hero['stats'] ) && is_array( $hero['stats'] ) ? (array) $hero['stats'] : array(),
	isset( $stats )         && is_array( $stats )         ? (array) $stats         : array()
);
$_trust = isset( $hero['trust_items'] ) && is_array( $hero['trust_items'] )
	? (array) $hero['trust_items']
	: array();

$_default_img  = get_template_directory_uri() . '/assets/images/backgrounds/home_hero.jpg';
// hero['image_id'] wins (dynamic parent-term pages); else WP featured image; else default.
$_hero_img_id  = ! empty( $hero['image_id'] ) ? (int) $hero['image_id'] : 0;
$_hero_img     = $_hero_img_id
	? ( wp_get_attachment_image_url( $_hero_img_id, 'large' ) ?: $_default_img )
	: ( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: $_default_img );
?>
<section class="page-hero">

	<?php adn_component( 'sections/page_hero_bg_banner', array( 'hero_img' => $_hero_img ) ); ?>

	<div class="container">
		<div class="page-hero-content">

			<?php /* Breadcrumb */ ?>
			<?php if ( ! empty( $breadcrumb ) ) : ?>
				<nav class="hero-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', ADN_TEXT_DOMAIN ); ?>">
					<?php foreach ( $breadcrumb as $_i => $_crumb ) :
						$_bc_label = isset( $_crumb['label'] ) ? (string) $_crumb['label'] : '';
						$_bc_url   = isset( $_crumb['url'] )   ? $_crumb['url']            : null;
						$_bc_last  = ( $_i === count( $breadcrumb ) - 1 );
					?>
						<?php if ( ! $_bc_last && null !== $_bc_url ) : ?>
							<a href="<?php echo esc_url( adn_link( $_bc_url ) ); ?>" class="hero-bc-item"><?php echo esc_html( $_bc_label ); ?></a>
							<span class="hero-bc-sep" aria-hidden="true">›</span>
						<?php else : ?>
							<span class="hero-bc-item hero-bc-active"<?php echo $_bc_last ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $_bc_label ); ?></span>
						<?php endif; ?>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

			<?php /* Eyebrow */ ?>
			<?php if ( ! empty( $hero['eyebrow'] ) ) : ?>
				<div class="hero-eyebrow"><?php echo esc_html( $hero['eyebrow'] ); ?></div>
			<?php endif; ?>

			<?php /* Title */ ?>
			<?php if ( ! empty( $hero['title'] ) ) : ?>
				<h1><?php echo esc_html( $hero['title'] ); ?></h1>
			<?php endif; ?>

			<?php /* Description */ ?>
			<?php if ( ! empty( $hero['description'] ) ) : ?>
				<p><?php echo esc_html( $hero['description'] ); ?></p>
			<?php endif; ?>

		</div>
	</div>

	<?php /* ── Bottom bar: stats take priority; else trust items ── */ ?>

	<?php if ( ! empty( $_stats ) ) : ?>
		<div class="page-hero-bar">
			<div class="container">
				<div class="page-hero-bar-inner">
					<?php foreach ( $_stats as $_s ) : ?>
						<div class="phb-stat-item">
							<strong class="phb-stat-value"><?php echo esc_html( isset( $_s['value'] ) ? (string) $_s['value'] : '' ); ?></strong>
							<span  class="phb-stat-label"><?php echo esc_html( isset( $_s['label'] ) ? (string) $_s['label'] : '' ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

	<?php elseif ( ! empty( $_trust ) ) :
		// Detect format from first item.
		$_first = reset( $_trust );
		$_is_string  = is_string( $_first );
		$_is_icon    = ! $_is_string && is_array( $_first ) && isset( $_first['icon'] );
	?>
		<div class="page-hero-bar">
			<div class="container">
				<div class="page-hero-bar-inner">
					<?php foreach ( $_trust as $_t ) :
						if ( $_is_string ) : ?>
							<div class="phb-trust-simple">
								<span class="phb-trust-check" aria-hidden="true">✓</span>
								<?php echo esc_html( (string) $_t ); ?>
							</div>
						<?php elseif ( $_is_icon ) :
							$_ti = adn_icon( isset( $_t['icon'] ) ? (string) $_t['icon'] : '' );
							// Support both label/title and note/subtitle key names.
							$_tp = esc_html( isset( $_t['label'] )    ? (string) $_t['label']    : ( isset( $_t['title'] )    ? (string) $_t['title']    : '' ) );
							$_ts = esc_html( isset( $_t['note'] )     ? (string) $_t['note']     : ( isset( $_t['subtitle'] ) ? (string) $_t['subtitle'] : '' ) );
						?>
							<div class="phb-trust-icon-item">
								<span class="phb-trust-icon" aria-hidden="true"><?php echo $_ti; ?></span>
								<div>
									<strong><?php echo $_tp; ?></strong>
									<?php if ( '' !== $_ts ) : ?>
										<span><?php echo $_ts; ?></span>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

</section>
