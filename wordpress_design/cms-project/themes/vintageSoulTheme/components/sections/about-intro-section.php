<?php
/**
 * VintageSoulTheme - About Page Intro Section
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$intro_data = ! empty( $intro ) && is_array( $intro ) ? $intro : (array) JsonFileProvider::read( 'data/content/about-intro.json' );

$tag   = (string) ( $intro_data['sub'] ?? '' );
$title = (string) ( $intro_data['title'] ?? '' );
$body  = (string) ( $intro_data['body'] ?? '' );
$img   = (string) ( $intro_data['media_image'] ?? '' );
?>
<section class="section about-intro-section">
	<div class="container container--narrow about-intro__container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'   => $tag,
				'title' => $title,
			)
		);
		?>
		<?php if ( '' !== $body ) : ?>
			<p class="about-intro__text"><?php echo esc_html( $body ); ?></p>
		<?php endif; ?>
		<div class="about-intro__media frame--ornate">
			<img src="<?php echo esc_url( UrlHelper::resolve( $img ) ); ?>" alt="<?php echo esc_attr( strip_tags( $title ) ); ?>" loading="lazy">
		</div>
	</div>
</section>
