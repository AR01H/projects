<?php
/**
 * Generic Dialog / Modal Component
 *
 * Args:
 *  id       (string) The ID of the dialog element (required to trigger it)
 *  title    (string) The title shown in the dialog header
 *  content  (string) HTML content for the body (often the generic form, passed as string or capture)
 *  class    (string) Extra CSS class for the dialog wrapper
 */
defined( 'ABSPATH' ) || exit;

$dialog_id = $args['id'] ?? 'nt-generic-dialog';
$title     = $args['title'] ?? '';
$content   = $args['content'] ?? '';
$class     = $args['class'] ?? '';
?>
<dialog id="<?php echo esc_attr( $dialog_id ); ?>" class="nt-dialog <?php echo esc_attr( $class ); ?>">
	<div class="nt-dialog-content card">
		<header class="nt-dialog-header">
			<?php if ( $title ) : ?>
				<h3 class="nt-dialog-title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<button class="nt-dialog-close" aria-label="Close dialog" onclick="document.getElementById('<?php echo esc_js( $dialog_id ); ?>').close();">&times;</button>
		</header>
		<div class="nt-dialog-body">
			<?php echo $content; ?>
		</div>
	</div>
</dialog>
