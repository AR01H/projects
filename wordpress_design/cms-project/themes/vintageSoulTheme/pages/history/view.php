<?php

use VintageSoul\Controllers\HistoryController;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new HistoryController() )->prepare();

$hero               = $data['hero'];
$accent             = $data['accent'];
$intro              = $data['intro'];
$why                = $data['why'];
$story              = $data['story'];
$history            = $data['history'];
$life_cycle         = $data['life_cycle'];
$goodness           = $data['goodness'];
$benefits           = $data['benefits'];
$uses               = $data['uses'];
$culture            = $data['culture'];
$why_everyone_loves = $data['why_everyone_loves'];
$faq                = $data['faq'];
$closing            = $data['closing'];
?>

<?php if ( ! empty( $hero ) ) : ?>
	<header class="page-hero">
		<?php if ( ! empty( $hero['image'] ) ) : ?>
			<div class="page-hero__media">
				<img src="<?php echo esc_url( (string) $hero['image'] ); ?>" alt="" loading="eager">
			</div>
		<?php endif; ?>
		<div class="container page-hero__inner">
			<span class="page-hero__flourish" aria-hidden="true"></span>
			<h1 class="page-hero__title"><?php echo esc_html( (string) ( $hero['title'] ?? '' ) ); ?></h1>
			<?php if ( ! empty( $hero['tag'] ) ) : ?>
				<p class="page-hero__tag"><?php echo esc_html( (string) $hero['tag'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $hero['sub'] ) ) : ?>
				<p class="page-hero__sub"><?php echo esc_html( (string) $hero['sub'] ); ?></p>
			<?php endif; ?>
			<span class="page-hero__flourish" aria-hidden="true"></span>
		</div>
	</header>

	<!-- Deckled Edge Divider -->
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( \VintageSoul\Support\UrlHelper::resolve( 'assets/images/textures/border/deckled-edge.svg' ) ); ?>" alt="" loading="lazy">
	</div>
<?php endif; ?>

<?php if ( ! empty( $accent ) ) : ?>
	<div class="section section--sm">
		<div class="container">
			<?php
			View::component(
				'photo-stamp/photo-stamp',
				array(
					'id'    => 'history-accent',
					'image' => (string) ( $accent['image'] ?? '' ),
					'stamp' => (array) ( $accent['stamp'] ?? array() ),
				)
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $intro ) ) : ?>
	<div class="section">
		<div class="container">
			<?php
			View::component(
				'banner/banner',
				array(
					'tag'   => (string) ( $intro['tag'] ?? '' ),
					'title' => (string) ( $intro['title'] ?? '' ),
					'sub'   => (string) ( $intro['sub'] ?? '' ),
					'image' => (string) ( $intro['image'] ?? '' ),
				)
			);
			$intro_photos = (array) ( $intro['photos'] ?? array() );
			if ( ! empty( $intro_photos ) ) :
				?>
				<div style="margin-top: var(--space-lg);">
					<?php View::component( 'gallery/gallery', array( 'items' => $intro_photos, 'id' => 'history-intro' ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $why ) ) : ?>
	<div class="section section--alt">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $why['tag'] ?? '' ),
					'title' => (string) ( $why['title'] ?? '' ),
				)
			);
			View::component(
				'feature-row/feature-row',
				array( 'items' => (array) ( $why['items'] ?? array() ) )
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $story ) ) : ?>
	<div class="section">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $story['tag'] ?? '' ),
					'title' => (string) ( $story['title'] ?? '' ),
				)
			);
			?>
			<p class="u-text-center"><?php echo esc_html( (string) ( $story['sub'] ?? '' ) ); ?></p>
			<?php
			$story_items = array_map(
				static function ( $photo ) {
					$photo = (array) $photo;
					return array(
						'src'   => (string) ( $photo['image'] ?? '' ),
						'label' => (string) ( $photo['label'] ?? '' ),
						'desc'  => (string) ( $photo['desc'] ?? '' ),
					);
				},
				(array) ( $story['photos'] ?? array() )
			);
			View::component( 'gallery/gallery', array( 'items' => $story_items, 'id' => 'history-story' ) );
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $history ) ) : ?>
	<div class="section section--alt">
		<div class="container">
			<?php
			View::component(
				'banner/banner',
				array(
					'id'    => 'history',
					'tag'   => (string) ( $history['tag'] ?? '' ),
					'title' => (string) ( $history['title'] ?? '' ),
					'sub'   => (string) ( $history['sub'] ?? '' ),
					'image' => (string) ( $history['image'] ?? '' ),
					'stamp' => (array) ( $history['stamp'] ?? array() ),
				)
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $life_cycle ) ) : ?>
	<div class="section">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $life_cycle['tag'] ?? '' ),
					'title' => (string) ( $life_cycle['title'] ?? '' ),
				)
			);
			View::component(
				'step-chain/step-chain',
				array( 'items' => (array) ( $life_cycle['items'] ?? array() ) )
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $goodness ) ) : ?>
	<div class="section section--alt">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $goodness['tag'] ?? '' ),
					'title' => (string) ( $goodness['title'] ?? '' ),
				)
			);
			View::component(
				'photo-grid/photo-grid',
				array( 'items' => (array) ( $goodness['items'] ?? array() ) )
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $benefits ) ) : ?>
	<div class="section">
		<div class="container">
			<?php
			View::component(
				'banner/banner',
				array(
					'tag'     => (string) ( $benefits['tag'] ?? '' ),
					'title'   => (string) ( $benefits['title'] ?? '' ),
					'items'   => (array) ( $benefits['items'] ?? array() ),
					'image'   => (string) ( $benefits['image'] ?? '' ),
					'variant' => 'reverse',
				)
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $uses ) ) : ?>
	<div class="section section--alt">
		<div class="container">
			<?php
			View::component(
				'banner/banner',
				array(
					'tag'   => (string) ( $uses['tag'] ?? '' ),
					'title' => (string) ( $uses['title'] ?? '' ),
					'items' => (array) ( $uses['items'] ?? array() ),
					'image' => (string) ( $uses['image'] ?? '' ),
				)
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $culture ) ) : ?>
	<div class="section">
		<div class="container">
			<?php
			View::component(
				'banner/banner',
				array(
					'id'      => 'culture',
					'tag'     => (string) ( $culture['tag'] ?? '' ),
					'title'   => (string) ( $culture['title'] ?? '' ),
					'sub'     => (string) ( $culture['sub'] ?? '' ),
					'image'   => (string) ( $culture['image'] ?? '' ),
					'stamp'   => (array) ( $culture['stamp'] ?? array() ),
					'variant' => 'reverse',
				)
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $why_everyone_loves ) ) : ?>
	<div class="section section--alt">
		<div class="container">
			<?php
			View::component(
				'banner/banner',
				array(
					'tag'   => (string) ( $why_everyone_loves['tag'] ?? '' ),
					'title' => (string) ( $why_everyone_loves['title'] ?? '' ),
					'items' => (array) ( $why_everyone_loves['items'] ?? array() ),
					'image' => (string) ( $why_everyone_loves['image'] ?? '' ),
				)
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $faq ) ) : ?>
	<div class="section">
		<div class="container container--narrow">
			<?php
			View::component(
				'faq/faq',
				array(
					'heading' => (string) ( $faq['heading'] ?? '' ),
					'items'   => (array) ( $faq['items'] ?? array() ),
				)
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $closing ) ) : ?>
	<div class="section" style="background: var(--color-primary); color: var(--color-primary-contrast); text-align: center;">
		<div class="container container--narrow">
			<h2 style="color: var(--color-secondary);"><?php echo esc_html( (string) ( $closing['title'] ?? '' ) ); ?></h2>
			<p><?php echo esc_html( (string) ( $closing['sub'] ?? '' ) ); ?></p>
		</div>
	</div>
	<div class="section-cut tex-cut-brush-a" style="background-color: var(--color-primary); margin-bottom: -85px;" aria-hidden="true"></div>
<?php endif; ?>

<section class="section section--sm back-home">
	<div class="container container--narrow back-home__inner">
		<h2 class="back-home__title"><?php esc_html_e( 'Back To Cane House', 'vintagesoul' ); ?></h2>
		<a class="btn btn--lg" href="<?php echo esc_url( RouteService::url( 'home' ) ); ?>">
			<?php esc_html_e( 'Visit The Cane House', 'vintagesoul' ); ?>
		</a>
	</div>
</section>

<?php View::component( 'sections/trust-ribbon-section' ); ?>

