<?php
/**
 * Template Name: Buying Guides
 */
defined( 'ABSPATH' ) || exit;

get_template_part( 'parts/header' );

$guide_sections = [
	[
		'slug'    => 'first-time-buyers',
		'icon'    => '🏡',
		'label'   => __( 'First-Time Buyers', 'ah-theme' ),
		'intro'   => __( 'Everything you need from saving your deposit to picking up the keys.', 'ah-theme' ),
		'guides'  => [
			[ 'title' => __( 'How Much Deposit Do You Need?', 'ah-theme' ),             'read' => '5 min',  'slug' => 'how-much-deposit' ],
			[ 'title' => __( 'Getting a Mortgage in Principle', 'ah-theme' ),           'read' => '7 min',  'slug' => 'mortgage-in-principle' ],
			[ 'title' => __( 'Making Your First Offer', 'ah-theme' ),                   'read' => '6 min',  'slug' => 'making-first-offer' ],
			[ 'title' => __( 'Understanding the Conveyancing Process', 'ah-theme' ),    'read' => '10 min', 'slug' => 'conveyancing-explained' ],
		],
	],
	[
		'slug'    => 'negotiation',
		'icon'    => '💼',
		'label'   => __( 'Negotiation', 'ah-theme' ),
		'intro'   => __( 'Proven tactics to reduce the price and secure better terms.', 'ah-theme' ),
		'guides'  => [
			[ 'title' => __( 'How to Research Comparable Prices', 'ah-theme' ),          'read' => '8 min',  'slug' => 'comparable-prices-research' ],
			[ 'title' => __( 'Reading Vendor Motivation', 'ah-theme' ),                  'read' => '6 min',  'slug' => 'reading-vendor-motivation' ],
			[ 'title' => __( 'Negotiation Scripts That Work', 'ah-theme' ),              'read' => '12 min', 'slug' => 'negotiation-scripts' ],
			[ 'title' => __( 'When to Walk Away from a Deal', 'ah-theme' ),              'read' => '5 min',  'slug' => 'when-to-walk-away' ],
		],
	],
	[
		'slug'    => 'surveys-legal',
		'icon'    => '📋',
		'label'   => __( 'Surveys & Legal', 'ah-theme' ),
		'intro'   => __( 'Navigate surveys, searches, and solicitors with confidence.', 'ah-theme' ),
		'guides'  => [
			[ 'title' => __( 'Survey Types: Which Do You Need?', 'ah-theme' ),            'read' => '7 min',  'slug' => 'survey-types' ],
			[ 'title' => __( 'What Property Searches Actually Check', 'ah-theme' ),       'read' => '6 min',  'slug' => 'property-searches-explained' ],
			[ 'title' => __( 'Choosing the Right Solicitor', 'ah-theme' ),                'read' => '5 min',  'slug' => 'choosing-solicitor' ],
			[ 'title' => __( 'Exchange to Completion: What Happens?', 'ah-theme' ),      'read' => '9 min',  'slug' => 'exchange-to-completion' ],
		],
	],
	[
		'slug'    => 'investment',
		'icon'    => '📈',
		'label'   => __( 'Property Investment', 'ah-theme' ),
		'intro'   => __( 'How to source, analyse, and acquire investment properties that perform.', 'ah-theme' ),
		'guides'  => [
			[ 'title' => __( 'Buy-to-Let Yields Explained', 'ah-theme' ),                 'read' => '8 min',  'slug' => 'buy-to-let-yields' ],
			[ 'title' => __( 'How to Source Off-Market Deals', 'ah-theme' ),              'read' => '10 min', 'slug' => 'off-market-sourcing' ],
			[ 'title' => __( 'HMO vs Single-Let: Which is Right?', 'ah-theme' ),         'read' => '9 min',  'slug' => 'hmo-vs-single-let' ],
			[ 'title' => __( 'Building a Property Portfolio', 'ah-theme' ),               'read' => '12 min', 'slug' => 'building-property-portfolio' ],
		],
	],
];
?>
<main id="main-content">

  <!-- Page Hero -->
  <section class="page-hero">
    <div class="container">
      <div class="eyebrow reveal" style="color:var(--accent)"><?php esc_html_e( 'Free Resources', 'ah-theme' ); ?></div>
      <h1 class="reveal reveal-delay-1"><?php esc_html_e( 'Buying Guides', 'ah-theme' ); ?></h1>
      <p class="reveal reveal-delay-2">
        <?php esc_html_e( 'In-depth guides on every aspect of buying property in the UK — written by buying agents who do this every day.', 'ah-theme' ); ?>
      </p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <?php foreach ( $guide_sections as $i => $section ) :
        $delay = [ '', 'reveal-delay-1' ][ $i % 2 ];
      ?>
        <div class="guides-section reveal <?php echo esc_attr( $delay ); ?>" id="<?php echo esc_attr( $section['slug'] ); ?>">
          <div class="guides-section__header">
            <span class="guides-section__icon"><?php echo esc_html( $section['icon'] ); ?></span>
            <div>
              <h2 class="guides-section__title"><?php echo esc_html( $section['label'] ); ?></h2>
              <p class="guides-section__intro"><?php echo esc_html( $section['intro'] ); ?></p>
            </div>
          </div>
          <div class="guides-list">
            <?php foreach ( $section['guides'] as $guide ) :
              $url = home_url( '/guides/' . $guide['slug'] . '/' );
            ?>
              <a href="<?php echo esc_url( $url ); ?>" class="guide-item">
                <span class="guide-item__title"><?php echo esc_html( $guide['title'] ); ?></span>
                <span class="guide-item__read">⏱ <?php echo esc_html( $guide['read'] ); ?></span>
                <svg class="guide-item__arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php if ( $i < count( $guide_sections ) - 1 ) : ?>
          <hr class="section-divider">
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </section>

  <?php get_template_part( 'components/cta' ); ?>

</main>
<?php get_template_part( 'parts/footer' ); ?>
