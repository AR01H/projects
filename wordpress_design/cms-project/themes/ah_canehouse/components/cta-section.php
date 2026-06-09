<?php
defined( 'ABSPATH' ) || exit;

$settings = ch_get_settings();
$phone    = $settings['phone'] ?? ( defined( 'CONTACT_NUMBER' ) ? CONTACT_NUMBER : '' );

$_d = CH_Shared_Data::cta_section_settings();

// Caller overrides via get_template_part( ..., null, $args )
$tag        = $args['tag']        ?? $_d['tag']       ?? '';
$heading    = $args['heading']    ?? $_d['heading']   ?? '';
$body       = $args['body']       ?? $_d['body']      ?? '';
$btn_label  = $args['btn_label']  ?? $_d['btn_label'] ?? '';
$btn_url    = $args['btn_url']    ?? home_url( '/#contact' );
$btn2_label = $args['btn2_label'] ?? '';
$btn2_url   = $args['btn2_url']   ?? '#';
$btn2_icon  = $args['btn2_icon']  ?? '';
$btn2_class = $args['btn2_class'] ?? 'btn-outline ch-btn-outline-light';
$show_phone = $args['show_phone'] ?? true;
$bg         = $args['bg']         ?? 'var(--client-color-3)';
?>

<section class="ch-cta-section" style="background:<?php echo esc_attr( $bg ); ?>;">
	<div class="container">
		<div class="ch-cta-inner fade-up">
			<div class="section-tag ch-cta-tag">
				<?php echo esc_html( $tag ); ?>
			</div>
			<h2 class="section-title ch-cta-title">
				<?php echo wp_kses( $heading, [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ] ); ?>
			</h2>
			<p class="section-body ch-cta-body">
				<?php echo esc_html( $body ); ?>
			</p>
			<div class="ch-cta-buttons">
				<a href="<?php echo esc_url( $btn_url ); ?>" class="btn-lime">
					<?php echo esc_html( $btn_label ); ?>
				</a>
				<?php if ( $btn2_label ) : ?>
					<a href="<?php echo esc_url( $btn2_url ); ?>" class="<?php echo esc_attr( $btn2_class ); ?>">
						<?php if ( $btn2_icon ) : ?><?php echo esc_html( $btn2_icon ); ?> <?php endif; ?>
						<?php echo esc_html( $btn2_label ); ?>
					</a>
				<?php elseif ( $show_phone && strlen( preg_replace( '/[^0-9]/', '', $phone ) ) >= 6 ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>"
					   class="btn-outline ch-cta-phone-btn">
						📞 <?php echo esc_html( $phone ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
