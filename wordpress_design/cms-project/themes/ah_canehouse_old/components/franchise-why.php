<?php
defined( 'ABSPATH' ) || exit;

$_d    = CH_About_Data::franchise_why_settings();
$tag   = $args['tag']   ?? $_d['tag']   ?? '';
$title = $args['title'] ?? $_d['title'] ?? '';
$body  = $args['body']  ?? $_d['body']  ?? '';

// Load franchise why items from data class
$items = CH_About_Data::franchise_why_items();

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
