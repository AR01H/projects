<?php

defined( 'ABSPATH' ) || exit;

$title = (string) ( $title ?? 'Quality / Certifications' );
$body  = (string) ( $body ?? 'Certified and verified by leading food safety and quality institutions.' );
$items = (array) ( $items ?? array() );
?>
<section class="section section--certs certs-vintage paper-rough" id="certifications">
	<div class="container certs-vintage__container">
		<div class="certs-vintage__header">
			<h2 class="certs-vintage__title"><?php echo esc_html( trim( strip_tags( $title ), " -—" ) ); ?></h2>
			<?php if ( '' !== $body ) : ?>
				<p class="certs-vintage__sub"><?php echo esc_html( $body ); ?></p>
			<?php endif; ?>
		</div>

		<div class="certs-vintage__grid">
			<?php foreach ( $items as $item ) :
				$code   = (string) ( $item['code'] ?? 'CERT' );
				$c_name = (string) ( $item['title'] ?? '' );
				$c_desc = (string) ( $item['desc'] ?? '' );
				$c_lbl  = (string) ( $item['action_label'] ?? 'View Certificate' );
			?>
				<div class="cert-box">
					<div class="cert-box__badge"><?php echo esc_html( $code ); ?></div>
					<h3 class="cert-box__title"><?php echo esc_html( $c_name ); ?></h3>
					<p class="cert-box__desc"><?php echo esc_html( $c_desc ); ?></p>
					<span class="cert-box__action"><?php echo esc_html( $c_lbl ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
