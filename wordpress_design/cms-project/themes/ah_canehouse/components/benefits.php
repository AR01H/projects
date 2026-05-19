<?php
defined( 'ABSPATH' ) || exit;
$benefits = ch_get_benefits();
?>

<section id="benefits" class="ch-benefits-section">
	<div class="ch-benefits-inner">
		<div class="fade-left">
			<div class="ch-section-tag">Good for You</div>
			<h2 class="ch-section-title">Why Sugarcane Juice is <span class="accent" style="color:var(--ch-lime);">Loved Worldwide</span></h2>
			<p class="ch-section-body">Fresh sugarcane juice is not just delicious - it's packed with natural benefits rooted in 2,000 years of Ayurvedic and South Asian wellness tradition.</p>
			<div class="ch-benefits-list">
				<?php foreach ( $benefits as $b ) :
					$b = (array) $b;
				?>
					<div class="ch-benefit-item">
						<div class="ch-benefit-icon" aria-hidden="true"><?php echo esc_html( $b['icon'] ?? '🌿' ); ?></div>
						<div>
							<div class="ch-b-title"><?php echo esc_html( $b['title'] ?? '' ); ?></div>
							<div class="ch-b-desc"><?php echo esc_html( $b['desc'] ?? '' ); ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="ch-benefits-visual fade-right" aria-hidden="true">
			<div class="ch-promise-card">
				<span class="ch-promise-icon">🌱</span>
				<div class="ch-promise-title">Our Promise</div>
				<div class="ch-promise-sub">Pressed Fresh. Served Cool.</div>
				<div class="ch-promise-tags">
					<div class="ch-promise-tag">No added sugar</div>
					<div class="ch-promise-tag">No preservatives</div>
					<div class="ch-promise-tag">Pure, natural refreshment</div>
					<div class="ch-promise-tag">Pressed live at every order</div>
					<div class="ch-promise-tag">Served chilled, always fresh</div>
					<div class="ch-promise-tag">Rooted in Ayurvedic tradition</div>
				</div>
				<p class="ch-promise-foot">Sugarcane has been cherished for over 2,000 years across the Indian subcontinent. Even the leftover fibre (bagasse) is biodegradable - a truly sustainable crop.</p>
			</div>
		</div>
	</div>
</section>
