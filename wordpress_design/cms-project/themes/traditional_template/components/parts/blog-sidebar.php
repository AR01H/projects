<?php
/**
 * components/parts/blog-sidebar.php - the blog's side column.
 *
 * GENERIC and switch-driven: every block is turned on or off in
 * admin/data/blog.json -> "sidebar", so a site that wants nothing but
 * categories gets exactly that with no template edit.
 *
 *   "sidebar": {
 *     "search": true,
 *     "categories": { "show": true, "heading": "Browse by" },
 *     "recent":     { "show": true, "heading": "Latest", "count": 4 },
 *     "tags":       { "show": true, "heading": "Topics" },
 *     "blocks":     ["get_brochure", "newsletter"]   // from blocks.json
 *   }
 *
 * The `blocks` list pulls from the SHARED message library, so the "Read the
 * blog" style promos in the sidebar are the same entries used elsewhere on
 * the site and only ever get edited once.
 *
 * Context: config (array) - the "sidebar" block from blog.json.
 */

defined( 'ABSPATH' ) || exit;

$nt_side = isset( $config ) && is_array( $config ) ? $config : array();
if ( empty( $nt_side ) ) {
	return;
}

/** Read a sidebar block's settings whether it is `true` or an object. */
$nt_block_cfg = static function ( $key ) use ( $nt_side ) {
	$value = $nt_side[ $key ] ?? false;
	if ( true === $value ) {
		return array( 'show' => true );
	}
	return is_array( $value ) ? $value : array( 'show' => false );
};
?>

<?php $nt_search = $nt_block_cfg( 'search' ); ?>
<?php if ( ! empty( $nt_search['show'] ) ) : ?>
	<div class="app-side-block app-side-block--search">
		<form class="app-side-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="app-blog-search"><?php echo esc_html( NT_Ui::label( 'search' ) ); ?></label>
			<input id="app-blog-search" class="app-form-input" type="search" name="s"
			       placeholder="<?php echo esc_attr( $nt_search['placeholder'] ?? NT_Ui::label( 'search' ) ); ?>"
			       value="<?php echo esc_attr( get_search_query() ); ?>">
			<button type="submit" class="app-side-search__go" aria-label="<?php echo esc_attr( NT_Ui::label( 'search' ) ); ?>">
				<?php NT_Icons::render( 'search' ); ?>
			</button>
		</form>
	</div>
<?php endif; ?>

<?php
$nt_cats = $nt_block_cfg( 'categories' );
if ( ! empty( $nt_cats['show'] ) ) :
	$nt_terms = get_categories( array( 'hide_empty' => true ) );
	if ( ! empty( $nt_terms ) && ! is_wp_error( $nt_terms ) ) :
		?>
		<div class="app-side-block">
			<?php if ( ! empty( $nt_cats['heading'] ) ) : ?>
				<h3 class="app-side-block__title"><?php echo esc_html( $nt_cats['heading'] ); ?></h3>
			<?php endif; ?>
			<ul class="app-side-list">
				<?php foreach ( $nt_terms as $nt_term ) : ?>
					<li>
						<a href="<?php echo esc_url( get_category_link( $nt_term->term_id ) ); ?>">
							<span><?php echo esc_html( $nt_term->name ); ?></span>
							<span class="app-side-list__count"><?php echo esc_html( (string) $nt_term->count ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	endif;
endif;
?>

<?php
$nt_recent = $nt_block_cfg( 'recent' );
if ( ! empty( $nt_recent['show'] ) ) :
	$nt_recent_posts = get_posts( array(
		'numberposts'      => max( 1, (int) ( $nt_recent['count'] ?? 4 ) ),
		'post_status'      => 'publish',
		'suppress_filters' => false,
	) );
	if ( ! empty( $nt_recent_posts ) ) :
		?>
		<div class="app-side-block">
			<?php if ( ! empty( $nt_recent['heading'] ) ) : ?>
				<h3 class="app-side-block__title"><?php echo esc_html( $nt_recent['heading'] ); ?></h3>
			<?php endif; ?>
			<ul class="app-side-posts">
				<?php foreach ( $nt_recent_posts as $nt_post ) : ?>
					<li class="app-side-post">
						<a href="<?php echo esc_url( get_permalink( $nt_post ) ); ?>">
							<?php if ( has_post_thumbnail( $nt_post ) ) : ?>
								<span class="app-side-post__thumb">
									<?php echo get_the_post_thumbnail( $nt_post, 'thumbnail', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
								</span>
							<?php endif; ?>
							<span class="app-side-post__copy">
								<span class="app-side-post__title"><?php echo esc_html( get_the_title( $nt_post ) ); ?></span>
								<span class="app-side-post__date"><?php echo esc_html( get_the_date( '', $nt_post ) ); ?></span>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	endif;
endif;
?>

<?php
$nt_tags = $nt_block_cfg( 'tags' );
if ( ! empty( $nt_tags['show'] ) ) :
	$nt_tag_list = get_tags( array( 'hide_empty' => true, 'number' => 18 ) );
	if ( ! empty( $nt_tag_list ) && ! is_wp_error( $nt_tag_list ) ) :
		?>
		<div class="app-side-block">
			<?php if ( ! empty( $nt_tags['heading'] ) ) : ?>
				<h3 class="app-side-block__title"><?php echo esc_html( $nt_tags['heading'] ); ?></h3>
			<?php endif; ?>
			<div class="app-side-tags">
				<?php foreach ( $nt_tag_list as $nt_tag ) : ?>
					<a class="app-side-tag" href="<?php echo esc_url( get_tag_link( $nt_tag->term_id ) ); ?>">
						<?php echo esc_html( $nt_tag->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	endif;
endif;
?>

<?php
// Shared promos from admin/data/blocks.json - the same entries the rest of
// the site uses, so the wording is edited in exactly one place.
foreach ( NT_Blocks::many( (array) ( $nt_side['blocks'] ?? array() ) ) as $nt_promo ) :
	?>
	<div class="app-side-block app-side-block--promo">
		<?php if ( '' !== $nt_promo['icon'] ) : ?>
			<span class="app-side-promo__icon" aria-hidden="true">
				<?php echo NT_Icons::get_or_text( $nt_promo['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
			</span>
		<?php endif; ?>
		<h3 class="app-side-block__title"><?php echo esc_html( $nt_promo['title'] ); ?></h3>
		<?php if ( '' !== $nt_promo['text'] ) : ?>
			<p class="app-side-promo__text"><?php echo esc_html( $nt_promo['text'] ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $nt_promo['label'] ) : ?>
			<?php if ( '' !== $nt_promo['dialog'] && NT_Dialog::exists( $nt_promo['dialog'] ) ) : ?>
				<button class="app-side-promo__cta" <?php app_dialog_trigger( $nt_promo['dialog'] ); ?>>
					<?php echo esc_html( $nt_promo['label'] ); ?>
				</button>
			<?php elseif ( '' !== $nt_promo['url'] ) : ?>
				<a class="app-side-promo__cta" href="<?php echo esc_url( App_Helpers::link( $nt_promo['url'] ) ); ?>">
					<?php echo esc_html( $nt_promo['label'] ); ?>
				</a>
			<?php endif; ?>
		<?php endif; ?>
	</div>
<?php endforeach; ?>
