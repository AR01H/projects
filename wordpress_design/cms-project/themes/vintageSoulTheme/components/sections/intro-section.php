<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;

$intro_data = (array) ( JsonFileProvider::read( 'data/content/intro.json' ) ?? array() );

$tag        = (string) ( $tag ?? ( $intro_data['tag'] ?? '' ) );
$title      = (string) ( $title ?? ( $intro_data['title'] ?? '' ) );
$subtitle   = (string) ( $subtitle ?? ( $intro_data['subtitle'] ?? '' ) );
$body       = (string) ( $body ?? ( $intro_data['body'] ?? '' ) );
$cta_label  = (string) ( $cta_label ?? ( $intro_data['cta_label'] ?? '' ) );
$cta_route  = (string) ( $cta_route ?? ( $intro_data['cta_route'] ?? 'contact' ) );
$highlights = (array) ( $intro_data['highlights'] ?? array() );
$images     = (array) ( $images ?? ( $intro_data['images'] ?? array() ) );
?>
<section class="section section--intro intro-vintage paper-rough" id="intro-story">
	<div class="container intro-vintage__container">
		
		<!-- Header System -->
		<div class="intro-vintage__header">
			<?php if ( '' !== $tag ) : ?>
				<span class="vintage-ribbon-tag">
					<span><?php echo esc_html( $tag ); ?></span>
				</span>
			<?php endif; ?>
			<?php if ( '' !== $title ) : ?>
				<h2 class="intro-vintage__title"><?php echo wp_kses_post( $title ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $subtitle ) : ?>
				<p class="section-eyebrow"><?php echo esc_html( strip_tags( $subtitle ) ); ?></p>
			<?php endif; ?>
		</div>

		<!-- 2-Column Split Showcase -->
		<div class="intro-vintage__grid">
			<!-- Left: Story Narrative & Heritage Highlights -->
			<div class="intro-vintage__text-col">
				<?php if ( '' !== $body ) : ?>
					<div class="intro-vintage__lead">
						<?php echo wp_kses_post( wpautop( $body ) ); ?>
					</div>
				<?php endif; ?>
				
				<?php if ( ! empty( $highlights ) ) : ?>
					<div class="intro-vintage__highlights">
						<?php foreach ( $highlights as $hl ) :
							$hl_title = (string) ( $hl['title'] ?? '' );
							$hl_desc  = (string) ( $hl['desc'] ?? '' );
						?>
							<div class="intro-highlight-item">
								<div class="intro-highlight-icon">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
								</div>
								<div class="intro-highlight-content">
									<?php if ( '' !== $hl_title ) : ?>
										<h4><?php echo esc_html( $hl_title ); ?></h4>
									<?php endif; ?>
									<?php if ( '' !== $hl_desc ) : ?>
										<p><?php echo esc_html( $hl_desc ); ?></p>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

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
			<?php if ( ! empty( $images ) ) : ?>
				<div class="intro-vintage__media-col">
					<div class="intro-vintage__photo-stack">
						<?php foreach ( $images as $idx => $img_item ) :
							$img_url     = UrlHelper::resolve( (string) ( $img_item['image'] ?? '' ) );
							$img_title   = (string) ( $img_item['title'] ?? '' );
							$img_caption = (string) ( $img_item['caption'] ?? '' );
							$img_author  = (string) ( $img_item['author'] ?? '' );
							$img_meta    = (string) ( $img_item['meta'] ?? '' );
							$img_tag     = (string) ( $img_item['tag'] ?? '' );
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
									<?php if ( '' !== $img_tag ) : ?>
										<span class="intro-photo-card__tag"><?php echo esc_html( $img_tag ); ?></span>
									<?php endif; ?>
									<?php if ( '' !== $img_title ) : ?>
										<span class="intro-photo-card__title"><?php echo esc_html( $img_title ); ?></span>
									<?php endif; ?>
									<span class="intro-photo-card__action" aria-hidden="true">✦</span>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>

	</div>
</section>
