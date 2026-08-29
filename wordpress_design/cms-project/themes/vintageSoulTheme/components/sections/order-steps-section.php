<?php

defined( 'ABSPATH' ) || exit;

$steps = (array) ( $steps ?? array(
	array( 'icon' => '🍹', 'title' => 'Choose your juice' ),
	array( 'icon' => '🔢', 'title' => 'Select quantity' ),
	array( 'icon' => '📍', 'title' => 'Confirm address' ),
	array( 'icon' => '🚚', 'title' => 'We deliver fresh!' ),
) );
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
