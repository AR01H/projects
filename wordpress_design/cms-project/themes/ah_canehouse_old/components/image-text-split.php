<?php
/**
 * Reusable 2-column image + text split section component.
 *
 * Supports image-left, image-right, and center-aligned layouts.
 * All visual identity (backgrounds, spacing, colours) is driven by
 * the section_class / inner_class args so existing CSS is unchanged.
 *
 * Args (all optional):
 *  layout          string        'image-right' (default) | 'image-left' | 'center'
 *  section_id      string        id attribute on <section>
 *  section_class   string        Extra CSS classes on <section>
 *  bg              string        Inline background value on <section>
 *  inner_class     string        Class on the inner grid wrapper (default: 'ch-split-grid')
 *  tag             string        Eyebrow label text
 *  tag_class       string        Class on the eyebrow element (default: 'section-tag')
 *  title           string        Heading HTML - wp_kses'd (span/em/strong allowed)
 *  title_tag       string        Heading tag h1|h2|h3 (default: 'h2')
 *  title_class     string        Class on the heading (default: 'section-title')
 *  body            string|array  Body text or array of paragraphs
 *  body_class      string        Class on each <p> (default: 'section-body')
 *  content_class   string        Extra class on the content-column div
 *  content_anim    string        Scroll-animation class for content col (auto from layout)
 *  extra_html      string        Pre-rendered HTML appended after body inside content col
 *  visual_html     string        Pre-rendered HTML for the visual column (overrides image_url)
 *  image_url       string        URL of the image
 *  image_alt       string        Alt text for the image
 *  image_class     string        Class on <img> (default: 'ch-split-img')
 *  visual_class    string        Extra class on the visual-column div
 *  visual_anim     string        Scroll-animation class for visual col (auto from layout)
 *  after_html      string        Pre-rendered HTML appended after the grid, inside .container
 */
defined( 'ABSPATH' ) || exit;

$layout        = $args['layout']        ?? 'image-right';
$section_id    = $args['section_id']    ?? '';
$section_class = $args['section_class'] ?? '';
$bg            = $args['bg']            ?? '';
$inner_class   = $args['inner_class']   ?? 'ch-split-grid';
$tag           = $args['tag']           ?? '';
$tag_class     = $args['tag_class']     ?? 'section-tag';
$title         = $args['title']         ?? '';
$title_tag_raw = $args['title_tag'] ?? 'h2';
$title_tag     = in_array( $title_tag_raw, [ 'h1', 'h2', 'h3' ], true ) ? $title_tag_raw : 'h2';
$title_class   = $args['title_class']   ?? 'section-title';
$body          = $args['body']          ?? '';
$body_class    = $args['body_class']    ?? 'section-body';
$content_class = $args['content_class'] ?? '';
$visual_class  = $args['visual_class']  ?? '';
$extra_html    = $args['extra_html']    ?? '';
$visual_html   = $args['visual_html']   ?? '';
$image_url     = $args['image_url']     ?? '';
$image_alt     = $args['image_alt']     ?? '';
$image_class   = $args['image_class']   ?? 'ch-split-img';
$after_html    = $args['after_html']    ?? '';

// Animation classes default to the natural reading direction of each column.
$content_anim = $args['content_anim'] ?? ( $layout === 'image-left' ? 'fade-right' : 'fade-left' );
$visual_anim  = $args['visual_anim']  ?? ( $layout === 'image-left' ? 'fade-left'  : 'fade-right' );

$allowed_kses = [
	'span'   => [ 'class' => [], 'style' => [] ],
	'em'     => [],
	'strong' => [],
	'br'     => [],
];

// Section attributes
$section_attrs  = $section_id ? ' id="' . esc_attr( $section_id ) . '"' : '';
$section_attrs .= $bg         ? ' style="background:' . esc_attr( $bg ) . '"' : '';
$section_cls    = trim( 'ch-split-section ' . $section_class );
?>

<section class="<?php echo esc_attr( $section_cls ); ?>"<?php echo $section_attrs; ?>>
	<div class="container">

		<?php if ( $layout === 'center' ) : ?>

			<div class="ch-section-center fade-up">
				<?php if ( $tag ) : ?>
					<div class="<?php echo esc_attr( $tag_class ); ?>"><?php echo esc_html( $tag ); ?></div>
				<?php endif; ?>
				<?php if ( $title ) : ?>
					<<?php echo $title_tag; ?> class="<?php echo esc_attr( $title_class ); ?>"><?php echo wp_kses( $title, $allowed_kses ); ?></<?php echo $title_tag; ?>>
				<?php endif; ?>
				<?php if ( is_array( $body ) ) : ?>
					<?php foreach ( $body as $para ) : ?>
						<p class="<?php echo esc_attr( $body_class ); ?>" style="margin-top:1rem;"><?php echo esc_html( $para ); ?></p>
					<?php endforeach; ?>
				<?php elseif ( $body ) : ?>
					<p class="<?php echo esc_attr( $body_class ); ?>"><?php echo esc_html( $body ); ?></p>
				<?php endif; ?>
				<?php if ( $extra_html ) echo $extra_html; ?>
			</div>

		<?php else : /* image-left | image-right */ ?>

			<div class="<?php echo esc_attr( $inner_class ); ?>">

				<?php
				// Pre-build both columns so order can be swapped for image-left.
				$col_content_cls = trim( 'ch-split-content ' . $content_class . ' ' . $content_anim );
				$col_visual_cls  = trim( 'ch-split-visual ' . $visual_class . ' ' . $visual_anim );

				ob_start();
				?>
				<div class="<?php echo esc_attr( $col_content_cls ); ?>">
					<?php if ( $tag ) : ?>
						<div class="<?php echo esc_attr( $tag_class ); ?>"><?php echo esc_html( $tag ); ?></div>
					<?php endif; ?>
					<?php if ( $title ) : ?>
						<<?php echo $title_tag; ?> class="<?php echo esc_attr( $title_class ); ?>"><?php echo wp_kses( $title, $allowed_kses ); ?></<?php echo $title_tag; ?>>
					<?php endif; ?>
					<?php if ( is_array( $body ) ) : ?>
						<?php foreach ( $body as $para ) : ?>
							<p class="<?php echo esc_attr( $body_class ); ?>" style="margin-top:1rem;"><?php echo esc_html( $para ); ?></p>
						<?php endforeach; ?>
					<?php elseif ( $body ) : ?>
						<p class="<?php echo esc_attr( $body_class ); ?>"><?php echo esc_html( $body ); ?></p>
					<?php endif; ?>
					<?php if ( $extra_html ) echo $extra_html; ?>
				</div>
				<?php
				$col_content = ob_get_clean();

				ob_start();
				?>
				<div class="<?php echo esc_attr( $col_visual_cls ); ?>">
					<?php if ( $visual_html ) : ?>
						<?php echo $visual_html; ?>
					<?php elseif ( $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>"
							 alt="<?php echo esc_attr( $image_alt ); ?>"
							 loading="lazy"
							 class="<?php echo esc_attr( $image_class ); ?>">
					<?php endif; ?>
				</div>
				<?php
				$col_visual = ob_get_clean();

				if ( $layout === 'image-left' ) {
					echo $col_visual;
					echo $col_content;
				} else {
					echo $col_content;
					echo $col_visual;
				}
				?>

			</div>

		<?php endif; ?>

		<?php if ( $after_html ) echo $after_html; ?>

	</div>
</section>
