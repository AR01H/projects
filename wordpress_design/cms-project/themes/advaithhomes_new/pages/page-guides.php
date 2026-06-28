<?php
/**
 * Template Name: Guides Hub
 *
 * pages/page-guides.php - /guides/ hub: all parent terms + their subtopics.
 *
 * Architecture:
 *   intermediate/page_guides_logical.php  adn_guides_get_context()
 *     → THIS FILE  (structure only)
 *       → components/sections/page_hero
 *       → components/sections/guides_parent_group  (per parent term)
 *       → components/parts/sidebar_guide_parents   (browse by topic)
 *       → components/parts/sidebar_quick_tools     (calculators)
 *       → components/parts/sidebar_news_mini       (latest news)
 *
 * RULE: No hardcoded content and no data reads here - only structure.
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_guides_logical.php';
$ctx = adn_guides_get_context();

$_seo_title = isset( $ctx['hero']['title'] )       ? (string) $ctx['hero']['title']                       : '';
$_seo_desc  = isset( $ctx['hero']['description'] ) ? wp_strip_all_tags( (string) $ctx['hero']['description'] ) : '';

/* Keywords + og:article:tag — one entry per parent term name */
$_seo_kw = array();
foreach ( isset( $ctx['groups'] ) ? $ctx['groups'] : array() as $_g ) {
	$_gn = isset( $_g['name'] ) ? trim( (string) $_g['name'] ) : '';
	if ( '' !== $_gn ) {
		$_seo_kw[] = $_gn;
	}
}

/* og:image — first parent term that has a photo */
$_seo_image = '';
foreach ( isset( $ctx['groups'] ) ? $ctx['groups'] : array() as $_g ) {
	if ( ! empty( $_g['image_url'] ) ) {
		$_seo_image = (string) $_g['image_url'];
		break;
	}
}

/* CollectionPage + ItemList schema — each parent term is one list item */
$_seo_col_items = array();
foreach ( isset( $ctx['groups'] ) ? $ctx['groups'] : array() as $_g ) {
	$_gi_title = isset( $_g['name'] ) ? (string) $_g['name'] : '';
	$_gi_url   = isset( $_g['url'] )  ? (string) $_g['url']  : '';
	if ( '' !== $_gi_title && '' !== $_gi_url ) {
		$_seo_col_items[] = array( 'title' => $_gi_title, 'url' => $_gi_url );
	}
}

adn_seo_register( array(
	'title'             => $_seo_title,
	'description'       => $_seo_desc,
	'canonical'         => defined( 'SITE_GUIDES_URL' ) ? home_url( SITE_GUIDES_URL ) : '',
	'breadcrumb'        => isset( $ctx['breadcrumb'] ) ? $ctx['breadcrumb'] : array(),
	'image'             => $_seo_image,
	'keywords'          => $_seo_kw,
	'tags'              => $_seo_kw,
	'article_section'   => defined( 'SITE_CONTENT_PLURAL' ) ? SITE_CONTENT_PLURAL : 'Guides',
	'schema_collection' => ! empty( $_seo_col_items ) ? array(
		'name'        => $_seo_title,
		'description' => $_seo_desc,
		'url'         => defined( 'SITE_GUIDES_URL' ) ? home_url( SITE_GUIDES_URL ) : '',
		'items'       => $_seo_col_items,
	) : array(),
) );

$_open_ctx               = $ctx;
$_open_ctx['breadcrumb'] = array();
adn_page_open( $_open_ctx );
?>

<?php /* ═══════════════════════════ HERO ═══════════════════════════ */ ?>
<?php adn_component( 'sections/page_hero', array(
	'hero'       => $ctx['hero'],
	'breadcrumb' => $ctx['breadcrumb'],
) ); ?>

<?php /* ═══════════════════════════ MAIN LAYOUT ═══════════════════════════ */ ?>
<div class="container">
	<div class="guides-hub-layout">

		<?php /* ── LEFT: parent groups ─────────────────────────────────── */ ?>
		<main class="guides-hub-main">
			<?php if ( ! empty( $ctx['groups'] ) ) : ?>
				<?php foreach ( $ctx['groups'] as $_group ) : ?>
					<?php adn_component( 'sections/guides_parent_group', array( 'group' => $_group ) ); ?>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="guides-empty"><?php esc_html_e( 'No guides available yet. Check back soon.', ADN_TEXT_DOMAIN ); ?></p>
			<?php endif; ?>
		</main>

		<?php /* ── RIGHT: sidebar ──────────────────────────────────────── */ ?>
		<aside class="guides-hub-sidebar">

			<?php if ( ! empty( $ctx['sidebar']['quick_tools']['items'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_quick_tools', array(
					'quick_tools' => $ctx['sidebar']['quick_tools'],
				) ); ?>
			<?php endif; ?>

			<?php if ( ! empty( $ctx['sidebar']['news_mini']['items'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_news_mini', array(
					'news_mini' => $ctx['sidebar']['news_mini'],
				) ); ?>
			<?php endif; ?>

			<?php if ( ! empty( $ctx['sidebar']['expert_help']['cta']['label'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_expert_help', array(
					'expert_help' => $ctx['sidebar']['expert_help'],
				) ); ?>
			<?php endif; ?>

		</aside>

	</div>
</div>

<?php adn_page_close( $ctx ); ?>
