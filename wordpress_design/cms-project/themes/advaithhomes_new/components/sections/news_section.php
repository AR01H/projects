<?php
/**
 * components/sections/news_section.php - News section: heading + grid or list of items.
 *
 * Dispatches to cards/news_card (type=grid) or cards/news_list_item (type=list).
 *
 * Props: $section { type, heading, link_label, link_url, items[] }
 * Usage: adn_component( 'sections/news_section', array( 'section' => $sec ) );
 */

defined( 'ABSPATH' ) || exit;

$section = isset( $section ) && is_array( $section ) ? $section : array();

if ( empty( $section['items'] ) ) {
	return;
}

$type       = isset( $section['type'] )       ? (string) $section['type']       : 'list';
$heading    = isset( $section['heading'] )    ? (string) $section['heading']    : '';
$link_label = isset( $section['link_label'] ) ? (string) $section['link_label'] : '';
$link_url   = isset( $section['link_url'] )   ? (string) $section['link_url']   : '';
$items      = (array) $section['items'];
?>
<div class="news-section" data-section-type="<?php echo esc_attr( $type ); ?>">

	<?php if ( $heading || $link_label ) : ?>
		<div class="news-section-hd">
			<?php if ( $heading ) : ?>
				<h2 class="news-section-title"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( $link_label && $link_url ) : ?>
				<a href="<?php echo esc_url( adn_link( $link_url ) ); ?>" class="news-section-link">
					<?php echo esc_html( $link_label ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( 'grid' === $type ) : ?>
		<div class="news-cards-grid">
			<?php foreach ( $items as $item ) :
				adn_component( 'cards/news_card', array( 'item' => $item ) );
			endforeach; ?>
		</div>

	<?php else : ?>
		<div class="news-list">
			<?php foreach ( $items as $item ) :
				adn_component( 'cards/news_list_item', array( 'item' => $item ) );
			endforeach; ?>
		</div>
	<?php endif; ?>

</div>
