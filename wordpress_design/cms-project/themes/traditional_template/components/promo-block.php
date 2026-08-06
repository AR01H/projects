<?php
/**
 * components/promo-block.php - reusable "say this, link there" cards.
 *
 * Renders one or more entries from the SHARED library in
 * admin/data/blocks.json (see src/Content/class-blocks.php), so the same
 * wording can appear on five pages and still be edited in one place.
 *
 *   { "component": "promo-block", "args": { "block": "read_blogs" } }
 *   { "component": "promo-block", "args": { "blocks": ["read_blogs","visit_us","call_us"] } }
 *
 * Per-placement wording tweaks without touching the library:
 *   { "args": { "blocks": [ { "block": "visit_us", "label": "Find the counter" } ] } }
 *
 * A block whose JSON has `dialog` instead of `url` opens that dialog rather
 * than navigating - the same entry works as a link on one page and a popup
 * on another.
 *
 * Args:
 *   block   string|array  One library key (or an override array).
 *   blocks  array         Several keys / override arrays.
 *   tag     string        Section kicker above the cards.
 *   title   string        Section heading (a single <em> allowed).
 *   sub     string        Lead paragraph.
 *   layout  string        'cards' (default) | 'rows' | 'single'
 */

defined( 'ABSPATH' ) || exit;

$nt_blocks = NT_Blocks::resolve(
	isset( $block ) ? $block : null,
	isset( $blocks ) ? $blocks : null
);
if ( empty( $nt_blocks ) ) {
	return;
}

$nt_layout = in_array( ( $layout ?? '' ), array( 'cards', 'rows', 'single' ), true ) ? $layout : 'cards';
$nt_tag    = $tag   ?? '';
$nt_title  = $title ?? '';
$nt_sub    = $sub   ?? '';
?>
<section class="nt-promo nt-promo--<?php echo esc_attr( $nt_layout ); ?>">
	<div class="container">

		<?php if ( $nt_tag || $nt_title || $nt_sub ) : ?>
			<div class="nt-section-center">
				<?php if ( $nt_tag ) : ?><div class="nt-section-tag"><?php echo esc_html( $nt_tag ); ?></div><?php endif; ?>
				<?php if ( $nt_title ) : ?><h2 class="section-title"><?php echo wp_kses( $nt_title, array( 'em' => array() ) ); ?></h2><?php endif; ?>
				<?php if ( $nt_sub ) : ?><p class="section-body"><?php echo esc_html( $nt_sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="nt-promo__grid">
			<?php foreach ( $nt_blocks as $nt_b ) : ?>
				<article class="nt-promo__card">

					<?php if ( '' !== $nt_b['image'] ) : ?>
						<figure class="nt-promo__figure">
							<img src="<?php echo esc_url( nt_link( $nt_b['image'] ) ); ?>" alt="" loading="lazy" decoding="async">
						</figure>
					<?php elseif ( '' !== $nt_b['icon'] ) : ?>
						<span class="nt-promo__icon" aria-hidden="true">
							<?php echo NT_Icons::get_or_text( $nt_b['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
						</span>
					<?php endif; ?>

					<?php if ( '' !== $nt_b['tag'] ) : ?>
						<span class="nt-promo__tag"><?php echo esc_html( $nt_b['tag'] ); ?></span>
					<?php endif; ?>

					<?php if ( '' !== $nt_b['title'] ) : ?>
						<h3 class="nt-promo__title"><?php echo esc_html( $nt_b['title'] ); ?></h3>
					<?php endif; ?>

					<?php if ( '' !== $nt_b['text'] ) : ?>
						<p class="nt-promo__text"><?php echo esc_html( $nt_b['text'] ); ?></p>
					<?php endif; ?>

					<?php if ( '' !== $nt_b['label'] ) : ?>
						<?php if ( '' !== $nt_b['dialog'] && NT_Dialog::exists( $nt_b['dialog'] ) ) : ?>
							<button class="nt-promo__cta" <?php nt_dialog_trigger( $nt_b['dialog'] ); ?>>
								<?php echo esc_html( $nt_b['label'] ); ?>
								<?php NT_Icons::render( 'arrow-right' ); ?>
							</button>
						<?php elseif ( '' !== $nt_b['url'] ) : ?>
							<a class="nt-promo__cta" href="<?php echo esc_url( nt_link( $nt_b['url'] ) ); ?>"
								<?php if ( $nt_b['new_tab'] ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
								<?php echo esc_html( $nt_b['label'] ); ?>
								<?php NT_Icons::render( 'arrow-right' ); ?>
							</a>
						<?php endif; ?>
					<?php endif; ?>

				</article>
			<?php endforeach; ?>
		</div>

	</div>
</section>
