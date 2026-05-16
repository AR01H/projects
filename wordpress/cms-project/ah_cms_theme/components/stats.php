<?php defined( 'ABSPATH' ) || exit;

$home_data = ah_get_home_data();
$buyers    = ah_raw( $home_data['stat_buyers']  ?? [], 'value', '500' );
$saving    = ah_raw( $home_data['stat_saving']  ?? [], 'value', '18' );
$time_saved= ah_raw( $home_data['stat_months']  ?? [], 'value', '6' );
?>
<section class="stats" aria-label="<?php esc_attr_e( 'Key statistics', 'ah-theme' ); ?>">
  <div class="container">
    <div class="stats__grid">
      <div class="stat-item reveal">
        <div class="stat-item__number">
          <span class="count-up" data-target="<?php echo esc_attr( $buyers ); ?>">0</span><span>+</span>
        </div>
        <div class="stat-item__label"><?php esc_html_e( 'Happy Customers across the UK', 'ah-theme' ); ?></div>
      </div>
      <div class="stat-item reveal reveal-delay-1">
        <div class="stat-item__number">
          £<span class="count-up" data-target="<?php echo esc_attr( $saving ); ?>">0</span><span>k+</span>
        </div>
        <div class="stat-item__label"><?php esc_html_e( 'Average saving per buyer', 'ah-theme' ); ?></div>
      </div>
      <div class="stat-item reveal reveal-delay-2">
        <div class="stat-item__number">
          <span class="count-up" data-target="<?php echo esc_attr( $time_saved ); ?>">0</span><span> mo</span>
        </div>
        <div class="stat-item__label"><?php esc_html_e( 'Average time saved searching', 'ah-theme' ); ?></div>
      </div>
    </div>
  </div>
</section>
