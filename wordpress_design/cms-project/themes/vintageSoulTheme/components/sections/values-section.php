<?php
/**
 * VintageSoulTheme - Core Values Section
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\View;

$values_data = ! empty( $values ) && is_array( $values ) ? $values : (array) JsonFileProvider::read( 'data/content/values.json' );

$values_tag   = (string) ( $values_data['sub'] ?? '' );
$values_title = (string) ( $values_data['title'] ?? '' );
$items        = (array) ( $values_data['items'] ?? array() );

if ( empty( $items ) ) {
	return;
}
?>
<section class="section about-values-section">
	<div class="container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'   => $values_tag,
				'title' => $values_title,
			)
		);
		?>

		<div class="about-values-grid">
			<?php foreach ( $items as $idx => $val_item ) :
				$v_num   = (string) ( $val_item['number'] ?? sprintf( '%02d', $idx + 1 ) );
				$v_title = (string) ( $val_item['title'] ?? '' );
				$v_text  = (string) ( $val_item['text'] ?? '' );
			?>
				<div class="value-card frame--rough-cut">
					<span class="value-card__badge"><?php echo esc_html( $v_num ); ?></span>
					<h3 class="value-card__title"><?php echo esc_html( $v_title ); ?></h3>
					<p class="value-card__text"><?php echo esc_html( $v_text ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
