<?php
/**
 * form_cta_banner - reusable two-column CTA card that opens a modal form.
 *
 * get_template_part( 'components/forms/form_cta_banner', null, [
 *   'section_id'    => 'consultation',
 *   'section_class' => 'pt-ctab-section',
 *   'card_class'    => 'pt-ctab-card',
 *   'image_side'    => 'right',                   // 'left' | 'right'
 *   'content_class' => 'pt-ctab-content',
 *   'visual_class'  => 'pt-ctab-visual',
 *   'badge_class'   => 'pt-ctab-badge',
 *   'badge'         => 'Free Consultation',
 *   'tag'           => 'Get Started',
 *   'title_class'   => 'pt-ctab-title',
 *   'heading'       => $heading,
 *   'sub_class'     => 'pt-ctab-sub',
 *   'sub'           => $sub,
 *   'features_class'=> 'pt-ctab-features',
 *   'features'      => [ '✓ No obligation', '✓ Fast turnaround', '✓ Expert advice' ],
 *   'image'         => $image_url,
 *   'image_alt'     => 'Team working',
 *   'button_id'     => 'pt-consult-open',
 *   'button_class'  => 'pt-btn pt-btn--primary',
 *   'button_label'  => 'Book Free Call',
 * ] );
 *
 * OPTIONS: section_id, section_class, card_class, image_side, content_class,
 *          visual_class, badge_class, badge, tag, tag_color, title_class,
 *          heading, sub_class, sub, features_class, features,
 *          image, image_alt, button_id, button_class, button_label
 */

defined( 'ABSPATH' ) || exit;

$section_id    = $args['section_id']    ?? '';
$section_class = $args['section_class'] ?? '';
$card_class    = $args['card_class']    ?? '';
$image_side    = ( ( $args['image_side'] ?? 'right' ) === 'left' ) ? 'left' : 'right';

$content_class = $args['content_class'] ?? '';
$visual_class  = $args['visual_class']  ?? '';
$badge_class   = $args['badge_class']   ?? '';
$badge         = $args['badge']         ?? '';

$tag       = $args['tag']       ?? '';
$tag_color = $args['tag_color'] ?? 'var(--pt-accent)';

$title_class = $args['title_class'] ?? '';
$heading     = $args['heading']     ?? '';
$sub_class   = $args['sub_class']   ?? '';
$sub         = $args['sub']         ?? '';

$features_class = $args['features_class'] ?? '';
$features       = is_array( $args['features'] ?? null ) ? $args['features'] : [];

$image     = $args['image']     ?? '';
$image_alt = $args['image_alt'] ?? '';

$button_id    = $args['button_id']    ?? '';
$button_class = $args['button_class'] ?? 'pt-btn pt-btn--primary';
$button_label = $args['button_label'] ?? 'Get Started';

$render_visual = static function () use ( $visual_class, $image, $image_alt, $badge_class, $badge ) {
	if ( ! $image ) return;
	?>
	<div class="<?php echo esc_attr( $visual_class ); ?>">
		<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy">
		<?php if ( $badge !== '' ) : ?>
			<div class="<?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $badge ); ?></div>
		<?php endif; ?>
	</div>
	<?php
};
?>

<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( $section_class ); ?>">
	<div class="pt-container">
		<div class="<?php echo esc_attr( $card_class ); ?>">

			<?php if ( $image_side === 'left' ) $render_visual(); ?>

			<div class="<?php echo esc_attr( $content_class ); ?>">
				<?php if ( $tag !== '' ) : ?>
					<div class="pt-section-tag" style="color:<?php echo esc_attr( $tag_color ); ?>;"><?php echo esc_html( $tag ); ?></div>
				<?php endif; ?>

				<?php if ( $heading !== '' ) : ?>
					<h2 class="<?php echo esc_attr( $title_class ); ?>"><?php echo wp_kses( $heading, [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ] ); ?></h2>
				<?php endif; ?>

				<?php if ( $sub !== '' ) : ?>
					<p class="<?php echo esc_attr( $sub_class ); ?>"><?php echo esc_html( $sub ); ?></p>
				<?php endif; ?>

				<?php if ( $features ) : ?>
					<ul class="<?php echo esc_attr( $features_class ); ?>">
						<?php foreach ( $features as $feat ) : ?>
							<li>
								<?php
								if ( is_array( $feat ) ) {
									echo esc_html( trim( ( $feat['icon'] ?? '' ) . ' ' . ( $feat['text'] ?? '' ) ) );
								} else {
									echo esc_html( $feat );
								}
								?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<button type="button" class="<?php echo esc_attr( $button_class ); ?>" id="<?php echo esc_attr( $button_id ); ?>">
					<?php echo esc_html( $button_label ); ?>
				</button>
			</div>

			<?php if ( $image_side === 'right' ) $render_visual(); ?>

		</div>
	</div>
</section>
