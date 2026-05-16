<?php
defined( 'ABSPATH' ) || exit;

/* ─────────────────────────────────────────────────────
   If the CMS plugin is active this theme is a companion.
   If not, the theme runs standalone (installer fires once).
   ───────────────────────────────────────────────────── */
if ( ! defined( 'AH_PLUGIN_DIR' ) && function_exists( 'AH_Installer::run' ) ) {
	AH_Installer::run();
}

define( 'AH_ADVAITH_VER', '1.0.0' );

// ── Theme Setup ──────────────────────────────────────
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'customize-selective-refresh-widgets' );

	register_nav_menus( [
		'primary'  => __( 'Primary Navigation', 'ah-theme' ),
		'footer'   => __( 'Footer Links', 'ah-theme' ),
		'guides'   => __( 'Buying Guides', 'ah-theme' ),
	] );

	load_theme_textdomain( 'ah-theme', get_template_directory() . '/languages' );
} );

// ── Enqueue Assets ───────────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
	$uri = get_template_directory_uri();
	$v   = AH_ADVAITH_VER;

	// Google Fonts
	wp_enqueue_style(
		'ah-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600&family=Instrument+Serif:ital@0;1&display=swap',
		[],
		null
	);

	// Design system + component library
	wp_enqueue_style( 'ah-main', "$uri/assets/css/main.css", [ 'ah-fonts' ], $v );

	// Site JS
	wp_enqueue_script( 'ah-main', "$uri/assets/js/main.js", [], $v, true );

	// Pass data to JS
	wp_localize_script( 'ah-main', 'AH_THEME', ah_get_js_data() );
} );

// ── JS Data Payload ──────────────────────────────────
function ah_get_js_data(): array {
	$settings = ah_get_settings();
	$posts    = ah_get_posts_for_nav();
	$news     = ah_get_newsbar_items();

	return [
		'homeUrl'        => home_url( '/' ),
		'themeUrl'       => get_template_directory_uri(),
		'whatsapp'       => $settings['whatsapp'] ?? '+447747223762',
		'phone'          => $settings['phone']     ?? '+447747223762',
		'blogPosts'      => $posts,
		'newsItems'      => $news,
		'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
		'nonce'          => wp_create_nonce( 'ah_public' ),
	];
}

// ── Safe Model Helpers ───────────────────────────────

function ah_model( string $model, string $method = 'all', array $args = [] ): array {
	$class = 'AH_' . $model . '_Model';
	if ( ! class_exists( $class ) || ! method_exists( $class, $method ) ) {
		return [];
	}
	$ref = new ReflectionMethod( $class, $method );
	$result = $ref->isStatic()
		? $class::$method( ...$args )
		: ( new $class() )->$method( ...$args );
	return is_array( $result ) ? $result : [];
}

// Safe object-or-array field access — never throws on type mismatch
function ah_field( $row, string $key, $fallback = '' ) {
	return is_object( $row ) ? ( $row->$key ?? $fallback ) : ( $row[ $key ] ?? $fallback );
}

function ah_get_settings(): array {
	$rows = ah_model( 'Settings' );
	$out  = [];
	foreach ( $rows as $row ) {
		$key = ah_field( $row, 'setting_key' );
		if ( $key !== '' ) {
			$out[ $key ] = ah_field( $row, 'setting_value' );
		}
	}
	if ( empty( $out ) ) {
		$out = [
			'site_name'        => get_bloginfo( 'name' ),
			'phone'            => '+447747223762',
			'email'            => 'contact@advaithhomes.co.uk',
			'whatsapp'         => '+447747223762',
			'address'          => 'London & Nationwide',
			'consultation_url' => home_url( '/free-consultation/' ),
			'facebook_url'     => '',
			'instagram_url'    => '',
			'twitter_url'      => '',
			'linkedin_url'     => '',
		];
	}
	return $out;
}

function ah_get_posts_for_nav( int $limit = 10 ): array {
	$posts = ah_model( 'Posts', 'paginate', [ 1, [ 'per_page' => $limit ] ] );
	$items = $posts['items'] ?? [];
	if ( empty( $items ) ) {
		return ah_static_blog_links();
	}
	return array_map( fn( $p ) => [
		'id'    => (int) ah_field( $p, 'id', 0 ),
		'slug'  => ah_field( $p, 'slug' ),
		'title' => ah_field( $p, 'title' ),
		'url'   => home_url( '/blog/' . ah_field( $p, 'slug' ) . '/' ),
	], $items );
}

function ah_get_newsbar_items(): array {
	$items = ah_model( 'Newsbar' );
	if ( empty( $items ) ) {
		return ah_static_news_items();
	}
	$mapped = array_map( fn( $n ) => [
		'id'        => (int) ah_field( $n, 'id', 0 ),
		'tag'       => ah_field( $n, 'tag', 'NEWS' ),
		'tag_class' => ah_field( $n, 'tag_class', 'tag-gold' ),
		'title'     => ah_field( $n, 'title' ),
		'url'       => ah_field( $n, 'url', '#' ),
	], $items );
	// If every item has an empty title the CMS table exists but has no real content yet
	$has_content = array_filter( $mapped, fn( $n ) => $n['title'] !== '' );
	return $has_content ? $mapped : ah_static_news_items();
}

function ah_get_home_data(): array {
	$rows = ah_model( 'Home' );
	if ( empty( $rows ) ) {
		return [];
	}
	$out = [];
	foreach ( $rows as $r ) {
		$key = ah_field( $r, 'section_key' );
		if ( $key !== '' ) {
			$out[ $key ] = $r;
		}
	}
	return $out;
}

function ah_get_services(): array {
	return ah_model( 'Services' );
}

function ah_get_reviews( int $limit = 6 ): array {
	$all = ah_model( 'Reviews' );
	return array_slice( $all, 0, $limit );
}

function ah_get_faqs( int $limit = 8 ): array {
	$all = ah_model( 'Faqs' );
	return array_slice( $all, 0, $limit );
}

function ah_get_posts( int $limit = 6, int $page = 1 ): array {
	$result = ah_model( 'Posts', 'paginate', [ $page, [ 'per_page' => $limit ] ] );
	return $result['items'] ?? [];
}

function ah_get_team(): array {
	return ah_model( 'Team' );
}

function ah_get_about(): array {
	$rows = ah_model( 'About' );
	$out  = [];
	foreach ( $rows as $r ) {
		$key = ah_field( $r, 'section_key' );
		if ( $key !== '' ) {
			$out[ $key ] = $r;
		}
	}
	return $out;
}

function ah_get_client_stories( int $limit = 6 ): array {
	return array_slice( ah_model( 'ClientStories' ), 0, $limit );
}

function ah_get_contact(): array {
	$rows = ah_model( 'Contact' );
	$out  = [];
	foreach ( $rows as $r ) {
		$key = ah_field( $r, 'section_key' );
		if ( $key !== '' ) {
			$out[ $key ] = $r;
		}
	}
	return $out;
}

function ah_media_url( $id, string $fallback = '' ): string {
	if ( ! $id ) {
		return $fallback;
	}
	if ( class_exists( 'AH_Media_Model' ) && method_exists( 'AH_Media_Model', 'get_url' ) ) {
		return ( new AH_Media_Model() )->get_url( (int) $id ) ?: $fallback;
	}
	$wp_url = wp_get_attachment_url( (int) $id );
	return $wp_url ?: $fallback;
}

// ── Static Fallback Data ─────────────────────────────

function ah_static_blog_links(): array {
	return [
		[ 'id' => 1,  'slug' => 'london-hotspots-2026',      'title' => 'London Hotspots 2026',       'url' => home_url('/blog/london-hotspots-2026/') ],
		[ 'id' => 2,  'slug' => 'negotiation-secrets',       'title' => 'Negotiation Secrets',         'url' => home_url('/blog/negotiation-secrets/') ],
		[ 'id' => 3,  'slug' => 'property-rules-2026',       'title' => 'Property Rules 2026',         'url' => home_url('/blog/property-rules-2026/') ],
		[ 'id' => 4,  'slug' => 'mortgage-secrets-2026',     'title' => 'Mortgage Secrets 2026',       'url' => home_url('/blog/mortgage-secrets-2026/') ],
		[ 'id' => 5,  'slug' => 'midlands-boom-2026',        'title' => 'The Midlands Boom',           'url' => home_url('/blog/midlands-boom-2026/') ],
		[ 'id' => 6,  'slug' => 'first-time-buyer-2026',     'title' => 'First-Time Buyer Guide',      'url' => home_url('/blog/first-time-buyer-2026/') ],
		[ 'id' => 7,  'slug' => 'hidden-costs-buying',       'title' => 'Hidden Costs of Buying',      'url' => home_url('/blog/hidden-costs-buying/') ],
		[ 'id' => 8,  'slug' => 'new-build-vs-period',       'title' => 'New Build vs Period Home',    'url' => home_url('/blog/new-build-vs-period/') ],
		[ 'id' => 9,  'slug' => 'shared-ownership-reality',  'title' => 'Shared Ownership Reality',    'url' => home_url('/blog/shared-ownership-reality/') ],
		[ 'id' => 10, 'slug' => 'digital-legals-2026',       'title' => 'Digital Legals 2026',         'url' => home_url('/blog/digital-legals-2026/') ],
	];
}

function ah_static_news_items(): array {
	$blog = home_url( '/blog/' );
	return [
		[ 'id' => 1, 'tag' => 'BREAKING', 'tag_class' => 'tag-red',  'title' => 'UK Government Announces 1,500 New Affordable Homes Scheme',                 'url' => $blog ],
		[ 'id' => 2, 'tag' => 'MARKET',   'tag_class' => 'tag-gold', 'title' => 'UK House Prices Rise 3.2% Year-on-Year — 6 Regions With Strongest Growth',  'url' => $blog ],
		[ 'id' => 3, 'tag' => 'RATES',    'tag_class' => 'tag-blue', 'title' => 'Bank of England Holds Base Rate at 4.5% — What It Means For Your Mortgage', 'url' => $blog ],
		[ 'id' => 4, 'tag' => 'POLICY',   'tag_class' => 'tag-red',  'title' => 'Stamp Duty Changes: First-Time Buyer Thresholds Updated',                    'url' => $blog ],
		[ 'id' => 5, 'tag' => 'BUYERS',   'tag_class' => 'tag-gold', 'title' => 'Off-Market Property Listings Surge 28% — How To Access Homes Before Rightmove', 'url' => $blog ],
	];
}

function ah_static_why_us_cards(): array {
	return [
		[ 'icon' => '🔍', 'title' => 'Hidden Property Problems',    'text' => 'Most buyers miss structural issues, planning problems, and neighbourhood concerns that only an expert eye catches — before you\'re legally committed.' ],
		[ 'icon' => '💸', 'title' => 'Overpaying Without Knowing',  'text' => 'Without access to real comparable data and skilled negotiation, most buyers pay 5–15% above fair market value. That\'s tens of thousands of pounds.' ],
		[ 'icon' => '⏰', 'title' => 'Months of Wasted Time',       'text' => 'The average first-time buyer spends 6–18 months searching, viewing unsuitable properties, and losing out to other buyers. We cut that dramatically.' ],
		[ 'icon' => '📋', 'title' => 'Legal & Survey Surprises',    'text' => 'Conveyancing, surveys, searches — the legal process is a minefield. We ensure nothing slips through, protecting you from nasty surprises after exchange.' ],
		[ 'icon' => '🤝', 'title' => 'No One In Your Corner',       'text' => 'The seller has their agent, their solicitor, their surveyor. You have… nobody. Until now. Advaith Homes is 100% on your side, every single step.' ],
		[ 'icon' => '🧭', 'title' => 'Confusing, Stressful Process', 'text' => 'Most buyers have never done this before. We give you a clear roadmap, explain every step in plain English, and handle the complexity so you don\'t have to.' ],
	];
}

function ah_static_faqs(): array {
	return [
		[ 'question' => 'What exactly is a buyer\'s agent?',          'answer' => 'A buyer\'s agent (also called a buyer\'s advocate) is a property professional who works exclusively for the buyer — not the seller. Unlike estate agents, who are paid by and legally obligated to the seller, we represent your interests at every stage: searching, viewing, researching, negotiating, and completing your purchase.' ],
		[ 'question' => 'How much does Advaith Homes cost?',          'answer' => 'Our fees vary depending on the service level and property price, but our clients typically save far more than our fee. We offer a free initial consultation with no obligation. Speak to us about your situation and we\'ll give you a clear, transparent quote — no hidden charges.' ],
		[ 'question' => 'Do you cover the whole of the UK?',          'answer' => 'We cover England and Wales. We have specialist knowledge of London and the South East, but operate nationwide and have successfully helped buyers in cities including Manchester, Birmingham, Bristol, Leeds, and many more.' ],
		[ 'question' => 'Can you help with new-build properties?',    'answer' => 'Absolutely. New builds have their own complexities — developer negotiations, reservation fees, snagging, and legal particulars. We have extensive experience dealing with major developers and can often secure better prices, extras, or incentives than buyers going direct.' ],
		[ 'question' => 'What if I already have a property in mind?', 'answer' => 'Perfect — we offer a "negotiation only" service where we step in to negotiate and handle due diligence on a specific property you\'ve already found. Many clients save our entire fee in the very first negotiation call.' ],
		[ 'question' => 'How is the free consultation structured?',   'answer' => 'It\'s a 30-minute video or phone call where we learn about your property goals, budget, and timeline. We give you honest, expert advice on your situation — whether or not you proceed with us. There\'s no sales pitch and no obligation.' ],
	];
}

function ah_static_services(): array {
	return [
		[ 'icon' => '🔍', 'title' => 'Property Search',              'description' => 'We use local market knowledge and exclusive networks to find properties that match your needs — including those not publicly listed.', 'image_id' => 0 ],
		[ 'icon' => '💰', 'title' => 'Expert Negotiation',           'description' => 'We negotiate on your behalf using real comparable data, consistently achieving prices 5–10% below asking price.', 'image_id' => 0 ],
		[ 'icon' => '📊', 'title' => 'Market Analysis & Advice',     'description' => 'Detailed analysis of local markets including schools, crime rates, amenities, and price trends so you buy with confidence.', 'image_id' => 0 ],
		[ 'icon' => '📄', 'title' => 'Liaise & Complete Paperwork',  'description' => 'We coordinate with solicitors, surveyors, and mortgage brokers and handle all documentation efficiently on your behalf.', 'image_id' => 0 ],
		[ 'icon' => '⚖️', 'title' => 'Dispute Resolution',           'description' => 'If disputes arise during the purchase process, we act as your mediator — protecting your interests at every stage.', 'image_id' => 0 ],
		[ 'icon' => '🏡', 'title' => 'Post-Purchase Support',        'description' => 'Help with utilities, renovation contractors, and rental management — ensuring a smooth transition into your new home.', 'image_id' => 0 ],
	];
}

function ah_static_reviews(): array {
	return [
		[ 'name' => 'Sarah & Raj Mehta',    'location' => 'First-Time Buyers in Richmond',   'rating' => 5, 'review' => 'Advaith Homes saved us £27,500 on our Richmond home and six months of stress. The team spotted issues we never would have seen and negotiated brilliantly.', 'avatar_id' => 0 ],
		[ 'name' => 'Emma & Tom Wright',    'location' => 'Dream Home in Surrey',             'rating' => 5, 'review' => 'The negotiation was flawless. We saved £40,000 off the asking price thanks to Advaith Homes\' expert intervention.', 'avatar_id' => 0 ],
		[ 'name' => 'James Wilson',         'location' => 'Property Investor',                'rating' => 5, 'review' => 'As an investor, I need ROI. Advaith Homes sourced three off-market properties for me this year, saving me over £80k in total.', 'avatar_id' => 0 ],
		[ 'name' => 'Michael Chen',         'location' => 'Portfolio Manager',                'rating' => 5, 'review' => 'I recommend Advaith Homes to all my fellow investors. They find gems before they even hit the open market.', 'avatar_id' => 0 ],
		[ 'name' => 'David Lawson',         'location' => 'Relocated from Dubai',             'rating' => 5, 'review' => 'Relocating to Manchester was daunting. Advaith Homes handled everything, from viewings to legal completion.', 'avatar_id' => 0 ],
		[ 'name' => 'Sophie Reed',          'location' => 'Sole Buyer in Bristol',            'rating' => 5, 'review' => 'Buying alone was scary, but they made me feel like I had a whole team of experts supporting me every day.', 'avatar_id' => 0 ],
	];
}

// ── Template Helper: Row Access ──────────────────────

function ah_val( $row, string $key, $fallback = '' ): string {
	return esc_html( (string) ah_field( $row, $key, $fallback ) );
}

function ah_raw( $row, string $key, $fallback = '' ): string {
	return (string) ah_field( $row, $key, $fallback );
}

// ── Image Fallback URLs (Unsplash) ───────────────────
function ah_unsplash( string $id, int $w = 800, int $h = 600 ): string {
	return "https://images.unsplash.com/photo-{$id}?w={$w}&h={$h}&fit=crop&q=80";
}

function ah_hero_img(): string {
	return get_template_directory_uri() . '/assets/images/hero-home.png';
}

// ── Buying Guides Nav Data ───────────────────────────
function ah_buying_guides_nav(): array {
	return [
		[ 'slug' => 'property-research', 'icon' => '🔍', 'title' => 'Property Research Report', 'desc' => 'Deep analysis before you buy' ],
		[ 'slug' => 'legal-search',      'icon' => '⚖️', 'title' => 'Legal Search Packs',        'desc' => "What's hidden in the paperwork" ],
		[ 'slug' => 'buyers-guide',      'icon' => '📋', 'title' => "Buyer's Guide",             'desc' => 'Complete buying process' ],
		[ 'slug' => 'deposit-guide',     'icon' => '💰', 'title' => 'Deposit Guide',             'desc' => 'How much you really need' ],
		[ 'slug' => 'mortgage-guide',    'icon' => '🏦', 'title' => 'Mortgage Guide',            'desc' => 'Navigate rates & lenders' ],
		[ 'slug' => 'moving-guide',      'icon' => '🚛', 'title' => 'Moving Guide',              'desc' => 'Stress-free moving day' ],
		[ 'slug' => 'price-calculator',  'icon' => '🧮', 'title' => 'Price Calculator',          'desc' => 'Dynamic cost estimations', 'highlight' => true ],
	];
}

// ── Pagination Helper ────────────────────────────────
function ah_pagination( int $total_items, int $per_page = 9, int $current = 1 ): void {
	if ( $total_items <= 0 || $per_page <= 0 ) {
		return;
	}
	$total_pages = (int) ceil( $total_items / $per_page );
	if ( $total_pages <= 1 ) {
		return;
	}
	echo '<nav class="ah-pagination" aria-label="' . esc_attr__( 'Page navigation', 'ah-theme' ) . '">';
	for ( $i = 1; $i <= $total_pages; $i++ ) {
		if ( $i === $current ) {
			echo '<span class="current">' . $i . '</span>';
		} else {
			$url = add_query_arg( 'paged', $i );
			printf( '<a href="%s">%d</a>', esc_url( $url ), $i );
		}
	}
	echo '</nav>';
}

// ── Post Categories Helper ───────────────────────────
function ah_get_post_categories(): array {
	$cats = ah_model( 'Categories' );
	if ( ! empty( $cats ) ) {
		return $cats;
	}
	// Fallback: WordPress built-in
	$wp_cats = get_categories( [ 'hide_empty' => true ] );
	if ( ! empty( $wp_cats ) ) {
		return array_map( fn( $c ) => [ 'slug' => $c->slug, 'name' => $c->name ], $wp_cats );
	}
	return [
		[ 'slug' => 'buying-guide', 'name' => __( 'Buying Guide', 'ah-theme' ) ],
		[ 'slug' => 'negotiation',  'name' => __( 'Negotiation', 'ah-theme' ) ],
		[ 'slug' => 'legal',        'name' => __( 'Legal', 'ah-theme' ) ],
		[ 'slug' => 'finance',      'name' => __( 'Finance', 'ah-theme' ) ],
		[ 'slug' => 'investment',   'name' => __( 'Investment', 'ah-theme' ) ],
	];
}

// ── Contact Form AJAX Handler ────────────────────────
add_action( 'wp_ajax_ah_contact_submit',        'ah_handle_contact_form' );
add_action( 'wp_ajax_nopriv_ah_contact_submit', 'ah_handle_contact_form' );

function ah_handle_contact_form(): void {
	if ( ! wp_verify_nonce( $_POST['ah_contact_nonce'] ?? '', 'ah_contact_form' ) ) {
		wp_send_json_error( __( 'Security check failed. Please refresh and try again.', 'ah-theme' ) );
	}

	$name       = sanitize_text_field( $_POST['name'] ?? '' );
	$email      = sanitize_email( $_POST['email'] ?? '' );
	$phone      = sanitize_text_field( $_POST['phone'] ?? '' );
	$budget     = sanitize_text_field( $_POST['budget'] ?? '' );
	$buyer_type = sanitize_text_field( $_POST['buyer_type'] ?? '' );
	$message    = sanitize_textarea_field( $_POST['message'] ?? '' );

	if ( ! $name || ! is_email( $email ) || ! $message ) {
		wp_send_json_error( __( 'Please fill in all required fields.', 'ah-theme' ) );
	}

	$settings  = ah_get_settings();
	$to        = $settings['email'] ?? get_option( 'admin_email' );
	$subject   = sprintf( '[Advaith Homes] New Enquiry from %s', $name );
	$body      = sprintf(
		"Name: %s\nEmail: %s\nPhone: %s\nBudget: %s\nBuyer Type: %s\n\nMessage:\n%s",
		$name, $email, $phone, $budget, $buyer_type, $message
	);
	$headers   = [ 'Content-Type: text/plain; charset=UTF-8', "Reply-To: $name <$email>" ];

	if ( wp_mail( $to, $subject, $body, $headers ) ) {
		wp_send_json_success( __( "Thanks {$name}! We'll be in touch within 24 hours.", 'ah-theme' ) );
	} else {
		wp_send_json_error( __( 'Sorry, there was a problem sending your message. Please try again or call us directly.', 'ah-theme' ) );
	}
}

// ── Blog Post Rewrite Rule ───────────────────────────
// Registers /blog/{slug}/ → custom query var, always on init
add_action( 'init', function () {
	add_rewrite_rule( '^blog/([^/]+)/?$', 'index.php?ah_post_slug=$matches[1]', 'top' );
}, 5 );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'ah_post_slug';
	return $vars;
} );

// Flush rewrite rules once so the new rule takes effect
add_action( 'init', function () {
	if ( get_option( 'ah_rewrites_v1' ) ) {
		return;
	}
	flush_rewrite_rules( true );
	update_option( 'ah_rewrites_v1', 1 );
}, 99 );

// Load single-post template when the query var is present
add_action( 'template_redirect', function () {
	$slug = get_query_var( 'ah_post_slug' );
	if ( ! $slug ) {
		return;
	}
	$tpl = get_template_directory() . '/templates/template-single-post.php';
	if ( file_exists( $tpl ) ) {
		include $tpl;
		exit;
	}
} );

// ── Auto-create Required Pages (runs once per version) ───────────
add_action( 'init', function () {
	if ( get_option( 'ah_pages_created_v2' ) ) {
		return;
	}
	$pages = [
		'services'       => [ 'title' => 'Services',       'template' => 'templates/template-services.php' ],
		'about'          => [ 'title' => 'About Us',        'template' => 'templates/template-about.php' ],
		'contact'        => [ 'title' => 'Contact',         'template' => 'templates/template-contact.php' ],
		'blog'           => [ 'title' => 'Blog',            'template' => 'templates/template-blog.php' ],
		'client-stories' => [ 'title' => 'Client Stories',  'template' => 'templates/template-client-stories.php' ],
		'buying-guides'  => [ 'title' => 'Buying Guides',   'template' => 'templates/template-buying-guides.php' ],
	];
	foreach ( $pages as $slug => $data ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			// Page exists — ensure it has the correct template
			update_post_meta( $existing->ID, '_wp_page_template', $data['template'] );
			continue;
		}
		$id = wp_insert_post( [
			'post_title'   => $data['title'],
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		] );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_wp_page_template', $data['template'] );
		}
	}
	update_option( 'ah_pages_created_v2', 1 );
	flush_rewrite_rules( true );
} );

// ── Page Title Helper ────────────────────────────────
function ah_page_title(): string {
	if ( is_singular() ) {
		return get_the_title();
	}
	if ( is_archive() ) {
		return get_the_archive_title();
	}
	if ( is_search() ) {
		return sprintf( __( 'Results for: %s', 'ah-theme' ), get_search_query() );
	}
	return get_bloginfo( 'name' );
}
