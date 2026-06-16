<?php
/**
 * Template Name: Ask an Expert
 *
 * pages/page-ask-expert.php - Expert directory listing page.
 * Category filter tabs + expert cards grid + sidebar.
 *
 * Architecture:
 *   DB (AH_Expert_DB) + WP page + admin banner option
 *     → intermediate/page_ask_expert_logical.php  adn_ask_expert_get_context()
 *       → THIS FILE (structure only)
 *
 * RULE: No hardcoded content or data reads here - only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_ask_expert_logical.php';
$ctx = adn_ask_expert_get_context();

// Pass AJAX url and contact nonce to ask_expert.js on the listing page.
// The script handle auto-registered in explode_function.php is 'adn-page-ask-expert-script'.
wp_localize_script( 'adn-page-ask-expert-script', 'adnExpert', array(
	'ajaxUrl' => isset( $ctx['ajax_url'] )      ? $ctx['ajax_url']      : admin_url( 'admin-ajax.php' ),
	'nonce'   => isset( $ctx['contact_nonce'] ) ? $ctx['contact_nonce'] : '',
) );

$_open_ctx               = $ctx;
$_open_ctx['breadcrumb'] = array();
adn_page_open( $_open_ctx );
?>

<?php /* ============================== HERO ============================== */ ?>
<?php adn_component( 'sections/page_hero', array(
	'hero'       => $ctx['hero'],
	'breadcrumb' => $ctx['breadcrumb'],
	'stats'      => $ctx['stats'],
) ); ?>

<?php /* ============================== CATEGORY TABS STRIP ============================== */ ?>
<?php if ( ! empty( $ctx['categories'] ) ) : ?>
	<?php adn_component( 'sections/expert_cats_strip', array( 'categories' => $ctx['categories'] ) ); ?>
<?php endif; ?>

<?php /* ============================== MAIN LAYOUT: CARDS + SIDEBAR ============================== */ ?>
<div class="expert-main-layout">

	<?php /* MAIN - expert cards */ ?>
	<main>

		<?php /* Loader - shown during search debounce */ ?>
		<div class="expert-grid-loader" id="expertGridLoader" hidden aria-hidden="true">
			<div class="egl-spinner"></div>
			<p><?php esc_html_e( 'Finding experts…', ADN_TEXT_DOMAIN ); ?></p>
		</div>

		<div class="expert-cards-grid" id="expertGrid">
			<?php foreach ( $ctx['experts'] as $_expert ) : ?>
				<?php adn_component( 'cards/expert_card', array( 'item' => (array) $_expert ) ); ?>
			<?php endforeach; ?>

			<?php /* Permanent placeholder - always visible, never filtered */ ?>
			<div class="expert-card expert-card-more" data-permanent="1">
				<div class="ecm-inner">
					<span class="ecm-icon" aria-hidden="true">🤝</span>
					<p class="ecm-heading"><?php esc_html_e( 'More experts here to help', ADN_TEXT_DOMAIN ); ?></p>
					<p class="ecm-sub"><?php esc_html_e( "Can't find who you need? Our network is growing - contact us and we'll match you with the right professional.", ADN_TEXT_DOMAIN ); ?></p>
					<a href="<?php echo esc_url( home_url( SITE_CONTACT_URL ) ); ?>" class="btn btn-primary ecm-btn">
						<?php esc_html_e( 'Get Matched', ADN_TEXT_DOMAIN ); ?>
					</a>
				</div>
			</div>
		</div>

		<?php /* Empty state - shown when search/filter returns no results (placeholder card always stays visible) */ ?>
		<div class="expert-no-results" id="expertNoResults" hidden aria-live="polite">
			<span class="enr-icon" aria-hidden="true">🔍</span>
			<p class="enr-heading"><?php esc_html_e( 'No experts found', ADN_TEXT_DOMAIN ); ?></p>
			<p class="enr-sub"><?php esc_html_e( 'Try a different name or specialism.', ADN_TEXT_DOMAIN ); ?></p>
		</div>

		<?php /* "Can't find the right expert?" banner */ ?>
		<?php if ( ! empty( $ctx['cant_find_cta'] ) ) : ?>
			<div style="margin-top:28px;">
				<?php adn_component( 'sections/expert_cant_find', array( 'cant_find_cta' => $ctx['cant_find_cta'] ) ); ?>
			</div>
		<?php endif; ?>
	</main>

	<?php /* SIDEBAR */ ?>
	<?php if ( ! empty( $ctx['sidebar'] ) ) : ?>
		<?php adn_component( 'parts/expert_sidebar', array( 'sidebar' => $ctx['sidebar'] ) ); ?>
	<?php endif; ?>

</div>

<?php adn_page_close( $ctx ); ?>
