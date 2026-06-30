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

<?php /* ── CMS Taxonomy Terms strip ── */ ?>
<?php if ( ! empty( $ctx['cms_terms'] ) ) : ?>
<div class="post-terms-bar">
	<div class="container">
		<?php foreach ( $ctx['cms_terms'] as $_pt ) : ?>
			<span class="post-term-pill"><?php if ( '' !== $_pt['emoji'] ) echo esc_html( $_pt['emoji'] ) . ' '; echo esc_html( $_pt['name'] ); ?></span>
		<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>

<?php /* ============================== ARTICLE LAYOUT ============================== */ ?>
<div class="article-outer">
	<div class="article-layout">

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

		<?php /* ── RIGHT COLUMN: TOC + Sidebar ── */ ?>
		<div class="article-right-col">

			<div class="article-toc-wrapper">
				<?php adn_component( 'parts/post_sidebar_toc' ); ?>
			</div>

			<aside class="article-sidebar" aria-label="<?php esc_attr_e( 'Article sidebar', ADN_TEXT_DOMAIN ); ?>">
			<?php /* Related guides (from WP_Query - same category, latest 4) */ ?>
			<?php if ( ! empty( $ctx['related_guides'] ) ) : ?>
				<div class="">
					<?php adn_component( 'parts/post_sidebar_related', array( 'related_guides' => $ctx['related_guides'] ) ); ?>
				</div>
			<?php endif; ?>

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


			<?php /* Newsletter signup */ ?>
			<?php if ( ! empty( $ctx['sidebar']['newsletter'] ) ) : ?>
				<div class="">
					<?php adn_component( 'parts/post_sidebar_newsletter', array( 'newsletter' => $ctx['sidebar']['newsletter'] ) ); ?>
				</div>
			<?php endif; ?>

		</aside>

		</div><!-- .article-right-col -->

	</div>
</div>

<?php /* ============================== RELATED ARTICLES ============================== */ ?>
<section class="post-related-articles" id="postRelatedArticles"
	data-post="<?php echo esc_attr( get_the_ID() ); ?>"
	data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
	hidden>
	<div class="container">
		<h3 class="pra-heading"><?php echo esc_html( adn_term( 'labels.also_viewed_articles', 'Also View' ) ); ?></h3>
		<div class="pra-row" id="praRow"></div>
	</div>
</section>
<script>
( function () {
	var sec  = document.getElementById( 'postRelatedArticles' );
	var row  = document.getElementById( 'praRow' );
	if ( ! sec || ! row ) { return; }

	var postId = sec.dataset.post;
	var ajax   = sec.dataset.ajax;
	if ( ! postId || ! ajax ) { return; }

	var xhr = new XMLHttpRequest();
	xhr.open( 'POST', ajax, true );
	xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
	xhr.onreadystatechange = function () {
		if ( xhr.readyState !== 4 ) { return; }
		try {
			var res = JSON.parse( xhr.responseText );
			if ( res && res.success && res.data && res.data.articles && res.data.articles.length ) {
				render( res.data.articles );
				sec.removeAttribute( 'hidden' );
			}
		} catch ( e ) {}
	};
	xhr.send( 'action=adn_post_related_articles&post_id=' + encodeURIComponent( postId ) );

	function x( s ) {
		return String( s ).replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' ).replace( /"/g, '&quot;' );
	}

	function render( articles ) {
		var html = '';
		articles.forEach( function ( a ) {
			html += '<a href="' + x( a.url ) + '" class="pra-card">';
			if ( a.thumbnail ) {
				html += '<div class="pra-thumb"><img src="' + x( a.thumbnail ) + '" alt="" loading="lazy"></div>';
			} else {
				html += '<div class="pra-thumb pra-thumb--placeholder"><svg aria-hidden="true" width="40" height="48" viewBox="0 0 40 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 2h20l12 12v32a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2z" fill="var(--slate-200,#e5e7eb)" stroke="var(--slate-300,#d1d5db)" stroke-width="1.5"/><path d="M26 2v12h12" fill="none" stroke="var(--slate-300,#d1d5db)" stroke-width="1.5"/><rect x="10" y="22" width="20" height="2" rx="1" fill="var(--slate-400,#9ca3af)"/><rect x="10" y="28" width="16" height="2" rx="1" fill="var(--slate-400,#9ca3af)"/><rect x="10" y="34" width="12" height="2" rx="1" fill="var(--slate-400,#9ca3af)"/></svg></div>';
			}
			html += '<div class="pra-body">';
			html += '<p class="pra-title">' + x( a.title ) + '</p>';
			if ( a.excerpt ) {
				html += '<p class="pra-excerpt">' + x( a.excerpt ) + '</p>';
			}
			html += '<span class="pra-read">Read more <svg width="11" height="11" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M2 6h8M7 3l3 3-3 3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
			html += '</div></a>';
		} );
		row.innerHTML = html;
	}
} () );
</script>

<?php /* ============================== FOOTER ============================== */ ?>
<?php
adn_component( 'parts/pre_footer' );
adn_component( 'parts/main_footer', array( 'footer' => isset( $ctx['chrome']['footer'] ) ? $ctx['chrome']['footer'] : array() ) );
adn_component( 'parts/post_footer' );
adn_component( 'parts/post_footer_notice' );

get_footer();
?>
