<?php
/**
 * components/logo-strip.php - a quiet row of marks.
 *
 * GENERIC: stockists, press mentions, partners, awards, certifications,
 * payment methods. Anything that is "logos in a line".
 *
 * Logos render in sepia and lift to full tone on hover, so a row of clashing
 * brand colours never breaks the parchment palette.
 *
 * Data: admin/data/<source>.json (default `logo_strip`)
 *   { tag, title, sub, marquee (bool), items[] { name, image, url, new_tab } }
 *
 * With `marquee: true` the row scrolls slowly and pauses on hover; it stops
 * entirely under prefers-reduced-motion (CSS, no JS).
 *
 * Args:
 *   source string  Which JSON file to read.
 */

defined( 'ABSPATH' ) || exit;

$nt_src   = ( isset( $source ) && $source ) ? (string) $source : 'logo_strip';
$nt_data  = App_Helpers::data( $nt_src );
$nt_items = ( is_array( $nt_data ) && ! empty( $nt_data['items'] ) ) ? (array) $nt_data['items'] : array();
if ( empty( $nt_items ) ) {
	return;
}

$nt_tag     = (string) ( $nt_data['tag'] ?? '' );
$nt_title   = (string) ( $nt_data['title'] ?? '' );
$nt_sub     = (string) ( $nt_data['sub'] ?? '' );
$nt_marquee = ! empty( $nt_data['marquee'] );
?>
<section class="app-logos<?php echo $nt_marquee ? ' app-logos--marquee' : ''; ?>" id="<?php echo esc_attr( sanitize_html_class( $nt_src ) ); ?>">
	<div class="container">

		<?php if ( $nt_tag || $nt_title || $nt_sub ) : ?>
			<div class="app-section-center">
				<?php if ( $nt_tag ) : ?><div class="app-section-tag"><?php echo esc_html( $nt_tag ); ?></div><?php endif; ?>
				<?php if ( $nt_title ) : ?><h2 class="section-title"><?php echo wp_kses( $nt_title, array( 'em' => array() ) ); ?></h2><?php endif; ?>
				<?php if ( $nt_sub ) : ?><p class="section-body"><?php echo esc_html( $nt_sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="app-logos__track">
			<?php
			// A marquee needs the row twice so the loop has no visible seam.
			$nt_passes = $nt_marquee ? 2 : 1;
			for ( $nt_pass = 0; $nt_pass < $nt_passes; $nt_pass++ ) :
				foreach ( $nt_items as $nt_item ) :
					$nt_item      = (array) $nt_item;
					$nt_item_name = trim( (string) ( $nt_item['name'] ?? '' ) );
					$nt_image     = (string) ( $nt_item['image'] ?? '' );
					if ( '' === $nt_item_name && '' === $nt_image ) {
						continue;
					}
					$nt_url = (string) ( $nt_item['url'] ?? '' );
					$nt_tag_name = ( '' !== $nt_url ) ? 'a' : 'span';
					?>
					<<?php echo $nt_tag_name; ?> class="app-logos__item"
						<?php if ( '' !== $nt_url ) : ?>
							href="<?php echo esc_url( App_Helpers::link( $nt_url ) ); ?>"
							<?php if ( ! empty( $nt_item['new_tab'] ) ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
						<?php endif; ?>
						<?php if ( $nt_pass > 0 ) : ?>aria-hidden="true"<?php endif; ?>>

						<?php if ( '' !== $nt_image ) : ?>
							<img src="<?php echo esc_url( App_Helpers::link( $nt_image ) ); ?>"
							     alt="<?php echo esc_attr( $nt_item_name ); ?>"
							     loading="lazy" decoding="async">
						<?php else : ?>
							<span class="app-logos__name"><?php echo esc_html( $nt_item_name ); ?></span>
						<?php endif; ?>
					</<?php echo $nt_tag_name; ?>>
					<?php
				endforeach;
			endfor;
			?>
		</div>

	</div>
</section>
