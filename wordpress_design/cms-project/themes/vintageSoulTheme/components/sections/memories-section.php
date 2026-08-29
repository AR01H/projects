<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\UrlHelper;

$title = (string) ( $title ?? 'Memories of Sugarcane' );
$items = (array) ( $items ?? array() );
?>
<section class="section section--memories memories-vintage paper-rough" id="memories">
	<div class="container memories-vintage__container">
		<div class="memories-vintage__header">
			<h2 class="memories-vintage__title"><?php echo esc_html( trim( strip_tags( $title ), " -—" ) ); ?></h2>
		</div>

		<div class="memories-vintage__grid">
			<?php foreach ( $items as $item ) :
				$caption = (string) ( $item['caption'] ?? '' );
				$img_raw = (string) ( $item['image'] ?? 'assets/images/sugarcane/story_moments.jpg' );
				$img     = UrlHelper::resolve( $img_raw );
			?>
				<div class="memory-card-vintage frame--ornate">
					<div class="memory-card-vintage__media">
						<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $caption ); ?>" loading="lazy">
					</div>
					<div class="memory-card-vintage__caption"><?php echo esc_html( $caption ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
