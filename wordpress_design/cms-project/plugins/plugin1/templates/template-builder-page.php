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

function ah_render_builder_block( string $type, array $d ): void {

	switch ( $type ) {

		// ── Hero ──────────────────────────────────────────────────────────────
		case 'hero':
			$bg   = $d['bg'] ?? 'white';
			$dark = in_array( $bg, array( 'dark', 'gold' ), true );
			// Map bg key → section modifier class or fallback inline style
			$bg_class = '';
			$bg_style = '';
			if     ( $bg === 'dark' )                $bg_class = 'section--dark';
			elseif ( $bg === 'light' )               $bg_class = 'section--alt';
			elseif ( $bg === 'gold' )                $bg_style = 'background:#92400e;color:#fff;';
			elseif ( $bg === 'client-color-light' )  $bg_style = 'background:var(--client-color-50);';
			elseif ( $bg === 'client-color-medium' ) $bg_style = 'background:var(--client-color-400);';
			elseif ( $bg === 'client-color-dark' )   $bg_style = 'background:var(--client-color-700);color:#fff;';
			?>
			<section class="ah-block-hero section text-center <?php echo $bg_class; ?>"
			         <?php if ( $bg_style ) echo 'style="' . esc_attr( $bg_style ) . '"'; ?>>
				<div class="container container--md">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<div class="ph__bg" aria-hidden="true"><div class="ph__grid-lines"></div></div>
						<h1 class="section__title"><?php echo $d['heading']; ?></h1>
					<?php endif; ?>
					<?php if ( ! empty( $d['subheading'] ) ) : ?>
						<p class="section__desc"><?php echo $d['subheading']; ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $d['cta1_text'] ) || ! empty( $d['cta2_text'] ) ) : ?>
						<div style="display:flex;gap:14px;flex-wrap:wrap;justify-content:center;margin-top:36px;">
							<?php if ( ! empty( $d['cta1_text'] ) ) : ?>
								<a href="<?php echo esc_url( $d['cta1_url'] ?? '#' ); ?>" class="btn btn-gold">
									<?php echo esc_html( $d['cta1_text'] ); ?>
								</a>
							<?php endif; ?>
							<?php if ( ! empty( $d['cta2_text'] ) ) : ?>
								<a href="<?php echo esc_url( $d['cta2_url'] ?? '#' ); ?>"
								   class="btn <?php echo $dark ? 'btn-white btn-outline' : 'btn-outline'; ?>">
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
			<section class="section section--sm<?php echo $align === 'center' ? ' text-center' : ''; ?>">
				<div class="container container--sm">
					<?php if ( ! empty( $d['title'] ) ) : ?>
						<h2 class="section__title"><?php echo esc_html( $d['title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( ! empty( $d['subtitle'] ) ) : ?>
						<p class="section__desc"><?php echo esc_html( $d['subtitle'] ); ?></p>
					<?php endif; ?>
				</div>
			</section>
			<?php break;

		// ── Text block ────────────────────────────────────────────────────────
		case 'text_block':
			?>
			<section class="section section--sm">
				<div class="container container--sm">
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
			?>
			<section class="section">
				<div class="container">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<h2 class="section__title text-center" style="margin-bottom:36px;">
							<?php echo esc_html( $d['heading'] ); ?>
						</h2>
					<?php endif; ?>
					<div <?php if ( $grid_class ) echo 'class="' . $grid_class . '"'; ?>>
						<?php foreach ( $cards as $card ) : ?>
							<div class="card">
								<div class="card__body">
									<?php if ( ! empty( $card['icon'] ) ) : ?>
										<div style="font-size:2rem;margin-bottom:14px;"><?php echo esc_html( $card['icon'] ); ?></div>
									<?php endif; ?>
									<?php if ( ! empty( $card['title'] ) ) : ?>
										<h3 class="card__title"><?php echo esc_html( $card['title'] ); ?></h3>
									<?php endif; ?>
									<?php if ( ! empty( $card['text'] ) ) : ?>
										<p class="card__excerpt"><?php echo esc_html( $card['text'] ); ?></p>
									<?php endif; ?>
									<?php if ( ! empty( $card['link_url'] ) ) : ?>
										<a href="<?php echo esc_url( $card['link_url'] ); ?>" class="btn btn-ghost btn-sm">
											Learn more →
										</a>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		// ── CTA banner ────────────────────────────────────────────────────────
		case 'cta_banner':
			$cta_themes = array(
				'gold'  => array( 'bg' => '#92400e', 'text' => '#fff',    'btn1' => 'btn-gold',    'btn2' => 'btn-outline btn-white' ),
				'dark'  => array( 'bg' => '#0f172a', 'text' => '#fff',    'btn1' => 'btn-gold',    'btn2' => 'btn-outline btn-white' ),
				'blue'  => array( 'bg' => '#1d4ed8', 'text' => '#fff',    'btn1' => 'btn-white',   'btn2' => 'btn-outline btn-white' ),
				'light' => array( 'bg' => '#f1f5f9', 'text' => '#0f172a', 'btn1' => 'btn-primary', 'btn2' => 'btn-outline'          ),
			);
			$th = $cta_themes[ $d['theme'] ?? 'gold' ] ?? $cta_themes['gold'];
			?>
			<section class="section text-center"
			         style="background:<?php echo $th['bg']; ?>;color:<?php echo $th['text']; ?>;">
				<div class="container container--sm">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<h2 class="section__title" style="color:<?php echo $th['text']; ?>;">
							<?php echo esc_html( $d['heading'] ); ?>
						</h2>
					<?php endif; ?>
					<?php if ( ! empty( $d['text'] ) ) : ?>
						<p class="section__desc" style="opacity:.85;"><?php echo esc_html( $d['text'] ); ?></p>
					<?php endif; ?>
					<div style="display:flex;gap:14px;flex-wrap:wrap;justify-content:center;margin-top:32px;">
						<?php if ( ! empty( $d['btn1_text'] ) ) : ?>
							<a href="<?php echo esc_url( $d['btn1_url'] ?? '#' ); ?>"
							   class="btn <?php echo $th['btn1']; ?>">
								<?php echo esc_html( $d['btn1_text'] ); ?>
							</a>
						<?php endif; ?>
						<?php if ( ! empty( $d['btn2_text'] ) ) : ?>
							<a href="<?php echo esc_url( $d['btn2_url'] ?? '#' ); ?>"
							   class="btn <?php echo $th['btn2']; ?>">
								<?php echo esc_html( $d['btn2_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</section>
			<?php break;

		// ── Stats row ─────────────────────────────────────────────────────────
		case 'stats_row':
			$stats = $d['stats'] ?? array();
			if ( empty( $stats ) ) break;
			?>
			<section class="section section--alt">
				<div class="container" style="display:flex;flex-wrap:wrap;justify-content:center;gap:40px;">
					<?php foreach ( $stats as $stat ) : ?>
						<div class="text-center" style="min-width:140px;">
							<div style="font-size:2.8rem;font-weight:800;color:var(--slate-900,#0f172a);line-height:1;">
								<?php
								echo esc_html( $stat['prefix'] ?? '' );
								echo esc_html( $stat['number'] ?? '' );
								echo esc_html( $stat['suffix'] ?? '' );
								?>
							</div>
							<div style="font-size:.85rem;color:var(--text-secondary,#6b7280);margin-top:8px;font-weight:500;">
								<?php echo esc_html( $stat['label'] ?? '' ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
			<?php break;

		// ── FAQ accordion ─────────────────────────────────────────────────────
		// Uses existing theme .faq / .faq__q / .faq__a classes + main.js handler.
		case 'faq':
			$items = $d['items'] ?? array();
			if ( empty( $items ) ) break;
			?>
			<section class="section">
				<div class="container container--sm">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<h2 class="section__title text-center" style="margin-bottom:40px;">
							<?php echo esc_html( $d['heading'] ); ?>
						</h2>
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
			<section class="section section--sm">
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
			?>
			<section class="section">
				<div class="container container--md">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<h2 class="section__title" style="margin-bottom:28px;">
							<?php echo esc_html( $d['heading'] ); ?>
						</h2>
					<?php endif; ?>
					<div <?php if ( $grid_class ) echo 'class="' . $grid_class . '"'; ?>>
						<?php foreach ( $links as $lnk ) : ?>
							<a href="<?php echo esc_url( $lnk['url'] ?? '#' ); ?>"
							   class="card" style="display:flex;align-items:flex-start;gap:14px;padding:18px 20px;text-decoration:none;color:inherit;">
								<?php if ( ! empty( $lnk['icon'] ) ) : ?>
									<span style="font-size:1.4rem;flex-shrink:0;"><?php echo esc_html( $lnk['icon'] ); ?></span>
								<?php endif; ?>
								<div>
									<div class="card__title" style="margin-bottom:4px;"><?php echo esc_html( $lnk['label'] ?? '' ); ?></div>
									<?php if ( ! empty( $lnk['desc'] ) ) : ?>
										<div class="card__excerpt"><?php echo esc_html( $lnk['desc'] ); ?></div>
									<?php endif; ?>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		// ── Image + text ─────────────────────────────────────────────────────
		case 'image_text':
			$img_left = ( $d['layout'] ?? 'image-left' ) === 'image-left';
			?>
			<section class="section">
				<div class="container container--md"
				     style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center;<?php echo $img_left ? '' : 'direction:rtl;'; ?>">
					<div style="<?php echo $img_left ? '' : 'direction:ltr;'; ?>">
						<?php if ( ! empty( $d['image_url'] ) ) : ?>
							<img src="<?php echo esc_url( $d['image_url'] ); ?>"
							     alt="<?php echo esc_attr( $d['image_alt'] ?? '' ); ?>"
							     style="width:100%;border-radius:14px;display:block;">
						<?php else : ?>
							<div class="section--alt" style="height:300px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:.85rem;">
								Image placeholder
							</div>
						<?php endif; ?>
					</div>
					<div style="<?php echo $img_left ? '' : 'direction:ltr;'; ?>">
						<?php if ( ! empty( $d['heading'] ) ) : ?>
							<h2 class="section__title"><?php echo esc_html( $d['heading'] ); ?></h2>
						<?php endif; ?>
						<?php if ( ! empty( $d['text'] ) ) : ?>
							<p style="line-height:1.75;margin:0 0 28px;"><?php echo nl2br( esc_html( $d['text'] ) ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $d['btn_text'] ) ) : ?>
							<a href="<?php echo esc_url( $d['btn_url'] ?? '#' ); ?>" class="btn btn-gold">
								<?php echo esc_html( $d['btn_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</section>
			<?php break;
	}
}
