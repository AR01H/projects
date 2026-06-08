<?php
defined( 'ABSPATH' ) || exit;

$grid_cards = $args['grid_cards'] ?? [];
$fb_blog    = $args['fb']['blog'] ?? '';

if ( ! $grid_cards ) return;
?>
<section class="nhp-articles">
  <div class="container">
    <div class="nhp-section-head" data-aos="fade-up">
      <div>
        <span class="nhp-eyebrow">Latest</span>
        <h2 class="nhp-section-title">Recent Guides & Articles</h2>
      </div>
      <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="nhp-see-all">See all →</a>
    </div>
    <div class="nhp-articles__grid">
      <?php foreach ( $grid_cards as $i => $m ) : ?>
      <a href="<?php echo esc_url( $m['url'] ); ?>"
         class="nhp-article-card"
         data-cat="<?php echo esc_attr( $m['cat'] ? $m['cat']->slug : '' ); ?>"
         data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 70; ?>">
        <div class="nhp-article-card__img-wrap">
          <img src="<?php echo esc_url( $m['thumb'] ?: $fb_blog ); ?>"
               alt="<?php echo esc_attr( $m['title'] ); ?>" loading="lazy" decoding="async">
          <?php if ( $m['cat'] ) : ?><span class="nhp-pill nhp-pill--sm nhp-article-card__badge"><?php echo esc_html( $m['cat']->name ); ?></span><?php endif; ?>
        </div>
        <div class="nhp-article-card__body">
          <h3 class="nhp-article-card__title"><?php echo esc_html( $m['title'] ); ?></h3>
          <p class="nhp-article-card__excerpt"><?php echo esc_html( $m['excerpt'] ); ?></p>
          <div class="nhp-article-card__footer">
            <?php if ( $m['rt'] ) : ?><span class="nhp-article-card__time"><?php echo esc_html( $m['rt'] ); ?></span><?php endif; ?>
            <span class="nhp-article-card__read">Read guide <span aria-hidden="true">→</span></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
