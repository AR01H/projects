<?php
/**
 * components/sections/contact_hero.php
 * Props: $hero { title, description, bg_icon, trust_items[] }
 */
defined( 'ABSPATH' ) || exit;

$_h     = isset( $hero ) ? (array) $hero : array();
$_title = esc_html( isset( $_h['title'] )       ? (string) $_h['title']       : '' );
$_desc  = esc_html( isset( $_h['description'] ) ? (string) $_h['description'] : '' );
$_trust = isset( $_h['trust_items'] ) ? (array) $_h['trust_items'] : array();

$_default_img = get_template_directory_uri() . '/assets/images/backgrounds/home_hero.jpg';
$_hero_img    = adn_versioned_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: $_default_img );
?>
<section class="contact-hero">
	<div class="contact-hero-inner container">
		<div class="contact-hero-text">
			<h1><?php echo $_title; ?></h1>
			<?php if ( '' !== $_desc ) : ?>
				<p><?php echo $_desc; ?></p>
			<?php endif; ?>
		</div>
		<div class="contact-hero-img">
			<img src="<?php echo esc_url( $_hero_img ); ?>" alt="" loading="eager" fetchpriority="high" />
		</div>
	</div>

	<?php if ( ! empty( $_trust ) ) : ?>
	<div class="contact-trust-bar">
		<div class="contact-trust-inner container">
			<?php foreach ( $_trust as $_t ) :
				$_ti = adn_icon( isset( $_t['icon'] )     ? (string) $_t['icon']     : '' );
				$_tt = esc_html( isset( $_t['title'] )    ? (string) $_t['title']    : '' );
				$_ts = esc_html( isset( $_t['subtitle'] ) ? (string) $_t['subtitle'] : '' );
			?>
				<div class="contact-trust-item">
					<span class="contact-trust-icon" aria-hidden="true"><?php echo $_ti; ?></span>
					<div>
						<strong><?php echo $_tt; ?></strong>
						<?php if ( '' !== $_ts ) : ?>
							<span><?php echo $_ts; ?></span>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</section>
