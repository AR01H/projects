<?php

defined( 'ABSPATH' ) || exit;

$tag   = (string) ( $tag ?? 'Wellness & Vitality' );
$title = strip_tags( (string) ( $title ?? 'BENEFITS OF SUGARCANE' ) );
$sub   = (string) ( $sub ?? "Nature's Pure Botanical Elixir" );

$items = array(
	array(
		'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
		'title' => 'INSTANT NATURAL ENERGY',
		'text'  => 'Raw natural sucrose delivers a clean, revitalizing energy boost without sugar crashes or caffeine jitters.',
		'stat'  => '100% Raw',
	),
	array(
		'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
		'title' => 'IMMUNITY & ANTIOXIDANTS',
		'text'  => 'Packed with bioavailable flavonoids, phenolic compounds, and essential minerals to support vitality.',
		'stat'  => 'Antioxidants',
	),
	array(
		'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>',
		'title' => 'DEEP CELLULAR HYDRATION',
		'text'  => 'Packed with natural electrolytes to quench thirst and rapidly rehydrate the body naturally.',
		'stat'  => 'Electrolytes',
	),
	array(
		'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>',
		'title' => 'NO ADDED REFINED SUGAR',
		'text'  => 'Pure plant nectar straight from the stalk — zero processed syrups, additives, or preservatives.',
		'stat'  => 'Zero Syrups',
	),
	array(
		'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
		'title' => 'DIGESTION & GUT BALANCE',
		'text'  => 'Naturally high in potassium, soluble fiber, and active enzymes that support digestion.',
		'stat'  => 'Active Enzymes',
	),
	array(
		'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
		'title' => 'AYURVEDIC TRADITION',
		'text'  => 'Revered in ancient holistic traditions for its cooling restorative properties and natural cleansing benefits.',
		'stat'  => 'Heritage',
	),
);
?>
<section class="section section--benefits benefits-vintage paper-rough" id="benefits">
	<div class="container benefits-vintage__container">
		
		<!-- Header System -->
		<div class="benefits-vintage__header">
			<span class="vintage-ribbon-tag">
				<span><?php echo esc_html( $tag ); ?></span>
			</span>
			<h2 class="benefits-vintage__title"><?php echo esc_html( $title ); ?></h2>
			<p class="section-eyebrow"><?php echo esc_html( $sub ); ?></p>
		</div>

		<!-- 6-Benefit Wellness Grid (1 item per row on mobile, 2 col tablet, 3 col desktop) -->
		<div class="benefits-vintage__grid">
			<?php foreach ( $items as $item ) : ?>
				<div class="benefit-card card--paper-cut">
					<div class="benefit-card__icon-box">
						<?php echo $item['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="benefit-card__content">
						<div class="benefit-card__top">
							<h3 class="benefit-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
							<span class="benefit-card__badge"><?php echo esc_html( $item['stat'] ); ?></span>
						</div>
						<p class="benefit-card__text"><?php echo esc_html( $item['text'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
