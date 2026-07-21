<?php defined( 'ABSPATH' ) || exit; get_header(); ?><main class='site-main'><?php
if ( nt_section_visible( 'order' ) ) get_template_part('components/order-to-deliver');
if ( nt_section_visible( 'faqs' ) )  get_template_part('components/faqs');
?></main><?php get_footer(); ?>
