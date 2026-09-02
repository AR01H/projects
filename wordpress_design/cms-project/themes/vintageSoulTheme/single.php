<?php
/**
 * VintageSoulTheme - Single Blog Article View
 */

use VintageSoul\Controllers\SingleController;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new SingleController() )->prepare();

get_header();
?>
<link rel="stylesheet" href="<?php echo esc_url( UrlHelper::resolve( 'assets/css/pages/blog.css' ) ); ?>">

<main id="main" class="main single-blog-main">
	
	<!-- Subtle Botanical Background Particle Layer -->
	<?php View::component( 'background/parchment-botanical-bg', array( 'seed' => 64 ) ); ?>

	<?php while ( have_posts() ) : the_post();
		$post_id     = get_the_ID();
		$thumb_id    = get_post_thumbnail_id();
		$thumb_url   = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'large' ) : UrlHelper::resolve( 'assets/images/sugarcane/hero_juice.jpg' );
		$cats        = get_the_category();
		$cat_name    = ! empty( $cats ) ? $cats[0]->name : 'Heritage Craft';
		$author_name = get_the_author_meta( 'display_name' ) ?: 'The Cane House';
		$permalink   = get_permalink();
		$post_title  = get_the_title();
	?>
		<!-- Master Common Subpage Hero -->
		<?php
		View::component(
			'subpage-hero/subpage-hero',
			array(
				'id'    => 'article-hero',
				'tag'   => '✦ ' . $cat_name . ' ✦',
				'title' => get_the_title(),
				'sub'   => 'By ' . $author_name . ' • ' . get_the_date( 'j F Y' ) . ' • ' . (int) ( $data['reading_time'] ?? 4 ) . ' min read',
				'image' => 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg',
			)
		);
		?>

		<div class="section">
			<div class="container single-article-view">
				<?php if ( $thumb_url ) : ?>
					<div class="single-article__featured-img frame--ornate">
						<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>">
					</div>
				<?php endif; ?>

				<!-- Social Sharing Bar -->
				<div class="article-social-share-bar">
					<span class="share-label"><?php esc_html_e( 'SHARE THIS CHRONICLE:', 'vintagesoul' ); ?></span>
					<div class="share-buttons">
						<a class="share-btn share-btn--whatsapp" href="https://api.whatsapp.com/send?text=<?php echo rawurlencode( $post_title . ' ' . $permalink ); ?>" target="_blank" rel="noopener" aria-label="Share on WhatsApp">
							<?php echo IconHelper::render( 'whatsapp', '#ffffff', 14 ); // phpcs:ignore ?>
							<span>WhatsApp</span>
						</a>
						<a class="share-btn share-btn--email" href="mailto:?subject=<?php echo rawurlencode( $post_title ); ?>&body=<?php echo rawurlencode( 'Read this chronicle on The Cane House: ' . $permalink ); ?>" aria-label="Share via Email">
							<?php echo IconHelper::render( 'mail', '#ffffff', 14 ); // phpcs:ignore ?>
							<span>Email</span>
						</a>
					</div>
				</div>

				<article class="single-article__content" id="post-<?php the_ID(); ?>">
					<?php the_content(); ?>
				</article>

				<!-- Article Navigation Bar -->
				<div class="single-article__nav-bar" style="margin-top: 36px; text-align: center;">
					<a href="<?php echo esc_url( RouteService::url( 'blog' ) ?: home_url( '/blog' ) ); ?>" class="btn btn--secondary-vintage" style="font-size:12px; padding:10px 24px;">
						<span>← ALL CHRONICLES</span>
					</a>
				</div>

				<!-- Related Posts Section -->
				<?php
				$related_posts = get_posts(
					array(
						'post_type'      => 'post',
						'post_status'    => 'publish',
						'posts_per_page' => 2,
						'post__not_in'   => array( $post_id ),
					)
				);
				if ( ! empty( $related_posts ) ) :
				?>
					<div class="related-chronicles-box" style="margin-top: 50px;">
						<h3 style="font-family:'Cinzel',serif; font-size:20px; color:#172b15; text-align:center; text-transform:uppercase; margin-bottom:24px; letter-spacing:0.06em;">
							<?php esc_html_e( 'MORE CHRONICLES FROM THE CANE HOUSE', 'vintagesoul' ); ?>
						</h3>
						<div class="search-results-grid">
							<?php foreach ( $related_posts as $r_post ) :
								$r_thumb = get_the_post_thumbnail_url( $r_post->ID, 'medium' ) ?: UrlHelper::resolve( 'assets/images/sugarcane/story_moments.jpg' );
							?>
								<article class="search-result-card frame--rough-cut">
									<a class="search-result-card__media" href="<?php echo esc_url( get_permalink( $r_post ) ); ?>">
										<img src="<?php echo esc_url( $r_thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $r_post ) ); ?>" loading="lazy">
										<span class="search-result-card__type-badge">ARTICLE</span>
									</a>
									<div class="search-result-card__body">
										<h4 class="search-result-card__title" style="font-size:16px;">
											<a href="<?php echo esc_url( get_permalink( $r_post ) ); ?>"><?php echo esc_html( get_the_title( $r_post ) ); ?></a>
										</h4>
										<p class="search-result-card__excerpt" style="font-size:13.5px;">
											<?php echo esc_html( wp_trim_words( $r_post->post_excerpt ?: $r_post->post_content, 18, '...' ) ); ?>
										</p>
										<div class="search-result-card__footer">
											<a class="search-result-card__link" href="<?php echo esc_url( get_permalink( $r_post ) ); ?>">
												<span>READ CHRONICLE</span>
												<span class="link-arrow">→</span>
											</a>
										</div>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( comments_open() || get_comments_number() ) : ?>
					<div style="margin-top:40px;">
						<?php comments_template(); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endwhile; ?>
</main>
<?php
get_footer();
