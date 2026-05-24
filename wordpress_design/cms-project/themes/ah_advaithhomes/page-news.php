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
	'desc'       => 'Stay up to date with the latest news, market updates, and announcements from ' . CLIENT_FULL_TITLE . '.',
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
        <?php
        $fallback_colors_bg = [ '#fef3c7', '#dbeafe', '#fce7f3', '#dcfce7' ];
        $fallback_colors_fg = [ '#92400e', '#1e40af', '#9d174d', '#166534' ];
        foreach ( $items as $i => $item ) :
          $url        = trim( $item->link_url ?? '' );
          $target     = $item->link_target ?? '_self';
          $content    = trim( $item->content ?? '' );
          $terms      = $item_terms[ (int) $item->id ] ?? [];
          $card_id    = 'nc-' . (int) $item->id;
          $first_term = $terms[0] ?? null;
          $col        = $first_term ? nc_term_color( $first_term->slug, $term_color_cache, $term_colors, $fallback_colors, $term_color_index ) : $term_colors['general'];
          $img_id     = (int) ( $item->image_id ?? 0 );
          $img_url    = $img_id ? wp_get_attachment_image_url( $img_id, 'medium_large' ) : '';
          $fb_bg      = $fallback_colors_bg[ $i % 4 ];
          $fb_fg      = $fallback_colors_fg[ $i % 4 ];
        ?>
        <article class="nc4" data-aos="fade-up" data-aos-delay="<?php echo min( $i, 4 ) * 50; ?>">

          <div class="nc4__main">

            <!-- Thumbnail -->
            <div class="nc4__thumb" <?php echo ! $img_url ? 'style="background:' . esc_attr( $fb_bg ) . '"' : ''; ?>>
              <?php if ( $img_url ) : ?>
                <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_ITEM_TEXT ); ?>" loading="lazy">
              <?php else : ?>
                <span class="nc4__thumb-icon" style="color:<?php echo esc_attr( $fb_fg ); ?>">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                </span>
              <?php endif; ?>
            </div>

            <!-- Content -->
            <div class="nc4__content">
              <?php if ( $first_term ) : ?>
                <span class="nc4__badge"
                      style="background:<?php echo esc_attr( $col['bg'] ); ?>;color:<?php echo esc_attr( $col['color'] ); ?>;border-color:<?php echo esc_attr( $col['border'] ); ?>">
                  <?php echo esc_html( strtoupper( $first_term->name ) ); ?>
                </span>
              <?php endif; ?>
              <h3 class="nc4__title"><?php echo esc_html( $item->text ); ?></h3>
              <?php if ( $item->start_date ) : ?>
                <span class="nc4__meta">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  <?php echo esc_html( date_i18n( 'j M Y', strtotime( $item->start_date ) ) ); ?>
                </span>
              <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="nc4__actions">
              <?php if ( $content ) : ?>
                <button class="nc4__expand-btn" type="button"
                        aria-expanded="false"
                        aria-controls="<?php echo esc_attr( $card_id . '-detail' ); ?>"
                        title="<?php echo esc_attr( TXT_EXPAND ); ?>">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
              <?php endif; ?>
              <?php if ( $url ) : ?>
                <a href="<?php echo esc_url( $url ); ?>"
                   target="<?php echo esc_attr( $target ); ?>"
                   <?php echo $target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                   class="nc4__link-btn" title="<?php echo esc_attr( TXT_VIEW_DETAILS ); ?>">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
              <?php endif; ?>
            </div>

          </div>

          <!-- Expandable detail -->
          <?php if ( $content ) : ?>
          <div class="nc4__detail prose-content" id="<?php echo esc_attr( $card_id . '-detail' ); ?>" hidden>
            <?php echo ah_format_content( wp_kses_post( $content ) ); ?>
            <?php if ( $url ) : ?>
              <a href="<?php echo esc_url( $url ); ?>"
                 target="<?php echo esc_attr( $target ); ?>"
                 <?php echo $target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                 class="btn btn-primary btn-sm" style="margin-top:12px;display:inline-flex;">
                View details →
              </a>
            <?php endif; ?>
          </div>
          <?php endif; ?>

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
          <?php foreach ( $links as $link ) echo esc_html( TXT_LI_CLASS_PAGINATION_ITEM_LINK_LI ); ?>
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

/* ── News Cards List (horizontal) ────────────────────────────────────────── */
.nc-list { display: flex; flex-direction: column; gap: 12px; }

.nc4 {
  background: #fff;
  border: 1.5px solid var(--border, #e2e8f0);
  border-radius: 14px;
  overflow: hidden;
  transition: box-shadow .22s, border-color .22s;
}
.nc4:hover {
  box-shadow: 0 4px 20px rgba(0,0,0,.09);
  border-color: #cbd5e1;
}

/* Main row */
.nc4__main {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 14px 16px;
}

/* Thumbnail */
.nc4__thumb {
  flex-shrink: 0;
  width: 88px;
  height: 66px;
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}
.nc4__thumb img {
  width: 100%; height: 100%;
  object-fit: cover;
  display: block;
  transition: transform .4s cubic-bezier(.4,0,.2,1);
}
.nc4:hover .nc4__thumb img { transform: scale(1.08); }
.nc4__thumb-icon { opacity: .55; }

/* Content */
.nc4__content { flex: 1; min-width: 0; }
.nc4__badge {
  display: inline-block;
  font-size: .63rem;
  font-weight: 700;
  letter-spacing: .08em;
  text-transform: uppercase;
  border: 1px solid;
  border-radius: 4px;
  padding: 2px 8px;
  margin-bottom: 6px;
}
.nc4__title {
  font-family: var(--font-display);
  font-size: .9375rem;
  font-weight: 700;
  color: var(--text, #0f172a);
  line-height: 1.4;
  margin: 0 0 5px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.nc4__meta {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: .72rem;
  color: var(--text-secondary, #94a3b8);
}

/* Action buttons */
.nc4__actions {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 6px;
}
.nc4__expand-btn,
.nc4__link-btn {
  width: 34px; height: 34px;
  border-radius: 50%;
  border: 1.5px solid var(--border, #e2e8f0);
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--text-secondary, #64748b);
  text-decoration: none;
  transition: background .18s, border-color .18s, color .18s;
}
.nc4__expand-btn:hover,
.nc4__link-btn:hover {
  background: var(--client-color-400, #f7c62f);
  border-color: var(--client-color-400, #f7c62f);
  color: #fff;
}
.nc4__expand-btn svg { transition: transform .25s; }
.nc4__expand-btn[aria-expanded="true"] svg { transform: rotate(180deg); }
.nc4__expand-btn[aria-expanded="true"] {
  background: var(--client-color-50, #fefce8);
  border-color: var(--client-color-300, #fde047);
  color: var(--text, #0f172a);
}

/* Expandable detail */
.nc4__detail {
  font-size: .875rem;
  color: var(--text-secondary, #475569);
  line-height: 1.75;
  padding: 0 16px 18px 120px;
  border-top: 1px solid var(--border, #f1f5f9);
}
.nc4__detail { font-size: .875rem; }
.nc4__detail h2, .nc4__detail h3 { font-weight: 700; color: var(--text-primary); margin: 14px 0 6px; }
.nc4__detail p  { margin: 0 0 .6em; }

@media (max-width: 500px) {
  .nc4__detail { padding-left: 16px; }
  .nc4__thumb { width: 70px; height: 52px; }
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
  function nc4Toggle(btn) {
    var panel = document.getElementById(btn.getAttribute('aria-controls'));
    if (!panel) return;
    var open = btn.getAttribute('aria-expanded') === 'true';
    btn.setAttribute('aria-expanded', String(!open));
    open ? panel.setAttribute('hidden', '') : panel.removeAttribute('hidden');
  }

  document.querySelectorAll('.nc4').forEach(function(card){
    card.style.cursor = 'pointer';
    card.addEventListener('click', function(e){
      if (e.target.closest('.nc4__link-btn')) return;  // let external link work normally
      if (e.target.closest('.nc4__detail')) return;     // allow selecting text inside expanded content
      var btn = card.querySelector('.nc4__expand-btn');
      if (btn) nc4Toggle(btn);
    });
  });
})();
</script>
