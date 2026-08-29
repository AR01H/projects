<?php

defined( 'ABSPATH' ) || exit;

$tag   = (string) ( $tag ?? 'Local Roots' );
$title = (string) ( $title ?? 'PROUD TO BE PART OF SUTTON' );
$body  = (string) ( $body ?? 'Rooted in our local community, supporting local markets, and serving thousands of happy Londoners with authentic pure sugarcane tradition.' );

$items = array(
	array(
		'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/></svg>',
		'title' => 'Supporting Local Markets',
		'desc'  => 'Proudly partnering with local shops, weekend pop-ups, and food markets across Sutton.',
		'tag'   => 'Sutton High Street',
	),
	array(
		'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
		'title' => 'Loved by Our Community',
		'desc'  => 'Bringing refreshing botanical moments to thousands of local families and neighbours.',
		'tag'   => '10,000+ Happy Locals',
	),
	array(
		'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
		'title' => 'Bringing People Together',
		'desc'  => 'Connecting diverse communities across summer food fairs, weddings, and cultural festivals.',
		'tag'   => 'London Events & Fairs',
	),
	array(
		'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.45 1-1 1H7c-.55 0-1-.45-1-1v-2.34"/><path d="M14 14.66V17c0 .55.45 1 1 1h2c.55 0 1-.45 1-1v-2.34"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>',
		'title' => 'Proud Artisan Tradition',
		'desc'  => 'Keeping authentic, handcrafted cold-pressed sugarcane extraction alive in the UK since 2014.',
		'tag'   => 'Independent & Family Run',
	),
);
?>
<section class="section section--community community-vintage paper-rough" id="community">
	<div class="container community-vintage__container">
		
		<!-- Header System -->
		<div class="community-vintage__header">
			<span class="vintage-ribbon-tag">
				<span><?php echo esc_html( $tag ); ?></span>
			</span>
			<h2 class="community-vintage__title"><?php echo esc_html( trim( strip_tags( $title ), " -—" ) ); ?></h2>
			<p class="section-eyebrow">Our London Heritage &amp; Community</p>
			<?php if ( '' !== $body ) : ?>
				<p class="community-vintage__body"><?php echo esc_html( $body ); ?></p>
			<?php endif; ?>
		</div>

		<!-- Community Grid (1 row per card on mobile) -->
		<div class="community-vintage__grid">
			<?php foreach ( $items as $item ) : ?>
				<div class="community-card card--paper-cut">
					<div class="community-card__icon-box">
						<?php echo $item['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="community-card__content">
						<h3 class="community-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
						<p class="community-card__desc"><?php echo esc_html( $item['desc'] ); ?></p>
						<span class="community-card__tag">
							<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#caa06d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 3px;"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
							<?php echo esc_html( $item['tag'] ); ?>
						</span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
