<?php

use VintageSoul\Controllers\FranchiseController;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new FranchiseController() )->prepare();

$hero    = $data['hero'];
$why     = $data['why'];
$how     = $data['how'];
$closing = $data['closing'];
?>

<?php if ( ! empty( $hero ) ) : ?>
	<div class="section">
		<div class="container">
			<?php
			View::component(
				'banner/banner',
				array(
					'id'      => 'franchise-hero',
					'tag'     => (string) ( $hero['tag'] ?? '' ),
					'title'   => (string) ( $hero['title'] ?? '' ),
					'sub'     => (string) ( $hero['sub'] ?? '' ),
					'image'   => (string) ( $hero['image'] ?? '' ),
					'stamp'   => (array) ( $hero['stamp'] ?? array() ),
					'buttons' => (array) ( $hero['buttons'] ?? array() ),
				)
			);
			?>
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
					'sub'   => (string) ( $why['sub'] ?? '' ),
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

<?php if ( ! empty( $how ) ) : ?>
	<div class="section">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $how['tag'] ?? '' ),
					'title' => (string) ( $how['title'] ?? '' ),
				)
			);
			View::component(
				'step-chain/step-chain',
				array( 'items' => (array) ( $how['items'] ?? array() ) )
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $closing ) ) : ?>
	<div class="section" style="background: var(--color-primary); color: var(--color-primary-contrast); text-align: center;">
		<div class="container container--narrow">
			<span class="banner__tag" style="color: var(--color-secondary);"><?php echo esc_html( (string) ( $closing['tag'] ?? '' ) ); ?></span>
			<h2 style="color: var(--color-primary-contrast);"><?php echo wp_kses_post( (string) ( $closing['title'] ?? '' ) ); ?></h2>
			<p><?php echo esc_html( (string) ( $closing['sub'] ?? '' ) ); ?></p>
			<?php if ( ! empty( $closing['buttons'] ) ) : ?>
				<div class="banner__actions" style="justify-content: center;">
					<?php foreach ( (array) $closing['buttons'] as $btn ) :
						$btn   = (array) $btn;
						$label = trim( (string) ( $btn['label'] ?? '' ) );
						$route = (string) ( $btn['route'] ?? '' );
						if ( '' === $label || '' === $route ) {
							continue;
						}
					?>
						<a class="btn" href="<?php echo esc_url( RouteService::url( $route ) ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
