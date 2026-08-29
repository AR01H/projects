<?php

defined( 'ABSPATH' ) || exit;

$badges = (array) ( $badges ?? array(
	'FRESHLY PRESSED',
	'100% NATURAL',
	'NATURALLY REFRESHING',
	'MADE WITH CARE',
	'TRADITION IN EVERY GLASS',
) );
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
