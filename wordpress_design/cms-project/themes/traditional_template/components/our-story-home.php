<?php
/**
 * Our Story – Vintage three-column layout.
 * Matches reference: parchment section with photo, text, and press machine.
 */
defined( 'ABSPATH' ) || exit;

/**
 * All text/images below come from admin/data/about.json (first entry).
 * Edit the copy there - nothing user-facing should be hardcoded in this file.
 */
$about = NT_Data_Provider::get('about');
$about = ( is_array($about) && !empty($about) ) ? (array) $about[0] : [];

$photo     = $about['image']         ?? '';
$photo_alt = $about['image_alt']     ?? '';
$machine   = $about['machine_image'] ?? '';

$tag         = $about['tag']          ?? '';
$head_lead   = $about['heading_lead'] ?? ( $about['title'] ?? '' );
$head_em     = $about['heading_em']   ?? '';
$subtitle    = $about['subtitle']     ?? '';
$stamp_1     = $about['stamp_line1']  ?? '';
$stamp_2     = $about['stamp_line2']  ?? '';
$cta_label   = $about['cta_label']    ?? '';
$cta_url     = $about['cta_url']      ?? '/about/';

// Body copy: any body_N key, in order, so you can add body_4+ in JSON without touching PHP.
$paras = [];
foreach ( $about as $k => $v ) {
	if ( 0 === strpos( (string) $k, 'body_' ) && '' !== trim( (string) $v ) ) {
		$paras[] = (string) $v;
	}
}
?>
<section class="app-tstory" id="our-story">
	<div class="container app-tstory__inner">

		<!-- Left: Vintage photo with stamp -->
		<figure class="app-tstory__photo">
			<?php if ( $photo ) : ?>
				<img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $photo_alt ); ?>" loading="lazy">
			<?php endif; ?>
			<?php if ( $stamp_1 || $stamp_2 ) : ?>
				<span class="app-tstory__stamp" aria-hidden="true">
					<span><?php echo esc_html( $stamp_1 ); ?></span>
					<strong><?php echo esc_html( $stamp_2 ); ?></strong>
				</span>
			<?php endif; ?>
		</figure>

		<!-- Centre: text content -->
		<div class="app-tstory__text">
			<?php if ( $tag ) : ?>
				<span class="app-section-tag"><?php echo esc_html( $tag ); ?></span>
			<?php endif; ?>
			<h2 class="app-tstory__heading">
				<?php echo esc_html( $head_lead ); ?><?php if ( $head_em ) : ?> <em><?php echo esc_html( $head_em ); ?></em><?php endif; ?>
			</h2>
			<?php if ( $subtitle ) : ?>
				<p class="app-tstory__script">
					<?php echo wp_kses( $subtitle, array( 'br' => array(), 'em' => array(), 'strong' => array() ) ); ?>
				</p>
			<?php endif; ?>
			<?php foreach ( $paras as $p ) : ?>
				<p class="app-tstory__body"><?php echo esc_html( $p ); ?></p>
			<?php endforeach; ?>
			<?php if ( $cta_label ) : ?>
				<a href="<?php echo esc_url( app_link( $cta_url ) ); ?>" class="btn">
					<?php echo esc_html( $cta_label ); ?> &rarr;
				</a>
			<?php endif; ?>
		</div>

		<!-- Right: press machine photo -->
		<div class="app-tstory__machine" aria-hidden="true">
			<img src="<?php echo esc_url( $machine ); ?>" alt="" loading="lazy">
		</div>

	</div>
</section>
