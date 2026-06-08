<?php
/**
 * showcase-carousel — section wrapper (header + bg) around two carousel_image_view strips.
 * Strip 1 runs in the passed direction (default rtl), strip 2 always runs ltr.
 *
 * Args:
 *  tag           (string)  Eyebrow tag.                     Default: 'Showcase'
 *  title         (string)  Heading HTML.                    Default: ''
 *  body          (string)  Intro text.                      Default: ''
 *  bg            (string)  Section background CSS value.    Default: 'var(--client-color-11)'
 *  id            (string)  Unique ID prefix for JS hooks.   Default: auto-generated
 *  speed         (int)     Scroll speed in pixels/second.   Default: 60
 *  direction     (string)  Direction of first strip.        Default: 'rtl'
 *  items         (array)   [{type, src, poster, label, desc}]
 */
defined( 'ABSPATH' ) || exit;

$tag       = $args['tag']       ?? 'Showcase';
$title     = $args['title']     ?? '';
$body      = $args['body']      ?? '';
$bg        = $args['bg']        ?? 'var(--client-color-11)';
$speed     = isset( $args['speed'] ) ? (int) $args['speed'] : 60;
$direction = $args['direction'] ?? 'rtl';
$items     = $args['items']     ?? [];
$uid       = esc_attr( $args['id'] ?? 'ch-sc-' . wp_rand( 100, 999 ) );

if ( empty( $items ) ) return;
?>

<section class="ch-sc-section" style="background:<?php echo esc_attr( $bg ); ?>;" id="<?php echo $uid; ?>-section">
	<div class="container">
		<?php get_template_part( 'components/section-header', null, [
			'tag'   => $tag,
			'title' => $title,
			'body'  => $body,
		] ); ?>
	</div>

	<?php get_template_part( 'components/carousels/carousel_image_view', null, [
		'uid'       => $uid,
		'direction' => $direction,
		'speed'     => $speed,
		'items'     => $items,
	] ); ?>
<br/>
	<?php get_template_part( 'components/carousels/carousel_image_view', null, [
		'uid'       => $uid . '-ltr',   /* distinct uid — two strips cannot share the same id */
		'direction' => 'ltr',
		'speed'     => $speed,
		'items'     => array_reverse($items),
	] ); ?>

</section>
