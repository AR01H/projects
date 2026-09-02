<?php

use VintageSoul\Controllers\AboutController;
use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new AboutController() )->prepare();

$hero   = (array) ( $data['hero'] ?? array() );
$intro  = (array) ( $data['intro'] ?? array() );
$values = (array) ( $data['values'] ?? array() );
$story  = (array) ( $data['story'] ?? array() );

$team       = (array) JsonFileProvider::read( 'data/content/team.json' );
$milestones = (array) JsonFileProvider::read( 'data/content/milestones.json' );

$team_members   = (array) ( $team['items'] ?? array() );
$milestone_items = (array) ( $milestones['items'] ?? array() );
?>

<div class="about-page">
	<?php View::component( 'background/parchment-botanical-bg', array( 'seed' => 23 ) ); ?>
	
	<!-- ═══════════ 1. MASTER VINTAGE HERO ═══════════ -->
	<?php
	View::component(
		'subpage-hero/subpage-hero',
		array(
			'id'    => 'about-hero',
			'tag'   => (string) ( $hero['tag'] ?? '' ),
			'title' => (string) ( $hero['title'] ?? '' ),
			'sub'   => (string) ( $hero['sub'] ?? '' ),
			'image' => (string) ( $hero['image'] ?? '' ),
		)
	);
	?>

	<!-- ═══════════ 2. INTRO: MORE THAN JUST A CROP ═══════════ -->
	<?php View::component( 'sections/about-intro-section', array( 'intro' => $intro ) ); ?>

	<!-- Gold Wave Divider -->
	<div class="gold-wave-divider" aria-hidden="true">
		<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/textures/border/gold-wave.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<!-- ═══════════ 3. OUR SERVICES ═══════════ -->
	<?php View::component( 'sections/about-services-section', array( 'intro' => $intro ) ); ?>

	<!-- Deckled Edge Divider -->
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/textures/border/deckled-edge.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<!-- ═══════════ 4. FOUR PILLARS (Dark Botanical) ═══════════ -->
	<?php View::component( 'sections/pillars-section', array( 'story' => $story ) ); ?>

	<!-- ═══════════ 5. OUR CORE VALUES ═══════════ -->
	<?php View::component( 'sections/values-section', array( 'values' => $values ) ); ?>

	<!-- Gold Wave Divider -->
	<div class="gold-wave-divider" aria-hidden="true">
		<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/textures/border/gold-wave.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<!-- ═══════════ 6. OUR MILESTONES TIMELINE ═══════════ -->
	<?php if ( ! empty( $milestone_items ) ) : ?>
		<?php
		View::component(
			'timeline/timeline',
			array(
				'tag'   => (string) ( $milestones['tag'] ?? '' ),
				'title' => (string) ( $milestones['title'] ?? '' ),
				'sub'   => (string) ( $milestones['sub'] ?? '' ),
				'items' => $milestone_items,
			)
		);
		?>
	<?php endif; ?>

	<!-- Deckled Edge Divider -->
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/textures/border/deckled-edge.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<!-- ═══════════ 7. MEET THE CANE FAMILY (Dark Botanical Stream) ═══════════ -->
	<?php if ( ! empty( $team_members ) ) : ?>
		<section class="section section--dark-botanical about-team-section" id="team" style="padding-top: 36px; padding-bottom: 44px;">
			<div class="container">
				<?php
				View::component(
					'section-header/section-header',
					array(
						'tag'     => (string) ( $team['tag'] ?? '' ),
						'title'   => (string) ( $team['title'] ?? '' ),
						'sub'     => (string) ( $team['sub'] ?? '' ),
						'variant' => 'dark',
						'ribbon'  => true,
					)
				);

				View::component( 'card-stream/card-stream', array(
					'items'      => $team_members,
					'card_type'  => 'team',
					'direction'  => 'ltr',
					'aria_label' => (string) ( $team['aria_label'] ?? ( $team['title'] ?? '' ) ),
				) );
				?>
			</div>
		</section>
	<?php endif; ?>

	<!-- Deckled Edge Divider -->
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/textures/border/deckled-edge.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<!-- ═══════════ 8. QUALITY & CERTIFICATIONS (Food Safety Registered) ═══════════ -->
	<?php View::component( 'sections/certifications-section' ); ?>

	<!-- Gold Wave Divider -->
	<div class="gold-wave-divider" aria-hidden="true">
		<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/textures/border/gold-wave.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<!-- ═══════════ 9. PHOTO GALLERY ARCHIVE ═══════════ -->
	<?php View::component( 'sections/gallery-section' ); ?>

	<!-- Gold Wave Divider -->
	<div class="gold-wave-divider" aria-hidden="true">
		<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/textures/border/gold-wave.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<!-- ═══════════ 10. LOGO STRIP / PARTNERS ═══════════ -->
	<?php View::component( 'sections/logo-strip-section' ); ?>

	<!-- ═══════════ 11. CLOSING CTA BANNER ═══════════ -->
	<?php if ( ! empty( $intro['closing'] ) ) : ?>
		<?php View::component( 'cta-banner/cta-banner', (array) $intro['closing'] ); ?>
	<?php endif; ?>

	<!-- ═══════════ 12. TRUST RIBBON ═══════════ -->
	<?php View::component( 'sections/trust-ribbon-section' ); ?>
</div>
