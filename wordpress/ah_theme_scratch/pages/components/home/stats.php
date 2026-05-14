<?php
$s1_n = get_option('ah_stat1_num', '500');
$s1_l = get_option('ah_stat1_label', 'Happy Customers across the UK');
$s2_n = get_option('ah_stat2_num', '18');
$s2_l = get_option('ah_stat2_label', 'Average saving per buyer');
$s3_n = get_option('ah_stat3_num', '6');
$s3_l = get_option('ah_stat3_label', 'Average time saved searching');
?>
<section class="stats">
    <div class="container">
      <div class="stats__grid">
        <div class="stat-item reveal">
          <div class="stat-item__number"><span class="count-up" data-target="<?php echo esc_attr($s1_n); ?>">0</span><span>+</span></div>
          <div class="stat-item__label"><?php echo esc_html($s1_l); ?></div>
        </div>
        <div class="stat-item reveal reveal-delay-1">
          <div class="stat-item__number">£<span class="count-up" data-target="<?php echo esc_attr($s2_n); ?>">0</span><span>k+</span></div>
          <div class="stat-item__label"><?php echo esc_html($s2_l); ?></div>
        </div>
        <div class="stat-item reveal reveal-delay-2">
          <div class="stat-item__number"><span class="count-up" data-target="<?php echo esc_attr($s3_n); ?>">0</span><span> mo</span></div>
          <div class="stat-item__label"><?php echo esc_html($s3_l); ?></div>
        </div>
      </div>
    </div>
</section>
