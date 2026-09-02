<?php
/**
 * VintageSoulTheme - About Services & Core Offerings Section
 *
 * Renders the 4 core business offerings of The Cane House:
 * 1. Franchise Partnerships (/franchise)
 * 2. Events & Live Catering (/events)
 * 3. Fresh Juice Stall & Orders (/#our-drinks)
 * 4. Wholesale & Bulk Supply (/contact)
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\View;

$services_data = (array) ( JsonFileProvider::read( 'data/content/about-services.json' ) ?? array() );

$tag   = (string) ( $services_data['tag'] ?? 'What We Provide' );
$title = (string) ( $services_data['title'] ?? 'OUR SERVICES &amp; <em>Core Offerings</em>' );
$sub   = (string) ( $services_data['sub'] ?? '' );
$items = (array) ( $services_data['items'] ?? array() );

if ( empty( $items ) ) {
	return;
}
?>
<section class="section about-services-section" id="our-offerings">
	<div class="container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'   => $tag,
				'title' => $title,
				'sub'   => $sub,
			)
		);
		?>

		<div class="about-offerings-grid">
			<?php foreach ( $items as $srv ) :
				$srv_icon     = (string) ( $srv['icon'] ?? 'stall' );
				$srv_badge    = (string) ( $srv['badge'] ?? '' );
				$srv_title    = (string) ( $srv['title'] ?? '' );
				$srv_tagline  = (string) ( $srv['tagline'] ?? '' );
				$srv_desc     = (string) ( $srv['desc'] ?? '' );
				$srv_features = (array) ( $srv['features'] ?? array() );
				$srv_label    = (string) ( $srv['cta_label'] ?? 'LEARN MORE' );
				$srv_link     = (string) ( $srv['cta_link'] ?? 'contact' );
				$srv_url      = 0 === strpos( $srv_link, '/' ) || 0 === strpos( $srv_link, 'http' ) ? $srv_link : RouteService::url( $srv_link );
			?>
				<article class="about-offering-card frame--rough-cut">
					
					<!-- Card Header with Icon & Badge -->
					<div class="about-offering-card__header">
						<div class="about-offering-card__icon-box">
							<?php echo IconHelper::render( $srv_icon, '#f6d599', 26 ); // phpcs:ignore ?>
						</div>
						<?php if ( '' !== $srv_badge ) : ?>
							<span class="about-offering-card__badge">✦ <?php echo esc_html( $srv_badge ); ?></span>
						<?php endif; ?>
					</div>

					<!-- Card Headings -->
					<h3 class="about-offering-card__title"><?php echo esc_html( $srv_title ); ?></h3>
					<?php if ( '' !== $srv_tagline ) : ?>
						<p class="about-offering-card__tagline"><?php echo esc_html( $srv_tagline ); ?></p>
					<?php endif; ?>

					<!-- Description -->
					<p class="about-offering-card__desc"><?php echo esc_html( $srv_desc ); ?></p>

					<!-- Feature Highlights -->
					<?php if ( ! empty( $srv_features ) ) : ?>
						<ul class="about-offering-card__features">
							<?php foreach ( $srv_features as $feat ) : ?>
								<li><span class="chk">✓</span> <?php echo esc_html( (string) $feat ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<!-- Action CTA Button -->
					<div class="about-offering-card__cta">
						<a class="btn btn--primary-vintage btn--sm" href="<?php echo esc_url( $srv_url ); ?>">
							<span><?php echo esc_html( $srv_label ); ?> ↗</span>
						</a>
					</div>

				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
