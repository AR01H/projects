<?php
/**
 * components/cards/journey_card.php - Full-bleed overlay journey card
 * Props: $card { image, icon, gradient, title, description, link_label, url }
 *        $num  int  1-based index (unused)
 */

defined( 'ABSPATH' ) || exit;

$card       = isset( $card ) && is_array( $card ) ? $card : array();
$_image     = isset( $card['image'] )       ? $card['image']       : '';
$_icon      = isset( $card['icon'] )        ? $card['icon']        : '';
$_title     = isset( $card['title'] )       ? $card['title']       : '';
$_desc      = isset( $card['description'] ) ? $card['description'] : '';
$_label     = isset( $card['link_label'] )  ? $card['link_label']  : adn_term( 'buttons.explore_arrow', 'Explore' );
$_url       = isset( $card['url'] )         ? $card['url']         : '';

$_cta_label = trim( rtrim( trim( $_label ), '→>' ) );
if ( '' === $_cta_label ) {
	$_cta_label = 'Explore';
}

$_num       = isset( $num ) ? (int) $num : 0;
$_fallbacks = array(
	'linear-gradient(145deg,#0d1f1c 0%,#1a3d30 100%)',
	'linear-gradient(145deg,#0d1a2c 0%,#182e48 100%)',
	'linear-gradient(145deg,#1d1508 0%,#3a2b10 100%)',
	'linear-gradient(145deg,#18102a 0%,#2e1d45 100%)',
	'linear-gradient(145deg,#0d1c1c 0%,#1a3636 100%)',
);
$_bg = ( isset( $card['gradient'] ) && $card['gradient'] )
	? $card['gradient']
	: $_fallbacks[ ( $_num - 1 ) % count( $_fallbacks ) ];
?>
<?php
	$is_restricted = (isset($card['restrict']) && $card['restrict']);
	$card_classes = 'jny-card';
	if ( ! $_image ) { $card_classes .= ' jny-card--no-img'; }
	if ( $is_restricted ) { $card_classes .= ' jny-card--coming-soon'; }
?>
<a <?php if ( ! $is_restricted ) { ?> href="<?php echo esc_url( adn_link( $_url ) ); ?>" <?php } ?> class="<?php echo esc_attr( $card_classes ); ?>">

	<?php /* Full-bleed background */ ?>
	<div class="jny-card__bg"<?php if ( ! $_image ) : ?> style="background:<?php echo esc_attr( $_bg ); ?>;"<?php endif; ?>>
		<?php if ( $_image ) : ?>
		<img src="<?php echo esc_url( $_image ); ?>"
		     alt="<?php echo esc_attr( $_title ); ?>"
		     loading="lazy"
		     class="jny-card__img">
		<?php endif; ?>
	</div>

	<?php /* Dark gradient overlay */ ?>
	<div class="jny-card__overlay"></div>

	<?php /* Icon badge - top left */ ?>
	<?php if ( $_icon ) : ?>
	<div class="jny-card__icon" aria-hidden="true">
		<?php echo adn_icon( $_icon ); ?>
	</div>
	<?php endif; ?>

	<?php /* Content - bottom */ ?>
	<div class="jny-card__body">
		<h3 class="jny-card__title"><?php echo esc_html( $_title ); ?></h3>
		<div class="jny-card__divider"></div>
		<?php if ( $_desc ) : ?>
		<p class="jny-card__desc" title="<?= esc_attr($_desc)?>"><?php echo esc_html( $_desc ); ?></p>
		<?php endif; ?>
		<?php if ( $is_restricted ) {
			echo '<span class="jny-card__cta"> '.adn_icon('⚡').'Coming Soon</span>';
		}else{
			echo "<span class='jny-card__cta'>
				{$_cta_label}
				<svg viewBox='0 0 16 16' fill='none' xmlns='http://www.w3.org/2000/svg' width='13' height='13' aria-hidden='true'>
					<path d='M2 8h12M9 3l5 5-5 5' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/>
				</svg>
			</span>";		
		} ?>		
	</div>

</a>
