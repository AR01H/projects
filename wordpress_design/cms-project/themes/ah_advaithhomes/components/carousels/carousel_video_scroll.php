<?php
/**
 * carousel_video_scroll — one-slide-at-a-time media slider with dots + arrows.
 *
 * Plays/shows any mix of:  YouTube  •  local/remote video file  •  image.
 * Data lives in a CSV (real_data/csv/video-showcase.csv by default).
 *
 * Styles    → assets/css/carousel-video.css   (enqueued as 'ah-carousel-video')
 * Behaviour → assets/js/carousel-video.js     (enqueued as 'ah-carousel-video')
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  HOW TO USE                                                     │
 * └─────────────────────────────────────────────────────────────────┘
 *
 *  // Simplest — auto-loads real_data/csv/video-showcase.csv
 *  get_template_part( 'components/carousels/carousel_video_scroll' );
 *
 *  // With a heading + optional different CSV
 *  get_template_part( 'components/carousels/carousel_video_scroll', null, [
 *    'tag'   => 'Watch',
 *    'title' => 'See It <span class="ah-accent">Live</span>',
 *    'body'  => 'Real footage from our properties.',
 *    'csv'   => 'video-showcase',
 *  ] );
 *
 *  // Or pass items inline (skips the CSV)
 *  get_template_part( 'components/carousels/carousel_video_scroll', null, [
 *    'items' => [
 *      [ 'type' => 'youtube', 'src' => 'https://youtu.be/XXXX', 'label' => 'Brand film' ],
 *      [ 'type' => 'video',   'src' => '…/clip.mp4', 'poster' => '…/thumb.jpg' ],
 *      [ 'type' => 'image',   'src' => '…/photo.jpg', 'desc' => 'Exterior view' ],
 *    ],
 *  ] );
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  CSV COLUMNS  (real_data/csv/video-showcase.csv)               │
 * └─────────────────────────────────────────────────────────────────┘
 *   type   image | video | youtube | gif   (leave blank to auto-detect from src)
 *   src    media URL or YouTube link/ID
 *   poster thumbnail image URL (optional)
 *   label  caption heading
 *   desc   caption sub-text
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  ALL OPTIONS                                                    │
 * └─────────────────────────────────────────────────────────────────┘
 *   'uid'         string  Unique id prefix.                 Default: auto
 *   'csv'         string  CSV file to auto-load.            Default: 'video-showcase'
 *   'items'       array   Inline items (overrides csv).
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
if ( ! function_exists( 'ah_vs_youtube_id' ) ) {
	function ah_vs_youtube_id( string $url ): string {
		$url = trim( $url );
		if ( $url === '' ) return '';
		if ( preg_match( '~^[A-Za-z0-9_-]{11}$~', $url ) ) return $url;
		if ( preg_match( '~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|v/))([A-Za-z0-9_-]{11})~', $url, $m ) ) {
			return $m[1];
		}
		return '';
	}
}

/* ── Args ──────────────────────────────────────────────────────────────────── */

$uid         = esc_attr( $args['uid'] ?? 'ah-vs-' . wp_rand( 100, 999 ) );
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
if ( empty( $items ) && class_exists( 'AH_Real_Loader' ) ) {
	$items = AH_Real_Loader::csv( $csv_name );
}

/* Normalise + drop rows with no src */
$slides = [];
foreach ( $items as $it ) {
	$it  = (array) $it;
	$src = trim( (string) ( $it['src'] ?? '' ) );
	if ( $src === '' ) continue;

	$type = strtolower( trim( (string) ( $it['type'] ?? '' ) ) );
	if ( $type === '' ) {
		if ( ah_vs_youtube_id( $src ) !== '' )                              $type = 'youtube';
		elseif ( preg_match( '~\.(mp4|webm|ogg|mov)(\?|$)~i', $src ) )    $type = 'video';
		else                                                                $type = 'image';
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

$allowed_kses = [
	'span'   => [ 'class' => [], 'style' => [] ],
	'em'     => [],
	'strong' => [],
];
?>

<section class="ah-vs-section"<?php echo $bg ? ' style="background:' . esc_attr( $bg ) . ';"' : ''; ?> id="<?php echo $uid; ?>-section">
	<?php if ( $tag || $title || $body ) : ?>
		<div class="container">
			<div class="ah-vs-header">
				<?php if ( $tag )   : ?><div class="section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
				<?php if ( $title ) : ?><h2 class="section-title"><?php echo wp_kses( $title, $allowed_kses ); ?></h2><?php endif; ?>
				<?php if ( $body )  : ?><p class="section-body"><?php echo esc_html( $body ); ?></p><?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="ah-vs" id="<?php echo $uid; ?>"
	     data-autoplay="<?php echo (int) $autoplay; ?>"
	     data-loop="<?php echo $loop ? '1' : '0'; ?>">

		<!-- Viewport -->
		<div class="ah-vs-viewport">
			<div class="ah-vs-track">

				<?php foreach ( $slides as $i => $s ) :
					$type   = $s['type'];
					$src    = $s['src'];
					$poster = $s['poster'];
					$label  = $s['label'];
					$desc   = $s['desc'];
					$yt_id  = ( $type === 'youtube' ) ? ah_vs_youtube_id( $src ) : '';
					$thumb  = $poster !== '' ? $poster
					        : ( $yt_id !== '' ? 'https://img.youtube.com/vi/' . $yt_id . '/hqdefault.jpg' : '' );
				?>
				<div class="ah-vs-slide<?php echo $i === 0 ? ' is-active' : ''; ?>"
				     data-index="<?php echo (int) $i; ?>"
				     role="group"
				     aria-roledescription="slide"
				     aria-label="<?php echo esc_attr( ( $i + 1 ) . ' of ' . $total ); ?>">

					<div class="ah-vs-media" style="aspect-ratio:<?php echo esc_attr( $ratio ); ?>;">
						<?php if ( $type === 'youtube' || $type === 'video' ) : ?>
							<!-- Play facade: loads the player only on click (poster + themed play button) -->
							<div class="ah-vs-yt" role="button" tabindex="0"
							     data-type="<?php echo esc_attr( $type ); ?>"
							     data-yt="<?php echo esc_attr( $yt_id ); ?>"
							     data-src="<?php echo esc_url( $src ); ?>"
							     aria-label="<?php echo esc_attr( 'Play video' . ( $label ? ': ' . $label : '' ) ); ?>">
								<?php if ( $thumb ) : ?>
									<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $label ); ?>" loading="lazy">
								<?php endif; ?>
								<span class="ah-vs-play" aria-hidden="true">
									<svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor" focusable="false"><path d="M8 5v14l11-7z"/></svg>
								</span>
							</div>

						<?php else : /* image / gif / fallback */ ?>
							<img class="ah-vs-img" src="<?php echo esc_url( $src ); ?>"
							     alt="<?php echo esc_attr( $label ); ?>" loading="lazy">
						<?php endif; ?>

						<?php if ( $label || $desc ) : ?>
							<div class="ah-vs-overlay">
								<?php if ( $label ) : ?><strong class="ah-vs-otitle"><?php echo esc_html( $label ); ?></strong><?php endif; ?>
								<?php if ( $desc )  : ?><span class="ah-vs-odesc"><?php echo esc_html( $desc ); ?></span><?php endif; ?>
							</div>
						<?php endif; ?>
					</div>

				</div><!-- .ah-vs-slide -->
				<?php endforeach; ?>

			</div><!-- .ah-vs-track -->

			<?php if ( $show_arrows && $total > 1 ) : ?>
				<button class="ah-vs-arrow ah-vs-arrow--prev" aria-label="Previous slide">←</button>
				<button class="ah-vs-arrow ah-vs-arrow--next" aria-label="Next slide">→</button>
			<?php endif; ?>
		</div><!-- .ah-vs-viewport -->

		<?php if ( $show_dots && $total > 1 ) : ?>
			<div class="ah-vs-dots" role="tablist" aria-label="Slide navigation">
				<?php foreach ( $slides as $i => $s ) : ?>
					<button class="ah-vs-dot<?php echo $i === 0 ? ' is-active' : ''; ?>"
					        role="tab"
					        aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
					        data-go="<?php echo (int) $i; ?>"
					        aria-label="<?php echo esc_attr( 'Go to slide ' . ( $i + 1 ) ); ?>"></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div><!-- .ah-vs -->
</section>
