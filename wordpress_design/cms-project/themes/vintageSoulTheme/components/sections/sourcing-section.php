<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$sourcing_data = (array) ( JsonFileProvider::read( 'data/content/sourcing.json' ) ?? array() );

$tag        = (string) ( $tag ?? ( $sourcing_data['tag'] ?? 'Our Heritage Sourcing' ) );
$title      = (string) ( $title ?? ( $sourcing_data['title'] ?? 'ETHICAL <em>Farm-To-Press</em> SOURCING' ) );
$eyebrow    = (string) ( $eyebrow ?? ( $sourcing_data['eyebrow'] ?? 'From Farm To Cold Extraction' ) );
$body       = (string) ( $body ?? ( $sourcing_data['body'] ?? 'We hand-select only mature, sunshine-ripened sugarcane grown without chemical ripeners. Pressed the traditional way on cold brass rolls to preserve all natural vitamins, live enzymes, and authentic sweetness.' ) );
$image      = UrlHelper::resolve( (string) ( $image ?? ( $sourcing_data['image'] ?? 'assets/images/sugarcane/stacks.jpg' ) ) );
$sign_lines   = (array) ( $sign_lines ?? ( $sourcing_data['sign_lines'] ?? array( 'FRESH CANE', 'PREMIUM QUALITY', 'DAILY COLD PRESSED', '100% PURE & NATURAL' ) ) );
$pillars      = (array) ( $pillars ?? ( $sourcing_data['pillars'] ?? array() ) );
$bg_watermark = (string) ( $bg_watermark ?? ( $sourcing_data['bg_watermark'] ?? '' ) );
?>
<section class="section section--sourcing sourcing-vintage paper-rough" id="sourcing">
	<?php if ( '' !== $bg_watermark ) : ?>
		<div class="section-cane-watermark" style="background-image: url('<?php echo esc_url( UrlHelper::resolve( $bg_watermark ) ); ?>');" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="container sourcing-vintage__container">
		
		<!-- Section Header -->
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'     => $tag,
				'title'   => 'ETHICAL <em>Farm-To-Press</em> SOURCING',
				'eyebrow' => $eyebrow,
				'body'    => $body,
				'ribbon'  => true,
			)
		);
		?>

		<!-- 2-Column Traditional Layout -->
		<div class="sourcing-vintage__grid">
			
			<!-- Left: 3 Traditional Quality Pillar Cards -->
			<div class="sourcing-vintage__pillars">
				<?php foreach ( $pillars as $pillar ) : ?>
					<div class="sourcing-pillar-card frame--ornate">
						<div class="sourcing-pillar-card__icon-box">
							<span class="sourcing-pillar-card__num"><?php echo esc_html( $pillar['num'] ); ?></span>
							<span class="sourcing-pillar-card__icon"><?php echo IconHelper::render( (string) $pillar['icon'], '#f6d599', 18 ); // phpcs:ignore ?></span>
						</div>
						<div class="sourcing-pillar-card__content">
							<h3 class="sourcing-pillar-card__title"><?php echo esc_html( $pillar['title'] ); ?></h3>
							<p class="sourcing-pillar-card__desc"><?php echo esc_html( $pillar['desc'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>

				<!-- Action Buttons -->
				<div class="sourcing-vintage__actions">
					<a class="btn btn--primary-vintage btn--order-now" href="<?php echo esc_url( RouteService::url( 'history' ) ); ?>">
						<span class="btn__icon"><?php echo IconHelper::render( 'sugarcane', '#f6d599', 15 ); // phpcs:ignore ?></span>
						<span>ALL ABOUT CANE</span>
					</a>
					<a class="btn btn--secondary-vintage btn--outline-vintage" href="<?php echo esc_url( RouteService::url( 'contact' ) ); ?>">
						<span class="btn__icon"><?php echo IconHelper::render( 'pin', '#f6d599', 15 ); // phpcs:ignore ?></span>
						<span>FIND OUR STALL</span>
					</a>
				</div>
			</div>

			<!-- Right: Framed Sugarcane Stacks Photographic Showcase -->
			<div class="sourcing-vintage__media-wrap">
				<div class="sourcing-vintage__photo-frame frame--ornate">
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" class="sourcing-vintage__img hover-zoom">
					<div class="sourcing-vintage__stamp stamp-circle">
						<span class="stamp-circle__line1">EST. 2014</span>
						<span class="stamp-circle__line2">FARM FRESH</span>
						<span class="stamp-circle__line3">STACKS</span>
					</div>
				</div>

				<!-- Wooden Tradition Signboard Banner -->
				<div class="sourcing-signboard frame--ornate">
					<div class="sourcing-signboard__inner">
						<?php foreach ( $sign_lines as $idx => $line ) : ?>
							<?php if ( $idx > 0 ) : ?><span class="sourcing-signboard__dot">◆</span><?php endif; ?>
							<span class="sourcing-signboard__text"><?php echo esc_html( (string) $line ); ?></span>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

		</div>

	</div>
</section>
