<?php
/**
 * components/parts/cta_banner.php - Full-width personalised guidance CTA banner.
 *
 * Props: $cta_banner { icon, title|heading, description|body, eyebrow?, cta|cta_primary { label, url }, cta_secondary? { label, url }, trust_items[]? }
 * Usage: adn_component( 'parts/cta_banner', array( 'cta_banner' => $ctx['cta_banner'] ) );
 */

defined( 'ABSPATH' ) || exit;

$cta_banner  = isset( $cta_banner ) && is_array( $cta_banner ) ? $cta_banner : array();
if ( empty( $cta_banner ) ) return;

$_ttl   = isset( $cta_banner['title'] ) ? (string) $cta_banner['title'] : ( isset( $cta_banner['heading'] ) ? (string) $cta_banner['heading'] : '' );
$_dsc   = isset( $cta_banner['description'] ) ? (string) $cta_banner['description'] : ( isset( $cta_banner['body'] ) ? (string) $cta_banner['body'] : '' );
$_eyb   = isset( $cta_banner['eyebrow'] ) ? (string) $cta_banner['eyebrow'] : '';
$_ico   = isset( $cta_banner['icon'] ) ? (string) $cta_banner['icon'] : 'fa-solid fa-comments';
$_cta1  = isset( $cta_banner['cta_primary'] ) && is_array( $cta_banner['cta_primary'] ) ? $cta_banner['cta_primary'] : ( isset( $cta_banner['cta'] ) && is_array( $cta_banner['cta'] ) ? $cta_banner['cta'] : array() );
$_cta2  = isset( $cta_banner['cta_secondary'] ) && is_array( $cta_banner['cta_secondary'] ) ? $cta_banner['cta_secondary'] : array();
$_trust = isset( $cta_banner['trust_items'] ) && is_array( $cta_banner['trust_items'] ) ? $cta_banner['trust_items'] : array(
	array( 'icon' => 'fa-solid fa-shield-halved', 'text' => 'Independent Advice' ),
	array( 'icon' => 'fa-solid fa-lock',           'text' => 'Completely Confidential' ),
	array( 'icon' => 'fa-solid fa-bolt',           'text' => 'Fast, Friendly Response' ),
);

if ( '' === $_ttl && '' === $_dsc ) return;
?>
<div class="cta-banner hiw-cta-card">
	<div class="cta-banner-content">
		<?php if ( '' !== $_eyb ) : ?>
		<span class="cta-banner-eyebrow"><?php echo esc_html( $_eyb ); ?></span>
		<?php endif; ?>

		<h3 class="cta-banner-heading"><?php echo esc_html( $_ttl ); ?></h3>

		<?php if ( '' !== $_dsc ) : ?>
		<p class="cta-banner-body"><?php echo esc_html( $_dsc ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $_trust ) ) : ?>
		<div class="cta-banner-trust-pills" aria-label="Trust highlights">
			<?php foreach ( $_trust as $_tr ) :
				$_tr_ico = isset( $_tr['icon'] ) ? (string) $_tr['icon'] : 'fa-solid fa-check';
				$_tr_txt = isset( $_tr['text'] ) ? (string) $_tr['text'] : ( is_string( $_tr ) ? $_tr : '' );
				if ( '' === $_tr_txt ) continue;
			?>
			<span class="cta-banner-trust-pill">
				<span class="cta-trust-pill-icon" aria-hidden="true"><?php echo adn_icon( $_tr_ico ); ?></span>
				<span><?php echo esc_html( $_tr_txt ); ?></span>
			</span>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>

	<div class="cta-banner-actions">
		<?php if ( ! empty( $_cta1['label'] ) ) : ?>
			<a href="<?php echo esc_url( adn_link( isset( $_cta1['url'] ) ? $_cta1['url'] : '' ) ); ?>" class="btn btn-accent btn-lg cta-btn-primary">
				<span><?php echo esc_html( $_cta1['label'] ); ?></span>
				<span class="cta-btn-arrow" aria-hidden="true">→</span>
			</a>
		<?php endif; ?>
		<?php if ( ! empty( $_cta2['label'] ) ) : ?>
			<a href="<?php echo esc_url( adn_link( isset( $_cta2['url'] ) ? $_cta2['url'] : '' ) ); ?>" class="btn btn-secondary-light btn-lg cta-btn-secondary">
				<?php echo esc_html( $_cta2['label'] ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
