<?php
/**
 * pages/page-topic_category_guide.php
 *
 * Topic/category listing page - articles within one taxonomy term.
 *
 * Layout:
 *   1. Hero (term name, parent label, description) + breadcrumb
 *   2. page-with-sidebar:
 *      Main  - article grid + pagination
 *      Sidebar - sibling topics, quick tools, expert help
 *   3. Related categories (full-width)
 *   4. Calculators (full-width)
 *   5. CTA banner + Newsletter
 *
 * RULE: No hardcoded content and no data reads here - only structure.
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_topic_category_logical.php';
$ctx = adn_topic_category_get_context();

$term      = $ctx['term'];
$parent    = $ctx['parent'];
$term_name = $term ? (string) $term->name : '';

$_open_ctx               = $ctx;
$_open_ctx['breadcrumb'] = array();
adn_page_open( $_open_ctx );
?>

<?php /* ============================== HERO ============================== */ ?>
<?php adn_component( 'sections/page_hero', array(
	'hero'       => $ctx['hero'],
	'breadcrumb' => $ctx['breadcrumb'],
) ); ?>

<?php /* ============================== MAIN + SIDEBAR ============================== */ ?>
<div class="container">
	<div class="page-with-sidebar topic-listing-layout">

		<main class="topic-listing-main">

			<?php /* ── Category Search ── */ ?>
			<div class="cat-search-bar">
				<div class="cat-search-wrap">
					<form class="cat-search-form"
						method="get"
						action="<?php echo esc_url( trailingslashit( $ctx['search']['base_url'] ) ); ?>"
						role="search"
						data-suggest="<?php echo esc_url( rest_url( 'wp/v2/search' ) ); ?>"
					>
						<label class="screen-reader-text" for="cat-search-input"><?php echo esc_html( sprintf( __( 'Search %s guides', ADN_TEXT_DOMAIN ), $term_name ) ); ?></label>
						<div class="cat-search-inner">
							<span class="cat-search-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
							<input
								id="cat-search-input"
								type="search"
								name="search"
								class="cat-search-input"
								value="<?php echo esc_attr( $ctx['search']['query'] ); ?>"
								placeholder="<?php echo esc_attr( sprintf( __( 'Search %s guides…', ADN_TEXT_DOMAIN ), $term_name ) ); ?>"
								autocomplete="off"
								aria-autocomplete="list"
								aria-expanded="false"
							>
							<?php if ( $ctx['search']['query'] !== '' ) : ?>
								<a href="<?php echo esc_url( trailingslashit( $ctx['search']['base_url'] ) ); ?>" class="cat-search-clear" aria-label="<?php esc_attr_e( 'Clear search', ADN_TEXT_DOMAIN ); ?>"><i class="fa-solid fa-xmark"></i></a>
							<?php endif; ?>
							<button type="submit" class="cat-search-btn btn btn-primary"><?php esc_html_e( 'Search', ADN_TEXT_DOMAIN ); ?></button>
						</div>
					</form>
					<div class="js-suggest search-suggest" hidden role="listbox"></div>
				</div>
				<?php if ( $ctx['search']['query'] !== '' ) : ?>
					<p class="cat-search-results-note">
						<?php echo esc_html( sprintf( __( 'Showing results for "%s" in %s', ADN_TEXT_DOMAIN ), $ctx['search']['query'], $term_name ) ); ?>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $ctx['articles'] ) ) : ?>
				<?php adn_component( 'parts/section_headers/section_header', array(
					'heading' => array(
						'title'      => $term_name . ' Guides',
						'link_label' => '',
						'link_url'   => '',
					),
					'tag' => 'h2',
				) ); ?>

				<div class="topic-articles-grid">
					<?php foreach ( $ctx['articles'] as $article ) : ?>
						<?php adn_component( 'cards/guide_listing_card', array( 'item' => $article ) ); ?>
					<?php endforeach; ?>
				</div>

				<?php /* Pagination */ ?>
				<?php
				$_pag  = $ctx['pagination'];
				$_cur  = isset( $_pag['current'] )  ? (int) $_pag['current']  : 1;
				$_tot  = isset( $_pag['total'] )    ? (int) $_pag['total']    : 1;
				$_base = isset( $_pag['base_url'] ) ? trailingslashit( $_pag['base_url'] ) : '';
				if ( $_tot > 1 ) :
					$links = paginate_links( array(
						'base'      => add_query_arg( 'paged', '%#%', $_base ),
						'format'    => '',
						'current'   => $_cur,
						'total'     => $_tot,
						'prev_text' => '&laquo; ' . __( 'Previous', ADN_TEXT_DOMAIN ),
						'next_text' => __( 'Next', ADN_TEXT_DOMAIN ) . ' &raquo;',
						'type'      => 'array',
						'end_size'  => 2,
						'mid_size'  => 1,
					) );
					if ( ! empty( $links ) ) :
				?>
				<nav class="topic-pagination" aria-label="<?php esc_attr_e( 'Page navigation', ADN_TEXT_DOMAIN ); ?>">
					<?php foreach ( $links as $link ) : ?>
						<?php echo wp_kses( $link, array(
							'a'    => array( 'href' => true, 'class' => true, 'aria-current' => true ),
							'span' => array( 'class' => true, 'aria-current' => true ),
						) ); ?>
					<?php endforeach; ?>
				</nav>
				<?php endif; endif; ?>

			<?php else : ?>
				<div class="cat-no-results">
					<span class="cat-no-results-icon" aria-hidden="true"><i class="fa-regular fa-file-lines"></i></span>
					<?php if ( $ctx['search']['query'] !== '' ) : ?>
						<h3 class="cat-no-results-title"><?php echo esc_html( sprintf( __( 'No results for "%s"', ADN_TEXT_DOMAIN ), $ctx['search']['query'] ) ); ?></h3>
						<p class="cat-no-results-sub"><?php esc_html_e( 'Try a different search term or browse all guides below.', ADN_TEXT_DOMAIN ); ?></p>
						<a href="<?php echo esc_url( trailingslashit( $ctx['search']['base_url'] ) ); ?>" class="btn btn-secondary cat-no-results-btn"><?php esc_html_e( 'Clear search', ADN_TEXT_DOMAIN ); ?></a>
					<?php else : ?>
						<h3 class="cat-no-results-title"><?php esc_html_e( 'No guides yet', ADN_TEXT_DOMAIN ); ?></h3>
						<p class="cat-no-results-sub"><?php esc_html_e( 'We\'re working on guides for this topic. Check back soon.', ADN_TEXT_DOMAIN ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</main>

		<aside class="sidebar-col topic-listing-sidebar">

			<?php /* Sibling topics — reuse sidebar_guide_parents */ ?>
			<?php if ( ! empty( $ctx['sidebar']['buying_topics']['items'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_guide_parents', array(
					'guide_parents' => $ctx['sidebar']['buying_topics'],
				) ); ?>
			<?php endif; ?>

			<?php /* Quick tools */ ?>
			<?php if ( ! empty( $ctx['sidebar']['quick_tools']['items'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_quick_tools', array(
					'quick_tools' => $ctx['sidebar']['quick_tools'],
				) ); ?>
			<?php endif; ?>

			<?php /* Latest updates for this category */ ?>
			<?php if ( ! empty( $ctx['sidebar']['latest_updates'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_link_list', array( 'list' => array(
					'heading'  => 'Latest Updates',
					'items'    => $ctx['sidebar']['latest_updates'],
					'view_all' => array( 'label' => 'All updates →', 'url' => home_url( '/' . $ctx['slug'] . '/' ) ),
				) ) ); ?>
			<?php endif; ?>

			<?php /* Contact / expert help CTA */ ?>
			<?php if ( ! empty( $ctx['sidebar']['expert_help'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_expert_help', array(
					'expert_help' => $ctx['sidebar']['expert_help'],
				) ); ?>
			<?php endif; ?>
		</aside>

	</div>
</div>

<?php /* ============================== FEATURED / POPULAR / SUGGESTED ============================== */ ?>
<?php if ( ! empty( $ctx['highlight_posts'] ) ) :
	$_hl      = $ctx['highlight_posts'];
	$_f_items = ! empty( $_hl['featured']['items'] )  ? $_hl['featured']['items']  : array();
	$_p_items = ! empty( $_hl['popular']['items'] )   ? $_hl['popular']['items']   : array();
	$_s_items = ! empty( $_hl['suggested']['items'] ) ? $_hl['suggested']['items'] : array();
?>
<section class="cat-highlight-section">
	<div class="container">
		<?php adn_component( 'sections/news_three_col', array(
			'news' => array(
				'heading' => array( 'title' => '⭐ Featured', 'link_label' => '', 'link_url' => '' ),
				'items'   => $_f_items,
			),
			'regulations' => array(
				'heading' => array( 'title' => '🔥 Popular' ),
				'items'   => $_p_items,
			),
			'hot_topics' => array(
				'title' => '💡 Suggested',
				'items' => $_s_items,
				'cta'   => array(),
			),
		) ); ?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== MORE TOPICS ============================== */ ?>
<?php if ( ! empty( $ctx['related_categories'] ) ) : ?>
<section class="cat-guide-related-section">
	<div class="container">
		<?php adn_component( 'parts/section_headers/section_header', array(
			'heading' => array(
				'title'      => 'More ' . ( $parent ? esc_html( $parent->name ) : 'Topic' ) . ' Guides',
				'link_label' => $parent ? 'View all →' : '',
				'link_url'   => $parent ? home_url( '/' . trim( $parent->slug, '/' ) . '/' ) : '',
			),
			'tag' => 'h2',
		) ); ?>
		<?php adn_component( 'sections/guides', array( 'items' => $ctx['related_categories'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== TOOLS ============================== */ ?>
<?php if ( ! empty( $ctx['calculators']['items'] ) ) : ?>
<section class="cat-guide-tools-section">
	<div class="container">
		<?php adn_component( 'parts/section_headers/section_header', array(
			'heading' => $ctx['calculators']['heading'],
			'tag'     => 'h2',
		) ); ?>
		<div class="topic-calc-carousel-wrap">
			<div class="topic-calc-carousel">
				<?php foreach ( $ctx['calculators']['items'] as $card ) : ?>
					<?php adn_component( 'cards/tool_card', array( 'card' => $card ) ); ?>
				<?php endforeach; ?>
			</div>
		</div>
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

<?php adn_page_close( $ctx ); ?>
