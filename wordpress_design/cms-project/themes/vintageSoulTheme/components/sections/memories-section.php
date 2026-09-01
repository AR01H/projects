<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\View;

$memories_data = (array) ( JsonFileProvider::read( 'data/content/memories.json' ) ?? array() );

$tag   = (string) ( $tag ?? ( $memories_data['tag'] ?? 'Our Heritage Journey' ) );
$title = (string) ( $title ?? ( $memories_data['title'] ?? 'MEMORIES OF <em>Sugarcane</em>' ) );
$sub   = (string) ( $sub ?? ( $memories_data['sub'] ?? 'Treasured vintage snapshots capturing laughter, market stall mornings, and genuine moments of pure connection across generations.' ) );
$items        = (array) ( $items ?? ( $memories_data['items'] ?? array() ) );
$bg_watermark = (string) ( $bg_watermark ?? ( $memories_data['bg_watermark'] ?? '' ) );
?>
<section class="section section--memories memories-vintage paper-rough" id="memories">
	<?php if ( '' !== $bg_watermark ) : ?>
		<div class="section-cane-watermark" style="background-image: url('<?php echo esc_url( \VintageSoul\Support\UrlHelper::resolve( $bg_watermark ) ); ?>');" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="container memories-vintage__container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'     => $tag,
				'title'   => $title,
				'eyebrow' => 'Treasured Heritage Journey',
				'sub'     => $sub,
				'ribbon'  => true,
			)
		);
		?>

		<?php if ( ! empty( $items ) ) : ?>
			<?php
			View::component( 'card-stream/card-stream', array(
				'items'     => $items,
				'card_type' => 'memory',
				'direction' => 'ltr',
			) );
			?>
		<?php endif; ?>
	</div>
</section>
