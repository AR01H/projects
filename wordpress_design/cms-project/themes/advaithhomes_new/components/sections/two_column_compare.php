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
