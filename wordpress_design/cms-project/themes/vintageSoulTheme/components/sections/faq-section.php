<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\View;

$heading = (string) ( $heading ?? 'Frequently Asked Questions' );
$items   = (array) ( $items ?? array() );
?>
<section class="section section--faq faq-vintage paper-rough" id="faq">
	<div class="container container--narrow faq-vintage__container">
		<div class="faq-vintage__header">
			<h2 class="faq-vintage__title">— <?php echo esc_html( $heading ); ?> —</h2>
		</div>

		<div class="faq-accordion">
			<?php foreach ( $items as $idx => $item ) :
				$q = (string) ( $item['question'] ?? '' );
				$a = (string) ( $item['answer'] ?? '' );
			?>
				<details class="faq-accordion__item frame--ornate-sm"<?php echo 0 === $idx ? ' open' : ''; ?>>
					<summary class="faq-accordion__summary">
						<span class="faq-accordion__question"><?php echo esc_html( $q ); ?></span>
						<span class="faq-accordion__icon" aria-hidden="true">+</span>
					</summary>
					<div class="faq-accordion__content">
						<p><?php echo esc_html( $a ); ?></p>
					</div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
