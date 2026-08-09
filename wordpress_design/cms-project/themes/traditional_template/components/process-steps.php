<?php
/**
 * Process steps - a numbered "how it's made / how it works" journey.
 *
 * Deliberately GENERIC: renders whatever ordered steps the JSON supplies, so it
 * works for a production journey, an onboarding flow, a service process, a
 * booking flow, etc. Nothing here is tied to one industry.
 *
 * Data (switchable per page):
 *   page_sections.json -> { "component": "process-steps", "args": { "source": "process_steps" } }
 * Source shape: { tag, title (em allowed), sub, steps[] { title, desc, image, alt } }
 *
 * Renders nothing when there are no steps.
 */
defined( 'ABSPATH' ) || exit;

$ps_source = ( isset( $source ) && $source ) ? (string) $source : 'process_steps';
$data      = App_Data_Provider::get( $ps_source );
$steps     = ( is_array( $data ) && ! empty( $data['steps'] ) ) ? (array) $data['steps'] : array();
if ( empty( $steps ) ) {
	return;
}

$tag   = $data['tag']   ?? '';
$title = $data['title'] ?? '';
$sub   = $data['sub']   ?? '';
?>
<section class="app-process" id="process">
	<div class="container">

		<?php if ( $tag || $title || $sub ) : ?>
			<div class="app-section-center app-process__header">
				<?php if ( $tag ) : ?><div class="app-section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
				<?php if ( $title ) : ?>
					<h2 class="section-title"><?php echo wp_kses( $title, array( 'em' => array(), 'span' => array( 'class' => array() ) ) ); ?></h2>
				<?php endif; ?>
				<?php if ( $sub ) : ?><p class="section-body"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<ol class="app-process__list">
			<?php foreach ( $steps as $i => $step ) :
				$step  = (array) $step;
				$s_ttl = $step['title'] ?? '';
				if ( '' === trim( (string) $s_ttl ) ) {
					continue;
				}
				$s_desc = $step['desc']  ?? '';
				$s_img  = $step['image'] ?? '';
				$s_alt  = $step['alt']   ?? $s_ttl;
			?>
				<li class="app-process__step">
					<span class="app-process__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<?php if ( $s_img ) : ?>
						<figure class="app-process__media">
							<img src="<?php echo esc_url( $s_img ); ?>" alt="<?php echo esc_attr( $s_alt ); ?>" loading="lazy">
						</figure>
					<?php endif; ?>
					<h3 class="app-process__title"><?php echo esc_html( $s_ttl ); ?></h3>
					<?php if ( $s_desc ) : ?>
						<p class="app-process__desc"><?php echo esc_html( $s_desc ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>

	</div>
</section>
