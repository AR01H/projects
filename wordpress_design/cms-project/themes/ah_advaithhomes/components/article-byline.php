<?php
/**
 * Article byline - E-E-A-T trust signals for single posts.
 * Shows: author (avatar + name, links to author archive), published date,
 * "Last updated" (only when meaningfully newer than published), and an
 * optional "Reviewed by" line driven by the _ah_reviewed_by post meta.
 */
defined( 'ABSPATH' ) || exit;

$pid       = get_the_ID();
$author_id = (int) get_post_field( 'post_author', $pid );
$author    = get_the_author_meta( 'display_name', $author_id );
$reviewer  = trim( (string) get_post_meta( $pid, '_ah_reviewed_by', true ) );

/* Author role/title: post meta override → author profile field → filterable default */
$role = trim( (string) get_post_meta( $pid, '_ah_author_role', true ) );
if ( '' === $role ) {
	$role = trim( (string) get_the_author_meta( 'ah_role', $author_id ) );
}
$role = apply_filters( 'ah_byline_role', $role, $pid, $author_id );

$pub_ts = (int) get_post_time( 'U', false, $pid );
$mod_ts = (int) get_post_modified_time( 'U', false, $pid );
$show_updated = ( $mod_ts - $pub_ts ) > DAY_IN_SECONDS;
?>
<div class="ah-byline">
	<div class="ah-byline__avatar"><?php echo get_avatar( $author_id, 80, '', $author ); ?></div>
	<div class="ah-byline__meta">
		<div class="ah-byline__line">
			<span class="ah-byline__by">By</span>
			<a class="ah-byline__author" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( $author ); ?></a>
			<?php if ( $role ) : ?>
				<span class="ah-byline__role"><?php echo esc_html( $role ); ?></span>
			<?php endif; ?>
			<?php if ( $reviewer ) : ?>
				<span class="ah-byline__sep" aria-hidden="true">·</span>
				<span class="ah-byline__reviewed">Reviewed by <strong><?php echo esc_html( $reviewer ); ?></strong></span>
			<?php endif; ?>
		</div>
		<div class="ah-byline__dates">
			<time datetime="<?php echo esc_attr( get_the_date( 'c', $pid ) ); ?>">Published <?php echo esc_html( get_the_date( '', $pid ) ); ?></time>
			<?php if ( $show_updated ) : ?>
				<span class="ah-byline__sep" aria-hidden="true">·</span>
				<time class="ah-byline__updated" datetime="<?php echo esc_attr( get_the_modified_date( 'c', $pid ) ); ?>">Last updated <?php echo esc_html( get_the_modified_date( '', $pid ) ); ?></time>
			<?php endif; ?>
		</div>
	</div>
</div>
