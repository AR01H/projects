<?php
/**
 * VintageSoulTheme - Latest Stories & Blog Showcase Section
 *
 * Renders a luxury 3-card grid of latest/random botanical stories with
 * deckle rough cuts, category badges, reading time indicators, and link to /blog.
 */
use VintageSoul\Controllers\BlogController;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$blog_ctrl = new BlogController();
$blog_data = $blog_ctrl->prepare();
$articles  = (array) ( $blog_data['articles'] ?? array() );

if ( empty( $articles ) ) {
	return;
}

// Pick 3 random or latest articles
$display_articles = $articles;
if ( count( $display_articles ) > 3 ) {
	shuffle( $display_articles );
	$display_articles = array_slice( $display_articles, 0, 3 );
}
?>
<link rel="stylesheet" href="<?php echo esc_url( \VintageSoul\Support\UrlHelper::resolve( 'assets/css/pages/blog.css' ) ); ?>">

<section class="section section--blog-showcase" id="blog-stories" style="position:relative;">
	<div class="container">
		
		<!-- Section Header -->
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'   => 'The Cane Chronicle',
				'title' => 'LATEST STORIES & <em>Insights</em>',
			)
		);
		?>
		<p class="about-intro__sub" style="text-align:center; max-width:720px; margin:-10px auto 36px;">
			Explore our latest writings on ancient sugarcane cultivation, raw cold-press nutrition, London market life, and botanical recipe pairings.
		</p>

		<!-- 3-Card Luxury Vintage Grid -->
		<div class="blog-grid" style="margin-bottom: 32px;">
			<?php foreach ( $display_articles as $art ) :
				$title   = (string) ( $art['title'] ?? '' );
				$link    = (string) ( $art['permalink'] ?? '#' );
				$img     = (string) ( $art['image'] ?? '' );
				$cat     = (string) ( $art['category'] ?? 'Heritage' );
				$date    = (string) ( $art['date'] ?? '' );
				$author  = (string) ( $art['author'] ?? 'The Cane House' );
				$read    = (int) ( $art['reading_time'] ?? 4 );
				$excerpt = (string) ( $art['excerpt'] ?? '' );
			?>
				<article class="blog-card" data-category="<?php echo esc_attr( $cat ); ?>">
					<div class="blog-card__media frame--ornate-sm">
						<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
						<span class="blog-card__category"><?php echo esc_html( $cat ); ?></span>
					</div>
					<div class="blog-card__meta">
						<span>📅 <?php echo esc_html( $date ); ?></span>
						<span>•</span>
						<span>⏳ <?php echo esc_html( $read ); ?> MIN READ</span>
					</div>
					<h3 class="blog-card__title">
						<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
					</h3>
					<p class="blog-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
					<div class="blog-card__footer">
						<span class="blog-card__author">By <?php echo esc_html( $author ); ?></span>
						<a href="<?php echo esc_url( $link ); ?>" class="blog-card__btn">
							READ STORY →
						</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<!-- View All Articles CTA Button -->
		<div style="text-align: center; margin-top: 12px;">
			<a href="<?php echo esc_url( RouteService::url( 'blog' ) ); ?>" class="btn btn--secondary" style="font-size: 12px; padding: 10px 24px;">
				EXPLORE THE FULL JOURNAL (<?php echo count( $articles ); ?> ARTICLES) →
			</a>
		</div>

	</div>
</section>

<!-- Gold Wave Divider -->
<div class="gold-wave-divider" aria-hidden="true">
	<img src="<?php echo esc_url( \VintageSoul\Support\UrlHelper::resolve( 'assets/images/textures/border/gold-wave.svg' ) ); ?>" alt="" loading="lazy">
</div>
