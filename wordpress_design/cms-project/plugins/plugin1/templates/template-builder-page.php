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

// Override <title> and meta description
add_filter( 'pre_get_document_title', fn() => esc_html( $title ) . ' | ' . get_bloginfo( 'name' ) );
add_action( 'wp_head', function() use ( $desc ) {
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
} );

get_header();
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

<?php get_footer(); ?>
<?php

// ── Block renderers ────────────────────────────────────────────────────────────

function ah_render_builder_block( string $type, array $d ): void {
	switch ( $type ) {

		case 'hero':
			$bg_map = array( 'dark' => '#1e293b', 'gold' => '#92400e', 'light' => '#f8fafc', 'white' => '#ffffff' );
			$bg     = $bg_map[ $d['bg'] ?? 'white' ] ?? '#ffffff';
			$dark   = in_array( $d['bg'] ?? '', array( 'dark', 'gold' ), true );
			?>
			<section class="ah-block-hero" style="background:<?php echo esc_attr( $bg ); ?>;padding:80px 20px 20px 20px;text-align:center;<?php echo $dark ? 'color:#fff;' : ''; ?>">
				<div style="max-width:760px;margin:0 auto;">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<h1 style="font-size:clamp(2rem,5vw,3.2rem);font-weight:700;margin:0 0 20px;<?php echo $dark ? 'color:#fff;' : ''; ?>">
							<?php echo esc_html( $d['heading'] ); ?>
						</h1>
					<?php endif; ?>
					<?php if ( ! empty( $d['subheading'] ) ) : ?>
						<p style="font-size:1.15rem;opacity:.85;margin:0 0 36px;max-width:600px;margin-left:auto;margin-right:auto;">
							<?php echo esc_html( $d['subheading'] ); ?>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $d['cta1_text'] ) || ! empty( $d['cta2_text'] ) ) : ?>
						<div style="display:flex;gap:14px;flex-wrap:wrap;justify-content:center;">
							<?php if ( ! empty( $d['cta1_text'] ) ) : ?>
								<a href="<?php echo esc_url( $d['cta1_url'] ?? '#' ); ?>"
								   style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;background:#c9a84c;color:#1a0a00;font-weight:700;border-radius:8px;text-decoration:none;font-size:.95rem;">
									<?php echo esc_html( $d['cta1_text'] ); ?>
								</a>
							<?php endif; ?>
							<?php if ( ! empty( $d['cta2_text'] ) ) : ?>
								<a href="<?php echo esc_url( $d['cta2_url'] ?? '#' ); ?>"
								   style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;background:transparent;color:<?php echo $dark ? '#fff' : '#1e293b'; ?>;font-weight:600;border-radius:8px;text-decoration:none;font-size:.95rem;border:2px solid currentColor;">
									<?php echo esc_html( $d['cta2_text'] ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>
			<?php break;

		case 'section_heading':
			$align = $d['align'] ?? 'center';
			?>
			<section style="padding:48px 20px 24px;text-align:<?php echo esc_attr( $align ); ?>;">
				<div style="max-width:800px;margin:0 auto;">
					<?php if ( ! empty( $d['title'] ) ) : ?>
						<h2 style="font-size:clamp(1.5rem,3.5vw,2.2rem);font-weight:700;margin:0 0 10px;">
							<?php echo esc_html( $d['title'] ); ?>
						</h2>
					<?php endif; ?>
					<?php if ( ! empty( $d['subtitle'] ) ) : ?>
						<p style="font-size:1.05rem;color:#6b7280;margin:0;">
							<?php echo esc_html( $d['subtitle'] ); ?>
						</p>
					<?php endif; ?>
				</div>
			</section>
			<?php break;

		case 'text_block':
			?>
			<section style="padding:32px 20px;">
				<div style="max-width:800px;margin:0 auto;line-height:1.75;font-size:1rem;color:#374151;">
					<?php echo wp_kses_post( $d['content'] ?? '' ); ?>
				</div>
			</section>
			<?php break;

		case 'spacer':
			$h = max( 10, min( 200, (int) ( $d['height'] ?? 40 ) ) );
			echo '<div style="height:' . $h . 'px;"></div>';
			break;

		case 'cards':
			$cards = $d['cards'] ?? array();
			$cols  = max( 1, min( 4, (int) ( $d['cols'] ?? 3 ) ) );
			?>
			<section style="padding:48px 20px;">
				<div style="max-width:1100px;margin:0 auto;">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<h2 style="text-align:center;font-size:1.9rem;font-weight:700;margin:0 0 36px;">
							<?php echo esc_html( $d['heading'] ); ?>
						</h2>
					<?php endif; ?>
					<div style="display:grid;grid-template-columns:repeat(<?php echo $cols; ?>,1fr);gap:24px;">
						<?php foreach ( $cards as $card ) : ?>
							<div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;padding:28px;transition:box-shadow .2s;"
							     onmouseenter="this.style.boxShadow='0 8px 30px rgba(0,0,0,.1)'"
							     onmouseleave="this.style.boxShadow='none'">
								<?php if ( ! empty( $card['icon'] ) ) : ?>
									<div style="font-size:2rem;margin-bottom:14px;"><?php echo esc_html( $card['icon'] ); ?></div>
								<?php endif; ?>
								<?php if ( ! empty( $card['title'] ) ) : ?>
									<h3 style="font-size:1.05rem;font-weight:700;margin:0 0 8px;"><?php echo esc_html( $card['title'] ); ?></h3>
								<?php endif; ?>
								<?php if ( ! empty( $card['text'] ) ) : ?>
									<p style="color:#6b7280;font-size:.9rem;line-height:1.6;margin:0 0 14px;"><?php echo esc_html( $card['text'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $card['link_url'] ) ) : ?>
									<a href="<?php echo esc_url( $card['link_url'] ); ?>"
									   style="font-size:.85rem;font-weight:600;color:#c9a84c;text-decoration:none;">
										Learn more →
									</a>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		case 'cta_banner':
			$theme_map = array(
				'gold'  => array( 'bg' => '#92400e', 'text' => '#fff',     'btn1' => '#c9a84c', 'btn1t' => '#1a0a00' ),
				'dark'  => array( 'bg' => '#0f172a', 'text' => '#fff',     'btn1' => '#c9a84c', 'btn1t' => '#0f172a' ),
				'blue'  => array( 'bg' => '#1d4ed8', 'text' => '#fff',     'btn1' => '#fff',    'btn1t' => '#1d4ed8' ),
				'light' => array( 'bg' => '#f1f5f9', 'text' => '#0f172a',  'btn1' => '#0f172a', 'btn1t' => '#fff'    ),
			);
			$th = $theme_map[ $d['theme'] ?? 'gold' ] ?? $theme_map['gold'];
			?>
			<section style="background:<?php echo $th['bg']; ?>;padding:64px 20px;text-align:center;color:<?php echo $th['text']; ?>;">
				<div style="max-width:700px;margin:0 auto;">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<h2 style="font-size:clamp(1.6rem,4vw,2.4rem);font-weight:700;margin:0 0 16px;color:<?php echo $th['text']; ?>;">
							<?php echo esc_html( $d['heading'] ); ?>
						</h2>
					<?php endif; ?>
					<?php if ( ! empty( $d['text'] ) ) : ?>
						<p style="font-size:1rem;opacity:.85;margin:0 0 32px;"><?php echo esc_html( $d['text'] ); ?></p>
					<?php endif; ?>
					<div style="display:flex;gap:14px;flex-wrap:wrap;justify-content:center;">
						<?php if ( ! empty( $d['btn1_text'] ) ) : ?>
							<a href="<?php echo esc_url( $d['btn1_url'] ?? '#' ); ?>"
							   style="padding:14px 28px;background:<?php echo $th['btn1']; ?>;color:<?php echo $th['btn1t']; ?>;font-weight:700;border-radius:8px;text-decoration:none;font-size:.95rem;">
								<?php echo esc_html( $d['btn1_text'] ); ?>
							</a>
						<?php endif; ?>
						<?php if ( ! empty( $d['btn2_text'] ) ) : ?>
							<a href="<?php echo esc_url( $d['btn2_url'] ?? '#' ); ?>"
							   style="padding:14px 28px;background:transparent;color:<?php echo $th['text']; ?>;font-weight:600;border-radius:8px;text-decoration:none;font-size:.95rem;border:2px solid <?php echo $th['text']; ?>;">
								<?php echo esc_html( $d['btn2_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</section>
			<?php break;

		case 'stats_row':
			$stats = $d['stats'] ?? array();
			if ( empty( $stats ) ) break;
			?>
			<section style="padding:56px 20px;background:#f8fafc;">
				<div style="max-width:1000px;margin:0 auto;display:flex;flex-wrap:wrap;justify-content:center;gap:40px;">
					<?php foreach ( $stats as $stat ) : ?>
						<div style="text-align:center;min-width:140px;">
							<div style="font-size:2.8rem;font-weight:800;color:#0f172a;line-height:1;">
								<?php
								echo esc_html( $stat['prefix'] ?? '' );
								echo esc_html( $stat['number'] ?? '' );
								echo esc_html( $stat['suffix'] ?? '' );
								?>
							</div>
							<div style="font-size:.85rem;color:#6b7280;margin-top:8px;font-weight:500;">
								<?php echo esc_html( $stat['label'] ?? '' ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
			<?php break;

		case 'faq':
			$items = $d['items'] ?? array();
			if ( empty( $items ) ) break;
			static $faq_id = 0; $faq_id++;
			?>
			<section class="faq-section" style="padding:56px 20px;">
				<div style="max-width:780px;margin:0 auto;">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<h2 style="text-align:center;font-size:1.9rem;font-weight:700;margin:0 0 40px;">
							<?php echo esc_html( $d['heading'] ); ?>
						</h2>
					<?php endif; ?>
					<?php foreach ( $items as $i => $item ) : ?>
						<div class="faq-item" style="border:1.5px solid #e5e7eb;border-radius:10px;margin-bottom:10px;overflow:hidden;">
							<button class="faq-trigger"
							        style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:18px 22px;background:#fff;border:none;cursor:pointer;text-align:left;font-size:.95rem;font-weight:600;color:#0f172a;"
							        aria-expanded="false">
								<span><?php echo esc_html( $item['q'] ?? '' ); ?></span>
								<span class="faq-arrow" style="flex-shrink:0;margin-left:16px;font-size:1.2rem;transition:transform .25s;">▼</span>
							</button>
							<div class="faq-answer"
							     style="display:none;padding:0 22px 18px;color:#374151;font-size:.9rem;line-height:1.75;">
								<?php echo nl2br( esc_html( $item['a'] ?? '' ) ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
			<script>
			(function(){
				var items = document.querySelectorAll('.faq-section .faq-item');
				items.forEach(function(item){
					item.querySelector('.faq-trigger').addEventListener('click', function(){
						var open = item.classList.toggle('is-open');
						item.querySelector('.faq-answer').style.display = open ? 'block' : 'none';
						this.setAttribute('aria-expanded', open ? 'true' : 'false');
						var arrow = item.querySelector('.faq-arrow');
						if(arrow) arrow.style.transform = open ? 'rotate(180deg)' : '';
					});
				});
			})();
			</script>
			<?php break;

		case 'button_row':
			$buttons = $d['buttons'] ?? array();
			if ( empty( $buttons ) ) break;
			$align = $d['align'] ?? 'center';
			$style_map = array(
				'primary'   => 'background:#0f172a;color:#fff;border:2px solid #0f172a;',
				'secondary' => 'background:#f1f5f9;color:#0f172a;border:2px solid #e2e8f0;',
				'outline'   => 'background:transparent;color:#0f172a;border:2px solid #0f172a;',
				'gold'      => 'background:#c9a84c;color:#1a0a00;border:2px solid #c9a84c;',
			);
			?>
			<section style="padding:32px 20px;">
				<div style="display:flex;flex-wrap:wrap;gap:14px;justify-content:<?php echo esc_attr( $align ); ?>;">
					<?php foreach ( $buttons as $btn ) : ?>
						<?php $bs = $style_map[ $btn['style'] ?? 'primary' ] ?? $style_map['primary']; ?>
						<a href="<?php echo esc_url( $btn['url'] ?? '#' ); ?>"
						   style="display:inline-flex;align-items:center;padding:13px 26px;border-radius:8px;font-weight:600;font-size:.9rem;text-decoration:none;<?php echo $bs; ?>">
							<?php echo esc_html( $btn['text'] ?? 'Click Here' ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
			<?php break;

		case 'links_list':
			$links = $d['links'] ?? array();
			if ( empty( $links ) ) break;
			$cols = max( 1, min( 3, (int) ( $d['cols'] ?? 2 ) ) );
			?>
			<section style="padding:48px 20px;">
				<div style="max-width:1000px;margin:0 auto;">
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<h2 style="font-size:1.7rem;font-weight:700;margin:0 0 28px;">
							<?php echo esc_html( $d['heading'] ); ?>
						</h2>
					<?php endif; ?>
					<div style="display:grid;grid-template-columns:repeat(<?php echo $cols; ?>,1fr);gap:16px;">
						<?php foreach ( $links as $lnk ) : ?>
							<a href="<?php echo esc_url( $lnk['url'] ?? '#' ); ?>"
							   style="display:flex;align-items:flex-start;gap:14px;padding:18px;background:#fff;border:1.5px solid #e5e7eb;border-radius:10px;text-decoration:none;color:inherit;transition:box-shadow .2s;"
							   onmouseenter="this.style.boxShadow='0 4px 16px rgba(0,0,0,.08)'"
							   onmouseleave="this.style.boxShadow='none'">
								<?php if ( ! empty( $lnk['icon'] ) ) : ?>
									<span style="font-size:1.4rem;flex-shrink:0;"><?php echo esc_html( $lnk['icon'] ); ?></span>
								<?php endif; ?>
								<div>
									<div style="font-weight:600;font-size:.9rem;color:#0f172a;margin-bottom:4px;">
										<?php echo esc_html( $lnk['label'] ?? '' ); ?>
									</div>
									<?php if ( ! empty( $lnk['desc'] ) ) : ?>
										<div style="font-size:.8rem;color:#6b7280;line-height:1.5;">
											<?php echo esc_html( $lnk['desc'] ); ?>
										</div>
									<?php endif; ?>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php break;

		case 'image_text':
			$img_left = ( $d['layout'] ?? 'image-left' ) === 'image-left';
			?>
			<section style="padding:56px 20px;">
				<div style="max-width:1000px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center;<?php echo $img_left ? '' : 'direction:rtl;'; ?>">
					<div style="<?php echo $img_left ? '' : 'direction:ltr;'; ?>">
						<?php if ( ! empty( $d['image_url'] ) ) : ?>
							<img src="<?php echo esc_url( $d['image_url'] ); ?>"
							     alt="<?php echo esc_attr( $d['image_alt'] ?? '' ); ?>"
							     style="width:100%;border-radius:14px;display:block;">
						<?php else : ?>
							<div style="width:100%;height:300px;background:#f1f5f9;border-radius:14px;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:.85rem;">Image placeholder</div>
						<?php endif; ?>
					</div>
					<div style="<?php echo $img_left ? '' : 'direction:ltr;'; ?>">
						<?php if ( ! empty( $d['heading'] ) ) : ?>
							<h2 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:700;margin:0 0 18px;"><?php echo esc_html( $d['heading'] ); ?></h2>
						<?php endif; ?>
						<?php if ( ! empty( $d['text'] ) ) : ?>
							<p style="color:#374151;line-height:1.75;margin:0 0 28px;"><?php echo nl2br( esc_html( $d['text'] ) ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $d['btn_text'] ) ) : ?>
							<a href="<?php echo esc_url( $d['btn_url'] ?? '#' ); ?>"
							   style="display:inline-flex;padding:13px 26px;background:#c9a84c;color:#1a0a00;font-weight:700;border-radius:8px;text-decoration:none;font-size:.9rem;">
								<?php echo esc_html( $d['btn_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</section>
			<?php break;
	}
}
