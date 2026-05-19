<?php
defined( 'ABSPATH' ) || exit;
$reviews = ch_get_reviews( 6 );
if ( empty( $reviews ) ) return;
?>

<section id="reviews" class="ch-reviews-section">
	<div class="ch-reviews__header fade-up">
		<div class="ch-section-tag">Happy Customers</div>
		<h2 class="ch-section-title">What Our <span class="accent">Fans Say</span></h2>
		<p class="ch-section-body">Real reviews from our sugarcane lovers across the UK - and beyond!</p>
	</div>

	<div class="ch-reviews-container">
		<div class="ch-reviews-track" id="ch-reviews-track">
			<?php foreach ( $reviews as $i => $r ) :
				$r = (array) $r;
				$name     = esc_html( $r['author_name'] ?? 'Happy Customer' );
				$location = esc_html( $r['location']    ?? 'Verified Customer' );
				$text     = esc_html( $r['review_text'] ?? '' );
				$rating   = (float) ( $r['rating'] ?? 5.0 );
				$result   = esc_html( $r['result']      ?? '' );
				$avatar   = 'https://i.pravatar.cc/300?u=' . ( $i + 1 );
			?>
				<div class="ch-review-card fade-up<?php echo $i === 0 ? ' active' : ''; ?>">
					<div class="ch-review-avatar">
						<img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo $name; ?>" loading="lazy">
					</div>
					<div class="ch-review-content">
						<div class="ch-review-rating">
							<?php ch_stars( $rating ); ?>
						</div>
						<p class="ch-review-text"><?php echo $text; ?></p>
						<div class="ch-review-meta">
							<div class="ch-review-name"><?php echo $name; ?></div>
							<div class="ch-review-location"><?php echo $location; ?></div>
							<?php if ( $result ) : ?>
								<div class="ch-review-result">✓ <?php echo $result; ?></div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="ch-reviews-nav" aria-label="<?php esc_attr_e( 'Review navigation', 'ch-theme' ); ?>">
			<div class="ch-nav-dots" id="ch-nav-dots">
				<?php foreach ( $reviews as $i => $_ ) : ?>
					<span class="ch-dot<?php echo $i === 0 ? ' active' : ''; ?>" data-index="<?php echo $i; ?>" role="button" tabindex="0" aria-label="Review <?php echo $i + 1; ?>"></span>
				<?php endforeach; ?>
			</div>
			<div class="ch-nav-arrows">
				<button class="ch-v-btn" id="ch-rev-prev" aria-label="<?php esc_attr_e( 'Previous review', 'ch-theme' ); ?>">↑</button>
				<button class="ch-v-btn" id="ch-rev-next" aria-label="<?php esc_attr_e( 'Next review', 'ch-theme' ); ?>">↓</button>
			</div>
		</div>
	</div>
</section>
