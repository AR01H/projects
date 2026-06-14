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

adn_page_open( $ctx );
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

			<?php if ( ! empty( $ctx['sidebar']['guide_parents']['items'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_guide_parents', array(
					'guide_parents' => $ctx['sidebar']['guide_parents'],
				) ); ?>
			<?php endif; ?>

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

		</aside>

	</div>
</div>

<?php adn_page_close( $ctx ); ?>
