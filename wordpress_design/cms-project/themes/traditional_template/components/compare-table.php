<?php
/**
 * Compare table - a side-by-side feature matrix.
 *
 * GENERIC: any set of options judged on the same criteria (packages, plans,
 * tiers, memberships). Switch data per page with `source`.
 *
 * A value of yes/true/1 becomes a tick, no/false/0/"" becomes a dash, and
 * anything else is printed as-is, so a row can mix ticks and short text.
 * Column count follows `plans`; each row's `values` is padded or trimmed to
 * match, so a half-filled row can never shift the grid out of alignment.
 *
 * Data: { tag, title (em allowed), sub, footnote,
 *         plans[] { name, note, featured (bool) },
 *         rows[]  { label, values[] } }
 */
defined( 'ABSPATH' ) || exit;

$ct_source = ( isset( $source ) && $source ) ? (string) $source : 'compare_table';
$data      = nt_data( $ct_source );
$plans     = ( is_array( $data ) && ! empty( $data['plans'] ) ) ? array_values( (array) $data['plans'] ) : array();
$rows      = ( is_array( $data ) && ! empty( $data['rows'] ) ) ? (array) $data['rows'] : array();
if ( empty( $plans ) || empty( $rows ) ) {
	return;
}

$tag      = $data['tag']      ?? '';
$title    = $data['title']    ?? '';
$sub      = $data['sub']      ?? '';
$footnote = $data['footnote'] ?? '';
$count    = count( $plans );

/**
 * Decide how one cell prints: tick, dash, or its own text.
 */
$cell = static function ( $value ) {
	$raw = is_string( $value ) ? strtolower( trim( $value ) ) : $value;
	if ( true === $raw || 1 === $raw || in_array( $raw, array( 'yes', 'true', '1' ), true ) ) {
		return array( 'yes', '' );
	}
	if ( false === $raw || null === $raw || 0 === $raw || in_array( $raw, array( 'no', 'false', '0', '' ), true ) ) {
		return array( 'no', '' );
	}
	return array( 'text', (string) $value );
};
?>
<section class="nt-compare" id="compare-table">
	<div class="container">

		<?php if ( $tag || $title || $sub ) : ?>
			<div class="nt-section-center">
				<?php if ( $tag ) : ?><div class="nt-section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
				<?php if ( $title ) : ?>
					<h2 class="section-title"><?php echo wp_kses( $title, array( 'em' => array() ) ); ?></h2>
				<?php endif; ?>
				<?php if ( $sub ) : ?><p class="section-body"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="nt-compare__scroll" tabindex="0" role="region"
		     aria-label="<?php esc_attr_e( 'Comparison table', NT_TEXT_DOMAIN ); ?>">
			<table class="nt-compare__table">
				<thead>
					<tr>
						<th scope="col" class="nt-compare__corner"><span class="screen-reader-text"><?php esc_html_e( 'Feature', NT_TEXT_DOMAIN ); ?></span></th>
						<?php foreach ( $plans as $plan ) :
							$plan = (array) $plan;
						?>
							<th scope="col" class="nt-compare__plan<?php echo ! empty( $plan['featured'] ) ? ' is-featured' : ''; ?>">
								<span class="nt-compare__plan-name"><?php echo esc_html( $plan['name'] ?? '' ); ?></span>
								<?php if ( ! empty( $plan['note'] ) ) : ?>
									<span class="nt-compare__plan-note"><?php echo esc_html( $plan['note'] ); ?></span>
								<?php endif; ?>
							</th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rows as $row ) :
						$row   = (array) $row;
						$label = trim( (string) ( $row['label'] ?? '' ) );
						if ( '' === $label ) {
							continue;
						}
						$values = ! empty( $row['values'] ) ? array_values( (array) $row['values'] ) : array();
						$values = array_slice( array_pad( $values, $count, '' ), 0, $count );
					?>
						<tr>
							<th scope="row" class="nt-compare__label"><?php echo esc_html( $label ); ?></th>
							<?php foreach ( $values as $i => $value ) :
								list( $kind, $text ) = $cell( $value );
								$plan_i  = (array) $plans[ $i ];
								$featured = ! empty( $plan_i['featured'] ) ? ' is-featured' : '';
							?>
								<td class="nt-compare__cell nt-compare__cell--<?php echo esc_attr( $kind ); ?><?php echo esc_attr( $featured ); ?>"
								    data-plan="<?php echo esc_attr( $plan_i['name'] ?? '' ); ?>">
									<?php if ( 'yes' === $kind ) : ?>
										<span aria-hidden="true">&#10003;</span>
										<span class="screen-reader-text"><?php esc_html_e( 'Included', NT_TEXT_DOMAIN ); ?></span>
									<?php elseif ( 'no' === $kind ) : ?>
										<span aria-hidden="true">&ndash;</span>
										<span class="screen-reader-text"><?php esc_html_e( 'Not included', NT_TEXT_DOMAIN ); ?></span>
									<?php else : ?>
										<?php echo esc_html( $text ); ?>
									<?php endif; ?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<?php if ( $footnote ) : ?>
			<p class="nt-compare__footnote"><?php echo esc_html( $footnote ); ?></p>
		<?php endif; ?>

	</div>
</section>
