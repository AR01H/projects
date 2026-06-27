<?php
/**
 * Template Name: Get Expert Guidance
 *
 * pages/page-guidance.php - "Get Expert Guidance" expert-matching request form.
 *
 * Architecture:
 *   data/json/guidance.json
 *     → apis/services.php  adn_service_guidance_data()
 *       → intermediate/page_guidance_logical.php  adn_guidance_get_context()
 *         → THIS FILE (structure only)
 *
 * RULE: No hardcoded content or data reads here - only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_guidance_logical.php';
$ctx = adn_guidance_get_context();

adn_seo_register( array(
	'title'       => isset( $ctx['hero']['title'] )       ? (string) $ctx['hero']['title']       : '',
	'description' => isset( $ctx['hero']['description'] ) ? wp_strip_all_tags( (string) $ctx['hero']['description'] ) : '',
	'canonical'   => defined( 'SITE_GUIDANCE_URL' ) ? home_url( SITE_GUIDANCE_URL ) : '',
	'breadcrumb'  => isset( $ctx['breadcrumb'] )          ? $ctx['breadcrumb']                   : array(),
) );

$_open_ctx               = $ctx;
$_open_ctx['breadcrumb'] = array();
adn_page_open( $_open_ctx );
?>

<?php /* ============================== HERO ============================== */ ?>
<?php if ( ! empty( $ctx['hero'] ) ) : ?>
	<?php adn_component( 'sections/page_hero', array(
		'hero'       => $ctx['hero'],
		'breadcrumb' => $ctx['breadcrumb'],
	) ); ?>
<?php endif; ?>

<?php /* ============================== MAIN: FORM LEFT + SERVICES RIGHT ============================== */ ?>
<div class="guidance-main-layout">

	<?php /* Request form */ ?>
	<?php adn_component( 'sections/guidance_form', array( 'form' => $ctx['form'] ) ); ?>

	<?php /* "We can help you with" service categories */ ?>
	<?php if ( ! empty( $ctx['services'] ) ) : ?>
		<?php adn_component( 'sections/guidance_services', array( 'services' => $ctx['services'] ) ); ?>
	<?php endif; ?>

</div>

<?php /* ============================== WHY CHOOSE ============================== */ ?>
<?php if ( ! empty( $ctx['why_choose'] ) ) : ?>
	<?php adn_component( 'sections/guidance_why_choose', array( 'why_choose' => $ctx['why_choose'] ) ); ?>
<?php endif; ?>

<?php
$_has_news    = ! empty( $ctx['latest_news']['items'] );
$_has_updates = ! empty( $ctx['latest_updates']['items'] );
?>
<?php if ( $_has_news || $_has_updates ) : ?>
<section class="page-latest-news">
	<div class="container">
		<div class="guidance-news-row">
			<?php if ( $_has_news ) : ?>
			<div class="guidance-news-col">
				<?php adn_component( 'parts/news_widget', array( 'widget' => $ctx['latest_news'] ) ); ?>
			</div>
			<?php endif; ?>
			<?php if ( $_has_updates ) : ?>
			<div class="guidance-news-col">
				<?php adn_component( 'parts/news_widget', array( 'widget' => $ctx['latest_updates'] ) ); ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php adn_page_close( $ctx ); ?>
