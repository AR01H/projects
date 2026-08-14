<?php
/**
 * pages/page-home.php - Home.
 *
 * Every real page template is the same 4 lines (see ARCHITECTURE.md
 * "Compose a page's sections"): get_header() -> <main> -> app_render_sections()
 * -> get_footer(). It lists no components itself - the ordered section list
 * lives in admin/data/page_sections.json['home'], each pointing at an
 * existing reusable component + its own admin/data/*.json content.
 */
defined( 'ABSPATH' ) || exit;


// Pre Header

get_header();
?>


<main id="main" class="app-main">

	<?php
	//  app_render_sections( 'home' ); 
	?>


<!-- Videos Hero Banner -->
<!-- General Hero Banner -->
<!-- Marquee -->
<!-- Statistics of Sugarcane -->
<!-- Stats -->
<!-- Little Info about Sugarcane/ Memory with Sugarcane -->
<!-- Our Story -->
<!-- Our Drinks -->
<!-- Partner/Resources -->
<!-- How We Serve -->
<!-- Gallery -->
<!-- Memories of Sugarcane -->
<!-- Our Events (Event info cards+Event Images+Event Images) -->
<!-- Our Franchise (Event info cards/oppurtuniteis+Event Images+Event Images) -->
<!-- Mini Banner -->


<!-- Benefits of Cane -->
<!-- Quality Certificates -->
<!-- Reviews -->
<!-- Contat US (contact component + contact info) -->
<!-- FAQs -->


</main>

<!-- Pre Footer -->

<?php
get_footer();


// Post Header