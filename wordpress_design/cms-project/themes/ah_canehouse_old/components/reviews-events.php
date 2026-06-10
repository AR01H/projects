<?php
/**
 * Event-context review section - white bg, event-type badge per card, mobile carousel.
 *
 * Args (all optional):
 *  limit  (int)     Number of reviews to show.  Default: 6
 *  tag    (string)  Eyebrow tag.                Default: 'Event Reviews'
 *  title  (string)  Heading HTML.               Default: preset
 *  body   (string)  Intro paragraph.            Default: preset
 */
defined( 'ABSPATH' ) || exit;

$_ev = CH_Shared_Data::reviews_events_settings();

$limit   = $args['limit'] ?? $_ev['limit'] ?? 6;
$reviews = ch_get_reviews( $limit, 'event' );
if ( empty( $reviews ) ) return;

$tag          = $args['tag']   ?? $_ev['tag']          ?? '';
$title        = $args['title'] ?? $_ev['title']        ?? '';
$body         = $args['body']  ?? $_ev['body']         ?? '';
$event_badges = $_ev['event_badges'] ?? [];
$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="ch-reviews-events-section">
	<div class="container">
		<?php get_template_part( 'components/section-header', null, [
			'tag'   => $tag,
			'title' => $title,
			'body'  => $body,
		] ); ?>

		<div class="ch-rev-ev-carousel fade-up">
			<div class="ch-rev-ev-track" id="ch-rev-ev-track">
				<?php foreach ( $reviews as $i => $r ) :
					$r      = (array) $r;
					$name   = esc_html( $r['author_name'] ?? 'Happy Customer' );
					$loc    = esc_html( $r['location']    ?? 'Verified Customer' );
					$_names = ch_get_review_highlight_names( (int) ( $r['id'] ?? 0 ) );
					$text   = ch_highlight_text( wp_strip_all_tags( $r['review_text'] ?? '' ), $_names );
					$rating = (float) ( $r['rating'] ?? 5.0 );
					$badge  = $event_badges[ $i % count( $event_badges ) ];
					$avatar = ch_get_review_image( $r, $i + 20, 'thumbnail' );
				?>
					<div class="ch-rev-ev-card<?php echo $i === 0 ? ' active' : ''; ?>">
						<div class="ch-rev-ev-badge"><?php echo esc_html( $badge ); ?></div>
						<div class="ch-rev-ev-stars">
							<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
								<span class="ch-star<?php echo $s <= $rating ? ' ch-star--full' : ' ch-star--empty'; ?>">★</span>
							<?php endfor; ?>
						</div>
						<p class="ch-rev-ev-text"><?php echo $text; ?></p>
						<div class="ch-rev-ev-author">
							<?php if ( $avatar ) : ?>
								<img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo $name; ?>" class="ch-rev-ev-avatar" loading="lazy">
							<?php endif; ?>
							<div>
								<div class="ch-rev-ev-name"><?php echo $name; ?></div>
								<div class="ch-rev-ev-loc"><?php echo $loc; ?></div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Mobile nav -->
			<div class="ch-rev-ev-nav">
				<div class="ch-rev-ev-dots" id="ch-rev-ev-dots" role="tablist" aria-label="Event reviews navigation">
					<?php foreach ( $reviews as $i => $_ ) : ?>
						<button class="ch-dot<?php echo $i === 0 ? ' active' : ''; ?>"
							role="tab" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
							aria-label="Review <?php echo $i + 1; ?>"></button>
					<?php endforeach; ?>
				</div>
				<div class="ch-rev-ev-arrows">
					<button class="ch-v-btn" id="ch-rev-ev-prev" aria-label="Previous review">←</button>
					<button class="ch-v-btn" id="ch-rev-ev-next" aria-label="Next review">→</button>
				</div>
			</div>
		</div>
	</div>
</section>
