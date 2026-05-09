<?php
/**
 * WordPress Customizer — Advaith Homes
 * Adds live-editable panels for the client.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function advaithhomes_customize_register( $wp_customize ) {

    // ── PANEL: Branding ─────────────────────────
    $wp_customize->add_section('ah_branding', ['title' => '🏠 Branding & Logo', 'priority' => 20]);
    $wp_customize->add_setting('ah_site_name', ['default' => AH_SITE_NAME, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('ah_site_name', ['label' => 'Site Name', 'section' => 'ah_branding', 'type' => 'text']);

    $wp_customize->add_setting('ah_tagline', ['default' => AH_TAGLINE, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('ah_tagline', ['label' => 'Tagline', 'section' => 'ah_branding', 'type' => 'text']);

    // ── PANEL: Brand Colours ─────────────────────────
    $wp_customize->add_section('ah_colours', ['title' => '🎨 Brand Colours', 'priority' => 25]);
    $wp_customize->add_setting('ah_color_primary', ['default' => AH_COLOR_PRIMARY, 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'ah_color_primary', ['label' => 'Primary (Purple)', 'section' => 'ah_colours']));

    $wp_customize->add_setting('ah_color_gold', ['default' => AH_COLOR_GOLD, 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'ah_color_gold', ['label' => 'Gold Accent', 'section' => 'ah_colours']));

    $wp_customize->add_setting('ah_color_dark', ['default' => AH_COLOR_DARK, 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'ah_color_dark', ['label' => 'Dark Background', 'section' => 'ah_colours']));

    // ── PANEL: Hero Section ─────────────────────────
    $wp_customize->add_section('ah_hero', ['title' => '🏠 Hero Section', 'priority' => 30]);
    $wp_customize->add_setting('ah_hero_title1', ['default' => AH_HERO_TITLE_LINE1, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('ah_hero_title1', ['label' => 'Hero Title Line 1', 'section' => 'ah_hero', 'type' => 'text']);

    $wp_customize->add_setting('ah_hero_title2', ['default' => AH_HERO_TITLE_LINE2, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('ah_hero_title2', ['label' => 'Hero Title Line 2', 'section' => 'ah_hero', 'type' => 'text']);

    $wp_customize->add_setting('ah_hero_desc', ['default' => AH_HERO_DESC, 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('ah_hero_desc', ['label' => 'Hero Description', 'section' => 'ah_hero', 'type' => 'textarea']);

    $wp_customize->add_setting('ah_hero_cta_primary', ['default' => AH_HERO_CTA_PRIMARY, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('ah_hero_cta_primary', ['label' => 'Primary CTA Button Text', 'section' => 'ah_hero', 'type' => 'text']);

    $wp_customize->add_setting('ah_hero_cta_secondary', ['default' => AH_HERO_CTA_SECONDARY, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('ah_hero_cta_secondary', ['label' => 'Secondary CTA Button Text', 'section' => 'ah_hero', 'type' => 'text']);

    // ── PANEL: Contact Info ─────────────────────────
    $wp_customize->add_section('ah_contact_info', ['title' => '📞 Contact Information', 'priority' => 40]);
    $wp_customize->add_setting('ah_phone', ['default' => AH_PHONE, 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('ah_phone', ['label' => 'Phone Number', 'section' => 'ah_contact_info', 'type' => 'text']);

    $wp_customize->add_setting('ah_whatsapp', ['default' => AH_WHATSAPP, 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('ah_whatsapp', ['label' => 'WhatsApp Number (digits only)', 'section' => 'ah_contact_info', 'type' => 'text']);

    $wp_customize->add_setting('ah_email', ['default' => AH_EMAIL, 'sanitize_callback' => 'sanitize_email']);
    $wp_customize->add_control('ah_email', ['label' => 'Email Address', 'section' => 'ah_contact_info', 'type' => 'email']);

    $wp_customize->add_setting('ah_address', ['default' => AH_ADDRESS, 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('ah_address', ['label' => 'Office Address', 'section' => 'ah_contact_info', 'type' => 'text']);

    // ── PANEL: SEO ─────────────────────────
    $wp_customize->add_section('ah_seo', ['title' => '🔍 SEO Settings', 'priority' => 50]);
    $wp_customize->add_setting('ah_seo_title', ['default' => AH_SEO_TITLE, 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('ah_seo_title', ['label' => 'Page Title (for Google)', 'section' => 'ah_seo', 'type' => 'text']);

    $wp_customize->add_setting('ah_seo_desc', ['default' => AH_SEO_DESC, 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp_customize->add_control('ah_seo_desc', ['label' => 'Meta Description (max 160 chars)', 'section' => 'ah_seo', 'type' => 'textarea']);
}
add_action('customize_register', 'advaithhomes_customize_register');

/**
 * Output live Customizer CSS for colour changes.
 */
function advaithhomes_customizer_css() {
    $primary = get_theme_mod('ah_color_primary', AH_COLOR_PRIMARY);
    $gold    = get_theme_mod('ah_color_gold',    AH_COLOR_GOLD);
    $dark    = get_theme_mod('ah_color_dark',    AH_COLOR_DARK);
    ?>
    <style id="ah-customizer-css">
        :root {
            --accent:      <?php echo esc_attr($primary); ?>;
            --gold-400:    <?php echo esc_attr($gold); ?>;
            --slate-900:   <?php echo esc_attr($dark); ?>;
        }
    </style>
    <?php
}
add_action('wp_head', 'advaithhomes_customizer_css');
