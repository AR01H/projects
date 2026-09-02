<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\View;

$community_data = (array) ( JsonFileProvider::read( 'data/content/community.json' ) ?? array() );

$tag     = (string) ( $tag ?? ( $community_data['tag'] ?? '' ) );
$title   = (string) ( $title ?? ( $community_data['title'] ?? '' ) );
$eyebrow = (string) ( $eyebrow ?? ( $community_data['eyebrow'] ?? '' ) );
$body    = (string) ( $body ?? ( $community_data['body'] ?? '' ) );
$items   = (array) ( $items ?? ( $community_data['items'] ?? array() ) );
?>
<section class="section section--community community-vintage paper-rough" id="community">
	<div class="container community-vintage__container">
		
		<!-- Header System -->
		<?php if ( '' !== $title || '' !== $tag || '' !== $body ) : ?>
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'    => $tag,
					'title'  => $title,
					'sub'    => $body,
					'ribbon' => true,
				)
			);
			?>
		<?php endif; ?>

		<!-- Community Grid (1 row per card on mobile) -->
		<?php if ( ! empty( $items ) ) : ?>
			<div class="community-vintage__grid">
				<?php foreach ( $items as $item ) :
					$c_icon  = (string) ( $item['icon'] ?? 'heart' );
					$c_title = (string) ( $item['title'] ?? '' );
					$c_tag   = (string) ( $item['tag'] ?? '' );
					$c_desc  = (string) ( $item['desc'] ?? '' );
				?>
					<div class="community-card card--paper-cut">
						<div class="community-card__icon-box">
							<?php echo IconHelper::render( $c_icon, '#f6d599', 22 ); // phpcs:ignore ?>
						</div>
						<div class="community-card__content">
							<div class="community-card__top">
								<h3 class="community-card__title"><?php echo esc_html( $c_title ); ?></h3>
								<?php if ( '' !== $c_tag ) : ?>
									<span class="community-card__tag"><?php echo esc_html( $c_tag ); ?></span>
								<?php endif; ?>
							</div>
							<p class="community-card__desc"><?php echo esc_html( $c_desc ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
