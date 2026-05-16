<?php
defined( 'ABSPATH' ) || exit;

$saved = isset( $_GET['saved'] );

// Load current values
$nav_vis   = ah_get_nav_visibility();
$nav_links = ah_get_nav_static_links();
$nav_cta   = ah_get_nav_cta();

$buying  = ah_get_nav_buying_topics();
$finance = ah_get_nav_finance_topics();
$legal   = ah_get_nav_legal_topics();

$top_items = [
	'buying'   => [ 'label' => 'Buying',          'icon' => '🏠', 'has_dropdown' => true  ],
	'finance'  => [ 'label' => 'Finance',          'icon' => '🏦', 'has_dropdown' => true  ],
	'legal'    => [ 'label' => 'Legal & Surveys',  'icon' => '⚖️', 'has_dropdown' => true  ],
	'news'     => [ 'label' => 'News & Guides',    'icon' => '📰', 'has_dropdown' => false ],
	'services' => [ 'label' => 'Services',         'icon' => '✦',  'has_dropdown' => false ],
];

$dropdown_groups = [
	'buying'  => [ 'label' => 'Buying Dropdown Links',  'items' => $buying  ],
	'finance' => [ 'label' => 'Finance Dropdown Links', 'items' => $finance ],
	'legal'   => [ 'label' => 'Legal Dropdown Links',   'items' => $legal   ],
];
?>
<div class="wrap ah-admin-wrap">

  <div class="ah-admin-header">
    <div class="ah-admin-logo">✦</div>
    <div>
      <h1>Navigation Manager</h1>
      <p>Control every nav item from here — no WordPress menus needed.</p>
    </div>
  </div>

  <?php if ( $saved ) : ?>
  <div class="ah-admin-notice ah-admin-notice--success">✓ Navigation settings saved.</div>
  <?php endif; ?>

  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php wp_nonce_field( 'ah_theme_nav' ); ?>
    <input type="hidden" name="action" value="ah_theme_nav">

    <!-- ── Top-level visibility ─────────────────────────────────────────────── -->
    <div class="ah-admin-box" style="margin-bottom:20px">
      <h2>Top-Level Menu Items</h2>
      <p style="color:#64748b;font-size:.875rem;margin-bottom:16px">Toggle which items appear in the main navigation bar.</p>
      <div class="ah-section-grid">
        <?php foreach ( $top_items as $key => $item ) : ?>
        <div class="ah-section-row">
          <div class="ah-section-row__left">
            <span class="ah-section-row__icon"><?php echo $item['icon']; ?></span>
            <div>
              <div class="ah-section-row__name"><?php echo esc_html( $item['label'] ); ?></div>
              <div class="ah-section-row__desc"><?php echo $item['has_dropdown'] ? 'Has dropdown' : 'Direct link'; ?></div>
            </div>
          </div>
          <label class="ah-toggle" title="Toggle <?php echo esc_attr( $item['label'] ); ?>">
            <input type="checkbox" name="nav_vis[<?php echo esc_attr( $key ); ?>]" value="1"
                   <?php checked( ! empty( $nav_vis[ $key ] ), true ); ?>>
            <span class="ah-toggle__track"></span>
          </label>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ── Static link URLs ─────────────────────────────────────────────────── -->
    <div class="ah-admin-box" style="margin-bottom:20px">
      <h2>Static Link Settings</h2>
      <p style="color:#64748b;font-size:.875rem;margin-bottom:16px">Label and URL for direct nav links (no dropdown).</p>
      <table class="ah-admin-table">
        <thead><tr><th>Item</th><th>Label</th><th>URL</th></tr></thead>
        <tbody>
          <?php foreach ( [ 'news' => 'News & Guides', 'services' => 'Services' ] as $key => $default_label ) :
            $link = $nav_links[ $key ] ?? [];
          ?>
          <tr>
            <td style="font-weight:600"><?php echo esc_html( $default_label ); ?></td>
            <td>
              <input type="text" name="nav_link[<?php echo esc_attr( $key ); ?>][label]"
                     value="<?php echo esc_attr( $link['label'] ?? $default_label ); ?>"
                     class="regular-text" style="width:180px">
            </td>
            <td>
              <input type="text" name="nav_link[<?php echo esc_attr( $key ); ?>][url]"
                     value="<?php echo esc_attr( $link['url'] ?? '' ); ?>"
                     class="regular-text" style="width:220px" placeholder="/blog/">
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- ── CTA Button ───────────────────────────────────────────────────────── -->
    <div class="ah-admin-box" style="margin-bottom:20px">
      <h2>CTA Button (top-right)</h2>
      <table class="ah-admin-table">
        <thead><tr><th>Label</th><th>URL</th></tr></thead>
        <tbody>
          <tr>
            <td><input type="text" name="nav_cta_label" value="<?php echo esc_attr( $nav_cta['label'] ?? 'Get Help' ); ?>" class="regular-text"></td>
            <td><input type="text" name="nav_cta_url"   value="<?php echo esc_attr( $nav_cta['url'] ?? '/contact/' ); ?>" class="regular-text"></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ── Dropdown link editors ────────────────────────────────────────────── -->
    <?php foreach ( $dropdown_groups as $gkey => $group ) : ?>
    <div class="ah-admin-box" style="margin-bottom:20px">
      <h2><?php echo esc_html( $group['label'] ); ?></h2>
      <p style="color:#64748b;font-size:.875rem;margin-bottom:16px">
        Each row is a link in the <strong><?php echo esc_html( ucfirst( $gkey ) ); ?></strong> dropdown.
        Slug becomes the URL: <code>/guides/{slug}/</code>
      </p>
      <div class="ah-nav-rows" id="nav-rows-<?php echo esc_attr( $gkey ); ?>">
        <?php foreach ( $group['items'] as $i => $item ) :
          $item = is_array( $item ) ? $item : (array) $item;
        ?>
        <div class="ah-nav-row" data-idx="<?php echo $i; ?>">
          <input type="text"     name="<?php echo esc_attr( $gkey ); ?>_items[<?php echo $i; ?>][icon]"  value="<?php echo esc_attr( $item['icon']  ?? '' ); ?>" placeholder="🏠"  style="width:60px" title="Icon (emoji)">
          <input type="text"     name="<?php echo esc_attr( $gkey ); ?>_items[<?php echo $i; ?>][title]" value="<?php echo esc_attr( $item['title'] ?? '' ); ?>" placeholder="Title" style="width:200px">
          <input type="text"     name="<?php echo esc_attr( $gkey ); ?>_items[<?php echo $i; ?>][desc]"  value="<?php echo esc_attr( $item['desc']  ?? '' ); ?>" placeholder="Short description" style="width:230px">
          <input type="text"     name="<?php echo esc_attr( $gkey ); ?>_items[<?php echo $i; ?>][slug]"  value="<?php echo esc_attr( $item['slug']  ?? '' ); ?>" placeholder="url-slug" style="width:150px">
          <label title="Highlight this item">
            <input type="checkbox" name="<?php echo esc_attr( $gkey ); ?>_items[<?php echo $i; ?>][highlight]" value="1" <?php checked( ! empty( $item['highlight'] ) ); ?>>
            ⭐ Featured
          </label>
          <button type="button" class="button ah-nav-remove-row">✕</button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="button ah-nav-add-row" data-target="nav-rows-<?php echo esc_attr( $gkey ); ?>" data-section="<?php echo esc_attr( $gkey ); ?>" style="margin-top:10px">+ Add Link</button>
    </div>
    <?php endforeach; ?>

    <p class="submit" style="margin-top:0">
      <?php submit_button( 'Save Navigation', 'primary', 'submit', false ); ?>
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="button" style="margin-left:8px">View Site →</a>
    </p>
  </form>
</div>

<style>
.ah-nav-rows { display:flex; flex-direction:column; gap:8px; }
.ah-nav-row  { display:flex; align-items:center; gap:8px; flex-wrap:wrap; padding:8px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; }
.ah-nav-row input[type="text"] { border:1.5px solid #e2e8f0; border-radius:6px; padding:5px 8px; font-size:.85rem; }
.ah-nav-row input[type="text"]:focus { border-color:#b7791f; outline:none; }
.ah-nav-remove-row { color:#dc2626; border-color:#fca5a5; }
</style>

<script>
(function() {
  'use strict';

  // Remove row
  document.addEventListener('click', function(e) {
    if (e.target.matches('.ah-nav-remove-row')) {
      e.target.closest('.ah-nav-row').remove();
    }
  });

  // Add row
  document.querySelectorAll('.ah-nav-add-row').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var target  = document.getElementById(btn.dataset.target);
      var section = btn.dataset.section;
      var count   = target.querySelectorAll('.ah-nav-row').length;
      var row     = document.createElement('div');
      row.className = 'ah-nav-row';
      row.innerHTML =
        '<input type="text" name="' + section + '_items[' + count + '][icon]"  placeholder="🏠" style="width:60px" title="Icon (emoji)">' +
        '<input type="text" name="' + section + '_items[' + count + '][title]" placeholder="Title" style="width:200px">' +
        '<input type="text" name="' + section + '_items[' + count + '][desc]"  placeholder="Short description" style="width:230px">' +
        '<input type="text" name="' + section + '_items[' + count + '][slug]"  placeholder="url-slug" style="width:150px">' +
        '<label><input type="checkbox" name="' + section + '_items[' + count + '][highlight]" value="1"> ⭐ Featured</label>' +
        '<button type="button" class="button ah-nav-remove-row">✕</button>';
      target.appendChild(row);
    });
  });
})();
</script>
