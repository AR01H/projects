<?php
/**
 * components/sections/contact_process.php
 * Props: $process_steps[] { number, icon, title, description }
 */
defined( 'ABSPATH' ) || exit;

$_steps = ( isset( $process_steps ) && is_array( $process_steps ) ) ? $process_steps : array();
if ( empty( $_steps ) ) return;
?>
<section class="contact-process-section">
	<div class="container">
		<h2 class="contact-section-heading"><?php esc_html_e( 'What happens after you submit?', ADN_TEXT_DOMAIN ); ?></h2>
		<div class="contact-process-steps">
			<?php foreach ( $_steps as $_i => $_s ) :
				$_num  = esc_html( isset( $_s['number'] )      ? (string) $_s['number']      : (string)( $_i + 1 ) );
				$_ico  = esc_html( isset( $_s['icon'] )        ? (string) $_s['icon']        : '' );
				$_ttl  = esc_html( isset( $_s['title'] )       ? (string) $_s['title']       : '' );
				$_dsc  = esc_html( isset( $_s['description'] ) ? (string) $_s['description'] : '' );
				$_last = ( $_i === count( $_steps ) - 1 );
			?>
				<div class="contact-process-step">
					<div class="cps-icon-wrap">
						<span class="cps-icon" aria-hidden="true"><?php echo $_ico; ?></span>
					</div>
					<div class="cps-number" aria-hidden="true"><?php echo $_num; ?></div>
					<h3><?php echo $_ttl; ?></h3>
					<p><?php echo $_dsc; ?></p>
				</div>
				<?php if ( ! $_last ) : ?>
					<div class="cps-arrow" aria-hidden="true">→</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
