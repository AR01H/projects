<?php
/**
 * Single post - The Cane Journal article view.
 * Posts are authored in the CMS plugin (admin.php?page=ah-posts) as native WP
 * posts, so the loop below picks them up exactly the way page-blog.php does.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ch-main ch-single" id="main-content">
	<?php while ( have_posts() ) : the_post();

		$cats        = get_the_category();
		$primary_cat = $cats ? $cats[0] : null;
		$author      = get_the_author();
		$word_count  = str_word_count( wp_strip_all_tags( get_the_content() ) );
		$read_min    = max( 1, (int) ceil( $word_count / 200 ) );
		$permalink   = get_permalink();
		$share_title = rawurlencode( get_the_title() );
		$share_url   = rawurlencode( $permalink );
	?>

		<!-- ── Article Hero (Reusable Component) ─────────────────────────────── -->
		<?php
		$excerpt = has_excerpt() ? get_the_excerpt() : '';
		get_template_part( 'components/page-hero', null, [
			'tag'      => '',
			'heading'  => get_the_title(),
			'desc'     => $excerpt,
			'modifier' => 'ch-page-hero--sugarcane',
		] );
		?>

		<!-- ── Featured Image ─────────────────────────────────────────────────── -->
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="ch-post-thumb container">
				<?php the_post_thumbnail( 'ch-hero', [ 'loading' => 'eager', 'class' => 'ch-single-thumb-img' ] ); ?>
			</div>
		<?php endif; ?>

		<!-- ── Content ────────────────────────────────────────────────────────── -->
		<article class="ch-single-content">
			<?php the_content(); ?>

			<?php
			$pagination = wp_link_pages( [ 'echo' => 0, 'before' => '<nav class="ch-pagination">', 'after' => '</nav>' ] );
			if ( $pagination ) echo $pagination;
			?>

			<!-- Tags + Share -->
			<div class="ch-single-footer">
				<?php $tags = get_the_tags(); if ( $tags ) : ?>
					<div class="ch-single-tags">
						<?php foreach ( $tags as $tag ) : ?>
							<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="ch-single-tag">#<?php echo esc_html( $tag->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="ch-single-share">
					<a class="ch-single-share__btn" target="_blank" rel="noopener" aria-label="Share on WhatsApp" title="Share on WhatsApp"
						href="<?php echo esc_url( 'https://wa.me/?text=' . $share_title . '%20' . $share_url ); ?>">💬</a>
					<a class="ch-single-share__btn" target="_blank" rel="noopener" aria-label="Share on Instagram" title="Share on Instagram"
						href="<?php echo esc_url( 'https://www.instagram.com/' ); ?>">📷</a>
					<button class="ch-single-share__btn" id="ch-native-share" aria-label="Native share" title="Share">📤</button>
				</div>
			</div>
		</article>

		<!-- ── CTA (Reusable Component) ────────────────────────────────────────── -->
		<?php get_template_part( 'components/cta-section', null, [
			'tag'        => 'Ready for More?',
			'heading'    => 'Thirsty for the <span class="accent">Real Thing?</span>',
			'body'       => 'Book our live sugarcane juice stall for your next event, or explore our fresh-pressed menu.',
			'btn_label'  => '🎪 Book an Event',
			'btn_url'    => home_url( '/events/' ),
			'show_phone' => false,
		] ); ?>

	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
