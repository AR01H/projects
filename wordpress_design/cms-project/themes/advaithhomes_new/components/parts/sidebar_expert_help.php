<?php
/**
 * components/parts/sidebar_expert_help.php - Sidebar: Expert Help CTA panel.
 *
 * Props: $expert_help { heading, subtitle, experts[], cta { label, url } }
 */

defined( 'ABSPATH' ) || exit;

$expert_help = isset( $expert_help ) && is_array( $expert_help ) ? $expert_help : array();
$experts     = isset( $expert_help['experts'] ) ? (array) $expert_help['experts'] : array();
$cta         = isset( $expert_help['cta'] )     ? (array) $expert_help['cta']     : array();

if ( empty( $cta['label'] ) && empty( $expert_help['heading'] ) ) { return; }

$_heading  = ! empty( $expert_help['heading'] )  ? $expert_help['heading']  : adn_term( 'sidebar.expert_help_heading', 'Need Expert Help?' );
$_subtitle = ! empty( $expert_help['subtitle'] ) ? $expert_help['subtitle'] : adn_term( 'sidebar.expert_help_subtitle', '' );
$_btn_label = ! empty( $cta['label'] ) ? $cta['label'] : adn_term( 'sidebar.expert_help_cta', 'Talk to an Expert' );
$_btn_url   = ! empty( $cta['url'] )   ? $cta['url']   : home_url( SITE_CONTACT_URL );
?>
<div class="expert-help-card">
	<div class="expert-help-card__icon">
		<i class="fa-solid fa-headset" aria-hidden="true"></i>
	</div>
	<h3 class="expert-help-card__title"><?php echo esc_html( $_heading ); ?></h3>
	<p class="expert-help-card__desc"><?php echo esc_html( $_subtitle ); ?></p>

	<?php if ( ! empty( $experts ) ) : ?>
		<div class="expert-help-card__experts">
			<?php foreach ( array_slice( $experts, 0, 3 ) as $expert ) : ?>
				<a href="<?php echo esc_url( adn_link( isset( $expert['url'] ) ? $expert['url'] : '' ) ); ?>" class="expert-help-card__expert" title="<?php echo esc_attr( isset( $expert['name'] ) ? $expert['name'] : '' ); ?>">
					<?php if ( ! empty( $expert['avatar'] ) ) : ?>
						<img src="<?php echo esc_url( $expert['avatar'] ); ?>" alt="<?php echo esc_attr( isset( $expert['name'] ) ? $expert['name'] : '' ); ?>" class="expert-help-card__avatar">
					<?php else : ?>
						<span class="expert-help-card__avatar expert-help-card__avatar--placeholder"><?php echo esc_html( mb_substr( isset( $expert['name'] ) ? $expert['name'] : '?', 0, 1 ) ); ?></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<a href="<?php echo esc_url( $_btn_url ); ?>" class="expert-help-card__btn">
		<?php echo esc_html( $_btn_label ); ?>
		<i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
	</a>
</div>
