<?php
/**
 * "Why Sugarcane Juice is Loved Worldwide" section.
 *
 * Args (all optional):
 *  tag    (string)  Eyebrow tag.    Default: 'Global Love'
 *  title  (string)  Heading HTML.   Default: preset
 *  body   (string)  Intro text.     Default: preset
 *  items  (array)   Benefit cards.  Default: preset list
 */
defined( 'ABSPATH' ) || exit;

$_d    = CH_Story_Data::sugarcane_benefits_settings();
$tag   = $args['tag']   ?? $_d['tag']   ?? '';
$title = $args['title'] ?? $_d['title'] ?? '';
$body  = $args['body']  ?? $_d['body']  ?? '';

$default_items = class_exists( 'CH_Story_Data' ) ? CH_Story_Data::sugarcane_benefits() : [];

$items   = $args['items'] ?? $default_items;
$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="ch-sc-benefits-section">
	<div class="container">
		<?php get_template_part( 'components/section-header', null, [
			'tag'   => $tag,
			'title' => $title,
			'body'  => $body,
		] ); ?>
		<div class="ch-scb-grid fade-up" id="ch-scb-track">
			<?php foreach ( $items as $item ) : ?>
				<div class="ch-scb-card">
					<div class="ch-scb-icon"><?php echo esc_html( $item['icon'] ?? '🌿' ); ?></div>
					<h3 class="ch-scb-title"><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
					<p class="ch-scb-text"><?php echo esc_html( $item['text'] ?? '' ); ?></p>
					<?php if ( ! empty( $item['stat'] ) ) : ?>
						<div class="ch-scb-stat"><?php echo esc_html( $item['stat'] ); ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="ch-scb-nav">
			<button class="ch-scb-btn" id="ch-scb-prev" aria-label="Previous">&#8592;</button>
			<span class="ch-scb-count" id="ch-scb-count">1 / <?php echo (int) count( $items ); ?></span>
			<button class="ch-scb-btn" id="ch-scb-next" aria-label="Next">&#8594;</button>
		</div>
	</div>
</section>
<script>
(function(){
	var track   = document.getElementById('ch-scb-track');
	var counter = document.getElementById('ch-scb-count');
	var total   = <?php echo (int) count( $items ); ?>;
	if ( !track ) return;
	var cards   = Array.from( track.querySelectorAll('.ch-scb-card') );
	var current = 0;

	function goTo( idx ) {
		idx = Math.max( 0, Math.min( total - 1, idx ) );
		current = idx;
		cards[ idx ].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
		if ( counter ) counter.textContent = (idx + 1) + ' / ' + total;
	}

	document.getElementById('ch-scb-prev').addEventListener('click', function(){ goTo( current - 1 ); });
	document.getElementById('ch-scb-next').addEventListener('click', function(){ goTo( current + 1 ); });

	var ticking = false;
	track.addEventListener('scroll', function(){
		if ( ticking ) return;
		ticking = true;
		requestAnimationFrame(function(){
			var cardW = cards[0] ? cards[0].offsetWidth + 22 : 1;
			var idx   = Math.round( track.scrollLeft / cardW );
			current   = Math.max( 0, Math.min( total - 1, idx ) );
			if ( counter ) counter.textContent = (current + 1) + ' / ' + total;
			ticking = false;
		});
	}, { passive: true });
})();
</script>
