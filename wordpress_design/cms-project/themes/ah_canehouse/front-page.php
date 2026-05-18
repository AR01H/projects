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

	<?php if ( ch_section_visible( 'marquee' ) ) : ?>
		<?php get_template_part( 'components/marquee' ); ?>
	<?php endif; ?>

	<?php if ( ch_section_visible( 'how_to_order' ) ) : ?>
		<?php get_template_part( 'components/how-to-order' ); ?>
	<?php endif; ?>

	<?php if ( ch_section_visible( 'reviews' ) ) : ?>
		<?php get_template_part( 'components/review-carousel' ); ?>
	<?php endif; ?>

	<?php if ( ch_section_visible( 'menu_builder' ) ) : ?>
		<?php get_template_part( 'components/menu-builder' ); ?>
	<?php endif; ?>

	<?php if ( ch_section_visible( 'benefits' ) ) : ?>
		<?php get_template_part( 'components/benefits' ); ?>
	<?php endif; ?>

	<?php if ( ch_section_visible( 'story' ) ) : ?>
		<?php get_template_part( 'components/story' ); ?>
	<?php endif; ?>

	<?php if ( ch_section_visible( 'hire' ) ) : ?>
		<?php get_template_part( 'components/hire-section' ); ?>
	<?php endif; ?>

	<?php if ( ch_section_visible( 'franchise' ) ) : ?>
		<?php get_template_part( 'components/franchise-section' ); ?>
	<?php endif; ?>

	<?php if ( ch_section_visible( 'faqs' ) ) : ?>
		<?php get_template_part( 'components/faq-section' ); ?>
	<?php endif; ?>

	<?php if ( ch_section_visible( 'contact' ) ) : ?>
		<?php get_template_part( 'components/contact-section' ); ?>
	<?php endif; ?>

</main>

<?php get_footer(); ?>
