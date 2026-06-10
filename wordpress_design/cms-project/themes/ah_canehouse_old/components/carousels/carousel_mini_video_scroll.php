<?php
/**
 * carousel_mini_video_scroll - horizontal shelf of portrait "Shorts-style" video cards.
 *
 * Each card: thumbnail + channel avatar/name overlay + red play badge,
 * with a title (link) and description below. Scrolls with ← → buttons under the row.
 * Plays YouTube / local video / shows image. Data comes from a CSV.
 *
 * Styles    → assets/css/carousel-mini-video.css  (enqueued 'ch-carousel-mini-video')
 * Behaviour → assets/js/carousel-mini-video.js     (enqueued 'ch-carousel-mini-video')
 * Both auto-init every .ch-mvs on the page.
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  HOW TO USE                                                     │
 * └─────────────────────────────────────────────────────────────────┘
 *
 *  // Auto-loads real_data/csv/mini-video-showcase.csv
 *  get_template_part( 'components/carousels/carousel_mini_video_scroll', null, [
 *    'tag'   => 'Happy Customers',
 *    'title' => 'Hear From <span class="accent">Our Guests</span>',
 *    'body'  => 'Real stories from real events.',
 *  ] );
 *
 *  // Or pass items inline
 *  get_template_part( 'components/carousels/carousel_mini_video_scroll', null, [
 *    'items' => [
 *      [ 'type'=>'youtube','src'=>'https://youtu.be/XXXX','avatar'=>'…','channel'=>'Priya',
 *        'handle'=>'The Cane House','title'=>'Loved it!','desc'=>'Best drink ever.' ],
 *    ],
 *  ] );
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  CSV COLUMNS  (real_data/csv/mini-video-showcase.csv)          │
 * └─────────────────────────────────────────────────────────────────┘
 *   type     image | video | youtube   (blank = auto-detect from src)
 *   src      media URL or YouTube link/ID  (also the title link target)
 *   poster   portrait thumbnail image URL  (auto from YouTube id if blank)
 *   avatar   small round channel avatar URL
 *   channel  bold name shown on the thumbnail
 *   handle   sub-line under the channel name
 *   title    heading shown below the card (links to src)
 *   desc     short description below the title
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  ALL OPTIONS                                                    │
 * └─────────────────────────────────────────────────────────────────┘
 *   'uid'      string  Unique id prefix.            Default: auto
 *   'csv'      string  CSV file to auto-load.       Default: 'mini-video-showcase'
 *   'items'    array   Inline items (overrides csv).
 *   'tag'/'title'/'body'  Section header (optional).
 *   'bg'       string  Section background CSS.      Default: '' (none)
 */

defined( 'ABSPATH' ) || exit;

/* ── YouTube id parser (shared; guard avoids redeclare with the other carousel) ─ */
if ( ! function_exists( 'ch_vs_youtube_id' ) ) {
	function ch_vs_youtube_id( string $url ): string {
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

$uid      = esc_attr( $args['uid'] ?? 'ch-mvs-' . wp_rand( 100, 999 ) );
$csv_name = $args['csv'] ?? 'mini-video-showcase';
$tag      = $args['tag']   ?? '';
$title    = $args['title'] ?? '';
$body     = $args['body']  ?? '';
$bg       = $args['bg']    ?? '';

$items = is_array( $args['items'] ?? null ) ? $args['items'] : [];
if ( empty( $items ) && class_exists( 'CH_Real_Loader' ) ) {
	$items = CH_Real_Loader::csv( $csv_name );
}

/* Normalise + drop rows with no src */
$cards = [];
foreach ( $items as $it ) {
	$it  = (array) $it;
	$src = trim( (string) ( $it['src'] ?? '' ) );
	if ( $src === '' ) continue;

	$type = strtolower( trim( (string) ( $it['type'] ?? '' ) ) );
	if ( $type === '' ) {
		if ( ch_vs_youtube_id( $src ) !== '' )                        $type = 'youtube';
		elseif ( preg_match( '~\.(mp4|webm|ogg|mov)(\?|$)~i', $src ) ) $type = 'video';
		else                                                          $type = 'image';
	}

	$cards[] = [
		'type'    => $type,
		'src'     => $src,
		'poster'  => trim( (string) ( $it['poster']  ?? '' ) ),
		'avatar'  => trim( (string) ( $it['avatar']  ?? '' ) ),
		'channel' => (string) ( $it['channel'] ?? '' ),
		'handle'  => (string) ( $it['handle']  ?? '' ),
		'title'   => (string) ( $it['title']   ?? '' ),
		'desc'    => (string) ( $it['desc']    ?? '' ),
	];
}

if ( empty( $cards ) ) return;
$total = count( $cards );
?>

<section class="ch-mvs-section"<?php echo $bg ? ' style="background:' . esc_attr( $bg ) . ';"' : ''; ?> id="<?php echo $uid; ?>-section">
	<?php if ( $tag || $title || $body ) : ?>
		<div class="container">
			<?php get_template_part( 'components/section-header', null, [
				'tag'   => $tag,
				'title' => $title,
				'body'  => $body,
			] ); ?>
		</div>
	<?php endif; ?>

	<div class="ch-mvs" id="<?php echo $uid; ?>">

		<!-- Scrolling row -->
		<div class="ch-mvs-track" tabindex="0">
			<?php foreach ( $cards as $i => $c ) :
				$type   = $c['type'];
				$src    = $c['src'];
				$yt_id  = ( $type === 'youtube' ) ? ch_vs_youtube_id( $src ) : '';
				$thumb  = $c['poster'] !== '' ? $c['poster']
				        : ( $yt_id !== '' ? 'https://img.youtube.com/vi/' . $yt_id . '/hqdefault.jpg' : '' );
			?>
			<div class="ch-mvs-card">

				<?php if ( $type === 'image' ) : ?>
					<!-- Image card (no play) -->
					<a class="ch-mvs-thumb" href="<?php echo esc_url( $src ); ?>" target="_blank" rel="noopener">
						<img src="<?php echo esc_url( $thumb ?: $src ); ?>" alt="<?php echo esc_attr( $c['title'] ); ?>" loading="lazy">
						<?php require __DIR__ . '/partials/mvs-channel.php'; ?>
					</a>

				<?php else : /* youtube | video → play facade */ ?>
					<div class="ch-mvs-thumb ch-mvs-play-btn"
					     role="button" tabindex="0"
					     data-type="<?php echo esc_attr( $type ); ?>"
					     data-yt="<?php echo esc_attr( $yt_id ); ?>"
					     data-src="<?php echo esc_url( $src ); ?>"
					     aria-label="<?php echo esc_attr( 'Play: ' . ( $c['title'] ?: $c['channel'] ) ); ?>">
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $c['title'] ); ?>" loading="lazy">
						<?php endif; ?>
						<?php require __DIR__ . '/partials/mvs-channel.php'; ?>
						<span class="ch-mvs-play" aria-hidden="true">
							<svg viewBox="0 0 24 24" width="26" height="26" fill="#fff" focusable="false"><path d="M8 5v14l11-7z"/></svg>
						</span>
					</div>
				<?php endif; ?>

				<!-- Title + description below the card -->
				<div class="ch-mvs-meta">
					<?php if ( $c['title'] ) : ?>
						<a class="ch-mvs-title" href="<?php echo esc_url( $src ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $c['title'] ); ?></a>
					<?php endif; ?>
					<?php if ( $c['desc'] ) : ?>
						<p class="ch-mvs-desc"><?php echo esc_html( $c['desc'] ); ?></p>
					<?php endif; ?>
				</div>

			</div><!-- .ch-mvs-card -->
			<?php endforeach; ?>
		</div><!-- .ch-mvs-track -->

		<!-- Nav buttons below the row -->
		<?php if ( $total > 1 ) : ?>
			<div class="ch-mvs-nav">
				<button class="ch-mvs-arrow ch-mvs-arrow--prev" aria-label="Scroll left">←</button>
				<button class="ch-mvs-arrow ch-mvs-arrow--next" aria-label="Scroll right">→</button>
			</div>
		<?php endif; ?>

	</div><!-- .ch-mvs -->
</section>
