<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;

$order_steps_data = (array) ( JsonFileProvider::read( 'data/content/order-steps.json' ) ?? array() );
$steps            = (array) ( $steps ?? ( $order_steps_data['steps'] ?? array() ) );
?>
<section class="section section--order-steps order-steps-vintage">
	<div class="container container--narrow order-steps-vintage__container">
		<div class="order-steps-vintage__header">
			<h3 class="order-steps-vintage__title">— PLACE YOUR ORDER —</h3>
		</div>
		<div class="order-steps-vintage__list">
			<?php foreach ( $steps as $idx => $st ) :
				$s_title = (string) ( $st['title'] ?? '' );
				$s_icon  = (string) ( $st['icon'] ?? '✦' );
			?>
				<div class="order-step-item">
					<span class="order-step-item__icon"><?php echo esc_html( $s_icon ); ?></span>
					<span class="order-step-item__title"><?php echo esc_html( $s_title ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
