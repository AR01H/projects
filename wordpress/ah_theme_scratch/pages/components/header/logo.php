<?php
// Default values if nothing is passed
$logo_height = get_query_var('logo_height', 50);
$logo_width  = get_query_var('logo_width', 50);
$logo_class  = get_query_var('logo_class', '');
?>
<div class="site-logo <?php echo esc_attr($logo_class); ?>">
    <a href="<?php echo home_url(); ?>">
        <img
            src="<?php echo mytheme_image('logo.png'); ?>"
            alt="<?php bloginfo('name'); ?>"
            height="<?php echo esc_attr($logo_height); ?>"
            width="<?php echo esc_attr($logo_width); ?>"
        >
    </a>
</div>