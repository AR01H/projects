<?php
/**
 * single.php - WordPress single post template.
 *
 * Renders any standard WordPress post.
 * Content (title, body, date, author, excerpt) comes from the WP post system.
 * Extended fields (category tag, read time, key takeaways, article icon)
 * come from post meta written by the plugin or the WP editor.
 * Sidebar data (tools, newsletter) comes from data/json/post_sidebar.json.
 * Related guides and latest news are queried live via WP_Query.
 *
 * Post meta keys (all optional, theme provides sensible defaults):
 *   _adn_article_icon      string   emoji icon shown in the article header
 *   _adn_read_time         string   "12 min read"
 *   _adn_key_takeaways     string   JSON-encoded string[] for the takeaways box
 *   _adn_category_tag      string   display category name override
 *
 * Assets: single.css + single.js are auto-enqueued via explode_function.php
 * when is_single() is true - no Template Name comment needed here.
 *
 * RULE: No hardcoded content or data reads here - only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/post_logical.php';

/*
 * Set up the WP post loop so all template tags (the_title, the_content, etc.)
 * return the correct data. single.php always has exactly one post in the query.
 */
if ( have_posts() ) {
	the_post();
}

$ctx = adn_post_get_context();

get_header();
?>

<?php adn_component( 'parts/main_header', array( 'chrome' => $ctx['chrome'] ) ); ?>

<?php /* ============================== BREADCRUMB ============================== */ ?>
<?php if ( ! empty( $ctx['breadcrumb'] ) ) : ?>
	<?php adn_component( 'sections/page_hero', array(
		'hero'       => $ctx['article'],
		'breadcrumb' => $ctx['breadcrumb'],
	) ); ?>
<?php endif; ?>

<?php /* ============================== ARTICLE LAYOUT ============================== */ ?>
<div class="article-outer">
	<div class="article-layout">

		<?php /* TOC - single.js auto-generates links from .article-body h2 headings */ ?>
		<div class="article-toc-wrapper">
			<?php adn_component( 'parts/post_sidebar_toc' ); ?>
		</div>

		<?php /* ── MAIN ARTICLE COLUMN ── */ ?>
		<main class="article-main" id="main-content">

			<?php /* Key takeaways box (renders nothing when empty) */ ?>
			<?php adn_component( 'sections/post_key_takeaways', array( 'key_takeaways' => $ctx['key_takeaways'] ) ); ?>

			<?php /* WordPress post content - blocks / classic editor HTML */ ?>
			<div class="article-body">
				<?php the_content(); ?>
			</div>

			<?php /* Feedback row + social share */ ?>
			<?php adn_component( 'sections/post_feedback', array( 'share' => $ctx['share'] ) ); ?>

			<?php /* Author box */ ?>
			<!-- <?php adn_component( 'sections/post_author', array( 'author' => $ctx['author'] ) ); ?> -->

			<?php /* Disclaimer */ ?>
			<!-- <?php adn_component( 'sections/post_disclaimer' ); ?> -->

			<?php /* Comments */ ?>
			<?php if ( comments_open() || get_comments_number() ) : ?>
				<?php comments_template(); ?>
			<?php endif; ?>

		</main>

		<?php /* ── SIDEBAR ── */ ?>
		<aside class="article-sidebar" aria-label="<?php esc_attr_e( 'Article sidebar', ADN_TEXT_DOMAIN ); ?>">
			<?php /* Highlight Links */ ?>
			<?php if ( ! empty( $ctx['highlight_links'] ) ) : ?>
				<div class="">
					<?php adn_component( 'parts/post_sidebar_highlights', array( 'highlight_links' => $ctx['highlight_links'] ) ); ?>
				</div>
			<?php endif; ?>

			<?php /* Related Content (Custom Links) */ ?>
			<?php if ( ! empty( $ctx['related_content'] ) ) : ?>
				<div class="container_post_sidebar_related_content" >
					<?php adn_component( 'parts/post_sidebar_related_content', array( 'related_content' => $ctx['related_content'] ) ); ?>
				</div>
			<?php endif; ?>

			<?php /* Popular tools (from post_sidebar.json) */ ?>
			<?php if ( ! empty( $ctx['sidebar']['calculators'] ) ) : ?>
				<div class="">
					<?php adn_component( 'parts/post_sidebar_tools', array( 'calculators' => $ctx['sidebar']['calculators'] ) ); ?>
				</div>
			<?php endif; ?>

			<?php /* Related guides (from WP_Query - same category, latest 4) */ ?>
			<?php if ( ! empty( $ctx['related_guides'] ) ) : ?>
				<div class="">
					<?php adn_component( 'parts/post_sidebar_related', array( 'related_guides' => $ctx['related_guides'] ) ); ?>
				</div>
			<?php endif; ?>

			<?php /* Latest news (from WP_Query - most recent 3 posts) */ ?>
			<?php if ( ! empty( $ctx['latest_news'] ) ) : ?>
				<div class="">
					<?php adn_component( 'parts/post_sidebar_news', array( 'latest_news' => $ctx['latest_news'] ) ); ?>
				</div>
			<?php endif; ?>

			<?php /* Newsletter signup */ ?>
			<?php if ( ! empty( $ctx['sidebar']['newsletter'] ) ) : ?>
				<div class="">
					<?php adn_component( 'parts/post_sidebar_newsletter', array( 'newsletter' => $ctx['sidebar']['newsletter'] ) ); ?>
				</div>
			<?php endif; ?>

		</aside>

	</div>
</div>

<?php /* ============================== FOOTER ============================== */ ?>
<?php
adn_component( 'parts/pre_footer' );
adn_component( 'parts/main_footer', array( 'footer' => isset( $ctx['chrome']['footer'] ) ? $ctx['chrome']['footer'] : array() ) );
adn_component( 'parts/post_footer' );
adn_component( 'parts/post_footer_notice' );

get_footer();
?>
