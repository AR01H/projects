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

adn_seo_register( array(
	'description' => isset( $ctx['meta_description'] ) ? (string) $ctx['meta_description'] : '',
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

		<?php /* Search bar */ ?>
		<div class="expert-search-row">
			<div class="search-input-wrap">
				<i class="fa-solid fa-magnifying-glass search-icon" aria-hidden="true"></i>
				<input type="search" id="expertSearch" autocomplete="off"
					placeholder="<?php esc_attr_e( 'Search by name or specialism…', ADN_TEXT_DOMAIN ); ?>"
					aria-label="<?php esc_attr_e( 'Search experts', ADN_TEXT_DOMAIN ); ?>">
				<button type="button" id="expertSearchClear" class="search-btn expert-search-clear"
					hidden aria-label="<?php esc_attr_e( 'Clear search', ADN_TEXT_DOMAIN ); ?>">
					<i class="fa-solid fa-xmark" aria-hidden="true"></i>
				</button>
			</div>
		</div>

		<?php /* Loader - shown during search debounce */ ?>
		<div class="expert-grid-loader" id="expertGridLoader" hidden aria-hidden="true">
			<div class="egl-spinner"></div>
			<p><?php esc_html_e( 'Finding experts…', ADN_TEXT_DOMAIN ); ?></p>
		</div>

		<?php /* Empty state - shown when search/filter returns no results */ ?>
		<div class="expert-no-results" id="expertNoResults" hidden aria-live="polite">
			<i class="fa-solid fa-magnifying-glass enr-icon" aria-hidden="true"></i>
			<p class="enr-heading"><?php esc_html_e( 'No experts found', ADN_TEXT_DOMAIN ); ?></p>
			<p class="enr-sub"><?php esc_html_e( 'Try a different name or specialism, or clear your search to see all experts.', ADN_TEXT_DOMAIN ); ?></p>
			<div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
				<button type="button" class="enr-reset" id="expertSearchReset"><?php esc_html_e( 'Clear Search', ADN_TEXT_DOMAIN ); ?></button>
				<a href="<?php echo esc_url( home_url( SITE_CONTACT_URL ) ); ?>" class="btn btn-primary" style="font-size:0.88rem;padding:9px 22px"><?php esc_html_e( 'Get Matched', ADN_TEXT_DOMAIN ); ?></a>
			</div>
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

<!-- <?php if ( ! empty( $ctx['latest_news']['items'] ) ) : ?>
<section class="page-latest-news">
	<div class="container">
		<?php adn_component( 'parts/news_widget', array( 'widget' => $ctx['latest_news'] ) ); ?>
	</div>
</section>
<?php endif; ?> -->

<?php
$_fi_exp     = get_option( 'adn_expert_banner', array() );
$_fi_exp_sec = ( is_array( $_fi_exp ) && ! empty( $_fi_exp['featured_in_section'] ) )
	? sanitize_key( $_fi_exp['featured_in_section'] ) : '';
adn_component( 'parts/featured_in', array( 'section' => $_fi_exp_sec ) );
?>

<?php adn_page_close( $ctx ); ?>
