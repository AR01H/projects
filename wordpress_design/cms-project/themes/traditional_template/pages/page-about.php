<?php
/**
 * About / History Page
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="site-main">
    <?php get_template_part('components/company-history'); ?>
    <?php get_template_part('components/product-experience'); ?>
    <?php get_template_part('components/features-certifications'); ?>
</main>
<?php
get_footer();
