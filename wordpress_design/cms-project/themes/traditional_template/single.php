<?php
/**
 * Single blog post.
 *
 * Built for READING, not just for display. Beyond the article itself it adds
 * the things that make a long piece comfortable:
 *
 *   - a poster header from the post's own featured image
 *   - a meta line with real icons: date, author, category, reading time
 *   - a share row (native share sheet where the browser has one, a copy-link
 *     button everywhere else - no third-party buttons, no tracking scripts)
 *   - previous / next links so a reader never hits a dead end
 *   - related posts from the same category
 *   - drifting cane leaves behind the header, in keeping with every other
 *     inner page
 *
 * The reading-progress bar at the top of the window is already provided site
 * wide by initScrollUI() in common.js, and the scroll reveal by legacy.js -
 * this template only has to add `.fade-up` where a reveal is wanted.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$nt_hdr = App_Helpers::data( 'page_headers' )['blog_post'] ?? array();

while ( have_posts() ) :
	the_post();

	$nt_id    = get_the_ID();
	$nt_words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $nt_id ) ) );
	$nt_mins  = max( 1, (int) ceil( $nt_words / 200 ) );
	$nt_cats  = get_the_category( $nt_id );

	$nt_poster_img = get_the_post_thumbnail_url( $nt_id, 'large' );
	if ( ! $nt_poster_img ) {
		$nt_poster_img = $nt_hdr['fallback_image'] ?? '';
	}

	App_Helpers::component( 'banners/page_header', array(
		'tag'   => ( ! empty( $nt_cats ) && ! is_wp_error( $nt_cats ) ) ? $nt_cats[0]->name : ( $nt_hdr['tag'] ?? '' ),
		'icon'  => $nt_hdr['icon'] ?? '',
		'title' => get_the_title(),
		'image' => $nt_poster_img,
	) );
	?>

	<article <?php post_class( 'app-entry app-single' ); ?>>
		<div class="container app-entry__wrap">

			<?php
			App_Helpers::component( 'breadcrumbs/breadcrumbs', array(
				'items' => array(
					array( 'label' => app_label( 'read_more', 'Journal' ), 'url' => App_Helpers::page_url( 'blog' ) ),
					array( 'label' => get_the_title() ),
				),
			) );
			?>

			<div class="app-entry__meta fade-up">
				<span class="app-entry__meta-item">
					<?php NT_Icons::render( 'calendar' ); ?>
					<?php echo esc_html( get_the_date() ); ?>
				</span>
				<span class="app-entry__meta-item">
					<?php NT_Icons::render( 'user' ); ?>
					<?php echo esc_html( get_the_author() ); ?>
				</span>
				<?php if ( ! empty( $nt_cats ) && ! is_wp_error( $nt_cats ) ) : ?>
					<a class="app-entry__meta-item" href="<?php echo esc_url( get_category_link( $nt_cats[0]->term_id ) ); ?>">
						<?php NT_Icons::render( 'tag' ); ?>
						<?php echo esc_html( $nt_cats[0]->name ); ?>
					</a>
				<?php endif; ?>
				<span class="app-entry__meta-item">
					<?php NT_Icons::render( 'clock' ); ?>
					<?php echo esc_html( sprintf( NT_Ui::label( 'minutes_read', '%s min read' ), (string) $nt_mins ) ); ?>
				</span>
			</div>

			<div class="app-entry-content fade-up"><?php the_content(); ?></div>

			<?php
			$nt_tags = get_the_tags( $nt_id );
			if ( ! empty( $nt_tags ) && ! is_wp_error( $nt_tags ) ) :
				?>
				<div class="app-entry__tags fade-up">
					<?php foreach ( $nt_tags as $nt_tag ) : ?>
						<a class="app-side-tag" href="<?php echo esc_url( get_tag_link( $nt_tag->term_id ) ); ?>">
							<?php echo esc_html( $nt_tag->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			// Share row. data-nt-share is picked up by ui-kit.js, which uses the
			// device's own share sheet when there is one; the copy button is the
			// fallback and works everywhere. No third-party script is loaded, so
			// reading an article sets no cookie from anybody else.
			?>
			<div class="app-entry__share fade-up"
			     data-nt-share
			     data-share-title="<?php echo esc_attr( get_the_title() ); ?>"
			     data-share-url="<?php echo esc_url( get_permalink() ); ?>">
				<span class="app-entry__share-label"><?php echo esc_html( NT_Ui::label( 'share' ) ); ?></span>
				<button type="button" class="app-entry__share-btn" data-nt-share-native hidden>
					<?php NT_Icons::render( 'share' ); ?>
					<span><?php echo esc_html( NT_Ui::label( 'share' ) ); ?></span>
				</button>
				<button type="button" class="app-entry__share-btn" data-nt-copy="<?php echo esc_url( get_permalink() ); ?>">
					<?php NT_Icons::render( 'link' ); ?>
					<span><?php echo esc_html( NT_Ui::label( 'copy', 'Copy link' ) ); ?></span>
				</button>
			</div>

			<?php
			// Previous / next, so the end of one piece is the start of another.
			$nt_prev = get_previous_post();
			$nt_next = get_next_post();
			if ( $nt_prev || $nt_next ) :
				?>
				<nav class="app-entry__nav fade-up" aria-label="<?php echo esc_attr( NT_Ui::aria( 'post_nav', 'More articles' ) ); ?>">
					<?php if ( $nt_prev ) : ?>
						<a class="app-entry__nav-link app-entry__nav-link--prev" href="<?php echo esc_url( get_permalink( $nt_prev ) ); ?>">
							<?php NT_Icons::render( 'arrow-left' ); ?>
							<span>
								<small><?php echo esc_html( NT_Ui::label( 'previous' ) ); ?></small>
								<strong><?php echo esc_html( get_the_title( $nt_prev ) ); ?></strong>
							</span>
						</a>
					<?php endif; ?>

					<?php if ( $nt_next ) : ?>
						<a class="app-entry__nav-link app-entry__nav-link--next" href="<?php echo esc_url( get_permalink( $nt_next ) ); ?>">
							<span>
								<small><?php echo esc_html( NT_Ui::label( 'next' ) ); ?></small>
								<strong><?php echo esc_html( get_the_title( $nt_next ) ); ?></strong>
							</span>
							<?php NT_Icons::render( 'arrow-right' ); ?>
						</a>
					<?php endif; ?>
				</nav>
			<?php endif; ?>

		</div>
	</article>

	<?php
	// Related reading from the same category. Self-hides when there is none,
	// so a one-post blog does not show an empty band.
	if ( ! empty( $nt_cats ) && ! is_wp_error( $nt_cats ) ) :
		$nt_related = new WP_Query( array(
			'post_type'           => 'post',
			'posts_per_page'      => 3,
			'post__not_in'        => array( $nt_id ),
			'category__in'        => wp_list_pluck( $nt_cats, 'term_id' ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		) );

		if ( $nt_related->have_posts() ) :
			?>
			<section class="app-related">
				<div class="container">
					<div class="app-section-center">
						<div class="app-section-tag"><?php echo esc_html( app_label( 'read_more' ) ); ?></div>
						<h2 class="section-title"><?php esc_html_e( 'Keep reading', NT_TEXT_DOMAIN ); ?></h2>
					</div>
					<div class="app-blog__grid">
						<?php
						while ( $nt_related->have_posts() ) {
							$nt_related->the_post();
							App_Helpers::component( 'cards/post_card', array( 'post_id' => get_the_ID() ) );
						}
						?>
					</div>
				</div>
			</section>
			<?php
		endif;
		wp_reset_postdata();
	endif;

	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

endwhile;

get_footer();
