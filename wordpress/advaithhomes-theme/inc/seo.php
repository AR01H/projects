<?php
/**
 * SEO Output — Advaith Homes
 * Reads from config.php and outputs all <meta> and JSON-LD.
 * Included inside header.php automatically.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<title><?php echo esc_html( get_customizer_val('ah_seo_title', AH_SEO_TITLE) ); ?></title>
<meta name="description" content="<?php echo esc_attr( get_customizer_val('ah_seo_desc', AH_SEO_DESC) ); ?>" />
<meta name="keywords" content="<?php echo esc_attr( AH_SEO_KEYWORDS ); ?>" />
<link rel="canonical" href="<?php echo esc_url( AH_SITE_URL . '/' ); ?>" />

<!-- Open Graph -->
<meta property="og:type" content="website" />
<meta property="og:url" content="<?php echo esc_url( AH_SITE_URL . '/' ); ?>" />
<meta property="og:title" content="<?php echo esc_attr( get_customizer_val('ah_seo_title', AH_SEO_TITLE) ); ?>" />
<meta property="og:description" content="<?php echo esc_attr( AH_SEO_DESC ); ?>" />
<meta property="og:image" content="<?php echo esc_url( AH_OG_IMAGE ); ?>" />

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:url" content="<?php echo esc_url( AH_SITE_URL . '/' ); ?>" />
<meta name="twitter:title" content="<?php echo esc_attr( get_customizer_val('ah_seo_title', AH_SEO_TITLE) ); ?>" />
<meta name="twitter:description" content="<?php echo esc_attr( AH_SEO_DESC ); ?>" />
<meta name="twitter:image" content="<?php echo esc_url( AH_TWITTER_IMAGE ); ?>" />

<!-- JSON-LD Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "RealEstateAgent",
  "name": "<?php echo esc_js( AH_SITE_NAME ); ?>",
  "image": "<?php echo esc_js( AH_OG_IMAGE ); ?>",
  "url": "<?php echo esc_js( AH_SITE_URL ); ?>",
  "telephone": "<?php echo esc_js( AH_PHONE ); ?>",
  "email": "<?php echo esc_js( AH_EMAIL ); ?>",
  "priceRange": "<?php echo esc_js( AH_PRICE_RANGE ); ?>",
  "description": "<?php echo esc_js( AH_SEO_DESC ); ?>",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "<?php echo esc_js( AH_CITY ); ?>",
    "addressCountry": "UK"
  },
  "sameAs": [
    "<?php echo esc_js( AH_INSTAGRAM ); ?>",
    "<?php echo esc_js( AH_FACEBOOK ); ?>",
    "<?php echo esc_js( AH_LINKEDIN ); ?>"
  ]
}
</script>
