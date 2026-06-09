<?php
/**
 * carousel_video_scroll — one-slide-at-a-time media slider with dots + arrows.
 *
 * Supports: YouTube • local/remote video • image.
 * Data from real_data/csv/video-showcase.csv (or pass items inline).
 *
 * Styles    → assets/css/carousel-video.css   (enqueued 'pt-carousel-video')
 * Behaviour → assets/js/carousel-video.js     (enqueued 'pt-carousel-video')
 *
 * get_template_part( 'components/carousels/carousel_video_scroll', null, [
 *   'tag'   => 'Watch',
 *   'title' => 'See Our Work',
 *   'body'  => 'Short films from recent projects.',
 *   'csv'   => 'video-showcase',   // real_data/csv/{name}.csv
 * ] );
 *
 * CSV columns: type, src, poster, label, desc
 * OPTIONS: uid, csv, items, tag, title, body, bg, ratio, autoplay, loop, show_dots, show_arrows
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

$uid         = esc_attr( $args['uid'] ?? 'pt-vs-' . wp_rand( 100, 999 ) );
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

$items = is_array( $args['items'] ?? null ) ? $args['items'] : [];
if ( empty( $items ) && class_exists( 'PT_Real_Loader' ) ) {
	$items = PT_Real_Loader::csv( $csv_name );
}

$slides = [];
foreach ( $items as $it ) {
	$it  = (array) $it;
	$src = trim( (string) ( $it['src'] ?? '' ) );
	if ( $src === '' ) continue;

	$type = strtolower( trim( (string) ( $it['type'] ?? '' ) ) );
	if ( $type === '' ) {
		if ( pt_vs_youtube_id( $src ) !== '' )                             $type = 'youtube';
		elseif ( preg_match( '~\.(mp4|webm|ogg|mov)(\?|$)~i', $src ) )   $type = 'video';
		else                                                               $type = 'image';
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

<section class="pt-vs-section"<?php echo $bg ? ' style="background:' . esc_attr( $bg ) . ';"' : ''; ?> id="<?php echo $uid; ?>-section">
	<?php if ( $tag || $title || $body ) : ?>
		<div class="pt-container">
			<div class="pt-vs-header">
				<?php if ( $tag )   : ?><div class="pt-section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
				<?php if ( $title ) : ?><h2 class="pt-section-title"><?php echo wp_kses( $title, $allowed_kses ); ?></h2><?php endif; ?>
				<?php if ( $body )  : ?><p class="pt-section-body"><?php echo esc_html( $body ); ?></p><?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="pt-vs" id="<?php echo $uid; ?>"
	     data-autoplay="<?php echo (int) $autoplay; ?>"
	     data-loop="<?php echo $loop ? '1' : '0'; ?>">

		<div class="pt-vs-viewport">
			<div class="pt-vs-track">

				<?php foreach ( $slides as $i => $s ) :
					$type   = $s['type'];
					$src    = $s['src'];
					$poster = $s['poster'];
					$label  = $s['label'];
					$desc   = $s['desc'];
					$yt_id  = ( $type === 'youtube' ) ? pt_vs_youtube_id( $src ) : '';
					$thumb  = $poster !== '' ? $poster
					        : ( $yt_id !== '' ? 'https://img.youtube.com/vi/' . $yt_id . '/hqdefault.jpg' : '' );
				?>
				<div class="pt-vs-slide<?php echo $i === 0 ? ' is-active' : ''; ?>"
				     data-index="<?php echo (int) $i; ?>"
				     role="group" aria-roledescription="slide"
				     aria-label="<?php echo esc_attr( ( $i + 1 ) . ' of ' . $total ); ?>">

					<div class="pt-vs-media" style="aspect-ratio:<?php echo esc_attr( $ratio ); ?>;">
						<?php if ( $type === 'youtube' || $type === 'video' ) : ?>
							<div class="pt-vs-yt" role="button" tabindex="0"
							     data-type="<?php echo esc_attr( $type ); ?>"
							     data-yt="<?php echo esc_attr( $yt_id ); ?>"
							     data-src="<?php echo esc_url( $src ); ?>"
							     aria-label="<?php echo esc_attr( 'Play video' . ( $label ? ': ' . $label : '' ) ); ?>">
								<?php if ( $thumb ) : ?>
									<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $label ); ?>" loading="lazy">
								<?php endif; ?>
								<span class="pt-vs-play" aria-hidden="true">
									<svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor" focusable="false"><path d="M8 5v14l11-7z"/></svg>
								</span>
							</div>
						<?php else : ?>
							<img class="pt-vs-img" src="<?php echo esc_url( $src ); ?>"
							     alt="<?php echo esc_attr( $label ); ?>" loading="lazy">
						<?php endif; ?>

						<?php if ( $label || $desc ) : ?>
							<div class="pt-vs-overlay">
								<?php if ( $label ) : ?><strong class="pt-vs-otitle"><?php echo esc_html( $label ); ?></strong><?php endif; ?>
								<?php if ( $desc )  : ?><span class="pt-vs-odesc"><?php echo esc_html( $desc ); ?></span><?php endif; ?>
							</div>
						<?php endif; ?>
					</div>

				</div>
				<?php endforeach; ?>

			</div>

			<?php if ( $show_arrows && $total > 1 ) : ?>
				<button class="pt-vs-arrow pt-vs-arrow--prev" aria-label="Previous slide">←</button>
				<button class="pt-vs-arrow pt-vs-arrow--next" aria-label="Next slide">→</button>
			<?php endif; ?>
		</div>

		<?php if ( $show_dots && $total > 1 ) : ?>
			<div class="pt-vs-dots" role="tablist" aria-label="Slide navigation">
				<?php foreach ( $slides as $i => $s ) : ?>
					<button class="pt-vs-dot<?php echo $i === 0 ? ' is-active' : ''; ?>"
					        role="tab"
					        aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
					        data-go="<?php echo (int) $i; ?>"
					        aria-label="<?php echo esc_attr( 'Go to slide ' . ( $i + 1 ) ); ?>"></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
