<?php
/**
 * VintageSoulTheme - Reusable Infinite Card Stream Component
 *
 * Renders an infinite, seamless scrolling stream of cards (LTR or RTL)
 * with hover pause, gradient mask, and support for gallery, review, product,
 * and social card variants.
 */
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

$items       = isset( $items ) && is_array( $items ) ? $items : array();
$card_type   = (string) ( $card_type ?? 'gallery' );
$direction   = strtolower( (string) ( $direction ?? 'ltr' ) );
$direction   = in_array( $direction, array( 'ltr', 'rtl' ), true ) ? $direction : 'ltr';
$loop_count  = (int) ( $loop_count ?? 4 );
$aria_label  = (string) ( $aria_label ?? '' );
$extra_class = isset( $class ) ? (string) $class : '';

if ( empty( $items ) ) {
	return;
}

$track_wrap_class = 'social-stream__track-wrap social-stream__track-wrap--' . $direction;
if ( '' !== $extra_class ) {
	$track_wrap_class .= ' ' . $extra_class;
}
$track_class = 'social-stream__track social-stream__track--' . $direction;
?>
<div class="<?php echo esc_attr( $track_wrap_class ); ?>"<?php echo '' !== $aria_label ? ' aria-label="' . esc_attr( $aria_label ) . '"' : ''; ?>>
	<div class="<?php echo esc_attr( $track_class ); ?>">
		<?php for ( $loop = 0; $loop < $loop_count; $loop++ ) : ?>
			<?php foreach ( $items as $item ) : ?>
				<?php if ( 'gallery' === $card_type ) :
					$raw_img = (string) ( $item['image'] ?? 'assets/images/sugarcane/hero_juice.jpg' );
					$img     = UrlHelper::resolve( $raw_img );
					$title   = (string) ( $item['title'] ?? '' );
					$tag     = (string) ( $item['tag'] ?? ( $item['date'] ?? '' ) );
				?>
					<div class="event-gallery-card franchise-gallery-card order-gallery-card frame--rough-cut" 
						 tabindex="0" 
						 role="button" 
						 aria-haspopup="dialog" 
						 aria-label="<?php echo esc_attr( $title ); ?>"
						 data-story-modal="true"
						 data-story-title="<?php echo esc_attr( $title ); ?>"
						 data-story-meta="<?php echo esc_attr( $tag ); ?>"
						 data-story-image="<?php echo esc_url( $img ); ?>">
						<div class="event-gallery-card__media franchise-gallery-card__media order-gallery-card__media">
							<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
							<?php if ( '' !== $tag ) : ?>
								<span class="event-gallery-card__tag franchise-gallery-card__tag order-gallery-card__tag"><?php echo esc_html( $tag ); ?></span>
							<?php endif; ?>
						</div>
						<?php if ( '' !== $title ) : ?>
							<h4 class="event-gallery-card__title franchise-gallery-card__title order-gallery-card__title"><?php echo esc_html( $title ); ?></h4>
						<?php endif; ?>
					</div>

				<?php elseif ( 'memory' === $card_type ) :
					$caption = (string) ( $item['caption'] ?? ( $item['title'] ?? '' ) );
					$raw_img = (string) ( $item['image'] ?? 'assets/images/sugarcane/story_moments.jpg' );
					$img     = UrlHelper::resolve( $raw_img );
				?>
					<div class="memory-card-vintage frame--ornate"
						 tabindex="0" 
						 role="button" 
						 aria-haspopup="dialog" 
						 aria-label="<?php echo esc_attr( $caption ); ?>"
						 data-story-modal="true"
						 data-story-title="<?php echo esc_attr( $caption ); ?>"
						 data-story-badge="HERITAGE MOMENT"
						 data-story-image="<?php echo esc_url( $img ); ?>">
						<div class="memory-card-vintage__media">
							<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $caption ); ?>" loading="lazy">
							<div class="memory-card-vintage__stamp">
								<span>✦ CANE MEMORY ✦</span>
							</div>
						</div>
						<?php if ( '' !== $caption ) : ?>
							<div class="memory-card-vintage__caption">“<?php echo esc_html( $caption ); ?>”</div>
						<?php endif; ?>
					</div>

				<?php elseif ( 'review' === $card_type || 'testimonial' === $card_type ) :
					$rating_raw = $item['rating'] ?? 5;
					$rating_str = is_numeric( $rating_raw ) ? str_repeat( '★', max( 1, min( 5, (int) $rating_raw ) ) ) : (string) $rating_raw;
					$quote      = (string) ( $item['quote'] ?? ( $item['text'] ?? '' ) );
					$author     = (string) ( $item['author'] ?? ( $item['name'] ?? '' ) );
					$meta       = (string) ( $item['event'] ?? ( $item['location'] ?? ( $item['city'] ?? '' ) ) );
					$badge      = (string) ( $item['badge'] ?? '' );
					$raw_bg     = isset( $item['image'] ) ? (string) $item['image'] : ( isset( $item['bg_image'] ) ? (string) $item['bg_image'] : '' );
					$bg_img     = '' !== $raw_bg ? UrlHelper::resolve( $raw_bg ) : '';
					$has_bg     = '' !== $bg_img;
				?>
					<div class="event-review-card review-box card--rough-cut<?php echo $has_bg ? ' has-bg-img' : ''; ?>"
						 <?php echo $has_bg ? 'style="--card-bg-img: url(\'' . esc_url( $bg_img ) . '\');"' : ''; ?>
						 tabindex="0" 
						 role="button" 
						 aria-haspopup="dialog" 
						 aria-label="Review by <?php echo esc_attr( $author ); ?>"
						 data-story-modal="true"
						 data-story-rating="<?php echo esc_attr( $rating_str ); ?>"
						 data-story-quote="<?php echo esc_attr( $quote ); ?>"
						 data-story-author="<?php echo esc_attr( $author ); ?>"
						 data-story-meta="<?php echo esc_attr( $meta ); ?>"
						 data-story-badge="<?php echo esc_attr( $badge ); ?>"
						 <?php echo $has_bg ? 'data-story-image="' . esc_url( $bg_img ) . '"' : ''; ?>>
						<div class="event-review-card__rating review-box__stars"><?php echo esc_html( $rating_str ); ?></div>
						<p class="event-review-card__quote review-box__quote">“<?php echo esc_html( $quote ); ?>”</p>
						<div class="event-review-card__meta review-box__author">
							<strong class="review-box__name">— <?php echo esc_html( $author ); ?></strong>
							<?php if ( '' !== $meta ) : ?>
								<span class="review-box__location"><?php echo esc_html( $meta ); ?></span>
							<?php endif; ?>
						</div>
						<?php if ( '' !== $badge ) : ?>
							<span class="review-box__badge"><?php echo esc_html( $badge ); ?></span>
						<?php endif; ?>
					</div>

				<?php elseif ( 'dark-review' === $card_type ) :
					$rating = (string) ( $item['rating'] ?? '★★★★★' );
					$quote  = (string) ( $item['quote'] ?? '' );
					$author = (string) ( $item['author'] ?? '' );
					$meta   = (string) ( $item['city'] ?? ( $item['event'] ?? '' ) );
					$raw_bg = isset( $item['image'] ) ? (string) $item['image'] : ( isset( $item['bg_image'] ) ? (string) $item['bg_image'] : '' );
					$bg_img = '' !== $raw_bg ? UrlHelper::resolve( $raw_bg ) : '';
					$has_bg = '' !== $bg_img;
				?>
					<div class="franchise-review-card order-review-card card--rough-cut-dark<?php echo $has_bg ? ' has-bg-img' : ''; ?>"
						 <?php echo $has_bg ? 'style="--card-bg-img: url(\'' . esc_url( $bg_img ) . '\');"' : ''; ?>
						 tabindex="0" 
						 role="button" 
						 aria-haspopup="dialog" 
						 aria-label="Review by <?php echo esc_attr( $author ); ?>"
						 data-story-modal="true"
						 data-story-rating="<?php echo esc_attr( $rating ); ?>"
						 data-story-quote="<?php echo esc_attr( $quote ); ?>"
						 data-story-author="<?php echo esc_attr( $author ); ?>"
						 data-story-meta="<?php echo esc_attr( $meta ); ?>"
						 <?php echo $has_bg ? 'data-story-image="' . esc_url( $bg_img ) . '"' : ''; ?>>
						<div class="franchise-review-card__rating order-review-card__rating"><?php echo esc_html( $rating ); ?></div>
						<p class="franchise-review-card__quote order-review-card__quote">“<?php echo esc_html( $quote ); ?>”</p>
						<div class="franchise-review-card__meta order-review-card__meta">
							<strong><?php echo esc_html( $author ); ?></strong>
							<?php if ( '' !== $meta ) : ?>
								<span><?php echo esc_html( $meta ); ?></span>
							<?php endif; ?>
						</div>
					</div>

				<?php elseif ( 'product' === $card_type ) :
					$name        = (string) ( $item['name'] ?? '' );
					$desc        = (string) ( $item['desc'] ?? '' );
					$tag         = (string) ( $item['tag'] ?? '100% PURE CANE' );
					$badge       = (string) ( $item['badge'] ?? '' );
					$ingredients = (array) ( $item['ingredients'] ?? array() );
					$ing_str     = ! empty( $ingredients ) ? implode( ' • ', $ingredients ) : '';
					$raw_img     = (string) ( $item['image'] ?? 'assets/images/sugarcane/drink_classic.jpg' );
					$img         = UrlHelper::resolve( $raw_img );
				?>
					<div class="order-product-card card--rough-cut frame--rough-cut"
						 tabindex="0" 
						 role="button" 
						 aria-haspopup="dialog" 
						 aria-label="<?php echo esc_attr( $name ); ?>"
						 data-story-modal="true"
						 data-story-title="<?php echo esc_attr( $name ); ?>"
						 data-story-badge="<?php echo esc_attr( $badge ?: $tag ); ?>"
						 data-story-meta="100% Fresh Pressed Cane"
						 data-story-quote="<?php echo esc_attr( $desc . ( '' !== $ing_str ? ' | Ingredients: ' . $ing_str : '' ) ); ?>"
						 data-story-image="<?php echo esc_url( $img ); ?>">
						<div class="order-product-card__media">
							<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
							<?php if ( '' !== $badge ) : ?>
								<span class="order-product-card__badge"><?php echo esc_html( $badge ); ?></span>
							<?php endif; ?>
							<span class="order-product-card__tag"><?php echo esc_html( $tag ); ?></span>
						</div>
						<div class="order-product-card__content">
							<h3 class="order-product-card__title"><?php echo esc_html( $name ); ?></h3>
							<p class="order-product-card__desc"><?php echo esc_html( $desc ); ?></p>
							<?php if ( ! empty( $ingredients ) ) : ?>
								<div class="order-product-card__ingredients">
									<span>🌿 <?php echo esc_html( implode( ', ', array_slice( $ingredients, 0, 3 ) ) ); ?></span>
								</div>
							<?php endif; ?>
						</div>
					</div>

				<?php elseif ( 'social' === $card_type ) :
					$platform = (string) ( $item['platform'] ?? 'instagram' );
					$badge    = (string) ( $item['badge'] ?? 'Social Post' );
					$handle   = (string) ( $item['handle'] ?? '@thecanehouse.uk' );
					$caption  = (string) ( $item['caption'] ?? '' );
					$likes    = (string) ( $item['likes'] ?? '1k' );
					$comments = (string) ( $item['comments'] ?? '50' );
					$raw_img  = (string) ( $item['image'] ?? 'assets/images/sugarcane/hero_juice.jpg' );
					$img      = UrlHelper::resolve( $raw_img );
					$raw_vid  = (string) ( $item['video'] ?? 'assets/videos/hero_bg.mp4' );
					$video    = str_starts_with( $raw_vid, 'http' ) ? $raw_vid : UrlHelper::resolve( $raw_vid );
					$link     = (string) ( $item['link'] ?? 'https://instagram.com' );
				?>
					<div class="social-card card--rough-cut"
						 tabindex="0" 
						 role="button" 
						 aria-haspopup="dialog" 
						 aria-label="<?php echo esc_attr( $badge . ': ' . $caption ); ?>"
						 data-story-modal="true"
						 data-story-platform="<?php echo esc_attr( $platform ); ?>"
						 data-story-badge="<?php echo esc_attr( $badge ); ?>"
						 data-story-author="<?php echo esc_attr( $handle ); ?>"
						 data-story-quote="<?php echo esc_attr( $caption ); ?>"
						 data-story-image="<?php echo esc_url( $img ); ?>"
						 data-story-video="<?php echo esc_url( $video ); ?>"
						 data-story-link="<?php echo esc_url( $link ); ?>"
						 data-story-likes="<?php echo esc_attr( $likes ); ?>"
						 data-story-comments="<?php echo esc_attr( $comments ); ?>">
						<div class="social-card__media">
							<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $caption ); ?>" loading="lazy">
							<span class="social-card__platform-badge social-card__platform-badge--<?php echo esc_attr( $platform ); ?>">
								<?php if ( 'instagram' === $platform ) : ?>
									<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
								<?php elseif ( 'youtube' === $platform ) : ?>
									<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><polygon points="10 15 15 12 10 9"/></svg>
								<?php else : ?>
									<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>
								<?php endif; ?>
								<span><?php echo esc_html( $badge ); ?></span>
							</span>
							<div class="social-card__play-btn" aria-hidden="true">▶</div>
						</div>
						<div class="social-card__content">
							<span class="social-card__handle"><?php echo esc_html( $handle ); ?></span>
							<p class="social-card__caption"><?php echo esc_html( $caption ); ?></p>
							<div class="social-card__meta">
								<span>❤️ <?php echo esc_html( $likes ); ?></span>
								<span>💬 <?php echo esc_html( $comments ); ?></span>
							</div>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endfor; ?>
	</div>
</div>
