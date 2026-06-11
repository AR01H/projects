<?php
/**
 * Template Name: All In One (Demo)
 *
 * pages/page-allinone.php - living demo of the theme architecture.
 * One page that exercises every layer, so you can verify the wiring
 * and copy working snippets into real pages:
 *
 *   1. Language helper      → lang_translate()
 *   2. Data loaders         → ADN_Real_Loader::csv / json / html
 *   3. Components           → adn_component() + adn_render_form()
 *   4. REST API             → the form posts to /advaithhomes/v1/contact
 *
 * RULE: Page templates fetch and render only - no business logic here.
 */

defined( 'ABSPATH' ) || exit;

// ── 1. Fetch data (loaders only - no queries in the markup) ────────
$faqs       = ADN_Real_Loader::csv( 'faqs' );
$buying     = ADN_Real_Loader::json( 'buying_details' );
$about_html = ADN_Real_Loader::html( 'about_intro' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<main id="primary" class="adn-page adn-page--allinone" style="max-width:960px;margin:0 auto;padding:2rem 1rem;">

	<header class="adn-page__header">
		<h1><?php esc_html_e( 'All In One - Architecture Demo', ADN_TEXT_DOMAIN ); ?></h1>
		<p><?php echo esc_html( lang_translate( 'welcome' ) ); ?></p>
	</header>

	<?php /* ── 2a. HTML fragment from data/html/ ── */ ?>
	<section class="adn-demo-block">
		<h2>HTML loader <code>ADN_Real_Loader::html('about_intro')</code></h2>
		<?php echo ADN_Real_Loader::html_safe( 'about_intro' ); // theme-authored fragment, kses-filtered ?>
	</section>

	<?php /* ── 2b. CSV rows from data/csv/ ── */ ?>
	<section class="adn-demo-block">
		<h2>CSV loader <code>ADN_Real_Loader::csv('faqs')</code> - <?php echo count( $faqs ); ?> rows</h2>
		<?php if ( $faqs ) : ?>
			<dl>
				<?php foreach ( $faqs as $faq ) : ?>
					<dt><strong><?php echo esc_html( $faq['question'] ?? '' ); ?></strong></dt>
					<dd><?php echo esc_html( $faq['answer'] ?? '' ); ?></dd>
				<?php endforeach; ?>
			</dl>
		<?php else : ?>
			<p><?php esc_html_e( 'No FAQs found - add rows to data/csv/faqs.csv.', ADN_TEXT_DOMAIN ); ?></p>
		<?php endif; ?>
	</section>

	<?php /* ── 2c. JSON from data/json/ ── */ ?>
	<section class="adn-demo-block">
		<h2>JSON loader <code>ADN_Real_Loader::json('buying_details')</code></h2>
		<p>
			<?php
			printf(
				/* translators: %d: number of top-level keys in the JSON file */
				esc_html__( 'Loaded buying_details.json with %d top-level keys.', ADN_TEXT_DOMAIN ),
				count( $buying )
			);
			?>
		</p>
	</section>

	<?php /* ── 3+4. Form builder component → REST contact endpoint ── */ ?>
	<section class="adn-demo-block">
		<h2>Form builder <code>adn_render_form()</code> → <code>POST <?php echo esc_html( ADN_API_NS ); ?>/contact</code></h2>
		<?php
		adn_render_form( array(
			'id'              => 'allinone-contact',
			'endpoint'        => rest_url( ADN_API_NS . '/contact' ),
			'submit_label'    => lang_translate( 'contact_us' ),
			'success_message' => 'Thanks! Your message has been received.',
			'fields'          => array(
				array( 'type' => 'text',     'name' => 'name',    'label' => 'Your Name',  'required' => true, 'width' => 'half' ),
				array( 'type' => 'email',    'name' => 'email',   'label' => 'Email',      'required' => true, 'width' => 'half' ),
				array( 'type' => 'tel',      'name' => 'phone',   'label' => 'Phone',      'width' => 'half' ),
				array(
					'type'    => 'select',
					'name'    => 'topic',
					'label'   => 'Topic',
					'width'   => 'half',
					'options' => array(
						'general' => 'General Enquiry',
						'buying'  => 'Buying a Home',
						'support' => 'Support',
					),
				),
				array( 'type' => 'textarea', 'name' => 'message', 'label' => 'Message', 'required' => true, 'rows' => 5 ),
			),
		) );
		?>
	</section>

</main>

<?php wp_footer(); ?>
</body>
</html>
