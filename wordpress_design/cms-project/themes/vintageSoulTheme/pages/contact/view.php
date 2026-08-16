<?php

use VintageSoul\Controllers\PageController;

defined( 'ABSPATH' ) || exit;

$data = ( new PageController() )->prepare();
?>
<div class="section">
	<div class="container container--narrow">
		<h1><?php echo esc_html( $data['title'] ); ?></h1>
		<?php the_content(); ?>
	</div>
</div>
