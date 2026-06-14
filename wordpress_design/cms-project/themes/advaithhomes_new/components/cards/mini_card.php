<?php
/**
 * components/cards/mini_card.php - Unified mini list-row card.
 *
 * Single component that replaces news_item, hot_topic_item, and regulation_item.
 * The left visual is determined by whichever prop is provided (badge > icon > img).
 *
 * Props via $card array:
 *   badge   string[]  - text lines stacked in a green gov-style badge block
 *   icon    string    - emoji or FA icon class shown in a coloured circle
 *   img     string    - CSS gradient / colour string for a rectangle thumbnail
 *   title   string    - primary text (required)
 *   meta    string    - secondary line: date, read-time, short desc, etc.
 *   tag     string    - optional pill chip shown beside meta
 *   url     string    - href; renders whole card as <a> when set
 *
 * Usage:
 *   adn_component( 'cards/mini_card', array( 'card' => array(
 *       'icon'  => '🏠',
 *       'title' => 'First-Time Buyer Guide',
 *       'meta'  => 'Jan 12, 2026',
 *       'url'   => '/guides/first-time-buyer/',
 *   ) ) );
 */

defined( 'ABSPATH' ) || exit;

$card  = isset( $card ) && is_array( $card ) ? $card : array();
$title = isset( $card['title'] ) ? (string) $card['title'] : '';
$meta  = isset( $card['meta'] )  ? (string) $card['meta']  : '';
$tag   = isset( $card['tag'] )   ? (string) $card['tag']   : '';
$url   = ! empty( $card['url'] ) && '#' !== (string) $card['url']
	? esc_url( adn_link( (string) $card['url'] ) )
	: '';

$badge = isset( $card['badge'] ) && is_array( $card['badge'] ) ? $card['badge'] : array();
$icon  = isset( $card['icon'] )  ? (string) $card['icon'] : '';
$img   = isset( $card['img'] )   ? (string) $card['img']  : '';

$el      = $url ? 'a' : 'div';
$el_attr = $url ? ' href="' . $url . '"' : '';
?>
<<?php echo $el . $el_attr; ?> class="mini-card<?php echo ! $url ? ' mini-card--no-link' : ''; ?>">

	<?php /* ── Left visual: badge wins, then icon, then img ── */ ?>
	<?php if ( ! empty( $badge ) ) : ?>
		<div class="mini-card-badge"><?php
			$_first = true;
			foreach ( $badge as $_line ) {
				if ( ! $_first ) { echo '<br>'; }
				echo esc_html( (string) $_line );
				$_first = false;
			}
		?></div>
	<?php elseif ( '' !== $icon ) : ?>
		<span class="mini-card-icon" aria-hidden="true"><?php echo adn_icon( $icon ); ?></span>
	<?php elseif ( '' !== $img ) : ?>
		<div class="mini-card-img" style="background:<?php echo esc_attr( $img ); ?>;"></div>
	<?php endif; ?>

	<?php /* ── Body ── */ ?>
	<div class="mini-card-body">
		<?php if ( '' !== $title ) : ?>
			<span class="mini-card-title"><?php echo esc_html( $title ); ?></span>
		<?php endif; ?>
		<?php if ( '' !== $meta || '' !== $tag ) : ?>
			<div class="mini-card-meta">
				<?php if ( '' !== $meta ) : ?>
					<span class="mini-card-meta-text"><?php echo esc_html( $meta ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $tag ) : ?>
					<span class="mini-card-tag"><?php echo esc_html( $tag ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $url ) : ?>
		<span class="mini-card-arrow" aria-hidden="true">›</span>
	<?php endif; ?>

</<?php echo $el; ?>>
