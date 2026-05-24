<?php
/**
 * Plugin Name: CMS Project Plugin
 * Description: Provides SEO meta tags and a Sitemap Generator admin page.
 * Version: 1.0.0
 * Author: Automated Agent
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access.
}

/** ------------------------------------------------------------
 *  SEO Helper Functions (moved from theme)
 * ------------------------------------------------------------ */

// Load SEO constants from the theme if they exist.
if ( file_exists( get_template_directory() . '/includes/seoC_common.php' ) ) {
    require_once get_template_directory() . '/includes/seoC_common.php';
}

/**
 * Output SEO meta tags. Hooked into wp_head.
 */
function seo_output_tags(): void {
    // Fallbacks for constants.
    $site_title       = defined( 'TXT_SITE_TITLE' ) ? TXT_SITE_TITLE : get_bloginfo( 'name' );
    $site_description = defined( 'TXT_SITE_DESCRIPTION' ) ? TXT_SITE_DESCRIPTION : get_bloginfo( 'description' );
    $og_default_image = defined( 'TXT_OG_DEFAULT_IMAGE' ) ? TXT_OG_DEFAULT_IMAGE : '';
    $logo_url         = defined( 'TXT_LOGO_URL' ) ? TXT_LOGO_URL : '';

    // Title tag.
    if ( is_front_page() ) {
        $title = esc_html( $site_title );
    } elseif ( is_singular() ) {
        $title = esc_html( get_the_title() . ' | ' . $site_title );
    } else {
        $title = esc_html( wp_get_document_title() );
    }
    echo "<title>{$title}</title>\n";

    // Meta description.
    if ( is_singular() && has_excerpt() ) {
        $desc = esc_html( get_the_excerpt() );
    } else {
        $desc = esc_html( $site_description );
    }
    echo "<meta name=\"description\" content=\"{$desc}\">\n";

    // Canonical URL.
    $canonical = esc_url( get_permalink() );
    echo "<link rel=\"canonical\" href=\"{$canonical}\">\n";

    // Open Graph tags.
    $og_image = $og_default_image;
    if ( is_singular() && has_post_thumbnail() ) {
        $og_image = esc_url( get_the_post_thumbnail_url( null, 'full' ) );
    }
    echo "<meta property=\"og:title\" content=\"{$title}\">\n";
    echo "<meta property=\"og:description\" content=\"{$desc}\">\n";
    echo "<meta property=\"og:url\" content=\"{$canonical}\">\n";
    echo "<meta property=\"og:image\" content=\"{$og_image}\">\n";
    echo "<meta property=\"og:site_name\" content=\"" . esc_attr( $site_title ) . "\">\n";

    // Twitter Card tags.
    echo "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
    echo "<meta name=\"twitter:title\" content=\"{$title}\">\n";
    echo "<meta name=\"twitter:description\" content=\"{$desc}\">\n";
    echo "<meta name=\"twitter:image\" content=\"{$og_image}\">\n";

    // JSON‑LD schemas (Organization + WebSite).
    $org_schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'url'      => $canonical,
        'logo'     => esc_url( $logo_url ),
        'name'     => esc_html( $site_title ),
    ];
    $website_schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'url'      => esc_url( home_url( '/' ) ),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target'=> esc_url( home_url( '/?s={search_term_string}' ) ) . '&search={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
    echo '<script type="application/ld+json">' . json_encode( $org_schema ) . '</script>\n';
    echo '<script type="application/ld+json">' . json_encode( $website_schema ) . '</script>\n';
}
add_action( 'wp_head', 'seo_output_tags' );

/** ------------------------------------------------------------
 *  Sitemap Generator Logic
 * ------------------------------------------------------------ */

/**
 * Generate sitemap XML content.
 *
 * @return string XML string.
 */
function cms_generate_sitemap_xml(): string {
    $posts = get_posts( [
        'post_type'      => 'any',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ] );

    $xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><urlset/>', LIBXML_NOERROR | LIBXML_ERR_NONE, false );
    $xml->addAttribute( 'xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );

    foreach ( $posts as $post_id ) {
        $url      = get_permalink( $post_id );
        $modified = get_the_modified_date( 'c', $post_id );
        $url_el   = $xml->addChild( 'url' );
        $url_el->addChild( 'loc', esc_url( $url ) );
        $url_el->addChild( 'lastmod', esc_html( $modified ) );
        $url_el->addChild( 'changefreq', 'weekly' );
        $url_el->addChild( 'priority', '0.6' );
    }
    return $xml->asXML();
}

/**
 * Write the sitemap file to the site root.
 */
function cms_write_sitemap_file(): bool {
    $xml_content = cms_generate_sitemap_xml();
    $path        = ABSPATH . 'sitemap.xml';
    $written     = file_put_contents( $path, $xml_content );
    return $written !== false;
}

/** ------------------------------------------------------------
 *  Admin UI – Sitemap Generator Page
 * ------------------------------------------------------------ */

function cms_sitemap_admin_menu() {
    add_menu_page(
        'Sitemap Generator',
        'Sitemap Generator',
        'read',
        'cms-sitemap-generator',
        'cms_sitemap_admin_page',
        'dashicons-admin-site',
        80
    );
}
add_action( 'admin_menu', 'cms_sitemap_admin_menu' );

function cms_sitemap_admin_page() {
    // Handle generation request.
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'generate' && check_admin_referer( 'cms_generate_sitemap' ) ) {
        $success = cms_write_sitemap_file();
        if ( $success ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Sitemap generated successfully.</p></div>';
            } );
        } else {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to write sitemap.xml. Check file permissions.</p></div>';
            } );
        }
    }

    // Render page.
    echo '<div class="wrap">';
    echo '<h1>Sitemap Generator</h1>';
    echo '<p>Click the button below to generate <code>sitemap.xml</code> for your site.</p>';
    echo '<form method="get">';
    echo '<input type="hidden" name="page" value="cms-sitemap-generator" />';
    echo '<input type="hidden" name="action" value="generate" />';
    wp_nonce_field( 'cms_generate_sitemap' );
    submit_button( 'Generate Sitemap' );
    echo '</form>';
    echo '</div>';
}

/** ------------------------------------------------------------
 *  Auto‑regenerate sitemap on post save (optional)
 * ------------------------------------------------------------ */
function cms_generate_sitemap_on_save( $post_id, $post, $update ) {
    if ( $post->post_status !== 'publish' ) {
        return;
    }
    cms_write_sitemap_file();
}
add_action( 'save_post', 'cms_generate_sitemap_on_save', 10, 3 );
?>
