<?php
/**
 * components/sections/article_header.php — Article header with category tag, title, meta.
 *
 * Props: $article { category_tag, title, intro, image_icon, image_gradient,
 *                   meta { date, read_time, save_label } }
 * Usage: adn_component( 'sections/article_header', array( 'article' => $ctx['article'] ) );
 */

defined( 'ABSPATH' ) || exit;

$article = isset( $article ) && is_array( $article ) ? $article : array();
$meta    = isset( $article['meta'] ) && is_array( $article['meta'] ) ? $article['meta'] : array();
?>
<div class="article-header-split">
	<div>
		<?php if ( ! empty( $article['category_tag'] ) ) : ?>
			<div class="article-category-tag"><?php echo esc_html( $article['category_tag'] ); ?></div>
		<?php endif; ?>

		<h1 class="article-title"><?php echo esc_html( isset( $article['title'] ) ? $article['title'] : '' ); ?></h1>

		<?php if ( ! empty( $article['intro'] ) ) : ?>
			<p class="article-intro"><?php echo esc_html( $article['intro'] ); ?></p>
		<?php endif; ?>

		<div class="article-meta">
			<?php if ( ! empty( $meta['date'] ) ) : ?>
				<div class="article-meta-item">&#x1F4C5; <span><?php echo esc_html( $meta['date'] ); ?></span></div>
			<?php endif; ?>
			<?php if ( ! empty( $meta['read_time'] ) ) : ?>
				<div class="article-meta-item">&#x1F550; <span><?php echo esc_html( $meta['read_time'] ); ?></span></div>
			<?php endif; ?>
			<?php if ( ! empty( $meta['save_label'] ) ) : ?>
				<div class="article-meta-item">&#x1F516; <span><?php echo esc_html( $meta['save_label'] ); ?></span></div>
			<?php endif; ?>
		</div>
	</div>

	<div>
		<div class="article-hero-img" style="background:<?php echo esc_attr( isset( $article['image_gradient'] ) ? $article['image_gradient'] : '' ); ?>;">
			<?php echo esc_html( isset( $article['image_icon'] ) ? $article['image_icon'] : '' ); ?>
		</div>
	</div>
</div>
