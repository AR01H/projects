<?php
/**
 * VintageSoulTheme - Reusable Vintage Call-to-Action (CTA) Banner
 *
 * Renders a luxury ornate framed CTA box with optional ribbon tag,
 * rich serif typography, description, and primary/secondary button actions.
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;

$tag     = (string) ( $tag ?? '' );
$title   = (string) ( $title ?? '' );
$sub     = (string) ( $sub ?? ( $desc ?? '' ) );
$variant = (string) ( $variant ?? 'dark' );
$buttons = isset( $buttons ) && is_array( $buttons ) ? $buttons : array();

if ( '' === $title && '' === $sub && empty( $buttons ) ) {
	return;
}

$is_dark   = 'dark' === $variant;
$box_class = $is_dark ? 'cta-banner-box cta-banner-box--dark card--rough-cut-dark' : 'cta-banner-box cta-banner-box--light';
?>
<section class="section cta-banner-section">
	<div class="container container--narrow">
		<div class="<?php echo esc_attr( $box_class ); ?>">
			
			<?php if ( '' !== $tag ) : ?>
				<div class="vintage-ribbon-tag<?php echo $is_dark ? ' vintage-ribbon-tag--gold' : ''; ?>" style="margin: 0 auto 16px; display: inline-flex;">
					<span><?php echo esc_html( $tag ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $title ) : ?>
				<h2 class="cta-banner__title">
					<?php echo wp_kses_post( $title ); ?>
				</h2>
			<?php endif; ?>

			<?php if ( '' !== $sub ) : ?>
				<p class="cta-banner__sub">
					<?php echo wp_kses_post( $sub ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $buttons ) ) : ?>
				<div class="hero-sugarcane__buttons">
					<?php foreach ( $buttons as $btn ) :
						$btn_lbl   = (string) ( $btn['label'] ?? 'LEARN MORE' );
						$btn_route = (string) ( $btn['route'] ?? ( $btn['link'] ?? ( $btn['url'] ?? 'contact' ) ) );
						$btn_style = (string) ( $btn['style'] ?? 'primary' );
						$btn_url   = ( 0 === strpos( $btn_route, '/' ) || 0 === strpos( $btn_route, 'http' ) )
							? $btn_route
							: RouteService::url( $btn_route );
						$btn_class = 'outline' === $btn_style || 'ghost' === $btn_style
							? 'btn btn--secondary-vintage'
							: 'btn btn--primary-vintage';
					?>
						<a class="<?php echo esc_attr( $btn_class ); ?>" href="<?php echo esc_url( $btn_url ); ?>">
							<span><?php echo esc_html( $btn_lbl ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
