<?php
defined( 'ABSPATH' ) || exit;
$steps = ch_get_order_steps();
?>

<section id="how-to-order" class="ch-how-section">
	<div class="ch-how__header fade-up">
		<div class="ch-section-tag">Simple &amp; Easy</div>
		<h2 class="ch-section-title">How to <span class="accent">Order</span></h2>
		<p class="ch-section-body">Build your perfect fresh cane juice in just <?php echo count( $steps ); ?> steps. Pressed live, just for you.</p>
	</div>

	<div class="ch-steps-grid">
		<?php foreach ( $steps as $step ) :
			$step = (array) $step;
			$is_highlight = ! empty( $step['highlight'] );
		?>
			<div class="ch-step-card fade-up<?php echo $is_highlight ? ' ch-step-card--highlight' : ''; ?>">
				<div class="ch-step-num<?php echo $is_highlight ? ' ch-step-num--highlight' : ''; ?>">
					<?php echo esc_html( $step['num'] ?? '' ); ?>
				</div>
				<div class="ch-step-emoji ch-shaking-leaf"><?php echo esc_html( $step['emoji'] ?? '' ); ?></div>
				<div class="ch-step-title"><?php echo esc_html( $step['title'] ?? '' ); ?></div>
				<div class="ch-step-desc"><?php echo esc_html( $step['desc'] ?? '' ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
