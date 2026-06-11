<?php
/**
 * components/sections/article_author.php — Author box + last updated + disclaimer.
 *
 * Props: $author { avatar, written_by, name, role, last_updated, disclaimer }
 * Usage: adn_component( 'sections/article_author', array( 'author' => $ctx['author'] ) );
 */

defined( 'ABSPATH' ) || exit;

$author = isset( $author ) && is_array( $author ) ? $author : array();
?>
<div class="article-author-row">
	<div class="author-box">
		<div class="author-avatar"><?php echo esc_html( isset( $author['avatar'] ) ? $author['avatar'] : '' ); ?></div>
		<div class="author-info">
			<?php if ( ! empty( $author['written_by'] ) ) : ?>
				<div class="author-written-by"><?php echo esc_html( $author['written_by'] ); ?></div>
			<?php endif; ?>
			<div class="author-name"><?php echo esc_html( isset( $author['name'] ) ? $author['name'] : COMPANY_NAME ); ?></div>
			<?php if ( ! empty( $author['role'] ) ) : ?>
				<div class="author-role"><?php echo esc_html( $author['role'] ); ?></div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( ! empty( $author['last_updated'] ) ) : ?>
		<div class="author-last-updated">
			&#x1F4C5; <?php echo esc_html( $author['last_updated'] ); ?>
		</div>
	<?php endif; ?>
</div>

<?php if ( ! empty( $author['disclaimer'] ) ) : ?>
	<div class="disclaimer-box">
		<strong><?php esc_html_e( 'Disclaimer:', ADN_TEXT_DOMAIN ); ?></strong>
		<?php echo esc_html( $author['disclaimer'] ); ?>
	</div>
<?php endif; ?>
