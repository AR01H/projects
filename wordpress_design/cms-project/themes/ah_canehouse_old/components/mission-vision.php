<?php
/**
 * Template Part: Mission / Vision / Values Section
 *
 * Usage (in any page template or template part):
 *
 *   $mvv = [
 *       [ 'icon' => '🌿', 'title' => 'Our Mission', 'text' => 'We exist to...'  ],
 *       [ 'icon' => '🔭', 'title' => 'Our Vision',  'text' => 'We imagine...'   ],
 *       [ 'icon' => '💎', 'title' => 'Our Values',  'text' => 'We believe...'   ],
 *   ];
 *   get_template_part( 'template-parts/section-mvv', null, [ 'mvv' => $mvv ] );
 *
 * OR use the helper function (recommended):
 *
 *   echo render_mvv_section( $mvv );
 *
 * @package YourTheme
 */

// Support both get_template_part( ..., $args ) and direct $mvv variable.
if ( ! isset( $mvv ) ) {
	$mvv = $args['mvv'] ?? [];
}

if ( empty( $mvv ) || ! is_array( $mvv ) ) {
	return;
}

$anims = [ 'fade-left', 'fade-up', 'fade-right' ];
?>

<section class="about-mission">
	<div class="container">

		<?php
		$_d_mv = CH_Story_Data::mission_vision_settings();
		get_template_part( 'components/section-header', null, [
			'tag'   => $_d_mv['tag']   ?? '',
			'title' => $_d_mv['title'] ?? '',
		] ); ?>

		<div class="mission-carousel" data-oc data-oc-autoplay="4500">
			<div class="mission-grid" data-oc-track>
				<?php foreach ( $mvv as $i => $card ) :
					$card = (array) $card;
					$cls  = $anims[ $i % 3 ] ?? 'fade-up';
				?>
					<div class="mission-card <?php echo esc_attr( $cls ); ?>">
						<div class="mission-icon">
							<?php echo esc_html( $card['icon'] ?? '🌿' ); ?>
						</div>
						<h3><?php echo esc_html( $card['title'] ?? '' ); ?></h3>
						<p><?php echo esc_html( $card['text'] ?? '' ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>

			<?php if ( count( $mvv ) > 1 ) : ?>
				<nav class="mission-nav" aria-label="<?php esc_attr_e( 'Mission carousel navigation', 'your-theme' ); ?>">
					<button type="button" class="mission-arrow" data-oc-prev aria-label="<?php esc_attr_e( 'Previous', 'your-theme' ); ?>">&#8249;</button>

					<div class="mission-dots" data-oc-dots>
						<?php foreach ( $mvv as $mi => $card ) : ?>
							<button
								type="button"
								class="mission-dot<?php echo 0 === $mi ? ' active' : ''; ?>"
								data-go="<?php echo (int) $mi; ?>"
								aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'your-theme' ), $mi + 1 ) ); ?>"
							></button>
						<?php endforeach; ?>
					</div>

					<button type="button" class="mission-arrow" data-oc-next aria-label="<?php esc_attr_e( 'Next', 'your-theme' ); ?>">&#8250;</button>
				</nav>
			<?php endif; ?>
		</div><!-- /.mission-carousel -->

	</div>
</section>