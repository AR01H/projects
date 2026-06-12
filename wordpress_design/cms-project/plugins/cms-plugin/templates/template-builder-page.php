<?php
/**
 * Frontend renderer for pages created in the Page Builder.
 * Loaded by the template_redirect hook in ah-cms.php.
 * $GLOBALS['ah_builder_page'] is the DB row from ah_builder_pages.
 */
defined( 'ABSPATH' ) || exit;

$pg     = $GLOBALS['ah_builder_page'];
$blocks = json_decode( $pg->blocks ?: '[]', true ) ?: array();
$title  = $pg->meta_title ?: $pg->title;
$desc   = $pg->meta_description ?: '';

// URL parameter support for bare/embedded rendering
$bare      = ! empty( $_GET['bare'] );
$no_header = $bare || ! empty( $_GET['no_header'] );
$no_footer = $bare || ! empty( $_GET['no_footer'] );

// Override <title> and meta description
add_filter( 'pre_get_document_title', fn() => esc_html( $title ) . ' | ' . get_bloginfo( 'name' ) );
add_action( 'wp_head', function() use ( $desc ) {
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
} );


if ( ! $no_header ) {
	get_header();
} else {
	// Bare mode: skip theme chrome but still emit wp_head() so styles/scripts load.
	?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php
}
?>

<main id="ah-builder-page" style="min-height:60vh;">

<?php foreach ( $blocks as $block ) :
	$t = $block['type'] ?? '';
	$d = $block['data'] ?? array();
	ah_render_builder_block( $t, $d );
endforeach;

if ( empty( $blocks ) ) : ?>
	<div style="text-align:center;padding:80px 20px;color:#9ca3af;">
		<p>This page has no content yet.</p>
	</div>
<?php endif; ?>

</main>

<script>
document.querySelectorAll('.ah-tabs__btn').forEach(function(btn){
	btn.addEventListener('click',function(){
		var wrap=btn.closest('.ah-tabs');
		wrap.querySelectorAll('.ah-tabs__btn').forEach(function(b){b.classList.remove('is-active');b.setAttribute('aria-selected','false');});
		wrap.querySelectorAll('.ah-tabs__panel').forEach(function(p){p.classList.remove('is-active');});
		btn.classList.add('is-active');btn.setAttribute('aria-selected','true');
		var panel=document.getElementById(btn.dataset.tab);
		if(panel)panel.classList.add('is-active');
	});
});
document.querySelectorAll('.ah-alert[data-dismissible="1"]').forEach(function(el){
	var btn=document.createElement('button');
	btn.className='ah-alert__close';btn.innerHTML='&times;';btn.setAttribute('aria-label','Close');
	btn.addEventListener('click',function(){el.closest('.container').style.display='none';});
	el.appendChild(btn);
});
(function(){
var obs=new IntersectionObserver(function(entries){
  entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('spine-visible');obs.unobserve(e.target);}});
},{threshold:.15});
document.querySelectorAll('.ah-steps').forEach(function(el){obs.observe(el);});
})();</script>

<?php
if ( ! $no_footer ) {
	get_footer();
} else {
	// Bare mode: skip theme chrome but still emit wp_footer() so scripts load.
	wp_footer();
	?>
</body>
</html>
	<?php
}

// ── Block renderers ────────────────────────────────────────────────────────────

function ah_section_open( array $d, string $classes = 'section', string $extra_style = '' ): string {
	$pad_map = array( 'none' => ' section--no-pad', 'sm' => ' section--sm', 'lg' => ' section--lg', 'md' => '' );
	$classes .= $pad_map[ $d['padding'] ?? 'md' ] ?? '';
	$id  = ! empty( $d['section_id'] ) ? ' id="' . esc_attr( $d['section_id'] ) . '"' : '';
	$sty = $extra_style ? ' style="' . esc_attr( $extra_style ) . '"' : '';
	return '<section' . $id . ' class="' . esc_attr( trim( $classes ) ) . '"' . $sty . '>';
}

function ah_render_builder_block( string $type, array $d ): void {

	switch ( $type ) {

		// ── Hero ──────────────────────────────────────────────────────────────
		case 'hero':
			$bg         = $d['bg'] ?? 'white';
			$bg_image   = ! empty( $d['bg_image'] ) ? esc_url( $d['bg_image'] ) : '';
			$bg_class   = '';
			$bg_style   = '';
			$mod_class  = '';
			if ( $bg_image ) {
				$overlay_map = array( 'none' => '0', 'light' => '.3', 'medium' => '.52', 'dark' => '.72' );
				$overlay_alpha = $overlay_map[ $d['overlay'] ?? 'medium' ] ?? '.52';
				$bg_style  = 'background-image:url(' . $bg_image . ');background-size:cover;background-position:center;--hero-overlay:' . $overlay_alpha . ';';
				$mod_class = 'ah-hero--image';
			} elseif ( $bg === 'dark' )                { $bg_class = 'section--dark'; $mod_class = 'ah-hero--dark'; }
			elseif   ( $bg === 'light' )               { $bg_class = 'section--alt'; }
			elseif   ( $bg === 'gold' )                { $bg_style = 'background:var(--client-color-700,#92400e);color:#fff;'; $mod_class = 'ah-hero--gold'; }
			elseif   ( $bg === 'client-color-light' )  { $bg_style = 'background:var(--client-color-50);'; }
			elseif   ( $bg === 'client-color-medium' ) { $bg_style = 'background:var(--client-color-400);'; }
			elseif   ( $bg === 'client-color-dark' )   { $bg_style = 'background:var(--client-color-700);color:#fff;'; $mod_class = 'ah-hero--gold'; }
			$dark       = (bool) $mod_class;
			$hero_style = $bg_style;
			if ( ! empty( $d['min_height'] ) ) {
				$hero_style .= 'min-height:' . (int) $d['min_height'] . 'px;';
			}
			if ( ( $d['full_height'] ?? 'no' ) === 'yes' ) $hero_style .= 'min-height:100vh;';
			$text_align_cls = ( $d['text_align'] ?? 'center' ) === 'left' ? 'text-left' : 'text-center';
			$pad_map   = array( 'none' => ' section--no-pad', 'sm' => ' section--sm', 'lg' => ' section--lg', 'md' => '' );
			$pad_cls   = $pad_map[ $d['padding'] ?? 'md' ] ?? '';
			$hero_id   = ! empty( $d['section_id'] ) ? ' id="' . esc_attr( $d['section_id'] ) . '"' : '';
			$hero_cls  = trim( "ah-block-hero section $text_align_cls $bg_class $mod_class$pad_cls" );
			?>
			<section class="<?php echo esc_attr( $hero_cls ); ?>"<?php echo $hero_id; ?>
			         <?php if ( $hero_style ) echo 'style="' . esc_attr( $hero_style ) . '"'; ?>
			         data-aos="fade-in">
				<div class="ph__bg" aria-hidden="true"><div class="ph__grid-lines"></div></div>
				<div class="container">
					<?php if ( ! empty( $d['eyebrow'] ) ) : ?>
						<p class="section__eyebrow" data-aos="fade-up"><?php echo esc_html( $d['eyebrow'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<h1 class=" " data-aos="fade-up" data-aos-delay="80">
							<?php echo $d['heading']; ?>
						</h1>
					<?php endif; ?>
					<?php if ( ! empty( $d['subheading'] ) ) : ?>
						<p class="section__desc" data-aos="fade-up" data-aos-delay="160">
							<?php echo $d['subheading']; ?>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $d['cta1_text'] ) || ! empty( $d['cta2_text'] ) ) : ?>
						<div style="display:flex;gap:14px;flex-wrap:wrap;justify-content:center;margin-top:36px;"
						     data-aos="fade-up" data-aos-delay="240">
							<?php if ( ! empty( $d['cta1_text'] ) ) : ?>
								<a href="<?php echo esc_url( $d['cta1_url'] ?? '#' ); ?>" class="btn btn-gold btn-lg">
									<?php echo esc_html( $d['cta1_text'] ); ?>
								</a>
							<?php endif; ?>
							<?php if ( ! empty( $d['cta2_text'] ) ) : ?>
								<a href="<?php echo esc_url( $d['cta2_url'] ?? '#' ); ?>"
								   class="btn btn-lg <?php echo $dark ? 'btn-white btn-outline' : 'btn-outline'; ?>">
									<?php echo esc_html( $d['cta2_text'] ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>
			<?php break;

		// ── Section heading ───────────────────────────────────────────────────
		case 'section_heading':
			$align = $d['align'] ?? 'center';
			?>
			<?php echo ah_section_open( $d, 'section section--sm' . ( $align === 'center' ? ' text-center' : '' ) ); ?>
				<div class="container  ">
					<?php if ( ! empty( $d['eyebrow'] ) ) : ?>
						<p class="section__eyebrow"><?php echo esc_html( $d['eyebrow'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $d['title'] ) ) : ?>
						<h2 class=" "><?php echo esc_html( $d['title'] ); ?></h2>
						<?php if ( ( $d['accent_bar'] ?? 'yes' ) !== 'no' ) : ?>
							<span class="ah-accent-bar"></span>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( ! empty( $d['subtitle'] ) ) : ?>
						<p class="section__desc" style="margin-top:14px;"><?php echo esc_html( $d['subtitle'] ); ?></p>
					<?php endif; ?>
				</div>
			</section>
			<?php break;

		// ── Text block ────────────────────────────────────────────────────────
		case 'text_block':
			?>
			<?php echo ah_section_open( $d, 'section section--sm' ); ?>
				<div class="container  ">
					<?php echo wp_kses_post( $d['content'] ?? '' ); ?>
				</div>
			</section>
			<?php break;

		// ── Spacer ────────────────────────────────────────────────────────────
		case 'spacer':
			$h = max( 10, min( 200, (int) ( $d['height'] ?? 40 ) ) );
			echo '<div style="height:' . $h . 'px;"></div>';
			break;

		// ── Cards grid ────────────────────────────────────────────────────────
		case 'cards':
			$cards      = $d['cards'] ?? array();
			$cols       = max( 1, min( 4, (int) ( $d['cols'] ?? 3 ) ) );
			$grid_class = $cols > 1 ? 'grid-' . $cols : '';
			$sec_cls    = ( $d['bg'] ?? 'white' ) === 'alt' ? 'section section--alt' : 'section';
			$cstyle     = $d['card_style'] ?? 'feat';
			?>
			<?php echo ah_section_open( $d, $sec_cls ); ?>
				<div class="container">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div class="section__header text-center" data-aos="fade-up" style="margin-bottom:40px;">
							<h2 class=" "><?php echo esc_html( $d['heading'] ); ?></h2>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<div <?php if ( $grid_class ) echo 'class="' . esc_attr( $grid_class ) . '"'; ?>>
						<?php foreach ( $cards as $i => $card ) :
							if ( $cstyle === 'value' ) : ?>
								<div class="about-value-card" data-aos="fade-up" data-aos-delay="<?php echo $i * 80; ?>">
									<?php if ( ! empty( $card['icon'] ) ) : ?><div class="about-value-card__icon"><?php echo esc_html( $card['icon'] ); ?></div><?php endif; ?>
									<?php if ( ! empty( $card['title'] ) ) : ?><div class="about-value-card__title"><?php echo esc_html( $card['title'] ); ?></div><?php endif; ?>
									<?php if ( ! empty( $card['text'] ) ) : ?><p class="about-value-card__body"><?php echo esc_html( $card['text'] ); ?></p><?php endif; ?>
									<?php if ( ! empty( $card['link_url'] ) ) : ?><a href="<?php echo esc_url( $card['link_url'] ); ?>" class="ah-feat-card__cta"><?php echo esc_html( $card['link_text'] ?? 'Learn more' ); ?> →</a><?php endif; ?>
								</div>
							<?php elseif ( $cstyle === 'plain' ) : ?>
								<div class="ah-plain-card" data-aos="fade-up" data-aos-delay="<?php echo $i * 80; ?>">
									<?php if ( ! empty( $card['icon'] ) ) : ?><div style="font-size:1.8rem;margin-bottom:10px;"><?php echo esc_html( $card['icon'] ); ?></div><?php endif; ?>
									<?php if ( ! empty( $card['title'] ) ) : ?><div style="font-family:var(--font-display);font-size:1.05rem;font-weight:700;margin-bottom:8px;"><?php echo esc_html( $card['title'] ); ?></div><?php endif; ?>
									<?php if ( ! empty( $card['text'] ) ) : ?><p style="font-size:.875rem;color:var(--text-secondary);line-height:1.7;"><?php echo esc_html( $card['text'] ); ?></p><?php endif; ?>
									<?php if ( ! empty( $card['link_url'] ) ) : ?><a href="<?php echo esc_url( $card['link_url'] ); ?>" class="ah-feat-card__cta"><?php echo esc_html( $card['link_text'] ?? 'Learn more' ); ?> →</a><?php endif; ?>
								</div>
							<?php else : ?>
								<div class="ah-feat-card" data-aos="fade-up" data-aos-delay="<?php echo $i * 80; ?>">
									<?php if ( ! empty( $card['icon'] ) ) : ?><div class="ah-feat-card__icon"><?php echo esc_html( $card['icon'] ); ?></div><?php endif; ?>
									<?php if ( ! empty( $card['title'] ) ) : ?><div class="ah-feat-card__title"><?php echo esc_html( $card['title'] ); ?></div><?php endif; ?>
									<?php if ( ! empty( $card['text'] ) ) : ?><p class="ah-feat-card__text"><?php echo esc_html( $card['text'] ); ?></p><?php endif; ?>
									<?php if ( ! empty( $card['link_url'] ) ) : ?><a href="<?php echo esc_url( $card['link_url'] ); ?>" class="ah-feat-card__cta"><?php echo esc_html( $card['link_text'] ?? 'Learn more' ); ?> →</a><?php endif; ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		// ── CTA banner ────────────────────────────────────────────────────────
		case 'cta_banner':
			$cta_themes = array(
				'gold'  => array( 'bg' => 'var(--client-color-700,#b7791f)', 'text' => '#fff',    'sub' => 'rgba(255,255,255,.8)', 'btn1' => 'btn-gold',    'btn2' => 'btn-outline btn-white' ),
				'dark'  => array( 'bg' => '#0f172a',                         'text' => '#fff',    'sub' => 'rgba(255,255,255,.75)', 'btn1' => 'btn-gold',    'btn2' => 'btn-outline btn-white' ),
				'blue'  => array( 'bg' => '#1d4ed8',                         'text' => '#fff',    'sub' => 'rgba(255,255,255,.75)', 'btn1' => 'btn-white',   'btn2' => 'btn-outline btn-white' ),
				'light' => array( 'bg' => 'var(--bg-alt,#f4f2ff)',            'text' => '#0f172a', 'sub' => 'var(--text-secondary)', 'btn1' => 'btn-primary', 'btn2' => 'btn-outline'          ),
			);
			$th        = $cta_themes[ $d['theme'] ?? 'gold' ] ?? $cta_themes['gold'];
			$is_split  = ( $d['layout'] ?? 'centered' ) === 'split';
			$cta_style = 'background:' . $th['bg'] . ';color:' . $th['text'] . ';';
			?>
			<section class="section ah-cta-wrap<?php echo $is_split ? '' : ' text-center'; ?>"
			         style="<?php echo $cta_style; ?>"
			         <?php if ( ! empty( $d['section_id'] ) ) echo 'id="' . esc_attr( $d['section_id'] ) . '"'; ?>
			         data-aos="fade-up">
				<div class="container<?php echo $is_split ? '' : '  '; ?>" style="position:relative;z-index:1;">
					<?php if ( $is_split ) : ?>
						<div class="ah-cta-split">
							<div class="ah-cta-split__text">
								<?php if ( ! empty( $d['eyebrow'] ) ) : ?>
									<p class="section__eyebrow" style="color:<?php echo $th['sub']; ?>;"><?php echo esc_html( $d['eyebrow'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $d['heading'] ) ) : ?>
									<h2 class=" " style="color:<?php echo $th['text']; ?>;"><?php echo esc_html( $d['heading'] ); ?></h2>
								<?php endif; ?>
								<?php if ( ! empty( $d['text'] ) ) : ?>
									<p class="section__desc" style="color:<?php echo $th['sub']; ?>;margin-top:12px;"><?php echo esc_html( $d['text'] ); ?></p>
								<?php endif; ?>
							</div>
							<div class="ah-cta-split__btns">
								<?php if ( ! empty( $d['btn1_text'] ) ) : ?>
									<a href="<?php echo esc_url( $d['btn1_url'] ?? '#' ); ?>" class="btn btn-lg <?php echo esc_attr( $th['btn1'] ); ?>"><?php echo esc_html( $d['btn1_text'] ); ?></a>
								<?php endif; ?>
								<?php if ( ! empty( $d['btn2_text'] ) ) : ?>
									<a href="<?php echo esc_url( $d['btn2_url'] ?? '#' ); ?>" class="btn btn-lg <?php echo esc_attr( $th['btn2'] ); ?>"><?php echo esc_html( $d['btn2_text'] ); ?></a>
								<?php endif; ?>
							</div>
						</div>
					<?php else : ?>
						<?php if ( ! empty( $d['eyebrow'] ) ) : ?>
							<p class="section__eyebrow" style="color:<?php echo $th['sub']; ?>;" data-aos="fade-up"><?php echo esc_html( $d['eyebrow'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $d['heading'] ) ) : ?>
							<h2 class=" " style="color:<?php echo $th['text']; ?>;" data-aos="fade-up">
								<?php echo esc_html( $d['heading'] ); ?>
							</h2>
						<?php endif; ?>
						<?php if ( ! empty( $d['text'] ) ) : ?>
							<p class="section__desc" style="color:<?php echo $th['sub']; ?>;margin-top:12px;" data-aos="fade-up" data-aos-delay="80">
								<?php echo esc_html( $d['text'] ); ?>
							</p>
						<?php endif; ?>
						<div style="display:flex;gap:14px;flex-wrap:wrap;justify-content:center;margin-top:36px;" data-aos="fade-up" data-aos-delay="160">
							<?php if ( ! empty( $d['btn1_text'] ) ) : ?>
								<a href="<?php echo esc_url( $d['btn1_url'] ?? '#' ); ?>"
								   class="btn btn-lg <?php echo esc_attr( $th['btn1'] ); ?>">
									<?php echo esc_html( $d['btn1_text'] ); ?>
								</a>
							<?php endif; ?>
							<?php if ( ! empty( $d['btn2_text'] ) ) : ?>
								<a href="<?php echo esc_url( $d['btn2_url'] ?? '#' ); ?>"
								   class="btn btn-lg <?php echo esc_attr( $th['btn2'] ); ?>">
									<?php echo esc_html( $d['btn2_text'] ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>
			<?php break;

		// ── Stats row ─────────────────────────────────────────────────────────
		case 'stats_row':
			$stats = $d['stats'] ?? array();
			if ( empty( $stats ) ) break;
			?>
			<?php echo ah_section_open( $d, 'section section--alt' ); ?>
				<div class="container">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div class="section__header text-center" style="margin-bottom:32px;" data-aos="fade-up">
							<h2 class=" "><?php echo esc_html( $d['heading'] ); ?></h2>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<div class="stats-strip" style="--cols:<?php echo count( $stats ); ?>;">
						<?php foreach ( $stats as $i => $stat ) : ?>
							<div class="stats-strip__item" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
								<?php if ( ! empty( $stat['icon'] ) ) : ?>
									<div class="stats-strip__icon"><?php echo esc_html( $stat['icon'] ); ?></div>
								<?php endif; ?>
								<div class="stats-strip__num">
									<?php echo esc_html( ( $stat['prefix'] ?? '' ) . ( $stat['number'] ?? '' ) . ( $stat['suffix'] ?? '' ) ); ?>
								</div>
								<div class="stats-strip__label"><?php echo esc_html( $stat['label'] ?? '' ); ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		// ── FAQ accordion ─────────────────────────────────────────────────────
		case 'faq':
			$items = $d['items'] ?? array();
			if ( empty( $items ) ) break;
			?>
			<?php echo ah_section_open( $d, 'section' ); ?>
				<div class="container  ">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div class="section__header text-center" style="margin-bottom:32px;">
							<h2 class=" "><?php echo esc_html( $d['heading'] ); ?></h2>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<?php foreach ( $items as $item ) : ?>
						<div class="faq">
							<button class="faq__q" aria-expanded="false">
								<span><?php echo esc_html( $item['q'] ?? '' ); ?></span>
								<span class="faq__icon" aria-hidden="true">
									<svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 4 6 8 10 4"/></svg>
								</span>
							</button>
							<div class="faq__a">
								<div class="faq__a-inner"><?php echo nl2br( esc_html( $item['a'] ?? '' ) ); ?></div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
			<?php break;

		// ── Button row ────────────────────────────────────────────────────────
		case 'button_row':
			$buttons = $d['buttons'] ?? array();
			if ( empty( $buttons ) ) break;
			$align        = $d['align'] ?? 'center';
			$btn_class_map = array(
				'primary'   => 'btn-primary',
				'secondary' => 'btn-ghost',
				'outline'   => 'btn-outline',
				'gold'      => 'btn-gold',
			);
			?>
			<?php echo ah_section_open( $d, 'section section--sm' ); ?>
				<div class="container" style="display:flex;flex-wrap:wrap;gap:14px;justify-content:<?php echo esc_attr( $align ); ?>;">
					<?php foreach ( $buttons as $btn ) :
						$extra = $btn_class_map[ $btn['style'] ?? 'primary' ] ?? 'btn-primary'; ?>
						<a href="<?php echo esc_url( $btn['url'] ?? '#' ); ?>" class="btn <?php echo $extra; ?>">
							<?php echo esc_html( $btn['text'] ?? 'Click Here' ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
			<?php break;

		// ── Links list ────────────────────────────────────────────────────────
		case 'links_list':
			$links      = $d['links'] ?? array();
			if ( empty( $links ) ) break;
			$cols       = max( 1, min( 3, (int) ( $d['cols'] ?? 2 ) ) );
			$grid_class = $cols > 1 ? 'grid-' . $cols : '';
			$lnk_style  = $d['style'] ?? 'card';
			?>
			<?php echo ah_section_open( $d, 'section' ); ?>
				<div class="container ">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div style="margin-bottom:32px;">
							<h2 class=" "><?php echo esc_html( $d['heading'] ); ?></h2>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<div <?php if ( $grid_class ) echo 'class="' . esc_attr( $grid_class ) . '"'; ?>>
						<?php foreach ( $links as $i => $lnk ) :
							if ( $lnk_style === 'plain' ) : ?>
								<a href="<?php echo esc_url( $lnk['url'] ?? '#' ); ?>"
								   style="display:flex;align-items:center;gap:8px;padding:6px 0;text-decoration:none;color:var(--text-primary);font-size:.9rem;"
								   data-aos="fade-up" data-aos-delay="<?php echo $i * 60; ?>">
									<?php if ( ! empty( $lnk['icon'] ) ) : ?><span><?php echo esc_html( $lnk['icon'] ); ?></span><?php endif; ?>
									<span><?php echo esc_html( $lnk['label'] ?? '' ); ?></span>
								</a>
							<?php elseif ( $lnk_style === 'numbered' ) : ?>
								<a href="<?php echo esc_url( $lnk['url'] ?? '#' ); ?>"
								   class="ah-link-item"
								   data-aos="fade-up" data-aos-delay="<?php echo $i * 60; ?>">
									<div class="ah-link-item__icon" style="font-family:var(--font-display);font-weight:700;color:var(--accent);"><?php echo str_pad( $i + 1, 2, '0', STR_PAD_LEFT ); ?>.</div>
									<div style="flex:1;min-width:0;">
										<div class="ah-link-item__title"><?php echo esc_html( $lnk['label'] ?? '' ); ?></div>
										<?php if ( ! empty( $lnk['desc'] ) ) : ?>
											<div class="ah-link-item__desc"><?php echo esc_html( $lnk['desc'] ); ?></div>
										<?php endif; ?>
									</div>
									<span class="ah-link-item__arrow">→</span>
								</a>
							<?php else : ?>
								<a href="<?php echo esc_url( $lnk['url'] ?? '#' ); ?>"
								   class="ah-link-item"
								   data-aos="fade-up" data-aos-delay="<?php echo $i * 60; ?>">
									<?php if ( ! empty( $lnk['icon'] ) ) : ?>
										<div class="ah-link-item__icon"><?php echo esc_html( $lnk['icon'] ); ?></div>
									<?php endif; ?>
									<div style="flex:1;min-width:0;">
										<div class="ah-link-item__title"><?php echo esc_html( $lnk['label'] ?? '' ); ?></div>
										<?php if ( ! empty( $lnk['desc'] ) ) : ?>
											<div class="ah-link-item__desc"><?php echo esc_html( $lnk['desc'] ); ?></div>
										<?php endif; ?>
									</div>
									<span class="ah-link-item__arrow">→</span>
								</a>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		// ── Testimonial ──────────────────────────────────────────────────────
		case 'testimonial':
			$bg_map  = array( 'white' => '', 'alt' => ' section--alt', 'gold' => ' ah-testimonial--gold' );
			$sec_cls = 'section' . ( $bg_map[ $d['bg'] ?? 'alt' ] ?? ' section--alt' );
			$stars   = max( 1, min( 5, (int) ( $d['stars'] ?? 5 ) ) );
			$is_card = ( $d['layout'] ?? 'centered' ) === 'card';
			?>
			<?php echo ah_section_open( $d, $sec_cls ); ?>
				<div class="container  ">
					<figure class="ah-testimonial<?php echo $is_card ? ' ah-testimonial--card' : ''; ?>">
						<div class="ah-testimonial__stars" aria-label="<?php echo $stars; ?> stars">
							<?php echo str_repeat( '★', $stars ) . str_repeat( '☆', 5 - $stars ); ?>
						</div>
						<blockquote class="ah-testimonial__quote">
							<?php echo wp_kses_post( $d['quote'] ?? '' ); ?>
						</blockquote>
						<figcaption class="ah-testimonial__author">
							<?php if ( ! empty( $d['avatar'] ) ) : ?>
								<img src="<?php echo esc_url( $d['avatar'] ); ?>"
								     alt="<?php echo esc_attr( $d['name'] ?? '' ); ?>"
								     class="ah-testimonial__avatar">
							<?php else : ?>
								<div class="ah-testimonial__avatar ah-testimonial__avatar--initials">
									<?php echo esc_html( mb_substr( $d['name'] ?? '?', 0, 1 ) ); ?>
								</div>
							<?php endif; ?>
							<div>
								<div class="ah-testimonial__name"><?php echo esc_html( $d['name'] ?? '' ); ?></div>
								<?php if ( ! empty( $d['role'] ) ) : ?>
									<div class="ah-testimonial__role"><?php echo esc_html( $d['role'] ); ?></div>
								<?php endif; ?>
								<?php if ( ! empty( $d['company'] ) ) : ?>
									<div class="ah-testimonial__role" style="opacity:.7;"><?php echo esc_html( $d['company'] ); ?></div>
								<?php endif; ?>
							</div>
						</figcaption>
					</figure>
				</div>
			</section>
			<?php break;

		// ── Steps / Process ───────────────────────────────────────────────────
		case 'steps':
			$items     = $d['items'] ?? array();
			if ( empty( $items ) ) break;
			$horiz     = ( $d['layout'] ?? 'vertical' ) === 'horizontal';
			$steps_cls = ( $d['bg'] ?? 'white' ) === 'alt' ? 'section section--alt' : 'section';
			$connector = ( $d['connector'] ?? 'no' ) === 'yes';
			?>
			<?php static $ah_steps_css_done = false; if ( ! $ah_steps_css_done ) : $ah_steps_css_done = true; ?>
<style>
.ah-steps{display:flex;flex-direction:column;gap:14px;}
.ah-step{display:flex!important;flex-direction:row!important;align-items:center!important;gap:24px!important;padding:28px 32px!important;background:#fff!important;border:1px solid rgba(183,121,31,.1)!important;border-left:5px solid #b7791f!important;border-radius:18px!important;box-shadow:0 3px 16px rgba(0,0,0,.06),0 1px 4px rgba(0,0,0,.03)!important;position:relative;overflow:hidden;transition:transform .3s ease,box-shadow .3s ease;}
.ah-step:hover{transform:translateX(8px)!important;box-shadow:0 14px 44px rgba(183,121,31,.15)!important;}
.ah-step::before{content:'';position:absolute;inset:0;background:linear-gradient(90deg,rgba(183,121,31,.04) 0%,transparent 40%);pointer-events:none;}
.ah-step__num{width:64px!important;min-width:64px!important;height:64px!important;border-radius:16px!important;display:flex!important;align-items:center!important;justify-content:center!important;font-size:1.4rem!important;font-weight:700!important;color:#b7791f!important;background:linear-gradient(135deg,#fffbeb,#fffdf7)!important;border:2px solid #fde68a!important;flex-shrink:0!important;}
.ah-step__body{flex:1!important;min-width:0!important;}
.ah-step__title{font-size:1.1rem!important;font-weight:700!important;color:#1e293b!important;-webkit-text-fill-color:#1e293b!important;background:none!important;-webkit-background-clip:initial!important;margin-bottom:6px!important;line-height:1.4!important;}
.ah-step__text{font-size:.9rem!important;color:#64748b!important;line-height:1.8!important;margin:0!important;}
.ah-steps--horiz{flex-direction:row!important;align-items:stretch!important;gap:16px!important;}
.ah-steps--horiz .ah-step{flex:1!important;flex-direction:column!important;align-items:flex-start!important;border-left:1px solid rgba(183,121,31,.08)!important;border-top:5px solid #b7791f!important;padding:24px!important;}
.ah-steps--horiz .ah-step:hover{transform:translateY(-6px)!important;}
.ah-steps--connector.ah-steps--horiz .ah-step::after{display:none!important;}
@media(max-width:768px){.ah-steps--horiz{flex-direction:column!important;}.ah-steps--horiz .ah-step{flex-direction:row!important;border-top:1px solid rgba(183,121,31,.08)!important;border-left:5px solid #b7791f!important;}}
</style>
<?php endif; ?>
<?php echo ah_section_open( $d, $steps_cls ); ?>
				<div class="container  ">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div class="section__header text-center" style="margin-bottom:40px;" data-aos="fade-up">
							<h2 class=" "><?php echo esc_html( $d['heading'] ); ?></h2>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<div class="ah-steps<?php echo $horiz ? ' ah-steps--horiz' : ''; ?><?php echo $connector ? ' ah-steps--connector' : ''; ?>">
						<?php foreach ( $items as $i => $step ) : ?>
							<div class="ah-step" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
								<div class="ah-step__num">
									<?php echo ! empty( $step['icon'] ) ? esc_html( $step['icon'] ) : str_pad( $i + 1, 2, '0', STR_PAD_LEFT ); ?>
								</div>
								<div class="ah-step__body">
									<?php if ( ! empty( $step['title'] ) ) : ?>
										<div class="ah-step__title"><?php echo esc_html( $step['title'] ); ?></div>
									<?php endif; ?>
									<?php if ( ! empty( $step['text'] ) ) : ?>
										<p class="ah-step__text"><?php echo esc_html( $step['text'] ); ?></p>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		// ── Divider ───────────────────────────────────────────────────────────
		case 'divider':
			$style = $d['style'] ?? 'line';
			$label = $d['label'] ?? '';
			?>
			<div class="ah-divider ah-divider--<?php echo esc_attr( $style ); ?>">
				<?php if ( $label ) : ?>
					<span class="ah-divider__label"><?php echo esc_html( $label ); ?></span>
				<?php elseif ( $style === 'ornament' ) : ?>
					<span class="ah-divider__ornament" aria-hidden="true">◆</span>
				<?php elseif ( $style === 'dots' ) : ?>
					<span class="ah-divider__ornament" aria-hidden="true">• • •</span>
				<?php endif; ?>
			</div>
			<?php break;

		// ── Alert / Notice ────────────────────────────────────────────────────
		case 'alert':
			$type_map = array(
				'info'    => array( 'icon' => 'ℹ️',  'cls' => 'ah-alert--info'    ),
				'success' => array( 'icon' => '✅',  'cls' => 'ah-alert--success' ),
				'warning' => array( 'icon' => '⚠️',  'cls' => 'ah-alert--warning' ),
				'tip'     => array( 'icon' => '💡',  'cls' => 'ah-alert--tip'     ),
			);
			$at          = $type_map[ $d['type'] ?? 'info' ] ?? $type_map['info'];
			$dismissible = ( $d['dismissible'] ?? 'no' ) === 'yes';
			$alert_id    = ! empty( $d['section_id'] ) ? ' id="' . esc_attr( $d['section_id'] ) . '"' : '';
			?>
			<div class="container  " style="padding-top:0;padding-bottom:0;margin-bottom:24px;" data-aos="fade-up"<?php echo $alert_id; ?>>
				<div class="ah-alert <?php echo $at['cls']; ?>"<?php echo $dismissible ? ' data-dismissible="1"' : ''; ?>>
					<div class="ah-alert__icon" aria-hidden="true"><?php echo $at['icon']; ?></div>
					<div class="ah-alert__body">
						<?php if ( ! empty( $d['title'] ) ) : ?>
							<div class="ah-alert__title"><?php echo esc_html( $d['title'] ); ?></div>
						<?php endif; ?>
						<?php if ( ! empty( $d['text'] ) ) : ?>
							<p class="ah-alert__text"><?php echo esc_html( $d['text'] ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php break;

		// ── 2/3-Col text columns ──────────────────────────────────────────────
		case 'columns':
			$items    = $d['items'] ?? array();
			if ( empty( $items ) ) break;
			$cols     = max( 2, min( 3, (int) ( $d['cols'] ?? 2 ) ) );
			$col_cls  = ( $d['bg'] ?? 'white' ) === 'alt'
				? 'section section--alt section--sm ah-columns-section'
				: 'section section--sm ah-columns-section';
			?>
			<?php echo ah_section_open( $d, $col_cls ); ?>
				<div class="container">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div class="section__header text-center ah-columns-header"
							data-aos="fade-up">
							<h2 class="ah-columns-title">
								<?php echo esc_html( $d['heading'] ); ?>
							</h2>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<div class="grid-<?php echo $cols; ?> ah-columns-grid">
						<?php foreach ( $items as $i => $col ) : ?>
							<div class="ah-columns-card"
								data-aos="fade-up"
								data-aos-delay="<?php echo $i * 80; ?>">
								<?php if ( ! empty( $col['icon'] ) ) : ?>
									<div class="ah-columns-icon">
										<?php echo esc_html( $col['icon'] ); ?>
									</div>
								<?php endif; ?>
								<?php if ( ! empty( $col['heading'] ) ) : ?>
									<h3 class="ah-columns-card-title">
										<?php echo esc_html( $col['heading'] ); ?>
									</h3>
								<?php endif; ?>
								<?php if ( ! empty( $col['text'] ) ) : ?>
									<p class="ah-columns-card-text">
										<?php echo nl2br( esc_html( $col['text'] ) ); ?>
									</p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		// ── Image + text ─────────────────────────────────────────────────────
		case 'image_text':
			$img_left = ( $d['layout'] ?? 'image-left' ) === 'image-left';
			$points   = $d['points'] ?? array();
			?>
			<?php echo ah_section_open( $d, 'section' ); ?>
				<div class="container  ">
					<div class="content-layout--2col ah-img-text-grid"
					     style="<?php echo $img_left ? '' : 'direction:rtl;'; ?>">
						<div style="<?php echo $img_left ? '' : 'direction:ltr;'; ?>"
						     data-aos="<?php echo $img_left ? 'fade-right' : 'fade-left'; ?>">
							<?php if ( ! empty( $d['image_url'] ) ) : ?>
								<img src="<?php echo esc_url( $d['image_url'] ); ?>"
								     alt="<?php echo esc_attr( $d['image_alt'] ?? '' ); ?>"
								     style="width:100%;border-radius:var(--r-lg,16px);display:block;box-shadow:var(--shadow-lg,0 20px 60px rgba(234,179,8,.14));">
							<?php else : ?>
								<div style="height:320px;border-radius:var(--r-lg,16px);background:var(--bg-alt,#f4f2ff);display:flex;align-items:center;justify-content:center;font-size:.85rem;color:var(--text-muted);">
									Image placeholder
								</div>
							<?php endif; ?>
						</div>
						<div style="<?php echo $img_left ? '' : 'direction:ltr;'; ?>"
						     data-aos="<?php echo $img_left ? 'fade-left' : 'fade-right'; ?>" data-aos-delay="100">
							<?php if ( ! empty( $d['eyebrow'] ) ) : ?>
								<p class="section__eyebrow"><?php echo esc_html( $d['eyebrow'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $d['heading'] ) ) : ?>
								<h2 class=" "><?php echo esc_html( $d['heading'] ); ?></h2>
								<span class="ah-accent-bar" style="margin-bottom:20px;"></span>
							<?php endif; ?>
							<?php if ( ! empty( $d['text'] ) ) : ?>
								<p style="line-height:1.8;margin-top:20px;margin-bottom:28px;color:var(--text-secondary);"><?php echo nl2br( esc_html( $d['text'] ) ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $points ) ) : ?>
								<ul style="list-style:none;padding:0;margin:0 0 24px;">
									<?php foreach ( $points as $pt ) : ?>
										<li style="display:flex;align-items:flex-start;gap:10px;padding:6px 0;font-size:.9rem;color:var(--text-secondary);">
											<?php if ( ! empty( $pt['icon'] ) ) : ?><span style="flex-shrink:0;"><?php echo esc_html( $pt['icon'] ); ?></span><?php endif; ?>
											<span><?php echo esc_html( $pt['text'] ?? '' ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<div style="display:flex;gap:12px;flex-wrap:wrap;">
								<?php if ( ! empty( $d['btn_text'] ) ) : ?>
									<a href="<?php echo esc_url( $d['btn_url'] ?? '#' ); ?>" class="btn btn-gold">
										<?php echo esc_html( $d['btn_text'] ); ?>
									</a>
								<?php endif; ?>
								<?php if ( ! empty( $d['btn2_text'] ) ) : ?>
									<a href="<?php echo esc_url( $d['btn2_url'] ?? '#' ); ?>" class="btn btn-outline">
										<?php echo esc_html( $d['btn2_text'] ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</section>
			<?php break;

		// ── Gallery ───────────────────────────────────────────────────────────
		case 'gallery':
			$images = $d['images'] ?? array();
			if ( empty( $images ) ) break;
			$cols = max( 2, min( 4, (int) ( $d['cols'] ?? 3 ) ) );
			$gap_map = array( 'sm' => '8px', 'md' => '14px', 'lg' => '24px' );
			$gap = $gap_map[ $d['gap'] ?? 'md' ] ?? '14px';
			?>
			<?php echo ah_section_open( $d, 'section' ); ?>
				<div class="container">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div class="section__header text-center" style="margin-bottom:32px;" data-aos="fade-up">
							<h2 class=" "><?php echo esc_html( $d['heading'] ); ?></h2>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<div class="ah-gallery" style="--gallery-cols:<?php echo $cols; ?>;--gallery-gap:<?php echo $gap; ?>;" data-aos="fade-up">
						<?php foreach ( $images as $img ) : ?>
							<figure class="ah-gallery__item">
								<a href="<?php echo esc_url( $img['url'] ?? '' ); ?>" target="_blank" rel="noopener">
									<img src="<?php echo esc_url( $img['url'] ?? '' ); ?>"
									     alt="<?php echo esc_attr( $img['alt'] ?? '' ); ?>"
									     loading="lazy">
								</a>
								<?php if ( ! empty( $img['caption'] ) ) : ?>
									<figcaption><?php echo esc_html( $img['caption'] ); ?></figcaption>
								<?php endif; ?>
							</figure>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		// ── Video Embed ───────────────────────────────────────────────────────
		case 'video':
			$url = $d['url'] ?? '';
			if ( empty( $url ) ) break;
			$embed = preg_replace( '|(?:https?://)?(?:www\.)?youtube\.com/watch\?v=([a-zA-Z0-9_-]+)|', 'https://www.youtube.com/embed/$1', $url );
			$embed = preg_replace( '|(?:https?://)?youtu\.be/([a-zA-Z0-9_-]+)|', 'https://www.youtube.com/embed/$1', $embed );
			$embed = preg_replace( '|(?:https?://)?(?:www\.)?vimeo\.com/(\d+)|', 'https://player.vimeo.com/video/$1', $embed );
			$ratio_map = array( '16:9' => '56.25%', '4:3' => '75%', '1:1' => '100%' );
			$ratio_pad = $ratio_map[ $d['ratio'] ?? '16:9' ] ?? '56.25%';
			?>
			<?php echo ah_section_open( $d, 'section section--sm' ); ?>
				<div class="container  " data-aos="fade-up">
					<?php if ( ! empty( $d['caption'] ) ) : ?>
						<p class="section__eyebrow" style="margin-bottom:16px;"><?php echo esc_html( $d['caption'] ); ?></p>
					<?php endif; ?>
					<div class="ah-video-wrap" style="padding-bottom:<?php echo $ratio_pad; ?>">
						<iframe src="<?php echo esc_url( $embed ); ?>"
						        frameborder="0"
						        allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture"
						        allowfullscreen
						        loading="lazy"></iframe>
					</div>
				</div>
			</section>
			<?php break;

		// ── Map Embed ─────────────────────────────────────────────────────────
		case 'map_embed':
			$url = $d['url'] ?? '';
			if ( empty( $url ) ) break;
			$h = max( 200, min( 700, (int) ( $d['height'] ?? 400 ) ) );
			?>
			<?php echo ah_section_open( $d, 'section section--sm' ); ?>
				<div class="container" data-aos="fade-up">
					<?php if ( ! empty( $d['label'] ) ) : ?>
						<div style="margin-bottom:20px;">
							<h2 class=" "><?php echo esc_html( $d['label'] ); ?></h2>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<div class="ah-map-wrap" style="height:<?php echo $h; ?>px;">
						<iframe src="<?php echo esc_url( $url ); ?>"
						        width="100%" height="100%"
						        style="border:0;" allowfullscreen="" loading="lazy"
						        referrerpolicy="no-referrer-when-downgrade"></iframe>
					</div>
				</div>
			</section>
			<?php break;

		// ── Logo Strip ────────────────────────────────────────────────────────
		case 'logo_strip':
			$logos = $d['logos'] ?? array();
			if ( empty( $logos ) ) break;
			$sec_cls = ( $d['bg'] ?? 'white' ) === 'alt' ? 'section section--alt' : 'section';
			?>
			<?php echo ah_section_open( $d, $sec_cls ); ?>
				<div class="container" data-aos="fade-up">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<p class="ah-logo-strip__label"><?php echo esc_html( $d['heading'] ); ?></p>
					<?php endif; ?>
					<div class="ah-logo-strip">
						<?php foreach ( $logos as $logo ) : ?>
							<?php $tag_open  = ! empty( $logo['link'] ) ? '<a href="' . esc_url( $logo['link'] ) . '" target="_blank" rel="noopener" class="ah-logo-strip__item">' : '<div class="ah-logo-strip__item">'; ?>
							<?php $tag_close = ! empty( $logo['link'] ) ? '</a>' : '</div>'; ?>
							<?php echo $tag_open; ?>
								<img src="<?php echo esc_url( $logo['url'] ?? '' ); ?>"
								     alt="<?php echo esc_attr( $logo['alt'] ?? '' ); ?>"
								     loading="lazy">
							<?php echo $tag_close; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		// ── Timeline ──────────────────────────────────────────────────────────
		case 'timeline':
			$items = $d['items'] ?? array();
			if ( empty( $items ) ) break;
			$sec_cls = ( $d['bg'] ?? 'white' ) === 'alt' ? 'section section--alt' : 'section';
			?>
			<?php echo ah_section_open( $d, $sec_cls ); ?>
				<div class="container  " data-aos="fade-up">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div class="section__header text-center" style="margin-bottom:40px;">
							<h2 class=" "><?php echo esc_html( $d['heading'] ); ?></h2>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<div class="ah-timeline">
						<?php foreach ( $items as $i => $ev ) : ?>
							<div class="ah-timeline__item" data-aos="fade-up" data-aos-delay="<?php echo $i * 80; ?>">
								<div class="ah-timeline__marker">
									<div class="ah-timeline__dot"><?php echo ! empty( $ev['icon'] ) ? esc_html( $ev['icon'] ) : ''; ?></div>
								</div>
								<div class="ah-timeline__body">
									<?php if ( ! empty( $ev['date'] ) ) : ?><div class="ah-timeline__date"><?php echo esc_html( $ev['date'] ); ?></div><?php endif; ?>
									<?php if ( ! empty( $ev['title'] ) ) : ?><div class="ah-timeline__title"><?php echo esc_html( $ev['title'] ); ?></div><?php endif; ?>
									<?php if ( ! empty( $ev['text'] ) ) : ?><p class="ah-timeline__text"><?php echo esc_html( $ev['text'] ); ?></p><?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		// ── Pricing ───────────────────────────────────────────────────────────
		case 'pricing':
			$plans = $d['plans'] ?? array();
			if ( empty( $plans ) ) break;
			$cols = count( $plans );
			?>
			<?php echo ah_section_open( $d, 'section section--alt' ); ?>
				<div class="container">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div class="section__header text-center" style="margin-bottom:40px;" data-aos="fade-up">
							<h2 class=" "><?php echo esc_html( $d['heading'] ); ?></h2>
							<?php if ( ! empty( $d['subtitle'] ) ) : ?><p class="section__desc"><?php echo esc_html( $d['subtitle'] ); ?></p><?php endif; ?>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<div class="ah-pricing-grid" style="--pricing-cols:<?php echo min( $cols, 3 ); ?>;">
						<?php foreach ( $plans as $i => $plan ) :
							$hi = ( $plan['highlight'] ?? 'no' ) === 'yes';
							$features = array_filter( array_map( 'trim', explode( "\n", $plan['features'] ?? '' ) ) );
						?>
							<div class="ah-pricing-card<?php echo $hi ? ' ah-pricing-card--featured' : ''; ?>" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
								<?php if ( $hi ) : ?><div class="ah-pricing-card__badge">Most Popular</div><?php endif; ?>
								<div class="ah-pricing-card__name"><?php echo esc_html( $plan['name'] ?? '' ); ?></div>
								<div class="ah-pricing-card__price"><?php echo esc_html( $plan['price'] ?? '' ); ?></div>
								<?php if ( ! empty( $plan['period'] ) ) : ?><div class="ah-pricing-card__period"><?php echo esc_html( $plan['period'] ); ?></div><?php endif; ?>
								<?php if ( ! empty( $plan['desc'] ) ) : ?><p class="ah-pricing-card__desc"><?php echo esc_html( $plan['desc'] ); ?></p><?php endif; ?>
								<?php if ( ! empty( $features ) ) : ?>
									<ul class="ah-pricing-card__features">
										<?php foreach ( $features as $feat ) : ?>
											<li><?php echo esc_html( $feat ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
								<?php if ( ! empty( $plan['cta_text'] ) ) : ?>
									<a href="<?php echo esc_url( $plan['cta_url'] ?? '#' ); ?>" class="btn <?php echo $hi ? 'btn-gold' : 'btn-outline'; ?> btn-full">
										<?php echo esc_html( $plan['cta_text'] ); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		// ── Pull Quote ────────────────────────────────────────────────────────
		case 'pull_quote':
			$quote = $d['quote'] ?? '';
			if ( empty( $quote ) ) break;
			$size_cls  = ( $d['size'] ?? 'md' ) === 'lg' ? ' ah-pull-quote--lg' : '';
			$align_cls = ( $d['align'] ?? 'center' ) === 'left' ? '' : ' text-center';
			$color     = $d['color'] ?? 'gold';
			?>
			<?php echo ah_section_open( $d, 'section section--sm' ); ?>
				<div class="container  " data-aos="fade-up">
					<blockquote class="ah-pull-quote<?php echo $size_cls . $align_cls; ?>" data-color="<?php echo esc_attr( $color ); ?>">
						<?php echo wp_kses_post( $quote ); ?>
					</blockquote>
				</div>
			</section>
			<?php break;

		// ── Icon List ─────────────────────────────────────────────────────────
		case 'icon_list':
			$items = $d['items'] ?? array();
			if ( empty( $items ) ) break;
			$cols    = max( 1, min( 2, (int) ( $d['cols'] ?? 1 ) ) );
			$sec_cls = ( $d['bg'] ?? 'white' ) === 'alt' ? 'section section--alt' : 'section';
			?>
			<?php echo ah_section_open( $d, $sec_cls ); ?>
				<div class="container  ">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div style="margin-bottom:28px;" data-aos="fade-up">
							<h2 class=" "><?php echo esc_html( $d['heading'] ); ?></h2>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<ul class="ah-icon-list<?php echo $cols > 1 ? ' ah-icon-list--' . $cols . 'col' : ''; ?>" data-aos="fade-up">
						<?php foreach ( $items as $item ) : ?>
							<li class="ah-icon-list__item">
								<span class="ah-icon-list__icon"><?php echo esc_html( $item['icon'] ?? '✅' ); ?></span>
								<div>
									<span class="ah-icon-list__text"><?php echo esc_html( $item['text'] ?? '' ); ?></span>
									<?php if ( ! empty( $item['sub'] ) ) : ?>
										<span class="ah-icon-list__sub"><?php echo esc_html( $item['sub'] ); ?></span>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>
			<?php break;

		// ── Download Button ───────────────────────────────────────────────────
		case 'download':
			$url = $d['url'] ?? '';
			if ( empty( $url ) ) break;
			?>
			<?php echo ah_section_open( $d, 'section section--sm ah-download-section' ); ?>
				<div class="container  " data-aos="fade-up">
					<div class="ah-download">
						<div class="ah-download__icon">⬇️</div>
						<div class="ah-download__body">
							<?php if ( ! empty( $d['label'] ) ) : ?><div class="ah-download__title"><?php echo esc_html( $d['label'] ); ?></div><?php endif; ?>
							<?php if ( ! empty( $d['desc'] ) ) : ?><p class="ah-download__desc"><?php echo esc_html( $d['desc'] ); ?></p><?php endif; ?>
							<div class="ah-download__meta">
								<?php if ( ! empty( $d['filetype'] ) ) : ?><span class="ah-download__type"><?php echo esc_html( $d['filetype'] ); ?></span><?php endif; ?>
								<?php if ( ! empty( $d['filesize'] ) ) : ?><span class="ah-download__size"><?php echo esc_html( $d['filesize'] ); ?></span><?php endif; ?>
							</div>
						</div>
						<a href="<?php echo esc_url( $url ); ?>" class="btn btn-gold" download>Download</a>
					</div>
				</div>
			</section>
			<?php break;

		// ── Tabs ──────────────────────────────────────────────────────────────
		case 'tabs':
			$tabs = $d['tabs'] ?? array();
			if ( empty( $tabs ) ) break;
			$block_id = 'ah-tabs-' . substr( md5( serialize( $tabs ) ), 0, 6 );
			?>
			<?php echo ah_section_open( $d, 'section' ); ?>
				<div class="container  " data-aos="fade-up">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div class="section__header text-center" style="margin-bottom:32px;">
							<h2 class=" "><?php echo esc_html( $d['heading'] ); ?></h2>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<div class="ah-tabs" id="<?php echo esc_attr( $block_id ); ?>">
						<div class="ah-tabs__nav" role="tablist">
							<?php foreach ( $tabs as $i => $tab ) : ?>
								<button class="ah-tabs__btn<?php echo $i === 0 ? ' is-active' : ''; ?>"
								        role="tab"
								        data-tab="<?php echo esc_attr( $block_id . '-' . $i ); ?>"
								        aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>">
									<?php if ( ! empty( $tab['icon'] ) ) : ?><?php echo esc_html( $tab['icon'] ); ?> <?php endif; ?>
									<?php echo esc_html( $tab['label'] ?? '' ); ?>
								</button>
							<?php endforeach; ?>
						</div>
						<div class="ah-tabs__panels">
							<?php foreach ( $tabs as $i => $tab ) : ?>
								<div class="ah-tabs__panel<?php echo $i === 0 ? ' is-active' : ''; ?>"
								     id="<?php echo esc_attr( $block_id . '-' . $i ); ?>"
								     role="tabpanel">
									<?php echo wp_kses_post( $tab['content'] ?? '' ); ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
			<?php break;

		// ── Comparison Table ──────────────────────────────────────────────────
		case 'comparison':
			$rows = $d['rows'] ?? array();
			if ( empty( $rows ) ) break;
			?>
			<?php echo ah_section_open( $d, 'section' ); ?>
				<div class="container  " data-aos="fade-up">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div class="section__header text-center" style="margin-bottom:32px;">
							<h2 class=" "><?php echo esc_html( $d['heading'] ); ?></h2>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<div class="ah-comparison">
						<div class="ah-comparison__head">
							<div class="ah-comparison__feature-col"></div>
							<div class="ah-comparison__col ah-comparison__col--yes"><?php echo esc_html( $d['col1'] ?? 'With Us' ); ?></div>
							<div class="ah-comparison__col ah-comparison__col--no"><?php echo esc_html( $d['col2'] ?? 'Without Us' ); ?></div>
						</div>
						<?php foreach ( $rows as $i => $row ) : ?>
							<div class="ah-comparison__row<?php echo $i % 2 === 0 ? ' ah-comparison__row--even' : ''; ?>">
								<div class="ah-comparison__feature"><?php echo esc_html( $row['feature'] ?? '' ); ?></div>
								<div class="ah-comparison__val"><?php echo esc_html( $row['col1'] ?? '' ); ?></div>
								<div class="ah-comparison__val"><?php echo esc_html( $row['col2'] ?? '' ); ?></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		// ── Notice Bar ────────────────────────────────────────────────────────
		case 'notice_bar':
			$style_map = array(
				'gold' => 'background:var(--client-color-400,#f7c62f);color:#1a0a00;',
				'dark' => 'background:#0f172a;color:#fff;',
				'info' => 'background:#eff6ff;color:#1e3a5f;border-top:3px solid #3b82f6;border-bottom:3px solid #3b82f6;',
			);
			$style = $style_map[ $d['style'] ?? 'gold' ] ?? $style_map['gold'];
			$id_attr = ! empty( $d['section_id'] ) ? ' id="' . esc_attr( $d['section_id'] ) . '"' : '';
			?>
			<div<?php echo $id_attr; ?> class="ah-notice-bar" style="<?php echo esc_attr( $style ); ?>">
				<span class="ah-notice-bar__text"><?php echo esc_html( $d['text'] ?? '' ); ?></span>
				<?php if ( ! empty( $d['cta'] ) ) : ?>
					<a href="<?php echo esc_url( $d['url'] ?? '#' ); ?>" class="ah-notice-bar__cta"><?php echo esc_html( $d['cta'] ); ?> →</a>
				<?php endif; ?>
			</div>
			<?php break;

		// ── Contact Card ──────────────────────────────────────────────────────
		case 'contact_card':
			$horiz = ( $d['layout'] ?? 'horizontal' ) === 'horizontal';
			?>
			<?php echo ah_section_open( $d, 'section section--alt' ); ?>
				<div class="container  " data-aos="fade-up">
					<div class="ah-contact-card<?php echo $horiz ? '' : ' ah-contact-card--vertical'; ?>">
						<?php if ( ! empty( $d['photo'] ) ) : ?>
							<img src="<?php echo esc_url( $d['photo'] ); ?>"
							     alt="<?php echo esc_attr( $d['name'] ?? '' ); ?>"
							     class="ah-contact-card__photo">
						<?php endif; ?>
						<div class="ah-contact-card__body">
							<?php if ( ! empty( $d['name'] ) ) : ?><div class="ah-contact-card__name"><?php echo esc_html( $d['name'] ); ?></div><?php endif; ?>
							<?php if ( ! empty( $d['role'] ) ) : ?><div class="ah-contact-card__role"><?php echo esc_html( $d['role'] ); ?></div><?php endif; ?>
							<?php if ( ! empty( $d['bio'] ) ) : ?><p class="ah-contact-card__bio"><?php echo esc_html( $d['bio'] ); ?></p><?php endif; ?>
							<div class="ah-contact-card__links">
								<?php if ( ! empty( $d['phone'] ) ) : ?><a href="tel:<?php echo esc_attr( preg_replace('/\s+/','',$d['phone']) ); ?>" class="ah-contact-card__link">📞 <?php echo esc_html( $d['phone'] ); ?></a><?php endif; ?>
								<?php if ( ! empty( $d['email'] ) ) : ?><a href="mailto:<?php echo esc_attr( $d['email'] ); ?>" class="ah-contact-card__link">✉️ <?php echo esc_html( $d['email'] ); ?></a><?php endif; ?>
							</div>
							<?php if ( ! empty( $d['cta_text'] ) ) : ?>
								<a href="<?php echo esc_url( $d['cta_url'] ?? '#' ); ?>" class="btn btn-gold" style="margin-top:16px;"><?php echo esc_html( $d['cta_text'] ); ?></a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</section>
			<?php break;

		// ── Team Members ──────────────────────────────────────────────────────
		case 'team':
			$members = $d['members'] ?? array();
			if ( empty( $members ) ) break;
			$cols = max( 2, min( 4, (int) ( $d['cols'] ?? 3 ) ) );
			?>
			<?php echo ah_section_open( $d, 'section' ); ?>
				<div class="container">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div class="section__header text-center" style="margin-bottom:40px;" data-aos="fade-up">
							<h2 class=" "><?php echo esc_html( $d['heading'] ); ?></h2>
							<span class="ah-accent-bar"></span>
						</div>
					<?php endif; ?>
					<div class="grid-<?php echo $cols; ?>">
						<?php foreach ( $members as $i => $m ) : ?>
							<div class="ah-team-card" data-aos="fade-up" data-aos-delay="<?php echo $i * 80; ?>">
								<?php if ( ! empty( $m['photo'] ) ) : ?>
									<img src="<?php echo esc_url( $m['photo'] ); ?>"
									     alt="<?php echo esc_attr( $m['name'] ?? '' ); ?>"
									     class="ah-team-card__photo">
								<?php else : ?>
									<div class="ah-team-card__photo ah-team-card__photo--placeholder"><?php echo esc_html( mb_substr( $m['name'] ?? '?', 0, 1 ) ); ?></div>
								<?php endif; ?>
								<div class="ah-team-card__body">
									<?php if ( ! empty( $m['name'] ) ) : ?><div class="ah-team-card__name"><?php echo esc_html( $m['name'] ); ?></div><?php endif; ?>
									<?php if ( ! empty( $m['role'] ) ) : ?><div class="ah-team-card__role"><?php echo esc_html( $m['role'] ); ?></div><?php endif; ?>
									<?php if ( ! empty( $m['bio'] ) ) : ?><p class="ah-team-card__bio"><?php echo esc_html( $m['bio'] ); ?></p><?php endif; ?>
									<div class="ah-team-card__links">
										<?php if ( ! empty( $m['email'] ) ) : ?><a href="mailto:<?php echo esc_attr( $m['email'] ); ?>" class="ah-team-card__link">✉️</a><?php endif; ?>
										<?php if ( ! empty( $m['link'] ) ) : ?><a href="<?php echo esc_url( $m['link'] ); ?>" class="ah-team-card__link">→</a><?php endif; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;
	}
}
