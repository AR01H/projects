<?php
/**
 * Component: Story CTA
 * Full-width call-to-action section at the bottom of the stories page.
 *
 * @param string $args['tag']                 Eyebrow label
 * @param string $args['heading']             Main heading (accepts HTML)
 * @param string $args['description']         Body paragraph
 * @param string $args['cta_primary_label']   Primary button text
 * @param string $args['cta_primary_url']     Primary button URL
 * @param string $args['cta_secondary_label'] Secondary button text (optional)
 * @param string $args['cta_secondary_url']   Secondary button URL (optional)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tag     = esc_html(    $args['tag']                  ?? '' );
$heading = wp_kses_post( $args['heading']              ?? '' );
$desc    = esc_html(    $args['description']           ?? '' );
$p_label = esc_html(    $args['cta_primary_label']     ?? 'Get in Touch' );
$p_url   = esc_url(     $args['cta_primary_url']       ?? '/contact' );
$s_label = esc_html(    $args['cta_secondary_label']   ?? '' );
$s_url   = esc_url(     $args['cta_secondary_url']     ?? '#' );

if ( ! $heading ) return;
?>

<section class="pt-story-cta">
	<div class="pt-container">
		<div class="pt-story-cta__inner">

			<?php if ( $tag ) : ?>
				<span class="pt-section-tag pt-section-tag--light"><?php echo $tag; ?></span>
			<?php endif; ?>

			<h2 class="pt-story-cta__heading"><?php echo $heading; ?></h2>

			<?php if ( $desc ) : ?>
				<p class="pt-story-cta__desc"><?php echo $desc; ?></p>
			<?php endif; ?>

			<div class="pt-story-cta__actions">
				<a href="<?php echo $p_url; ?>" class="pt-btn pt-btn--primary">
					<?php echo $p_label; ?>
				</a>
				<?php if ( $s_label ) : ?>
				<a href="<?php echo $s_url; ?>" class="pt-btn pt-btn--outline-light">
					<?php echo $s_label; ?>
				</a>
				<?php endif; ?>
			</div>

		</div>
	</div>
</section>
