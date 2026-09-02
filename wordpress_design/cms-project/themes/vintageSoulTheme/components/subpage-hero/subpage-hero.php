<?php
/**
 * Master Common Subpage Hero Header Component
 *
 * Provides a unified, ultra-premium vintage animated header across all inner pages:
 * (About Us, All About Cane, Events, Franchise, Blog, Contact, Game)
 * Features:
 * - Dynamic Wave & Shimmering Title Letter Animations
 * - Centered Cane House Botanical Crest & Heart-Wreath Emblem with Aura Glow
 * - Dancing Script cursive tag
 * - Ambient floating botanical shimmer & grain overlay
 * - Gold wave decorative bottom divider
 */
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

$tag   = (string) ( $tag ?? '' );
$title = (string) ( $title ?? '' );
$sub   = (string) ( $sub ?? '' );
$id    = (string) ( $id ?? 'subpage-hero' );
$image = (string) ( $image ?? '' );
$share_url   = (string) ( $share_url ?? '' );
$share_title = (string) ( $share_title ?? $title );
$image_url  = '' !== $image ? UrlHelper::resolve( $image ) : '';
$wreath_url = UrlHelper::resolve( 'assets/images/decorative/cane-heart-wreath.png' );
$gold_wave  = UrlHelper::resolve( 'assets/images/textures/border/gold-wave.svg' );

$format_subpage_title = static function( string $raw_title ): string {
	if ( preg_match( '/^(.*?)<em>(.*?)<\/em>(.*)$/is', $raw_title, $matches ) ) {
		$before = trim( $matches[1] );
		$em     = trim( $matches[2] );
		$after  = trim( $matches[3] );

		$render_words = static function( string $text, int &$global_idx, bool $is_em = false ): string {
			if ( '' === $text ) {
				return '';
			}
			$words = preg_split( '/\s+/', $text );
			$html  = '';
			foreach ( (array) $words as $w ) {
				$chars      = preg_split( '//u', (string) $w, -1, PREG_SPLIT_NO_EMPTY );
				$char_spans = '';
				foreach ( (array) $chars as $c ) {
					$char_spans .= '<span class="subpage-char' . ( $is_em ? ' subpage-char--em' : '' ) . '" style="--sub-char-idx:' . $global_idx . ';">' . esc_html( (string) $c ) . '</span>';
					$global_idx++;
				}
				$html .= '<span class="subpage-word' . ( $is_em ? ' subpage-word--em' : '' ) . '">' . $char_spans . '</span> ';
			}
			return trim( $html );
		};

		$idx = 0;
		$out = '';
		if ( '' !== $before ) {
			$out .= $render_words( $before, $idx, false ) . ' ';
		}
		if ( '' !== $em ) {
			$out .= '<em class="subpage-title-em">' . $render_words( $em, $idx, true ) . '</em>';
		}
		if ( '' !== $after ) {
			$out .= ' ' . $render_words( $after, $idx, false );
		}
		return $out;
	}

	$words = preg_split( '/\s+/', strip_tags( $raw_title ) );
	$idx   = 0;
	$html  = '';
	foreach ( (array) $words as $w ) {
		$chars      = preg_split( '//u', (string) $w, -1, PREG_SPLIT_NO_EMPTY );
		$char_spans = '';
		foreach ( (array) $chars as $c ) {
			$char_spans .= '<span class="subpage-char" style="--sub-char-idx:' . $idx . ';">' . esc_html( (string) $c ) . '</span>';
			$idx++;
		}
		$html .= '<span class="subpage-word">' . $char_spans . '</span> ';
	}
	return trim( $html );
};
?>
<header class="common-subpage-hero page-hero" id="<?php echo esc_attr( $id ); ?>">
	
	<!-- Ambient Botanical Light Glow -->
	<div class="common-subpage-hero__ambient-glow" aria-hidden="true"></div>

	<!-- Botanical Watermark Layer with Ken-Burns Drift -->
	<div class="common-subpage-hero__watermark" aria-hidden="true" style="background-image: url('<?php echo esc_url( $image_url ); ?>');"></div>
	
	<!-- Roughness Texture -->
	<div class="common-subpage-hero__texture" aria-hidden="true"></div>

	<div class="container common-subpage-hero__inner">
		
		<!-- Central Botanical Crest Stamp with Radiant Halo -->
		<div class="subpage-hero__crest-wrap">
			<div class="subpage-hero__crest">
				<img src="<?php echo esc_url( $wreath_url ); ?>" alt="" class="subpage-hero__wreath-img" loading="eager">
				<span class="subpage-hero__crest-text">EST. LONDON</span>
			</div>
		</div>

		<!-- Tagline Script -->
		<?php if ( '' !== $tag ) : ?>
			<p class="common-subpage-hero__tag"><?php echo esc_html( $tag ); ?></p>
		<?php endif; ?>

		<!-- Main Title with Animated Letter Wave -->
		<?php if ( '' !== $title ) : ?>
			<h1 class="common-subpage-hero__title"><?php echo $format_subpage_title( $title ); // phpcs:ignore ?></h1>
		<?php endif; ?>

		<!-- Botanical Flourish Divider -->
		<div class="subpage-hero__flourish" aria-hidden="true">
			<span class="subpage-hero__flourish-line"></span>
			<span class="subpage-hero__flourish-symbol">✦</span>
			<span class="subpage-hero__flourish-line"></span>
		</div>

		<!-- Editorial Subtitle -->
		<?php if ( '' !== $sub ) : ?>
			<p class="common-subpage-hero__sub"><?php echo esc_html( $sub ); ?></p>
		<?php endif; ?>

		<!-- Hero Social Share Bar -->
		<?php if ( '' !== $share_url ) : ?>
			<div class="subpage-hero__share-bar">
				<span class="subpage-hero__share-label"><?php esc_html_e( 'SHARE:', 'vintagesoul' ); ?></span>
				<div class="subpage-hero__share-buttons">
					<a class="hero-share-btn hero-share-btn--whatsapp" href="https://api.whatsapp.com/send?text=<?php echo rawurlencode( $share_title . ' ' . $share_url ); ?>" target="_blank" rel="noopener" aria-label="Share on WhatsApp">
						<?php echo IconHelper::render( 'whatsapp', '#ffffff', 13 ); // phpcs:ignore ?>
						<span>WhatsApp</span>
					</a>
					<a class="hero-share-btn hero-share-btn--email" href="mailto:?subject=<?php echo rawurlencode( $share_title ); ?>&body=<?php echo rawurlencode( 'Read this chronicle on The Cane House: ' . $share_url ); ?>" aria-label="Share via Email">
						<?php echo IconHelper::render( 'mail', '#ffffff', 13 ); // phpcs:ignore ?>
						<span>Email</span>
					</a>
				</div>
			</div>
		<?php endif; ?>

	</div>
</header>

<!-- Gold Wave Divider -->
<div class="gold-wave-divider" aria-hidden="true">
	<img src="<?php echo esc_url( $gold_wave ); ?>" alt="" loading="lazy">
</div>
