<?php
/**
 * components/sections/post_author.php
 *
 * Author box - avatar icon, author name, role, last updated date.
 *
 * Props (via extract):
 *   $author = [
 *       'name'         => string,
 *       'role'         => string,
 *       'last_updated' => string,   formatted date
 *   ]
 */

defined( 'ABSPATH' ) || exit;

$_author  = isset( $author ) ? (array) $author : array();
$_name    = esc_html( isset( $_author['name'] )         ? (string) $_author['name']         : '' );
$_role    = esc_html( isset( $_author['role'] )         ? (string) $_author['role']         : '' );
$_updated = esc_html( isset( $_author['last_updated'] ) ? (string) $_author['last_updated'] : '' );
?>
<div class="author-box">

	<div class="author-avatar" aria-hidden="true">🏠</div>

	<div class="author-info">
		<span class="author-label"><?php esc_html_e( 'Written by', ADN_TEXT_DOMAIN ); ?></span>
		<?php if ( '' !== $_name ) : ?>
			<strong class="author-name"><?php echo $_name; ?></strong>
		<?php endif; ?>
		<?php if ( '' !== $_role ) : ?>
			<span class="author-role"><?php echo $_role; ?></span>
		<?php endif; ?>
	</div>

	<?php if ( '' !== $_updated ) : ?>
	<div class="author-updated">
		<span aria-hidden="true">📅</span>
		<div>
			<span class="author-label"><?php esc_html_e( 'Last updated', ADN_TEXT_DOMAIN ); ?></span>
			<strong><?php echo $_updated; ?></strong>
		</div>
	</div>
	<?php endif; ?>

</div>
