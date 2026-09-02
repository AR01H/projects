<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\View;

$benefits_data = (array) ( JsonFileProvider::read( 'data/content/benefits.json' ) ?? array() );

$tag   = (string) ( $tag ?? ( $benefits_data['tag'] ?? '' ) );
$title = (string) ( $title ?? ( $benefits_data['title'] ?? '' ) );
$sub   = (string) ( $sub ?? ( $benefits_data['sub'] ?? '' ) );
$items = (array) ( $items ?? ( $benefits_data['items'] ?? array() ) );
?>
<section class="section section--benefits benefits-vintage paper-rough" id="benefits">
	<div class="container benefits-vintage__container">
		
		<!-- Header System (3 Elements: Tag, Title, Subtitle) -->
		<?php if ( '' !== $title || '' !== $tag || '' !== $sub ) : ?>
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
		<?php endif; ?>

		<!-- 6-Benefit Wellness Grid (1 item per row on mobile, 2 col tablet, 3 col desktop) -->
		<?php if ( ! empty( $items ) ) : ?>
			<div class="benefits-vintage__grid">
				<?php foreach ( $items as $item ) :
					$b_icon  = (string) ( $item['icon'] ?? 'leaf' );
					$b_title = (string) ( $item['title'] ?? '' );
					$b_stat  = (string) ( $item['stat'] ?? '' );
					$b_text  = (string) ( $item['text'] ?? '' );
				?>
					<div class="benefit-card card--paper-cut">
						<div class="benefit-card__icon-box">
							<?php echo IconHelper::render( $b_icon, '#f6d599', 22 ); // phpcs:ignore ?>
						</div>
						<div class="benefit-card__content">
							<div class="benefit-card__top">
								<h3 class="benefit-card__title"><?php echo esc_html( $b_title ); ?></h3>
								<?php if ( '' !== $b_stat ) : ?>
									<span class="benefit-card__badge"><?php echo esc_html( $b_stat ); ?></span>
								<?php endif; ?>
							</div>
							<p class="benefit-card__text"><?php echo esc_html( $b_text ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
