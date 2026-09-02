<?php
/**
 * Master Events & Live Cane Bar Catering Homepage Section
 * Structurally identical to Franchise Section with Dark Botanical Luxury Aesthetic
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Services\SettingsService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$events_data = (array) ( JsonFileProvider::read( 'data/content/events-features.json' ) ?? array() );

$tag   = (string) ( $tag ?? ( $events_data['tag'] ?? '' ) );
$title = (string) ( $title ?? ( $events_data['title'] ?? '' ) );
$sub   = (string) ( $sub ?? ( $events_data['sub'] ?? '' ) );

$gallery_ribbon = (string) ( $events_data['gallery_ribbon'] ?? '' );
$steps_ribbon   = (string) ( $events_data['steps_ribbon'] ?? '' );
$reviews_ribbon = (string) ( $events_data['reviews_ribbon'] ?? '' );

$events_pillars = (array) ( $pillars ?? ( $events_data['pillars'] ?? array() ) );
$events_gallery = (array) ( $gallery ?? ( $events_data['gallery'] ?? array() ) );
$events_steps   = (array) ( $steps ?? ( $events_data['steps'] ?? array() ) );
$events_reviews = (array) ( $reviews ?? ( $events_data['reviews'] ?? array() ) );
$cta_box        = (array) ( $events_data['cta'] ?? array() );
$cta_buttons    = (array) ( $cta_box['buttons'] ?? array() );
?>
<section class="section section--events events-vintage-block torn-dark-block grain-dark" id="events">
	<?php View::component( 'background/ambient-layer', array( 'variant' => 'dark', 'cane_positions' => array( 'top-right', 'bottom-left' ), 'bubble_count' => 12 ) ); ?>
	<div class="container events-vintage__container franchise-vintage__container">
		
		<!-- 1. Header -->
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'     => $tag,
				'title'   => $title,
				'sub'     => $sub,
				'variant' => 'dark',
				'ribbon'  => true,
			)
		);
		?>

		<!-- 2. 4 Event Service Pillars Grid -->
		<?php if ( ! empty( $events_pillars ) ) : ?>
			<div class="franchise-pillars-grid">
				<?php foreach ( $events_pillars as $pillar ) :
					$icon_svg = IconHelper::get( (string) ( $pillar['icon'] ?? 'stall' ), '#f6d599', 30 );
				?>
					<div class="franchise-pillar-card card--rough-cut-dark">
						<span class="franchise-pillar-card__icon"><?php echo $icon_svg; // phpcs:ignore ?></span>
						<h3 class="franchise-pillar-card__title"><?php echo esc_html( (string) ( $pillar['title'] ?? '' ) ); ?></h3>
						<p class="franchise-pillar-card__desc"><?php echo esc_html( (string) ( $pillar['desc'] ?? '' ) ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- 3. Our Live Events & Machinery Photo Gallery Stream (Left-to-Right) -->
		<?php if ( ! empty( $events_gallery ) ) : ?>
			<?php if ( '' !== $gallery_ribbon ) : ?>
				<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
					<span><?php echo esc_html( $gallery_ribbon ); ?></span>
				</div>
			<?php endif; ?>
			<?php
			View::component( 'card-stream/card-stream', array(
				'items'      => $events_gallery,
				'card_type'  => 'gallery',
				'direction'  => 'ltr',
				'aria_label' => $gallery_ribbon,
			) );
			?>
		<?php endif; ?>

		<!-- 4. Step-by-Step Booking Timeline -->
		<?php if ( ! empty( $events_steps ) ) : ?>
			<?php if ( '' !== $steps_ribbon ) : ?>
				<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
					<span><?php echo esc_html( $steps_ribbon ); ?></span>
				</div>
			<?php endif; ?>
			<div class="franchise-steps-grid">
				<?php foreach ( $events_steps as $st ) : ?>
					<div class="franchise-step-card card--rough-cut-dark">
						<span class="franchise-step-card__badge"><?php echo esc_html( (string) ( $st['num'] ?? '' ) ); ?></span>
						<h4 class="franchise-step-card__title"><?php echo esc_html( (string) ( $st['title'] ?? '' ) ); ?></h4>
						<p class="franchise-step-card__desc"><?php echo esc_html( (string) ( $st['desc'] ?? '' ) ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- 5. Client Event Experiences Stream (Right-to-Left) -->
		<?php if ( ! empty( $events_reviews ) ) : ?>
			<?php if ( '' !== $reviews_ribbon ) : ?>
				<div class="vintage-ribbon-tag">
					<span><?php echo esc_html( $reviews_ribbon ); ?></span>
				</div>
			<?php endif; ?>
			<?php
			View::component( 'card-stream/card-stream', array(
				'items'      => $events_reviews,
				'card_type'  => 'dark-review',
				'direction'  => 'rtl',
				'aria_label' => $reviews_ribbon,
			) );
			?>
		<?php endif; ?>

		<!-- 6. Event Setup Call to Action Box -->
		<?php if ( ! empty( $cta_box['title'] ) || ! empty( $cta_box['text'] ) ) : ?>
			<div class="franchise-cta-box card--rough-cut">
				<div class="franchise-cta-box__content">
					<?php if ( ! empty( $cta_box['title'] ) ) : ?>
						<h3 class="franchise-cta-box__title"><?php echo esc_html( (string) $cta_box['title'] ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $cta_box['text'] ) ) : ?>
						<p class="franchise-cta-box__text"><?php echo esc_html( (string) $cta_box['text'] ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $cta_buttons ) ) : ?>
					<div class="franchise-cta-box__actions">
						<?php foreach ( $cta_buttons as $c_btn ) :
							$btn_lbl   = (string) ( $c_btn['label'] ?? '' );
							$btn_route = (string) ( $c_btn['route'] ?? 'contact' );
							$btn_style = (string) ( $c_btn['style'] ?? 'primary' );
							$btn_icon  = (string) ( $c_btn['icon'] ?? '' );
							$btn_url   = 'whatsapp' === $btn_route
								? SettingsService::whatsapp_url()
								: ( 0 === strpos( $btn_route, '/' ) || 0 === strpos( $btn_route, 'http' ) ? $btn_route : RouteService::url( $btn_route ) );
							$btn_class = 'primary' === $btn_style
								? 'btn btn--primary-vintage'
								: ( 'secondary' === $btn_style ? 'btn btn--secondary-vintage' : 'btn btn--outline-vintage' );
						?>
							<a class="<?php echo esc_attr( $btn_class ); ?>" href="<?php echo esc_url( $btn_url ); ?>"<?php echo 'whatsapp' === $btn_route ? ' target="_blank" rel="noopener"' : ''; ?>>
								<?php if ( '' !== $btn_icon ) : ?>
									<span class="btn__icon"><?php echo IconHelper::render( $btn_icon, '#f6d599', 15 ); // phpcs:ignore ?></span>
								<?php endif; ?>
								<span><?php echo esc_html( $btn_lbl ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
