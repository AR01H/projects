<?php
/**
 * "Why Choose Us" section for the events page.
 *
 * Args (all optional):
 *  tag    (string)  Eyebrow tag.            Default: 'Why Choose Us'
 *  title  (string)  Heading HTML.           Default: 'What Makes Us <span class="accent">Different</span>'
 *  body   (string)  Intro paragraph.        Default: preset copy
 *  image  (string)  Image URL.              Default: Unsplash placeholder
 *  items  (array)   Array of why-items, each: [ 'icon', 'title', 'text' ]
 */
defined( 'ABSPATH' ) || exit;

$_d    = CH_Hire_Data::events_why_settings();
$tag   = $args['tag']   ?? $_d['tag']   ?? '';
$title = $args['title'] ?? $_d['title'] ?? '';
$body  = $args['body']  ?? $_d['body']  ?? '';
$image = $args['image'] ?? $_d['image'] ?? '';

$items   = $args['items'] ?? [];
$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="ch-events-why-section">
	<div class="container">
		<div class="ch-why-grid">
			<div class="fade-left">
				<?php get_template_part( 'components/section-header', null, [
					'tag'        => $tag,
					'title'      => $title,
					'body'       => $body,
					'no_wrapper' => true,
				] ); ?>
				<div class="ch-why-list">
					<?php foreach ( $items as $item ) : ?>
						<div class="ch-why-item">
							<div class="ch-why-icon"><?php echo esc_html( $item['icon'] ?? '✓' ); ?></div>
							<div>
								<strong><?php echo esc_html( $item['title'] ?? '' ); ?></strong>
								<p><?php echo esc_html( $item['text'] ?? '' ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="ch-why-visual fade-right">
				<img src="<?php echo esc_url( $image ); ?>"
					alt="The Cane House at an event"
					loading="lazy"
					style="width:100%;height:100%;object-fit:cover;border-radius:20px;">
			</div>
		</div>
	</div>
</section>
