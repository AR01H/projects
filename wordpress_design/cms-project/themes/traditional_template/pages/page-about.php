<?php
/**
 * About / History Page
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="site-main">
    <?php if ( nt_section_visible( 'about_history' ) )        get_template_part('components/company-history'); ?>
    <?php if ( nt_section_visible( 'about_experience' ) )     get_template_part('components/product-experience'); ?>
    <?php if ( nt_section_visible( 'about_certifications' ) ) get_template_part('components/features-certifications'); ?>
</main>
<?php
get_footer();
