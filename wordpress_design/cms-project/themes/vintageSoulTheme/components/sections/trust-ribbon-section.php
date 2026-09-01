<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\SettingsService;

$feature_badges = (array) ( JsonFileProvider::read( 'data/content/feature-badges.json' ) ?? array() );
$badge_items    = (array) ( $feature_badges['items'] ?? array() );

if ( ! empty( $badge_items ) ) {
	$badges = array_map( function( $item ) {
		return is_array( $item ) ? (string) ( $item['label'] ?? '' ) : (string) $item;
	}, $badge_items );
} else {
	$badges = SettingsService::preheader();
}
?>
<div class="trust-ribbon torn-dark-block grain-dark">
	<div class="container trust-ribbon__container">
		<ul class="trust-ribbon__list">
			<?php foreach ( $badges as $idx => $badge ) : ?>
				<?php if ( $idx > 0 ) : ?>
					<li class="trust-ribbon__divider" aria-hidden="true">❦</li>
				<?php endif; ?>
				<li class="trust-ribbon__item"><?php echo esc_html( (string) $badge ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
