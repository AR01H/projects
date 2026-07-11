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

$badge   = isset( $card['badge'] )   && is_array( $card['badge'] ) ? $card['badge'] : array();
$icon    = isset( $card['icon'] )    ? (string) $card['icon']    : '';
$img     = isset( $card['img'] )     ? (string) $card['img']     : '';
$img_url = isset( $card['img_url'] ) ? (string) $card['img_url'] : '';
$overlay     = isset( $card['overlay'] )     ? (string) $card['overlay']     : '';
$tooltip     = isset( $card['tooltip'] )     ? (string) $card['tooltip']     : '';
$desc        = isset( $card['desc'] )        ? (string) $card['desc']        : '';
$thumb_label = isset( $card['thumb_label'] ) ? (string) $card['thumb_label'] : '';
$bg_image    = isset( $card['bg_image'] )    ? (string) $card['bg_image']    : '';

// Determine unified thumb modifier: photo > gradient > badge > icon
$_thumb_mod = '';
if ( '' !== $img_url )       { $_thumb_mod = ' mini-card-thumb--photo'; }
elseif ( '' !== $img )       { $_thumb_mod = ' mini-card-thumb--gradient'; }
elseif ( ! empty( $badge ) ) { $_thumb_mod = ' mini-card-thumb--badge'; }
elseif ( '' !== $icon )      { $_thumb_mod = ' mini-card-thumb--icon'; }
$_has_visual = '' !== $_thumb_mod;

$el      = $url ? 'a' : 'div';
$el_attr = $url
	? ' href="' . $url . '"' . ( '' !== $tooltip ? ' title="' . esc_attr( $tooltip ) . '"' : '' )
	: '';
?>
<<?php echo $el . $el_attr; ?> class="mini-card<?php echo ! $url ? ' mini-card--no-link' : ''; ?>">

	<?php /* ── Left visual: unified 16:9 thumb box ── */ ?>
	<?php if ( $_has_visual ) : ?>
	<div class="mini-card-thumb<?php echo $_thumb_mod; ?>"<?php echo ( '' !== $img && '' === $img_url ) ? ' style="background:' . esc_attr( $img ) . ';"' : ''; ?>>
		<?php if ( '' !== $img_url ) : ?>
			<img src="<?php echo esc_url( $img_url ); ?>" alt="" loading="lazy"
				onerror="this.style.display='none';this.nextElementSibling.removeAttribute('hidden');">
			<span class="mini-card-thumb-icon" aria-hidden="true" hidden><i class="fa-regular fa-image"></i></span>
			<?php if ( '' !== $overlay ) : ?>
				<span class="mini-card-thumb-overlay"><?php echo esc_html( $overlay ); ?></span>
			<?php endif; ?>
		<?php elseif ( ! empty( $badge ) ) : ?>
			<?php $_first = true; foreach ( $badge as $_line ) { if ( ! $_first ) { echo '<br>'; } echo esc_html( (string) $_line ); $_first = false; } ?>
		<?php elseif ( '' !== $icon ) : ?>
			<span class="mini-card-thumb-icon" aria-hidden="true"><?php echo adn_icon( $icon ); ?></span>
			<?php if ( '' !== $thumb_label ) : ?>
				<span class="mini-card-thumb-label"><?php echo esc_html( $thumb_label ); ?></span>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php /* ── Info indicator - outside thumb, left of body ── */ ?>
	<?php /* ── Body ── */ ?>
	<div class="mini-card-body">
		<?php if ( '' !== $title ) : ?>
			<span class="mini-card-title"><?php echo esc_html( $title ); ?></span>
		<?php endif; ?>
		<?php if ( '' !== $meta || '' !== $tag ) : ?>
			<div class="mini-card-meta">
				<?php if ( '' !== $tag ) : ?>
					<span class="mini-card-tag"><?php echo esc_html( $tag ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $meta ) : ?>
					<span class="mini-card-date"><?php echo esc_html( $meta ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( '' !== $desc ) : ?>
		<span class="mini-card-info-dot" title="<?php echo esc_attr( $desc ); ?>"><i class="fas fa-circle-info" aria-hidden="true"></i></span>
	<?php endif; ?>

	<?php if ( $url ) : ?>
		<span class="mini-card-arrow" aria-hidden="true">›</span>
	<?php endif; ?>

	<?php if ( '' !== $bg_image ) : ?>
		<div class="mini-card-bg-fade" style="background-image: url('<?php echo esc_url( $bg_image ); ?>');"></div>
	<?php endif; ?>

</<?php echo $el; ?>>
