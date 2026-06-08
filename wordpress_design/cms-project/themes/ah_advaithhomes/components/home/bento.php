<?php
defined( 'ABSPATH' ) || exit;

$bento = $args['bento'] ?? [];
$tiles = $args['tiles'] ?? [];
$fb    = $args['fb']    ?? [];

$card_wide = $bento['wide'] ?? null;
$card_dark = $bento['dark'] ?? null;
$card_art  = $bento['art']  ?? null;
$card_n1   = $bento['n1']   ?? null;
$card_n2   = $bento['n2']   ?? null;

$t0 = $tiles[0] ?? null;
$t1 = $tiles[1] ?? null;
$t2 = $tiles[2] ?? null;

if ( ! $card_wide ) return;

$dots = '<span class="nhp-mq-dots" aria-hidden="true"><i></i><i></i><i class="is-on"></i></span>';

$badge = function ( $card ) {
	if ( ! empty( $card['is_news'] ) ) return '<span class="nhp-mq-badge nhp-mq-badge--green">NEWS</span>';
	if ( ! empty( $card['badge'] ) )   return '<span class="nhp-mq-badge nhp-mq-badge--cat">' . esc_html( strtoupper( $card['badge'] ) ) . '</span>';
	return '';
};
?>
<section class="nhp-bento-section">
  <div class="container">
    <div class="nhp-mq-grid">

      <!-- ROW 1 ── wide card -->
      <?php $b = $card_wide; $fb_wide = $b['is_news'] ? $fb['news'] : $fb['blog']; ?>
      <a href="<?php echo esc_url( $b['url'] ); ?>" class="nhp-mq-wide nhp-mq-area-wide"
         style="--ac:<?php echo esc_attr( AH_Home_Data::slug_color( $b['slug'] ) ); ?>" data-aos="fade-up">
        <div class="nhp-mq-wide__img" style="background-image:url('<?php echo esc_url( $b['thumb'] ?: $fb_wide ); ?>')">
          <?php echo $badge( $b ); ?>
        </div>
        <div class="nhp-mq-wide__body">
          <h3 class="nhp-mq-wide__title"><?php echo esc_html( $b['title'] ); ?></h3>
          <?php if ( $b['excerpt'] ) : ?><p class="nhp-mq-wide__excerpt"><?php echo esc_html( $b['excerpt'] ); ?></p><?php endif; ?>
          <div class="nhp-mq-foot">
            <?php echo $dots; ?>
            <?php if ( $b['meta'] ) : ?><span class="nhp-mq-rt"><?php echo esc_html( $b['meta'] ); ?></span><?php endif; ?>
          </div>
        </div>
      </a>

      <!-- ROW 1 ── promo tile -->
      <?php if ( $t0 ) :
        $t0_img = ! empty( $t0['image'] ) ? get_template_directory_uri() . '/' . $t0['image'] : '';
      ?>
      <a href="<?php echo esc_url( home_url( $t0['url'] ) ); ?>" class="nhp-mq-promo nhp-mq-area-promo"
         style="--ac:<?php echo esc_attr( $t0['color'] ); ?>" data-aos="fade-up" data-delay="80">
        <div class="nhp-mq-tile__img" <?php if ( $t0_img ) echo 'style="background-image:url(' . esc_url( $t0_img ) . ')"'; ?>>
          <span class="nhp-mq-promo__icon"><?php echo esc_html( $t0['icon'] ); ?></span>
        </div>
        <div class="nhp-mq-promo__body">
          <h3 class="nhp-mq-promo__title"><?php echo esc_html( $t0['title'] ); ?></h3>
          <?php if ( $t0['desc'] ) : ?><p class="nhp-mq-promo__desc"><?php echo esc_html( $t0['desc'] ); ?></p><?php endif; ?>
          <span class="nhp-mq-go"><?php echo esc_html( $t0['cta'] ); ?> <span aria-hidden="true">→</span></span>
        </div>
      </a>
      <?php endif; ?>

      <!-- ROW 2 ── dark featured (post) -->
      <?php if ( $card_dark ) : $b = $card_dark; ?>
      <a href="<?php echo esc_url( $b['url'] ); ?>" class="nhp-mq-dark nhp-mq-area-dark"
         style="--ac:<?php echo esc_attr( AH_Home_Data::slug_color( $b['slug'] ) ); ?>" data-aos="fade-up">
        <div class="nhp-mq-dark__img" style="background-image:url('<?php echo esc_url( $b['thumb'] ?: $fb['guides'] ); ?>')">
        </div>
        <div class="nhp-mq-dark__body">
          <?php if ( $b['badge'] ) : ?><span class="nhp-mq-badge nhp-mq-badge--glass"><?php echo esc_html( strtoupper( $b['badge'] ) ); ?></span><?php endif; ?>
          <h3 class="nhp-mq-dark__title"><?php echo esc_html( $b['title'] ); ?></h3>
          <?php if ( $b['excerpt'] ) : ?><p class="nhp-mq-dark__excerpt"><?php echo esc_html( $b['excerpt'] ); ?></p><?php endif; ?>
          <div class="nhp-mq-foot nhp-mq-foot--light">
            <?php echo $dots; ?>
            <?php if ( $b['meta'] ) : ?><span class="nhp-mq-rt"><?php echo esc_html( $b['meta'] ); ?></span><?php endif; ?>
          </div>
        </div>
      </a>
      <?php endif; ?>

      <!-- ROW 2 ── article (post) -->
      <?php if ( $card_art ) : $b = $card_art; ?>
      <a href="<?php echo esc_url( $b['url'] ); ?>" class="nhp-mq-card nhp-mq-area-art"
         style="--ac:<?php echo esc_attr( AH_Home_Data::slug_color( $b['slug'] ) ); ?>" data-aos="fade-up" data-delay="80">
        <div class="nhp-mq-card__img" style="background-image:url('<?php echo esc_url( $b['thumb'] ?: $fb['blog'] ); ?>')">
          <?php echo $badge( $b ); ?>
        </div>
        <div class="nhp-mq-card__body">
          <h3 class="nhp-mq-card__title"><?php echo esc_html( $b['title'] ); ?></h3>
          <?php if ( $b['excerpt'] ) : ?><p class="nhp-mq-card__excerpt"><?php echo esc_html( $b['excerpt'] ); ?></p><?php endif; ?>
          <div class="nhp-mq-foot">
            <?php echo $dots; ?>
            <?php if ( $b['meta'] ) : ?><span class="nhp-mq-rt"><?php echo esc_html( $b['meta'] ); ?></span><?php endif; ?>
          </div>
        </div>
      </a>
      <?php endif; ?>

      <!-- ROW 2 ── service tile -->
      <?php if ( $t1 ) :
        $t1_img = ! empty( $t1['image'] ) ? get_template_directory_uri() . '/' . $t1['image'] : '';
      ?>
      <a href="<?php echo esc_url( home_url( $t1['url'] ) ); ?>" class="nhp-mq-service nhp-mq-area-svc1"
         style="--ac:<?php echo esc_attr( $t1['color'] ); ?>" data-aos="fade-up" data-delay="160">
        <div class="nhp-mq-tile__img" <?php if ( $t1_img ) echo 'style="background-image:url(' . esc_url( $t1_img ) . ')"'; ?>>
          <span class="nhp-mq-service__icon"><?php echo esc_html( $t1['icon'] ); ?></span>
        </div>
        <div class="nhp-mq-service__body">
          <h3 class="nhp-mq-service__title"><?php echo esc_html( $t1['title'] ); ?></h3>
          <?php if ( $t1['desc'] ) : ?><p class="nhp-mq-service__desc"><?php echo esc_html( $t1['desc'] ); ?></p><?php endif; ?>
          <span class="nhp-mq-go"><?php echo esc_html( $t1['cta'] ); ?> <span aria-hidden="true">→</span></span>
        </div>
      </a>
      <?php endif; ?>

      <!-- ROW 3 ── service tile -->
      <?php if ( $t2 ) :
        $t2_img = ! empty( $t2['image'] ) ? get_template_directory_uri() . '/' . $t2['image'] : '';
      ?>
      <a href="<?php echo esc_url( home_url( $t2['url'] ) ); ?>" class="nhp-mq-service nhp-mq-area-svc2"
         style="--ac:<?php echo esc_attr( $t2['color'] ); ?>" data-aos="fade-up">
        <div class="nhp-mq-tile__img" <?php if ( $t2_img ) echo 'style="background-image:url(' . esc_url( $t2_img ) . ')"'; ?>>
          <span class="nhp-mq-service__icon"><?php echo esc_html( $t2['icon'] ); ?></span>
        </div>
        <div class="nhp-mq-service__body">
          <h3 class="nhp-mq-service__title"><?php echo esc_html( $t2['title'] ); ?></h3>
          <?php if ( $t2['desc'] ) : ?><p class="nhp-mq-service__desc"><?php echo esc_html( $t2['desc'] ); ?></p><?php endif; ?>
          <span class="nhp-mq-go"><?php echo esc_html( $t2['cta'] ); ?> <span aria-hidden="true">→</span></span>
        </div>
      </a>
      <?php endif; ?>

      <!-- ROW 3 ── two news/post cards -->
      <?php foreach ( [ 'svc3' => $card_n1, 'svc4' => $card_n2 ] as $area => $b ) :
        if ( ! $b ) continue;
        $fb_card = $b['is_news'] ? $fb['news'] : $fb['review'];
      ?>
      <a href="<?php echo esc_url( $b['url'] ); ?>" class="nhp-mq-card nhp-mq-area-<?php echo esc_attr( $area ); ?>"
         style="--ac:<?php echo esc_attr( AH_Home_Data::slug_color( $b['slug'] ) ); ?>" data-aos="fade-up">
        <div class="nhp-mq-card__img" style="background-image:url('<?php echo esc_url( $b['thumb'] ?: $fb_card ); ?>')">
          <?php echo $badge( $b ); ?>
        </div>
        <div class="nhp-mq-card__body">
          <h3 class="nhp-mq-card__title"><?php echo esc_html( $b['title'] ); ?></h3>
          <?php if ( $b['excerpt'] ) : ?><p class="nhp-mq-card__excerpt"><?php echo esc_html( $b['excerpt'] ); ?></p><?php endif; ?>
          <div class="nhp-mq-foot">
            <?php echo $dots; ?>
            <?php if ( $b['meta'] ) : ?><span class="nhp-mq-rt"><?php echo esc_html( $b['meta'] ); ?></span><?php endif; ?>
          </div>
        </div>
      </a>
      <?php endforeach; ?>

    </div><!-- /.nhp-mq-grid -->
  </div>
</section>
