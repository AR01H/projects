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
$hero_info = (array) ( $blog_data['hero'] ?? array() );
$articles  = (array) ( $blog_data['articles'] ?? array() );

if ( empty( $articles ) ) {
	return;
}

$tag   = (string) ( $hero_info['tag'] ?? '' );
$title = (string) ( $hero_info['title'] ?? '' );
$sub   = (string) ( $hero_info['sub'] ?? '' );

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
		<?php if ( '' !== $title || '' !== $tag ) : ?>
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => $tag,
					'title' => $title,
					'sub'   => $sub,
				)
			);
			?>
		<?php endif; ?>

		<!-- 3-Card Luxury Vintage Grid -->
		<div class="blog-grid" style="margin-bottom: 32px;">
			<?php foreach ( $display_articles as $art ) :
				$title   = (string) ( $art['title'] ?? '' );
				$link    = (string) ( $art['permalink'] ?? '#' );
				$img     = (string) ( $art['image'] ?? '' );
				$cat     = (string) ( $art['category'] ?? '' );
				$date    = (string) ( $art['date'] ?? '' );
				$author  = (string) ( $art['author'] ?? '' );
				$read    = (int) ( $art['reading_time'] ?? 4 );
				$excerpt = (string) ( $art['excerpt'] ?? '' );
			?>
				<article class="blog-card" data-category="<?php echo esc_attr( $cat ); ?>">
					<?php if ( '' !== $img ) : ?>
						<div class="blog-card__media frame--ornate-sm">
							<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
							<?php if ( '' !== $cat ) : ?>
								<span class="blog-card__category"><?php echo esc_html( $cat ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<div class="blog-card__meta">
						<?php if ( '' !== $date ) : ?>
							<span class="blog-card__date"><?php echo esc_html( $date ); ?></span>
						<?php endif; ?>
						<?php if ( $read > 0 ) : ?>
							<span class="blog-card__reading"><?php echo esc_html( (string) $read ); ?> min read</span>
						<?php endif; ?>
					</div>
					<h3 class="blog-card__title">
						<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
					</h3>
					<?php if ( '' !== $excerpt ) : ?>
						<p class="blog-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
					<?php endif; ?>
					<div class="blog-card__footer">
						<?php if ( '' !== $author ) : ?>
							<span class="blog-card__author">By <?php echo esc_html( $author ); ?></span>
						<?php endif; ?>
						<a class="blog-card__link" href="<?php echo esc_url( $link ); ?>">Read Story →</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<div class="blog-showcase__actions" style="text-align: center; margin-top: 20px;">
			<a class="btn btn--secondary-vintage" href="<?php echo esc_url( RouteService::url( 'blog' ) ); ?>">
				<span>VIEW ALL CHRONICLES ✦</span>
			</a>
		</div>

	</div>
</section>
