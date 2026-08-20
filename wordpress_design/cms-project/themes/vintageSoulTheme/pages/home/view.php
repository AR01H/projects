<?php

use VintageSoul\Controllers\HomeController;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new HomeController() )->prepare();
$hero = (array) ( $data['hero'] ?? array() );
?>
<?php if ( ! empty( $hero['enabled'] ) ) : ?>
	<?php
	View::component(
		'hero/hero',
		array(
			'id'            => 'home-hero',
			'heading_level' => 1,
			'settings'      => (array) ( $hero['settings'] ?? array() ),
			'slides'        => (array) ( $hero['slides'] ?? array() ),
		)
	);
	?>
<?php endif; ?>
<div class="section">
	<div class="container">
		<?php ?>
	</div>
</div>
