<?php
/**
 * Coming Soon section - drop onto any page that isn't live yet.
 *
 * Args (all optional):
 *   key        string  Key from coming-soon.json  (default: 'default')
 *   icon       string  Override emoji / icon
 *   tag        string  Override eyebrow label
 *   title      string  Override heading HTML (span/em allowed)
 *   body       string  Override description paragraph
 *   cta_label  string  Override button text  (empty string = hide button)
 *   cta_url    string  Override button href
 *   note       string  Override small italic note below button
 *
 * Usage:
 *   // Uses 'default' entry from coming-soon.json
 *   get_template_part( 'components/coming-soon' );
 *
 *   // Uses 'shop' entry from coming-soon.json
 *   get_template_part( 'components/coming-soon', null, [ 'key' => 'shop' ] );
 *
 *   // Uses 'shop' entry but overrides the title
 *   get_template_part( 'components/coming-soon', null, [
 *       'key'   => 'shop',
 *       'title' => 'Merch <span class="accent">Store</span> Coming Soon',
 *   ] );
 */
defined( 'ABSPATH' ) || exit;

$_all = CH_Real_Loader::json( 'coming-soon' );
$_key = sanitize_key( $args['key'] ?? 'default' );
$_d   = $_all[ $_key ] ?? $_all['default'] ?? [];

$icon      = $args['icon']      ?? $_d['icon']      ?? '🌿';
$tag       = $args['tag']       ?? $_d['tag']       ?? 'Coming Soon';
$title     = $args['title']     ?? $_d['title']     ?? '';
$body      = $args['body']      ?? $_d['body']      ?? '';
$cta_label = $args['cta_label'] ?? $_d['cta_label'] ?? '';
$cta_url   = $args['cta_url']   ?? $_d['cta_url']   ?? '';
$note      = $args['note']      ?? $_d['note']      ?? '';

$allowed = [
	'span'   => [ 'class' => [], 'style' => [] ],
	'em'     => [],
	'strong' => [],
	'br'     => [],
];
?>

<section class="ch-cs-section">
	<div class="ch-cs-inner container fade-up">

		<?php if ( $icon ) : ?>
			<div class="ch-cs-icon"><?php echo esc_html( $icon ); ?></div>
		<?php endif; ?>

		<?php if ( $tag ) : ?>
			<div class="section-tag ch-cs-tag"><?php echo esc_html( $tag ); ?></div>
		<?php endif; ?>

		<?php if ( $title ) : ?>
			<h2 class="section-title ch-cs-title"><?php echo wp_kses( $title, $allowed ); ?></h2>
		<?php endif; ?>

		<?php if ( $body ) : ?>
			<p class="section-body ch-cs-body"><?php echo esc_html( $body ); ?></p>
		<?php endif; ?>

		<?php if ( $cta_label ) : ?>
			<div class="ch-cs-cta">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="btn-lime"><?php echo esc_html( $cta_label ); ?></a>
			</div>
		<?php endif; ?>

		<?php if ( $note ) : ?>
			<p class="ch-cs-note"><?php echo esc_html( $note ); ?></p>
		<?php endif; ?>

	</div>
</section>
