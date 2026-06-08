<?php
/**
 * Shared Card Renderer for Carousels
 * Renders different card types: image, feature, step, selector
 * Include this in all carousel components.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'cc_render_card' ) ) {
	function cc_render_card( array $item, string $type ): string {
		switch ( $type ) {

			/* ── Image Card ─────────────────────────────────────────── */
			case 'image':
				$img_url  = esc_url( $item['image'] ?? '' );
				$img_alt  = esc_attr( $item['title'] ?? '' );
				$title    = esc_html( $item['title'] ?? '' );
				$subtitle = esc_html( $item['subtitle'] ?? '' );
				return sprintf(
					'<div class="cc-card cc-card--image"><img src="%s" alt="%s" class="cc-card__img" loading="lazy"><div class="cc-card__overlay"></div><div class="cc-card__caption"><h3 class="cc-card__title">%s</h3>%s</div></div>',
					$img_url, $img_alt, $title,
					$subtitle ? '<p class="cc-card__subtitle">' . $subtitle . '</p>' : ''
				);

			/* ── Feature Card ──────────────────────────────────────── */
			case 'feature':
				$icon      = $item['icon'] ?? '';
				$icon_type = $item['icon_type'] ?? 'emoji';
				$title     = esc_html( $item['title'] ?? '' );
				$text      = esc_html( $item['text'] ?? '' );

				$icon_html = '';
				if ( $icon ) {
					if ( $icon_type === 'img' ) {
						$icon_html = '<div class="cc-card__icon-wrap"><img src="' . esc_url( $icon ) . '" alt=""></div>';
					} else {
						$icon_html = '<div class="cc-card__icon-wrap">' . esc_html( $icon ) . '</div>';
					}
				}

				return sprintf(
					'<div class="cc-card cc-card--feature">%s<h3 class="cc-card__title">%s</h3><p class="cc-card__text">%s</p></div>',
					$icon_html, $title, $text
				);

			/* ── Step Card ──────────────────────────────────────────── */
			case 'step':
				$step  = esc_html( $item['step'] ?? '' );
				$icon  = esc_html( $item['icon'] ?? '' );
				$title = esc_html( $item['title'] ?? '' );
				$text  = esc_html( $item['text'] ?? '' );

				return sprintf(
					'<div class="cc-card cc-card--step"><div class="cc-card__badge">%s</div><div class="cc-card__icon">%s</div><h3 class="cc-card__title">%s</h3><p class="cc-card__text">%s</p></div>',
					$step, $icon, $title, $text
				);

			/* ── Selector Card ──────────────────────────────────────── */
			case 'selector':
				$icon  = $item['icon'] ?? '';
				$label = esc_html( $item['label'] ?? '' );
				$value = esc_attr( $item['value'] ?? $label );

				return sprintf(
					'<div class="cc-card cc-card--selector" data-value="%s"><div class="cc-card__icon">%s</div><h3 class="cc-card__title">%s</h3></div>',
					$value, esc_html( $icon ), $label
				);

			default:
				return '';
		}
	}
}

/* Include this renderer CSS in all carousel components */
?>
<style>
/* ── Card Base ───────────────────────────────────────────────── */
.cc-card {
	background: #fff;
	border-radius: 16px;
	overflow: hidden;
	transition: transform 0.28s ease, box-shadow 0.28s ease;
	box-shadow: 0 2px 16px rgba(0,0,0,0.07);
}

.cc-card:hover {
	transform: translateY(-4px);
	box-shadow: 0 10px 32px rgba(0,0,0,0.10);
}

/* ── Shared typography ───────────────────────────────────────── */
.cc-card__title {
	margin: 0 0 8px;
	font-size: 1.05rem;
	font-weight: 700;
	line-height: 1.4;
	color: var(--client-color-1, #1a1a1a);
}

.cc-card__text {
	margin: 0;
	font-size: 0.9rem;
	line-height: 1.65;
	color: var(--client-color-16, #555);
}

.cc-card__subtitle {
	margin: 0;
	font-size: 0.875rem;
	opacity: 0.85;
	line-height: 1.5;
}

/* ── Image Card ──────────────────────────────────────────────── */
.cc-card--image {
	position: relative;
	overflow: hidden;
}

.cc-card__img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
	aspect-ratio: 4 / 3;
}

.cc-card__overlay {
	position: absolute;
	inset: 0;
	background: linear-gradient(180deg, rgba(0,0,0,0) 30%, rgba(0,0,0,0.55) 100%);
	z-index: 1;
}

.cc-card__caption {
	position: absolute;
	bottom: 0;
	left: 0;
	right: 0;
	padding: 20px 18px;
	color: #fff;
	z-index: 2;
}

.cc-card--image .cc-card__title {
	color: #fff;
	font-size: 1rem;
	margin-bottom: 4px;
}

/* ── Feature Card ────────────────────────────────────────────── */
.cc-card--feature {
	padding: 28px 24px 32px;
	display: flex;
	flex-direction: column;
	gap: 0;
	min-height: 240px;
	justify-content: flex-start;
}

.cc-card__icon-wrap {
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

/* Legacy icon selector (without wrapper) */
.cc-card--feature > .cc-card__icon {
	font-size: 1.6rem;
	line-height: 1;
	margin-bottom: 18px;
	width: 52px;
	height: 52px;
	border-radius: 13px;
	background: var(--client-color-11, #eef5e8);
	display: flex;
	align-items: center;
	justify-content: center;
}

.cc-card--feature > .cc-card__icon img {
	max-width: 28px;
	height: auto;
}

.cc-card--feature .cc-card__title {
	margin-bottom: 10px;
}

/* ── Step Card ────────────────────────────────────────────────── */
.cc-card--step {
	padding: 28px 24px;
	background: #fff;
	position: relative;
	display: flex;
	flex-direction: column;
	gap: 10px;
	min-height: 220px;
}

.cc-card__badge {
	position: absolute;
	top: 20px;
	right: 20px;
	width: 36px;
	height: 36px;
	border-radius: 10px;
	background: var(--client-color-7, #5a9e3a);
	color: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 700;
	font-size: 0.8rem;
	letter-spacing: 0.3px;
}

.cc-card--step .cc-card__icon {
	font-size: 1.8rem;
	line-height: 1;
	margin-bottom: 6px;
}

/* ── Selector Card ───────────────────────────────────────────── */
.cc-card--selector {
	padding: 20px;
	cursor: pointer;
	text-align: center;
	transition: all 0.28s ease;
	background: #fff;
}

.cc-card--selector:hover,
.cc-card--selector.is-selected {
	background: var(--client-color-11, #eef5e8);
	box-shadow: 0 8px 24px rgba(0,0,0,0.10);
	transform: none;
}

.cc-card--selector .cc-card__icon {
	font-size: 2rem;
	margin-bottom: 10px;
	display: block;
}

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width: 640px) {
	.cc-card--feature { padding: 20px 18px 24px; min-height: 200px; }
	.cc-card--step    { padding: 20px 18px; }
	.cc-card__title   { font-size: 0.95rem; }
	.cc-card__text    { font-size: 0.85rem; }
	.cc-card__icon-wrap,
	.cc-card--feature > .cc-card__icon { width: 44px; height: 44px; font-size: 1.35rem; border-radius: 10px; margin-bottom: 14px; }
}
</style>
<?php
