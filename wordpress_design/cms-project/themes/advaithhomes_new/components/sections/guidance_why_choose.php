<?php
/**
 * components/sections/guidance_why_choose.php
 * Props: $why_choose { heading, items[] { icon, title, desc } }
 */
defined( 'ABSPATH' ) || exit;

$_wc    = isset( $why_choose ) ? (array) $why_choose : array();
$_hdg   = esc_html( isset( $_wc['heading'] ) ? (string) $_wc['heading'] : '' );
$_items = isset( $_wc['items'] ) ? (array) $_wc['items'] : array();
if ( empty( $_items ) ) return;
?>
<section class="guidance-why-section">
	<div class="container">
		<?php if ( '' !== $_hdg ) : ?>
			<h2 class="contact-section-heading"><?php echo $_hdg; ?></h2>
		<?php endif; ?>
		<div class="guidance-why-grid">
			<?php foreach ( $_items as $_w ) :
				$_wi = esc_html( isset( $_w['icon'] )  ? (string) $_w['icon']  : '' );
				$_wt = esc_html( isset( $_w['title'] ) ? (string) $_w['title'] : '' );
				$_wd = esc_html( isset( $_w['desc'] )  ? (string) $_w['desc']  : '' );
			?>
				<div class="guidance-why-item">
					<span class="gw-icon" aria-hidden="true"><?php echo $_wi; ?></span>
					<h3><?php echo $_wt; ?></h3>
					<p><?php echo $_wd; ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
