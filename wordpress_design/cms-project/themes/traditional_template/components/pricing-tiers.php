<?php
/**
 * Pricing tiers - a row of plan/package cards plus an optional "what's included"
 * checklist.
 *
 * Deliberately GENERIC and reusable: it renders whatever tiers the JSON supplies,
 * so it works for franchise investment levels, service packages, membership
 * plans, room types, etc. Nothing here is tied to one industry.
 *
 * Switch data per page with `source` (defaults to pricing_tiers).
 * Data: admin/data/pricing_tiers.json
 *   tag, title (em allowed), sub
 *   tiers[]      { name, price, note, badge, features[] }
 *   offer_title, offers[]
 *
 * Renders nothing when there are no tiers.
 */
defined( 'ABSPATH' ) || exit;

$pt_source = ( isset( $source ) && $source ) ? (string) $source : 'pricing_tiers';
$data      = nt_data( $pt_source );
$tiers     = ( is_array( $data ) && ! empty( $data['tiers'] ) ) ? (array) $data['tiers'] : array();
if ( empty( $tiers ) ) {
	return;
}

$tag         = $data['tag']         ?? '';
$title       = $data['title']       ?? '';
$sub         = $data['sub']         ?? '';
$offer_title = $data['offer_title'] ?? '';
$offers      = ( ! empty( $data['offers'] ) ) ? (array) $data['offers'] : array();
?>
<section class="nt-tiers" id="pricing-tiers">
	<div class="container">

		<?php if ( $tag || $title || $sub ) : ?>
			<div class="nt-section-center nt-tiers__header">
				<?php if ( $tag ) : ?><div class="nt-section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
				<?php if ( $title ) : ?>
					<h2 class="section-title"><?php echo wp_kses( $title, array( 'em' => array(), 'span' => array( 'class' => array() ) ) ); ?></h2>
				<?php endif; ?>
				<?php if ( $sub ) : ?><p class="section-body"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="nt-tiers__grid">
			<?php foreach ( $tiers as $tier ) :
				$tier     = (array) $tier;
				$name     = $tier['name'] ?? '';
				if ( '' === trim( (string) $name ) ) {
					continue;
				}
				$price    = $tier['price']    ?? '';
				$note     = $tier['note']     ?? '';
				$badge    = $tier['badge']    ?? '';
				$features = ( ! empty( $tier['features'] ) ) ? (array) $tier['features'] : array();
			?>
				<article class="nt-tier">
					<?php if ( $badge ) : ?>
						<span class="nt-tier__badge"><?php echo esc_html( $badge ); ?></span>
					<?php endif; ?>
					<h3 class="nt-tier__name"><?php echo esc_html( $name ); ?></h3>
					<?php if ( $note ) : ?><span class="nt-tier__note"><?php echo esc_html( $note ); ?></span><?php endif; ?>
					<?php if ( $price ) : ?><div class="nt-tier__price"><?php echo esc_html( $price ); ?></div><?php endif; ?>
					<?php if ( ! empty( $features ) ) : ?>
						<ul class="nt-tier__features">
							<?php foreach ( $features as $feature ) : ?>
								<li><?php echo esc_html( (string) $feature ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $offers ) ) : ?>
			<div class="nt-tiers__offer">
				<?php if ( $offer_title ) : ?>
					<h3 class="nt-tiers__offer-title"><?php echo esc_html( $offer_title ); ?></h3>
				<?php endif; ?>
				<ul class="nt-tiers__offer-list">
					<?php foreach ( $offers as $offer ) : ?>
						<li><?php echo esc_html( (string) $offer ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

	</div>
</section>
