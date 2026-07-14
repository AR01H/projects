<?php
/**
 * components/sections/category_hero.php - Category page hero, full-bleed image.
 *
 * Image covers the entire section. A left→right gradient fade keeps the left
 * side readable. Breadcrumb sits at the top of the content, inside the banner.
 *
 * Props:
 *   $hero        array  { title, description, trust_items[] }
 *   $breadcrumb  array  [ { label, url } ] - optional; omit to hide
 */

defined( 'ABSPATH' ) || exit;

$hero        = isset( $hero ) && is_array( $hero ) ? $hero : array();
$trust_items = isset( $hero['trust_items'] ) ? (array) $hero['trust_items'] : array();
$actions     = isset( $hero['actions'] ) ? (array) $hero['actions'] : array();
$breadcrumb  = isset( $breadcrumb ) && is_array( $breadcrumb ) ? $breadcrumb : array();

$_default_img = get_template_directory_uri() . THEME_DEFAULT_HERO_IMG;
$_hero_img_id = isset( $hero['image_id'] ) && ! empty( $hero['image_id'] ) ? (int) $hero['image_id'] : 0;
$_hero_img    = $_hero_img_id
	? wp_get_attachment_image_url( $_hero_img_id, 'large' )
	: $_default_img;
$_hero_img    = adn_versioned_url( $_hero_img );
?>

<?php adn_component( 'sections/page_hero_bg_banner', array( 'hero_img' => $_hero_img ) ); ?>

<?php /* Text content - sits above the overlay at z-index 2 */ ?>
<div class="container">
	<div class="category-hero-content">

		<?php /* Breadcrumb inside the banner */ ?>
		<?php if ( ! empty( $breadcrumb ) ) : ?>
			<nav class="hero-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', ADN_TEXT_DOMAIN ); ?>">
				<?php foreach ( $breadcrumb as $i => $crumb ) :
					$_bc_label  = isset( $crumb['label'] ) ? (string) $crumb['label'] : '';
					$_bc_url    = isset( $crumb['url'] ) ? $crumb['url'] : null;
					$_bc_last   = ( $i === count( $breadcrumb ) - 1 );
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

		<h1><?php echo esc_html( isset( $hero['title'] ) ? $hero['title'] : '' ); ?></h1>

		<?php if ( ! empty( $hero['description'] ) ) : ?>
			<p><?php echo esc_html( $hero['description'] ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $actions ) ) : ?>
		<div class="hero-actions">
			<?php foreach ( $actions as $action ) :
				$style = isset( $action['style'] ) && 'outline' === $action['style'] ? 'btn-outline premium-btn-outline' : 'btn-primary premium-btn-dark';
				$label = isset( $action['label'] ) ? $action['label'] : '';
			?>
				<a href="<?php echo esc_url( adn_link( isset( $action['url'] ) ? $action['url'] : '' ) ); ?>"
				   class="btn <?php echo esc_attr( $style ); ?> btn-md">
				   <?php echo esc_html( $label ); ?>
				   <?php if ( 'outline' === ( $action['style'] ?? '' ) ) echo ' &rarr;'; ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php if ( $trust_items ) : ?>
			<div class="trust-bar">
				<?php foreach ( $trust_items as $item ) : ?>
					<div class="trust-bar-item">
						<span class="trust-bar-icon">&#x2713;</span>
						<?php echo esc_html( $item ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</div>
