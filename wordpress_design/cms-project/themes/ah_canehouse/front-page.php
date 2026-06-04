<?php
defined( 'ABSPATH' ) || exit;
get_header();

$s = ch_get_settings();
?>

<main class="ch-main" id="main-content">

	<?php if ( ch_section_visible( 'news_ticker' ) ) : ?>
		<?php get_template_part( 'components/news-ticker' ); ?>
	<?php endif; ?>

	<?php if ( ch_section_visible( 'hero' ) ) : ?>
		<?php get_template_part( 'components/hero' ); ?>
	<?php endif; ?>

	<!-- Hero Banners(multiple and responsive) -->
	<?php get_template_part( 'components/home-banners' ); ?>

	<?php get_template_part( 'components/showcase-carousel', null, [
		'tag'   => 'See It Live',
		'title' => 'Our Machines, <span class="accent">Our Craft</span>',
		'body'  => 'From the press to the bottle — a closer look at how we bring fresh sugarcane to life.',
		'bg'    => 'var(--ch-bg-alt)',
		'id'    => 'sc-home',
		'items' => ch_get_showcase(),
	] ); ?>



	<?php if ( ch_section_visible( 'menu_builder' ) ) : ?>
		<?php get_template_part( 'components/menu-builder' ); ?>
	<?php endif; ?>

	<?php if ( ch_section_visible( 'benefits' ) ) : ?>
		<?php get_template_part( 'components/benefits' ); ?>
	<?php endif; ?>

	
	<!-- <?php if ( ch_section_visible( 'story' ) ) : ?>
		<?php get_template_part( 'components/story' ); ?>
	<?php endif; ?> -->
		
	<?php if ( ch_section_visible( 'hire' ) ) : ?>
			<?php get_template_part( 'components/hire-section' ); ?>
	<?php endif; ?>
	<?php get_template_part( 'components/booking-wizard' ); ?>

	
	<?php if ( ch_section_visible( 'franchise' ) ) : ?>
		<?php get_template_part( 'components/franchise-section' ); ?>
	<?php endif; ?>

	<?php get_template_part( 'components/franchise-enquiry' ); ?>
		
	<?php if ( ch_section_visible( 'faqs' ) ) : ?>
		<?php get_template_part( 'components/faq-section' ); ?>
	<?php endif; ?>
			
	<?php if ( ch_section_visible( 'contact' ) ) : ?>
		<?php get_template_part( 'components/contact-section' ); ?>
	<?php endif; ?>
				
	<?php if ( ch_section_visible( 'certifications' ) ) : ?>
		<?php get_template_part( 'components/certifications' ); ?>
	<?php endif; ?>

</main>

<?php get_footer(); ?>
