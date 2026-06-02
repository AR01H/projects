<?php
defined( 'ABSPATH' ) || exit;

$tag   = $args['tag']   ?? 'The Opportunity';
$title = $args['title'] ?? 'Why <span class="accent">The Cane House?</span>';
$body  = $args['body']  ?? 'The demand for healthy, natural drinks is booming. The UK has almost no live-press sugarcane brand - yet. Be the first in your city.';

$default_items = [
	[ 'icon' => '📈', 'title' => 'Growing Market',       'text' => 'The natural drinks sector is growing 15%+ year-on-year. Live-press juice is untapped in most UK cities - massive first-mover advantage awaits.' ],
	[ 'icon' => '🏗️', 'title' => 'Full Setup Support',   'text' => 'We provide the equipment, training, branding, marketing templates, and supplier contacts. You focus on serving customers - we handle the rest.' ],
	[ 'icon' => '💰', 'title' => 'Strong Margins',        'text' => 'Low cost ingredients, high selling price. A single busy event can generate significant returns. Scalable from a single stall to multiple locations.' ],
	[ 'icon' => '🤝', 'title' => 'Ongoing Partnership',   'text' => 'We\'re not just a licensor - we\'re your business partner. Regular check-ins, marketing support, and a growing network of fellow franchise owners.' ],
	[ 'icon' => '🌿', 'title' => 'Ethical & Sustainable', 'text' => 'A product you can be genuinely proud of. Natural, sustainable, and culturally resonant with communities across the UK.' ],
	[ 'icon' => '⚡',  'title' => 'Quick to Launch',       'text' => 'Minimal setup time compared to traditional food franchise. Be ready to trade at events within weeks of joining the family.' ],
];

$items   = $args['items'] ?? $default_items;
$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="ch-franchise-why-section">
	<div class="container">
		<?php get_template_part( 'components/section-header', null, [
			'tag'   => $tag,
			'title' => $title,
			'body'  => $body,
		] ); ?>

		<div class="ch-fwhy-carousel fade-up">
			<div class="ch-fwhy-track" id="ch-fwhy-track">
				<?php foreach ( $items as $i => $item ) : ?>
					<div class="ch-fw-card<?php echo $i === 0 ? ' active' : ''; ?>">
						<div class="ch-fw-icon"><?php echo esc_html( $item['icon'] ?? '' ); ?></div>
						<h3><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
						<p><?php echo esc_html( $item['text'] ?? '' ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="ch-fwhy-nav">
				<div class="ch-fwhy-dots" id="ch-fwhy-dots" role="tablist" aria-label="Why franchise navigation">
					<?php foreach ( $items as $i => $_ ) : ?>
						<button class="ch-dot<?php echo $i === 0 ? ' active' : ''; ?>"
							role="tab" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
							aria-label="Reason <?php echo $i + 1; ?>"></button>
					<?php endforeach; ?>
				</div>
				<div class="ch-fwhy-arrows">
					<button class="ch-v-btn" id="ch-fwhy-prev" aria-label="Previous">←</button>
					<button class="ch-v-btn" id="ch-fwhy-next" aria-label="Next">→</button>
				</div>
			</div>
		</div>
	</div>
</section>
