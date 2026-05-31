<?php
/**
 * Reusable inner-page hero component.
 *
 * Pass args via get_template_part( 'components/page-hero', null, $args ):
 *
 *  tag          (string)  Small eyebrow tag.         Default: 'The Cane House'
 *  heading      (string)  H1 HTML (span/em allowed). Default: 'Welcome'
 *  desc         (string)  Paragraph beneath heading. Default: ''
 *  modifier     (string)  Extra CSS class on section. Default: ''
 *                         Use a ch-page-hero--* colour variant, e.g. 'ch-page-hero--events'
 *  btn1_label   (string)  Primary button label.      Default: '' (hidden if empty)
 *  btn1_url     (string)  Primary button href.       Default: '#'
 *  btn1_class   (string)  Primary button CSS class.  Default: 'btn-lime'
 *  btn1_icon    (string)  Emoji/icon before label.   Default: ''
 *  btn2_label   (string)  Secondary button label.    Default: '' (hidden if empty)
 *  btn2_url     (string)  Secondary button href.     Default: '#'
 *  btn2_class   (string)  Secondary button CSS class. Default: 'btn-outline ch-btn-outline-light'
 *  btn2_icon    (string)  Emoji/icon before label.   Default: ''
 *  show_phone   (bool)    Show site phone as btn2 (overrides btn2_* when true). Default: false
 *  badge        (string)  Optional badge/pill text above the tag. Default: ''
 *  extra        (string)  Arbitrary safe HTML rendered below buttons. Default: ''
 */
defined( 'ABSPATH' ) || exit;

$settings = ch_get_settings();
$phone    = $settings['phone'] ?? ( defined( 'CONTACT_NUMBER' ) ? CONTACT_NUMBER : '' );

$tag        = $args['tag']       ?? 'The Cane House';
$heading    = $args['heading']   ?? 'Welcome';
$desc       = $args['desc']      ?? '';
$modifier   = $args['modifier']  ?? '';

$btn1_label = $args['btn1_label'] ?? '';
$btn1_url   = $args['btn1_url']   ?? '#';
$btn1_class = $args['btn1_class'] ?? 'btn-lime';
$btn1_icon  = $args['btn1_icon']  ?? '';

$btn2_label = $args['btn2_label'] ?? '';
$btn2_url   = $args['btn2_url']   ?? '#';
$btn2_class = $args['btn2_class'] ?? 'btn-outline ch-btn-outline-light';
$btn2_icon  = $args['btn2_icon']  ?? '';

$show_phone = $args['show_phone'] ?? false;
$badge      = $args['badge']      ?? '';
$extra      = $args['extra']      ?? '';

// When show_phone is true, the second button becomes a tel: link.
if ( $show_phone && $phone ) {
	$btn2_label = $btn2_label ?: $phone;
	$btn2_url   = 'tel:' . preg_replace( '/[^+0-9]/', '', $phone );
	$btn2_icon  = $btn2_icon ?: '📞';
}

$section_class = trim( 'ch-page-hero ' . esc_attr( $modifier ) );
$allowed_html  = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [], 'br' => [], 'strong' => [] ];
?>

<section class="<?php echo $section_class; ?>">
	<div class="container">
		<div class="ch-page-hero__inner fade-up">

			<?php if ( $badge ) : ?>
				<div class="ch-page-hero__badge">
					<?php echo esc_html( $badge ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $tag ) : ?>
				<div class="ch-eyebrow">
					<span><?php echo esc_html( $tag ); ?></span>
				</div>
			<?php endif; ?>

			<h1 class="ch-page-hero__title">
				<?php echo wp_kses( $heading, $allowed_html ); ?>
			</h1>

			<?php if ( $desc ) : ?>
				<p class="ch-page-hero__desc">
					<?php echo esc_html( $desc ); ?>
				</p>
			<?php endif; ?>

			<?php if ( $btn1_label || $btn2_label ) : ?>
				<div class="ch-page-hero__btns">
					<?php if ( $btn1_label ) : ?>
						<a href="<?php echo esc_url( $btn1_url ); ?>" class="<?php echo esc_attr( $btn1_class ); ?>">
							<?php if ( $btn1_icon ) : ?><?php echo esc_html( $btn1_icon ); ?> <?php endif; ?>
							<?php echo esc_html( $btn1_label ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $btn2_label ) : ?>
						<a href="<?php echo esc_url( $btn2_url ); ?>" class="<?php echo esc_attr( $btn2_class ); ?>">
							<?php if ( $btn2_icon ) : ?><?php echo esc_html( $btn2_icon ); ?> <?php endif; ?>
							<?php echo esc_html( $btn2_label ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $extra ) : ?>
				<div class="ch-page-hero__extra">
					<?php echo wp_kses_post( $extra ); ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
