<?php
/**
 * Page header band. Context: 'title' (string, required),
 * 'subtitle' (string, optional).
 *
 *   nt_component( 'parts/page_header', array( 'title' => 'Contact' ) );
 */

defined( 'ABSPATH' ) || exit;

$title    = isset( $title ) ? (string) $title : get_the_title();
$subtitle = isset( $subtitle ) ? (string) $subtitle : '';
?>
<div class="nt-page-header">
	<h1 class="nt-page-title"><?php echo esc_html( $title ); ?></h1>
	<?php if ( '' !== $subtitle ) : ?>
		<p class="nt-page-subtitle"><?php echo esc_html( $subtitle ); ?></p>
	<?php endif; ?>
</div>
