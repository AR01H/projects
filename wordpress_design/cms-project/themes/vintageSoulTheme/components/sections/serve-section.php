<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;

$title = (string) ( $title ?? 'HOW WE SERVE YOU' );
$steps = (array) ( $steps ?? array() );
?>
<section class="section section--serve serve-vintage paper-rough" id="how-we-serve">
	<div class="container container--narrow serve-vintage__container">
		<div class="serve-vintage__header">
			<h2 class="serve-vintage__title"><?php echo esc_html( trim( strip_tags( $title ), " -—" ) ); ?></h2>
		</div>

		<div class="serve-vintage__list">
			<?php foreach ( $steps as $step ) :
				$num       = (string) ( $step['number'] ?? '01' );
				$s_title   = (string) ( $step['title'] ?? '' );
				$s_desc    = (string) ( $step['desc'] ?? '' );
				$cta       = (array) ( $step['cta'] ?? array() );
				$btn_label = (string) ( $cta['label'] ?? 'Order Now' );
				$btn_route = (string) ( $cta['route'] ?? 'contact' );
			?>
				<div class="serve-card">
					<div class="serve-card__badge"><?php echo esc_html( $num ); ?></div>
					<div class="serve-card__content">
						<h3 class="serve-card__title"><?php echo esc_html( $s_title ); ?></h3>
						<p class="serve-card__desc"><?php echo esc_html( $s_desc ); ?></p>
						<a class="btn btn--serve" href="<?php echo esc_url( RouteService::url( $btn_route ) ); ?>">
							<?php echo esc_html( $btn_label ); ?>
						</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
