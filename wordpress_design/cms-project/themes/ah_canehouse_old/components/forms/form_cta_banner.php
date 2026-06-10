<?php
/**
 * form_cta_banner - reusable "banner card" that opens a modal form.
 *
 * A two-column card: image on one side, heading + sub + feature list + an
 * "open" button on the other. Used to launch the booking / franchise / etc. modals.
 * Every class & id is passed in, so the existing stylesheet and forms.js keep working.
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  EXAMPLE                                                        │
 * └─────────────────────────────────────────────────────────────────┘
 *  get_template_part( 'components/forms/form_cta_banner', null, [
 *    'section_id'    => 'booking',
 *    'section_class' => 'ch-bkb-section',
 *    'card_class'    => 'ch-bkb-card fade-up',
 *    'image_side'    => 'right',                 // 'left' | 'right'
 *    'content_class' => 'ch-bkb-content',
 *    'visual_class'  => 'ch-bkb-visual',
 *    'badge_class'   => 'ch-bkb-visual-badge',
 *    'badge'         => 'Pressed Fresh 🌿',
 *    'tag'           => 'Live Juice Booking',
 *    'title_class'   => 'ch-bkb-title',
 *    'heading'       => $wiz_heading,            // HTML (span/em allowed)
 *    'sub_class'     => 'ch-bkb-sub',
 *    'sub'           => $wiz_sub,
 *    'features_class'=> 'ch-bkb-features',
 *    'features'      => [ '🍋 Mix & match your flavours', '📅 Perfect for events & parties' ],
 *    'image'         => $wiz_image,
 *    'image_alt'     => 'Fresh juice',
 *    'button_id'     => 'ch-bk-open',
 *    'button_class'  => 'ch-bkb-open btn-lime',
 *    'button_label'  => '🌿 Book Now',
 *  ] );
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  OPTIONS                                                        │
 * └─────────────────────────────────────────────────────────────────┘
 *   section_id / section_class   <section> id + class
 *   card_class                   class on the inner card (include 'fade-up' if wanted)
 *   image_side                   'left' | 'right'  - which side the image sits
 *   content_class                class on the text column
 *   visual_class                 class on the image column
 *   badge_class / badge          floating badge over the image (omit badge to hide)
 *   tag / tag_color              small eyebrow label + its colour
 *   title_class / heading        heading; HTML span/em allowed
 *   sub_class / sub              sub-paragraph (plain text)
 *   features_class / features    <ul> list. Each item: a string, or [ 'icon'=>'', 'text'=>'' ]
 *   image / image_alt            banner image
 *   button_id / button_class / button_label   the open-modal button
 */

defined( 'ABSPATH' ) || exit;

$section_id    = $args['section_id']    ?? '';
$section_class = $args['section_class'] ?? '';
$card_class    = $args['card_class']    ?? 'fade-up';
$image_side    = ( ( $args['image_side'] ?? 'right' ) === 'left' ) ? 'left' : 'right';

$content_class = $args['content_class'] ?? '';
$visual_class  = $args['visual_class']  ?? '';
$badge_class   = $args['badge_class']   ?? '';
$badge         = $args['badge']         ?? '';

$tag       = $args['tag']       ?? '';
$tag_color = $args['tag_color'] ?? 'var(--client-color-7)';

$title_class = $args['title_class'] ?? '';
$heading     = $args['heading']     ?? '';
$sub_class   = $args['sub_class']   ?? '';
$sub         = $args['sub']         ?? '';

$features_class = $args['features_class'] ?? '';
$features       = is_array( $args['features'] ?? null ) ? $args['features'] : [];

$image     = $args['image']     ?? '';
$image_alt = $args['image_alt'] ?? '';

$button_id    = $args['button_id']    ?? '';
$button_class = $args['button_class'] ?? 'btn-lime';
$button_label = $args['button_label'] ?? 'Open';

/* The image column - rendered on whichever side image_side asks for. */
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
	<div class="container">
		<div class="<?php echo esc_attr( $card_class ); ?>">

			<?php if ( $image_side === 'left' ) $render_visual(); ?>

			<div class="<?php echo esc_attr( $content_class ); ?>">
				<?php if ( $tag !== '' ) : ?>
					<div class="section-tag" style="color:<?php echo esc_attr( $tag_color ); ?>;"><?php echo esc_html( $tag ); ?></div>
				<?php endif; ?>

				<?php if ( $heading !== '' ) : ?>
					<h2 class="<?php echo esc_attr( $title_class ); ?>"><?php echo wp_kses( $heading, [ 'span' => [ 'class' => [] ], 'em' => [] ] ); ?></h2>
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
