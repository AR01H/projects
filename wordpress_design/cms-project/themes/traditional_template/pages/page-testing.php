<?php
/**
 * pages/page-testing.php - Component Showcase / development page.
 *
 * This is NOT a website page. It is the one page kept after the theme was
 * reset to a clean component-development foundation (see config/pages.php -
 * every real content page was removed). It exists so every reusable
 * component still in components/ can be reviewed, in isolation, from one
 * URL: /testing/.
 *
 * Each block below calls an EXISTING component through the normal
 * App_Helpers::component() / get_template_part() APIs with real (or
 * representative) data from admin/data/*.json - nothing here is a new
 * component, just a labelled demo call. When a real page is rebuilt, copy
 * the call you need into admin/data/page_sections.json (see ARCHITECTURE.md
 * "Compose a page's sections") rather than copying markup from here.
 *
 * Header / navigation / logo / footer are NOT re-demoed below - they are
 * already live at the top and bottom of this page (get_header() / get_footer()
 * render navigation/main_header + navigation/main_footer on every page, this one
 * included).
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'nt_dev_heading' ) ) {
	/**
	 * Local-only heading for one showcase block. Not a component - this file
	 * is the single place it is used.
	 */
	function nt_dev_heading( $index, $label, $ref = '', $note = '' ) {
		?>
		<div class="dev-block__head">
			<span class="dev-block__index"><?php echo esc_html( (string) $index ); ?></span>
			<h2 class="dev-block__label"><?php echo esc_html( $label ); ?></h2>
			<?php if ( '' !== $ref ) : ?>
				<span class="dev-block__ref"><?php echo esc_html( $ref ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( '' !== $note ) : ?>
			<p class="dev-block__note"><?php echo esc_html( $note ); ?></p>
		<?php endif;
	}
}

// Kept in sync by hand with the <section id="..."> list below.
$nt_dev_toc = array(
	array( 'typography',        'Typography' ),
	array( 'buttons',           'Buttons' ),
	array( 'alerts',            'Alerts' ),
	array( 'breadcrumbs',       'Breadcrumbs' ),
	array( 'section-headers',   'Section Headers' ),
	array( 'stamp',             'Stamp' ),
	array( 'hero-movie-header', 'Hero (Movie Header)' ),
	array( 'hero-page-header',  'Hero (Page Header)' ),
	array( 'media-carousel',    'Media Carousel' ),
	array( 'home-banner',       'Home Banner' ),
	array( 'photo-carousel',    'Photo Carousel' ),
	array( 'ticker',            'Ticker' ),
	array( 'our-story',         'Our Story' ),
	array( 'company-history',   'Company History' ),
	array( 'milestones',        'Milestones' ),
	array( 'process-steps',     'Process Steps' ),
	array( 'video-feature',     'Video Feature' ),
	array( 'values',            'Values' ),
	array( 'certifications',    'Features & Certifications' ),
	array( 'features-in',       'Features In' ),
	array( 'team',              'Team' ),
	array( 'stats-bar',         'Stats Bar' ),
	array( 'reviews',           'Reviews / Testimonials' ),
	array( 'logo-strip',        'Logo Strip' ),
	array( 'our-drinks',        'Our Drinks' ),
	array( 'signature-flavours','Signature Flavours' ),
	array( 'product-menu',      'Product Menu (Cards)' ),
	array( 'products-list',     'Product Builder' ),
	array( 'product-benefits',  'Product Benefits' ),
	array( 'product-experience','Product Experience' ),
	array( 'filter-cards',      'Filter Cards' ),
	array( 'pricing-tiers',     'Pricing Tiers' ),
	array( 'compare-table',     'Compare Table' ),
	array( 'franchise-section', 'Franchise Section' ),
	array( 'events-preview',    'Events Preview' ),
	array( 'events-catering',   'Events & Catering' ),
	array( 'order-to-deliver',  'Order To Deliver' ),
	array( 'cta-banner',        'CTA Banner' ),
	array( 'gallery-grid',      'Gallery Grid' ),
	array( 'post-card',         'Post Card' ),
	array( 'posts-preview',     'Posts Preview' ),
	array( 'blog-sidebar',      'Blog Sidebar' ),
	array( 'paper-story',       'Paper Story' ),
	array( 'contact-section',   'Contact Section' ),
	array( 'locations',         'Locations' ),
	array( 'opening-hours',     'Opening Hours' ),
	array( 'map-embed',         'Map Embed' ),
	array( 'newsletter',        'Newsletter' ),
	array( 'faqs',              'FAQs (Accordion)' ),
	array( 'quick-links',       'Quick Links' ),
	array( 'promo-block',       'Promo Block' ),
	array( 'info-cards',        'Info Cards' ),
	array( 'downloads',         'Downloads' ),
	array( 'careers',           'Careers' ),
	array( 'spotlights',        'Spotlights' ),
	array( 'newsbar',           'Newsbar' ),
	array( 'legal-document',    'Legal Document' ),
	array( 'tabs',              'Tabs' ),
	array( 'split-feature',     'Split Feature' ),
	array( 'generic-form',      'Forms & Inputs' ),
	array( 'multistep-wizard',  'Multi-step Wizard' ),
	array( 'generic-dialog',    'Generic Dialog' ),
);

get_header();
?>
<div class="site-main app-inner-page dev-page" id="main-content">

	<div class="dev-intro">
		<span class="dev-intro__kicker">Development Only</span>
		<h1 class="dev-intro__title">Component Showcase</h1>
		<p class="dev-intro__note">
			Every reusable component currently in <code>components/</code>, rendered once each with real
			or representative data, so it can be reviewed and styled from a single page. This is not the
			site homepage - real pages are rebuilt by adding entries back to
			<code>admin/data/page_sections.json</code>.
		</p>
	</div>

	<nav class="dev-toc" aria-label="Component index">
		<ul class="dev-toc__list">
			<?php foreach ( $nt_dev_toc as $nt_item ) : ?>
				<li><a href="#<?php echo esc_attr( $nt_item[0] ); ?>"><?php echo esc_html( $nt_item[1] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<p class="dev-chrome-note">
		Header, navigation, logo and footer are not repeated below - they are already rendered on this
		page by <code>navigation/main_header</code> (top) and <code>navigation/main_footer</code> (bottom), exactly
		as on every other page.
	</p>

	<!-- ═══════════════════════════ 1. UI PRIMITIVES ═══════════════════════════ -->

	<section class="dev-block" id="typography">
		<?php nt_dev_heading( '01', 'Typography', 'assets/css/main.css', 'Raw heading levels and body copy - global styling, no component involved.' ); ?>
		<div class="dev-demo">
			<h1>Heading One - The Cane House</h1>
			<h2>Heading Two - Pressed To Order</h2>
			<h3>Heading Three - Field To Glass</h3>
			<h4>Heading Four - Since 1974</h4>
			<h5>Heading Five - Small Batch</h5>
			<p>
				Body copy sits on <code>--app-font-body</code>. It should read comfortably at length -
				<em>emphasis</em>, <strong>strong emphasis</strong> and a
				<a href="#typography">plain inline link</a> all need to stay legible against the parchment
				background.
			</p>
			<ul>
				<li>Unordered list item one</li>
				<li>Unordered list item two</li>
			</ul>
			<ol>
				<li>Ordered list item one</li>
				<li>Ordered list item two</li>
			</ol>
		</div>
	</section>

	<section class="dev-block" id="buttons">
		<?php nt_dev_heading( '02', 'Buttons', 'assets/css/main.css', 'The four current button classes: .btn / .btn-primary, .btn-gold, .btn-outline-gold, .btn-outline.' ); ?>
		<div class="dev-demo dev-demo--flex">
			<a href="#buttons" class="btn">Primary Button</a>
			<a href="#buttons" class="btn-gold">Gold Button</a>
			<a href="#buttons" class="btn-outline-gold">Outline Gold</a>
			<a href="#buttons" class="btn-outline">Outline</a>
			<button type="button" class="btn" disabled>Disabled</button>
		</div>
	</section>

	<section class="dev-block" id="alerts">
		<?php nt_dev_heading( '03', 'Alerts', 'components/alerts/alert.php via app_alert()', 'Inline note boxes - one call per tone.' ); ?>
		<div class="dev-demo">
			<?php
			app_alert( array( 'tone' => 'info', 'title' => 'Heads up', 'body' => 'This is an informational alert.' ) );
			app_alert( array( 'tone' => 'success', 'title' => 'Saved', 'body' => 'This is a success alert.' ) );
			app_alert( array( 'tone' => 'warning', 'title' => 'Careful', 'body' => 'This is a warning alert.' ) );
			app_alert( array( 'tone' => 'error', 'title' => 'Something went wrong', 'body' => 'This is an error alert.', 'dismissible' => true ) );
			app_alert( array( 'tone' => 'note', 'body' => 'A compact, dismissible note.', 'compact' => true, 'dismissible' => true, 'link_label' => 'Learn more', 'link_url' => '#alerts' ) );
			?>
		</div>
	</section>

	<section class="dev-block" id="breadcrumbs">
		<?php nt_dev_heading( '04', 'Breadcrumbs', 'components/breadcrumbs/breadcrumbs.php', 'Builds itself from the current page + config/pages.php - no args needed.' ); ?>
		<div class="dev-demo">
			<?php App_Helpers::component( 'breadcrumbs/breadcrumbs' ); ?>
		</div>
	</section>

	<section class="dev-block" id="section-headers">
		<?php nt_dev_heading( '05', 'Section Headers', 'components/section-heading/section-header(-dark).php', 'The eyebrow + title + body pattern reused above almost every section.' ); ?>
		<div class="dev-demo">
			<?php
			// These two "parts" take their args the get_template_part() way
			// (read as $args['key'], not extracted bare variables) - that is
			// how their one existing caller (carousel_mini_grid_with_badge_
			// container.php) already uses them.
			get_template_part( 'components/section-heading/section-header', null, array(
				'tag'   => 'Light Variant',
				'title' => 'Section Header <em>Light</em>',
				'body'  => 'Used on the parchment background - the default look.',
			) );
			?>
		</div>
		<div class="dev-demo dev-demo--dark">
			<?php
			get_template_part( 'components/section-heading/section-header-dark', null, array(
				'tag'   => 'Dark Variant',
				'title' => 'Section Header <em>Dark</em>',
				'body'  => 'Used on a dark photo or wood background instead of parchment.',
			) );
			?>
		</div>
	</section>

	<section class="dev-block" id="stamp">
		<?php nt_dev_heading( '06', 'Stamp', 'components/stamps/stamp.php', 'The reusable SVG ink stamp, in its three tones.' ); ?>
		<div class="dev-demo dev-demo--flex">
			<?php
			App_Helpers::component( 'stamps/stamp', array( 'top' => 'Family Business', 'bottom' => 'Full Support', 'middle' => 'Proven Model', 'tone' => 'ink' ) );
			App_Helpers::component( 'stamps/stamp', array( 'top' => 'Est. 1974', 'bottom' => 'Small Batch', 'middle' => array( '100%', 'Natural' ), 'tone' => 'gold' ) );
			App_Helpers::component( 'stamps/stamp', array( 'top' => 'Freshly Pressed', 'bottom' => 'Every Morning', 'middle' => 'Sold Out Daily', 'tone' => 'red' ) );
			?>
		</div>
	</section>

	<!-- ═══════════════════════════ 2. STRUCTURAL / HERO ═══════════════════════════ -->

	<section class="dev-block" id="hero-movie-header">
		<?php nt_dev_heading( '07', 'Hero - Movie Header', 'components/movie-header/header.php', 'The carved-signboard hero used on every inner page. Two samples from admin/data/page_headers.json.' ); ?>
		<?php
		$nt_mh = App_Helpers::data( 'page_headers' );
		if ( ! empty( $nt_mh['about'] ) ) {
			App_Helpers::component( 'movie-header/header', (array) $nt_mh['about'] );
		}
		if ( ! empty( $nt_mh['franchise'] ) ) {
			// This one also demos the per-page "style" colour/texture override.
			App_Helpers::component( 'movie-header/header', (array) $nt_mh['franchise'] );
		}
		?>
	</section>

	<section class="dev-block" id="hero-page-header">
		<?php nt_dev_heading( '08', 'Hero - Page Header', 'components/banners/page_header.php', 'Flat parchment band, then the cinematic poster mode (with a background image).' ); ?>
		<?php
		App_Helpers::component( 'banners/page_header', array(
			'tag'      => 'Flat Mode',
			'icon'     => '📰',
			'title'    => 'Page Header - Flat',
			'subtitle' => 'No image supplied - renders as a flat parchment band.',
		) );
		App_Helpers::component( 'banners/page_header', array(
			'tag'      => 'Poster Mode',
			'icon'     => '🎪',
			'title'    => 'Page Header - Poster',
			'subtitle' => 'An image switches this to a full-bleed sepia banner.',
			'image'    => 'https://images.unsplash.com/photo-1519671482749-fd09be7ccebf?auto=format&fit=crop&w=1600&q=80',
		) );
		?>
	</section>

	<section class="dev-block" id="media-carousel">
		<?php nt_dev_heading( '09', 'Media Carousel', 'components/media-carousel.php', 'admin/data/home_media.json - image/video fading carousel.' ); ?>
		<?php App_Helpers::component( 'media-carousel' ); ?>
	</section>

	<section class="dev-block" id="home-banner">
		<?php nt_dev_heading( '10', 'Home Banner', 'components/banners/home-banner.php', 'The vintage sepia hero banner used at the very top of the home page.' ); ?>
		<?php App_Helpers::component( 'banners/home-banner' ); ?>
	</section>

	<section class="dev-block" id="photo-carousel">
		<?php nt_dev_heading( '11', 'Photo Carousel', 'components/photo-carousel.php', 'admin/data/photo_carousel.json' ); ?>
		<?php App_Helpers::component( 'photo-carousel' ); ?>
	</section>

	<section class="dev-block" id="ticker">
		<?php nt_dev_heading( '12', 'Ticker', 'components/ticker.php', 'admin/data/ticker.json - scrolling announcement strip.' ); ?>
		<?php App_Helpers::component( 'ticker' ); ?>
	</section>

	<!-- ═══════════════════════════ 3. STORY & CREDIBILITY ═══════════════════════════ -->

	<section class="dev-block" id="our-story">
		<?php nt_dev_heading( '13', 'Our Story', 'components/our-story-home.php', 'admin/data/content.json' ); ?>
		<?php App_Helpers::component( 'our-story-home' ); ?>
	</section>

	<section class="dev-block" id="company-history">
		<?php nt_dev_heading( '14', 'Company History', 'components/company-history.php', 'admin/data/history.json' ); ?>
		<?php App_Helpers::component( 'company-history' ); ?>
	</section>

	<section class="dev-block" id="milestones">
		<?php nt_dev_heading( '15', 'Milestones', 'components/milestones.php', 'admin/data/milestones.json - timeline.' ); ?>
		<?php App_Helpers::component( 'milestones' ); ?>
	</section>

	<section class="dev-block" id="process-steps">
		<?php nt_dev_heading( '16', 'Process Steps', 'components/process-steps.php', 'admin/data/process_steps.json' ); ?>
		<?php App_Helpers::component( 'process-steps' ); ?>
	</section>

	<section class="dev-block" id="video-feature">
		<?php nt_dev_heading( '17', 'Video Feature', 'components/video-feature.php', 'admin/data/video_feature.json' ); ?>
		<?php App_Helpers::component( 'video-feature' ); ?>
	</section>

	<section class="dev-block" id="values">
		<?php nt_dev_heading( '18', 'Values', 'components/values.php', 'admin/data/values.json' ); ?>
		<?php App_Helpers::component( 'values' ); ?>
	</section>

	<section class="dev-block" id="certifications">
		<?php nt_dev_heading( '19', 'Features & Certifications', 'components/features-certifications.php', 'Also demos components/cards/carousel_mini_grid_with_badge_container.php, which it renders internally.' ); ?>
		<?php App_Helpers::component( 'features-certifications' ); ?>
	</section>

	<section class="dev-block" id="features-in">
		<?php nt_dev_heading( '20', 'Features In', 'components/features-in.php', '"Featured in" logo strip. Renders nothing if no logos are seeded yet - that is expected.' ); ?>
		<?php App_Helpers::component( 'features-in' ); ?>
	</section>

	<section class="dev-block" id="team">
		<?php nt_dev_heading( '21', 'Team', 'components/team.php', 'admin/data/team.json' ); ?>
		<?php App_Helpers::component( 'team' ); ?>
	</section>

	<section class="dev-block" id="stats-bar">
		<?php nt_dev_heading( '22', 'Stats Bar', 'components/stats-bar.php', 'admin/data/stats.json' ); ?>
		<?php App_Helpers::component( 'stats-bar' ); ?>
	</section>

	<section class="dev-block" id="reviews">
		<?php nt_dev_heading( '23', 'Reviews / Testimonials', 'components/reviews.php', 'admin/data/reviews.json' ); ?>
		<?php App_Helpers::component( 'reviews' ); ?>
	</section>

	<section class="dev-block" id="logo-strip">
		<?php nt_dev_heading( '24', 'Logo Strip', 'components/logo-strip.php', 'admin/data/logo_strip.json' ); ?>
		<?php App_Helpers::component( 'logo-strip' ); ?>
	</section>

	<!-- ═══════════════════════════ 4. PRODUCT & MENU ═══════════════════════════ -->

	<section class="dev-block" id="our-drinks">
		<?php nt_dev_heading( '25', 'Our Drinks', 'components/our-drinks.php', 'admin/data/content.json' ); ?>
		<?php App_Helpers::component( 'our-drinks' ); ?>
	</section>

	<section class="dev-block" id="signature-flavours">
		<?php nt_dev_heading( '26', 'Signature Flavours', 'components/signature-flavours.php', 'admin/data/signature_flavours.json' ); ?>
		<?php App_Helpers::component( 'signature-flavours' ); ?>
	</section>

	<section class="dev-block" id="product-menu">
		<?php nt_dev_heading( '27', 'Product Menu (Cards)', 'components/product-menu.php', 'admin/data/flavours.json - the closest match to a "product card" grid.' ); ?>
		<?php App_Helpers::component( 'product-menu' ); ?>
	</section>

	<section class="dev-block" id="products-list">
		<?php nt_dev_heading( '28', 'Product Builder', 'components/products-list.php', 'admin/data/flavours.json - base/texture/flavour option rows and chips.' ); ?>
		<?php App_Helpers::component( 'products-list' ); ?>
	</section>

	<section class="dev-block" id="product-benefits">
		<?php nt_dev_heading( '29', 'Product Benefits', 'components/product-benefits.php', 'admin/data/benefits_items.json' ); ?>
		<?php App_Helpers::component( 'product-benefits' ); ?>
	</section>

	<section class="dev-block" id="product-experience">
		<?php nt_dev_heading( '30', 'Product Experience', 'components/product-experience.php', 'admin/data/experience_data.json' ); ?>
		<?php App_Helpers::component( 'product-experience' ); ?>
	</section>

	<section class="dev-block" id="filter-cards">
		<?php nt_dev_heading( '31', 'Filter Cards', 'components/filter-cards.php', 'admin/data/filter_cards.json - filterable card grid (works with JS off too).' ); ?>
		<?php App_Helpers::component( 'filter-cards' ); ?>
	</section>

	<!-- ═══════════════════════════ 5. COMMERCIAL ═══════════════════════════ -->

	<section class="dev-block" id="pricing-tiers">
		<?php nt_dev_heading( '32', 'Pricing Tiers', 'components/pricing-tiers.php', 'admin/data/pricing_tiers.json' ); ?>
		<?php App_Helpers::component( 'pricing-tiers' ); ?>
	</section>

	<section class="dev-block" id="compare-table">
		<?php nt_dev_heading( '33', 'Compare Table', 'components/compare-table.php', 'No default compare_table.json exists yet, so this points at admin/data/compare_franchise.json.' ); ?>
		<?php App_Helpers::component( 'compare-table', array( 'source' => 'compare_franchise' ) ); ?>
	</section>

	<section class="dev-block" id="franchise-section">
		<?php nt_dev_heading( '34', 'Franchise Section', 'components/franchise-section.php', 'admin/data/franchise.json + form_franchise.json. Its own "data-nt-open" button + form-modal.php IS the franchise enquiry flow - components/franchise-enquiry.php was a broken, unused duplicate of this and has been removed.' ); ?>
		<?php App_Helpers::component( 'franchise-section' ); ?>
	</section>

	<section class="dev-block" id="events-preview">
		<?php nt_dev_heading( '36', 'Events Preview', 'components/events-preview.php', 'admin/data/hire_packages.json + form_events.json. Its own "data-nt-open" button + form-modal.php IS the events enquiry flow - components/events-quote.php was an unused duplicate of this and has been removed.' ); ?>
		<?php App_Helpers::component( 'events-preview' ); ?>
	</section>

	<section class="dev-block" id="events-catering">
		<?php nt_dev_heading( '38', 'Events & Catering', 'components/events-catering.php', 'admin/data/content.json' ); ?>
		<?php App_Helpers::component( 'events-catering' ); ?>
	</section>

	<section class="dev-block" id="order-to-deliver">
		<?php nt_dev_heading( '39', 'Order To Deliver', 'components/order-to-deliver.php', 'admin/data/delivery_products.json + form_order.json' ); ?>
		<?php App_Helpers::component( 'order-to-deliver' ); ?>
	</section>

	<section class="dev-block" id="cta-banner">
		<?php nt_dev_heading( '40', 'CTA Banner', 'components/banners/cta-banner.php', 'admin/data/cta_default.json' ); ?>
		<?php App_Helpers::component( 'banners/cta-banner', array( 'source' => 'cta_default' ) ); ?>
	</section>

	<!-- ═══════════════════════════ 6. MEDIA & EDITORIAL ═══════════════════════════ -->

	<section class="dev-block" id="gallery-grid">
		<?php nt_dev_heading( '41', 'Gallery Grid', 'components/gallery-grid.php', 'admin/data/gallery.json - lightbox-enabled photo grid.' ); ?>
		<?php App_Helpers::component( 'gallery-grid' ); ?>
	</section>

	<section class="dev-block" id="post-card">
		<?php nt_dev_heading( '42', 'Post Card', 'components/cards/post_card.php', 'One card per published post. Nothing renders here if the site has no posts yet.' ); ?>
		<div class="dev-demo dev-demo--grid">
			<?php
			$nt_demo_posts = get_posts( array( 'numberposts' => 3, 'post_status' => 'publish' ) );
			foreach ( $nt_demo_posts as $nt_demo_post ) {
				App_Helpers::component( 'cards/post_card', array( 'post_id' => $nt_demo_post->ID ) );
			}
			?>
		</div>
	</section>

	<section class="dev-block" id="posts-preview">
		<?php nt_dev_heading( '43', 'Posts Preview', 'components/posts-preview.php', 'admin/data/posts_preview.json' ); ?>
		<?php App_Helpers::component( 'posts-preview' ); ?>
	</section>

	<section class="dev-block" id="blog-sidebar">
		<?php nt_dev_heading( '44', 'Blog Sidebar', 'components/parts/blog-sidebar.php', 'admin/data/blog.json -> sidebar. Search, categories, recent posts, tags and shared promo blocks.' ); ?>
		<div class="dev-demo dev-demo--grid">
			<?php App_Helpers::component( 'parts/blog-sidebar', array( 'config' => App_Helpers::data( 'blog' )['sidebar'] ?? array() ) ); ?>
		</div>
	</section>

	<section class="dev-block" id="paper-story">
		<?php nt_dev_heading( '45', 'Paper Story', 'components/paper-story.php', 'admin/data/paper_story.json - flips through like turning a page, keyboard and swipe included.' ); ?>
		<?php App_Helpers::component( 'paper-story' ); ?>
	</section>

	<!-- ═══════════════════════════ 7. CONTACT & CONVERSION ═══════════════════════════ -->

	<section class="dev-block" id="contact-section">
		<?php nt_dev_heading( '46', 'Contact Section', 'components/contact-section.php', 'admin/data/content.json + site.json - includes its own generic-form demo.' ); ?>
		<?php App_Helpers::component( 'contact-section' ); ?>
	</section>

	<section class="dev-block" id="locations">
		<?php nt_dev_heading( '47', 'Locations', 'components/locations.php', 'admin/data/locations.json' ); ?>
		<?php App_Helpers::component( 'locations' ); ?>
	</section>

	<section class="dev-block" id="opening-hours">
		<?php nt_dev_heading( '48', 'Opening Hours', 'components/opening-hours.php', 'admin/data/opening_hours.json - the open/closed badge is computed server-side from the WP timezone.' ); ?>
		<?php App_Helpers::component( 'opening-hours' ); ?>
	</section>

	<section class="dev-block" id="map-embed">
		<?php nt_dev_heading( '49', 'Map Embed', 'components/map-embed.php', 'admin/data/map.json' ); ?>
		<?php App_Helpers::component( 'map-embed' ); ?>
	</section>

	<section class="dev-block" id="newsletter">
		<?php nt_dev_heading( '50', 'Newsletter', 'components/newsletter.php', 'admin/data/newsletter.json' ); ?>
		<?php App_Helpers::component( 'newsletter' ); ?>
	</section>

	<section class="dev-block" id="faqs">
		<?php nt_dev_heading( '51', 'FAQs (Accordion)', 'components/faqs.php', 'Native <details>/<summary> accordion - the theme has no separate "accordion" component, this is it.' ); ?>
		<?php App_Helpers::component( 'faqs' ); ?>
	</section>

	<!-- ═══════════════════════════ 8. PORTAL / MISC ═══════════════════════════ -->

	<section class="dev-block" id="quick-links">
		<?php nt_dev_heading( '52', 'Quick Links', 'components/quick-links.php', 'admin/data/quick_links.json (or blocks.json keys)' ); ?>
		<?php App_Helpers::component( 'quick-links' ); ?>
	</section>

	<section class="dev-block" id="promo-block">
		<?php nt_dev_heading( '53', 'Promo Block', 'components/promo-block.php', 'admin/data/blocks.json - the shared "say this, link there" message library.' ); ?>
		<?php
		App_Helpers::component( 'promo-block', array(
			'tag'    => 'From The Library',
			'title'  => 'Promo <em>Block</em>',
			'blocks' => array( 'read_blogs', 'get_brochure', 'join_us' ),
		) );
		?>
	</section>

	<section class="dev-block" id="info-cards">
		<?php nt_dev_heading( '54', 'Info Cards', 'components/info-cards.php', 'admin/data/info_cards.json' ); ?>
		<?php App_Helpers::component( 'info-cards' ); ?>
	</section>

	<section class="dev-block" id="downloads">
		<?php nt_dev_heading( '55', 'Downloads', 'components/downloads.php', 'admin/data/downloads.json' ); ?>
		<?php App_Helpers::component( 'downloads' ); ?>
	</section>

	<section class="dev-block" id="careers">
		<?php nt_dev_heading( '56', 'Careers', 'components/careers.php', 'admin/data/careers.json - each role opens its own application dialog via form_apply.json.' ); ?>
		<?php App_Helpers::component( 'careers' ); ?>
	</section>

	<section class="dev-block" id="spotlights">
		<?php nt_dev_heading( '57', 'Spotlights', 'components/spotlights.php', 'Renders nothing if no spotlights are seeded yet - that is expected.' ); ?>
		<?php App_Helpers::component( 'spotlights' ); ?>
	</section>

	<section class="dev-block" id="newsbar">
		<?php nt_dev_heading( '58', 'Newsbar', 'components/newsbar.php', 'Renders nothing if no newsbar items are seeded yet - that is expected.' ); ?>
		<?php App_Helpers::component( 'newsbar' ); ?>
	</section>

	<section class="dev-block" id="legal-document">
		<?php nt_dev_heading( '59', 'Legal Document', 'components/legal-document.php', 'admin/data/legal_privacy.json - the shared template behind Privacy / Cookies / Terms.' ); ?>
		<?php App_Helpers::component( 'legal-document', array( 'source' => 'legal_privacy' ) ); ?>
	</section>

	<section class="dev-block" id="tabs">
		<?php nt_dev_heading( '60', 'Tabs', 'components/tabs.php', 'admin/data/tabs.json has no content yet, so this points at admin/data/tabs_services.json.' ); ?>
		<?php App_Helpers::component( 'tabs', array( 'source' => 'tabs_services' ) ); ?>
	</section>

	<section class="dev-block" id="split-feature">
		<?php nt_dev_heading( '61', 'Split Feature', 'components/split-feature.php', 'admin/data/split_feature.json' ); ?>
		<?php App_Helpers::component( 'split-feature' ); ?>
	</section>

	<!-- ═══════════════════════════ 9. FORMS & OVERLAYS ═══════════════════════════ -->

	<section class="dev-block" id="generic-form">
		<?php nt_dev_heading( '62', 'Forms & Inputs', 'components/forms/generic-form.php', 'Every field type it supports: text, email, tel, textarea, select, checkbox, radio.' ); ?>
		<div class="dev-demo">
			<?php
			get_template_part( 'components/forms/generic-form', null, array(
				'id'     => 'dev-generic-form',
				'action' => 'dev_showcase_noop',
				'submit' => 'Submit',
				'fields' => array(
					array( 'type' => 'text', 'id' => 'dev-name', 'name' => 'dev_name', 'label' => 'Text input', 'placeholder' => 'Your name', 'required' => true ),
					array( 'type' => 'email', 'id' => 'dev-email', 'name' => 'dev_email', 'label' => 'Email input', 'placeholder' => 'you@email.com' ),
					array( 'type' => 'tel', 'id' => 'dev-tel', 'name' => 'dev_tel', 'label' => 'Telephone input', 'placeholder' => '+44 ...' ),
					array( 'type' => 'select', 'id' => 'dev-select', 'name' => 'dev_select', 'label' => 'Select field', 'options' => array( '' => 'Choose one…', 'a' => 'Option A', 'b' => 'Option B' ) ),
					array( 'type' => 'textarea', 'id' => 'dev-textarea', 'name' => 'dev_textarea', 'label' => 'Textarea', 'placeholder' => 'Longer message…' ),
					array( 'type' => 'checkbox', 'id' => 'dev-checkbox', 'name' => 'dev_checkbox', 'label' => 'Checkbox - I agree to the terms' ),
					array( 'type' => 'radio', 'id' => 'dev-radio-1', 'name' => 'dev_radio', 'label' => 'Radio - Option one' ),
					array( 'type' => 'radio', 'id' => 'dev-radio-2', 'name' => 'dev_radio', 'label' => 'Radio - Option two' ),
				),
			) );
			?>
		</div>
	</section>

	<section class="dev-block" id="multistep-wizard">
		<?php nt_dev_heading( '63', 'Multi-step Wizard', 'components/forms/generic-multistep-form.php + form-modal.php', 'Reuses the real franchise enquiry config (form_franchise.json) inside the reusable modal shell.' ); ?>
		<div class="dev-demo">
			<button type="button" class="btn" data-nt-open="dev-wizard-modal">Open multi-step wizard</button>
		</div>
		<?php
		get_template_part( 'components/dialogs/form-modal', null, array(
			'id'     => 'dev-wizard-modal',
			'title'  => 'Multi-step Wizard Demo',
			'sub'    => 'Same component that powers the franchise, events and order wizards.',
			'config' => App_Helpers::data( 'form_franchise' ),
		) );
		?>
	</section>

	<section class="dev-block" id="generic-dialog">
		<?php nt_dev_heading( '64', 'Generic Dialog', 'components/dialogs/generic-dialog.php + NT_Dialog (dialogs.json)', 'Two separate dialog systems live in this theme - both are demoed here.' ); ?>
		<div class="dev-demo dev-demo--flex">
			<button type="button" class="btn-outline" onclick="document.getElementById('dev-generic-dialog').showModal();">
				Open native &lt;dialog&gt; demo
			</button>
			<button type="button" class="btn-outline-gold" <?php app_dialog_trigger( 'brochure' ); ?>>
				Open data-driven dialog ("brochure")
			</button>
		</div>
		<?php
		get_template_part( 'components/dialogs/generic-dialog', null, array(
			'id'      => 'dev-generic-dialog',
			'title'   => 'Generic Dialog Demo',
			'content' => '<p>This markup is passed in as a plain HTML string via the <code>content</code> arg.</p>',
		) );
		?>
	</section>

</div>
<?php get_footer(); ?>
