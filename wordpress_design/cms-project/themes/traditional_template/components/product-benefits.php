<?php
/**
 * "Why Our Product is Loved Worldwide" section.
 *
 * Args (all optional):
 *  tag    (string)  Eyebrow tag.    Default: 'Global Love'
 *  title  (string)  Heading HTML.   Default: preset
 *  body   (string)  Intro text.     Default: preset
 *  items  (array)   Benefit cards.  Default: preset list
 */
defined( 'ABSPATH' ) || exit;

$content = nt_data( 'content' )['product_benefits'] ?? [];
$tag   = $args['tag']   ?? $content['tag']   ?? 'WHY US';
$title = $args['title'] ?? $content['heading'] ?? 'The Benefits';
$body  = $args['body']  ?? $content['body']  ?? '';

$items   = $args['items'] ?? nt_data( 'benefits_items' ) ?? [];
$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="nt-benefits-section section">
	<div class="container wrapper">
		<?php get_template_part( 'components/parts/section-header', null, [
			'tag'   => $tag,
			'title' => $title,
			'body'  => $body,
		] ); ?>
		<div class="nt-ben-grid grid fade-up" id="nt-ben-track">
			<?php foreach ( $items as $item ) : ?>
				<div class="nt-ben-card card">
					<div class="nt-ben-icon"><?php echo esc_html( $item['icon'] ?? '🌿' ); ?></div>
					<h3 class="nt-ben-title"><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
					<p class="nt-ben-text"><?php echo esc_html( $item['text'] ?? '' ); ?></p>
					<?php if ( ! empty( $item['stat'] ) ) : ?>
						<div class="nt-ben-stat"><?php echo esc_html( $item['stat'] ); ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="nt-ben-nav">
			<button class="nt-ben-btn button" id="nt-ben-prev" aria-label="Previous">&#8592;</button>
			<span class="nt-ben-count" id="nt-ben-count">1 / <?php echo (int) count( $items ); ?></span>
			<button class="nt-ben-btn button" id="nt-ben-next" aria-label="Next">&#8594;</button>
		</div>
	</div>
</section>
<script>
(function(){
	var track   = document.getElementById('nt-ben-track');
	var counter = document.getElementById('nt-ben-count');
	var total   = <?php echo (int) count( $items ); ?>;
	if ( !track ) return;
	var cards   = Array.from( track.querySelectorAll('.nt-ben-card') );
	var current = 0;

	function goTo( idx ) {
		idx = Math.max( 0, Math.min( total - 1, idx ) );
		current = idx;
		cards[ idx ].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
		if ( counter ) counter.textContent = (idx + 1) + ' / ' + total;
	}

	if (document.getElementById('nt-ben-prev')) {
		document.getElementById('nt-ben-prev').addEventListener('click', function(){ goTo( current - 1 ); });
	}
	if (document.getElementById('nt-ben-next')) {
		document.getElementById('nt-ben-next').addEventListener('click', function(){ goTo( current + 1 ); });
	}

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
