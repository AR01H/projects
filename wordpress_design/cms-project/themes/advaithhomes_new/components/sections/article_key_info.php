<?php
/**
 * components/sections/article_key_info.php — Key takeaways + In-this-guide TOC panel.
 *
 * Props:
 *   $key_takeaways  { title, items[] }
 *   $toc            { title, items[] { num, label } }
 * Usage: adn_component( 'sections/article_key_info', array(
 *            'key_takeaways' => $ctx['key_takeaways'],
 *            'toc'           => $ctx['toc'],
 *        ) );
 */

defined( 'ABSPATH' ) || exit;

$key_takeaways = isset( $key_takeaways ) && is_array( $key_takeaways ) ? $key_takeaways : array();
$toc           = isset( $toc ) && is_array( $toc ) ? $toc : array();
$kt_items      = isset( $key_takeaways['items'] ) ? (array) $key_takeaways['items'] : array();
$toc_items     = isset( $toc['items'] ) ? (array) $toc['items'] : array();
?>
<div class="article-key-info-row">

	<?php if ( $kt_items ) : ?>
	<div class="key-takeaways">
		<?php if ( ! empty( $key_takeaways['title'] ) ) : ?>
			<div class="key-takeaways-title"><?php echo esc_html( $key_takeaways['title'] ); ?></div>
		<?php endif; ?>
		<div class="key-takeaways-grid">
			<?php foreach ( $kt_items as $item ) : ?>
				<div class="takeaway-item">
					<span class="takeaway-check">&#x2705;</span>
					<?php echo esc_html( $item ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( $toc_items ) : ?>
	<div class="article-in-guide">
		<?php if ( ! empty( $toc['title'] ) ) : ?>
			<div class="in-guide-title"><?php echo esc_html( $toc['title'] ); ?></div>
		<?php endif; ?>
		<div class="in-guide-list">
			<?php foreach ( $toc_items as $item ) : ?>
				<span><?php echo esc_html( isset( $item['num'] ) ? $item['num'] . '. ' : '' ); ?><?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?></span>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

</div>
