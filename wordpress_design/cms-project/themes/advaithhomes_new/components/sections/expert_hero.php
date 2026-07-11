<?php
/**
 * components/sections/expert_hero.php
 * Props: $hero { title, description, bg_icon }, $stats[]
 */
defined( 'ABSPATH' ) || exit;

$_h     = isset( $hero )  ? (array) $hero  : array();
$_stats = isset( $stats ) ? (array) $stats : array();
$_title = esc_html( isset( $_h['title'] )       ? (string) $_h['title']       : '' );
$_desc  = esc_html( isset( $_h['description'] ) ? (string) $_h['description'] : '' );

$_default_img = get_template_directory_uri() . THEME_DEFAULT_HERO_IMG;
$_hero_img    = adn_versioned_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: $_default_img );
?>
<section class="expert-hero">
	<div class="expert-hero-inner container">
		<div class="expert-hero-text">
			<h1><?php echo $_title; ?></h1>
			<?php if ( '' !== $_desc ) : ?>
				<p><?php echo $_desc; ?></p>
			<?php endif; ?>
		</div>
		<div class="expert-hero-img">
			<img src="<?php echo esc_url( $_hero_img ); ?>" alt="" loading="eager" fetchpriority="high" />
		</div>
	</div>

	<?php if ( ! empty( $_stats ) ) : ?>
	<div class="expert-stats-bar">
		<div class="expert-stats-inner container">
			<?php foreach ( $_stats as $_s ) :
				$_sv = esc_html( isset( $_s['value'] ) ? (string) $_s['value'] : '' );
				$_sl = esc_html( isset( $_s['label'] ) ? (string) $_s['label'] : '' );
			?>
				<div class="expert-stat-item">
					<strong class="expert-stat-value"><?php echo $_sv; ?></strong>
					<span class="expert-stat-label"><?php echo $_sl; ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</section>
