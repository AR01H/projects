<?php
/**
 * pages/page-tool-single.php - Individual tool detail page.
 *
 * Served by template_redirect when ?ah_calc_page=KEY is present.
 * Renders the full WP page (header + footer) with the tool widget,
 * guide link, help text, related tools, and the tools sidebar.
 *
 * RULE: No hardcoded content - only structure.
 */

defined( 'ABSPATH' ) || exit;

wp_enqueue_style( 'adn-tools-style', get_template_directory_uri() . '/assets/css/tools.css', array(), ADN_THEME_VERSION );
wp_enqueue_style( 'adn-single-style', get_template_directory_uri() . '/assets/css/single.css', array(), ADN_THEME_VERSION );

$_calc_key = sanitize_key( wp_unslash( isset( $_GET['ah_calc_page'] ) ? $_GET['ah_calc_page'] : '' ) );

require_once ADN_THEME_DIR . '/intermediate/page_tool_single_logical.php';
$ctx = adn_calculator_single_get_context( $_calc_key );

if ( ! $ctx ) {
	status_header( 404 );
	$_404 = get_404_template();
	if ( $_404 ) {
		include $_404;
	} else {
		wp_die( esc_html__( 'Tool not found.', ADN_TEXT_DOMAIN ), '', array( 'response' => 404 ) );
	}
	return;
}

// Breadcrumb renders inside the hero - suppress from adn_page_open.
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

<?php /* ── Highlight badge strip ── */ ?>
<?php if ( ! empty( $ctx['highlight'] ) ) : ?>
<div class="tool-highlight-strip">
	<div class="container">
		<span class="tool-badge tool-badge--large"><?php echo esc_html( $ctx['highlight'] ); ?></span>
	</div>
</div>
<?php endif; ?>

<?php /* ============================== MAIN LAYOUT: CONTENT + SIDEBAR ============================== */ ?>
<div class="container">
	<div class="page-with-sidebar tool-single-layout">

		<main>

			<?php /* ── Before-calculator content ── */ ?>
			<?php if ( ! empty( $ctx['before_content'] ) ) : ?>
			<div class="tool-single-page-content tool-single-page-content--before">
				<?php echo wp_kses_post( $ctx['before_content'] ); ?>
			</div>
			<?php endif; ?>

			<?php /* ── Tool widget (standalone iframe embed) ── */ ?>
			<?php echo do_shortcode( '[ah_calculator key="' . esc_attr( $ctx['key'] ) . '"]' ); ?>

			<?php /* ── After-calculator content ── */ ?>
			<?php if ( ! empty( $ctx['after_content'] ) ) : ?>
			<div class="tool-single-page-content tool-single-page-content--after">
				<?php echo wp_kses_post( $ctx['after_content'] ); ?>
			</div>
			<?php endif; ?>

			<?php /* ── Help text (outside iframe) ── */ ?>
			<?php if ( ! empty( $ctx['help_text'] ) ) : ?>
			<div class="tool-single-help">
				<?php echo wpautop( wp_kses_post( $ctx['help_text'] ) ); ?>
			</div>
			<?php endif; ?>

			<?php /* ── Share bar (same UI as single.php, no Helpful button) ── */ ?>
			<?php if ( ! empty( $ctx['share'] ) ) : ?>
				<?php adn_component( 'sections/post_feedback', array( 'share' => $ctx['share'], 'hide_helpful' => true ) ); ?>
			<?php endif; ?>

			<?php /* ── Guide link ── */ ?>
			<?php if ( ! empty( $ctx['guide']['url'] ) ) : ?>
			<div class="tool-single-guide-link">
				<a href="<?php echo esc_url( $ctx['guide']['url'] ); ?>" class="btn btn-outline">
					<?php echo esc_html( $ctx['guide']['label'] ); ?>
				</a>
			</div>
			<?php endif; ?>

			<?php /* ── Related tools ── */ ?>
			<?php if ( ! empty( $ctx['related'] ) ) : ?>
			<section class="category-section category-tools">
				<h3 class="tool-single-section-title">
					<?php esc_html_e( 'Related Tools', ADN_TEXT_DOMAIN ); ?>
				</h3>
				<div class="tool-grid tool-grid--7col">
					<?php foreach ( $ctx['related'] as $card ) : ?>
						<?php adn_component( 'cards/tool_card', array( 'card' => $card ) ); ?>
					<?php endforeach; ?>
				</div>
			</section>
			<?php endif; ?>


		</main>

		<aside class="sidebar-col">

			<?php /* ── Browse tool categories + help CTA ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['categories'] ) ) : ?>
				<?php
				$_eh = isset( $ctx['sidebar']['expert_help'] ) ? $ctx['sidebar']['expert_help'] : array();
				adn_component( 'parts/tools_sidebar', array( 'sidebar' => array(
					'hl_heading'   => isset( $ctx['sidebar']['hl_heading'] ) ? $ctx['sidebar']['hl_heading'] : '',
					'hl_links'     => isset( $ctx['sidebar']['hl_links'] )   ? $ctx['sidebar']['hl_links']   : array(),
					'categories'   => $ctx['sidebar']['categories'],
					'help'         => array(
						'title'        => isset( $_eh['heading'] )          ? $_eh['heading']          : '',
						'text'         => isset( $_eh['subtitle'] )         ? $_eh['subtitle']         : '',
						'button_label' => isset( $_eh['cta']['label'] )     ? $_eh['cta']['label']     : '',
						'button_url'   => isset( $_eh['cta']['url'] )       ? $_eh['cta']['url']       : '',
					),
				) ) );
				?>
			<?php endif; ?>

			<?php /* ── Latest news mini ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['news_mini']['items'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_news_mini', array( 'news_mini' => $ctx['sidebar']['news_mini'] ) ); ?>
			<?php endif; ?>

		</aside>

	</div>
</div>

<?php adn_page_close( $ctx ); ?>
