<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;

$intro_data = (array) ( JsonFileProvider::read( 'data/content/intro.json' ) ?? array() );

$tag       = (string) ( $tag ?? ( $intro_data['tag'] ?? 'Our Heritage' ) );
$title     = (string) ( $title ?? ( $intro_data['title'] ?? 'MORE THAN JUST A DRINK.' ) );
$subtitle  = (string) ( $subtitle ?? ( $intro_data['subtitle'] ?? "It's a memory." ) );
$body      = (string) ( $body ?? ( $intro_data['body'] ?? 'Before supermarkets. Before energy drinks. Before everything became instant—there was sugarcane juice. Freshly pressed right before your eyes. Shared with friends and family. Enjoyed together under the summer sun.' ) );
$cta_label = (string) ( $cta_label ?? ( $intro_data['cta_label'] ?? 'DISCOVER OUR STORY' ) );
$cta_route = (string) ( $cta_route ?? ( $intro_data['cta_route'] ?? 'about' ) );
$images    = (array) ( $images ?? ( $intro_data['images'] ?? array() ) );
if ( empty( $images ) ) {
	$images = array(
		array(
			'image'       => 'assets/images/sugarcane/story_moments.jpg',
			'title'       => 'Traditional Street Stall Heritage',
			'caption'     => 'Generations sharing freshly pressed sugarcane under the warm summer sunshine with authentic family hospitality.',
			'author'      => 'The Cane House Heritage',
			'meta'        => 'Traditional Roadside Stall · 1950s',
			'tag'         => 'Heritage Stall',
			'rotation'    => '-2.5deg',
			'is_featured' => true,
		),
	);
}
?>
<section class="section section--intro intro-vintage paper-rough" id="intro-story">
	<div class="container intro-vintage__container">
		
		<!-- Header System -->
		<div class="intro-vintage__header">
			<span class="vintage-ribbon-tag">
				<span><?php echo esc_html( $tag ); ?></span>
			</span>
			<h2 class="intro-vintage__title">MORE THAN JUST A <em>Drink</em></h2>
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

			<!-- Right: Interactive Vintage Heritage Archival Photo Stack -->
			<div class="intro-vintage__media-col">
				<div class="intro-vintage__photo-stack">
					<?php foreach ( $images as $idx => $img_item ) :
						$img_url     = UrlHelper::resolve( (string) ( $img_item['image'] ?? 'assets/images/sugarcane/story_moments.jpg' ) );
						$img_title   = (string) ( $img_item['title'] ?? 'Heritage Moment' );
						$img_caption = (string) ( $img_item['caption'] ?? '' );
						$img_author  = (string) ( $img_item['author'] ?? 'The Cane House' );
						$img_meta    = (string) ( $img_item['meta'] ?? 'Archival Snapshot' );
						$img_tag     = (string) ( $img_item['tag'] ?? 'Heritage' );
						$img_rot     = (string) ( $img_item['rotation'] ?? '0deg' );
						$is_feat     = ! empty( $img_item['is_featured'] ) || 0 === $idx;
					?>
						<div class="intro-photo-card card--rough-cut <?php echo $is_feat ? 'intro-photo-card--featured' : 'intro-photo-card--secondary intro-photo-card--sub-' . ( $idx ); ?>"
						     style="--intro-rot: <?php echo esc_attr( $img_rot ); ?>;"
						     role="button"
						     tabindex="0"
						     data-story-modal="true"
						     data-story-image="<?php echo esc_url( $img_url ); ?>"
						     data-story-quote="<?php echo esc_attr( $img_caption ); ?>"
						     data-story-author="<?php echo esc_attr( $img_author ); ?>"
						     data-story-meta="<?php echo esc_attr( $img_meta ); ?>"
						     data-story-badge="<?php echo esc_attr( $img_tag ); ?>"
						     data-story-title="<?php echo esc_attr( $img_title ); ?>"
						     aria-label="<?php echo esc_attr( $img_title ); ?>">
							
							<div class="intro-photo-card__image-box">
								<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $img_title ); ?>" loading="lazy">
							</div>

							<div class="intro-photo-card__label-bar">
								<span class="intro-photo-card__tag"><?php echo esc_html( $img_tag ); ?></span>
								<span class="intro-photo-card__title"><?php echo esc_html( $img_title ); ?></span>
								<span class="intro-photo-card__action" aria-hidden="true">✦</span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

	</div>
</section>
