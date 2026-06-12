<?php
/**
 * components/sections/guidance_hero.php
 * Props: $hero { title, description, bg_icon, trust_items[] }
 */
defined( 'ABSPATH' ) || exit;

$_h     = isset( $hero ) ? (array) $hero : array();
$_title = esc_html( isset( $_h['title'] )       ? (string) $_h['title']       : '' );
$_desc  = esc_html( isset( $_h['description'] ) ? (string) $_h['description'] : '' );
$_icon  = adn_icon( isset( $_h['bg_icon'] )     ? (string) $_h['bg_icon']     : '🤝' );
$_trust = isset( $_h['trust_items'] ) ? (array) $_h['trust_items'] : array();
?>
<section class="guidance-hero">
	<div class="guidance-hero-inner container">
		<div class="guidance-hero-text">
			<h1><?php echo $_title; ?></h1>
			<?php if ( '' !== $_desc ) : ?>
				<p><?php echo $_desc; ?></p>
			<?php endif; ?>
		</div>
		<div class="guidance-hero-img" aria-hidden="true"><?php echo $_icon; ?></div>
	</div>

	<?php if ( ! empty( $_trust ) ) : ?>
	<div class="guidance-trust-bar">
		<div class="guidance-trust-inner container">
			<?php foreach ( $_trust as $_t ) :
				$_ti  = adn_icon( isset( $_t['icon'] )  ? (string) $_t['icon']  : '' );
				$_tl  = esc_html( isset( $_t['label'] ) ? (string) $_t['label'] : '' );
				$_tn  = esc_html( isset( $_t['note'] )  ? (string) $_t['note']  : '' );
			?>
				<div class="guidance-trust-item">
					<span class="guidance-trust-icon" aria-hidden="true"><?php echo $_ti; ?></span>
					<div>
						<strong><?php echo $_tl; ?></strong>
						<?php if ( '' !== $_tn ) : ?>
							<span><?php echo $_tn; ?></span>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</section>
