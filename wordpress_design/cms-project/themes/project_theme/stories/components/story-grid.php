<?php
/**
 * Component: Story Grid
 * Renders a section containing a grid of story-card components.
 *
 * @param array  $args['stories'] Array of story data arrays (from PT_Stories_Data)
 * @param string $args['tag']     Optional section eyebrow label
 * @param string $args['heading'] Optional section heading
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$stories = (array) ( $args['stories'] ?? [] );
if ( empty( $stories ) ) return;

$tag     = esc_html( $args['tag']     ?? '' );
$heading = esc_html( $args['heading'] ?? '' );
?>

<section class="pt-stories-grid-section">
	<div class="pt-container">

		<?php if ( $tag || $heading ) : ?>
		<div class="pt-stories-grid-section__header">
			<?php if ( $tag ) : ?>
				<span class="pt-section-tag"><?php echo $tag; ?></span>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<h2 class="pt-stories-grid-section__heading"><?php echo $heading; ?></h2>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="pt-stories-grid">
			<?php foreach ( $stories as $story ) :
				get_template_part( 'stories/components/story-card', null, (array) $story );
			endforeach; ?>
		</div>

	</div>
</section>
