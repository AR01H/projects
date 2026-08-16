<?php

use VintageSoul\Services\TerminologyService;

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="main">
	<div class="container not-found">
		<h1><?php echo esc_html( TerminologyService::label( 'not_found_title', __( 'Page Not Found', 'vintagesoul' ) ) ); ?></h1>
		<p><?php echo esc_html( TerminologyService::label( 'not_found_body' ) ); ?></p>
	</div>
</main>
<?php
get_footer();
