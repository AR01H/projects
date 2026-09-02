<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\View;

$story_data = (array) ( JsonFileProvider::read( 'data/content/story.json' ) ?? array() );

$tag          = (string) ( $tag ?? ( $story_data['tag'] ?? 'Our Heritage' ) );
$heading_lead = (string) ( $title ?? ( $heading_lead ?? ( $story_data['title'] ?? 'OUR STORY' ) ) );
$subtitle     = (string) ( $subtitle ?? ( $sub ?? ( $story_data['sub'] ?? 'A Tradition With Deep Roots' ) ) );
$body_1       = (string) ( $body ?? ( $body_1 ?? ( $story_data['body'] ?? 'The Cane House brings that timeless tradition to Sutton and beyond. We source the finest sugarcane, press it fresh while you watch, and serve it with love.' ) ) );
$pillars      = (array) ( $pillars ?? ( $story_data['pillars'] ?? array() ) );
?>
<section class="section section--story story-vintage paper-rough" id="our-story">
	<div class="container story-vintage__container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'    => ! empty( $tag ) ? $tag : 'Our Roots',
				'title'  => 'TRADITIONAL <em>Handcrafted Story</em>',
				'sub'    => $body_1,
				'ribbon' => true,
			)
		);
		?>

		<div class="story-vintage__pillars-grid">
			<?php foreach ( $pillars as $pillar ) :
				$p_icon  = (string) ( $pillar['icon'] ?? 'leaf' );
				$p_label = (string) ( $pillar['label'] ?? '' );
				$p_note  = (string) ( $pillar['note'] ?? '' );
			?>
				<div class="pillar-card card--rough-cut">
					<div class="pillar-card__icon"><?php echo IconHelper::render( $p_icon, '#f6d599', 20 ); // phpcs:ignore ?></div>
					<h3 class="pillar-card__label"><?php echo esc_html( $p_label ); ?></h3>
					<p class="pillar-card__note"><?php echo esc_html( $p_note ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
