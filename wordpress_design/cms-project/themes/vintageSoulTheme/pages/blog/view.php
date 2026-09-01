<?php

use VintageSoul\Controllers\BlogController;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new BlogController() )->prepare();

$hero       = (array) ( $data['hero'] ?? array() );
$categories = (array) ( $data['categories'] ?? array() );
$articles   = (array) ( $data['articles'] ?? array() );

$requested_slug = sanitize_title( (string) ( $_GET['article'] ?? '' ) );
$current_article = null;

if ( '' !== $requested_slug ) {
	foreach ( $articles as $art ) {
		if ( (string) ( $art['slug'] ?? '' ) === $requested_slug ) {
			$current_article = $art;
			break;
		}
	}
}
?>

<link rel="stylesheet" href="<?php echo esc_url( \VintageSoul\Support\UrlHelper::resolve( 'assets/css/pages/blog.css' ) ); ?>">

<?php if ( $current_article ) : ?>
	<!-- ═══════════ SINGLE ARTICLE VIEW ═══════════ -->
	<header class="page-hero common-subpage-hero article-header-hero" id="article-hero">
		<div class="container article-header-hero__inner">
			<div class="article-header__badge-wrap">
				<span class="article-header__category">
					✦ <?php echo esc_html( (string) ( $current_article['category'] ?? 'Heritage' ) ); ?> ✦
				</span>
			</div>
			<h1 class="article-header__title"><?php echo esc_html( (string) $current_article['title'] ); ?></h1>
			<p class="article-header__meta">
				By <?php echo esc_html( (string) $current_article['author'] ); ?> • <?php echo esc_html( (string) $current_article['date'] ); ?> • <?php echo esc_html( (string) $current_article['reading_time'] ); ?> min read
			</p>
		</div>
	</header>

	<div class="gold-wave-divider" aria-hidden="true">
		<img src="<?php echo esc_url( \VintageSoul\Support\UrlHelper::resolve( 'assets/images/textures/border/gold-wave.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<div class="section">
		<div class="container single-article-view">
			<div class="single-article__featured-img">
				<img src="<?php echo esc_url( (string) $current_article['image'] ); ?>" alt="<?php echo esc_attr( (string) $current_article['title'] ); ?>">
			</div>

			<div class="single-article__content">
				<?php echo wp_kses_post( (string) $current_article['content'] ); ?>
			</div>

			<div class="single-article__nav-bar" style="margin-top: 36px; text-align: center;">
				<a href="<?php echo esc_url( RouteService::url( 'blog' ) ); ?>" class="btn btn--secondary-vintage" style="font-size:12px; padding:10px 24px;">
					<span>← BACK TO ALL CHRONICLES</span>
				</a>
			</div>
		</div>
	</div>

<?php else : ?>

	<!-- ═══════════ BLOG ARCHIVE / LISTING VIEW ═══════════ -->
	<?php
	View::component(
		'subpage-hero/subpage-hero',
		array(
			'id'    => 'blog-hero',
			'tag'   => (string) ( $hero['tag'] ?? 'Journal & Botanical Insights' ),
			'title' => 'THE CANE <em>Chronicle</em>',
			'sub'   => (string) ( $hero['sub'] ?? 'Explore our latest writings on ancient sugarcane cultivation, raw cold-press nutrition, and botanical pairings.' ),
			'image' => 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg',
		)
	);
	?>

	<div class="section">
		<div class="container">
			
			<!-- Category Filter Pills -->
			<?php if ( ! empty( $categories ) ) : ?>
				<div class="blog-categories-bar">
					<?php foreach ( $categories as $idx => $cat ) : ?>
						<button type="button" class="blog-category-pill <?php echo 0 === $idx ? 'is-active' : ''; ?>" onclick="filterBlogCards('<?php echo esc_js( $cat ); ?>', this)">
							<?php echo esc_html( $cat ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- Blog Articles Grid -->
			<div class="blog-grid" id="blog-articles-grid">
				<?php foreach ( $articles as $art ) :
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

		</div>
	</div>

	<script>
	function filterBlogCards(category, btn) {
		var pills = document.querySelectorAll('.blog-category-pill');
		pills.forEach(function(p) { p.classList.remove('is-active'); });
		if (btn) btn.classList.add('is-active');

		var cards = document.querySelectorAll('.blog-card');
		cards.forEach(function(card) {
			var cardCat = card.getAttribute('data-category');
			if (category === 'All Articles' || category === '' || cardCat === category) {
				card.style.display = 'flex';
			} else {
				card.style.display = 'none';
			}
		});
	}
	</script>

<?php endif; ?>

<!-- Featured Trust Strip -->
<?php View::component( 'sections/logo-strip-section' ); ?>

<!-- Trust Ribbon Bottom -->
<?php View::component( 'sections/trust-ribbon-section' ); ?>
