<?php
/**
 * components/sections/step_timeline.php
 *
 * Graphical, alternating step timeline. A vertical connecting line runs
 * down the centre (desktop) / left edge (mobile) linking numbered,
 * icon-badged step cards. Named after its shape (not "how it works")
 * since a numbered process timeline is a reusable pattern, not tied to
 * any one page.
 *
 * Props: $timeline {
 *   eyebrow, heading, subheading,
 *   steps[] { number, icon, title, description, url }
 *     - url is optional: when set, the whole step card becomes a link.
 * }
 * Usage: adn_component( 'sections/step_timeline', array( 'timeline' => $ctx['process'] ) );
 */
defined( 'ABSPATH' ) || exit;

$_t     = isset( $timeline ) && is_array( $timeline ) ? $timeline : array();
$_steps = isset( $_t['steps'] ) && is_array( $_t['steps'] ) ? $_t['steps'] : array();
if ( empty( $_steps ) ) return;

$_eyb = isset( $_t['eyebrow'] )    ? (string) $_t['eyebrow']    : '';
$_hdg = isset( $_t['heading'] )    ? (string) $_t['heading']    : '';
$_sub = isset( $_t['subheading'] ) ? (string) $_t['subheading'] : '';
?>
<section class="hiw-process-section">

	<?php /* Ambient decorative blobs - reuses the same .phb-circle classes as
	   the site's hero banners (assets/css/shared.css), already loaded on
	   every page, so this adds zero new CSS. Purely decorative + inert. */ ?>
	<span class="phb-circle phb-circle--a" aria-hidden="true"></span>
	<span class="phb-circle phb-circle--b" aria-hidden="true"></span>

	<div class="container">

		<?php adn_component( 'parts/section_headers/eyebrow_heading', array(
			'eyebrow'       => $_eyb,
			'heading'       => $_hdg,
			'subheading'    => $_sub,
			'wrapper_class' => 'hiw-process-header',
		) ); ?>

		<div class="hiw-timeline">
			<?php foreach ( $_steps as $_i => $_s ) :
				$_num  = esc_html( isset( $_s['number'] )      ? (string) $_s['number']      : str_pad( (string) ( $_i + 1 ), 2, '0', STR_PAD_LEFT ) );
				$_ico  = adn_icon( isset( $_s['icon'] )        ? (string) $_s['icon']        : '' );
				$_ttl  = esc_html( isset( $_s['title'] )       ? (string) $_s['title']       : '' );
				$_dsc  = esc_html( isset( $_s['description'] ) ? (string) $_s['description'] : '' );
				$_url  = isset( $_s['url'] ) ? (string) $_s['url'] : '';
				$_side = ( 0 === $_i % 2 ) ? 'hiw-step--left' : 'hiw-step--right';
				$_last = ( $_i === count( $_steps ) - 1 );
				$_tag  = '' !== $_url ? 'a' : 'div';
			?>
				<div class="hiw-step <?php echo esc_attr( $_side ); ?><?php echo $_last ? ' hiw-step--last' : ''; ?>">
					<div class="hiw-step-node">
						<span class="hiw-step-icon" aria-hidden="true"><?php echo $_ico; ?></span>
						<span class="hiw-step-num" aria-hidden="true"><?php echo $_num; ?></span>
					</div>
					<<?php echo $_tag; ?> class="hiw-step-card<?php echo '' !== $_url ? ' hiw-step-card--link' : ''; ?>"<?php echo '' !== $_url ? ' href="' . esc_url( adn_link( $_url ) ) . '"' : ''; ?>>
						<h3><?php echo $_ttl; ?></h3>
						<p><?php echo $_dsc; ?></p>
						<?php if ( '' !== $_url ) : ?><span class="hiw-step-card-cta" aria-hidden="true">→</span><?php endif; ?>
					</<?php echo $_tag; ?>>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
