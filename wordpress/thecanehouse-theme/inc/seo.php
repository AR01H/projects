<?php
/**
 * SEO Output — The Cane House
 * Reads from config.php and outputs all <meta> and JSON-LD.
 * Included inside header.php automatically.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<title><?php echo esc_html( get_customizer_val('tch_seo_title', TCH_SEO_TITLE) ); ?></title>
<meta name="description" content="<?php echo esc_attr( get_customizer_val('tch_seo_desc', TCH_SEO_DESC) ); ?>" />
<meta name="keywords" content="<?php echo esc_attr( TCH_SEO_KEYWORDS ); ?>" />
<link rel="canonical" href="<?php echo esc_url( TCH_SITE_URL . '/' ); ?>" />

<!-- Open Graph -->
<meta property="og:type" content="website" />
<meta property="og:url" content="<?php echo esc_url( TCH_SITE_URL . '/' ); ?>" />
<meta property="og:title" content="<?php echo esc_attr( get_customizer_val('tch_seo_title', TCH_SEO_TITLE) ); ?>" />
<meta property="og:description" content="<?php echo esc_attr( TCH_SEO_DESC ); ?>" />
<meta property="og:image" content="<?php echo esc_url( TCH_OG_IMAGE ); ?>" />

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:url" content="<?php echo esc_url( TCH_SITE_URL . '/' ); ?>" />
<meta name="twitter:title" content="<?php echo esc_attr( get_customizer_val('tch_seo_title', TCH_SEO_TITLE) ); ?>" />
<meta name="twitter:description" content="<?php echo esc_attr( TCH_SEO_DESC ); ?>" />
<meta name="twitter:image" content="<?php echo esc_url( TCH_TWITTER_IMAGE ); ?>" />

<!-- JSON-LD Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FoodService",
  "name": "<?php echo esc_js( TCH_SITE_NAME ); ?>",
  "image": "<?php echo esc_js( TCH_OG_IMAGE ); ?>",
  "url": "<?php echo esc_js( TCH_SITE_URL ); ?>",
  "telephone": "<?php echo esc_js( TCH_PHONE ); ?>",
  "email": "<?php echo esc_js( TCH_EMAIL ); ?>",
  "description": "<?php echo esc_js( TCH_SEO_DESC ); ?>",
  "areaServed": "United Kingdom",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "<?php echo esc_js( TCH_ADDRESS ); ?>",
    "addressCountry": "UK"
  },
  "sameAs": [
    "<?php echo esc_js( TCH_INSTAGRAM ); ?>",
    "<?php echo esc_js( TCH_FACEBOOK ); ?>",
    "<?php echo esc_js( TCH_TIKTOK ); ?>"
  ]
}
</script>
