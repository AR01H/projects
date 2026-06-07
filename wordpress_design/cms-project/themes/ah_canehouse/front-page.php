<?php
defined('ABSPATH') || exit;
get_header();

$s = ch_get_settings();
?>

<main class="ch-main" id="main-content">

	<?php get_template_part('components/news-ticker'); ?>

	<?php get_template_part('components/home-banners'); ?>

	<?php get_template_part('components/hero'); ?>


	<?php get_template_part('components/showcase-carousel', null, [
		'tag' => 'See It Live',
		'title' => 'Our Machines, <span class="accent">Our Craft</span>',
		'body' => 'From the press to the bottle - a closer look at how we bring fresh sugarcane to life.',
		'bg' => 'var(--client-color-12)',
		'id' => 'sc-home',
		'items' => ch_get_equipment_media_gallery(),
	]); ?>
	
		<?php
		// function ch_get_showcase_new() {
		// 	return [

		// 		[
		// 			'type'    => 'image',
		// 			'src'     => get_template_directory_uri() . '/assets/images/machine-press.jpg',
		// 			'title' => 'Cold-press extraction in action',
		// 			'aspect'  => 'portrait',
		// 		],

		// 		[
		// 			'type'    => 'video',
		// 			'src'     => get_template_directory_uri() . '/assets/videos/bottling-line.mp4',
		// 			'title' => 'Our bottling line',
		// 			'aspect'  => 'portrait',
		// 		],

		// 		[
		// 			'type'    => 'youtube',
		// 			'src'     => 'https://www.youtube.com/embed/kZyd8FtGxvA?si=_Ufe04ZI8xhN0CoX',
		// 			'title' => 'Behind the scenes at Cane House',
		// 			'aspect'  => 'landscape',
		// 		],

		// 		[
		// 			'type'    => 'instagram',
		// 			'src'     => 'https://www.instagram.com/p/YOUR_POST_ID/',
		// 			'title' => 'Follow us @canehouse',
		// 			'aspect'  => 'portrait',
		// 		],

		// 		[
		// 			'type'    => 'image',
		// 			'src'     => 'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=600',
		// 			'title' => 'Fresh from the field',
		// 			'aspect'  => 'portrait',
		// 		],
		// 		[

		// 			'type'        => 'youtube',
		// 		'src'         => 'https://www.youtube.com/embed/kZyd8FtGxvA?si=_Ufe04ZI8xhN0CoX',
		// 		'title'       => 'How TAP Academy Helped Me',
		// 		'description' => 'A student shares their placement story.',
		// 		'aspect'      => 'portrait',
		// 		]


		// 	];
		// }


		// get_template_part( 'components/media-caurosel', null, [
		// 	'tag'   => 'See It Live',
		// 	'title' => 'Our Machines, <span class="accent">Our Craft</span>',
		// 	'body'  => 'From the press to the bottle - a closer look at how we bring fresh sugarcane to life.',
		// 	'bg'    => 'var(--client-color-12)',
		// 	'id'    => 'sc-home',
		// 	'items' => ch_get_showcase_new(),
		// ]);
		?>


	<?php get_template_part('components/menu-builder'); ?>

	<?php get_template_part('components/benefits'); ?>

	<?php get_template_part('components/story'); ?>

	<?php get_template_part('components/event-typesection'); ?>

	<?php get_template_part('components/booking-wizard'); ?>

	<?php get_template_part('components/franchise-section'); ?>

	<?php get_template_part('components/franchise-enquiry'); ?>

	<?php get_template_part('components/faq-section'); ?>

	<?php get_template_part('components/contact-section'); ?>

	<?php get_template_part('components/certifications'); ?>

</main>

<?php get_footer(); ?>