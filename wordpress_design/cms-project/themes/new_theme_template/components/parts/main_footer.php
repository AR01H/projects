<?php
/**
 * Site footer: footer nav (WP menu with JSON fallback), contact info from
 * admin settings, social links loop, copyright. Rendered by footer.php.
 */

defined( 'ABSPATH' ) || exit;

$nt_nav_fallback = nt_data( 'nav' );
$nt_socials      = array_filter( (array) nt_option( 'social' ) );
$nt_footer_note  = (string) nt_option( 'general', 'footer_note' );
?>
<footer class="nt-footer">
	<div class="nt-container nt-footer-inner">

		<div class="nt-footer-col">
			<p class="nt-footer-brand"><?php echo esc_html( NT_BRAND_NAME ); ?></p>
			<p class="nt-footer-tagline"><?php echo esc_html( nt_option( 'general', 'tagline' ) ); ?></p>
			<?php if ( '' !== $nt_footer_note ) : ?>
				<div class="nt-footer-note"><?php echo wp_kses_post( $nt_footer_note ); ?></div>
			<?php endif; ?>
		</div>

		<div class="nt-footer-col">
			<h4><?php esc_html_e( 'Quick Links', NT_TEXT_DOMAIN ); ?></h4>
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'nt-footer-menu',
					'depth'          => 1,
				) );
				?>
			<?php else : ?>
				<ul class="nt-footer-menu">
					<?php foreach ( (array) ( $nt_nav_fallback['footer'] ?? array() ) as $nt_item ) : ?>
						<li><a href="<?php echo esc_url( nt_link( $nt_item['url'] ?? '#' ) ); ?>"><?php echo esc_html( $nt_item['label'] ?? '' ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="nt-footer-col">
			<h4><?php esc_html_e( 'Contact', NT_TEXT_DOMAIN ); ?></h4>
			<ul class="nt-footer-contact">
				<li><?php echo esc_html( nt_option( 'general', 'phone', NT_BRAND_PHONE ) ); ?></li>
				<li><?php echo esc_html( nt_option( 'general', 'email', NT_BRAND_EMAIL ) ); ?></li>
				<?php if ( '' !== (string) nt_option( 'general', 'address' ) ) : ?>
					<li><?php echo esc_html( nt_option( 'general', 'address' ) ); ?></li>
				<?php endif; ?>
			</ul>
			<?php if ( $nt_socials ) : ?>
				<ul class="nt-footer-social">
					<?php foreach ( $nt_socials as $nt_network => $nt_url ) : ?>
						<li><a href="<?php echo esc_url( nt_link( (string) $nt_url ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( ucfirst( (string) $nt_network ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

	</div>

	<div class="nt-footer-bottom">
		<div class="nt-container">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( NT_BRAND_NAME ); ?>. <?php esc_html_e( 'All rights reserved.', NT_TEXT_DOMAIN ); ?></p>
		</div>
	</div>
</footer>
