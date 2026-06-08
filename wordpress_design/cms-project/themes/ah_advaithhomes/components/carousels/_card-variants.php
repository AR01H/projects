<?php
/**
 * Card Variants for Carousels
 * Multiple professional designs for different content types
 *
 * Types: image-overlay, image-split, stat, testimonial, minimal, feature-detailed
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'cc_render_card_variant' ) ) {
	function cc_render_card_variant( array $item, string $variant ): string {
		switch ( $variant ) {

			/* ── Image Overlay: Image with text overlay ─────────────────── */
			case 'image-overlay':
				$img_url = esc_url( $item['image'] ?? '' );
				$img_alt = esc_attr( $item['title'] ?? '' );
				$title   = esc_html( $item['title'] ?? '' );
				$text    = esc_html( $item['text'] ?? '' );
				$tag     = esc_html( $item['tag'] ?? '' );

				return sprintf(
					'<div class="cc-card cc-card--img-overlay">
						<img src="%s" alt="%s" class="cc-card__bg-img" loading="lazy">
						<div class="cc-card__overlay-dark"></div>
						<div class="cc-card__content">
							%s
							<h3 class="cc-card__title">%s</h3>
							<p class="cc-card__text">%s</p>
						</div>
					</div>',
					$img_url, $img_alt,
					$tag ? '<span class="cc-card__tag">' . $tag . '</span>' : '',
					$title, $text
				);

			/* ── Image Split: Image left, content right ──────────────────── */
			case 'image-split':
				$img_url = esc_url( $item['image'] ?? '' );
				$img_alt = esc_attr( $item['title'] ?? '' );
				$title   = esc_html( $item['title'] ?? '' );
				$text    = esc_html( $item['text'] ?? '' );
				$checks  = (array) ( $item['checklist'] ?? [] );

				$check_html = '';
				if ( $checks ) {
					$check_html = '<ul class="cc-card__list">';
					foreach ( $checks as $c ) {
						$check_html .= '<li class="cc-card__list-item">' . esc_html( $c ) . '</li>';
					}
					$check_html .= '</ul>';
				}

				return sprintf(
					'<div class="cc-card cc-card--split">
						<div class="cc-card__media">
							<img src="%s" alt="%s" loading="lazy">
						</div>
						<div class="cc-card__body">
							<h3 class="cc-card__title">%s</h3>
							<p class="cc-card__text">%s</p>
							%s
						</div>
					</div>',
					$img_url, $img_alt, $title, $text, $check_html
				);

			/* ── Stat: Large number + label + description ──────────────── */
			case 'stat':
				$stat      = esc_html( $item['stat'] ?? '100' );
				$stat_label = esc_html( $item['stat_label'] ?? 'Metric' );
				$title     = esc_html( $item['title'] ?? '' );
				$text      = esc_html( $item['text'] ?? '' );
				$icon      = $item['icon'] ?? '';

				return sprintf(
					'<div class="cc-card cc-card--stat">
						%s
						<div class="cc-card__stat-number">%s</div>
						<div class="cc-card__stat-label">%s</div>
						<h3 class="cc-card__title">%s</h3>
						<p class="cc-card__text">%s</p>
					</div>',
					$icon ? '<div class="cc-card__icon">' . esc_html( $icon ) . '</div>' : '',
					$stat, $stat_label, $title, $text
				);

			/* ── Testimonial: Quote + author + rating ───────────────────── */
			case 'testimonial':
				$quote  = esc_html( $item['quote'] ?? '' );
				$author = esc_html( $item['author'] ?? 'Customer' );
				$role   = esc_html( $item['role'] ?? '' );
				$rating = (int) ( $item['rating'] ?? 5 );

				$stars = '';
				for ( $i = 1; $i <= 5; $i++ ) {
					$stars .= '<span class="cc-star' . ( $i <= $rating ? ' cc-star--fill' : '' ) . '">★</span>';
				}

				return sprintf(
					'<div class="cc-card cc-card--testimonial">
						<div class="cc-card__quote-mark">"</div>
						<p class="cc-card__quote">%s</p>
						<div class="cc-card__stars">%s</div>
						<div class="cc-card__author">
							<div class="cc-card__author-name">%s</div>
							%s
						</div>
					</div>',
					$quote, $stars, $author,
					$role ? '<div class="cc-card__author-role">' . $role . '</div>' : ''
				);

			/* ── Minimal: Just title + description (clean) ────────────── */
			case 'minimal':
				$title = esc_html( $item['title'] ?? '' );
				$text  = esc_html( $item['text'] ?? '' );

				return sprintf(
					'<div class="cc-card cc-card--minimal">
						<h3 class="cc-card__title">%s</h3>
						<p class="cc-card__text">%s</p>
					</div>',
					$title, $text
				);

			/* ── Feature Detailed: Icon + title + text + checklist ────── */
			case 'feature-detailed':
				$icon      = $item['icon'] ?? '';
				$icon_type = $item['icon_type'] ?? 'emoji';
				$title     = esc_html( $item['title'] ?? '' );
				$text      = esc_html( $item['text'] ?? '' );
				$checks    = (array) ( $item['checklist'] ?? [] );

				$icon_html = '';
				if ( $icon ) {
					if ( $icon_type === 'img' ) {
						$icon_html = '<div class="cc-card__icon-wrap"><img src="' . esc_url( $icon ) . '" alt=""></div>';
					} else {
						$icon_html = '<div class="cc-card__icon-wrap">' . esc_html( $icon ) . '</div>';
					}
				}

				$check_html = '';
				if ( $checks ) {
					$check_html = '<ul class="cc-card__list">';
					foreach ( $checks as $c ) {
						$check_html .= '<li class="cc-card__list-item">' . esc_html( $c ) . '</li>';
					}
					$check_html .= '</ul>';
				}

				return sprintf(
					'<div class="cc-card cc-card--feature-detail">%s<h3 class="cc-card__title">%s</h3><p class="cc-card__text">%s</p>%s</div>',
					$icon_html, $title, $text, $check_html
				);

			default:
				return '';
		}
	}
}
?>
<style>
/* ── Image Overlay Card ──────────────────────────────────────── */
.cc-card--img-overlay {
	position: relative;
	overflow: hidden;
	min-height: 360px;
	display: flex;
	align-items: flex-end;
}

.cc-card--img-overlay .cc-card__bg-img {
	position: absolute;
	inset: 0;
	width: 100%;
	height: 100%;
	object-fit: cover;
	z-index: 1;
}

.cc-card--img-overlay .cc-card__overlay-dark {
	position: absolute;
	inset: 0;
	background: linear-gradient(135deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.6) 100%);
	z-index: 2;
}

.cc-card--img-overlay .cc-card__content {
	position: relative;
	z-index: 3;
	padding: 32px 24px;
	color: #fff;
}

.cc-card--img-overlay .cc-card__tag {
	display: inline-block;
	padding: 6px 12px;
	background: var(--client-color-7);
	border-radius: 4px;
	font-size: 0.75rem;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin-bottom: 12px;
}

.cc-card--img-overlay .cc-card__title {
	color: #fff;
	margin-bottom: 8px;
}

.cc-card--img-overlay .cc-card__text {
	color: rgba(255,255,255,0.95);
}

/* ── Image Split Card ────────────────────────────────────────── */
.cc-card--split {
	display: flex;
	min-height: 300px;
	overflow: hidden;
}

.cc-card--split .cc-card__media {
	flex: 0 0 45%;
	overflow: hidden;
}

.cc-card--split .cc-card__media img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.cc-card--split .cc-card__body {
	flex: 1;
	padding: 32px 28px;
	display: flex;
	flex-direction: column;
	justify-content: center;
}

.cc-card__list {
	list-style: none;
	padding: 0;
	margin: 16px 0 0;
}

.cc-card__list-item {
	padding: 8px 0 8px 28px;
	position: relative;
	color: var(--client-color-1);
	font-size: 0.95rem;
	line-height: 1.5;
}

.cc-card__list-item::before {
	content: '✓';
	position: absolute;
	left: 0;
	color: var(--client-color-7);
	font-weight: 700;
	font-size: 1.2rem;
}

/* ── Stat Card ──────────────────────────────────────────────── */
.cc-card--stat {
	padding: 32px 24px;
	display: flex;
	flex-direction: column;
	align-items: center;
	text-align: center;
	justify-content: center;
	min-height: 320px;
}

.cc-card--stat .cc-card__icon {
	font-size: 3.5rem;
	margin-bottom: 16px;
	line-height: 1;
}

.cc-card__stat-number {
	font-size: 3.5rem;
	font-weight: 800;
	color: var(--client-color-7);
	line-height: 1;
	margin-bottom: 4px;
}

.cc-card__stat-label {
	font-size: 0.95rem;
	color: var(--client-color-16);
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin-bottom: 16px;
}

.cc-card--stat .cc-card__title {
	font-size: 1.15rem;
	margin-bottom: 8px;
}

/* ── Testimonial Card ───────────────────────────────────────── */
.cc-card--testimonial {
	padding: 32px 24px;
	display: flex;
	flex-direction: column;
	min-height: 300px;
	justify-content: space-between;
}

.cc-card__quote-mark {
	font-size: 3rem;
	color: var(--client-color-7);
	line-height: 1;
	opacity: 0.8;
	margin-bottom: 8px;
}

.cc-card__quote {
	font-size: 1rem;
	font-style: italic;
	line-height: 1.7;
	color: var(--client-color-1);
	margin: 0 0 20px;
	flex: 1;
}

.cc-card__stars {
	display: flex;
	gap: 4px;
	margin-bottom: 16px;
	font-size: 1.2rem;
}

.cc-star {
	color: var(--client-color-4);
}

.cc-star--fill {
	color: var(--client-color-7);
}

.cc-card__author {
	text-align: left;
	padding-top: 16px;
	border-top: 1px solid var(--client-color-4);
}

.cc-card__author-name {
	font-weight: 700;
	color: var(--client-color-1);
	margin-bottom: 2px;
}

.cc-card__author-role {
	font-size: 0.85rem;
	color: var(--client-color-16);
}

/* ── Minimal Card ───────────────────────────────────────────── */
.cc-card--minimal {
	padding: 32px 28px;
	display: flex;
	flex-direction: column;
	justify-content: center;
	min-height: 220px;
	background: #fff;
}

.cc-card--minimal .cc-card__title {
	font-size: 1.2rem;
	margin-bottom: 10px;
}

.cc-card--minimal .cc-card__text {
	font-size: 0.9rem;
	line-height: 1.65;
}

/* ── Feature Detailed Card ──────────────────────────────────── */
.cc-card--feature-detail {
	padding: 28px 24px 32px;
	display: flex;
	flex-direction: column;
	gap: 0;
	min-height: 280px;
	justify-content: flex-start;
	background: #fff;
}

.cc-card--feature-detail .cc-card__icon-wrap {
	width: 52px;
	height: 52px;
	border-radius: 13px;
	background: var(--client-color-11, #eef5e8);
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 1.6rem;
	line-height: 1;
	margin-bottom: 18px;
	flex-shrink: 0;
}

.cc-card--feature-detail .cc-card__title {
	font-size: 1.05rem;
	margin-bottom: 8px;
}

.cc-card--feature-detail .cc-card__text {
	font-size: 0.9rem;
	line-height: 1.6;
	margin-bottom: 12px;
}

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width: 900px) {
	.cc-card--img-overlay {
		min-height: 300px;
	}

	.cc-card--split {
		min-height: 280px;
	}

	.cc-card--split .cc-card__media {
		flex: 0 0 35%;
	}

	.cc-card__stat-number {
		font-size: 2.8rem;
	}
}

@media (max-width: 640px) {
	.cc-card--split {
		flex-direction: column;
		min-height: auto;
	}

	.cc-card--split .cc-card__media {
		flex: 0 0 180px;
		min-height: 180px;
	}

	.cc-card--split .cc-card__body {
		padding: 24px;
	}

	.cc-card--img-overlay .cc-card__content {
		padding: 24px;
	}

	.cc-card__stat-number {
		font-size: 2.5rem;
	}

	.cc-card--testimonial .cc-card__quote {
		font-size: 0.95rem;
	}
}
</style>
<?php
