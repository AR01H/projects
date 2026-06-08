<?php
defined( 'ABSPATH' ) || exit;
$_d      = CH_Shared_Data::review_carousel_settings();
$reviews = ch_get_reviews( 6 );
if ( empty( $reviews ) ) return;
?>

<section id="reviews" class="ch-reviews-section">

	<?php get_template_part( 'components/section-header', null, [
		'tag'           => $_d['tag']   ?? '',
		'title'         => $_d['title'] ?? '',
		'body'          => $_d['body']  ?? '',
		'wrapper_class' => 'ch-reviews-header',
	] ); ?>

	<div class="ch-reviews-wrapper">

		<!-- ── Cards ─────────────────────────────────────────────────────────── -->
		<div class="ch-reviews-track" id="ch-reviews-track">
			<?php foreach ( $reviews as $i => $r ) :
				$r        = (array) $r;
				$name     = esc_html( $r['author_name'] ?? 'Happy Customer' );
				$location = esc_html( $r['location']    ?? 'Verified Customer' );
				$_names   = ch_get_review_highlight_names( (int) ( $r['id'] ?? 0 ) );
				$text     = ch_highlight_text( wp_strip_all_tags( $r['review_text'] ?? '' ), $_names );
				$rating   = (float) ( $r['rating'] ?? 5.0 );
				$avatar   = ch_get_review_image( $r, $i, 'thumbnail' );
			?>
				<div class="ch-review-card<?php echo $i === 0 ? ' active' : ''; ?>">

					<div class="ch-review-quote">&#10077;</div>

					<p class="ch-review-text"><?php echo $text ?: 'Absolutely amazing experience. The freshest cane juice we&rsquo;ve ever had!'; ?></p>

					<div class="ch-review-footer">
						<div class="ch-review-author">
							<?php if ( $avatar ) : ?>
								<img src="<?php echo esc_url( $avatar ); ?>"
									alt="<?php echo $name; ?>"
									class="ch-review-avatar" loading="lazy">
							<?php endif; ?>
							<div class="ch-review-author-info">
								<div class="ch-review-name"><?php echo $name; ?></div>
								<div class="ch-review-subtitle"><?php echo $location; ?></div>
							</div>
						</div>
						<div class="ch-review-stars" aria-label="<?php echo esc_attr( $rating . ' stars out of 5' ); ?>">
							<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
								<span class="ch-star<?php echo $s <= $rating ? ' ch-star--full' : ' ch-star--empty'; ?>">★</span>
							<?php endfor; ?>
						</div>
					</div>

				</div>
			<?php endforeach; ?>
		</div>

		<!-- ── Bottom navigation: dots ← → ─────────────────────────────────── -->
		<div class="ch-reviews-nav">
			<div class="ch-nav-dots" id="ch-nav-dots" role="tablist" aria-label="Reviews navigation">
				<?php foreach ( $reviews as $i => $_ ) : ?>
					<button class="ch-dot<?php echo $i === 0 ? ' active' : ''; ?>"
						role="tab" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
						aria-label="Review <?php echo $i + 1; ?>"></button>
				<?php endforeach; ?>
			</div>
			<div class="ch-reviews-arrows">
				<button class="ch-v-btn" id="ch-rev-prev" aria-label="Previous review">←</button>
				<button class="ch-v-btn" id="ch-rev-next" aria-label="Next review">→</button>
			</div>
		</div>

		<?php ch_more_button( home_url( '/client-stories/' ), 'Read All Reviews & Stories →' ); ?>

	</div><!-- .ch-reviews-wrapper -->
</section>
