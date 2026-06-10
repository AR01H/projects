<?php
/**
 * Knowledge Hub discover row: Explore Areas · Latest Articles · Experts table.
 * Args: [ 'areas' => {...}, 'articles' => {...}, 'experts' => {...} ]
 */
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/components/khub/khub-icons.php';

$areas    = $args['areas']    ?? array();
$articles = $args['articles'] ?? array();
$experts  = $args['experts']  ?? array();
?>
<section class="khub-discover" aria-label="Explore areas, articles and professional help">
  <div class="container khub-discover__grid">

    <!-- Areas -->
    <?php if ( ! empty( $areas['title'] ) ) : ?>
    <div class="khub-card khub-areas">
      <h3 class="khub-card__title"><?php echo esc_html( $areas['title'] ); ?></h3>
      <?php if ( ! empty( $areas['sub'] ) ) : ?>
        <p class="khub-card__sub"><?php echo esc_html( $areas['sub'] ); ?></p>
      <?php endif; ?>
      <?php if ( ! empty( $areas['image'] ) && file_exists( str_replace( get_template_directory_uri(), get_template_directory(), $areas['image'] ) ) ) : ?>
        <div class="khub-areas__map" style="background-image:url('<?php echo esc_url( $areas['image'] ); ?>')" aria-hidden="true"></div>
      <?php else : ?>
        <div class="khub-areas__map khub-areas__map--ph" aria-hidden="true">🗺️</div>
      <?php endif; ?>
      <?php if ( ! empty( $areas['cta']['label'] ) ) : ?>
        <a class="btn btn-ghost khub-areas__cta" href="<?php echo esc_url( $areas['cta']['url'] ?? '#' ); ?>">
          <?php echo esc_html( $areas['cta']['label'] ); ?> <?php echo ah_khub_icon( 'arrow', 15 ); ?>
        </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Latest articles -->
    <?php if ( ! empty( $articles['title'] ) ) : ?>
    <div class="khub-card khub-articles">
      <h3 class="khub-card__title"><?php echo esc_html( $articles['title'] ); ?></h3>
      <?php if ( ! empty( $articles['sub'] ) ) : ?>
        <p class="khub-card__sub"><?php echo esc_html( $articles['sub'] ); ?></p>
      <?php endif; ?>

      <?php if ( ! empty( $articles['items'] ) ) : ?>
        <div class="khub-articles__grid">
          <?php foreach ( $articles['items'] as $a ) : ?>
            <a class="khub-art" href="<?php echo esc_url( $a['url'] ?? '#' ); ?>">
              <span class="khub-art__media"<?php echo ! empty( $a['image'] ) ? ' style="background-image:url(\'' . esc_url( $a['image'] ) . '\')"' : ''; ?>>
                <?php if ( ! empty( $a['tag'] ) ) : ?><em class="khub-art__tag"><?php echo esc_html( $a['tag'] ); ?></em><?php endif; ?>
              </span>
              <span class="khub-art__title"><?php echo esc_html( $a['title'] ?? '' ); ?></span>
              <?php if ( ! empty( $a['meta'] ) ) : ?><span class="khub-art__meta"><?php echo esc_html( $a['meta'] ); ?></span><?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else : ?>
        <p class="khub-card__sub">New articles are on the way - check back soon.</p>
      <?php endif; ?>

      <?php if ( ! empty( $articles['foot']['label'] ) ) : ?>
        <a class="khub-card__foot" href="<?php echo esc_url( $articles['foot']['url'] ?? '#' ); ?>">
          <?php echo esc_html( $articles['foot']['label'] ); ?> <?php echo ah_khub_icon( 'arrow', 14 ); ?>
        </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Experts -->
    <?php if ( ! empty( $experts['title'] ) ) : ?>
    <div class="khub-card khub-experts">
      <div class="khub-experts__head">
        <h3 class="khub-card__title"><?php echo esc_html( $experts['title'] ); ?></h3>
        <span class="khub-experts__badge"><?php echo ah_khub_icon( 'agent', 22 ); ?></span>
      </div>
      <?php if ( ! empty( $experts['sub'] ) ) : ?>
        <p class="khub-card__sub"><?php echo esc_html( $experts['sub'] ); ?></p>
      <?php endif; ?>
      <ul class="khub-experts__list" role="list">
        <?php foreach ( (array) ( $experts['rows'] ?? array() ) as $r ) : ?>
          <li class="khub-expert">
            <span class="khub-expert__ico"><?php echo ah_khub_icon( $r['icon'] ?? 'agent', 18 ); ?></span>
            <span class="khub-expert__info">
              <strong class="khub-expert__name"><?php echo esc_html( $r['name'] ?? '' ); ?></strong>
              <small class="khub-expert__desc"><?php echo esc_html( $r['desc'] ?? '' ); ?></small>
            </span>
            <a class="khub-expert__cta" href="<?php echo esc_url( $r['url'] ?? '#' ); ?>">
              <?php echo esc_html( $r['cta'] ?? 'Find help' ); ?> <?php echo ah_khub_icon( 'arrow', 13 ); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

  </div>
</section>
