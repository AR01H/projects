<?php
defined( 'ABSPATH' ) || exit;

$origin = $args['origin'];
$milestones = $args['milestones'] ?? [];

$tag   = $origin['tag']  ?? '';
$title = $origin['title'] ?? '';
$image = $origin['image'] ?? '';
$paras      = $origin['paras'] ?? [];

// Pre-render the timeline so it can be passed as after_html.
ob_start();
?>
<div class="ch-origin-timeline fade-up">
	<?php foreach ( $milestones as $ms ) : ?>
		<div class="ch-timeline-item">
			<div class="ch-timeline-year"><?php echo esc_html( $ms['year'] ?? '' ); ?></div>
			<div class="ch-timeline-dot"></div>
			<div class="ch-timeline-text"><?php echo esc_html( $ms['text'] ?? '' ); ?></div>
		</div>
	<?php endforeach; ?>
</div>
<?php
$timeline_html = ob_get_clean();

get_template_part( 'components/image-text-split', null, [
	'layout'        => 'image-right',
	'section_class' => 'ch-about-origin-section',
	'inner_class'   => 'ch-origin-grid',
	'tag'           => $tag,
	'title'         => $title,
	'body'          => $paras,
	'image_url'     => $image,
	'image_alt'     => 'The Cane House origins',
	'image_class'   => 'ch-origin-img',
	'content_anim'  => 'fade-left',
	'visual_anim'   => 'fade-right',
	'after_html'    => $timeline_html,
] );
