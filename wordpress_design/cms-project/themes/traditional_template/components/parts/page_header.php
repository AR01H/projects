<?php
/**
 * Page header banner - ONE reusable heading for any inner page (Contact,
 * News, single posts, FAQ, Careers, etc.).
 *
 * Two modes, same component:
 *   - No 'image' passed  -> flat parchment band (icon/tag + title + rule + subtitle).
 *   - 'image' passed     -> cinematic "poster" banner: full-bleed sepia photo,
 *                           dark gradient scrim, large dramatic title on top.
 *
 * Context/args:
 *   'title'    (string, required)  Falls back to get_the_title().
 *   'subtitle' (string, optional)
 *   'tag'      (string, optional)  Small eyebrow label above the title.
 *   'icon'     (string, optional)  Emoji/icon shown next to the tag.
 *   'image'    (string, optional)  Background photo URL - switches to poster mode.
 *   'meta'     (string, optional)  Small line under the title (e.g. post date/author).
 *
 *   nt_component( 'parts/page_header', array(
 *       'tag'      => 'Our Blog',
 *       'icon'     => '📰',
 *       'title'    => 'News & Updates',
 *       'subtitle' => 'The latest from our team.',
 *       'image'    => 'https://images.unsplash.com/...',
 *   ) );
 */

defined( 'ABSPATH' ) || exit;

$title    = isset( $title ) ? (string) $title : get_the_title();
$subtitle = isset( $subtitle ) ? (string) $subtitle : '';
$tag      = isset( $tag ) ? (string) $tag : '';
$icon     = isset( $icon ) ? (string) $icon : '';
$image    = isset( $image ) ? (string) $image : '';
$meta     = isset( $meta ) ? (string) $meta : '';
$is_poster = '' !== $image;
?>
<div class="nt-page-header<?php echo $is_poster ? ' nt-page-header--poster' : ''; ?>"
	<?php if ( $is_poster ) : ?>style="background-image:url('<?php echo esc_url( $image ); ?>');"<?php endif; ?>>
	<?php if ( $is_poster ) : ?>
		<div class="nt-page-header__scrim" aria-hidden="true"></div>
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/corner-ornament.svg' ); ?>" class="nt-page-header__corner nt-page-header__corner--tl" alt="" aria-hidden="true">
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/corner-ornament.svg' ); ?>" class="nt-page-header__corner nt-page-header__corner--tr" alt="" aria-hidden="true">
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/corner-ornament.svg' ); ?>" class="nt-page-header__corner nt-page-header__corner--bl" alt="" aria-hidden="true">
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/corner-ornament.svg' ); ?>" class="nt-page-header__corner nt-page-header__corner--br" alt="" aria-hidden="true">
	<?php endif; ?>
	<div class="nt-page-header__inner container">
		<?php if ( $tag || $icon ) : ?>
			<div class="nt-page-header__eyebrow">
				<?php if ( $icon ) : ?><span class="nt-page-header__icon" aria-hidden="true"><?php echo esc_html( $icon ); ?></span><?php endif; ?>
				<?php if ( $tag ) : ?><span class="nt-page-header__tag"><?php echo esc_html( $tag ); ?></span><?php endif; ?>
			</div>
		<?php endif; ?>
		<h1 class="nt-page-title"><?php echo esc_html( $title ); ?></h1>
		<span class="nt-page-header__rule" aria-hidden="true"></span>
		<?php if ( '' !== $subtitle ) : ?>
			<p class="nt-page-subtitle"><?php echo esc_html( $subtitle ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $meta ) : ?>
			<p class="nt-page-header__meta"><?php echo esc_html( $meta ); ?></p>
		<?php endif; ?>
	</div>
</div>
