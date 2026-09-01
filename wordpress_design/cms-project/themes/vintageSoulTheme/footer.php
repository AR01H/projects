<?php

use VintageSoul\Controllers\FooterController;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;
?>
</main>

<?php
View::component( 'footer/footer', ( new FooterController() )->prepare() );
View::component( 'dialog/story-modal' );
View::component( 'floating-whatsapp/floating-whatsapp' );
View::component( 'scroll-top/scroll-top' );
View::component( 'section-nav/section-nav' );
?>

<?php wp_footer(); ?>
</body>
</html>
