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
 *     stats       array    [{value, label}]  - merged with top-level $stats
 *   }
 *   $breadcrumb  array  [ { label, url } ]  - optional
 *   $stats       array  [ { value, label } ] - alternative way to pass stats
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

$_default_img  = get_template_directory_uri() . THEME_DEFAULT_HERO_IMG;
// hero['bg_url'] wins; then hero['image_id'] wins; else WP featured image; else default.
$_hero_img_id  = ! empty( $hero['image_id'] ) ? (int) $hero['image_id'] : 0;
$_hero_img     = ! empty( $hero['bg_url'] ) 
	? (string) $hero['bg_url']
	: adn_versioned_url( $_hero_img_id
		? ( wp_get_attachment_image_url( $_hero_img_id, 'large' ) ?: $_default_img )
		: ( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: $_default_img ) );

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

			<?php /* Title */ ?>
			<?php
			$_eyebrow = isset( $hero['eyebrow'] ) && '' !== $hero['eyebrow'] ? $hero['eyebrow'] : ( isset( $hero['subheading'] ) ? $hero['subheading'] : '' );
			if ( '' !== $_eyebrow ) : ?>
				<div class="hero-eyebrow"><?php echo esc_html( $_eyebrow ); ?></div>
			<?php endif; ?>

			<?php if ( ! empty( $hero['title'] ) ) : ?>
				<h1><?php echo wp_kses_post( $hero['title'] ); ?></h1>
			<?php endif; ?>

			<?php /* Description */ ?>
			<?php if ( ! empty( $hero['description'] ) ) : ?>
				<p><?php echo ( $hero['description'] ); ?></p>
			<?php endif; ?>

			<?php adn_component( 'parts/hero_share', array( 'share' => $share ?? null ) ); ?>

		</div>

		<?php if ( is_single() ) : ?>
			<div class="page-hero-nav-cards">
				<?php adn_component( 'parts/hero_nav_cards', array( 'cms_terms' => $cms_terms ?? null, 'sidebar' => $sidebar ?? null, 'breadcrumb' => $breadcrumb ?? null ) ); ?>
			</div>
		<?php elseif ( ! empty( $hero['services'] ) ) : ?>
			<div class="page-hero-services">
				<ul class="phs-list">
					<?php foreach ( $hero['services'] as $svc ) : 
						$icon = ! empty( $svc['icon'] ) ? $svc['icon'] : '<i class="fa-solid fa-chevron-right"></i>';
					?>
						<li>
							<a href="<?php echo esc_url( adn_link( isset( $svc['url'] ) ? $svc['url'] : '' ) ); ?>" class="phs-card">
								<div class="phs-icon">
									<?php echo ( strpos( $icon, '<' ) !== false || strpos( $icon, 'fa-' ) !== false ) ? ( strpos( $icon, '<' ) !== false ? $icon : '<i class="' . esc_attr( $icon ) . '"></i>' ) : '<i class="fa-regular fa-file-lines"></i>'; ?>
								</div>
								<div class="phs-text"><?php echo esc_html( isset( $svc['title'] ) ? $svc['title'] : '' ); ?></div>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php else : ?>
			<div class="page-hero-static-info">
				<?php 
				$_glass_cards = isset( $hero['glass_cards'] ) && is_array( $hero['glass_cards'] ) ? $hero['glass_cards'] : array();
				if ( empty( $_glass_cards ) ) {
					// Fallback defaults ONLY for specific pages
					if ( is_page( array( 'calculators', 'contact', 'ask-an-expert', 'guides' ) ) || is_archive() ) {
						$_glass_cards = array(
							array( 'icon' => 'fa-handshake-angle', 'title' => 'Expert Assistance', 'desc' => 'Connect with vetted professionals to buy, sell, or rent.' ),
							array( 'icon' => 'fa-shield-halved', 'title' => 'Trusted Property Hub', 'desc' => 'Independent guidance, tools, and expert support.' ),
							array( 'icon' => 'fa-book-open', 'title' => 'Comprehensive Insights', 'desc' => 'Stay informed with the latest market trends, news, and guides.' ),
						);
					}
				}
				foreach ( $_glass_cards as $_gc ) :
				?>
				<div class="glass-info-card">
					<div class="gic-icon"><?php echo adn_icon( $_gc['icon'] ); ?></div>
					<div class="gic-content">
						<h4><?php echo esc_html( $_gc['title'] ); ?></h4>
						<p><?php echo esc_html( $_gc['desc'] ); ?></p>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
	
</section>
<?php if ( ! empty( $_trust ) ) :
	$_first     = reset( $_trust );
	$_is_string = is_string( $_first );
	$_is_icon   = ! $_is_string && is_array( $_first ) && isset( $_first['icon'] );

	get_template_part( 'components/marque_scroll/point_marque', null, [
		'trust'     => $_trust,
		'is_string' => $_is_string,
		'is_icon'   => $_is_icon,
	] );
?>
   
<?php endif; ?>
