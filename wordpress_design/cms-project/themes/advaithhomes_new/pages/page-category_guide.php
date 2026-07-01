<?php
/**
 * Template Name: Category Guide
 *
 * pages/page-category_guide.php - Generic category landing page.
 *
 * Handles any slug-based category (buying, selling, house-movers…).
 * The page slug drives which JSON is loaded via the service layer, so
 * no content is hardcoded here.  When the plugin is ready to take over,
 * replace adn_service_category_data() internals - nothing else changes.
 *
 * Architecture:
 *   data/json/{slug}.json
 *     → apis/services.php  adn_service_category_data($slug)
 *       → intermediate/page_category_logical.php  adn_category_get_context()
 *         → THIS FILE  (structure only)
 *           → components/sections/*  components/parts/*  components/cards/*
 *
 * RULE: No hardcoded content and no data reads here - only structure.
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_category_logical.php';
$ctx = adn_category_get_context();

// Enqueue the buying page design CSS when viewing the 'buying' parent-term.
if ( isset( $ctx['slug'] ) && 'buying' === $ctx['slug'] ) {
	wp_enqueue_style( 'adn-buying-design', get_template_directory_uri() . '/assets/css/buying-design.css', array(), ADN_THEME_VERSION );
}

if ( ! empty( $ctx['faqs']['items'] ) ) {
	wp_enqueue_style( 'adn-page-faqs-style', get_template_directory_uri() . '/assets/css/faqs.css', array(), ADN_THEME_VERSION );
	wp_enqueue_script( 'adn-page-faqs-script', get_template_directory_uri() . '/assets/js/faqs.js', array(), ADN_THEME_VERSION, true );
}
wp_enqueue_style( 'adn-resources', get_template_directory_uri() . '/assets/css/resources.css', array(), ADN_THEME_VERSION );

// ── SEO: register title, description, canonical, breadcrumb for this category page ──
$_seo_title = isset( $ctx['hero']['title'] ) ? (string) $ctx['hero']['title'] : '';
$_seo_desc  = '';
if ( ! empty( $ctx['meta']['meta_description'] ) ) {
	$_seo_desc = wp_strip_all_tags( (string) $ctx['meta']['meta_description'] );
} elseif ( ! empty( $ctx['hero']['description'] ) ) {
	$_seo_desc = wp_strip_all_tags( (string) $ctx['hero']['description'] );
}
$_seo_slug = isset( $ctx['slug'] ) ? sanitize_key( (string) $ctx['slug'] ) : '';

/* Resolve hero image URL for og:image / twitter:image */
$_seo_image_cat   = '';
$_cat_hero_img_id = ! empty( $ctx['hero']['image_id'] ) ? (int) $ctx['hero']['image_id'] : 0;
if ( $_cat_hero_img_id > 0 ) {
	$_cat_img_url   = wp_get_attachment_image_url( $_cat_hero_img_id, 'large' );
	$_seo_image_cat = $_cat_img_url ? (string) $_cat_img_url : '';
}

/* Keywords/tags: category title drives both the keyword meta and og:article:tag */
$_cat_keywords = array_values( array_filter( array( $_seo_title ) ) );

/* FAQPage schema — pass FAQ items registered via AH_Category_Settings → faqs */
$_cat_schema_faqs = array();
if ( ! empty( $ctx['faqs']['items'] ) && is_array( $ctx['faqs']['items'] ) ) {
	foreach ( $ctx['faqs']['items'] as $_faq ) {
		$_fq = trim( (string) ( $_faq['question'] ?? '' ) );
		$_fa = trim( wp_strip_all_tags( (string) ( $_faq['answer'] ?? '' ) ) );
		if ( '' !== $_fq && '' !== $_fa ) {
			$_cat_schema_faqs[] = array( 'question' => $_fq, 'answer' => $_fa );
		}
	}
}

/* CollectionPage items: child topic guide cards */
$_cat_col_items = array();
if ( ! empty( $ctx['guides']['items'] ) && is_array( $ctx['guides']['items'] ) ) {
	foreach ( $ctx['guides']['items'] as $_gi ) {
		$_gtitle = (string) ( $_gi['title'] ?? $_gi['name'] ?? '' );
		$_gurl   = (string) ( $_gi['url']   ?? '' );
		if ( '' !== $_gtitle && '' !== $_gurl ) {
			$_cat_col_items[] = array( 'title' => $_gtitle, 'url' => $_gurl );
		}
	}
}

adn_seo_register( array(
	'title'             => $_seo_title,
	'description'       => $_seo_desc,
	'canonical'         => '' !== $_seo_slug ? home_url( '/' . $_seo_slug . '/' ) : '',
	'breadcrumb'        => isset( $ctx['breadcrumb'] ) ? $ctx['breadcrumb'] : array(),
	'image'             => $_seo_image_cat,
	'keywords'          => $_cat_keywords,
	'tags'              => $_cat_keywords,
	'article_section'   => $_seo_title,
	'schema_faqs'       => $_cat_schema_faqs,
	'schema_collection' => ! empty( $_cat_col_items ) ? array(
		'name'        => $_seo_title,
		'description' => $_seo_desc,
		'url'         => '' !== $_seo_slug ? home_url( '/' . $_seo_slug . '/' ) : '',
		'items'       => $_cat_col_items,
	) : array(),
) );

// Breadcrumb renders inside the hero banner - skip it from adn_page_open().
$_open_ctx                = $ctx;
$_open_ctx['breadcrumb']  = array();
adn_page_open( $_open_ctx );
?>

<?php /* ============================== HERO ============================== */ ?>
<?php adn_component( 'sections/page_hero', array(
	'hero'       => $ctx['hero'],
	'breadcrumb' => $ctx['breadcrumb'],
) ); ?>

<?php /* ============================== CONTROL CENTRE ============================== */ ?>
<?php if ( ! empty( $ctx['journey'] ) || ! empty( $ctx['spotlights']['terms'] ) ) : ?>
	<?php adn_component( 'sections/category_control_center', array(
		'journey'    => $ctx['journey'],
		'spotlights' => isset( $ctx['spotlights'] ) ? $ctx['spotlights'] : array(),
	) ); ?>
<?php endif; ?>

<?php /* ============================== MAIN + SIDEBAR ============================== */ ?>
<div class="container parent-term-category-list">
	<div class="page-with-sidebar">

		<main class="cat-guide-main">

			<?php /* ── Guides Grid ── */ ?>
			<?php if ( ! empty( $ctx['guides']['items'] ) ) : ?>
			<div class="category-section category-guides">
				<?php adn_component( 'parts/section_headers/section_header', array(
					'heading' => isset( $ctx['guides']['heading'] ) ? $ctx['guides']['heading'] : array(),
					'tag'     => 'h2',
				) ); ?>
				<div class="cat-guides-grid">
					<?php foreach ( (array) $ctx['guides']['items'] as $_cg_card ) : ?>
						<?php adn_component( 'cards/guide_card', array( 'card' => $_cg_card ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php /* ── Popular Posts (admin-curated) ── */ ?>
			<?php if ( ! empty( $ctx['popular_posts']['items'] ) ) : ?>
			<div class="category-section category-popular">
				<?php adn_component( 'parts/section_headers/section_header', array(
					'heading' => isset( $ctx['popular_posts']['heading'] ) ? $ctx['popular_posts']['heading'] : array(),
					'tag'     => 'h2',
				) ); ?>
				<div class="cat-guides-grid">
					<?php foreach ( (array) $ctx['popular_posts']['items'] as $_cg_card ) : ?>
						<?php adn_component( 'cards/guide_card', array( 'card' => $_cg_card ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>


			<?php /* ── Resources (library items) ── */ ?>
			<?php if ( ! empty( $ctx['resources']['items'] ) ) : ?>
			<div class="category-section">
				<?php adn_component( 'sections/category_resources', array( 'resources' => $ctx['resources'] ) ); ?>
			</div>
			<?php endif; ?>

		</main>

		<aside class="sidebar-col">

			<?php /* ── Hot Topics ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['hot_topics'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_hot_topics', array( 'hot_topics' => $ctx['sidebar']['hot_topics'] ) ); ?>
			<?php endif; ?>

			<?php /* ── Featured Topics ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['featured_topics'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_featured_topics', array( 'featured_topics' => $ctx['sidebar']['featured_topics'] ) ); ?>
			<?php endif; ?>

			<?php /* ── Expert Help / Need Help From a Professional? ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['expert_help'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_expert_help', array( 'expert_help' => $ctx['sidebar']['expert_help'] ) ); ?>
			<?php endif; ?>

			<?php /* ── Quick Links ── */ ?>
			<?php
			$_cat_all  = class_exists( 'AH_Category_Settings' ) ? AH_Category_Settings::get_all( $ctx['slug'] ) : array();
			$_ql_d     = isset( $_cat_all['quick_links'] ) && is_array( $_cat_all['quick_links'] ) ? $_cat_all['quick_links'] : array();
			if ( ! empty( $_ql_d['items'] ) ) :
				adn_component( 'parts/quick_links_widget', array( 'quick_links' => $_ql_d ) );
			endif; ?>

		</aside>

	</div>
</div>

<?php /* ============================== TOOLS CAROUSEL (full-width) ============================== */ ?>
<?php if ( ! empty( $ctx['calculators']['items'] ) ) : ?>
<section class="cat-guide-tools-section">
	<div class="container">
		<?php adn_component( 'parts/section_headers/section_header', array(
			'heading' => isset( $ctx['calculators']['heading'] ) ? $ctx['calculators']['heading'] : array(),
			'tag'     => 'h2',
		) ); ?>
		<div class="cgt-carousel-wrap">
			<div class="cgt-track">
				<?php foreach ( (array) $ctx['calculators']['items'] as $_tc ) : ?>
					<?php adn_component( 'cards/tool_card', array( 'card' => $_tc ) ); ?>
				<?php endforeach; ?>
			</div>
			<button class="cgt-arrow cgt-arrow--prev" aria-label="<?php esc_attr_e( 'Previous', ADN_TEXT_DOMAIN ); ?>">
				<i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
			</button>
			<button class="cgt-arrow cgt-arrow--next" aria-label="<?php esc_attr_e( 'Next', ADN_TEXT_DOMAIN ); ?>">
				<i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
			</button>
		</div>
	</div>
</section>
<script>
(function(){
	var wrap  = document.currentScript.previousElementSibling;
	var track = wrap.querySelector('.cgt-track');
	var prev  = wrap.querySelector('.cgt-arrow--prev');
	var next  = wrap.querySelector('.cgt-arrow--next');
	if(!track||!prev||!next){ return; }
	function cardW(){ var c=track.children[0]; return c ? c.offsetWidth + parseInt(getComputedStyle(track).gap||0) : 320; }
	function upd(){
		prev.classList.toggle('cgt-arrow--hidden', track.scrollLeft <= 2);
		next.classList.toggle('cgt-arrow--hidden', track.scrollLeft >= track.scrollWidth - track.clientWidth - 2);
	}
	prev.addEventListener('click', function(){ track.scrollBy({left:-cardW()*2, behavior:'smooth'}); });
	next.addEventListener('click', function(){ track.scrollBy({left: cardW()*2, behavior:'smooth'}); });
	track.addEventListener('scroll', upd, {passive:true});
	upd();
}());
</script>
<?php endif; ?>

<?php /* ============================== CTA BANNER (interruption after guides) ============================== */ ?>
<?php if ( ! empty( $ctx['cta_banner']['title'] ) ) : ?>
<div class="cat-cta-wrap cat-cta-wrap--inline">
	<?php adn_component( 'parts/cta_banner', array( 'cta_banner' => $ctx['cta_banner'] ) ); ?>
</div>
<?php endif; ?>

<?php /* ============================== LATEST NEWS + UPDATES (full-width) ============================== */ ?>
<?php if ( ! empty( $ctx['news']['items'] ) || ! empty( $ctx['regulations']['items'] ) ) : ?>
<section class="news-three-col">
	<div class="container">
		<?php adn_component( 'sections/news_three_col', array(
			'news'        => $ctx['news'],
			'regulations' => $ctx['regulations'],
		) ); ?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== FAQs ============================== */ ?>
<?php if ( ! empty( $ctx['faqs']['items'] ) ) : ?>
<div class="section-faqs">
	<?php adn_component( 'parts/faq_list', array(
		'faqs'    => $ctx['faqs']['items'],
		'heading' => $ctx['faqs']['heading'] ?? '',
	) ); ?>
</div>
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
$_fi_cat_d   = isset( $ctx['slug'] ) ? AH_Category_Settings::get_all( sanitize_key( $ctx['slug'] ) ) : array();
$_fi_cat_sec = ( isset( $_fi_cat_d['featured_in']['section'] ) && '' !== $_fi_cat_d['featured_in']['section'] )
	? sanitize_key( $_fi_cat_d['featured_in']['section'] ) : '';
adn_component( 'parts/featured_in', array( 'section' => $_fi_cat_sec ) );
?>

<?php adn_page_close( $ctx ); ?>
