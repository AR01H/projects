<?php
/**
 * components/sections/post_header.php
 *
 * Article header: category tag, h1, intro, meta (date, read time, save),
 * and a decorative icon panel on the right.
 *
 * Props (via extract):
 *   $article = [
 *       'category_tag' => string,   display category label
 *       'title'        => string,   post title
 *       'intro'        => string,   excerpt / intro text
 *       'icon'         => string,   emoji icon (e.g. '🏠')
 *       'date'         => string,   formatted date string
 *       'read_time'    => string,   e.g. '12 min read'
 *   ]
 */

defined( 'ABSPATH' ) || exit;

$_article  = isset( $article )  ? (array) $article  : array();
$_cat_tag  = isset( $_article['category_tag'] ) ? esc_html( $_article['category_tag'] ) : '';
$_title    = isset( $_article['title'] )        ? esc_html( $_article['title'] )        : '';
$_intro    = isset( $_article['intro'] )        ? esc_html( $_article['intro'] )        : '';
$_icon     = isset( $_article['icon'] )         ? adn_icon( $_article['icon'] )         : '🏠';
$_date     = isset( $_article['date'] )         ? esc_html( $_article['date'] )         : '';
$_rt       = isset( $_article['read_time'] )    ? esc_html( $_article['read_time'] )    : '';
?>
<div class="article-header-section">

	<div class="article-header-left">

		<?php if ( '' !== $_cat_tag ) : ?>
			<span class="article-category-tag"><?php echo $_cat_tag; ?></span>
		<?php endif; ?>

		<h1 class="article-title"><?php echo $_title; ?></h1>

		<?php if ( '' !== $_intro ) : ?>
			<p class="article-intro"><?php echo $_intro; ?></p>
		<?php endif; ?>

		<div class="article-meta">
			<?php if ( '' !== $_date ) : ?>
				<span class="meta-item">📅 <?php echo $_date; ?></span>
			<?php endif; ?>
			<?php if ( '' !== $_rt ) : ?>
				<span class="meta-item">⏱ <?php echo $_rt; ?></span>
			<?php endif; ?>
			<button class="save-guide-btn" type="button" aria-label="<?php esc_attr_e( 'Save this guide', ADN_TEXT_DOMAIN ); ?>">
				🔖 <?php esc_html_e( 'Save Guide', ADN_TEXT_DOMAIN ); ?>
			</button>
		</div>

	</div>

	<div class="article-header-img" aria-hidden="true"><?php echo $_icon; ?></div>

</div>
