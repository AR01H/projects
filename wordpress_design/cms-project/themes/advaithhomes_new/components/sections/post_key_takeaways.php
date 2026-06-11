<?php
/**
 * components/sections/post_key_takeaways.php
 *
 * Key takeaways box — rendered only when the post has takeaway points stored
 * in post meta _adn_key_takeaways (JSON array of strings).
 *
 * Props (via extract):
 *   $key_takeaways = string[]   array of plain-text takeaway items
 */

defined( 'ABSPATH' ) || exit;

$_items = ( isset( $key_takeaways ) && is_array( $key_takeaways ) ) ? $key_takeaways : array();

if ( empty( $_items ) ) {
	return;
}
?>
<div class="key-takeaways-box">

	<div class="takeaways-header">
		<span class="takeaways-icon" aria-hidden="true">💡</span>
		<h2><?php esc_html_e( 'Key Takeaways', ADN_TEXT_DOMAIN ); ?></h2>
	</div>

	<ul class="takeaways-grid" role="list">
		<?php foreach ( $_items as $_item ) : ?>
			<li class="takeaway-item">
				<span class="takeaway-check" aria-hidden="true">✓</span>
				<span><?php echo esc_html( (string) $_item ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>

</div>
