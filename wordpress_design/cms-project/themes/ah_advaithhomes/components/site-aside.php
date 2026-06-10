<?php
/**
 * Reusable informative aside / sidebar - drop into any page's main column.
 * Makes the site feel like a complete information platform: every page can
 * surface latest news, calculators, helpful links, stats and a newsletter.
 *
 * Args (all optional):
 *   cards       (array)  Which cards + order. Default:
 *                        ['news','calculators','links','stats','newsletter']
 *   parent_term (string) Scope "Latest News" to a group (e.g. 'buying').
 *   exclude     (int)    Post ID to exclude from news.
 *   sticky      (bool)   Sticky on scroll. Default true.
 */
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/components/khub/khub-icons.php';

$cards   = $args['cards']       ?? array( 'news', 'calculators', 'links', 'stats', 'newsletter' );
$pt_slug = $args['parent_term'] ?? '';
$exclude = (int) ( $args['exclude'] ?? 0 );
$sticky  = $args['sticky'] ?? true;
$wrap    = $args['wrap']   ?? true; // false → render only the cards (host page provides the <aside>)

/* ── Data sources (reuse the shared helpers / data files) ─────────────────── */
$news_args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 4,
	'orderby'        => 'date',
	'order'          => 'DESC',
);
if ( $exclude ) {
	$news_args['post__not_in'] = array( $exclude );
}
if ( $pt_slug && function_exists( 'ah_parent_term_cat_ids' ) ) {
	$cat_ids = ah_parent_term_cat_ids( $pt_slug );
	if ( $cat_ids ) {
		$news_args['category__in'] = $cat_ids;
	}
}
$news = get_posts( $news_args );

$calcs = array();
if ( class_exists( 'AH_Real_Loader' ) ) {
	$cdata = AH_Real_Loader::json( 'calculators' );
	foreach ( (array) ( $cdata['calculators'] ?? array() ) as $c ) {
		if ( ! empty( $c['popular'] ) ) {
			$calcs[] = $c;
		}
	}
	$calcs = array_slice( $calcs, 0, 5 );
}

$stats = function_exists( 'ah_get_site_stats' ) ? ah_get_site_stats() : array();

$links = array(
	array( 'icon' => 'doc',      'label' => 'All Guides',       'url' => home_url( '/guides/' ) ),
	array( 'icon' => 'calc',     'label' => 'Calculators',      'url' => home_url( '/calculators/' ) ),
	array( 'icon' => 'mortgage', 'label' => 'Mortgage Advice',  'url' => home_url( '/mortgages/' ) ),
	array( 'icon' => 'home',     'label' => 'Explore Areas',    'url' => home_url( '/areas/' ) ),
	array( 'icon' => 'agent',    'label' => 'Ask an Expert',    'url' => home_url( '/contact/' ) ),
);
$links = apply_filters( 'ah_aside_links', $links, $pt_slug );

$render = array(); // collect non-empty cards so we can bail if all empty
?>
<aside class="ah-aside<?php echo $sticky ? ' ah-aside--sticky' : ''; ?>" aria-label="More on this topic">

  <?php foreach ( $cards as $card ) : ?>
    <?php if ( 'news' === $card && $news ) : ?>
      <div class="aside-card">
        <h3 class="aside-card__title">Latest Property News</h3>
        <ul class="aside-news" role="list">
          <?php foreach ( $news as $p ) :
            $thumb = get_the_post_thumbnail_url( $p->ID, 'thumbnail' ) ?: get_the_post_thumbnail_url( $p->ID, 'medium' ) ?: '';
          ?>
            <li class="aside-news__item">
              <a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>">
                <?php if ( $thumb ) : ?><span class="aside-news__img" style="background-image:url('<?php echo esc_url( $thumb ); ?>')" aria-hidden="true"></span><?php endif; ?>
                <span class="aside-news__body">
                  <span class="aside-news__t"><?php echo esc_html( wp_trim_words( get_the_title( $p->ID ), 9, '…' ) ); ?></span>
                  <small class="aside-news__d"><?php echo esc_html( get_the_date( '', $p->ID ) ); ?></small>
                </span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <a class="aside-card__foot" href="<?php echo esc_url( home_url( '/allnews/' ) ); ?>">View all news <?php echo ah_khub_icon( 'arrow', 13 ); ?></a>
      </div>

    <?php elseif ( 'calculators' === $card && $calcs ) : ?>
      <div class="aside-card">
        <h3 class="aside-card__title">Popular Calculators</h3>
        <ul class="aside-links" role="list">
          <?php foreach ( $calcs as $c ) : ?>
            <li><a class="aside-link" href="<?php echo esc_url( home_url( $c['url'] ?? '/calculators/' ) ); ?>">
              <span class="aside-link__ico"><?php echo ah_khub_icon( $c['icon'] ?? 'calc', 16 ); ?></span>
              <span class="aside-link__label"><?php echo esc_html( $c['title'] ?? '' ); ?></span>
              <span class="aside-link__chev"><?php echo ah_khub_icon( 'arrow', 13 ); ?></span>
            </a></li>
          <?php endforeach; ?>
        </ul>
        <a class="aside-card__foot" href="<?php echo esc_url( home_url( '/calculators/' ) ); ?>">View all calculators <?php echo ah_khub_icon( 'arrow', 13 ); ?></a>
      </div>

    <?php elseif ( 'links' === $card && $links ) : ?>
      <div class="aside-card">
        <h3 class="aside-card__title">Helpful Links</h3>
        <ul class="aside-links" role="list">
          <?php foreach ( $links as $l ) : ?>
            <li><a class="aside-link" href="<?php echo esc_url( $l['url'] ?? '#' ); ?>">
              <span class="aside-link__ico"><?php echo ah_khub_icon( $l['icon'] ?? 'doc', 16 ); ?></span>
              <span class="aside-link__label"><?php echo esc_html( $l['label'] ?? '' ); ?></span>
              <span class="aside-link__chev"><?php echo ah_khub_icon( 'arrow', 13 ); ?></span>
            </a></li>
          <?php endforeach; ?>
        </ul>
      </div>

    <?php elseif ( 'stats' === $card && $stats ) : ?>
      <div class="aside-card aside-card--stats">
        <h3 class="aside-card__title">Trusted Resource</h3>
        <div class="aside-stats">
          <?php foreach ( array_slice( (array) $stats, 0, 4 ) as $s ) :
            $s = (array) $s;
            $num = $s['value'] ?? $s['number'] ?? $s['stat'] ?? '';
            $lab = $s['label'] ?? $s['title'] ?? '';
            if ( '' === $num && '' === $lab ) continue;
          ?>
            <div class="aside-stat">
              <strong class="aside-stat__num"><?php echo esc_html( $num ); ?></strong>
              <small class="aside-stat__lab"><?php echo esc_html( $lab ); ?></small>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    <?php elseif ( 'newsletter' === $card ) : ?>
      <div class="aside-card aside-card--news-cta">
        <span class="aside-card--news-cta__ico"><?php echo ah_khub_icon( 'mail', 22 ); ?></span>
        <h3 class="aside-card__title">Stay Informed</h3>
        <p class="aside-card__sub">Get the latest guides, news and updates straight to your inbox.</p>
        <a class="btn btn-primary btn-block" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" style="justify-content:center">Subscribe</a>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>

</aside>
