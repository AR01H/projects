<?php
/**
 * Template Name: Guide Article
 *
 * pages/page-topic_category_guide.php — Generic guide/article page.
 *
 * Works for any category's guide articles: buying, selling, moving or any future type.
 * The page slug drives which JSON is loaded — no content is hardcoded here.
 *
 * Architecture:
 *   data/json/guide-{slug}.json
 *     → apis/services.php  adn_service_guide_data($slug)
 *       → intermediate/page_guide_logical.php  adn_guide_get_context()
 *         → THIS FILE  (structure only)
 *           → components/sections/*  (article sections)
 *           → components/parts/*     (sidebar, stay-informed bar)
 *           → components/cards/*     (reused from home/category pages)
 *
 * RULE: No hardcoded content and no data reads here — only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_guide_logical.php';
$ctx = adn_guide_get_context();

get_header();
?>

<?php adn_component( 'parts/main_header', array( 'chrome' => $ctx['chrome'] ) ); ?>

<?php /* ============================== BREADCRUMB ============================== */ ?>
<?php adn_component( 'parts/breadcrumb', array( 'items' => $ctx['breadcrumb'] ) ); ?>

<?php /* ============================== ARTICLE HEADER ============================== */ ?>
<section class="article-header-section">
	<div class="container">
		<?php adn_component( 'sections/article_header', array( 'article' => $ctx['article'] ) ); ?>
	</div>
</section>

<?php /* ============================== KEY TAKEAWAYS + IN-THIS-GUIDE ============================== */ ?>
<?php if ( ! empty( $ctx['key_takeaways'] ) || ! empty( $ctx['toc']['items'] ) ) : ?>
<div class="container">
	<?php adn_component( 'sections/article_key_info', array(
		'key_takeaways' => $ctx['key_takeaways'],
		'toc'           => $ctx['toc'],
	) ); ?>
</div>
<?php endif; ?>

<?php /* ============================== MAIN ARTICLE + SIDEBAR ============================== */ ?>
<div class="container">
	<div class="article-layout">

		<article class="article-main">
			<div class="article-body">
				<?php adn_component( 'sections/article_body', array( 'sections' => $ctx['sections'] ) ); ?>
			</div>

			<?php adn_component( 'sections/article_feedback', array( 'feedback' => $ctx['feedback'] ) ); ?>
			<?php adn_component( 'sections/article_author', array( 'author' => $ctx['author'] ) ); ?>
		</article>

		<aside class="article-sidebar">
			<?php
			if ( ! empty( $ctx['toc']['items'] ) ) :
				adn_component( 'parts/sidebar_toc', array( 'toc' => $ctx['toc'] ) );
			endif;

			if ( ! empty( $ctx['sidebar']['quick_tools'] ) ) :
				adn_component( 'parts/sidebar_quick_tools', array( 'quick_tools' => $ctx['sidebar']['quick_tools'] ) );
			endif;

			if ( ! empty( $ctx['sidebar']['most_read'] ) ) :
				adn_component( 'parts/sidebar_most_read', array( 'most_read' => $ctx['sidebar']['most_read'] ) );
			endif;

			if ( ! empty( $ctx['sidebar']['news']['items'] ) ) :
				adn_component( 'parts/sidebar_news_mini', array( 'news_mini' => $ctx['sidebar']['news'] ) );
			endif;

			if ( ! empty( $ctx['sidebar']['expert_help'] ) ) :
				adn_component( 'parts/sidebar_expert_help', array( 'expert_help' => $ctx['sidebar']['expert_help'] ) );
			endif;
			?>
		</aside>

	</div>
</div>

<?php /* ============================== STAY INFORMED ============================== */ ?>
<?php if ( ! empty( $ctx['stay_informed'] ) ) : ?>
<section class="stay-informed-bar">
	<div class="container">
		<?php adn_component( 'parts/stay_informed_bar', array( 'stay_informed' => $ctx['stay_informed'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== FOOTER ============================== */ ?>
<?php
adn_component( 'parts/pre_footer' );
adn_component( 'parts/main_footer', array( 'footer' => isset( $ctx['chrome']['footer'] ) ? $ctx['chrome']['footer'] : array() ) );
adn_component( 'parts/post_footer' );
adn_component( 'parts/post_footer_notice' );

get_footer();
?>
