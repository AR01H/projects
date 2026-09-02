<?php
/**
 * VintageSoulTheme - About Services Section
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\View;

$intro_data = ! empty( $intro ) && is_array( $intro ) ? $intro : (array) JsonFileProvider::read( 'data/content/about-intro.json' );

$services_tag   = (string) ( $intro_data['services_tag'] ?? '' );
$services_title = (string) ( $intro_data['services_title'] ?? '' );
$services       = (array) ( $intro_data['services'] ?? array() );

if ( empty( $services ) ) {
	return;
}
?>
<section class="section about-services-section">
	<div class="container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'   => $services_tag,
				'title' => $services_title,
			)
		);
		?>

		<div class="about-services-grid">
			<?php foreach ( $services as $srv ) :
				$srv_icon  = (string) ( $srv['icon'] ?? 'stall' );
				$srv_title = (string) ( $srv['title'] ?? '' );
				$srv_desc  = (string) ( $srv['desc'] ?? '' );
				$srv_lbl   = (string) ( $srv['btn_label'] ?? '' );
				$srv_link  = (string) ( $srv['btn_link'] ?? 'contact' );
				$srv_url   = 0 === strpos( $srv_link, '/' ) || 0 === strpos( $srv_link, 'http' ) ? $srv_link : RouteService::url( $srv_link );
			?>
				<div class="service-card frame--rough-cut">
					<div class="service-card__icon-wrap">
						<?php echo IconHelper::get( $srv_icon, '#f3d49d', 28 ); // phpcs:ignore ?>
					</div>
					<h3 class="service-card__title"><?php echo esc_html( $srv_title ); ?></h3>
					<p class="service-card__desc"><?php echo esc_html( $srv_desc ); ?></p>
					<?php if ( '' !== $srv_lbl ) : ?>
						<a class="btn btn--primary-vintage btn--sm" href="<?php echo esc_url( $srv_url ); ?>"><?php echo esc_html( $srv_lbl ); ?></a>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
