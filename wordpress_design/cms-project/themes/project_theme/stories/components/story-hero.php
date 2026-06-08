<?php
/**
 * Component: Story Hero
 * Full-width page hero for the stories listing.
 *
 * @param string $args['tag']         Small eyebrow label
 * @param string $args['heading']     Main H1 (accepts HTML via wp_kses_post)
 * @param string $args['description'] Supporting paragraph
 * @param string $args['cta_label']   CTA button text (optional)
 * @param string $args['cta_url']     CTA button href (optional)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tag   = esc_html( $args['tag']          ?? '' );
$head  = wp_kses_post( $args['heading']  ?? '' );
$desc  = esc_html( $args['description']  ?? '' );
$label = esc_html( $args['cta_label']    ?? '' );
$url   = esc_url( $args['cta_url']       ?? '#' );

if ( ! $head ) return;
?>

<section class="pt-story-hero">
	<div class="pt-container">
		<div class="pt-story-hero__inner">

			<?php if ( $tag ) : ?>
				<span class="pt-story-hero__tag"><?php echo $tag; ?></span>
			<?php endif; ?>

			<h1 class="pt-story-hero__heading"><?php echo $head; ?></h1>

			<?php if ( $desc ) : ?>
				<p class="pt-story-hero__desc"><?php echo $desc; ?></p>
			<?php endif; ?>

			<?php if ( $label ) : ?>
				<a href="<?php echo $url; ?>" class="pt-btn pt-btn--primary">
					<?php echo $label; ?>
				</a>
			<?php endif; ?>

		</div>
	</div>
</section>
