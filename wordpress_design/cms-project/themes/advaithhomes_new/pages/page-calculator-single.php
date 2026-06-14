<?php
/**
 * pages/page-calculator-single.php - Individual calculator detail page.
 *
 * Served by template_redirect when ?ah_calc_page=KEY is present.
 * Renders the full WP page (header + footer) with the calculator widget,
 * guide link, help text, related calculators, and the calcs sidebar.
 *
 * RULE: No hardcoded content — only structure.
 */

defined( 'ABSPATH' ) || exit;

$_calc_key = sanitize_key( wp_unslash( isset( $_GET['ah_calc_page'] ) ? $_GET['ah_calc_page'] : '' ) );

require_once ADN_THEME_DIR . '/intermediate/page_calculator_single_logical.php';
$ctx = adn_calculator_single_get_context( $_calc_key );

if ( ! $ctx ) {
	status_header( 404 );
	$_404 = get_404_template();
	if ( $_404 ) {
		include $_404;
	} else {
		wp_die( esc_html__( 'Calculator not found.', ADN_TEXT_DOMAIN ), '', array( 'response' => 404 ) );
	}
	return;
}

// Breadcrumb renders inside the hero — suppress from adn_page_open.
$_open_ctx               = $ctx;
$_open_ctx['breadcrumb'] = array();
adn_page_open( $_open_ctx );
?>

<?php /* ============================== HERO ============================== */ ?>
<?php if ( ! empty( $ctx['hero'] ) ) : ?>
	<?php adn_component( 'sections/page_hero', array(
		'hero'       => $ctx['hero'],
		'breadcrumb' => $ctx['breadcrumb'],
	) ); ?>
<?php endif; ?>

<?php /* ============================== MAIN LAYOUT: CONTENT + SIDEBAR ============================== */ ?>
<div class="container">
	<div class="page-with-sidebar calc-single-layout">

		<main>

			<?php /* ── Highlight badge strip ── */ ?>
			<?php if ( ! empty( $ctx['highlight'] ) || ! empty( $ctx['thumbnail_url'] ) ) : ?>
			<div class="calc-single-meta">
				<?php if ( ! empty( $ctx['thumbnail_url'] ) ) : ?>
					<img src="<?php echo esc_url( $ctx['thumbnail_url'] ); ?>" alt="<?php echo esc_attr( $ctx['title'] ); ?>" class="calc-single-thumb">
				<?php endif; ?>
				<?php if ( ! empty( $ctx['highlight'] ) ) : ?>
					<span class="calc-badge calc-badge--large"><?php echo esc_html( $ctx['highlight'] ); ?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php /* ── Calculator widget ── */ ?>
			<section class="calc-single-widget">
				<?php echo do_shortcode( '[ah_calculator key="' . esc_attr( $ctx['key'] ) . '"]' ); ?>
			</section>

			<?php /* ── Share bar ── */ ?>
			<?php if ( ! empty( $ctx['share'] ) ) : ?>
				<?php adn_component( 'parts/share_bar', array( 'share' => $ctx['share'] ) ); ?>
			<?php endif; ?>

			<?php /* ── Help text (shown below the calculator) ── */ ?>
			<?php if ( ! empty( $ctx['help_text'] ) ) : ?>
			<div class="calc-single-help">
				<p><?php echo esc_html( $ctx['help_text'] ); ?></p>
			</div>
			<?php endif; ?>

			<?php /* ── Guide link ── */ ?>
			<?php if ( ! empty( $ctx['guide']['url'] ) ) : ?>
			<div class="calc-single-guide-link">
				<a href="<?php echo esc_url( $ctx['guide']['url'] ); ?>" class="btn btn-outline">
					<?php echo esc_html( $ctx['guide']['label'] ); ?>
				</a>
			</div>
			<?php endif; ?>

			<?php /* ── Related calculators ── */ ?>
			<?php if ( ! empty( $ctx['related'] ) ) : ?>
			<section class="category-section category-calculators">
				<h3 class="calc-single-section-title">
					<?php esc_html_e( 'Related Calculators', ADN_TEXT_DOMAIN ); ?>
				</h3>
				<div class="calc-grid calc-grid--7col">
					<?php foreach ( $ctx['related'] as $card ) : ?>
						<?php adn_component( 'cards/calc_card', array( 'card' => $card ) ); ?>
					<?php endforeach; ?>
				</div>
			</section>
			<?php endif; ?>

			<?php /* ── Latest news ── */ ?>
			<?php if ( ! empty( $ctx['news'] ) ) : ?>
			<section class="category-section category-news mini_card_container_design">
				<h3 class="calc-single-section-title">
					<?php esc_html_e( 'Latest News', ADN_TEXT_DOMAIN ); ?>
				</h3>
				<?php foreach ( $ctx['news'] as $item ) :
					adn_component( 'cards/news_item', array( 'item' => $item ) );
				endforeach; ?>
			</section>
			<?php endif; ?>

		</main>

		<aside class="sidebar-col">

			<?php /* ── Browse calculator categories ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['categories'] ) ) : ?>
				<?php adn_component( 'parts/calcs_sidebar', array( 'sidebar' => array(
					'categories' => $ctx['sidebar']['categories'],
					'help'       => array(),
				) ) ); ?>
			<?php endif; ?>

			<?php /* ── Expert Help / Contact ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['expert_help'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_expert_help', array( 'expert_help' => $ctx['sidebar']['expert_help'] ) ); ?>
			<?php endif; ?>

			<?php /* ── Latest news mini ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['news_mini']['items'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_news_mini', array( 'news_mini' => $ctx['sidebar']['news_mini'] ) ); ?>
			<?php endif; ?>

		</aside>

	</div>
</div>

<?php adn_page_close( $ctx ); ?>
