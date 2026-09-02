<?php
/**
 * VintageSoulTheme - Single Blog Article View with Sticky Editorial Sidebar
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
				'id'          => 'article-hero',
				'tag'         => '✦ ' . $cat_name . ' ✦',
				'title'       => get_the_title(),
				'sub'         => get_the_date( 'j F Y' ) . ' • ' . (int) ( $data['reading_time'] ?? 4 ) . ' min read',
				'image'       => 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg',
				'share_url'   => $permalink,
				'share_title' => $post_title,
			)
		);
		?>

		<div class="section single-article-section paper-rough">
			<div class="container single-article-layout">
				
				<!-- ═══════════ MAIN ARTICLE COLUMN ═══════════ -->
				<div class="single-article-main-col">
					
					<article class="single-article-card frame--ornate" id="post-<?php the_ID(); ?>">
						
						<?php if ( $thumb_url ) : ?>
							<div class="single-article__featured-img frame--ornate">
								<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>">
							</div>
						<?php endif; ?>

						<!-- Article Content with Rich Editorial Hierarchy -->
						<div class="single-article__content entry-content">
							<?php the_content(); ?>
						</div>

					</article>

					<!-- Comments section hidden for now -->
					<?php if ( false && ( comments_open() || get_comments_number() ) ) : ?>
						<div class="single-article-comments-wrap">
							<?php comments_template(); ?>
						</div>
					<?php endif; ?>

				</div>

				<!-- ═══════════ STICKY EDITORIAL SIDEBAR (Laptop / Desktop) ═══════════ -->
				<aside class="single-article__sidebar">
					<div class="single-article__sidebar-sticky">
						
						<!-- Sidebar Widget 1: Related / Recent Chronicles -->
						<div class="sidebar-widget frame--ornate">
							<div class="sidebar-widget__header">
								<span class="sidebar-widget__badge">✦ DISCOVER ✦</span>
								<h3 class="sidebar-widget__title"><?php esc_html_e( 'RELATED CHRONICLES', 'vintagesoul' ); ?></h3>
							</div>
							<div class="sidebar-widget__list">
								<?php
								$sidebar_posts = get_posts(
									array(
										'post_type'      => 'post',
										'post_status'    => 'publish',
										'posts_per_page' => 4,
										'post__not_in'   => array( $post_id ),
									)
								);
								if ( ! empty( $sidebar_posts ) ) :
									foreach ( $sidebar_posts as $sp ) :
										$sp_thumb = get_the_post_thumbnail_url( $sp->ID, 'thumbnail' ) ?: UrlHelper::resolve( 'assets/images/sugarcane/story_moments.jpg' );
									?>
										<a href="<?php echo esc_url( get_permalink( $sp ) ); ?>" class="sidebar-post-item">
											<div class="sidebar-post-item__thumb">
												<img src="<?php echo esc_url( $sp_thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $sp ) ); ?>" loading="lazy">
											</div>
											<div class="sidebar-post-item__meta">
												<span class="sidebar-post-item__date"><?php echo esc_html( get_the_date( 'j M Y', $sp ) ); ?></span>
												<h4 class="sidebar-post-item__title"><?php echo esc_html( get_the_title( $sp ) ); ?></h4>
											</div>
										</a>
									<?php
									endforeach;
								else :
									?>
									<p class="sidebar-empty-msg"><?php esc_html_e( 'Stay tuned for more stories.', 'vintagesoul' ); ?></p>
								<?php endif; ?>
							</div>
						</div>

						<!-- Sidebar Widget 2: Explore Topics -->
						<?php
						$categories = get_categories( array( 'hide_empty' => true ) );
						if ( ! empty( $categories ) ) :
						?>
							<div class="sidebar-widget frame--ornate">
								<div class="sidebar-widget__header">
									<h3 class="sidebar-widget__title"><?php esc_html_e( 'EXPLORE TOPICS', 'vintagesoul' ); ?></h3>
								</div>
								<div class="sidebar-category-tags">
									<?php foreach ( $categories as $cat ) : ?>
										<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="sidebar-cat-tag">
											<span class="cat-tag__name"><?php echo esc_html( $cat->name ); ?></span>
											<span class="cat-tag__count"><?php echo esc_html( $cat->count ); ?></span>
										</a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

						<!-- Sidebar Widget 4: Concierge & Quick Links -->
						<div class="sidebar-widget sidebar-widget--concierge frame--ornate">
							<div class="sidebar-widget__header">
								<h3 class="sidebar-widget__title"><?php esc_html_e( 'LONDON PARLOUR', 'vintagesoul' ); ?></h3>
							</div>
							<p class="sidebar-concierge__text">
								<?php esc_html_e( 'Have questions about our botanical drinks or franchise opportunities?', 'vintagesoul' ); ?>
							</p>
							<a href="<?php echo esc_url( RouteService::url( 'contact' ) ?: home_url( '/contact' ) ); ?>" class="btn btn--secondary-vintage" style="width: 100%; text-align: center;">
								<span>GET IN TOUCH</span>
							</a>
						</div>

					</div>
				</aside>

			</div>
		</div>
	<?php endwhile; ?>
</main>
<?php
get_footer();
