<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\View;

$tag          = (string) ( $tag ?? 'Our Heritage' );
$heading_lead = (string) ( $title ?? ( $heading_lead ?? 'OUR STORY' ) );
$subtitle     = (string) ( $subtitle ?? ( $sub ?? 'A Tradition With Deep Roots' ) );
$body_1       = (string) ( $body ?? ( $body_1 ?? 'The Cane House brings that timeless tradition to Sutton and beyond. We source the finest sugarcane, press it fresh while you watch, and serve it with love.' ) );
$pillars      = (array) ( $pillars ?? array(
	array( 'icon' => '🌱', 'label' => 'TRADITION', 'note' => 'Handcrafted method passed down' ),
	array( 'icon' => '✦', 'label' => 'QUALITY', 'note' => 'Freshly harvested cane' ),
	array( 'icon' => '🤝', 'label' => 'COMMUNITY', 'note' => 'Bringing people together' ),
	array( 'icon' => '❤️', 'label' => 'AUTHENTIC', 'note' => 'Zero artificial additives' ),
) );
?>
<section class="section section--story story-vintage paper-rough" id="our-story">
	<div class="container story-vintage__container">
		<div class="story-vintage__header">
			<h2 class="story-vintage__title"><?php echo esc_html( $heading_lead ); ?></h2>
			<p class="story-vintage__sub"><?php echo esc_html( $subtitle ); ?></p>
			<p class="story-vintage__text"><?php echo esc_html( $body_1 ); ?></p>
		</div>

		<div class="story-vintage__pillars-grid">
			<?php foreach ( $pillars as $pillar ) :
				$p_icon  = (string) ( $pillar['icon'] ?? '🌱' );
				$p_label = (string) ( $pillar['label'] ?? '' );
				$p_note  = (string) ( $pillar['note'] ?? '' );
			?>
				<div class="pillar-card card--rough-cut">
					<div class="pillar-card__icon"><?php echo esc_html( $p_icon ); ?></div>
					<h3 class="pillar-card__label"><?php echo esc_html( $p_label ); ?></h3>
					<p class="pillar-card__note"><?php echo esc_html( $p_note ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
