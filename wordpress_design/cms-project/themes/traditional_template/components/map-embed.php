<?php
/**
 * components/map-embed.php - "find us" map, privacy-first.
 *
 * The map is NOT embedded on load. A still image sits in its place until the
 * visitor presses it, exactly like the video facade in common.js - so no
 * request reaches the map provider, and no third-party cookie is set, for
 * anyone who never asks for the map.
 *
 * The "Open in maps" link is a real anchor, so the section works with JS off
 * and for anyone who would rather not load an embed at all.
 *
 * Data: admin/data/<source>.json (default `map`)
 *   { tag, title, sub, embed_url, link_url, poster, poster_alt,
 *     play_label, note, address, directions_label }
 *
 * Args:
 *   source string  Which JSON file to read.
 */

defined( 'ABSPATH' ) || exit;

$nt_src   = ( isset( $source ) && $source ) ? (string) $source : 'map';
$nt_data  = nt_data( $nt_src );
$nt_embed = (string) ( $nt_data['embed_url'] ?? '' );
$nt_link  = (string) ( $nt_data['link_url'] ?? '' );
if ( '' === $nt_embed && '' === $nt_link ) {
	return;
}

$nt_tag    = (string) ( $nt_data['tag'] ?? '' );
$nt_title  = (string) ( $nt_data['title'] ?? '' );
$nt_sub    = (string) ( $nt_data['sub'] ?? '' );
$nt_poster = (string) ( $nt_data['poster'] ?? '' );
$nt_play   = (string) ( $nt_data['play_label'] ?? '' );
?>
<section class="nt-map" id="<?php echo esc_attr( sanitize_html_class( $nt_src ) ); ?>">
	<div class="container">

		<?php if ( $nt_tag || $nt_title || $nt_sub ) : ?>
			<div class="nt-section-center">
				<?php if ( $nt_tag ) : ?><div class="nt-section-tag"><?php echo esc_html( $nt_tag ); ?></div><?php endif; ?>
				<?php if ( $nt_title ) : ?><h2 class="section-title"><?php echo wp_kses( $nt_title, array( 'em' => array() ) ); ?></h2><?php endif; ?>
				<?php if ( $nt_sub ) : ?><p class="section-body"><?php echo esc_html( $nt_sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="nt-map__frame" data-nt-video-host>
			<?php if ( '' !== $nt_poster ) : ?>
				<img class="nt-map__poster"
				     src="<?php echo esc_url( nt_link( $nt_poster ) ); ?>"
				     alt="<?php echo esc_attr( $nt_data['poster_alt'] ?? '' ); ?>"
				     loading="lazy" decoding="async">
			<?php endif; ?>

			<?php if ( '' !== $nt_embed ) : ?>
				<?php // Real link first: with JS off this opens the map site itself. ?>
				<a class="nt-map__play"
				   data-nt-video="<?php echo esc_url( $nt_embed ); ?>"
				   href="<?php echo esc_url( '' !== $nt_link ? $nt_link : $nt_embed ); ?>"
				   target="_blank" rel="noopener noreferrer"
				   aria-label="<?php echo esc_attr( $nt_play ); ?>">
					<span class="nt-map__pin" aria-hidden="true"><?php NT_Icons::render( 'pin' ); ?></span>
					<?php if ( '' !== $nt_play ) : ?>
						<span class="nt-map__play-label"><?php echo esc_html( $nt_play ); ?></span>
					<?php endif; ?>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $nt_data['address'] ) || ! empty( $nt_data['note'] ) || '' !== $nt_link ) : ?>
			<div class="nt-map__foot">
				<div class="nt-map__where">
					<?php if ( ! empty( $nt_data['address'] ) ) : ?>
						<p class="nt-map__address">
							<?php NT_Icons::render( 'pin' ); ?>
							<?php echo esc_html( $nt_data['address'] ); ?>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $nt_data['note'] ) ) : ?>
						<p class="nt-map__note"><?php echo esc_html( $nt_data['note'] ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( '' !== $nt_link && ! empty( $nt_data['directions_label'] ) ) : ?>
					<a class="nt-map__link" href="<?php echo esc_url( $nt_link ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $nt_data['directions_label'] ); ?>
						<?php NT_Icons::render( 'external' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
