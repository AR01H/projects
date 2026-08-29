<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;

$tag        = (string) ( $tag ?? 'Our Heritage Sourcing' );
$title      = (string) ( $title ?? 'STACKS OF SUGARCANE' );
$eyebrow    = (string) ( $eyebrow ?? 'From Farm To Cold Extraction' );
$body       = (string) ( $body ?? 'We hand-select only mature, sunshine-ripened sugarcane grown without chemical ripeners. Pressed the traditional way on cold brass rolls to preserve all natural vitamins, live enzymes, and authentic sweetness.' );
$image      = UrlHelper::resolve( (string) ( $image ?? 'assets/images/sugarcane/stacks.jpg' ) );
$sign_lines = (array) ( $sign_lines ?? array( 'FRESH CANE', 'PREMIUM QUALITY', 'DAILY COLD PRESSED', '100% PURE & NATURAL' ) );

$pillars    = array(
	array(
		'icon'  => '🌾',
		'num'   => '01',
		'title' => 'Hand-Selected Mature Stalks',
		'desc'  => 'Harvested at peak Brix sweetness from sustainable farms with thick juicy fibers.',
	),
	array(
		'icon'  => '⚙️',
		'num'   => '02',
		'title' => 'Traditional Cold Extraction',
		'desc'  => 'Slow, cold mechanical brass-roller pressing to keep live enzymes and alkaline minerals alive.',
	),
	array(
		'icon'  => '✨',
		'num'   => '03',
		'title' => '100% Pure — Zero Added Sugar',
		'desc'  => 'Unpasteurized, unadulterated raw nectar served naturally chilled with no water dilution.',
	),
);
?>
<section class="section section--sourcing sourcing-vintage paper-rough" id="sourcing">
	<div class="container sourcing-vintage__container">
		
		<!-- Section Header -->
		<div class="sourcing-vintage__header">
			<span class="vintage-ribbon-tag">
				<span><?php echo esc_html( $tag ); ?></span>
			</span>
			<h2 class="sourcing-vintage__title"><?php echo esc_html( trim( strip_tags( $title ), " -—" ) ); ?></h2>
			<p class="section-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<p class="sourcing-vintage__body"><?php echo esc_html( $body ); ?></p>
		</div>

		<!-- 2-Column Traditional Layout -->
		<div class="sourcing-vintage__grid">
			
			<!-- Left: 3 Traditional Quality Pillar Cards -->
			<div class="sourcing-vintage__pillars">
				<?php foreach ( $pillars as $pillar ) : ?>
					<div class="sourcing-pillar-card frame--ornate">
						<div class="sourcing-pillar-card__icon-box">
							<span class="sourcing-pillar-card__num"><?php echo esc_html( $pillar['num'] ); ?></span>
							<span class="sourcing-pillar-card__emoji"><?php echo esc_html( $pillar['icon'] ); ?></span>
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
						<span class="btn__icon">🌾</span>
						<span>ALL ABOUT CANE</span>
					</a>
					<a class="btn btn--secondary-vintage btn--outline-vintage" href="<?php echo esc_url( RouteService::url( 'contact' ) ); ?>">
						<span class="btn__icon">📍</span>
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
