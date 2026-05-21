<?php
/**
 * Template Name: News Listing
 */
get_header();

$per_page    = 10;
$paged       = max( 1, get_query_var( 'paged' ) ?: ( get_query_var( 'page' ) ?: 1 ) );
$active_slug = sanitize_text_field( $_GET['term'] ?? '' );

// ── Fetch active items ─────────────────────────────────────────────────────
$model     = class_exists( 'AH_Newsbar_Model' ) ? new AH_Newsbar_Model() : null;
$all_active = $model ? $model->get_active() : [];

// ── Batch-fetch taxonomy terms for all items ───────────────────────────────
$taxonomy_result = $all_active
	? AH_Theme_Content_Taxonomy::get_terms_for_items( $all_active, 'news_bar_item' )
	: [ 'item_terms' => [], 'unique_terms' => [] ];

$item_terms   = $taxonomy_result['item_terms'];   // [ item_id => [ term, … ] ]
$unique_terms = $taxonomy_result['unique_terms']; // [ slug => term ] - for filter tabs

// ── Apply term filter ──────────────────────────────────────────────────────
$filtered = $all_active;
if ( $active_slug ) {
	$filtered = array_values( array_filter( $all_active, function( $item ) use ( $active_slug, $item_terms ) {
		foreach ( $item_terms[ (int) $item->id ] ?? [] as $t ) {
			if ( $t->slug === $active_slug ) return true;
		}
		return false;
	} ) );
}

$total     = count( $filtered );
$max_pages = $total ? (int) ceil( $total / $per_page ) : 1;
$items     = array_slice( $filtered, ( $paged - 1 ) * $per_page, $per_page );

// ── Sidebar stats ──────────────────────────────────────────────────────────
$properties = get_option( 'ah_featured_properties', [] );
if ( is_string( $properties ) ) $properties = json_decode( $properties, true ) ?: [];

$cities_map = [];
foreach ( $properties as $p ) {
	$city = $p['city'] ?? $p['location'] ?? '';
	if ( $city ) $cities_map[ $city ] = ( $cities_map[ $city ] ?? 0 ) + 1;
}
arsort( $cities_map );

// Badge colour map for taxonomy terms
$term_colors = [
	'new-launch'  => [ 'bg' => '#dcfce7', 'color' => '#15803d', 'border' => '#bbf7d0' ],
	'expansion'   => [ 'bg' => '#dbeafe', 'color' => '#1d4ed8', 'border' => '#bfdbfe' ],
	'offers'      => [ 'bg' => '#fce7f3', 'color' => '#be185d', 'border' => '#fbcfe8' ],
	'general'     => [ 'bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#e2e8f0' ],
];
$fallback_colors = [
	[ 'bg' => '#fff7ed', 'color' => '#c2410c', 'border' => '#fed7aa' ],
	[ 'bg' => '#f5f3ff', 'color' => '#7c3aed', 'border' => '#ddd6fe' ],
	[ 'bg' => '#ecfdf5', 'color' => '#065f46', 'border' => '#a7f3d0' ],
];
$term_color_index = 0;
$term_color_cache = [];
function nc_term_color( string $slug, array &$cache, array $map, array $fallbacks, int &$idx ): array {
	if ( isset( $cache[ $slug ] ) ) return $cache[ $slug ];
	if ( isset( $map[ $slug ] ) )   { $cache[ $slug ] = $map[ $slug ]; return $cache[ $slug ]; }
	$cache[ $slug ] = $fallbacks[ $idx % count( $fallbacks ) ];
	$idx++;
	return $cache[ $slug ];
}
?>

<?php get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'Latest Updates',
	'title'      => 'News &amp;',
	'title_em'   => 'Announcements',
	'desc'       => 'Stay up to date with the latest news, market updates, and announcements from Advaith Homes.',
	'badge'      => count( $all_active ) ? count( $all_active ) . ' items' : '',
	'breadcrumb' => [ [ 'Home', home_url( '/' ) ], [ 'News', '' ] ],
] ); ?>

<!-- ── Main layout ────────────────────────────────────────────────────────── -->
<div class="news-layout section">
  <div class="container news-layout__inner">

    <!-- ── LEFT: cards ─────────────────────────────────────────────────── -->
    <main class="news-layout__main">

      <!-- Cards list -->
      <?php if ( $items ) : ?>
      <div class="nc-list" id="nc-list">
        <?php foreach ( $items as $i => $item ) :
          $url     = trim( $item->link_url    ?? '' );
          $target  = $item->link_target ?? '_self';
          $content = trim( $item->content     ?? '' );
          $terms   = $item_terms[ (int) $item->id ] ?? [];
          $card_id = 'nc-' . (int) $item->id;
          $first_term = $terms[0] ?? null;
          $col     = $first_term ? nc_term_color( $first_term->slug, $term_color_cache, $term_colors, $fallback_colors, $term_color_index ) : $term_colors['general'];
        ?>
        <article class="nc2" id="<?php echo esc_attr( $card_id ); ?>" data-id="<?php echo esc_attr( (int) $item->id ); ?>">

          <button class="nc2__row" aria-expanded="false" aria-controls="<?php echo esc_attr( $card_id . '-body' ); ?>" type="button">
            <span class="nc2__dot" aria-hidden="true"></span>

            <?php if ( $first_term ) : ?>
              <span class="nc2__tag"
                    style="background:<?php echo esc_attr( $col['bg'] ); ?>;color:<?php echo esc_attr( $col['color'] ); ?>;border-color:<?php echo esc_attr( $col['border'] ); ?>">
                <?php echo esc_html( strtoupper( $first_term->name ) ); ?>
              </span>
            <?php endif; ?>

            <span class="nc2__text"><?php echo esc_html( $item->text ); ?></span>

            <span class="nc2__chevron" aria-hidden="true">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </span>
          </button>

          <div class="nc2__body" id="<?php echo esc_attr( $card_id . '-body' ); ?>" hidden>
            <?php if ( $content ) : ?>
              <div class="nc2__content">
                <?php echo wp_kses_post( $content ); ?>
              </div>
            <?php endif; ?>

            <div class="nc2__footer">
              <?php if ( $url ) : ?>
                <a href="<?php echo esc_url( $url ); ?>"
                   target="<?php echo esc_attr( $target ); ?>"
                   <?php echo $target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                   class="btn btn-primary btn-sm nc2__view-btn">
                  View details →
                </a>
              <?php endif; ?>
            </div>
          </div>

        </article>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ( $max_pages > 1 ) :
        $links = paginate_links( [
          'base'      => trailingslashit( get_permalink() ) . '%_%',
          'format'    => '?paged=%#%',
          'current'   => $paged,
          'total'     => $max_pages,
          'prev_text' => '← Prev',
          'next_text' => 'Next →',
          'type'      => 'array',
        ] );
        if ( $links ) :
      ?>
      <nav class="pagination" style="margin-top:32px">
        <ul class="pagination__list">
          <?php foreach ( $links as $link ) echo '<li class="pagination__item">' . $link . '</li>'; ?>
        </ul>
      </nav>
      <?php endif; endif; ?>

      <?php else : ?>
      <div class="text-center" style="padding:60px 0">
        <div style="font-size:3rem;margin-bottom:12px">📰</div>
        <h2 style="font-size:1.25rem;margin-bottom:8px">No news found</h2>
        <p style="color:var(--text-secondary)">
          <?php echo $active_slug ? 'Nothing in this category yet.' : 'Check back soon.'; ?>
        </p>
        <?php if ( $active_slug ) : ?>
          <a href="<?php echo esc_url( get_permalink() ); ?>" class="btn btn-outline" style="margin-top:16px">View all →</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </main>

  </div>
</div>

<?php get_template_part( 'components/cta-section', null, [] ); ?>
<?php get_footer(); ?>

<style>
/* ── Layout ──────────────────────────────────────────────────────────────── */
.news-layout__inner {
  display: grid;
  grid-template-columns: 1fr;
  gap: 40px;
  align-items: start;
}
@media (max-width: 900px) {
  .news-layout__sidebar { order: -1; }
}

/* ── Cards ───────────────────────────────────────────────────────────────── */
.nc-list { display: flex; flex-direction: column; gap: 10px; }

.nc2 {
  border: 1.5px solid var(--border, #e2e8f0);
  border-radius: 12px;
  background: #fff;
  overflow: hidden;
  transition: box-shadow .2s, border-color .2s;
}
.nc2:hover { box-shadow: 0 2px 12px rgba(0,0,0,.08); }
.nc2.nc2--read { opacity: .55; }
.nc2.nc2--open { border-color: var(--client-color-400, #f7c62f); box-shadow: 0 2px 12px rgba(247,198,47,.18); }

.nc2__row {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 18px;
  background: none;
  border: none;
  cursor: pointer;
  text-align: left;
}
.nc2__row:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

.nc2__dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: #22c55e;
  flex-shrink: 0;
  transition: background .2s;
}
.nc2--read .nc2__dot { background: #cbd5e1; }

.nc2__date {
  flex-shrink: 0;
  font-size: .75rem;
  font-weight: 600;
  color: var(--text-secondary);
  background: var(--bg-alt, #f8fafc);
  border: 1px solid var(--border);
  border-radius: 999px;
  padding: 2px 10px;
  white-space: nowrap;
}

.nc2__tag {
  flex-shrink: 0;
  font-size: .68rem;
  font-weight: 700;
  letter-spacing: .06em;
  border: 1px solid;
  border-radius: 4px;
  padding: 2px 8px;
  white-space: nowrap;
}

.nc2__text {
  flex: 1;
  font-size: .9375rem;
  font-weight: 600;
  color: var(--text, #0f172a);
  line-height: 1.45;
}

.nc2__chevron {
  flex-shrink: 0;
  color: var(--text-secondary);
  transition: transform .25s;
}
.nc2.nc2--open .nc2__chevron { transform: rotate(180deg); }

/* Expanded body */
.nc2__body { padding: 0 18px 18px; }

.nc2__content {
  font-size: .9rem;
  color: var(--text-secondary, #475569);
  line-height: 1.75;
  border-top: 1px solid var(--border);
  padding-top: 14px;
  margin-bottom: 14px;
}
.nc2__content h2, .nc2__content h3 {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text);
  margin: 0 0 8px;
}
.nc2__content p  { margin: 0 0 .65em; }
.nc2__content ul { padding-left: 1.25em; margin: 0 0 .65em; }
.nc2__content li { margin-bottom: .35em; }

.nc2__footer {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.nc2__read-btn.nc2--done {
  text-decoration: line-through;
  opacity: .5;
  pointer-events: none;
}

/* ── Sidebar widgets ─────────────────────────────────────────────────────── */
.news-layout__sidebar {
  display: flex;
  flex-direction: column;
  gap: 16px;
  position: sticky;
  top: calc(var(--nav-h, 72px) + 24px);
}

.ns-widget {
  border: 1.5px solid var(--border, #e2e8f0);
  border-radius: 12px;
  background: #fff;
  padding: 20px;
}
.ns-widget__title {
  font-size: .7rem;
  font-weight: 700;
  letter-spacing: .1em;
  color: var(--text-secondary);
  text-transform: uppercase;
  margin: 0 0 16px;
  padding-bottom: 10px;
  border-bottom: 1px solid var(--border);
}

/* Stats grid */
.ns-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.ns-stat {
  background: var(--bg-alt, #f8fafc);
  border-radius: 8px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.ns-stat__num {
  font-family: var(--font-display);
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text);
  line-height: 1;
}
.ns-stat__label {
  font-size: .72rem;
  color: var(--text-secondary);
}

/* Cities */
.ns-cities { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.ns-city__top { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 5px; }
.ns-city__name { font-size: .875rem; font-weight: 600; color: var(--text); }
.ns-city__count { font-size: .72rem; color: var(--text-secondary); }
.ns-city__bar { height: 4px; background: var(--bg-alt, #f1f5f9); border-radius: 2px; overflow: hidden; }
.ns-city__fill { height: 100%; background: var(--client-color-400, #f7c62f); border-radius: 2px; }

/* Dark CTA */
.ns-cta {
  background: #1a1a2e;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
}
.ns-cta__text {
  font-family: var(--font-display);
  font-size: 1.125rem;
  color: #fff;
  line-height: 1.4;
  margin: 0;
}
.ns-cta__text em { color: var(--client-color-400, #f7c62f); font-style: italic; }
</style>

<script>
(function(){
  // Expand / collapse
  document.querySelectorAll('.nc2__row').forEach(function(btn){
    btn.addEventListener('click', function(){
      var card = btn.closest('.nc2');
      var body = document.getElementById(btn.getAttribute('aria-controls'));
      var open = card.classList.toggle('nc2--open');
      btn.setAttribute('aria-expanded', open);
      open ? body.removeAttribute('hidden') : body.setAttribute('hidden', '');
    });
  });
})();
</script>
