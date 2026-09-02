<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;

$title = (string) ( $title ?? '' );
$steps = (array) ( $steps ?? array() );

if ( '' === $title && empty( $steps ) ) {
	return;
}
?>
<section class="section section--serve serve-vintage paper-rough" id="how-we-serve">
	<div class="container container--narrow serve-vintage__container">
		<?php if ( '' !== $title ) : ?>
			<div class="serve-vintage__header">
				<h2 class="serve-vintage__title"><?php echo esc_html( trim( strip_tags( $title ), " -—" ) ); ?></h2>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $steps ) ) : ?>
			<div class="serve-vintage__list">
				<?php foreach ( $steps as $step ) :
					$num       = (string) ( $step['number'] ?? '' );
					$s_title   = (string) ( $step['title'] ?? '' );
					$s_desc    = (string) ( $step['desc'] ?? '' );
					$cta       = (array) ( $step['cta'] ?? array() );
					$btn_label = (string) ( $cta['label'] ?? '' );
					$btn_route = (string) ( $cta['route'] ?? 'contact' );
				?>
					<div class="serve-card">
						<?php if ( '' !== $num ) : ?>
							<div class="serve-card__badge"><?php echo esc_html( $num ); ?></div>
						<?php endif; ?>
						<div class="serve-card__content">
							<?php if ( '' !== $s_title ) : ?>
								<h3 class="serve-card__title"><?php echo esc_html( $s_title ); ?></h3>
							<?php endif; ?>
							<?php if ( '' !== $s_desc ) : ?>
								<p class="serve-card__desc"><?php echo esc_html( $s_desc ); ?></p>
							<?php endif; ?>
							<?php if ( '' !== $btn_label ) : ?>
								<a class="btn btn--serve" href="<?php echo esc_url( RouteService::url( $btn_route ) ); ?>">
									<?php echo esc_html( $btn_label ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
