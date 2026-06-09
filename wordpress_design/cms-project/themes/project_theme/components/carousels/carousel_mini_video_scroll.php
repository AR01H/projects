<?php
/**
 * carousel_mini_video_scroll - horizontal shelf of portrait "Shorts-style" video cards.
 *
 * Data from real_data/csv/mini-video-showcase.csv (or pass items inline).
 *
 * Styles    → assets/css/carousel-mini-video.css  (enqueued 'pt-carousel-mini-video')
 * Behaviour → assets/js/carousel-mini-video.js     (enqueued 'pt-carousel-mini-video')
 *
 * get_template_part( 'components/carousels/carousel_mini_video_scroll', null, [
 *   'tag'   => 'Client Stories',
 *   'title' => 'Hear From Our Clients',
 *   'body'  => 'Real people sharing their experience.',
 * ] );
 *
 * CSV columns: type, src, poster, avatar, channel, handle, title, desc
 * OPTIONS: uid, csv, items, tag, title, body, bg
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'pt_vs_youtube_id' ) ) {
	function pt_vs_youtube_id( string $url ): string {
		$url = trim( $url );
		if ( $url === '' ) return '';
		if ( preg_match( '~^[A-Za-z0-9_-]{11}$~', $url ) ) return $url;
		if ( preg_match( '~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|v/))([A-Za-z0-9_-]{11})~', $url, $m ) ) {
			return $m[1];
		}
		return '';
	}
}

$uid      = esc_attr( $args['uid'] ?? 'pt-mvs-' . wp_rand( 100, 999 ) );
$csv_name = $args['csv'] ?? 'mini-video-showcase';
$tag      = $args['tag']   ?? '';
$title    = $args['title'] ?? '';
$body     = $args['body']  ?? '';
$bg       = $args['bg']    ?? '';

$items = is_array( $args['items'] ?? null ) ? $args['items'] : [];
if ( empty( $items ) && class_exists( 'PT_Real_Loader' ) ) {
	$items = PT_Real_Loader::csv( $csv_name );
}

$cards = [];
foreach ( $items as $it ) {
	$it  = (array) $it;
	$src = trim( (string) ( $it['src'] ?? '' ) );
	if ( $src === '' ) continue;

	$type = strtolower( trim( (string) ( $it['type'] ?? '' ) ) );
	if ( $type === '' ) {
		if ( pt_vs_youtube_id( $src ) !== '' )                          $type = 'youtube';
		elseif ( preg_match( '~\.(mp4|webm|ogg|mov)(\?|$)~i', $src ) ) $type = 'video';
		else                                                            $type = 'image';
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

$allowed_kses = [
	'span'   => [ 'class' => [], 'style' => [] ],
	'em'     => [],
	'strong' => [],
];
?>

<section class="pt-mvs-section"<?php echo $bg ? ' style="background:' . esc_attr( $bg ) . ';"' : ''; ?> id="<?php echo $uid; ?>-section">
	<?php if ( $tag || $title || $body ) : ?>
		<div class="pt-container">
			<div class="pt-mvs-header">
				<?php if ( $tag )   : ?><div class="pt-section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
				<?php if ( $title ) : ?><h2 class="pt-section-title"><?php echo wp_kses( $title, $allowed_kses ); ?></h2><?php endif; ?>
				<?php if ( $body )  : ?><p class="pt-section-body"><?php echo esc_html( $body ); ?></p><?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="pt-mvs" id="<?php echo $uid; ?>">

		<div class="pt-mvs-track" tabindex="0">
			<?php foreach ( $cards as $i => $c ) :
				$type   = $c['type'];
				$src    = $c['src'];
				$yt_id  = ( $type === 'youtube' ) ? pt_vs_youtube_id( $src ) : '';
				$thumb  = $c['poster'] !== '' ? $c['poster']
				        : ( $yt_id !== '' ? 'https://img.youtube.com/vi/' . $yt_id . '/hqdefault.jpg' : '' );
			?>
			<div class="pt-mvs-card">

				<?php if ( $type === 'image' ) : ?>
					<a class="pt-mvs-thumb" href="<?php echo esc_url( $src ); ?>" target="_blank" rel="noopener">
						<img src="<?php echo esc_url( $thumb ?: $src ); ?>" alt="<?php echo esc_attr( $c['title'] ); ?>" loading="lazy">
						<?php require __DIR__ . '/partials/mvs-channel.php'; ?>
					</a>
				<?php else : ?>
					<div class="pt-mvs-thumb pt-mvs-play-btn"
					     role="button" tabindex="0"
					     data-type="<?php echo esc_attr( $type ); ?>"
					     data-yt="<?php echo esc_attr( $yt_id ); ?>"
					     data-src="<?php echo esc_url( $src ); ?>"
					     aria-label="<?php echo esc_attr( 'Play: ' . ( $c['title'] ?: $c['channel'] ) ); ?>">
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $c['title'] ); ?>" loading="lazy">
						<?php endif; ?>
						<?php require __DIR__ . '/partials/mvs-channel.php'; ?>
						<span class="pt-mvs-play" aria-hidden="true">
							<svg viewBox="0 0 24 24" width="26" height="26" fill="#fff" focusable="false"><path d="M8 5v14l11-7z"/></svg>
						</span>
					</div>
				<?php endif; ?>

				<div class="pt-mvs-meta">
					<?php if ( $c['title'] ) : ?>
						<a class="pt-mvs-title" href="<?php echo esc_url( $src ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $c['title'] ); ?></a>
					<?php endif; ?>
					<?php if ( $c['desc'] ) : ?>
						<p class="pt-mvs-desc"><?php echo esc_html( $c['desc'] ); ?></p>
					<?php endif; ?>
				</div>

			</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $total > 1 ) : ?>
			<div class="pt-mvs-nav">
				<button class="pt-mvs-arrow pt-mvs-arrow--prev" aria-label="Scroll left">←</button>
				<button class="pt-mvs-arrow pt-mvs-arrow--next" aria-label="Scroll right">→</button>
			</div>
		<?php endif; ?>

	</div>
</section>
