<?php
/**
 * components/paper-story.php - the "stack of papers" story.
 *
 * A pile of aged sheets on a desk. The top one lifts and slides away to
 * reveal the next, one page at a time - the way you would actually read a
 * bundle of old documents. Click the sheet, use the arrows, the dots, arrow
 * keys or a swipe.
 *
 * GENERIC: it is just an ordered list of pages, so it suits a company
 * history, a how-it-is-made walkthrough, a recipe, an onboarding sequence or
 * a set of terms. Nothing about it is specific to one business.
 *
 * Data: admin/data/<source>.json (default `paper_story`)
 *   {
 *     "tag": "…", "title": "…<em>…</em>", "sub": "…",
 *     "autoplay": 0,                       // ms between turns, 0 = manual
 *     "sheets": [
 *       { "chapter": "01", "year": "1985", "title": "…", "text": "…",
 *         "quote": "…", "image": "…", "image_alt": "…", "stamp": "…",
 *         "link_label": "…", "link_url": "…" }
 *     ]
 *   }
 *
 * With JS off every sheet is visible in order, so the story still reads.
 *
 * Args:
 *   source  string  Which JSON file to read (default: paper_story).
 */

defined( 'ABSPATH' ) || exit;

$nt_src  = ( isset( $source ) && $source ) ? (string) $source : 'paper_story';
$nt_data = nt_data( $nt_src );

$nt_sheets = array();
foreach ( (array) ( $nt_data['sheets'] ?? array() ) as $nt_sheet ) {
	$nt_sheet = (array) $nt_sheet;
	if ( '' !== trim( (string) ( $nt_sheet['title'] ?? '' ) ) || '' !== trim( (string) ( $nt_sheet['text'] ?? '' ) ) ) {
		$nt_sheets[] = $nt_sheet;
	}
}
if ( empty( $nt_sheets ) ) {
	return;
}

$nt_tag      = (string) ( $nt_data['tag'] ?? '' );
$nt_title    = (string) ( $nt_data['title'] ?? '' );
$nt_sub      = (string) ( $nt_data['sub'] ?? '' );
$nt_autoplay = max( 0, (int) ( $nt_data['autoplay'] ?? 0 ) );
$nt_total    = count( $nt_sheets );
?>
<section class="nt-story" id="<?php echo esc_attr( sanitize_html_class( $nt_src ) ); ?>">
	<div class="container">

		<?php if ( $nt_tag || $nt_title || $nt_sub ) : ?>
			<div class="nt-section-center">
				<?php if ( $nt_tag ) : ?><div class="nt-section-tag"><?php echo esc_html( $nt_tag ); ?></div><?php endif; ?>
				<?php if ( $nt_title ) : ?><h2 class="section-title"><?php echo wp_kses( $nt_title, array( 'em' => array() ) ); ?></h2><?php endif; ?>
				<?php if ( $nt_sub ) : ?><p class="section-body"><?php echo esc_html( $nt_sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="nt-story__desk"
		     data-nt-story
		     data-nt-story-autoplay="<?php echo esc_attr( (string) $nt_autoplay ); ?>"
		     tabindex="0"
		     role="group"
		     aria-roledescription="carousel"
		     aria-label="<?php echo esc_attr( NT_Ui::aria( 'story', 'Story pages' ) ); ?>">

			<div class="nt-story__stack">
				<?php foreach ( $nt_sheets as $nt_i => $nt_sheet ) : ?>
					<article class="nt-story__sheet" data-nt-story-sheet
					         style="--nt-sheet-i:<?php echo esc_attr( (string) $nt_i ); ?>;">
						<div class="nt-story__paper">

							<header class="nt-story__head">
								<?php if ( ! empty( $nt_sheet['chapter'] ) ) : ?>
									<span class="nt-story__chapter"><?php echo esc_html( $nt_sheet['chapter'] ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $nt_sheet['year'] ) ) : ?>
									<span class="nt-story__year"><?php echo esc_html( $nt_sheet['year'] ); ?></span>
								<?php endif; ?>
							</header>

							<div class="nt-story__cols">
								<?php if ( ! empty( $nt_sheet['image'] ) ) : ?>
									<figure class="nt-story__photo">
										<img src="<?php echo esc_url( nt_link( $nt_sheet['image'] ) ); ?>"
										     alt="<?php echo esc_attr( $nt_sheet['image_alt'] ?? '' ); ?>"
										     loading="lazy" decoding="async">
										<?php if ( ! empty( $nt_sheet['stamp'] ) ) : ?>
											<figcaption class="nt-story__stamp"><?php echo esc_html( $nt_sheet['stamp'] ); ?></figcaption>
										<?php endif; ?>
									</figure>
								<?php endif; ?>

								<div class="nt-story__copy">
									<?php if ( ! empty( $nt_sheet['title'] ) ) : ?>
										<h3 class="nt-story__title"><?php echo esc_html( $nt_sheet['title'] ); ?></h3>
									<?php endif; ?>

									<?php if ( ! empty( $nt_sheet['text'] ) ) : ?>
										<?php foreach ( (array) $nt_sheet['text'] as $nt_para ) : ?>
											<p class="nt-story__text"><?php echo esc_html( $nt_para ); ?></p>
										<?php endforeach; ?>
									<?php endif; ?>

									<?php if ( ! empty( $nt_sheet['quote'] ) ) : ?>
										<blockquote class="nt-story__quote">
											<?php NT_Icons::render( 'quote', 'nt-story__quote-mark' ); ?>
											<span><?php echo esc_html( $nt_sheet['quote'] ); ?></span>
										</blockquote>
									<?php endif; ?>

									<?php if ( ! empty( $nt_sheet['link_label'] ) && ! empty( $nt_sheet['link_url'] ) ) : ?>
										<a class="nt-story__link" href="<?php echo esc_url( nt_link( $nt_sheet['link_url'] ) ); ?>">
											<?php echo esc_html( $nt_sheet['link_label'] ); ?>
											<?php NT_Icons::render( 'arrow-right' ); ?>
										</a>
									<?php endif; ?>
								</div>
							</div>

							<span class="nt-story__pin" aria-hidden="true"></span>
							<span class="nt-story__fold" aria-hidden="true"></span>
						</div>
					</article>
				<?php endforeach; ?>
			</div>

			<div class="nt-story__controls">
				<button type="button" class="nt-story__nav" data-nt-story-prev
				        aria-label="<?php echo esc_attr( NT_Ui::aria( 'prev_page', NT_Ui::label( 'previous' ) ) ); ?>">
					<?php NT_Icons::render( 'chevron-left' ); ?>
				</button>

				<div class="nt-story__dots">
					<?php foreach ( $nt_sheets as $nt_i => $nt_sheet ) : ?>
						<button type="button" class="nt-story__dot" data-nt-story-dot
						        aria-label="<?php echo esc_attr( trim( (string) ( $nt_sheet['chapter'] ?? '' ) . ' ' . (string) ( $nt_sheet['title'] ?? ( $nt_i + 1 ) ) ) ); ?>"></button>
					<?php endforeach; ?>
				</div>

				<span class="nt-story__counter" data-nt-story-counter aria-live="polite">1 / <?php echo esc_html( (string) $nt_total ); ?></span>

				<button type="button" class="nt-story__nav" data-nt-story-next
				        aria-label="<?php echo esc_attr( NT_Ui::aria( 'next_page', NT_Ui::label( 'next' ) ) ); ?>">
					<?php NT_Icons::render( 'chevron-right' ); ?>
				</button>
			</div>

		</div>
	</div>
</section>
