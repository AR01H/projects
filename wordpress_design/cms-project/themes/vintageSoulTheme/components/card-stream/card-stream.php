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
					$raw_input = is_string( $item ) ? $item : (string) ( $item['embed'] ?? ( $item['html'] ?? ( $item['embed_code'] ?? ( $item['link'] ?? ( $item['url'] ?? ( $item['video'] ?? '' ) ) ) ) ) );
					
					// If full HTML blockquote or iframe snippet is provided, extract the URL automatically
					if ( preg_match( '/data-instgrm-permalink=["\']([^"\']+)["\']/i', $raw_input, $matches ) ) {
						$raw_link = $matches[1];
					} elseif ( preg_match( '/src=["\']([^"\']+)["\']/i', $raw_input, $matches ) ) {
						$raw_link = $matches[1];
					} else {
						$raw_link = $raw_input;
					}

					if ( '' === $raw_link ) {
						$raw_link = 'https://www.instagram.com/p/DbgIbW4h42y/';
					}

					$embed_url = UrlHelper::resolve_video_embed( $raw_link );
				?>
					<div class="social-card social-card--embed card--rough-cut">
						<iframe class="social-card__instagram-embed"
								src="<?php echo esc_url( $embed_url ); ?>"
								loading="lazy"
								frameborder="0"
								scrolling="no"
								allowtransparency="true"
								allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
						</iframe>
					</div>

				<?php elseif ( 'team' === $card_type ) :
					$name      = (string) ( $item['name'] ?? '' );
					$role      = (string) ( $item['role'] ?? '' );
					$bio       = (string) ( $item['bio'] ?? '' );
					$raw_photo = (string) ( $item['photo'] ?? ( $item['image'] ?? 'assets/images/sugarcane/story_moments.jpg' ) );
					$photo     = UrlHelper::resolve( $raw_photo );
				?>
					<div class="team-card frame--rough-cut"
						 tabindex="0" 
						 role="button" 
						 aria-haspopup="dialog" 
						 aria-label="<?php echo esc_attr( $name ); ?>"
						 data-story-modal="true"
						 data-story-title="<?php echo esc_attr( $name ); ?>"
						 data-story-badge="<?php echo esc_attr( $role ); ?>"
						 data-story-quote="<?php echo esc_attr( $bio ); ?>"
						 data-story-image="<?php echo esc_url( $photo ); ?>">
						<div class="team-card__photo frame--ornate-sm">
							<img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
						</div>
						<div class="team-card__body">
							<h4 class="team-card__name"><?php echo esc_html( $name ); ?></h4>
							<span class="team-card__role"><?php echo esc_html( $role ); ?></span>
							<p class="team-card__bio"><?php echo esc_html( $bio ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endfor; ?>
	</div>
</div>
