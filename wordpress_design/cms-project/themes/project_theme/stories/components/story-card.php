<?php
/**
 * Component: Story Card
 * Renders a single story. Used by story-grid.php and the featured slot.
 *
 * Variants:
 *   default  - standard grid card (image top, content bottom)
 *   featured - wide horizontal layout (image left, content right)
 *
 * Data keys (all optional with safe fallbacks):
 * @param string $args['id']              Unique story slug (used to build URL)
 * @param string $args['title']           Story title
 * @param string $args['client']          Client name
 * @param string $args['industry']        Industry tag label
 * @param string $args['tagline']         Short one-line tagline
 * @param string $args['summary']         Paragraph summary (shown in featured only)
 * @param string $args['result_1_label']  First result label
 * @param string $args['result_1_value']  First result value
 * @param string $args['result_2_label']  Second result label
 * @param string $args['result_2_value']  Second result value
 * @param string $args['result_3_label']  Third result label
 * @param string $args['result_3_value']  Third result value
 * @param string $args['image']           Image URL
 * @param string $args['variant']         'default' | 'featured'
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$id       = sanitize_key(  $args['id']       ?? '' );
$title    = esc_html(       $args['title']    ?? 'Untitled Story' );
$client   = esc_html(       $args['client']   ?? '' );
$industry = esc_html(       $args['industry'] ?? '' );
$tagline  = esc_html(       $args['tagline']  ?? '' );
$summary  = esc_html(       $args['summary']  ?? '' );
$image    = esc_url(        $args['image']    ?? '' );
$variant  = esc_attr(       $args['variant']  ?? 'default' );

/* Build results array - only include complete label+value pairs */
$results = [];
for ( $i = 1; $i <= 3; $i++ ) {
	$lbl = trim( $args[ "result_{$i}_label" ] ?? '' );
	$val = trim( $args[ "result_{$i}_value" ] ?? '' );
	if ( $lbl && $val ) {
		$results[] = [ 'label' => esc_html( $lbl ), 'value' => esc_html( $val ) ];
	}
}

/* Build permalink - /stories/{slug} or just # if no id */
$permalink = $id ? esc_url( home_url( '/stories/' . $id ) ) : '#';

$classes = 'pt-story-card';
if ( $variant === 'featured' ) $classes .= ' pt-story-card--featured';
?>

<article class="<?php echo $classes; ?>">

	<?php if ( $image ) : ?>
	<div class="pt-story-card__media">
		<img src="<?php echo $image; ?>"
		     alt="<?php echo $title; ?>"
		     class="pt-story-card__img"
		     loading="lazy"
		     decoding="async">
		<?php if ( $industry ) : ?>
			<span class="pt-story-card__industry-tag"><?php echo $industry; ?></span>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<div class="pt-story-card__body">

		<?php if ( $client ) : ?>
			<p class="pt-story-card__client"><?php echo $client; ?></p>
		<?php endif; ?>

		<h2 class="pt-story-card__title">
			<a href="<?php echo $permalink; ?>" class="pt-story-card__title-link">
				<?php echo $title; ?>
			</a>
		</h2>

		<?php if ( $tagline ) : ?>
			<p class="pt-story-card__tagline"><?php echo $tagline; ?></p>
		<?php endif; ?>

		<?php if ( $summary && $variant === 'featured' ) : ?>
			<p class="pt-story-card__summary"><?php echo $summary; ?></p>
		<?php endif; ?>

		<?php if ( $results ) : ?>
		<div class="pt-story-card__results">
			<?php foreach ( $results as $r ) : ?>
			<div class="pt-story-card__result">
				<span class="pt-story-card__result-value"><?php echo $r['value']; ?></span>
				<span class="pt-story-card__result-label"><?php echo $r['label']; ?></span>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php if ( $id ) : ?>
		<a href="<?php echo $permalink; ?>" class="pt-story-card__cta">
			Read full story
			<svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M4 10h12M11 5l5 5-5 5"/>
			</svg>
		</a>
		<?php endif; ?>

	</div>

</article>
