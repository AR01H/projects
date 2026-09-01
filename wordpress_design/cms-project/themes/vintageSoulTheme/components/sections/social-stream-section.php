<?php
/**
 * VintageSoulTheme - Dual-Direction Infinite Social Media Stream Showcase
 *
 * Row 1: Right-to-Left infinite stream (Instagram Reels, TikToks & Fresh Juice moments)
 * Row 2: Left-to-Right infinite stream (YouTube Shorts, Live Stall & Festival crowds)
 */
use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\SettingsService;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$stream_data = (array) ( JsonFileProvider::read( 'data/content/social-stream.json' ) ?? array() );

$tag   = (string) ( $tag ?? ( $stream_data['tag'] ?? '' ) );
$title = (string) ( $title ?? ( $stream_data['title'] ?? '' ) );
$sub   = (string) ( $sub ?? ( $stream_data['sub'] ?? '' ) );

$row1_items = (array) ( $stream_data['row1'] ?? array() );
$row2_items = (array) ( $stream_data['row2'] ?? array() );
?>

<section class="section section--social-stream social-stream-vintage paper-rough" id="social-stream">
	
	<!-- Header System -->
	<?php if ( '' !== $title || '' !== $tag || '' !== $sub ) : ?>
		<div class="container container--narrow">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'    => $tag,
					'title'  => $title,
					'sub'    => $sub,
					'ribbon' => true,
				)
			);
			?>
		</div>
	<?php endif; ?>

	<!-- Infinite Double Card Streams -->
	<div class="social-stream__wrapper">
		
		<!-- Row 1: Right-to-Left Infinite Loop -->
		<?php if ( ! empty( $row1_items ) ) : ?>
			<?php
			View::component( 'card-stream/card-stream', array(
				'items'      => $row1_items,
				'card_type'  => 'social',
				'direction'  => 'rtl',
				'speed'      => 40,
				'aria_label' => (string) ( $stream_data['row1_label'] ?? '' ),
			) );
			?>
		<?php endif; ?>

		<!-- Row 2: Left-to-Right Infinite Loop -->
		<?php if ( ! empty( $row2_items ) ) : ?>
			<?php
			View::component( 'card-stream/card-stream', array(
				'items'      => $row2_items,
				'card_type'  => 'social',
				'direction'  => 'ltr',
				'speed'      => 45,
				'aria_label' => (string) ( $stream_data['row2_label'] ?? '' ),
			) );
			?>
		<?php endif; ?>

	</div>

	<!-- Social Follow Action Bar -->
	<?php
	$cta_cfg     = (array) ( $stream_data['cta'] ?? array() );
	$cta_title   = (string) ( $cta_cfg['title'] ?? '' );
	$cta_buttons = (array) ( $cta_cfg['buttons'] ?? array() );
	if ( ! empty( $cta_buttons ) || '' !== $cta_title ) :
	?>
		<div class="social-stream__cta-bar">
			<?php if ( '' !== $cta_title ) : ?>
				<div class="social-stream__cta-title">
					<span><?php echo esc_html( $cta_title ); ?></span>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $cta_buttons ) ) : ?>
				<div class="social-stream__cta-buttons">
					<?php foreach ( $cta_buttons as $btn ) :
						$network   = (string) ( $btn['network'] ?? '' );
						$btn_icon  = (string) ( $btn['icon'] ?? '' );
						$btn_lbl   = (string) ( $btn['label'] ?? '' );
						$btn_class = (string) ( $btn['class'] ?? 'btn--secondary-vintage' );
						$btn_url   = 'whatsapp' === $network ? SettingsService::whatsapp_url() : SettingsService::social_url( $network );
						if ( '' === $btn_url ) {
							continue;
						}
						$display_text = trim( $btn_icon . ' ' . $btn_lbl );
					?>
						<a class="btn <?php echo esc_attr( $btn_class ); ?>" href="<?php echo esc_url( $btn_url ); ?>" target="_blank" rel="noopener">
							<span><?php echo esc_html( $display_text ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

</section>
