<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\Formatter;

/**
 * The reference design's repeating block: a scrollable row of image cards
 * followed by reviews for that same topic. The "information" part above it is
 * rendered by the caller (feature cards), so this component owns parts 2 and 3.
 *
 *   'id'           => unique id, used to scope the carousel controls
 *   'carousel_tag' => small numbered label above the row
 *   'items'        => [ { title, desc, image, cta:{label,route} } ]
 *   'reviews_tag'  => small label above the reviews
 *   'reviews'      => [ { text, name, role, rating } ]
 */

$id           = ( isset( $id ) && '' !== trim( (string) $id ) ) ? sanitize_html_class( (string) $id ) : 'showcase';
$carousel_tag = isset( $carousel_tag ) ? trim( (string) $carousel_tag ) : '';
$reviews_tag  = isset( $reviews_tag ) ? trim( (string) $reviews_tag ) : '';

$items = ( isset( $items ) && is_array( $items ) ) ? $items : array();
$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) {
				$item = (array) $item;
				$cta  = (array) ( $item['cta'] ?? array() );
				return array(
					'title' => trim( (string) ( $item['title'] ?? '' ) ),
					'desc'  => (string) ( $item['desc'] ?? '' ),
					'image' => (string) ( $item['image'] ?? '' ),
					'cta'   => array(
						'label' => trim( (string) ( $cta['label'] ?? '' ) ),
						'route' => (string) ( $cta['route'] ?? '' ),
					),
				);
			},
			$items
		),
		static function ( $item ) {
			return '' !== $item['title'];
		}
	)
);

$reviews = ( isset( $reviews ) && is_array( $reviews ) ) ? $reviews : array();
$reviews = array_values(
	array_filter(
		array_map(
			static function ( $review ) {
				$review = (array) $review;
				return array(
					'text'   => trim( (string) ( $review['text'] ?? '' ) ),
					'name'   => trim( (string) ( $review['name'] ?? '' ) ),
					'role'   => (string) ( $review['role'] ?? '' ),
					'rating' => max( 0, min( 5, (int) ( $review['rating'] ?? 5 ) ) ),
					'image'  => (string) ( $review['image'] ?? '' ),
				);
			},
			$reviews
		),
		static function ( $review ) {
			return '' !== $review['text'] && '' !== $review['name'];
		}
	)
);

if ( empty( $items ) && empty( $reviews ) ) {
	return;
}
?>
<?php if ( ! empty( $items ) ) : ?>
	<div class="showcase" id="<?php echo esc_attr( $id ); ?>" data-vs-showcase>
		<?php if ( '' !== $carousel_tag ) : ?>
			<span class="showcase__tag"><?php echo esc_html( $carousel_tag ); ?></span>
		<?php endif; ?>
		<div class="showcase__viewport">
			<div class="showcase__track">
				<?php foreach ( $items as $item ) : 
					$array = ['a', 'b', 'c'];
				?>
					<article class="showcase__card roughness-<?php echo $array[array_rand($array)]; ?>">
						<?php if ( '' !== $item['image'] ) : ?>
							<div class="showcase__media">
								<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy">
							</div>
						<?php endif; ?>
						<div class="showcase__body">
							<h4 class="showcase__title"><?php echo esc_html( $item['title'] ); ?></h4>
							<?php if ( '' !== $item['desc'] ) : ?>
								<p class="showcase__desc"><?php echo esc_html( $item['desc'] ); ?></p>
							<?php endif; ?>
							<?php if ( '' !== $item['cta']['label'] && '' !== $item['cta']['route'] ) : ?>
								<a class="btn btn--sm showcase__cta" href="<?php echo esc_url( RouteService::url( $item['cta']['route'] ) ); ?>">
									<?php echo esc_html( $item['cta']['label'] ); ?>
								</a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
		<button type="button" class="carousel-arrow showcase__arrow showcase__arrow--prev" aria-label="<?php esc_attr_e( 'Scroll left', 'vintagesoul' ); ?>">
			<span aria-hidden="true">&larr;</span>
		</button>
		<button type="button" class="carousel-arrow showcase__arrow showcase__arrow--next" aria-label="<?php esc_attr_e( 'Scroll right', 'vintagesoul' ); ?>">
			<span aria-hidden="true">&rarr;</span>
		</button>
	</div>
<?php endif; ?>

<?php if ( ! empty( $reviews ) ) : ?>
	<div class="showcase-reviews">
		<?php if ( '' !== $reviews_tag ) : ?>
			<span class="showcase__tag"><?php echo esc_html( $reviews_tag ); ?></span>
		<?php endif; ?>
		<div class="showcase-reviews__grid">
			<?php foreach ( $reviews as $review ) : 
				$array = ['a', 'b', 'c']
			?>
				<figure class="showcase-review roughness-<?php echo $array[array_rand($array)]; ?>">
					<span class="showcase-review__mark" aria-hidden="true">&ldquo;</span>
					<blockquote class="showcase-review__text"><?php echo esc_html( $review['text'] ); ?></blockquote>
					<span class="showcase-review__rating" aria-label="<?php echo esc_attr( sprintf( __( '%d out of 5 stars', 'vintagesoul' ), $review['rating'] ) ); ?>">
						<?php echo esc_html( Formatter::star_rating( $review['rating'] ) ); ?>
					</span>
					<figcaption class="showcase-review__author">
						<?php if ( '' !== $review['image'] ) : ?>
							<img class="showcase-review__avatar" src="<?php echo esc_url( $review['image'] ); ?>" alt="" loading="lazy">
						<?php else : ?>
							<span class="showcase-review__avatar showcase-review__avatar--fallback" aria-hidden="true"><?php echo esc_html( mb_substr( $review['name'], 0, 1 ) ); ?></span>
						<?php endif; ?>
						<span class="showcase-review__meta">
							<span class="showcase-review__name"><?php echo esc_html( $review['name'] ); ?></span>
							<?php if ( '' !== $review['role'] ) : ?>
								<span class="showcase-review__role"><?php echo esc_html( $review['role'] ); ?></span>
							<?php endif; ?>
						</span>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
