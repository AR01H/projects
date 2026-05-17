<?php
defined( 'ABSPATH' ) || exit;

$team = ah_get_team();
if ( empty( $team ) ) return;
?>
<section class="section section--alt" aria-label="Meet the team" style="background-color: var(--client-color-50);">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">Our Team</span>
      <h2 class="section__title">The Experts in Your Corner</h2>
      <p class="section__desc" style="margin-inline:auto">
        Experienced buyer's agents, analysts, and coordinators — all working exclusively for you.
      </p>
    </div>
    <div class="grid-4">
      <?php foreach ( $team as $i => $member ) :
        $initials = '';
        foreach ( explode( ' ', $member->name ) as $w ) {
          $initials .= strtoupper( $w[0] ?? '' );
        }
      ?>
      <div class="team-card" data-aos="fade-up" data-delay="<?php echo $i * 100; ?>">
        <div class="team-card__avatar">
          <?php if ( ! empty( $member->photo_url ) ) : ?>
            <img src="<?php echo esc_url( $member->photo_url ); ?>" alt="<?php echo esc_attr( $member->name ); ?>">
          <?php else : ?>
            <?php echo esc_html( $initials ); ?>
          <?php endif; ?>
        </div>
        <div class="team-card__name"><?php echo esc_html( $member->name ); ?></div>
        <div class="team-card__role"><?php echo esc_html( $member->role ); ?></div>
        <p class="team-card__bio"><?php echo esc_html( $member->bio ); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
