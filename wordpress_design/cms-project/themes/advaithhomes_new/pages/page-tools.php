<?php
/**
 * Template Name: Tools
 *
 * pages/page-tools.php - All Tools listing page.
 *
 * All content comes from the calculator registry via the service layer.
 * No content is hardcoded here - only structure.
 *
 * Architecture:
 *   adn_calculators() registry
 *     → intermediate/page_tools_logical.php  adn_calculators_get_context()
 *       → components/sections/tools_hero.php
 *       → components/sections/tools_trust_bar.php
 *       → components/sections/tools_search_bar.php
 *       → components/sections/tools_popular.php
 *       → components/sections/tools_all.php
 *       → components/parts/tools_sidebar.php
 *       → components/sections/newsletter_cta.php
 *
 * RULE: No hardcoded content and no data reads here - only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_tools_logical.php';
$ctx = adn_calculators_get_context();

adn_seo_register( array(
	'title'       => isset( $ctx['hero']['title'] )       ? (string) $ctx['hero']['title']       : '',
	'description' => isset( $ctx['hero']['description'] ) ? wp_strip_all_tags( (string) $ctx['hero']['description'] ) : '',
	'canonical'   => defined( 'SITE_CALCULATORS_URL' ) ? home_url( SITE_CALCULATORS_URL ) : '',
	'breadcrumb'  => isset( $ctx['breadcrumb'] )          ? $ctx['breadcrumb']                   : array(),
) );

// Breadcrumb renders inside the hero banner - suppress from adn_page_open.
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

<?php /* ============================== TRUST BAR ============================== */ ?>
<?php if ( ! empty( $ctx['trust_items'] ) ) : ?>
	<?php adn_component( 'sections/tools_trust_bar', array( 'trust_items' => $ctx['trust_items'] ) ); ?>
<?php endif; ?>

<?php /* ============================== SEARCH BAR ============================== */ ?>
<!-- <?php adn_component( 'sections/tools_search_bar', array( 'search' => $ctx['search'] ) ); ?> -->

<?php /* ============================== POPULAR / QUICK ACCESS ============================== */ ?>
<?php if ( ! empty( $ctx['popular_tools'] ) ) : ?>
	<?php adn_component( 'sections/tools_popular', array( 'popular_tools' => $ctx['popular_tools'] ) ); ?>
<?php endif; ?>

<?php /* ============================== FEATURED & SUGGESTION SECTION ============================== */ ?>
<?php
$has_featured  = ! empty( $ctx['featured_tools'] );
$has_suggested = ! empty( $ctx['suggested_tools'] );
if ( $has_featured || $has_suggested ) :
?>
<section class="tc-section tc-featured-section">
	<div class="container">
		<div class="tc-two-col">

			<?php /* ── Suggested Calculators widget ── */ ?>
			<?php if ( $has_suggested ) : ?>
			<div class="tc-widget tc-suggested-widget">
				<div class="tc-widget-header">
					<div class="tc-widget-title">
						<span class="tc-widget-icon">✨</span>
						<h2><?php esc_html_e( 'Suggested Calculators', ADN_TEXT_DOMAIN ); ?></h2>
					</div>
				</div>
				<div class="tc-suggested-list">
					<?php foreach ( $ctx['suggested_tools'] as $sc ) :
						$sc_url   = esc_url( isset( $sc['url'] )   ? $sc['url']   : '' );
						$sc_title = isset( $sc['title'] ) ? $sc['title'] : '';
						$sc_raw   = isset( $sc['icon'] ) ? trim( (string) $sc['icon'] ) : '';
						$sc_icon  = '' !== $sc_raw ? $sc_raw : '💡';
					?>
						<a href="<?php echo $sc_url; ?>" class="tc-suggested-item">
							<span class="tc-suggested-icon"><?php echo adn_icon( $sc_icon ); ?></span>
							<span class="tc-suggested-label"><?php echo esc_html( $sc_title ); ?></span>
							<span class="tc-suggested-arrow">&rarr;</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php /* ── Featured Calculator widget ── */ ?>
			<?php if ( $has_featured ) :
				$fc = $ctx['featured_tools'][0];
				$fc_title = ! empty( $fc['featured_title'] ) ? $fc['featured_title'] : $fc['title'];
				$fc_desc  = ! empty( $fc['featured_desc'] )  ? $fc['featured_desc']  : $fc['desc'];
				$fc_badge = ! empty( $fc['highlight'] ) ? $fc['highlight'] : 'MOST USED';
				$benefits = array_filter( array(
					! empty( $fc['benefit_1'] ) ? $fc['benefit_1'] : 'Instant & accurate estimations',
					! empty( $fc['benefit_2'] ) ? $fc['benefit_2'] : '100% free to use, no sign-up',
					! empty( $fc['benefit_3'] ) ? $fc['benefit_3'] : 'Based on latest UK property rules',
					! empty( $fc['benefit_4'] ) ? $fc['benefit_4'] : '',
				) );
			?>
			<div class="tc-featured-card">
				<div class="tc-featured-content">
					<span class="tc-featured-badge"><?php echo esc_html( $fc_badge ); ?></span>
					<h2><?php echo esc_html( $fc_title ); ?></h2>
					<?php if ( $fc_desc ) : ?>
						<p class="tc-featured-desc"><?php echo esc_html( $fc_desc ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $benefits ) ) : ?>
					<ul class="tc-featured-benefits">
						<?php foreach ( $benefits as $b ) : ?>
							<li>
								<svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3 3 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
								<?php echo esc_html( $b ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
					<?php endif; ?>
					<a href="<?php echo esc_url( $fc['url'] ); ?>" class="tc-featured-btn">
						<?php echo esc_html( 'Open ' . $fc_title ); ?>
						<svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>
				</div>
				<div class="tc-featured-illustration" aria-hidden="true">
					<div class="mock-calculator">
						<div class="mock-calc-screen">123,456</div>
						<div class="mock-calc-grid">
							<span class="mock-btn mock-btn-fn">C</span><span class="mock-btn mock-btn-fn">&plusmn;</span><span class="mock-btn mock-btn-fn">%</span><span class="mock-btn mock-btn-op">&divide;</span>
							<span class="mock-btn">7</span><span class="mock-btn">8</span><span class="mock-btn">9</span><span class="mock-btn mock-btn-op">&times;</span>
							<span class="mock-btn">4</span><span class="mock-btn">5</span><span class="mock-btn">6</span><span class="mock-btn mock-btn-op">&minus;</span>
							<span class="mock-btn">1</span><span class="mock-btn">2</span><span class="mock-btn">3</span><span class="mock-btn mock-btn-op">&plus;</span>
							<span class="mock-btn mock-btn-double">0</span><span class="mock-btn">.</span><span class="mock-btn mock-btn-eq">&equals;</span>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

		</div>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== BROWSE BY CATEGORY (no-op) ============================== */ ?>
<?php if ( ! empty( $ctx['sidebar']['categories'] ) ) : ?>
	<?php adn_component( 'sections/tools_categories', array( 'categories' => $ctx['sidebar']['categories'] ) ); ?>
<?php endif; ?>


<?php /* ============================== ALL CALCULATORS ============================== */ ?>
<?php if ( ! empty( $ctx['all_tools'] ) ) : ?>
	<?php adn_component( 'sections/tools_all', array(
		'filter_tabs' => $ctx['filter_tabs'],
		'all_tools'   => $ctx['all_tools'],
		'find_cta'    => $ctx['find_cta'],
	) ); ?>
<?php endif; ?>

<?php if ( ! empty( $ctx['news']['items'] ) || ! empty( $ctx['regulations']['items'] ) || ! empty( $ctx['hot_topics']['items'] ) ) : ?>
<section class="cat-highlight-section">
	<div class="container">
		<?php adn_component( 'sections/news_three_col', array(
			'news'        => $ctx['news'],
			'regulations' => $ctx['regulations'],
			'hot_topics'  => $ctx['hot_topics'],
		) ); ?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== NEWSLETTER ============================== */ ?>
<?php if ( ! empty( $ctx['newsletter'] ) ) : ?>
<section class="newsletter-cta">
	<div class="container">
		<?php adn_component( 'sections/newsletter_cta', array( 'newsletter' => $ctx['newsletter'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php
$_fi_tools     = get_option( 'adn_calculators_general', array() );
$_fi_tools_sec = ( is_array( $_fi_tools ) && ! empty( $_fi_tools['featured_in_section'] ) )
	? sanitize_key( $_fi_tools['featured_in_section'] ) : '';

if ( '' !== $_fi_tools_sec ) {
	adn_component( 'parts/featured_in', array( 'section' => $_fi_tools_sec ) );
}
?>

<?php adn_page_close( $ctx ); ?>

