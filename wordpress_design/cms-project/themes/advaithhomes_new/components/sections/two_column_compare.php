<?php
/**
 * components/sections/two_column_compare.php
 * Comparison section: supports 2-column or 3-column comparison cards
 * (e.g. "Going Solo vs. Other Services vs. With ADVAITH HOMES").
 *
 * Props: $compare {
 *   eyebrow, heading, subheading?,
 *   columns[] { label, subtitle?, icon?, tone ("con"|"neutral"|"pro"), is_highlighted?, badge?, items[] }
 *   -- OR legacy: con { label, items[] }, pro { label, items[] }
 * }
 */
defined( 'ABSPATH' ) || exit;

$_c       = isset( $compare ) && is_array( $compare ) ? $compare : array();
$_columns = isset( $_c['columns'] ) && is_array( $_c['columns'] ) ? $_c['columns'] : array();

// Convert legacy con/pro to columns array if needed
if ( empty( $_columns ) ) {
	$_con = isset( $_c['con'] ) && is_array( $_c['con'] ) ? $_c['con'] : array();
	$_pro = isset( $_c['pro'] ) && is_array( $_c['pro'] ) ? $_c['pro'] : array();
	if ( ! empty( $_con['items'] ) ) {
		$_columns[] = array(
			'label' => isset( $_con['label'] ) ? (string) $_con['label'] : 'Doing It Alone',
			'tone'  => 'con',
			'items' => (array) $_con['items'],
		);
	}
	if ( ! empty( $_pro['items'] ) ) {
		$_columns[] = array(
			'label'          => isset( $_pro['label'] ) ? (string) $_pro['label'] : 'With ADVAITH HOMES',
			'tone'           => 'pro',
			'is_highlighted' => true,
			'items'          => (array) $_pro['items'],
		);
	}
}

if ( empty( $_columns ) ) return;

$_eyb = isset( $_c['eyebrow'] )    ? (string) $_c['eyebrow']    : '';
$_hdg = isset( $_c['heading'] )    ? (string) $_c['heading']    : '';
$_sub = isset( $_c['subheading'] ) ? (string) $_c['subheading'] : '';
$_col_count = count( $_columns );
$_grid_class = ( 3 === $_col_count ) ? 'hiw-compare-grid--three' : 'hiw-compare-grid--two';
?>
<section class="hiw-compare-section">
	<?php /* Ambient Animated Luxury Backdrop */ ?>
	<div class="hiw-compare-bg" aria-hidden="true">
		<div class="hiw-cmp-orb hiw-cmp-orb--gold"></div>
		<div class="hiw-cmp-orb hiw-cmp-orb--emerald"></div>
		<div class="hiw-cmp-orb hiw-cmp-orb--pearl"></div>
		<div class="hiw-cmp-light-sweep"></div>
		
		<!-- Rotating Geometric Compass / Balance Ring SVG -->
		<svg class="hiw-cmp-svg-glyph hiw-cmp-svg-glyph--ring" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
			<circle cx="100" cy="100" r="90" stroke="currentColor" stroke-width="1.5" stroke-dasharray="6 8" opacity="0.35"/>
			<circle cx="100" cy="100" r="70" stroke="currentColor" stroke-width="2" stroke-dasharray="12 6" opacity="0.45"/>
			<circle cx="100" cy="100" r="50" stroke="currentColor" stroke-width="1.5" opacity="0.6"/>
			<line x1="100" y1="5" x2="100" y2="195" stroke="currentColor" stroke-width="1" stroke-dasharray="4 4" opacity="0.3"/>
			<line x1="5" y1="100" x2="195" y2="100" stroke="currentColor" stroke-width="1" stroke-dasharray="4 4" opacity="0.3"/>
		</svg>

		<!-- Floating Milestone Shield SVG -->
		<svg class="hiw-cmp-svg-glyph hiw-cmp-svg-glyph--shield" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M80 10 L140 36 V90 C140 126 80 152 80 152 C80 152 20 126 20 90 V36 Z" stroke="currentColor" stroke-width="2" stroke-dasharray="8 6" opacity="0.3"/>
			<path d="M80 30 L120 50 V85 C120 110 80 130 80 130 C80 130 40 110 40 85 V50 Z" stroke="currentColor" stroke-width="1.5" opacity="0.4"/>
			<circle cx="80" cy="80" r="15" stroke="currentColor" stroke-width="2"/>
			<circle cx="80" cy="80" r="4" fill="currentColor"/>
		</svg>

		<!-- Subtle Dynamic Flow Waves SVG -->
		<svg class="hiw-cmp-svg-waves" viewBox="0 0 1440 320" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
			<path class="hiw-cmp-wave-1" d="M0,160L48,176C96,192,192,224,288,218.7C384,213,480,171,576,165.3C672,160,768,192,864,208C960,224,1056,224,1152,197.3C1248,171,1344,117,1392,90.7L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z" fill="url(#cmp-wave-grad-1)" opacity="0.5"/>
			<path class="hiw-cmp-wave-2" d="M0,96L60,117.3C120,139,240,181,360,181.3C480,181,600,139,720,138.7C840,139,960,181,1080,192C1200,203,1320,181,1380,170.7L1440,160L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z" fill="url(#cmp-wave-grad-2)" opacity="0.35"/>
			<defs>
				<linearGradient id="cmp-wave-grad-1" x1="0%" y1="0%" x2="100%" y2="0%">
					<stop offset="0%" stop-color="#c9a84c" stop-opacity="0.08"/>
					<stop offset="50%" stop-color="#1e3a2f" stop-opacity="0.04"/>
					<stop offset="100%" stop-color="#c9a84c" stop-opacity="0.09"/>
				</linearGradient>
				<linearGradient id="cmp-wave-grad-2" x1="100%" y1="0%" x2="0%" y2="0%">
					<stop offset="0%" stop-color="#1e3a2f" stop-opacity="0.06"/>
					<stop offset="50%" stop-color="#c9a84c" stop-opacity="0.05"/>
					<stop offset="100%" stop-color="#1e3a2f" stop-opacity="0.07"/>
				</linearGradient>
			</defs>
		</svg>

		<!-- Star Sparkle Particles -->
		<span class="hiw-cmp-sparkle hiw-cmp-sparkle--1"></span>
		<span class="hiw-cmp-sparkle hiw-cmp-sparkle--2"></span>
		<span class="hiw-cmp-sparkle hiw-cmp-sparkle--3"></span>
	</div>

	<div class="container">
		<?php adn_component( 'parts/section_headers/eyebrow_heading', array(
			'eyebrow'       => $_eyb,
			'heading'       => $_hdg,
			'subheading'    => $_sub,
			'wrapper_class' => 'hiw-compare-header',
		) ); ?>

		<div class="hiw-compare-grid <?php echo esc_attr( $_grid_class ); ?>">
			<?php foreach ( $_columns as $_col ) :
				$_lbl   = esc_html( isset( $_col['label'] ) ? (string) $_col['label'] : '' );
				$_subt  = esc_html( isset( $_col['subtitle'] ) ? (string) $_col['subtitle'] : '' );
				$_tone  = isset( $_col['tone'] ) ? (string) $_col['tone'] : 'neutral';
				$_is_hi = ! empty( $_col['is_highlighted'] );
				$_badge = esc_html( isset( $_col['badge'] ) ? (string) $_col['badge'] : ( $_is_hi ? 'The Clear Choice' : '' ) );
				$_ico   = isset( $_col['icon'] ) ? (string) $_col['icon'] : ( 'pro' === $_tone ? 'fa-solid fa-shield-halved' : ( 'con' === $_tone ? 'fa-solid fa-user' : 'fa-solid fa-building' ) );
				$_items = isset( $_col['items'] ) && is_array( $_col['items'] ) ? $_col['items'] : array();

				$_cls = 'hiw-compare-col hiw-compare-' . $_tone;
				if ( $_is_hi ) { $_cls .= ' hiw-compare-col--highlighted'; }
			?>
			<div class="<?php echo esc_attr( $_cls ); ?>">
				<?php if ( '' !== $_badge ) : ?>
				<span class="hiw-compare-badge"><?php echo adn_icon( 'fa-solid fa-star' ); ?> <?php echo $_badge; ?></span>
				<?php endif; ?>

				<div class="hiw-compare-col-header">
					<span class="hiw-compare-col-icon" aria-hidden="true"><?php echo adn_icon( $_ico ); ?></span>
					<div class="hiw-compare-col-title-wrap">
						<h3 class="hiw-compare-label"><?php echo $_lbl; ?></h3>
						<?php if ( '' !== $_subt ) : ?>
						<p class="hiw-compare-subtitle"><?php echo $_subt; ?></p>
						<?php endif; ?>
					</div>
				</div>

				<ul class="hiw-compare-list <?php echo ( 'pro' === $_tone ) ? 'hiw-compare-list--check' : 'hiw-compare-list--cross'; ?>">
					<?php foreach ( $_items as $_it ) : ?>
					<li class="hiw-compare-item">
						<span class="hiw-compare-item-icon" aria-hidden="true">
							<?php echo ( 'pro' === $_tone ) ? adn_icon( 'fa-solid fa-check' ) : adn_icon( 'fa-solid fa-xmark' ); ?>
						</span>
						<span class="hiw-compare-item-text"><?php echo esc_html( (string) $_it ); ?></span>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
