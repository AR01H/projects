<?php

namespace Ah\Cms\Feature\Pages\Renderer;

defined( 'ABSPATH' ) || exit;

class BlockRenderer {

	/**
	 * Padding modifier class shared by every block that honours $d['padding'].
	 */
	private static function padClass( array $d ): string {
		$map = array( 'none' => ' section--no-pad', 'sm' => ' section--sm', 'lg' => ' section--lg', 'md' => '' );
		return $map[ $d['padding'] ?? 'md' ] ?? '';
	}

	/**
	 * Open a section tag with optional classes and extra style.
	 */
	public static function sectionOpen( array $d, string $classes = 'section', string $extra_style = '' ): string {
		$classes .= self::padClass( $d );
		$id  = ! empty( $d['section_id'] ) ? ' id="' . esc_attr( $d['section_id'] ) . '"' : '';
		$sty = $extra_style ? ' style="' . esc_attr( $extra_style ) . '"' : '';
		return '<section' . $id . ' class="' . esc_attr( trim( $classes ) ) . '"' . $sty . '>';
	}

	/**
	 * Shared "heading + gold accent bar" row used by most content blocks
	 * (cards, stats_row, faq, tabs, comparison, timeline, pricing, icon_list,
	 * links_list, section, steps) - was duplicated verbatim ~10x before.
	 */
	private static function heading( string $heading, string $subtitle = '', string $align = 'center' ): void {
		if ( '' === $heading ) {
			return;
		}
		$cls = 'section__header block-render-heading' . ( 'center' === $align ? ' text-center' : '' );
		echo '<div class="' . esc_attr( $cls ) . '" data-aos="fade-up">';
		echo '<h2>' . esc_html( $heading ) . '</h2>';
		if ( '' !== $subtitle ) {
			echo '<p class="section__desc">' . esc_html( $subtitle ) . '</p>';
		}
		echo '<span class="ah-accent-bar"></span>';
		echo '</div>';
	}

	/** type => method name. The single dispatch point for every block type. */
	private static function blockTypeMap(): array {
		return array(
			'hero'            => 'renderHero',
			'section_heading' => 'renderSectionHeading',
			'text_block'      => 'renderTextBlock',
			'spacer'          => 'renderSpacer',
			'cards'           => 'renderCards',
			'cta_banner'      => 'renderCtaBanner',
			'stats_row'       => 'renderStatsRow',
			'faq'             => 'renderFaq',
			'button_row'      => 'renderButtonRow',
			'links_list'      => 'renderLinksList',
			'testimonial'     => 'renderTestimonial',
			'steps'           => 'renderSteps',
			'divider'         => 'renderDivider',
			'alert'           => 'renderAlert',
			'columns'         => 'renderColumns',
			'image_text'      => 'renderImageText',
			'gallery'         => 'renderGallery',
			'video'           => 'renderVideo',
			'map_embed'       => 'renderMapEmbed',
			'logo_strip'      => 'renderLogoStrip',
			'timeline'        => 'renderTimeline',
			'pricing'         => 'renderPricing',
			'pull_quote'      => 'renderPullQuote',
			'icon_list'       => 'renderIconList',
			'download'        => 'renderDownload',
			'tabs'            => 'renderTabs',
			'comparison'      => 'renderComparison',
			'notice_bar'      => 'renderNoticeBar',
			'contact_card'    => 'renderContactCard',
		);
	}

	/**
	 * Render a builder block by type and data.
	 */
	public static function render( string $type, array $d ): void {
		$map = self::blockTypeMap();
		if ( isset( $map[ $type ] ) ) {
			self::{$map[ $type ]}( $d );
		}
	}

	// ── Hero ─────────────────────────────────────────────────────────────────

	private static function renderHero( array $d ): void {
		$bg_image = ! empty( $d['bg_image'] ) ? esc_url( $d['bg_image'] ) : '';
		$bg       = $d['bg'] ?? 'white';
		$mod      = '';
		$alt_cls  = '';

		if ( $bg_image ) {
			$mod = 'block-render-hero--image';
		} elseif ( 'dark' === $bg ) {
			$mod = 'block-render-hero--dark';
		} elseif ( 'light' === $bg ) {
			$alt_cls = 'section--alt';
		} elseif ( 'gold' === $bg ) {
			$mod = 'block-render-hero--gold';
		} elseif ( 'client-color-light' === $bg ) {
			$mod = 'block-render-hero--brand-light';
		} elseif ( 'client-color-medium' === $bg ) {
			$mod = 'block-render-hero--brand-medium';
		} elseif ( 'client-color-dark' === $bg ) {
			$mod = 'block-render-hero--gold';
		}
		$dark = in_array( $mod, array( 'block-render-hero--dark', 'block-render-hero--gold' ), true );

		$style_vars = '';
		if ( $bg_image ) {
			$overlay_map = array( 'none' => '0', 'light' => '.3', 'medium' => '.52', 'dark' => '.72' );
			$overlay     = $overlay_map[ $d['overlay'] ?? 'medium' ] ?? '.52';
			$style_vars .= '--hero-bg-image:url(' . $bg_image . ');--hero-overlay:' . $overlay . ';';
		}
		if ( ! empty( $d['min_height'] ) ) {
			$style_vars .= '--hero-min-h:' . (int) $d['min_height'] . 'px;';
		} elseif ( ( $d['full_height'] ?? 'no' ) === 'yes' ) {
			$style_vars .= '--hero-min-h:100vh;';
		}

		$text_align_cls = ( $d['text_align'] ?? 'center' ) === 'left' ? 'text-left' : 'text-center';
		$hero_id        = ! empty( $d['section_id'] ) ? ' id="' . esc_attr( $d['section_id'] ) . '"' : '';
		$hero_cls       = trim( "block-render-hero section $text_align_cls $mod $alt_cls" . self::padClass( $d ) );
		?>
		<section class="<?php echo esc_attr( $hero_cls ); ?>"<?php echo $hero_id; ?>
		         <?php if ( $style_vars ) echo 'style="' . esc_attr( $style_vars ) . '"'; ?>
		         data-aos="fade-in">
			<div class="ph__bg" aria-hidden="true"><div class="ph__grid-lines"></div></div>
			<div class="container">
				<?php if ( ! empty( $d['eyebrow'] ) ) : ?>
					<p class="section__eyebrow" data-aos="fade-up"><?php echo esc_html( $d['eyebrow'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $d['heading'] ) ) : ?>
					<h1 data-aos="fade-up" data-aos-delay="80">
						<?php echo wp_kses_post( $d['heading'] ); ?>
					</h1>
				<?php endif; ?>
				<?php if ( ! empty( $d['subheading'] ) ) : ?>
					<p class="section__desc" data-aos="fade-up" data-aos-delay="160">
						<?php echo wp_kses_post( $d['subheading'] ); ?>
					</p>
				<?php endif; ?>
				<?php if ( ! empty( $d['cta1_text'] ) || ! empty( $d['cta2_text'] ) ) : ?>
					<div class="ah-hero__ctas" data-aos="fade-up" data-aos-delay="240">
						<?php if ( ! empty( $d['cta1_text'] ) ) : ?>
							<a href="<?php echo esc_url( $d['cta1_url'] ?? '#' ); ?>" class="btn btn-gold btn-lg btn-primary">
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
		<?php
	}

	// ── Section heading ────────────────────────────────────────────────────────

	private static function renderSectionHeading( array $d ): void {
		$align = $d['align'] ?? 'center';
		?>
		<?php echo self::sectionOpen( $d, 'section section--sm' . ( $align === 'center' ? ' text-center' : '' ) ); ?>
			<div class="container">
				<?php if ( ! empty( $d['eyebrow'] ) ) : ?>
					<p class="section__eyebrow"><?php echo esc_html( $d['eyebrow'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $d['title'] ) ) : ?>
					<h2><?php echo esc_html( $d['title'] ); ?></h2>
					<?php if ( ( $d['accent_bar'] ?? 'yes' ) !== 'no' ) : ?>
						<span class="ah-accent-bar"></span>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ( ! empty( $d['subtitle'] ) ) : ?>
					<p class="section__desc block-render-heading__subtitle"><?php echo esc_html( $d['subtitle'] ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	// ── Text block ──────────────────────────────────────────────────────────────

	private static function renderTextBlock( array $d ): void {
		?>
		<?php echo self::sectionOpen( $d, 'section section--sm' ); ?>
			<div class="container">
				<div class="ah-rich-text"><?php echo wp_kses_post( $d['content'] ?? '' ); ?></div>
			</div>
		</section>
		<?php
	}

	// ── Spacer ──────────────────────────────────────────────────────────────────

	private static function renderSpacer( array $d ): void {
		$h = max( 10, min( 200, (int) ( $d['height'] ?? 40 ) ) );
		echo '<div class="block-render-spacer" style="--spacer-h:' . $h . 'px;"></div>';
	}

	// ── Cards grid ────────────────────────────────────────────────────────────

	private static function renderCards( array $d ): void {
		// $d['source'] opts into live data instead of the manually-typed $d['cards']
		// array - e.g. 'latest_news' / 'latest_posts'. Unset or 'manual' (the
		// default) keeps the original static behaviour exactly as before.
		$cards      = self::resolveDynamicCards( $d ) ?? ( $d['cards'] ?? array() );
		$cols       = max( 1, min( 4, (int) ( $d['cols'] ?? 3 ) ) );
		$grid_class = $cols > 1 ? 'grid-' . $cols : '';
		$sec_cls    = ( $d['bg'] ?? 'white' ) === 'alt' ? 'section section--alt' : 'section';
		$cstyle     = $d['card_style'] ?? 'feat';
		?>
		<?php echo self::sectionOpen( $d, $sec_cls ); ?>
			<div class="container">
				<?php self::heading( $d['heading'] ?? '' ); ?>
				<div <?php if ( $grid_class ) echo 'class="' . esc_attr( $grid_class ) . '"'; ?>>
					<?php foreach ( $cards as $i => $card ) : ?>
						<?php if ( $cstyle === 'plain' ) : ?>
							<div class="ah-plain-card" data-aos="fade-up" data-aos-delay="<?php echo $i * 80; ?>">
								<?php if ( ! empty( $card['icon'] ) ) : ?><div class="ah-plain-card__icon"><?php echo esc_html( $card['icon'] ); ?></div><?php endif; ?>
								<?php if ( ! empty( $card['title'] ) ) : ?><div class="ah-plain-card__title"><?php echo esc_html( $card['title'] ); ?></div><?php endif; ?>
								<?php if ( ! empty( $card['text'] ) ) : ?><p class="ah-plain-card__text"><?php echo esc_html( $card['text'] ); ?></p><?php endif; ?>
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
		<?php
	}

	// ── CTA banner ──────────────────────────────────────────────────────────────

	/** Button variant classes per CTA theme (reuses existing sitewide btn-* classes only). */
	private static function ctaButtonClasses( string $theme ): array {
		$map = array(
			'gold'  => array( 'btn-gold',    'btn-outline btn-white' ),
			'dark'  => array( 'btn-gold',    'btn-outline btn-white' ),
			'blue'  => array( 'btn-white',   'btn-outline btn-white' ),
			'light' => array( 'btn-primary', 'btn-outline' ),
		);
		return $map[ $theme ] ?? $map['gold'];
	}

	private static function renderCtaBanner( array $d ): void {
		$theme      = $d['theme'] ?? 'gold';
		list( $btn1_cls, $btn2_cls ) = self::ctaButtonClasses( $theme );
		$is_split   = ( $d['layout'] ?? 'centered' ) === 'split';
		$wrap_cls   = 'section block-render-cta block-render-cta--' . esc_attr( $theme ) . ( $is_split ? '' : ' text-center' );
		?>
		<section class="<?php echo esc_attr( $wrap_cls ); ?>"
		         <?php if ( ! empty( $d['section_id'] ) ) echo 'id="' . esc_attr( $d['section_id'] ) . '"'; ?>
		         data-aos="fade-up">
			<div class="container<?php echo $is_split ? '' : '  '; ?> block-render-cta__inner">
				<?php if ( $is_split ) : ?>
					<div class="ah-cta-split">
						<div class="ah-cta-split__text">
							<?php if ( ! empty( $d['eyebrow'] ) ) : ?>
								<p class="section__eyebrow"><?php echo esc_html( $d['eyebrow'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $d['heading'] ) ) : ?>
								<h2><?php echo esc_html( $d['heading'] ); ?></h2>
							<?php endif; ?>
							<?php if ( ! empty( $d['text'] ) ) : ?>
								<p class="section__desc"><?php echo esc_html( $d['text'] ); ?></p>
							<?php endif; ?>
						</div>
						<div class="ah-cta-split__btns">
							<?php if ( ! empty( $d['btn1_text'] ) ) : ?>
								<a href="<?php echo esc_url( $d['btn1_url'] ?? '#' ); ?>" class="btn btn-lg <?php echo esc_attr( $btn1_cls ); ?>"><?php echo esc_html( $d['btn1_text'] ); ?></a>
							<?php endif; ?>
							<?php if ( ! empty( $d['btn2_text'] ) ) : ?>
								<a href="<?php echo esc_url( $d['btn2_url'] ?? '#' ); ?>" class="btn btn-lg <?php echo esc_attr( $btn2_cls ); ?>"><?php echo esc_html( $d['btn2_text'] ); ?></a>
							<?php endif; ?>
						</div>
					</div>
				<?php else : ?>
					<?php if ( ! empty( $d['eyebrow'] ) ) : ?>
						<p class="section__eyebrow" data-aos="fade-up"><?php echo esc_html( $d['eyebrow'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $d['heading'] ) ) : ?>
						<h2 data-aos="fade-up">
							<?php echo esc_html( $d['heading'] ); ?>
						</h2>
					<?php endif; ?>
					<?php if ( ! empty( $d['text'] ) ) : ?>
						<p class="section__desc" data-aos="fade-up" data-aos-delay="80">
							<?php echo esc_html( $d['text'] ); ?>
						</p>
					<?php endif; ?>
					<div class="ah-hero__ctas" data-aos="fade-up" data-aos-delay="160">
						<?php if ( ! empty( $d['btn1_text'] ) ) : ?>
							<a href="<?php echo esc_url( $d['btn1_url'] ?? '#' ); ?>"
							   class="btn btn-lg <?php echo esc_attr( $btn1_cls ); ?>">
								<?php echo esc_html( $d['btn1_text'] ); ?>
							</a>
						<?php endif; ?>
						<?php if ( ! empty( $d['btn2_text'] ) ) : ?>
							<a href="<?php echo esc_url( $d['btn2_url'] ?? '#' ); ?>"
							   class="btn btn-lg <?php echo esc_attr( $btn2_cls ); ?>">
								<?php echo esc_html( $d['btn2_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	// ── Stats row ───────────────────────────────────────────────────────────────

	private static function renderStatsRow( array $d ): void {
		$stats = $d['stats'] ?? array();
		if ( empty( $stats ) ) {
			return;
		}
		?>
		<?php echo self::sectionOpen( $d, 'section section--alt' ); ?>
			<div class="container">
				<?php self::heading( $d['heading'] ?? '' ); ?>
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
		<?php
	}

	// ── FAQ accordion ───────────────────────────────────────────────────────────

	private static function renderFaq( array $d ): void {
		$items = $d['items'] ?? array();
		if ( empty( $items ) ) {
			return;
		}
		?>
		<?php echo self::sectionOpen( $d, 'section block-render-faq' ); ?>
			<div class="container">
				<?php self::heading( $d['heading'] ?? '' ); ?>
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
		<?php
	}

	// ── Button row ──────────────────────────────────────────────────────────────

	private static function renderButtonRow( array $d ): void {
		$buttons = $d['buttons'] ?? array();
		if ( empty( $buttons ) ) {
			return;
		}
		$align_cls_map = array( 'left' => 'block-render-buttons--left', 'right' => 'block-render-buttons--right', 'center' => 'block-render-buttons--center' );
		$align_cls     = $align_cls_map[ $d['align'] ?? 'center' ] ?? $align_cls_map['center'];
		$btn_class_map = array(
			'primary'   => 'btn-primary',
			'secondary' => 'btn-ghost',
			'outline'   => 'btn-outline',
			'gold'      => 'btn-gold',
		);
		?>
		<?php echo self::sectionOpen( $d, 'section section--sm' ); ?>
			<div class="container block-render-buttons <?php echo esc_attr( $align_cls ); ?>">
				<?php foreach ( $buttons as $btn ) :
					$extra = $btn_class_map[ $btn['style'] ?? 'primary' ] ?? 'btn-primary'; ?>
					<a href="<?php echo esc_url( $btn['url'] ?? '#' ); ?>" class="btn <?php echo esc_attr( $extra ); ?>">
						<?php echo esc_html( $btn['text'] ?? 'Click Here' ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}

	// ── Links list ──────────────────────────────────────────────────────────────

	private static function renderLinksList( array $d ): void {
		$links = $d['links'] ?? array();
		if ( empty( $links ) ) {
			return;
		}
		$cols       = max( 1, min( 3, (int) ( $d['cols'] ?? 2 ) ) );
		$grid_class = $cols > 1 ? 'grid-' . $cols : '';
		$lnk_style  = $d['style'] ?? 'card';
		?>
		<?php echo self::sectionOpen( $d, 'section' ); ?>
			<div class="container">
				<?php self::heading( $d['heading'] ?? '', '', 'left' ); ?>
				<div <?php if ( $grid_class ) echo 'class="' . esc_attr( $grid_class ) . '"'; ?>>
					<?php foreach ( $links as $i => $lnk ) :
						if ( $lnk_style === 'plain' ) : ?>
							<a href="<?php echo esc_url( $lnk['url'] ?? '#' ); ?>"
							   class="ah-link-plain"
							   data-aos="fade-up" data-aos-delay="<?php echo $i * 60; ?>">
								<?php if ( ! empty( $lnk['icon'] ) ) : ?><span><?php echo esc_html( $lnk['icon'] ); ?></span><?php endif; ?>
								<span><?php echo esc_html( $lnk['label'] ?? '' ); ?></span>
							</a>
						<?php elseif ( $lnk_style === 'numbered' ) : ?>
							<a href="<?php echo esc_url( $lnk['url'] ?? '#' ); ?>"
							   class="ah-link-item"
							   data-aos="fade-up" data-aos-delay="<?php echo $i * 60; ?>">
								<div class="ah-link-item__icon ah-link-item__icon--numbered"><?php echo str_pad( $i + 1, 2, '0', STR_PAD_LEFT ); ?>.</div>
								<div class="ah-link-item__body">
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
								<div class="ah-link-item__body">
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
		<?php
	}

	// ── Testimonial ─────────────────────────────────────────────────────────────

	private static function renderTestimonial( array $d ): void {
		$bg_map  = array( 'white' => '', 'alt' => ' section--alt', 'gold' => ' ah-testimonial--gold' );
		$sec_cls = 'section' . ( $bg_map[ $d['bg'] ?? 'alt' ] ?? ' section--alt' );
		$stars   = max( 1, min( 5, (int) ( $d['stars'] ?? 5 ) ) );
		$is_card = ( $d['layout'] ?? 'centered' ) === 'card';
		?>
		<?php echo self::sectionOpen( $d, $sec_cls ); ?>
			<div class="container">
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
							     class="ah-testimonial__avatar" loading="lazy">
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
								<div class="ah-testimonial__role ah-testimonial__role--muted"><?php echo esc_html( $d['company'] ); ?></div>
							<?php endif; ?>
						</div>
					</figcaption>
				</figure>
			</div>
		</section>
		<?php
	}

	// ── Steps / Process ─────────────────────────────────────────────────────────

	private static function renderSteps( array $d ): void {
		$items = $d['items'] ?? array();
		if ( empty( $items ) ) {
			return;
		}
		$horiz     = ( $d['layout'] ?? 'vertical' ) === 'horizontal';
		$connector = ( $d['connector'] ?? 'no' ) === 'yes';
		$steps_cls = ( $d['bg'] ?? 'white' ) === 'alt' ? 'section section--alt' : 'section';
		?>
		<?php echo self::sectionOpen( $d, $steps_cls ); ?>
			<div class="container">
				<?php self::heading( $d['heading'] ?? '' ); ?>
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
		<?php
	}

	// ── Divider ─────────────────────────────────────────────────────────────────

	private static function renderDivider( array $d ): void {
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
		<?php
	}

	// ── Alert / Notice ──────────────────────────────────────────────────────────

	private static function renderAlert( array $d ): void {
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
		<div class="container block-render-alert-wrap" data-aos="fade-up"<?php echo $alert_id; ?>>
			<div class="ah-alert <?php echo esc_attr( $at['cls'] ); ?>"<?php echo $dismissible ? ' data-dismissible="1"' : ''; ?>>
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
		<?php
	}

	// ── 2/3-Col text columns ─────────────────────────────────────────────────────

	private static function renderColumns( array $d ): void {
		$items   = $d['items'] ?? array();
		if ( empty( $items ) ) {
			return;
		}
		$cols    = max( 2, min( 3, (int) ( $d['cols'] ?? 2 ) ) );
		$col_cls = ( $d['bg'] ?? 'white' ) === 'alt'
			? 'section section--alt section--sm ah-columns-section'
			: 'section section--sm ah-columns-section';
		?>
		<?php echo self::sectionOpen( $d, $col_cls ); ?>
			<div class="container">
				<?php if ( ! empty( $d['heading'] ) ) : ?>
					<div class="section__header text-center ah-columns-header" data-aos="fade-up">
						<h2 class="ah-columns-title"><?php echo esc_html( $d['heading'] ); ?></h2>
						<span class="ah-accent-bar"></span>
					</div>
				<?php endif; ?>
				<div class="grid-<?php echo $cols; ?> ah-columns-grid">
					<?php foreach ( $items as $i => $col ) : ?>
						<div class="ah-columns-card" data-aos="fade-up" data-aos-delay="<?php echo $i * 80; ?>">
							<?php if ( ! empty( $col['icon'] ) ) : ?>
								<div class="ah-columns-icon"><?php echo esc_html( $col['icon'] ); ?></div>
							<?php endif; ?>
							<?php if ( ! empty( $col['heading'] ) ) : ?>
								<h3 class="ah-columns-card-title"><?php echo esc_html( $col['heading'] ); ?></h3>
							<?php endif; ?>
							<?php if ( ! empty( $col['text'] ) ) : ?>
								<p class="ah-columns-card-text"><?php echo nl2br( esc_html( $col['text'] ) ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}

	// ── Image + text ────────────────────────────────────────────────────────────

	private static function renderImageText( array $d ): void {
		$img_left = ( $d['layout'] ?? 'image-left' ) === 'image-left';
		$points   = $d['points'] ?? array();
		$grid_cls = 'content-layout--2col ah-img-text-grid' . ( $img_left ? '' : ' ah-img-text-grid--reverse' );
		?>
		<?php echo self::sectionOpen( $d, 'section' ); ?>
			<div class="container">
				<div class="<?php echo esc_attr( $grid_cls ); ?>">
					<div class="ah-img-text__image" data-aos="<?php echo $img_left ? 'fade-right' : 'fade-left'; ?>">
						<?php if ( ! empty( $d['image_url'] ) ) : ?>
							<img src="<?php echo esc_url( $d['image_url'] ); ?>"
							     alt="<?php echo esc_attr( $d['image_alt'] ?? '' ); ?>"
							     loading="lazy">
						<?php else : ?>
							<div class="ah-img-text__placeholder">Image placeholder</div>
						<?php endif; ?>
					</div>
					<div class="ah-img-text__content" data-aos="<?php echo $img_left ? 'fade-left' : 'fade-right'; ?>" data-aos-delay="100">
						<?php if ( ! empty( $d['eyebrow'] ) ) : ?>
							<p class="section__eyebrow"><?php echo esc_html( $d['eyebrow'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $d['heading'] ) ) : ?>
							<h2><?php echo esc_html( $d['heading'] ); ?></h2>
							<span class="ah-accent-bar ah-img-text__accent-bar"></span>
						<?php endif; ?>
						<?php if ( ! empty( $d['text'] ) ) : ?>
							<p class="ah-img-text__text"><?php echo nl2br( esc_html( $d['text'] ) ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $points ) ) : ?>
							<ul class="ah-img-text__points">
								<?php foreach ( $points as $pt ) : ?>
									<li>
										<?php if ( ! empty( $pt['icon'] ) ) : ?><span><?php echo esc_html( $pt['icon'] ); ?></span><?php endif; ?>
										<span><?php echo esc_html( $pt['text'] ?? '' ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<div class="ah-hero__ctas ah-img-text__ctas">
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
		<?php
	}

	// ── Gallery ─────────────────────────────────────────────────────────────────

	private static function renderGallery( array $d ): void {
		$images = $d['images'] ?? array();
		if ( empty( $images ) ) {
			return;
		}
		$cols    = max( 2, min( 4, (int) ( $d['cols'] ?? 3 ) ) );
		$gap_map = array( 'sm' => '8px', 'md' => '14px', 'lg' => '24px' );
		$gap     = $gap_map[ $d['gap'] ?? 'md' ] ?? '14px';
		?>
		<?php echo self::sectionOpen( $d, 'section' ); ?>
			<div class="container">
				<?php self::heading( $d['heading'] ?? '' ); ?>
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
		<?php
	}

	// ── Video Embed ─────────────────────────────────────────────────────────────

	private static function renderVideo( array $d ): void {
		$url = $d['url'] ?? '';
		if ( empty( $url ) ) {
			return;
		}
		$embed     = preg_replace( '|(?:https?://)?(?:www\.)?youtube\.com/watch\?v=([a-zA-Z0-9_-]+)|', 'https://www.youtube.com/embed/$1', $url );
		$embed     = preg_replace( '|(?:https?://)?youtu\.be/([a-zA-Z0-9_-]+)|', 'https://www.youtube.com/embed/$1', $embed );
		$embed     = preg_replace( '|(?:https?://)?(?:www\.)?vimeo\.com/(\d+)|', 'https://player.vimeo.com/video/$1', $embed );
		$ratio_map = array( '16:9' => '56.25%', '4:3' => '75%', '1:1' => '100%' );
		$ratio_pad = $ratio_map[ $d['ratio'] ?? '16:9' ] ?? '56.25%';
		?>
		<?php echo self::sectionOpen( $d, 'section section--sm' ); ?>
			<div class="container" data-aos="fade-up">
				<?php if ( ! empty( $d['caption'] ) ) : ?>
					<p class="section__eyebrow block-render-heading__subtitle"><?php echo esc_html( $d['caption'] ); ?></p>
				<?php endif; ?>
				<div class="ah-video-wrap" style="--video-ratio:<?php echo esc_attr( $ratio_pad ); ?>;">
					<iframe src="<?php echo esc_url( $embed ); ?>"
					        frameborder="0"
					        allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture"
					        allowfullscreen
					        loading="lazy"></iframe>
				</div>
			</div>
		</section>
		<?php
	}

	// ── Map Embed ───────────────────────────────────────────────────────────────

	private static function renderMapEmbed( array $d ): void {
		$url = $d['url'] ?? '';
		if ( empty( $url ) ) {
			return;
		}
		$h = max( 200, min( 700, (int) ( $d['height'] ?? 400 ) ) );
		?>
		<?php echo self::sectionOpen( $d, 'section section--sm' ); ?>
			<div class="container" data-aos="fade-up">
				<?php if ( ! empty( $d['label'] ) ) : ?>
					<div class="block-render-heading__subtitle">
						<h2><?php echo esc_html( $d['label'] ); ?></h2>
						<span class="ah-accent-bar"></span>
					</div>
				<?php endif; ?>
				<div class="ah-map-wrap" style="--map-h:<?php echo $h; ?>px;">
					<iframe src="<?php echo esc_url( $url ); ?>"
					        width="100%" height="100%"
					        allowfullscreen="" loading="lazy"
					        referrerpolicy="no-referrer-when-downgrade"></iframe>
				</div>
			</div>
		</section>
		<?php
	}

	// ── Logo Strip ──────────────────────────────────────────────────────────────

	private static function renderLogoStrip( array $d ): void {
		$logos = $d['logos'] ?? array();
		if ( empty( $logos ) ) {
			return;
		}
		$sec_cls = ( $d['bg'] ?? 'white' ) === 'alt' ? 'section section--alt' : 'section';
		?>
		<?php echo self::sectionOpen( $d, $sec_cls ); ?>
			<div class="container" data-aos="fade-up">
				<?php if ( ! empty( $d['heading'] ) ) : ?>
					<p class="ah-logo-strip__label"><?php echo esc_html( $d['heading'] ); ?></p>
				<?php endif; ?>
				<div class="ah-logo-strip">
					<?php foreach ( $logos as $logo ) :
						$tag_open  = ! empty( $logo['link'] ) ? '<a href="' . esc_url( $logo['link'] ) . '" target="_blank" rel="noopener" class="ah-logo-strip__item">' : '<div class="ah-logo-strip__item">';
						$tag_close = ! empty( $logo['link'] ) ? '</a>' : '</div>';
						echo $tag_open; ?>
						<img src="<?php echo esc_url( $logo['url'] ?? '' ); ?>"
						     alt="<?php echo esc_attr( $logo['alt'] ?? '' ); ?>"
						     loading="lazy">
						<?php echo $tag_close; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}

	// ── Timeline ────────────────────────────────────────────────────────────────

	private static function renderTimeline( array $d ): void {
		$items = $d['items'] ?? array();
		if ( empty( $items ) ) {
			return;
		}
		$sec_cls = ( $d['bg'] ?? 'white' ) === 'alt' ? 'section section--alt' : 'section';
		?>
		<?php echo self::sectionOpen( $d, $sec_cls ); ?>
			<div class="container" data-aos="fade-up">
				<?php self::heading( $d['heading'] ?? '' ); ?>
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
		<?php
	}

	// ── Pricing ─────────────────────────────────────────────────────────────────

	private static function renderPricing( array $d ): void {
		$plans = $d['plans'] ?? array();
		if ( empty( $plans ) ) {
			return;
		}
		$cols = count( $plans );
		?>
		<?php echo self::sectionOpen( $d, 'section section--alt' ); ?>
			<div class="container">
				<?php self::heading( $d['heading'] ?? '', $d['subtitle'] ?? '' ); ?>
				<div class="ah-pricing-grid" style="--pricing-cols:<?php echo min( $cols, 3 ); ?>;">
					<?php foreach ( $plans as $i => $plan ) :
						$hi       = ( $plan['highlight'] ?? 'no' ) === 'yes';
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
		<?php
	}

	// ── Pull Quote ──────────────────────────────────────────────────────────────

	private static function renderPullQuote( array $d ): void {
		$quote = $d['quote'] ?? '';
		if ( empty( $quote ) ) {
			return;
		}
		$size_cls  = ( $d['size'] ?? 'md' ) === 'lg' ? ' ah-pull-quote--lg' : '';
		$align_cls = ( $d['align'] ?? 'center' ) === 'left' ? '' : ' text-center';
		$color     = $d['color'] ?? 'gold';
		?>
		<?php echo self::sectionOpen( $d, 'section section--sm' ); ?>
			<div class="container" data-aos="fade-up">
				<blockquote class="ah-pull-quote<?php echo $size_cls . $align_cls; ?>" data-color="<?php echo esc_attr( $color ); ?>">
					<?php echo wp_kses_post( $quote ); ?>
				</blockquote>
			</div>
		</section>
		<?php
	}

	// ── Icon List ───────────────────────────────────────────────────────────────

	private static function renderIconList( array $d ): void {
		$items = $d['items'] ?? array();
		if ( empty( $items ) ) {
			return;
		}
		$cols    = max( 1, min( 2, (int) ( $d['cols'] ?? 1 ) ) );
		$sec_cls = ( $d['bg'] ?? 'white' ) === 'alt' ? 'section section--alt' : 'section';
		?>
		<?php echo self::sectionOpen( $d, $sec_cls ); ?>
			<div class="container">
				<?php self::heading( $d['heading'] ?? '', '', 'left' ); ?>
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
		<?php
	}

	// ── Download Button ─────────────────────────────────────────────────────────

	private static function renderDownload( array $d ): void {
		$url = $d['url'] ?? '';
		if ( empty( $url ) ) {
			return;
		}
		?>
		<?php echo self::sectionOpen( $d, 'section section--sm ah-download-section' ); ?>
			<div class="container" data-aos="fade-up">
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
		<?php
	}

	// ── Tabs ────────────────────────────────────────────────────────────────────

	private static function renderTabs( array $d ): void {
		$tabs = $d['tabs'] ?? array();
		if ( empty( $tabs ) ) {
			return;
		}
		$block_id = 'ah-tabs-' . substr( md5( serialize( $tabs ) ), 0, 6 );
		?>
		<?php echo self::sectionOpen( $d, 'section' ); ?>
			<div class="container" data-aos="fade-up">
				<?php self::heading( $d['heading'] ?? '' ); ?>
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
		<?php
	}

	// ── Comparison Table ─────────────────────────────────────────────────────────

	private static function renderComparison( array $d ): void {
		$rows = $d['rows'] ?? array();
		if ( empty( $rows ) ) {
			return;
		}
		?>
		<?php echo self::sectionOpen( $d, 'section' ); ?>
			<div class="container" data-aos="fade-up">
				<?php self::heading( $d['heading'] ?? '' ); ?>
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
		<?php
	}

	// ── Notice Bar ──────────────────────────────────────────────────────────────

	private static function renderNoticeBar( array $d ): void {
		$variant = in_array( $d['style'] ?? 'gold', array( 'gold', 'dark', 'info' ), true ) ? $d['style'] : 'gold';
		$id_attr = ! empty( $d['section_id'] ) ? ' id="' . esc_attr( $d['section_id'] ) . '"' : '';
		?>
		<div<?php echo $id_attr; ?> class="ah-notice-bar block-render-notice block-render-notice--<?php echo esc_attr( $variant ); ?>">
			<span class="ah-notice-bar__text"><?php echo esc_html( $d['text'] ?? '' ); ?></span>
			<?php if ( ! empty( $d['cta'] ) ) : ?>
				<a href="<?php echo esc_url( $d['url'] ?? '#' ); ?>" class="ah-notice-bar__cta"><?php echo esc_html( $d['cta'] ); ?> →</a>
			<?php endif; ?>
		</div>
		<?php
	}

	// ── Contact Card ─────────────────────────────────────────────────────────────

	private static function renderContactCard( array $d ): void {
		$horiz = ( $d['layout'] ?? 'horizontal' ) === 'horizontal';
		?>
		<?php echo self::sectionOpen( $d, 'section section--alt' ); ?>
			<div class="container">
				<div class="ah-contact-card<?php echo $horiz ? '' : ' ah-contact-card--vertical'; ?>">
					<?php if ( ! empty( $d['photo'] ) ) : ?>
						<img src="<?php echo esc_url( $d['photo'] ); ?>"
						     alt="<?php echo esc_attr( $d['name'] ?? '' ); ?>"
						     class="ah-contact-card__photo" loading="lazy">
					<?php endif; ?>
					<div class="ah-contact-card__body">
						<?php if ( ! empty( $d['name'] ) ) : ?><div class="ah-contact-card__name"><?php echo esc_html( $d['name'] ); ?></div><?php endif; ?>
						<?php if ( ! empty( $d['role'] ) ) : ?><div class="ah-contact-card__role"><?php echo esc_html( $d['role'] ); ?></div><?php endif; ?>
						<?php if ( ! empty( $d['bio'] ) ) : ?><p class="ah-contact-card__bio"><?php echo esc_html( $d['bio'] ); ?></p><?php endif; ?>
						<div class="ah-contact-card__links">
							<?php if ( ! empty( $d['phone'] ) ) : ?><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $d['phone'] ) ); ?>" class="ah-contact-card__link">📞 <?php echo esc_html( $d['phone'] ); ?></a><?php endif; ?>
							<?php if ( ! empty( $d['email'] ) ) : ?><a href="mailto:<?php echo esc_attr( $d['email'] ); ?>" class="ah-contact-card__link">✉️ <?php echo esc_html( $d['email'] ); ?></a><?php endif; ?>
						</div>
						<?php if ( ! empty( $d['cta_text'] ) ) : ?>
							<a href="<?php echo esc_url( $d['cta_url'] ?? '#' ); ?>" class="btn btn-gold ah-contact-card__cta"><?php echo esc_html( $d['cta_text'] ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}

	/**
	 * Builds a $cards array from a live data source instead of the manually-typed
	 * $d['cards'] array, for the 'cards' block. Returns null (fall back to the
	 * static array) when $d['source'] is unset/'manual' or an unknown value.
	 */
	private static function resolveDynamicCards( array $d ): ?array {
		$source = $d['source'] ?? 'manual';
		if ( 'manual' === $source || '' === $source ) {
			return null;
		}
		$limit = max( 1, (int) ( $d['source_limit'] ?? 4 ) );

		if ( 'latest_news' === $source ) {
			if ( ! function_exists( 'adn_cms_newsbar_items' ) ) {
				return array();
			}
			$cards = array();
			foreach ( adn_cms_newsbar_items( $limit ) as $n ) {
				$cards[] = array(
					'title'     => (string) ( $n->text ?? '' ),
					'text'      => wp_trim_words( wp_strip_all_tags( (string) ( $n->excerpt ?: ( $n->content ?? '' ) ) ), 20 ),
					'link_url'  => function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( (int) $n->id, (string) ( $n->slug ?? '' ) ) : '',
					'link_text' => 'Read more',
				);
			}
			return $cards;
		}

		if ( 'latest_posts' === $source ) {
			$q     = new \WP_Query( array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			) );
			$cards = array();
			foreach ( $q->posts as $p ) {
				$cards[] = array(
					'title'     => get_the_title( $p ),
					'text'      => wp_trim_words( wp_strip_all_tags( $p->post_excerpt ?: $p->post_content ), 20 ),
					'link_url'  => get_permalink( $p ),
					'link_text' => 'Read more',
				);
			}
			wp_reset_postdata();
			return $cards;
		}

		return null;
	}
}
