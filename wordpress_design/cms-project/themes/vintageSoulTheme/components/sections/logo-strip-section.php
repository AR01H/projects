<?php
/**
 * VintageSoulTheme - As Featured & Trusted By Partner Logo Strip
 */
use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$strip_data = (array) ( JsonFileProvider::read( 'data/content/logo-strip.json' ) ?? array() );
$tag        = (string) ( $tag ?? ( $strip_data['tag'] ?? '' ) );
$title      = (string) ( $title ?? ( $strip_data['title'] ?? '' ) );
$sub        = (string) ( $sub ?? ( $strip_data['sub'] ?? '' ) );
$items      = (array) ( $items ?? ( $strip_data['items'] ?? array() ) );

if ( empty( $items ) ) {
	return;
}
?>
<section class="section section--logo-strip logo-strip-vintage" id="partners">
	<?php if ( '' !== $title || '' !== $tag || '' !== $sub ) : ?>
		<div class="container logo-strip-vintage__header">
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

	<div class="logo-strip-vintage__scroller">
		<div class="logo-strip-vintage__track">
			<?php for ( $loop = 0; $loop < 3; ++$loop ) : ?>
				<?php foreach ( $items as $item ) :
					$name         = (string) ( $item['name'] ?? '' );
					$image        = (string) ( $item['image'] ?? '' );
					$url          = trim( (string) ( $item['url'] ?? ( $item['link'] ?? '' ) ) );
					$resolved_img = '' !== $image ? UrlHelper::resolve( $image ) : '';
					if ( '' === $resolved_img ) {
						continue;
					}
				?>
					<?php if ( '' !== $url && '#' !== $url ) : ?>
						<a class="logo-strip-item-img logo-strip-item-img--link" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $name ); ?>" aria-label="<?php echo esc_attr( $name ); ?>">
							<img src="<?php echo esc_url( $resolved_img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" height="46">
						</a>
					<?php else : ?>
						<div class="logo-strip-item-img" title="<?php echo esc_attr( $name ); ?>">
							<img src="<?php echo esc_url( $resolved_img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" height="46">
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endfor; ?>
		</div>
	</div>
</section>
