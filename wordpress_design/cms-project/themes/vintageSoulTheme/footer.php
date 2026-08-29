<?php

use VintageSoul\Controllers\FooterController;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;
?>
</main>

<?php
View::component( 'footer/footer', ( new FooterController() )->prepare() );
View::component( 'scroll-top/scroll-top' );
?>

<?php wp_footer(); ?>
</body>
</html>
