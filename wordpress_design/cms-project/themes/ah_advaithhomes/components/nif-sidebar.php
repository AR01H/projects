<?php
/**
 * Component: NIF Portal Sidebar
 * Flash Updates (news bar items), Market Pulse, Popular Now,
 * Browse by Topic, Weekly Market Briefing — all data from DB.
 *
 * @var array $args {
 *   @type array     $site_stats      From ah_get_site_stats().
 *   @type WP_Post[] $popular_posts   Top posts by comment_count.
 *   @type array     $news_bar_items  From ah_get_news_bar_items().
 *   @type WP_Term[] $cats            WP categories.
 *   @type string    $active_cat      Currently active category slug.
 *   @type string    $permalink       Page permalink for filter links.
 * }
 */
defined( 'ABSPATH' ) || exit;

$site_stats     = $args['site_stats']     ?? [];
$popular_posts  = $args['popular_posts']  ?? [];
$news_bar_items = $args['news_bar_items'] ?? [];
$cats           = $args['cats']           ?? [];
$active_cat     = $args['active_cat']     ?? '';
$permalink      = $args['permalink']      ?? get_permalink();
?>

<!-- ══ FLASH UPDATES (News Bar items from CMS Admin) ════════ -->
<?php if ( ! empty( $news_bar_items ) ) : ?>
<div class="nif-sb-card nif-sb-card--flash" aria-label="<?php esc_attr_e( 'Latest Updates', 'ah-theme' ); ?>">
  <div class="nif-sb-card__header nif-sb-card__header--row">
    <span class="nif-section-label--primary"><?php esc_html_e( 'Latest Updates', 'ah-theme' ); ?></span>
    <a href="<?php echo esc_url( home_url( '/news/' ) ); ?>" class="nif-sb-more-link">
      <?php esc_html_e( 'See all', 'ah-theme' ); ?> <span aria-hidden="true">→</span>
    </a>
  </div>
  <ul class="nif-sb-flash">
    <?php foreach ( $news_bar_items as $item ) :
      $item   = is_object( $item ) ? (array) $item : $item;
      $text   = $item['text']        ?? '';
      $url    = $item['link_url']    ?? '';
      $target = $item['link_target'] ?? '_self';
      if ( ! $text ) continue;
    ?>
    <li class="nif-sb-flash__item">
      <?php if ( $url ) : ?>
        <a href="<?php echo esc_url( $url ); ?>"
           class="nif-sb-flash__link"
           target="<?php echo esc_attr( $target ); ?>"
           <?php echo $target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>>
          <span class="nif-sb-flash__dot" aria-hidden="true"></span>
          <?php echo esc_html( $text ); ?>
        </a>
      <?php else : ?>
        <span class="nif-sb-flash__text">
          <span class="nif-sb-flash__dot" aria-hidden="true"></span>
          <?php echo esc_html( $text ); ?>
        </span>
      <?php endif; ?>
    </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<!-- ══ MARKET PULSE ══════════════════════════════════════════ -->
<?php if ( ! empty( $site_stats ) ) :
  $stats = is_array( $site_stats ) ? $site_stats : [];
?>
<div class="nif-sb-card nif-sb-card--stats" aria-label="<?php esc_attr_e( 'Market Pulse', 'ah-theme' ); ?>">
  <div class="nif-sb-card__header">
    <span class="nif-section-label--primary"><?php esc_html_e( 'Market Pulse', 'ah-theme' ); ?></span>
  </div>
  <div class="nif-sb-stats">
    <?php foreach ( $stats as $stat ) :
      $stat  = is_object( $stat ) ? (array) $stat : $stat;
      $label = $stat['label'] ?? '';
      $val   = $stat['value'] ?? ( $stat['val'] ?? '' );
      $trend = $stat['trend'] ?? '';
      if ( ! $label || ! $val ) continue;
    ?>
    <div class="nif-sb-stat">
      <span class="nif-sb-stat__label"><?php echo esc_html( $label ); ?></span>
      <span class="nif-sb-stat__value">
        <?php echo esc_html( $val ); ?>
        <?php if ( $trend === 'up' ) : ?>
          <svg class="nif-trend nif-trend--up" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-label="up"><polyline points="18 15 12 9 6 15"/></svg>
        <?php elseif ( $trend === 'down' ) : ?>
          <svg class="nif-trend nif-trend--down" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-label="down"><polyline points="6 9 12 15 18 9"/></svg>
        <?php endif; ?>
      </span>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- ══ POPULAR NOW ═══════════════════════════════════════════ -->
<?php if ( ! empty( $popular_posts ) ) : ?>
<div class="nif-sb-card" aria-label="<?php esc_attr_e( 'Popular Now', 'ah-theme' ); ?>">
  <div class="nif-sb-card__header">
    <span class="nif-section-label--primary"><?php esc_html_e( 'Popular Now', 'ah-theme' ); ?></span>
  </div>
  <ol class="nif-sb-popular">
    <?php foreach ( $popular_posts as $pp ) :
      $pp_cats = get_the_category( $pp->ID );
      $pp_cat  = $pp_cats[0] ?? null;
    ?>
    <li class="nif-sb-popular__item">
      <a href="<?php echo esc_url( get_permalink( $pp->ID ) ); ?>" class="nif-sb-popular__link">
        <?php if ( $pp_cat ) : ?>
          <span class="nif-sb-popular__cat" data-slug="<?php echo esc_attr( $pp_cat->slug ); ?>">
            <?php echo esc_html( $pp_cat->name ); ?>
          </span>
        <?php endif; ?>
        <span class="nif-sb-popular__title"><?php echo esc_html( get_the_title( $pp->ID ) ); ?></span>
      </a>
    </li>
    <?php endforeach; ?>
  </ol>
</div>
<?php endif; ?>

<!-- ══ BROWSE BY TOPIC ═══════════════════════════════════════ -->
<?php if ( ! empty( $cats ) ) : ?>
<div class="nif-sb-card" aria-label="<?php esc_attr_e( 'Browse by Topic', 'ah-theme' ); ?>">
  <div class="nif-sb-card__header">
    <span class="nif-section-label--primary"><?php esc_html_e( 'Browse by Topic', 'ah-theme' ); ?></span>
  </div>
  <div class="nif-sb-topics">
    <a href="<?php echo esc_url( $permalink ); ?>"
       class="nif-sb-topic<?php echo ! $active_cat ? ' nif-sb-topic--active' : ''; ?>">
      <?php esc_html_e( 'All', 'ah-theme' ); ?>
    </a>
    <?php foreach ( $cats as $cat ) :
      $is_active = ( $active_cat === $cat->slug );
    ?>
    <a href="<?php echo esc_url( add_query_arg( 'category', $cat->slug, $permalink ) ); ?>"
       class="nif-sb-topic<?php echo $is_active ? ' nif-sb-topic--active' : ''; ?>"
       data-slug="<?php echo esc_attr( $cat->slug ); ?>">
      <?php echo esc_html( $cat->name ); ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- ══ WEEKLY MARKET BRIEFING ════════════════════════════════ -->
<div class=" hidden nif-sb-card nif-sb-card--briefing" aria-label="<?php esc_attr_e( 'Weekly Market Briefing', 'ah-theme' ); ?>">
  <div class="nif-sb-card__header">
    <span class="nif-section-label--primary"><?php esc_html_e( 'Weekly Market Briefing', 'ah-theme' ); ?></span>
  </div>
  <p class="nif-sb-briefing__desc">
    <?php esc_html_e( 'Property market updates, buying guides and market intel — delivered every Sunday.', 'ah-theme' ); ?>
  </p>
  <form class="nif-sb-briefing__form" data-form="briefing" novalidate>
    <label for="nif-briefing-email" class="screen-reader-text">
      <?php esc_html_e( 'Your email address', 'ah-theme' ); ?>
    </label>
    <input type="email" id="nif-briefing-email" name="email" class="nif-sb-briefing__input"
           placeholder="<?php esc_attr_e( 'Your email address', 'ah-theme' ); ?>"
           required autocomplete="email">
    <button type="submit" class="nif-sb-briefing__btn">
      <?php esc_html_e( 'Subscribe Free', 'ah-theme' ); ?>
    </button>
    <p class="nif-sb-briefing__note">
      <?php esc_html_e( 'No spam. Unsubscribe anytime.', 'ah-theme' ); ?>
    </p>
  </form>
</div>
