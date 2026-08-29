<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;

$tag       = (string) ( $tag ?? 'Our Heritage' );
$title     = (string) ( $title ?? 'MORE THAN JUST A DRINK.' );
$subtitle  = (string) ( $subtitle ?? "It's a memory." );
$body      = (string) ( $body ?? 'Before supermarkets. Before energy drinks. Before everything became instant—there was sugarcane juice. Freshly pressed right before your eyes. Shared with friends and family. Enjoyed together under the summer sun.' );
$cta_label = (string) ( $cta_label ?? 'DISCOVER OUR STORY' );
$cta_route = (string) ( $cta_route ?? 'about' );
$image     = UrlHelper::resolve( (string) ( $image ?? 'assets/images/sugarcane/story_moments.jpg' ) );
?>
<section class="section section--intro intro-vintage paper-rough" id="intro-story">
	<div class="container intro-vintage__container">
		
		<!-- Header System -->
		<div class="intro-vintage__header">
			<span class="vintage-ribbon-tag">
				<span><?php echo esc_html( $tag ); ?></span>
			</span>
			<h2 class="intro-vintage__title"><?php echo esc_html( trim( strip_tags( $title ), " -—" ) ); ?></h2>
			<p class="section-eyebrow"><?php echo esc_html( strip_tags( $subtitle ) ); ?></p>
		</div>

		<!-- 2-Column Split Showcase -->
		<div class="intro-vintage__grid">
			<!-- Left: Story Narrative & Heritage Highlights -->
			<div class="intro-vintage__text-col">
				<p class="intro-vintage__lead">
					<?php echo esc_html( $body ); ?>
				</p>
				
				<div class="intro-vintage__highlights">
					<div class="intro-highlight-item">
						<div class="intro-highlight-icon">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
						</div>
						<div class="intro-highlight-content">
							<h4>100% RAW SUGARCANE</h4>
							<p>Never diluted with water, zero artificial preservatives, strictly unrefined.</p>
						</div>
					</div>

					<div class="intro-highlight-item">
						<div class="intro-highlight-icon">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
						</div>
						<div class="intro-highlight-content">
							<h4>PRESSED COLD IN SECONDS</h4>
							<p>Extracted instantly to lock in active vitamins, live enzymes, and minerals.</p>
						</div>
					</div>

					<div class="intro-highlight-item">
						<div class="intro-highlight-icon">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
						</div>
						<div class="intro-highlight-content">
							<h4>LONDON ARTISAN HERITAGE</h4>
							<p>Proudly serving Sutton and London festivals with warm family hospitality.</p>
						</div>
					</div>
				</div>

				<?php if ( '' !== $cta_label ) : ?>
					<div class="intro-vintage__cta">
						<a class="btn btn--primary-vintage" href="<?php echo esc_url( RouteService::url( $cta_route ) ); ?>">
							<span><?php echo esc_html( $cta_label ); ?></span>
							<span class="btn__arrow" aria-hidden="true">→</span>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<!-- Right: Framed Heritage Archival Photo with Stamp Badge -->
			<div class="intro-vintage__media-col">
				<div class="intro-vintage__frame-card frame--rough-cut">
					<div class="intro-vintage__image-box">
						<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
						<div class="intro-vintage__stamp">
							<span class="stamp-icon">🌾</span>
							<span class="stamp-text">AUTHENTIC CANE PRESS</span>
							<span class="stamp-sub">EST. 2014 · LONDON</span>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</section>
