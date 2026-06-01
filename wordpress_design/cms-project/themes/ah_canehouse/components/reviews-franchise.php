<?php
defined( 'ABSPATH' ) || exit;

$limit   = $args['limit'] ?? 5;
$reviews = ch_get_reviews( $limit, 'partner' );
if ( empty( $reviews ) ) return;

$tag   = $args['tag']   ?? 'Franchise Partners';
$title = $args['title'] ?? 'Hear From Our <span class="accent" style="color:var(--ch-lime);">Partners</span>';
$body  = $args['body']  ?? 'Real people who took the leap and built something they\'re proud of.';

$cities  = [ 'Birmingham', 'Manchester', 'Leeds', 'Leicester', 'London', 'Bradford' ];
$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="ch-reviews-franchise-section">
	<div class="container">
		<div class="ch-section-center fade-up" style="color:var(--ch-white);">
			<div class="section-tag" style="color:var(--ch-lime);"><?php echo esc_html( $tag ); ?></div>
			<h2 class="section-title" style="color:var(--ch-white);"><?php echo wp_kses( $title, $allowed ); ?></h2>
			<p class="section-body" style="color:rgba(255,255,255,0.65);"><?php echo esc_html( $body ); ?></p>
		</div>

		<div class="ch-rfr-carousel fade-up">
			<div class="ch-rfr-track" id="ch-rfr-track">
				<?php foreach ( $reviews as $i => $r ) :
					$r = (array) $r;
					$name   = esc_html( $r['author_name'] ?? 'Partner' );
					$location = esc_html( $r['location'] ?? $cities[ $i ] ?? 'UK' );
					$_names = ch_get_review_highlight_names( (int) ( $r['id'] ?? 0 ) );
					$text   = ch_highlight_text( wp_strip_all_tags( $r['review_text'] ?? '' ), $_names );
					$rating = (float) ( $r['rating'] ?? 5.0 );
					$avatar = ch_get_review_image( $r, $i, 'thumbnail' );
				?>
					<div class="ch-rfr-card<?php echo $i === 0 ? ' active' : ''; ?>">
						<div class="ch-rfr-card__quote">&#10077;</div>
						<p class="ch-rfr-card__text"><?php echo $text; ?></p>
						<div class="ch-rfr-card__author">
							<?php if ( $avatar ) : ?>
								<img src="<?php echo esc_url( $avatar ); ?>"
									alt="<?php echo $name; ?>"
									class="ch-rfr-card__avatar" loading="lazy">
							<?php endif; ?>
							<div>
								<div class="ch-rfr-card__name"><?php echo $name; ?></div>
								<div class="ch-rfr-card__city">
									<?php echo $location; ?>
									&nbsp;·&nbsp;
									<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
										<span class="ch-star ch-star--full" style="color:var(--ch-lime);font-size:.8rem;">★</span>
									<?php endfor; ?>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="ch-rfr-nav">
				<div class="ch-rfr-dots" id="ch-rfr-dots" role="tablist" aria-label="Partner reviews navigation">
					<?php foreach ( $reviews as $i => $_ ) : ?>
						<button class="ch-dot<?php echo $i === 0 ? ' active' : ''; ?>"
							role="tab" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
							aria-label="Review <?php echo $i + 1; ?>"></button>
					<?php endforeach; ?>
				</div>
				<div class="ch-rfr-arrows">
					<button class="ch-v-btn" id="ch-rfr-prev" aria-label="Previous">←</button>
					<button class="ch-v-btn" id="ch-rfr-next" aria-label="Next">→</button>
				</div>
			</div>
		</div>
	</div>
</section>
