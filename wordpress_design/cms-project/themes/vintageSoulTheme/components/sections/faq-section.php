<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\View;

$faqs_data = (array) ( JsonFileProvider::read( 'data/content/faqs.json' ) ?? array() );

$tag     = (string) ( $tag ?? ( $faqs_data['tag'] ?? '' ) );
$heading = (string) ( $heading ?? ( $title ?? ( $faqs_data['title'] ?? ( $faqs_data['heading'] ?? '' ) ) ) );
$sub     = (string) ( $sub ?? ( $faqs_data['sub'] ?? '' ) );
$items   = (array) ( $items ?? ( $faqs_data['items'] ?? array() ) );

if ( '' === $heading && empty( $items ) ) {
	return;
}
?>
<section class="section section--faq faq-vintage paper-rough" id="faq">
	<div class="container container--narrow faq-vintage__container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'    => $tag,
				'title'  => $heading,
				'sub'    => $sub,
				'ribbon' => true,
			)
		);
		?>

		<?php if ( ! empty( $items ) ) : ?>
			<div class="faq-accordion">
				<?php foreach ( $items as $idx => $item ) :
					$q      = (string) ( $item['question'] ?? '' );
					$a      = (string) ( $item['answer'] ?? '' );
					$faq_id = 'faq-' . sanitize_title( $q );
				?>
					<details class="faq-accordion__item frame--ornate-sm" id="<?php echo esc_attr( $faq_id ); ?>" name="home-faq">
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
		<?php endif; ?>
	</div>
</section>
