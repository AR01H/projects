<?php
/**
 * Component: Post / Category Filter  (reusable)
 * ---------------------------------------------------------------------------
 * Compact category filter for archive / blog templates.
 *   • Up to `threshold` categories → a tidy row of chips.
 *   • More than `threshold`        → a single dropdown (saves the vertical
 *                                     space a wrapping pill-row would waste).
 *
 *   get_template_part( 'components/post-filter', null, array(
 *       'categories' => get_categories( array( 'hide_empty' => true ) ),
 *       'active'     => $active_cat,          // active term slug ('' = all)
 *       'base_url'   => get_permalink(),
 *       'all_label'  => 'All Articles',
 *       'label'      => 'Filter',
 *       'threshold'  => 2,                    // > this many → dropdown
 *   ) );
 * ---------------------------------------------------------------------------
 */
defined( 'ABSPATH' ) || exit;

$categories = $args['categories'] ?? array();
$active     = (string) ( $args['active']   ?? '' );
$base_url   = $args['base_url']  ?? get_permalink();
$all_label  = $args['all_label'] ?? 'All';
$label      = $args['label']     ?? 'Filter';
$threshold  = (int) ( $args['threshold'] ?? 2 );

$categories = array_values( array_filter( (array) $categories ) );
if ( empty( $categories ) ) {
	return;
}

$use_dropdown = count( $categories ) > $threshold;
?>
<div class="ch-filter">
	<div class="container">
		<div class="ch-filter__inner">
			<span class="ch-filter__label"><?php echo esc_html( $label ); ?></span>

			<?php if ( $use_dropdown ) : ?>
				<select class="ch-filter__select"
					onchange="if(this.value){window.location.href=this.value;}"
					aria-label="<?php echo esc_attr( $label ); ?>">
					<option value="<?php echo esc_url( $base_url ); ?>" <?php selected( $active, '' ); ?>>
						<?php echo esc_html( $all_label ); ?>
					</option>
					<?php foreach ( $categories as $cat ) : ?>
						<option value="<?php echo esc_url( add_query_arg( 'category', $cat->slug, $base_url ) ); ?>"
							<?php selected( $active, $cat->slug ); ?>>
							<?php echo esc_html( $cat->name ); ?><?php echo isset( $cat->count ) ? ' (' . (int) $cat->count . ')' : ''; ?>
						</option>
					<?php endforeach; ?>
				</select>

			<?php else : ?>
				<div class="ch-filter__chips">
					<a href="<?php echo esc_url( $base_url ); ?>"
						class="ch-filter__chip<?php echo '' === $active ? ' is-active' : ''; ?>">
						<?php echo esc_html( $all_label ); ?>
					</a>
					<?php foreach ( $categories as $cat ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'category', $cat->slug, $base_url ) ); ?>"
							class="ch-filter__chip<?php echo $active === $cat->slug ? ' is-active' : ''; ?>">
							<?php echo esc_html( $cat->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
