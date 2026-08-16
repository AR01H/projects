<?php

use VintageSoul\Controllers\GameController;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new GameController() )->prepare();

$hero  = $data['hero'];
$embed = (string) $data['embed'];
?>

<?php if ( ! empty( $hero ) ) : ?>
	<div class="section section--sm">
		<div class="container container--narrow">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $hero['tag'] ?? '' ),
					'title' => (string) ( $hero['title'] ?? '' ),
					'sub'   => (string) ( $hero['sub'] ?? '' ),
				)
			);
			?>
		</div>
	</div>
<?php endif; ?>

<?php if ( '' !== $embed ) : ?>
	<div class="section section--flush">
		<div class="container--narrow">
			<div class="game-embed tex-vintage-grain-a">
				<div class="game-embed__screen">
					<iframe
						class="game-embed__frame"
						src="<?php echo esc_url( $embed ); ?>"
						title="<?php echo esc_attr( (string) ( $hero['title'] ?? 'Game' ) ); ?>"
						loading="lazy"
						allow="autoplay"
						allowfullscreen
					></iframe>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
