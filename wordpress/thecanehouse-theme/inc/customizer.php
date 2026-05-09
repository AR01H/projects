<?php
/**
 * WordPress Customizer — The Cane House
 * Adds live-editable panels for the client.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function thecanehouse_customize_register( $wp_customize ) {

    // ── PANEL: Branding ─────────────────────────
    $wp_customize->add_section('tch_branding', [
        'title'    => '🌿 Branding & Logo',
        'priority' => 20,
    ]);
    $wp_customize->add_setting('tch_site_name', ['default' => TCH_SITE_NAME, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('tch_site_name', ['label' => 'Site Name', 'section' => 'tch_branding', 'type' => 'text']);

    $wp_customize->add_setting('tch_tagline', ['default' => TCH_TAGLINE, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('tch_tagline', ['label' => 'Tagline', 'section' => 'tch_branding', 'type' => 'text']);

    // ── PANEL: Brand Colours ─────────────────────────
    $wp_customize->add_section('tch_colours', [
        'title'    => '🎨 Brand Colours',
        'priority' => 25,
    ]);
    $wp_customize->add_setting('tch_color_primary', ['default' => TCH_COLOR_PRIMARY, 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'tch_color_primary', ['label' => 'Primary (Deep Green)', 'section' => 'tch_colours']));

    $wp_customize->add_setting('tch_color_accent', ['default' => TCH_COLOR_ACCENT, 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'tch_color_accent', ['label' => 'Accent (Lime)', 'section' => 'tch_colours']));

    $wp_customize->add_setting('tch_color_text', ['default' => TCH_COLOR_TEXT, 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'tch_color_text', ['label' => 'Body Text', 'section' => 'tch_colours']));

    // ── PANEL: Hero Section ─────────────────────────
    $wp_customize->add_section('tch_hero', [
        'title'    => '🏠 Hero Section',
        'priority' => 30,
    ]);
    $wp_customize->add_setting('tch_hero_title1', ['default' => TCH_HERO_TITLE_LINE1, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('tch_hero_title1', ['label' => 'Hero Title Line 1', 'section' => 'tch_hero', 'type' => 'text']);

    $wp_customize->add_setting('tch_hero_title2', ['default' => TCH_HERO_TITLE_LINE2, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('tch_hero_title2', ['label' => 'Hero Title Line 2 (Accent)', 'section' => 'tch_hero', 'type' => 'text']);

    $wp_customize->add_setting('tch_hero_desc', ['default' => TCH_HERO_DESC, 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('tch_hero_desc', ['label' => 'Hero Description', 'section' => 'tch_hero', 'type' => 'textarea']);

    $wp_customize->add_setting('tch_hero_cta_primary', ['default' => TCH_HERO_CTA_PRIMARY, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('tch_hero_cta_primary', ['label' => 'Primary Button Text', 'section' => 'tch_hero', 'type' => 'text']);

    $wp_customize->add_setting('tch_hero_cta_secondary', ['default' => TCH_HERO_CTA_SECONDARY, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
    $wp_customize->add_control('tch_hero_cta_secondary', ['label' => 'Secondary Button Text', 'section' => 'tch_hero', 'type' => 'text']);

    // ── PANEL: Contact Info ─────────────────────────
    $wp_customize->add_section('tch_contact_info', [
        'title'    => '📞 Contact Information',
        'priority' => 40,
    ]);
    $wp_customize->add_setting('tch_phone', ['default' => TCH_PHONE, 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('tch_phone', ['label' => 'Phone Number', 'section' => 'tch_contact_info', 'type' => 'text']);

    $wp_customize->add_setting('tch_whatsapp', ['default' => TCH_WHATSAPP, 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('tch_whatsapp', ['label' => 'WhatsApp Number (digits only)', 'section' => 'tch_contact_info', 'type' => 'text']);

    $wp_customize->add_setting('tch_email', ['default' => TCH_EMAIL, 'sanitize_callback' => 'sanitize_email']);
    $wp_customize->add_control('tch_email', ['label' => 'Email Address', 'section' => 'tch_contact_info', 'type' => 'email']);

    $wp_customize->add_setting('tch_address', ['default' => TCH_ADDRESS, 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('tch_address', ['label' => 'Address / Location', 'section' => 'tch_contact_info', 'type' => 'text']);

    // ── PANEL: SEO ─────────────────────────
    $wp_customize->add_section('tch_seo', [
        'title'    => '🔍 SEO Settings',
        'priority' => 50,
    ]);
    $wp_customize->add_setting('tch_seo_title', ['default' => TCH_SEO_TITLE, 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('tch_seo_title', ['label' => 'Page Title (for Google)', 'section' => 'tch_seo', 'type' => 'text']);

    $wp_customize->add_setting('tch_seo_desc', ['default' => TCH_SEO_DESC, 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp_customize->add_control('tch_seo_desc', ['label' => 'Meta Description (for Google, max 160 chars)', 'section' => 'tch_seo', 'type' => 'textarea']);
}
add_action('customize_register', 'thecanehouse_customize_register');

/**
 * Output live Customizer CSS for colour changes.
 */
function thecanehouse_customizer_css() {
    $primary = get_theme_mod('tch_color_primary', TCH_COLOR_PRIMARY);
    $accent  = get_theme_mod('tch_color_accent',  TCH_COLOR_ACCENT);
    $text    = get_theme_mod('tch_color_text',    TCH_COLOR_TEXT);
    ?>
    <style id="tch-customizer-css">
        :root {
            --green-deep: <?php echo esc_attr($primary); ?>;
            --lime:       <?php echo esc_attr($accent); ?>;
            --text:       <?php echo esc_attr($text); ?>;
        }
    </style>
    <?php
}
add_action('wp_head', 'thecanehouse_customizer_css');
