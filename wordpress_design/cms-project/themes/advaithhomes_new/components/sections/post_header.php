<?php
/**
 * components/sections/post_header.php
 *
 * Article hero: 1fr/1fr split - left content + decorative circles/fade,
 * right full-bleed image. No container wrapper - the parent .article-hero-wrap
 * spans the full viewport width so the image grows to the right edge.
 *
 * Props (via extract):
 *   $article = [
 *       'category_tag' => string,
 *       'title'        => string,
 *       'intro'        => string,
 *       'date'         => string,
 *       'read_time'    => string,
 *       'image_url'    => string,
 *   ]
 */

defined( 'ABSPATH' ) || exit;

$_article = isset( $article ) ? (array) $article : array();
$_cat_tag = isset( $_article['category_tag'] ) ? esc_html( $_article['category_tag'] ) : '';
$_title   = isset( $_article['title'] )        ? esc_html( $_article['title'] )        : '';
$_intro   = isset( $_article['intro'] )        ? esc_html( $_article['intro'] )        : '';
$_date    = isset( $_article['date'] )         ? esc_html( $_article['date'] )         : '';
$_rt      = isset( $_article['read_time'] )    ? esc_html( $_article['read_time'] )    : '';
$_img     = isset( $_article['image_url'] )    ? esc_url( $_article['image_url'] )     : '';
?>
<div class="article-header-section">

	<?php /* Left column - decorative circles behind content, right-edge fade */ ?>
	<div class="article-header-left">

		<?php /* Decorative circle blobs (behind content via z-index) */ ?>
		<span class="ahero-circle ahero-circle--a" aria-hidden="true"></span>
		<span class="ahero-circle ahero-circle--b" aria-hidden="true"></span>
		<span class="ahero-circle ahero-circle--c" aria-hidden="true"></span>

		<?php /* Actual content - z-index above circles */ ?>
		<div class="article-header-body">

			<?php if ( '' !== $_cat_tag ) : ?>
				<span class="article-category-tag"><?php echo $_cat_tag; ?></span>
			<?php endif; ?>

			<h1 class="article-title"><?php echo $_title; ?></h1>

			<?php if ( '' !== $_intro ) : ?>
				<p class="article-intro"><?php echo $_intro; ?></p>
			<?php endif; ?>

			<div class="article-meta">
				<?php if ( '' !== $_date ) : ?>
					<span class="meta-item"><?php echo adn_icon( 'fa-calendar-days' ); ?> <?php echo $_date; ?></span>
				<?php endif; ?>
				<?php if ( '' !== $_rt ) : ?>
					<span class="meta-item"><?php echo adn_icon( 'fa-clock' ); ?> <?php echo $_rt; ?></span>
				<?php endif; ?>
				<button class="save-guide-btn" type="button" aria-label="<?php esc_attr_e( 'Save this guide', ADN_TEXT_DOMAIN ); ?>">
					<?php echo adn_icon( 'fa-bookmark' ); ?> <?php esc_html_e( 'Save Guide', ADN_TEXT_DOMAIN ); ?>
				</button>
			</div>

		</div>

	</div>

	<?php /* Right column - image grows with 1fr to fill the right half of viewport */ ?>
	<?php if ( '' !== $_img ) : ?>
		<div class="article-header-img">
			<img src="<?php echo $_img; ?>" alt="" loading="eager" />
		</div>
	<?php endif; ?>

</div>
