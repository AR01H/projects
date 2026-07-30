<?php
/**
 * components/sections/showcase_gallery.php - Masonry photo + video grid for
 * pages/PageReviews.php, sourced from the CMS Plugin's "Showcase Gallery"
 * admin feature (admin.php?page=ah-client-stories).
 *
 * Props:
 *   $heading     string        Section title, centered (Showcase Gallery's
 *                                "Heading" field)
 *   $description string        Centered subtitle below the heading
 *                                (Showcase Gallery's "Information" field)
 *   $images      array<object>  Rows from wp_ah_client_gallery (id, image_id,
 *                                width_class, sort_order, status)
 *   $videos      array<object>  Rows from wp_ah_client_video_links (id,
 *                                heading, video_url, thumbnail_id, sort_order,
 *                                status)
 *
 * Every image links to the shared review lightbox (same
 * .ah-rv-lightbox-trigger mechanism the review cards use) so clicking one
 * opens it full-size with prev/next through the rest of this gallery. Video
 * tiles open the video's own URL in a new tab instead - the lightbox only
 * knows how to display images. Each video tile's thumbnail is (in order):
 * the admin's uploaded "Thumbnail" image, an auto-detected YouTube preview
 * frame, or - if neither is available - a plain play-icon tile.
 *
 * Usage:
 *   adn_component( 'sections/showcase_gallery', array(
 *       'heading'     => $showcase_heading,
 *       'description' => $showcase_description,
 *       'images'      => $showcase_images,
 *       'videos'      => $showcase_videos,
 *   ) );
 */

defined( 'ABSPATH' ) || exit;

$heading     = isset( $heading ) ? (string) $heading : '';
$description = isset( $description ) ? (string) $description : '';
$images      = isset( $images ) && is_array( $images ) ? $images : array();
$videos      = isset( $videos ) && is_array( $videos ) ? $videos : array();

if ( empty( $images ) && empty( $videos ) ) { return; }

if ( '' !== $heading ) {
	adn_component( 'parts/section_headers/section_header', array(
		'heading' => array( 'title' => $heading, 'link_label' => '', 'link_url' => '' ),
		'tag'     => 'h2',
		'center'  => true,
	) );
}
if ( '' !== $description ) : ?>
	<p class="showcase-gallery__description"><?php echo esc_html( $description ); ?></p>
<?php endif;

// ah_review_render_lightbox_once() normally gets defined by AH_Reviews_Model
// when it renders a With Photos/Full Story card first - this section can
// render even when zero review cards did (e.g. no active reviews but a
// showcase gallery exists), so it can't rely on that happening first. Same
// theme-override-with-plugin-fallback resolution AH_Reviews_Model uses.
if ( ! function_exists( 'ah_review_render_lightbox_once' ) ) {
	$_lightbox_path = function_exists( 'locate_template' ) ? locate_template( 'components/reviews/render-lightbox.php' ) : '';
	if ( ! $_lightbox_path && defined( 'AH_PLUGIN_DIR' ) ) {
		$_lightbox_path = AH_PLUGIN_DIR . '/models/reviews/render-lightbox.php';
	}
	if ( $_lightbox_path && file_exists( $_lightbox_path ) ) {
		require_once $_lightbox_path;
	}
}

/** Best-effort YouTube thumbnail when no thumbnail was uploaded for a video. */
function ah_showcase_youtube_thumb( string $video_url ): string {
	if ( preg_match( '#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})#', $video_url, $m ) ) {
		return 'https://img.youtube.com/vi/' . $m[1] . '/hqdefault.jpg';
	}
	return '';
}
?>
<div class="showcase-gallery">
	<?php foreach ( $images as $gi ) :
		$url_full = wp_get_attachment_image_url( (int) $gi->image_id, 'large' );
		$url_med  = wp_get_attachment_image_url( (int) $gi->image_id, 'medium_large' );
		if ( ! $url_med ) continue;
	?>
		<div class="showcase-gallery__tile showcase-gallery__tile--<?php echo esc_attr( $gi->width_class ?: 'medium' ); ?>">
			<img class="ah-rv-lightbox-trigger" src="<?php echo esc_url( $url_med ); ?>" data-full="<?php echo esc_url( $url_full ?: $url_med ); ?>" alt="" loading="lazy">
		</div>
	<?php endforeach; ?>

	<?php foreach ( $videos as $vi ) :
		$video_url = (string) $vi->video_url;
		if ( ! $video_url ) continue;
		$thumb_url = ! empty( $vi->thumbnail_id ) ? wp_get_attachment_image_url( (int) $vi->thumbnail_id, 'medium_large' ) : '';
		if ( ! $thumb_url ) { $thumb_url = ah_showcase_youtube_thumb( $video_url ); }
	?>
		<div class="showcase-gallery__tile showcase-gallery__tile--video">
			<a href="<?php echo esc_url( $video_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $vi->heading ?: __( 'Watch video', 'ah-cms' ) ); ?>">
				<?php if ( $thumb_url ) : ?>
					<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" loading="lazy">
				<?php else : ?>
					<span class="showcase-gallery__video-fallback" aria-hidden="true"></span>
				<?php endif; ?>
				<span class="showcase-gallery__play" aria-hidden="true"></span>
				<?php if ( ! empty( $vi->heading ) ) : ?><span class="showcase-gallery__video-title"><?php echo esc_html( $vi->heading ); ?></span><?php endif; ?>
			</a>
		</div>
	<?php endforeach; ?>
</div>
<?php if ( function_exists( 'ah_review_render_lightbox_once' ) ) { echo ah_review_render_lightbox_once(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static HTML/JS, no user data.
} ?>
