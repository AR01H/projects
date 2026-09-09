<?php
/**
 * components/sections/post_buying_support.php - Reusable Post-Buying & Moving Support Section
 *
 * Renders a high-impact, reusable 4-card grid showcasing post-purchase services:
 * Packers & Movers, Repairs & Maintenance, Utility Setup, and Lifetime Advisory.
 *
 * Props:
 *   $support array {
 *     eyebrow?    string
 *     heading?    string
 *     subheading? string
 *     badge?      string
 *     cards[] {
 *       icon      string  FontAwesome icon class
 *       title     string  Card title
 *       text      string  Card description
 *       tag?      string  Optional micro-badge tag (e.g. 'Relocation Support')
 *     }
 *   }
 *
 * Usage:
 *   adn_component( 'sections/post_buying_support', array(
 *       'support' => $ctx['post_buying_support'],
 *   ) );
 */
defined( 'ABSPATH' ) || exit;

$_s = isset( $support ) && is_array( $support ) ? $support : array();
$_cards = isset( $_s['cards'] ) && is_array( $_s['cards'] ) ? $_s['cards'] : array();
if ( empty( $_cards ) ) return;

$_eyb = isset( $_s['eyebrow'] )    ? (string) $_s['eyebrow']    : 'Beyond Handover';
$_hdg = isset( $_s['heading'] )    ? (string) $_s['heading']    : 'Complete Post-Buying & Moving Support';
$_sub = isset( $_s['subheading'] ) ? (string) $_s['subheading'] : '';
$_bdg = isset( $_s['badge'] )      ? (string) $_s['badge']      : '';
?>
<section class="hiw-pbs-section" aria-label="<?php echo esc_attr( $_hdg ); ?>">
	<?php /* Ambient animated background glows */ ?>
	<div class="hiw-pbs-bg" aria-hidden="true">
		<span class="hiw-pbs-orb hiw-pbs-orb--1"></span>
		<span class="hiw-pbs-orb hiw-pbs-orb--2"></span>
		<svg class="hiw-pbs-glyph hiw-pbs-glyph--truck" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M10 30 H65 V70 H10 Z M65 45 H85 L92 56 V70 H65 Z" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round" opacity="0.35"/>
			<circle cx="28" cy="74" r="8" stroke="currentColor" stroke-width="2.5"/>
			<circle cx="78" cy="74" r="8" stroke="currentColor" stroke-width="2.5"/>
		</svg>
		<svg class="hiw-pbs-glyph hiw-pbs-glyph--home" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M20 50 L50 22 L80 50 V82 H20 Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" opacity="0.3"/>
			<rect x="40" y="55" width="20" height="27" stroke="currentColor" stroke-width="2"/>
		</svg>
	</div>

	<div class="container">
		<?php adn_component( 'parts/section_headers/eyebrow_heading', array(
			'eyebrow'       => $_eyb,
			'heading'       => $_hdg,
			'subheading'    => $_sub,
			'wrapper_class' => 'hiw-pbs-header',
		) ); ?>

		<div class="hiw-pbs-grid">
			<?php foreach ( $_cards as $_idx => $_card ) :
				$_ico = isset( $_card['icon'] )  ? (string) $_card['icon']  : 'fa-solid fa-check';
				$_ttl = isset( $_card['title'] ) ? (string) $_card['title'] : '';
				$_txt = isset( $_card['text'] )  ? (string) $_card['text']  : '';
				$_tag = isset( $_card['tag'] )   ? (string) $_card['tag']   : '';
			?>
			<div class="hiw-pbs-card" style="--pbs-delay: <?php echo (int)( $_idx * 60 ); ?>ms;">
				<div class="hiw-pbs-card-inner">
					<div class="hiw-pbs-top">
						<div class="hiw-pbs-icon-wrap" aria-hidden="true">
							<span class="hiw-pbs-icon"><?php echo adn_icon( $_ico ); ?></span>
						</div>
						<?php if ( '' !== $_tag ) : ?>
						<span class="hiw-pbs-tag"><?php echo esc_html( $_tag ); ?></span>
						<?php endif; ?>
					</div>

					<h3 class="hiw-pbs-title"><?php echo esc_html( $_ttl ); ?></h3>
					<p class="hiw-pbs-text"><?php echo esc_html( $_txt ); ?></p>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
