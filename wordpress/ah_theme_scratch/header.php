<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<header class="site-header" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000;">
    <?php get_template_part('pages/components/header/nav-menu'); ?>
</header>

<div style="position: fixed; top: 60px; left: 0; right: 0; z-index: 999;">
    <?php get_template_part('pages/components/header/news-ticker'); ?>
</div>