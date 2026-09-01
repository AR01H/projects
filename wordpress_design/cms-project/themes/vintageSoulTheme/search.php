<?php
/**
 * VintageSoulTheme - Masterpiece Search Results Page
 *
 * Rich vintage parchment search template with subpage hero header,
 * on-page refinement search bar, ornate search result cards, post type badges,
 * fallbacks for pages/posts, and vintage pagination.
 */

use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\PostHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

get_header();

global $wp_query;
$query_str   = get_search_query();
$total_posts = $wp_query->found_posts;
?>
<main id="main" class="main search-page-main">

	<!-- Subtle Botanical Background Particle Layer -->
	<?php View::component( 'background/parchment-botanical-bg', array( 'seed' => 88 ) ); ?>

	<!-- ═══════════ 1. SEARCH HEADER HERO ═══════════ -->
	<section class="section search-hero-section">
		<div class="container container--narrow">
			<div class="search-hero-box frame--ornate">
				<span class="vintage-ribbon-tag"><?php esc_html_e( 'Cane House Archives', 'vintagesoul' ); ?></span>
				<h1 class="search-hero-title">
					<?php
					if ( '' !== trim( $query_str ) ) {
						/* translators: %s: search query */
						printf( esc_html__( 'Search Results For: %s', 'vintagesoul' ), '<em>“' . esc_html( $query_str ) . '”</em>' );
					} else {
						esc_html_e( 'Search The Archives', 'vintagesoul' );
					}
					?>
				</h1>
				<p class="search-hero-sub">
					<?php
					if ( $total_posts > 0 ) {
						/* translators: %d: number of results */
						printf( esc_html__( 'Discovered %d matching chronicle(s) across our recipes, menu items, and heritage notes.', 'vintagesoul' ), (int) $total_posts );
					} else {
						esc_html_e( 'Explore our botanical stories, catering packages, and craft sugarcane chronicles.', 'vintagesoul' );
					}
					?>
				</p>

				<!-- On-Page Search Input Bar -->
				<form role="search" method="get" class="search-hero-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<div class="search-hero-input-wrap">
						<span class="search-hero-icon"><?php echo IconHelper::render( 'search', '#8e622d', 18 ); // phpcs:ignore ?></span>
						<input type="search" class="search-hero-input" placeholder="<?php esc_attr_e( 'Search drinks, catering, history, franchise...', 'vintagesoul' ); ?>" value="<?php echo esc_attr( $query_str ); ?>" name="s" required>
						<button type="submit" class="btn btn--order-now search-hero-submit">
							<span><?php esc_html_e( 'SEARCH', 'vintagesoul' ); ?></span>
						</button>
					</div>
				</form>
			</div>
		</div>
	</section>

	<!-- ═══════════ 2. RESULTS GRID ═══════════ -->
	<section class="section search-results-section">
		<div class="container">
			
			<?php if ( have_posts() ) : ?>
				<div class="search-results-grid">
					<?php while ( have_posts() ) : the_post();
						$post_id   = get_the_ID();
						$post_slug = (string) get_post_field( 'post_name', $post_id );
						if ( 'sample-page' === $post_slug ) {
							continue;
						}
						$post_type = get_post_type();
						$type_lbl  = 'page' === $post_type ? 'PAGE' : ( 'post' === $post_type ? 'ARTICLE' : strtoupper( $post_type ) );
						$has_thumb = has_post_thumbnail( $post_id );
						$thumb_url = $has_thumb ? get_the_post_thumbnail_url( $post_id, 'medium_large' ) : UrlHelper::resolve( 'assets/images/sugarcane/story_moments.jpg' );
						$date_str  = get_the_date( 'M j, Y' );
					?>
						<article class="search-result-card frame--rough-cut" id="post-<?php echo esc_attr( $post_id ); ?>">
							
							<!-- Thumbnail / Visual -->
							<a class="search-result-card__media" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
								<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
								<span class="search-result-card__type-badge"><?php echo esc_html( $type_lbl ); ?></span>
							</a>

							<!-- Body Content -->
							<div class="search-result-card__body">
								<?php if ( 'page' !== $post_type ) : ?>
									<div class="search-result-card__meta">
										<span class="search-result-card__date">
											<?php echo IconHelper::render( 'calendar', '#8e622d', 13 ); // phpcs:ignore ?>
											<?php echo esc_html( $date_str ); ?>
										</span>
									</div>
								<?php endif; ?>

								<h2 class="search-result-card__title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h2>

								<p class="search-result-card__excerpt">
									<?php echo esc_html( PostHelper::excerpt( $post_id, 22 ) ); ?>
								</p>

								<div class="search-result-card__footer">
									<a class="search-result-card__link" href="<?php the_permalink(); ?>">
										<span><?php esc_html_e( 'READ CHRONICLE', 'vintagesoul' ); ?></span>
										<span class="link-arrow">→</span>
									</a>
								</div>
							</div>

						</article>
					<?php endwhile; ?>
				</div>

				<!-- Pagination -->
				<div class="search-pagination-wrap">
					<?php
					the_posts_pagination(
						array(
							'mid_size'  => 2,
							'prev_text' => '← ' . __( 'Previous', 'vintagesoul' ),
							'next_text' => __( 'Next', 'vintagesoul' ) . ' →',
						)
					);
					?>
				</div>

			<?php else : ?>
				
				<!-- Empty Results State -->
				<div class="search-empty-box frame--ornate">
					<div class="search-empty-icon">🌾</div>
					<h2 class="search-empty-title"><?php esc_html_e( 'NO MATCHING RECORDS FOUND', 'vintagesoul' ); ?></h2>
					<p class="search-empty-desc">
						<?php esc_html_e( 'We could not find any archives matching your query. Try searching for different terms such as "events", "menu", "history", or "catering".', 'vintagesoul' ); ?>
					</p>
					<div class="search-empty-actions">
						<a class="btn btn--primary-vintage" href="<?php echo esc_url( home_url( '/' ) ); ?>">
							<span><?php esc_html_e( 'RETURN TO HOME', 'vintagesoul' ); ?></span>
						</a>
						<a class="btn btn--secondary-vintage" href="<?php echo esc_url( RouteService::url( 'history' ) ?: home_url( '/history' ) ); ?>">
							<span><?php esc_html_e( 'EXPLORE CANE HISTORY', 'vintagesoul' ); ?></span>
						</a>
					</div>
				</div>

			<?php endif; ?>

		</div>
	</section>

</main>
<?php
get_footer();
