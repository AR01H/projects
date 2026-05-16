<?php
/**
 * Template Name: About Us
 */
defined( 'ABSPATH' ) || exit;

get_template_part( 'parts/header' );

$about = ah_get_about();
$team  = ah_get_team();

$mission = $about['mission'] ?? __( 'We exist for one reason: to make buying a home a genuinely empowering experience. In a market where estate agents work for sellers, we work exclusively for buyers — giving you the access, expertise, and negotiating power that was previously only available to the wealthy few.', 'ah-theme' );
$values  = $about['values'] ?? [
	[ 'icon' => '🎯', 'title' => __( 'Total Alignment', 'ah-theme' ),    'text' => __( "Our fee comes from you — which means our interests are perfectly aligned with yours. We have zero incentive to push you towards any particular property.", 'ah-theme' ) ],
	[ 'icon' => '🔍', 'title' => __( 'Radical Honesty', 'ah-theme' ),    'text' => __( 'If a property has problems, we tell you — even if that means walking away from a deal. Our reputation depends on giving you advice we genuinely believe in.', 'ah-theme' ) ],
	[ 'icon' => '📊', 'title' => __( 'Data-Driven', 'ah-theme' ),         'text' => __( 'Every negotiation is backed by real comparable data. We never guess — we research, analyse, and present facts that give you leverage.', 'ah-theme' ) ],
	[ 'icon' => '🤝', 'title' => __( 'Long-Term Thinking', 'ah-theme' ), 'text' => __( 'Most of our clients come from referrals. That only happens when you do right by people — so we always optimise for your outcome, not our commission.', 'ah-theme' ) ],
];

if ( empty( $team ) ) {
	$team = [
		[ 'name' => 'Advaith Sharma',    'role' => __( 'Founder & Lead Buyer Agent', 'ah-theme' ),  'bio' => __( '15 years sourcing and negotiating property across London and the South East. Former chartered surveyor.', 'ah-theme' ),                    'image_id' => 0, 'initials' => 'AS' ],
		[ 'name' => 'Priya Nair',        'role' => __( 'Senior Property Researcher', 'ah-theme' ),   'bio' => __( 'Specialist in off-market acquisition and investment analysis. MBA-qualified with a background in residential development.', 'ah-theme' ),  'image_id' => 0, 'initials' => 'PN' ],
		[ 'name' => 'James Okafor',      'role' => __( 'Negotiation & Legal Liaison', 'ah-theme' ), 'bio' => __( 'Former estate agent turned buyer advocate. Expert at reading vendor motivation and securing below-asking prices.', 'ah-theme' ),           'image_id' => 0, 'initials' => 'JO' ],
	];
}
?>
<main id="main-content">

  <!-- Page Hero -->
  <section class="page-hero">
    <div class="container">
      <div class="eyebrow reveal"><?php esc_html_e( 'Our Story', 'ah-theme' ); ?></div>
      <h1 class="reveal reveal-delay-1"><?php esc_html_e( 'About Advaith Homes', 'ah-theme' ); ?></h1>
      <p class="reveal reveal-delay-2">
        <?php esc_html_e( 'Buying agents who genuinely work for you — not the seller, not the market, just you.', 'ah-theme' ); ?>
      </p>
    </div>
  </section>

  <!-- Mission -->
  <section class="section">
    <div class="container">
      <div class="about-mission reveal">
        <div class="about-mission__text">
          <div class="eyebrow"><?php esc_html_e( 'Our Mission', 'ah-theme' ); ?></div>
          <h2><?php esc_html_e( 'Why We Started Advaith Homes', 'ah-theme' ); ?></h2>
          <p><?php echo esc_html( $mission ); ?></p>
          <div class="about-stats">
            <div class="about-stat">
              <div class="about-stat__num">100<span>+</span></div>
              <div class="about-stat__label"><?php esc_html_e( 'Families Helped', 'ah-theme' ); ?></div>
            </div>
            <div class="about-stat">
              <div class="about-stat__num">£22k</div>
              <div class="about-stat__label"><?php esc_html_e( 'Average Saving', 'ah-theme' ); ?></div>
            </div>
            <div class="about-stat">
              <div class="about-stat__num">5★</div>
              <div class="about-stat__label"><?php esc_html_e( 'Client Rating', 'ah-theme' ); ?></div>
            </div>
          </div>
        </div>
        <div class="about-mission__img reveal reveal-delay-2">
          <img src="<?php echo esc_url( ah_unsplash( '1560520653-9e0e4c89eb11', 600, 500 ) ); ?>"
               alt="<?php esc_attr_e( 'Advaith Homes team', 'ah-theme' ); ?>"
               loading="lazy">
        </div>
      </div>
    </div>
  </section>

  <!-- Values -->
  <section class="section" style="background:var(--bg-alt)">
    <div class="container">
      <div style="text-align:center;margin-bottom:48px">
        <div class="eyebrow reveal"><?php esc_html_e( 'What We Stand For', 'ah-theme' ); ?></div>
        <h2 class="reveal reveal-delay-1"><?php esc_html_e( 'Our Core Values', 'ah-theme' ); ?></h2>
      </div>
      <div class="why-grid">
        <?php foreach ( (array) $values as $i => $v ) :
          $delay = [ '', 'reveal-delay-1', 'reveal-delay-2', 'reveal-delay-1' ][ $i % 4 ];
          $icon  = is_array( $v ) ? ( $v['icon'] ?? '✦' ) : ( $v->icon ?? '✦' );
          $title = is_array( $v ) ? ( $v['title'] ?? '' ) : ( $v->title ?? '' );
          $text  = is_array( $v ) ? ( $v['text'] ?? '' ) : ( $v->text ?? '' );
        ?>
          <div class="why-card reveal <?php echo esc_attr( $delay ); ?>">
            <div class="why-card__icon"><?php echo esc_html( $icon ); ?></div>
            <h4><?php echo esc_html( $title ); ?></h4>
            <p><?php echo esc_html( $text ); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Team -->
  <?php if ( ! empty( $team ) ) : ?>
  <section class="section">
    <div class="container">
      <div style="text-align:center;margin-bottom:48px">
        <div class="eyebrow reveal"><?php esc_html_e( 'The Team', 'ah-theme' ); ?></div>
        <h2 class="reveal reveal-delay-1"><?php esc_html_e( 'Meet the People Behind Your Purchase', 'ah-theme' ); ?></h2>
      </div>
      <div class="team-grid">
        <?php foreach ( $team as $i => $member ) :
          $name     = ah_val( $member, 'name' );
          $role     = ah_val( $member, 'role' );
          $bio      = ah_val( $member, 'bio' );
          $img_id   = ah_val( $member, 'image_id', 0 );
          $img      = $img_id ? ah_media_url( $img_id ) : '';
          $initials = ah_val( $member, 'initials', strtoupper( substr( $name, 0, 2 ) ) );
          $delay    = [ '', 'reveal-delay-1', 'reveal-delay-2' ][ $i % 3 ];
        ?>
          <div class="team-card reveal <?php echo esc_attr( $delay ); ?>">
            <div class="team-card__avatar">
              <?php if ( $img ) : ?>
                <img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
              <?php else : ?>
                <span class="team-card__initials"><?php echo esc_html( $initials ); ?></span>
              <?php endif; ?>
            </div>
            <h4 class="team-card__name"><?php echo esc_html( $name ); ?></h4>
            <div class="team-card__role"><?php echo esc_html( $role ); ?></div>
            <p class="team-card__bio"><?php echo esc_html( $bio ); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php get_template_part( 'components/reviews' ); ?>
  <?php get_template_part( 'components/cta' ); ?>

</main>
<?php get_template_part( 'parts/footer' ); ?>
