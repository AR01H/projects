<?php
/**
 * VintageSoulTheme - Four Pillars Section (Dark Botanical)
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\View;

$story_data = ! empty( $story ) && is_array( $story ) ? $story : (array) JsonFileProvider::read( 'data/content/story.json' );

$pillars_tag   = (string) ( $story_data['pillars_tag'] ?? '' );
$pillars_title = (string) ( $story_data['pillars_title'] ?? '' );
$pillars       = (array) ( $story_data['pillars'] ?? array() );

if ( empty( $pillars ) ) {
	return;
}
?>
<section class="section section--dark-botanical about-pillars-section">
	<div class="container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'     => $pillars_tag,
				'title'   => $pillars_title,
				'variant' => 'dark',
				'ribbon'  => true,
			)
		);
		?>

		<div class="history-grid-4">
			<?php foreach ( $pillars as $p ) :
				$p_icon  = (string) ( $p['icon'] ?? 'plant' );
				$p_label = (string) ( $p['label'] ?? '' );
				$p_note  = (string) ( $p['note'] ?? '' );
				$p_svg   = IconHelper::get( $p_icon, '#172b15', 24 );
				if ( empty( $p_svg ) ) {
					$p_svg = IconHelper::get( 'plant', '#172b15', 24 );
				}
			?>
				<div class="pillar-card frame--ornate-sm">
					<div class="pillar-card__icon"><?php echo $p_svg; // phpcs:ignore ?></div>
					<h3 class="pillar-card__label"><?php echo esc_html( $p_label ); ?></h3>
					<p class="pillar-card__note"><?php echo esc_html( $p_note ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
