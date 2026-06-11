<?php
/**
 * components/parts/expert_sidebar.php
 * Props: $sidebar { need_help{}, popular_categories{}, how_it_works{} }
 */
defined( 'ABSPATH' ) || exit;

$_sb   = isset( $sidebar ) ? (array) $sidebar : array();
$_nh   = isset( $_sb['need_help'] )           ? (array) $_sb['need_help']           : array();
$_pc   = isset( $_sb['popular_categories'] )  ? (array) $_sb['popular_categories']  : array();
$_hiw  = isset( $_sb['how_it_works'] )        ? (array) $_sb['how_it_works']        : array();
?>
<aside class="expert-sidebar">

	<?php /* Need Help? CTA */ ?>
	<?php if ( ! empty( $_nh ) ) :
		$_nh_h   = esc_html( isset( $_nh['heading'] )      ? (string) $_nh['heading']      : 'Need help?' );
		$_nh_d   = esc_html( isset( $_nh['desc'] )         ? (string) $_nh['desc']         : '' );
		$_nh_btn = esc_html( isset( $_nh['button_label'] ) ? (string) $_nh['button_label'] : 'Get Guidance' );
		$_nh_url = esc_url( adn_link( isset( $_nh['button_url'] ) ? (string) $_nh['button_url'] : '#' ) );
	?>
	<div class="expert-sb-box expert-need-help">
		<h3><?php echo $_nh_h; ?></h3>
		<?php if ( '' !== $_nh_d ) : ?>
			<p><?php echo $_nh_d; ?></p>
		<?php endif; ?>
		<a href="<?php echo $_nh_url; ?>" class="btn btn-primary expert-nh-btn">
			<?php echo $_nh_btn; ?> →
		</a>
	</div>
	<?php endif; ?>

	<?php /* Popular Categories */ ?>
	<?php if ( ! empty( $_pc ) ) :
		$_pc_hdg   = esc_html( isset( $_pc['heading'] )       ? (string) $_pc['heading']       : 'Popular Categories' );
		$_pc_items = isset( $_pc['items'] ) ? (array) $_pc['items'] : array();
		$_pc_all   = esc_html( isset( $_pc['view_all_label'] ) ? (string) $_pc['view_all_label'] : 'View all categories →' );
	?>
	<div class="expert-sb-box">
		<h3><?php echo $_pc_hdg; ?></h3>
		<ul class="expert-cat-list">
			<?php foreach ( $_pc_items as $_ci ) :
				$_ci_icon = esc_html( isset( $_ci['icon'] )  ? (string) $_ci['icon']  : '' );
				$_ci_lbl  = esc_html( isset( $_ci['label'] ) ? (string) $_ci['label'] : '' );
				$_ci_dsc  = esc_html( isset( $_ci['desc'] )  ? (string) $_ci['desc']  : '' );
				$_ci_url  = esc_url( adn_link( isset( $_ci['url'] ) ? (string) $_ci['url'] : '#' ) );
			?>
				<li>
					<a href="<?php echo $_ci_url; ?>" class="expert-cat-link">
						<span class="ecl-icon" aria-hidden="true"><?php echo $_ci_icon; ?></span>
						<span class="ecl-text">
							<strong><?php echo $_ci_lbl; ?></strong>
							<span><?php echo $_ci_dsc; ?></span>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<a href="#" class="sidebar-view-all"><?php echo $_pc_all; ?></a>
	</div>
	<?php endif; ?>

	<?php /* How it works */ ?>
	<?php if ( ! empty( $_hiw ) ) :
		$_hiw_hdg   = esc_html( isset( $_hiw['heading'] ) ? (string) $_hiw['heading'] : 'How it works' );
		$_hiw_steps = isset( $_hiw['steps'] ) ? (array) $_hiw['steps'] : array();
	?>
	<div class="expert-sb-box">
		<h3><?php echo $_hiw_hdg; ?></h3>
		<ol class="expert-hiw-list">
			<?php foreach ( $_hiw_steps as $_step ) :
				$_sn  = esc_html( isset( $_step['number'] ) ? (string) $_step['number'] : '' );
				$_st  = esc_html( isset( $_step['title'] )  ? (string) $_step['title']  : '' );
				$_sd  = esc_html( isset( $_step['desc'] )   ? (string) $_step['desc']   : '' );
			?>
				<li class="expert-hiw-step">
					<span class="hiw-num" aria-hidden="true"><?php echo $_sn; ?></span>
					<div>
						<strong><?php echo $_st; ?></strong>
						<p><?php echo $_sd; ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
	<?php endif; ?>

</aside>
