<?php
defined( 'ABSPATH' ) || exit;
get_header();
$hp = require get_template_directory() . '/intermediate_logics/home-page.php';
?>
<div class="nhp-wrap">
<?php
get_template_part( 'components/home/hero',     null, $hp );
get_template_part( 'components/home/bento',    null, $hp );
get_template_part( 'components/home/topics',   null, $hp );
get_template_part( 'components/home/articles', null, $hp );
?>
</div>
<?php
get_template_part( 'components/cta-section', null, [] );
get_footer();
