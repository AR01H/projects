<?php
/**
 * Master Common Subpage Hero Header Component
 *
 * Provides a unified, ultra-premium vintage header across all inner pages:
 * (About Us, All About Cane, Events, Franchise, Blog, Contact, Game)
 * Features:
 * - Centered Cane House Botanical Crest & Heart-Wreath Emblem
 * - Dancing Script cursive tag
 * - Cinzel title with italic serif emphasis
 * - Aged parchment background with pure sugarcane forest trees watermark
 * - Gold wave decorative bottom divider
 */
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

$tag   = (string) ( $tag ?? '' );
$title = (string) ( $title ?? '' );
$sub   = (string) ( $sub ?? '' );
$id    = (string) ( $id ?? 'subpage-hero' );
$image = (string) ( $image ?? 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg' );
$image_url = UrlHelper::resolve( $image );
$wreath_url = UrlHelper::resolve( 'assets/images/decorative/cane-heart-wreath.png' );
$gold_wave  = UrlHelper::resolve( 'assets/images/textures/border/gold-wave.svg' );
?>
<header class="common-subpage-hero page-hero" id="<?php echo esc_attr( $id ); ?>">
	
	<!-- Botanical Watermark Layer -->
	<div class="common-subpage-hero__watermark" aria-hidden="true" style="background-image: url('<?php echo esc_url( $image_url ); ?>');"></div>
	
	<!-- Roughness Texture -->
	<div class="common-subpage-hero__texture" aria-hidden="true"></div>

	<div class="container common-subpage-hero__inner">
		
		<!-- Central Botanical Crest Stamp -->
		<div class="subpage-hero__crest-wrap">
			<div class="subpage-hero__crest">
				<img src="<?php echo esc_url( $wreath_url ); ?>" alt="" class="subpage-hero__wreath-img" loading="eager">
				<span class="subpage-hero__crest-text">EST. LONDON</span>
			</div>
		</div>

		<!-- Tagline -->
		<?php if ( '' !== $tag ) : ?>
			<p class="common-subpage-hero__tag"><?php echo esc_html( $tag ); ?></p>
		<?php endif; ?>

		<!-- Main Title -->
		<?php if ( '' !== $title ) : ?>
			<h1 class="common-subpage-hero__title"><?php echo wp_kses_post( $title ); ?></h1>
		<?php endif; ?>

		<!-- Editorial Subtitle -->
		<?php if ( '' !== $sub ) : ?>
			<p class="common-subpage-hero__sub"><?php echo esc_html( $sub ); ?></p>
		<?php endif; ?>

	</div>
</header>

<!-- Gold Wave Divider -->
<div class="gold-wave-divider" aria-hidden="true">
	<img src="<?php echo esc_url( $gold_wave ); ?>" alt="" loading="lazy">
</div>
