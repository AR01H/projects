<?php
/**
 * Memory / Polaroid section — home page.
 * "More Than A Drink. It's a Memory."
 * Renders four Polaroid-style photos from the equipment media gallery.
 */
defined( 'ABSPATH' ) || exit;

$_ss    = class_exists( 'CH_Data' ) ? CH_Data::story_settings() : [];
$body_1 = $_ss['body_1'] ?? 'The sound of the machine. The smell of fresh cane. The joy of that first sip on a hot summer day.';
$body_2 = "Some memories never fade. We're here to bring them back to life.";

$gallery  = ch_get_equipment_media_gallery();
$captions = [ 'Summer Days', 'Timeless Tradition', 'Simple Joys', 'Friends Forever' ];
$photos   = array_slice( (array) $gallery, 0, 4 );
?>

<section class="ch-memory-section fade-up" id="memory">
	<div class="container">
		<div class="ch-memory-inner">

			<div class="ch-memory-text fade-left">
				<p class="ch-memory-eyebrow">More Than A Drink.</p>
				<h2 class="ch-memory-heading">It's a <em>Memory.</em></h2>
				<span class="ch-ornament-heart">— ♥ —</span>
				<p class="ch-memory-body"><?php echo esc_html( $body_1 ); ?></p>
				<p class="ch-memory-body"><?php echo esc_html( $body_2 ); ?></p>
			</div>

			<div class="ch-memory-photos fade-right">
				<?php foreach ( $photos as $i => $item ) :
					$item = (array) $item;
					$src  = $item['src'] ?? $item['url'] ?? $item['image'] ?? '';
					$cap  = $captions[ $i ] ?? ( $item['label'] ?? '' );
					if ( ! $src ) continue;
				?>
					<div class="ch-polaroid ch-polaroid--<?php echo (int) ( $i + 1 ); ?>">
						<div class="ch-polaroid-mount">
							<img src="<?php echo esc_url( $src ); ?>"
								 alt="<?php echo esc_attr( $cap ); ?>"
								 loading="lazy">
						</div>
						<div class="ch-polaroid-caption"><?php echo esc_html( $cap ); ?></div>
					</div>
				<?php endforeach; ?>

				<?php if ( empty( $photos ) ) : ?>
					<div class="ch-memory-placeholder">
						<p class="ch-memory-quote"><em>Simple. Natural. Refreshing. Always.</em></p>
					</div>
				<?php endif; ?>
			</div>

		</div>
	</div>
</section>
