<?php
/**
 * The template for displaying the home/front page.
 */
get_header(); ?>

<!-- ── HERO ── -->
<?php get_template_part('pages/components/home/hero'); ?>

<!-- ── WHY YOU NEED US ── -->
<?php get_template_part('pages/components/home/why-us'); ?>

<!-- ── GUIDING ── -->
<?php get_template_part('pages/components/home/guiding'); ?>

<!-- ── STATS ── -->
<?php get_template_part('pages/components/home/stats'); ?>

<!-- ── FEATURED PROPERTIES (3D CAROUSEL) ── -->
<?php get_template_part('pages/components/home/featured-properties'); ?>

<!-- ── PODCASTS & INSIGHTS ── -->
<?php get_template_part('pages/components/home/podcasts'); ?>

<!-- ── REUSABLE SERVICES GRID ── -->
<?php get_template_part('pages/components/home/services'); ?>

<!-- ── TESTIMONIALS ── -->
<?php get_template_part('pages/components/home/testimonials'); ?>

<!-- ── FINAL CTA ── -->
<?php get_template_part('pages/components/home/cta'); ?>

<?php get_footer(); ?>