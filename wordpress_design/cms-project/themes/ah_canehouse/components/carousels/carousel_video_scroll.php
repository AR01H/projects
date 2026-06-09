<?php
/**
 * carousel_video_scroll — one-slide-at-a-time media slider with dots + arrows.
 *
 * Plays/shows any mix of:  YouTube  •  local/remote video file  •  image  •  image URL.
 * Data lives in a CSV (real_data/csv/video-showcase.csv by default) so you can just
 * add rows — no code changes needed.
 *
 * Styles  → assets/css/carousel-video.css   (enqueued as 'ch-carousel-video')
 * Behaviour → assets/js/carousel-video.js    (enqueued as 'ch-carousel-video')
 * Both are registered in functions.php and auto-init every .ch-vs on the page.
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  HOW TO USE                                                     │
 * └─────────────────────────────────────────────────────────────────┘
 *
 *  // Simplest — auto-loads real_data/csv/video-showcase.csv
 *  get_template_part( 'components/carousels/carousel_video_scroll' );
 *
 *  // With a heading + a different CSV
 *  get_template_part( 'components/carousels/carousel_video_scroll', null, [
 *    'tag'   => 'Watch',
 *    'title' => 'See It <span class="accent">Live</span>',
 *    'body'  => 'Real footage from our events.',
 *    'csv'   => 'video-showcase',     // file in real_data/csv/ (without .csv)
 *  ] );
 *
 *  // Or pass items inline (skips the CSV)
 *  get_template_part( 'components/carousels/carousel_video_scroll', null, [
 *    'items' => [
 *      [ 'type' => 'youtube', 'src' => 'https://youtu.be/XXXX', 'label' => 'Brand film' ],
 *      [ 'type' => 'video',   'src' => '…/clip.mp4', 'poster' => '…/thumb.jpg' ],
 *      [ 'type' => 'image',   'src' => '…/photo.jpg', 'desc' => 'Pressed fresh' ],
 *    ],
 *  ] );
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  CSV COLUMNS  (real_data/csv/video-showcase.csv)               │
 * └─────────────────────────────────────────────────────────────────┘
 *   type   image | video | youtube | gif   (leave blank to auto-detect from src)
 *   src    media URL or YouTube link/ID
 *   poster thumbnail image URL (optional — used for video & youtube)
 *   label  caption heading
 *   desc   caption sub-text
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  ALL OPTIONS                                                    │
 * └─────────────────────────────────────────────────────────────────┘
 *   'uid'         string  Unique id prefix.                 Default: auto
 *   'csv'         string  CSV file to auto-load.            Default: 'video-showcase'
 *   'items'       array   Inline items (overrides csv).     Default: from csv
 *   'tag'/'title'/'body'  Section header (optional).
 *   'bg'          string  Section background CSS.           Default: '' (none)
 *   'ratio'       string  Media aspect-ratio.               Default: '16 / 9'
 *   'autoplay'    int     Auto-advance ms (0 = off).        Default: 0
 *   'loop'        bool    Wrap past first/last slide.       Default: true
 *   'show_dots'   bool    Show dot indicators.              Default: true
 *   'show_arrows' bool    Show ← → arrow buttons.          Default: true
 */

defined( 'ABSPATH' ) || exit;

/* ── YouTube id parser (guarded — component may be included more than once) ──── */
if ( ! function_exists( 'ch_vs_youtube_id' ) ) {
	function ch_vs_youtube_id( string $url ): string {
		$url = trim( $url );
		if ( $url === '' ) return '';
		// Already a bare 11-char id?
		if ( preg_match( '~^[A-Za-z0-9_-]{11}$~', $url ) ) return $url;
		// youtu.be/ID , /watch?v=ID , /embed/ID , /shorts/ID , /v/ID
		if ( preg_match( '~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|v/))([A-Za-z0-9_-]{11})~', $url, $m ) ) {
			return $m[1];
		}
		return '';
	}
}

/* ── Args ──────────────────────────────────────────────────────────────────── */

$uid         = esc_attr( $args['uid'] ?? 'ch-vs-' . wp_rand( 100, 999 ) );
$csv_name    = $args['csv'] ?? 'video-showcase';
$tag         = $args['tag']   ?? '';
$title       = $args['title'] ?? '';
$body        = $args['body']  ?? '';
$bg          = $args['bg']    ?? '';
$ratio       = $args['ratio'] ?? '16 / 9';
$autoplay    = isset( $args['autoplay'] ) ? (int) $args['autoplay'] : 0;
$loop        = isset( $args['loop'] )        ? (bool) $args['loop']        : true;
$show_dots   = isset( $args['show_dots'] )   ? (bool) $args['show_dots']   : true;
$show_arrows = isset( $args['show_arrows'] ) ? (bool) $args['show_arrows'] : true;

/* Items: inline override, else load from CSV */
$items = is_array( $args['items'] ?? null ) ? $args['items'] : [];
if ( empty( $items ) && class_exists( 'CH_Real_Loader' ) ) {
	$items = CH_Real_Loader::csv( $csv_name );
}

/* Normalise + drop rows with no src */
$slides = [];
foreach ( $items as $it ) {
	$it   = (array) $it;
	$src  = trim( (string) ( $it['src'] ?? '' ) );
	if ( $src === '' ) continue;

	$type = strtolower( trim( (string) ( $it['type'] ?? '' ) ) );
	if ( $type === '' ) {
		// Auto-detect from the src
		if ( ch_vs_youtube_id( $src ) !== '' )                 $type = 'youtube';
		elseif ( preg_match( '~\.(mp4|webm|ogg|mov)(\?|$)~i', $src ) ) $type = 'video';
		else                                                   $type = 'image';
	}

	$slides[] = [
		'type'   => $type,
		'src'    => $src,
		'poster' => trim( (string) ( $it['poster'] ?? '' ) ),
		'label'  => (string) ( $it['label'] ?? '' ),
		'desc'   => (string) ( $it['desc'] ?? '' ),
	];
}

if ( empty( $slides ) ) return;
$total = count( $slides );
?>

<section class="ch-vs-section"<?php echo $bg ? ' style="background:' . esc_attr( $bg ) . ';"' : ''; ?> id="<?php echo $uid; ?>-section">
	<?php if ( $tag || $title || $body ) : ?>
		<div class="container">
			<?php get_template_part( 'components/section-header', null, [
				'tag'   => $tag,
				'title' => $title,
				'body'  => $body,
			] ); ?>
		</div>
	<?php endif; ?>

	<div class="ch-vs" id="<?php echo $uid; ?>"
	     data-autoplay="<?php echo (int) $autoplay; ?>"
	     data-loop="<?php echo $loop ? '1' : '0'; ?>">

		<!-- Viewport -->
		<div class="ch-vs-viewport">
			<div class="ch-vs-track">

				<?php foreach ( $slides as $i => $s ) :
					$type   = $s['type'];
					$src    = $s['src'];
					$poster = $s['poster'];
					$label  = $s['label'];
					$desc   = $s['desc'];
					$yt_id  = ( $type === 'youtube' ) ? ch_vs_youtube_id( $src ) : '';
					$thumb  = $poster !== '' ? $poster
					        : ( $yt_id !== '' ? 'https://img.youtube.com/vi/' . $yt_id . '/hqdefault.jpg' : '' );
				?>
				<div class="ch-vs-slide<?php echo $i === 0 ? ' is-active' : ''; ?>"
				     data-index="<?php echo (int) $i; ?>"
				     role="group"
				     aria-roledescription="slide"
				     aria-label="<?php echo esc_attr( ( $i + 1 ) . ' of ' . $total ); ?>">

					<div class="ch-vs-media" style="aspect-ratio:<?php echo esc_attr( $ratio ); ?>;">
						<?php if ( $type === 'youtube' || $type === 'video' ) : ?>
							<!-- Play facade: loads the player only on click (poster + themed play button) -->
							<div class="ch-vs-yt" role="button" tabindex="0"
							     data-type="<?php echo esc_attr( $type ); ?>"
							     data-yt="<?php echo esc_attr( $yt_id ); ?>"
							     data-src="<?php echo esc_url( $src ); ?>"
							     aria-label="<?php echo esc_attr( 'Play video' . ( $label ? ': ' . $label : '' ) ); ?>">
								<?php if ( $thumb ) : ?>
									<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $label ); ?>" loading="lazy">
								<?php endif; ?>
								<span class="ch-vs-play" aria-hidden="true">
									<svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor" focusable="false"><path d="M8 5v14l11-7z"/></svg>
								</span>
							</div>

						<?php else : /* image / gif / fallback */ ?>
							<img class="ch-vs-img" src="<?php echo esc_url( $src ); ?>"
							     alt="<?php echo esc_attr( $label ); ?>" loading="lazy">
						<?php endif; ?>

						<?php if ( $label || $desc ) : ?>
							<!-- Title/desc designed on top of the media (hidden once a YouTube video plays) -->
							<div class="ch-vs-overlay">
								<?php if ( $label ) : ?><strong class="ch-vs-otitle"><?php echo esc_html( $label ); ?></strong><?php endif; ?>
								<?php if ( $desc )  : ?><span class="ch-vs-odesc"><?php echo esc_html( $desc ); ?></span><?php endif; ?>
							</div>
						<?php endif; ?>
					</div>

				</div><!-- .ch-vs-slide -->
				<?php endforeach; ?>

			</div><!-- .ch-vs-track -->

			<?php if ( $show_arrows && $total > 1 ) : ?>
				<button class="ch-vs-arrow ch-vs-arrow--prev" aria-label="Previous slide">←</button>
				<button class="ch-vs-arrow ch-vs-arrow--next" aria-label="Next slide">→</button>
			<?php endif; ?>
		</div><!-- .ch-vs-viewport -->

		<?php if ( $show_dots && $total > 1 ) : ?>
			<div class="ch-vs-dots" role="tablist" aria-label="Slide navigation">
				<?php foreach ( $slides as $i => $s ) : ?>
					<button class="ch-carousel__dot ch-vs-dot<?php echo $i === 0 ? ' is-active' : ''; ?>"
					        role="tab"
					        aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
					        data-go="<?php echo (int) $i; ?>"
					        aria-label="<?php echo esc_attr( 'Go to slide ' . ( $i + 1 ) ); ?>"></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div><!-- .ch-vs -->
</section>
