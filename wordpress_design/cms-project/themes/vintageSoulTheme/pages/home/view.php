<?php

use VintageSoul\Controllers\HomeController;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new HomeController() )->prepare();

$hero     = (array) ( $data['hero'] ?? array() );
$ticker   = (array) ( $data['ticker'] ?? array() );
$stats    = (array) ( $data['stats'] ?? array() );
$story    = (array) ( $data['story'] ?? array() );
$sourcing = (array) ( $data['sourcing'] ?? array() );
$products = (array) ( $data['products'] ?? array() );
$certs    = (array) ( $data['certifications'] ?? array() );
$benefits = (array) ( $data['benefits'] ?? array() );
$serve    = (array) ( $data['serve_steps'] ?? array() );
$gallery  = (array) ( $data['gallery'] ?? array() );
$memories = (array) ( $data['memories'] ?? array() );
$order    = (array) ( $data['order_steps'] ?? array() );
$events   = (array) ( $data['events'] ?? array() );
$fran     = (array) ( $data['franchise'] ?? array() );
$enquiry  = (array) ( $data['enquiry'] ?? array() );
$faqs     = (array) ( $data['faqs'] ?? array() );
$contact  = (array) ( $data['contact'] ?? array() );
$closing  = (array) ( $data['closing'] ?? array() );
$showcases = (array) ( $data['showcases'] ?? array() );
$testimonials_meta = (array) ( $data['testimonials_meta'] ?? array() );
?>

<?php if ( ! empty( $hero['enabled'] ) ) : ?>
	<?php
	View::component(
		'hero/hero',
		array(
			'id'            => 'home-hero',
			'heading_level' => 1,
			'settings'      => (array) ( $hero['settings'] ?? array() ),
			'slides'        => (array) ( $hero['slides'] ?? array() ),
		)
	);
	?>
<?php endif; ?>

<?php if ( ! empty( $ticker['items'] ) ) : ?>
	<?php View::component( 'marquee/marquee', array( 'items' => $ticker['items'], 'variant' => 'a' ) ); ?>
<?php endif; ?>

<?php if ( ! empty( $stats['items'] ) ) : ?>
	<section class="section section--sm section--scroll" id="stats">
		<div class="container">
			<?php View::component( 'stats/stats', array( 'items' => $stats['items'], 'ground' => false ) ); ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $story ) ) : ?>
	<section class="section" id="our-story">
		<div class="container">
			<?php
			View::component(
				'banner/banner',
				array(
					'id'      => 'story-banner',
					'tag'     => (string) ( $story['tag'] ?? '' ),
					'title'   => trim( (string) ( $story['heading_lead'] ?? '' ) . ' <em>' . (string) ( $story['heading_em'] ?? '' ) . '</em>' ),
					'sub'     => (string) ( $story['subtitle'] ?? '' ),
					'body'    => array_filter( array(
						(string) ( $story['body_1'] ?? '' ),
						(string) ( $story['body_2'] ?? '' ),
						(string) ( $story['body_3'] ?? '' ),
					) ),
					'image'   => (string) ( $story['image'] ?? '' ),
					'image_alt' => (string) ( $story['image_alt'] ?? '' ),
					'items'   => array_map(
						static function ( $p ) {
							$p = (array) $p;
							return array( 'label' => (string) ( $p['label'] ?? '' ), 'text' => (string) ( $p['note'] ?? '' ) );
						},
						(array) ( $story['pillars'] ?? array() )
					),
					'stamp'   => array( 'center' => trim( (string) ( $story['stamp_line1'] ?? '' ) . ' ' . (string) ( $story['stamp_line2'] ?? '' ) ) ),
					'buttons' => array(
						array( 'label' => (string) ( $story['cta_label'] ?? '' ), 'route' => (string) ( $story['cta_route'] ?? '' ) ),
					),
				)
			);
			?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $sourcing['items'] ) ) : ?>
	<section class="section section--alt" id="sourcing">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $sourcing['tag'] ?? '' ),
					'title' => (string) ( $sourcing['title'] ?? '' ),
					'sub'   => (string) ( $sourcing['body'] ?? '' ),
				)
			);
			View::component( 'process-steps/process-steps', array( 'items' => $sourcing['items'] ) );
			?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $products['items'] ) ) : ?>
	<section class="section" id="our-drinks">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $products['tag'] ?? '' ),
					'title' => (string) ( $products['title'] ?? '' ),
					'sub'   => (string) ( $products['sub'] ?? '' ),
				)
			);
			// products.json carries one shared button for the whole range; the
			// design puts it on every card, so fan it out to any item without
			// its own.
			$shared_button = (array) ( $products['button'] ?? array() );
			$product_items = array_map(
				static function ( $item ) use ( $shared_button ) {
					$item = (array) $item;
					if ( empty( $item['button'] ) && ! empty( $shared_button ) ) {
						$item['button'] = $shared_button;
					}
					return $item;
				},
				(array) $products['items']
			);
			View::component( 'product-list/product-list', array( 'items' => $product_items ) );
			?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $certs['items'] ) ) : ?>
	<section class="section section--alt" id="certifications">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $certs['tag'] ?? '' ),
					'title' => (string) ( $certs['title'] ?? '' ),
					'sub'   => (string) ( $certs['body'] ?? '' ),
				)
			);
			View::component( 'certificate-carousel/certificate-carousel', array( 'items' => $certs['items'] ) );
			?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $benefits['items'] ) ) : ?>
	<section class="section" id="benefits">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $benefits['tag'] ?? '' ),
					'title' => (string) ( $benefits['title'] ?? '' ),
					'sub'   => (string) ( $benefits['body'] ?? '' ),
				)
			);
			?>
			<div class="feature-grid">
				<?php foreach ( (array) $benefits['items'] as $item ) :
					$item = (array) $item;
					View::component(
						'feature-card/feature-card',
						array(
							'icon'  => (string) ( $item['icon'] ?? '' ),
							'title' => (string) ( $item['title'] ?? '' ),
							'text'  => (string) ( $item['text'] ?? '' ),
							'stat'  => (string) ( $item['stat'] ?? '' ),
						)
					);
				endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $serve['steps'] ) ) : ?>
	<section class="section section--alt" id="how-we-serve">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $serve['tag'] ?? '' ),
					'title' => (string) ( $serve['title'] ?? '' ),
					'sub'   => (string) ( $serve['sub'] ?? '' ),
				)
			);
			View::component( 'step-chain/step-chain', array( 'items' => $serve['steps'] ) );
			?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $gallery['images'] ) ) : ?>
	<section class="section" id="gallery">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $gallery['tag'] ?? '' ),
					'title' => (string) ( $gallery['title'] ?? '' ),
					'sub'   => (string) ( $gallery['body'] ?? '' ),
				)
			);
			View::component(
				'gallery/gallery',
				array(
					'id'         => 'home-gallery',
					'items'      => $gallery['images'],
					'categories' => (array) ( $gallery['categories'] ?? array() ),
				)
			);
			?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $memories['items'] ) ) : ?>
	<section class="section section--alt" id="memories">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $memories['tag'] ?? '' ),
					'title' => (string) ( $memories['title'] ?? '' ),
				)
			);
			View::component( 'memories/memories', array( 'items' => $memories['items'] ) );
			?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $order['steps'] ) ) : ?>
	<section class="section" id="order">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $order['tag'] ?? '' ),
					'title' => (string) ( $order['title'] ?? '' ),
				)
			);
			View::component( 'process-steps/process-steps', array( 'items' => $order['steps'] ) );
			?>
			<?php
			$sc = (array) ( $showcases['order'] ?? array() );
			if ( ! empty( $sc ) ) {
				View::component(
					'showcase/showcase',
					array(
						'id'           => 'showcase-order',
						'carousel_tag' => (string) ( $sc['carousel_tag'] ?? '' ),
						'items'        => (array) ( $sc['items'] ?? array() ),
						'reviews_tag'  => (string) ( $sc['reviews_tag'] ?? '' ),
						'reviews'      => (array) ( $sc['reviews'] ?? array() ),
					)
				);
			}
			?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $events['items'] ) ) : ?>
	<section class="section section--alt" id="events">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $events['tag'] ?? '' ),
					'title' => (string) ( $events['title'] ?? '' ),
					'sub'   => (string) ( $events['body'] ?? '' ),
				)
			);
			?>
			<div class="feature-grid">
				<?php foreach ( (array) $events['items'] as $item ) :
					$item = (array) $item;
					View::component(
						'feature-card/feature-card',
						array(
							'icon'  => (string) ( $item['icon'] ?? '' ),
							'title' => (string) ( $item['title'] ?? '' ),
							'text'  => (string) ( $item['text'] ?? '' ),
						)
					);
				endforeach; ?>
			</div>
			<?php
			$sc = (array) ( $showcases['events'] ?? array() );
			if ( ! empty( $sc ) ) {
				View::component(
					'showcase/showcase',
					array(
						'id'           => 'showcase-events',
						'carousel_tag' => (string) ( $sc['carousel_tag'] ?? '' ),
						'items'        => (array) ( $sc['items'] ?? array() ),
						'reviews_tag'  => (string) ( $sc['reviews_tag'] ?? '' ),
						'reviews'      => (array) ( $sc['reviews'] ?? array() ),
					)
				);
			}
			?>
		</div>
	</section>
<?php endif; ?>

<?php
$fran_why = (array) ( $fran['why'] ?? array() );
if ( ! empty( $fran_why['items'] ) ) :
	?>
	<section class="section" id="franchise">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $fran_why['tag'] ?? '' ),
					'title' => (string) ( $fran_why['title'] ?? '' ),
					'sub'   => (string) ( $fran_why['sub'] ?? '' ),
				)
			);
			?>
			<div class="feature-grid">
				<?php foreach ( (array) $fran_why['items'] as $item ) :
					$item = (array) $item;
					View::component(
						'feature-card/feature-card',
						array(
							'icon'  => (string) ( $item['icon'] ?? '' ),
							'title' => (string) ( $item['title'] ?? $item['label'] ?? '' ),
							'text'  => (string) ( $item['text'] ?? $item['desc'] ?? $item['note'] ?? '' ),
						)
					);
				endforeach; ?>
			</div>
			<?php
			$sc = (array) ( $showcases['franchise'] ?? array() );
			if ( ! empty( $sc ) ) {
				View::component(
					'showcase/showcase',
					array(
						'id'           => 'showcase-franchise',
						'carousel_tag' => (string) ( $sc['carousel_tag'] ?? '' ),
						'items'        => (array) ( $sc['items'] ?? array() ),
						'reviews_tag'  => (string) ( $sc['reviews_tag'] ?? '' ),
						'reviews'      => (array) ( $sc['reviews'] ?? array() ),
					)
				);
			}
			?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $data['testimonials'] ) ) : ?>
	<section class="section section--alt" id="testimonials">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $testimonials_meta['tag'] ?? $data['testimonials_tag'] ?? '' ),
					'title' => (string) ( $testimonials_meta['title'] ?? $data['testimonials_title'] ?? '' ),
					'sub'   => (string) ( $testimonials_meta['sub'] ?? '' ),
				)
			);
			?>
			<div class="testimonial-grid">
				<?php foreach ( (array) $data['testimonials'] as $t ) :
					View::component( 'testimonial-card/testimonial-card', (array) $t );
				endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
$enquiry_cta = (array) ( $enquiry['cta'] ?? array() );
if ( ! empty( $enquiry['title'] ) ) :
	?>
	<section class="section section--ink" id="enquiry">
		<div class="container container--narrow enquiry-prompt">
			<h2 class="enquiry-prompt__title"><?php echo wp_kses_post( (string) $enquiry['title'] ); ?></h2>
			<?php if ( ! empty( $enquiry['body'] ) ) : ?>
				<p class="enquiry-prompt__body"><?php echo esc_html( (string) $enquiry['body'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $enquiry_cta['label'] ) && ! empty( $enquiry_cta['route'] ) ) : ?>
				<a class="btn btn--secondary btn--lg" href="<?php echo esc_url( RouteService::url( (string) $enquiry_cta['route'] ) ); ?>">
					<?php echo esc_html( (string) $enquiry_cta['label'] ); ?>
				</a>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $faqs['items'] ) ) : ?>
	<section class="section" id="faq">
		<div class="container container--narrow">
			<?php
			View::component(
				'faq/faq',
				array(
					'id'      => 'home-faq',
					'heading' => (string) ( $faqs['heading'] ?? '' ),
					'items'   => $faqs['items'],
				)
			);
			?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $contact ) ) : ?>
	<section class="section section--alt" id="connect">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array( 'title' => (string) ( $contact['title'] ?? '' ) )
			);
			?>
			<ul class="connect-list">
				<?php
				$rows = array(
					'address' => 'map-pin',
					'phone'   => 'phone',
					'email'   => 'mail',
					'website' => 'globe',
					'hours'   => 'clock',
				);
				foreach ( $rows as $key => $icon ) :
					$value = trim( (string) ( $contact[ $key ] ?? '' ) );
					if ( '' === $value ) {
						continue;
					}
					?>
					<li class="connect-list__item roughness-a">
						<span class="connect-list__icon connect-list__icon--<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
						<span class="connect-list__text"><?php echo esc_html( $value ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $closing['quote'] ) ) : ?>
	<section class="section section--sm closing-quote">
		<div class="container container--narrow">
			<p class="closing-quote__text"><?php echo esc_html( (string) $closing['quote'] ); ?></p>
			<?php if ( ! empty( $closing['attribution'] ) ) : ?>
				<p class="closing-quote__attr"><?php echo esc_html( (string) $closing['attribution'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>
