<?php
/**
 * Features ribbon bar.
 *
 * Args (all optional):
 *  class  (string)  Extra CSS class on wrapper div. Default: ''
 */
defined( 'ABSPATH' ) || exit;

$features    = ch_get_hire_features();
$extra_class = isset( $args['class'] ) ? ' ' . esc_attr( $args['class'] ) : '';

if ( empty( $features ) ) return;
?>

<div class="ch-features-ribbon<?php echo $extra_class; ?>">
	<div class="container">
		<div class="ch-ribbon-grid">
			<?php foreach ( $features as $feat ) :
				$feat = (array) $feat;
			?>
				<div class="ch-ribbon-item">
					<span class="ch-ribbon-icon"><?php echo esc_html( $feat['icon'] ?? '✓' ); ?></span>
					<span class="ch-ribbon-text"><?php echo esc_html( $feat['text'] ?? '' ); ?></span>
					<?php if ( ! empty( $feat['sub'] ) ) : ?>
					<span class="ch-ribbon-sub"><?php echo esc_html( $feat['sub'] ); ?></span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
