<?php
/**
 * Theme-level override template for Page Builder pages.
 *
 * The plugin (plugins/cms-plugin/ah-cms.php) checks for this file via
 * locate_template('templates/ah-builder-page.php') and uses it in preference
 * to its own generic fallback template.
 *
 * Requires (guaranteed by the plugin routing before this file is included):
 *   $GLOBALS['ah_builder_page']  — DB row from ah_builder_pages
 *   ah_render_builder_block()    — from plugins/cms-plugin/inc/builder-block-renderer.php
 *   adn_page_open()              — from themes/advaithhomes_new/common/common_functions.php
 *   adn_page_close()             — from themes/advaithhomes_new/common/common_functions.php
 *   adn_component()              — from themes/advaithhomes_new/common/common_functions.php
 */
defined( 'ABSPATH' ) || exit;

$pg        = $GLOBALS['ah_builder_page'];
$blocks    = json_decode( $pg->blocks ?: '[]', true ) ?: array();
$title     = $pg->meta_title ?: $pg->title;
$desc      = $pg->meta_description ?: '';
$page_opts = (array) get_option( 'ah_bp_' . (int) $pg->id . '_opts', array() );

// Site chrome — nav, footer data, social links, settings.
$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

// Header / footer visibility (URL params override DB settings for embed / preview mode).
$bare      = ! empty( $_GET['bare'] ) || ( ! empty( $_GET['content'] ) && 'true' === (string) $_GET['content'] );
$no_header = $bare || ! empty( $_GET['no_header'] ) || ( isset( $page_opts['show_header'] ) && ! $page_opts['show_header'] );
$no_footer = $bare || ! empty( $_GET['no_footer'] ) || ( isset( $page_opts['show_footer'] ) && ! $page_opts['show_footer'] );

// Override <title> and meta description for this virtual page.
add_filter( 'pre_get_document_title', fn() => esc_html( $title ) . ' | ' . get_bloginfo( 'name' ) );
add_action( 'wp_head', function () use ( $desc ) {
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
} );

// ── Hero data ──────────────────────────────────────────────────────────────────
// If the first block is a hero, pull its content into the theme's native page_hero
// component so the page matches every other page on the site.
// The builder hero block is consumed here and skipped from the content loop below.

$first_block   = ! empty( $blocks ) ? $blocks[0] : null;
$first_is_hero = is_array( $first_block ) && ( $first_block['type'] ?? '' ) === 'hero';
$hero_ctas     = array();   // CTA buttons extracted from a builder hero block.
$body_blocks   = $blocks;   // Blocks that go into <main>.

if ( $first_is_hero ) {
	$_hd = $first_block['data'] ?? array();

	$hero_data = array(
		'eyebrow'     => (string) ( $_hd['eyebrow']    ?? '' ),
		'title'       => wp_strip_all_tags( (string) ( $_hd['heading']    ?? $title ) ),
		'description' => wp_strip_all_tags( (string) ( $_hd['subheading'] ?? $desc  ) ),
	);

	// Preserve any CTA buttons the builder hero had — they'll render below the hero.
	if ( ! empty( $_hd['cta1_text'] ) ) {
		$hero_ctas[] = array(
			'text'  => (string) $_hd['cta1_text'],
			'url'   => (string) ( $_hd['cta1_url'] ?? '#' ),
			'class' => 'btn-gold btn-lg',
		);
	}
	if ( ! empty( $_hd['cta2_text'] ) ) {
		$hero_ctas[] = array(
			'text'  => (string) $_hd['cta2_text'],
			'url'   => (string) ( $_hd['cta2_url'] ?? '#' ),
			'class' => 'btn-outline btn-lg',
		);
	}

	// Skip the first block in the content loop — we rendered it as the native hero.
	$body_blocks = array_slice( $blocks, 1 );

} else {
	// No hero block: use the page title / meta description.
	$hero_data = array(
		'eyebrow'     => '',
		'title'       => $title,
		'description' => $desc,
	);
}

// ── Breadcrumb ────────────────────────────────────────────────────────────────
$breadcrumb = array(
	array( 'label' => 'Home', 'url' => home_url( '/' ) ),
	array( 'label' => $title, 'url' => '' ),
);

// ── Open: header + native page hero ───────────────────────────────────────────
if ( ! $no_header ) {

	// adn_page_open renders get_header() + nav. Pass empty breadcrumb here because
	// the breadcrumb is rendered *inside* the page_hero component below.
	adn_page_open( array( 'chrome' => $chrome, 'breadcrumb' => array() ) );

	// Full-bleed theme hero — same component used on every other site page.
	if ( function_exists( 'adn_component' ) ) {
		adn_component( 'sections/page_hero', array(
			'hero'       => $hero_data,
			'breadcrumb' => $breadcrumb,
		) );
	}

} else {
	// Bare / embedded mode: emit a minimal HTML shell so styles and scripts still load.
	?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'ah-builder-bare' ); ?>>
	<?php
}
?>

<?php /* ── CTA buttons from a builder hero block (shown just below the site hero) ── */ ?>
<?php if ( ! $no_header && ! empty( $hero_ctas ) ) : ?>
<div class="section section--sm" style="padding-top:0;padding-bottom:32px;">
	<div class="container" style="display:flex;gap:14px;flex-wrap:wrap;justify-content:center;">
		<?php foreach ( $hero_ctas as $_cta ) : ?>
			<a href="<?php echo esc_url( $_cta['url'] ); ?>" class="btn <?php echo esc_attr( $_cta['class'] ); ?>">
				<?php echo esc_html( $_cta['text'] ); ?>
			</a>
		<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>

<?php /* ── Main content ─────────────────────────────────────────────────────────── */ ?>
<main id="ah-builder-page" class="ah-builder-main" style="min-height:40vh;">

<?php foreach ( $body_blocks as $_block ) :
	$_t = $_block['type'] ?? '';
	$_d = $_block['data'] ?? array();
	ah_render_builder_block( $_t, $_d );
endforeach; ?>

<?php if ( empty( $body_blocks ) && empty( $hero_ctas ) ) : ?>
	<div style="text-align:center;padding:80px 20px;color:#9ca3af;">
		<p>This page has no content yet.</p>
	</div>
<?php endif; ?>

<?php
// Bottom CTA from page-level settings (configured in the builder admin panel).
if ( ! empty( $page_opts['cta_enabled'] ) && ! empty( $page_opts['cta_heading'] ) ) :
	ah_render_builder_block( 'cta_banner', array(
		'heading'   => $page_opts['cta_heading'],
		'text'      => $page_opts['cta_text']      ?? '',
		'btn1_text' => $page_opts['cta_btn1_text'] ?? '',
		'btn1_url'  => $page_opts['cta_btn1_url']  ?? '#',
		'btn2_text' => $page_opts['cta_btn2_text'] ?? '',
		'btn2_url'  => $page_opts['cta_btn2_url']  ?? '#',
		'theme'     => $page_opts['cta_theme']     ?? 'dark',
		'layout'    => 'centered',
	) );
endif;
?>

</main>

<script>
/* Builder page interactive JS ─ FAQ accordion, tabs, dismissible alerts, steps scroll animation */
document.querySelectorAll('.faq__q').forEach(function(btn){
	btn.addEventListener('click',function(){
		var e=btn.getAttribute('aria-expanded')==='true';
		btn.setAttribute('aria-expanded',e?'false':'true');
		var p=btn.nextElementSibling;
		if(p)p.classList.toggle('is-open',!e);
	});
});
document.querySelectorAll('.ah-tabs__btn').forEach(function(btn){
	btn.addEventListener('click',function(){
		var w=btn.closest('.ah-tabs');
		w.querySelectorAll('.ah-tabs__btn').forEach(function(b){b.classList.remove('is-active');b.setAttribute('aria-selected','false');});
		w.querySelectorAll('.ah-tabs__panel').forEach(function(p){p.classList.remove('is-active');});
		btn.classList.add('is-active');btn.setAttribute('aria-selected','true');
		var p=document.getElementById(btn.dataset.tab);
		if(p)p.classList.add('is-active');
	});
});
document.querySelectorAll('.ah-alert[data-dismissible="1"]').forEach(function(el){
	var b=document.createElement('button');
	b.className='ah-alert__close';b.innerHTML='&times;';b.setAttribute('aria-label','Close');
	b.addEventListener('click',function(){el.closest('.container').style.display='none';});
	el.appendChild(b);
});
(function(){
	var obs=new IntersectionObserver(function(entries){
		entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('spine-visible');obs.unobserve(e.target);}});
	},{threshold:.15});
	document.querySelectorAll('.ah-steps').forEach(function(el){obs.observe(el);});
})();
</script>

<?php
// ── Close: footer ─────────────────────────────────────────────────────────────
if ( ! $no_footer ) {
	adn_page_close( array( 'chrome' => $chrome ) );
} else {
	wp_footer();
	?>
</body>
</html>
	<?php
}
