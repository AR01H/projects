<?php
defined( 'ABSPATH' ) || exit;

$saved = isset( $_GET['saved'] );

// Current visibility settings
$raw_vis = get_option( 'ah_section_visibility', [] );
if ( is_string( $raw_vis ) ) $raw_vis = json_decode( $raw_vis, true ) ?: [];
$vis = (array) $raw_vis;
$get_vis = function( string $key ) use ( $vis ): bool {
	return isset( $vis[ $key ] ) ? (bool) $vis[ $key ] : true; // default ON
};

// Suggestion data for tag-picker - loaded from helpers
$all_services = ah_get_services( 50 );
$all_faqs     = ah_get_faqs( 50 );

$service_suggestions = array_map( function( $s ) {
	return [ 'id' => (string) ( $s->id ?? $s->ID ?? '' ), 'label' => $s->title ?? '' ];
}, $all_services );

$faq_suggestions = array_map( function( $f ) {
	return [ 'id' => (string) ( $f->id ?? $f->ID ?? '' ), 'label' => $f->question ?? '' ];
}, $all_faqs );

// Define all sections
$section_groups = [
	'Global' => [
		[ 'key' => 'global_news_ticker', 'icon' => '📰', 'name' => 'News Ticker',  'desc' => 'Scrolling headline bar at the top of every page' ],
		[ 'key' => 'global_trust_bar',   'icon' => '⭐', 'name' => 'Trust Bar',    'desc' => 'Five trust signals shown below the hero' ],
	],
	'Home Page' => [
		[ 'key' => 'home_hero',          'icon' => '🏠', 'name' => 'Hero Section',         'desc' => 'Main headline, CTA, and hero image' ],
		[ 'key' => 'home_guide_cards',   'icon' => '📚', 'name' => 'Guide Cards',          'desc' => '4-column guide category cards' ],
		[ 'key' => 'home_process',       'icon' => '📋', 'name' => 'How It Works',         'desc' => 'Step-by-step process cards (01-06)' ],
		[ 'key' => 'home_services',      'icon' => '✦',  'name' => 'Services Section',     'desc' => 'Service cards pulled from DB or mock' ],
		[ 'key' => 'home_properties',    'icon' => '🏡', 'name' => 'Property Showcase',    'desc' => '3D carousel of recently secured properties' ],
		[ 'key' => 'home_team',          'icon' => '👥', 'name' => 'Team Section',         'desc' => 'Team member cards' ],
		[ 'key' => 'home_faq',           'icon' => '❓', 'name' => 'FAQ Section',          'desc' => 'FAQ accordion (up to 6 questions)' ],
		[ 'key' => 'home_blog',          'icon' => '✏️', 'name' => 'Latest Blog Posts',    'desc' => 'Three most recent posts with gold highlight' ],
		[ 'key' => 'home_cta',           'icon' => '🎯', 'name' => 'CTA Section',          'desc' => 'Dark full-width call-to-action banner' ],
	],
];
?>
<div class="wrap ah-admin-wrap">

  <!-- Header -->
  <div class="ah-admin-header">
    <div class="ah-admin-logo">✦</div>
    <div>
      <h1>Section Controls</h1>
      <p>Toggle which sections appear on the front page. Changes take effect immediately after saving.</p>
    </div>
  </div>

  <?php if ( $saved ) : ?>
  <div class="ah-admin-notice ah-admin-notice--success">
    ✓ Section settings saved successfully.
  </div>
  <?php endif; ?>

  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php wp_nonce_field( 'ah_theme_sections' ); ?>
    <input type="hidden" name="action" value="ah_theme_sections">

    <?php foreach ( $section_groups as $group_name => $sections ) : ?>
    <div class="ah-admin-box" style="margin-bottom:20px">
      <h2><?php echo esc_html( $group_name ); ?> Sections</h2>
      <div class="ah-section-grid">
        <?php foreach ( $sections as $sec ) : ?>
        <div class="ah-section-row">
          <div class="ah-section-row__left">
            <span class="ah-section-row__icon"><?php echo esc_html( $sec['icon'] ); ?></span>
            <div>
              <div class="ah-section-row__name"><?php echo esc_html( $sec['name'] ); ?></div>
              <div class="ah-section-row__desc"><?php echo esc_html( $sec['desc'] ); ?></div>
            </div>
          </div>
          <label class="ah-toggle" title="Toggle.<?php echo esc_attr( $sec['name'] ); ?>">
            <input type="checkbox"
                   name="section_<?php echo esc_attr( $sec['key'] ); ?>"
                   value="1"
                   <?php checked( $get_vis( $sec['key'] ), true ); ?>>
            <span class="ah-toggle__track"></span>
          </label>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <p class="submit" style="margin-top:0">
      <?php submit_button( 'Save Section Settings', 'primary', 'submit', false ); ?>
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"
         class="button" style="margin-left:8px">View Site →</a>
    </p>
  </form>
</div>

<script>
(function() {
  'use strict';

  document.querySelectorAll('.ah-tag-picker').forEach(function(picker) {
    var pickerName = picker.getAttribute('data-picker');
    var suggestions = JSON.parse(picker.getAttribute('data-suggestions') || '[]');
    var input       = picker.querySelector('.ah-tag-picker__input');
    var dropdown    = picker.parentElement.querySelector('.ah-suggestions');
    var hiddenInput = picker.parentElement.parentElement.querySelector('.ah-picker-value[name="' + pickerName + '"]');

    var selected = [];
    // Init from existing hidden value
    var existing = (hiddenInput.value || '').split(',').filter(Boolean);
    existing.forEach(function(id) {
      var match = suggestions.find(function(s) { return s.id === id; });
      if (match) addTag(match);
    });

    var focusedIdx = -1;

    function renderDropdown(query) {
      var q = query.toLowerCase().trim();
      if (!q) { dropdown.style.display = 'none'; return; }

      var results = suggestions.filter(function(s) {
        return s.label.toLowerCase().includes(q) && !selected.find(function(t) { return t.id === s.id; });
      }).slice(0, 8);

      if (!results.length) { dropdown.style.display = 'none'; return; }

      dropdown.innerHTML = results.map(function(s, i) {
        var hi = s.label.replace(new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi'), '<mark>$1</mark>');
        return '<div class="ah-suggestion-item" data-id="' + s.id + '" data-label="' + s.label.replace(/"/g, '&quot;') + '">' + hi + '</div>';
      }).join('');
      dropdown.style.display = 'block';
      focusedIdx = -1;

      dropdown.querySelectorAll('.ah-suggestion-item').forEach(function(item) {
        item.addEventListener('mousedown', function(e) {
          e.preventDefault();
          addTag({ id: item.getAttribute('data-id'), label: item.getAttribute('data-label') });
          input.value = '';
          dropdown.style.display = 'none';
        });
      });
    }

    function addTag(item) {
      if (selected.find(function(t) { return t.id === item.id; })) return;
      selected.push(item);
      var tag = document.createElement('span');
      tag.className = 'ah-tag-picker__tag';
      tag.innerHTML = '<span>' + item.label + '</span><button type="button" aria-label="<?php echo esc_attr( TXT_REMOVE ); ?>">×</button>';
      tag.querySelector('button').addEventListener('click', function() {
        selected = selected.filter(function(t) { return t.id !== item.id; });
        tag.remove();
        syncHidden();
      });
      picker.insertBefore(tag, input);
      syncHidden();
    }

    function syncHidden() {
      hiddenInput.value = selected.map(function(t) { return t.id; }).join(',');
    }

    input.addEventListener('input', function() { renderDropdown(input.value); });

    input.addEventListener('keydown', function(e) {
      var items = dropdown.querySelectorAll('.ah-suggestion-item');
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        focusedIdx = Math.min(focusedIdx + 1, items.length - 1);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        focusedIdx = Math.max(focusedIdx - 1, 0);
      } else if (e.key === 'Enter' && focusedIdx >= 0) {
        e.preventDefault();
        var item = items[focusedIdx];
        if (item) {
          addTag({ id: item.getAttribute('data-id'), label: item.getAttribute('data-label') });
          input.value = '';
          dropdown.style.display = 'none';
        }
        return;
      } else if (e.key === 'Backspace' && !input.value && selected.length) {
        var last = picker.querySelectorAll('.ah-tag-picker__tag');
        if (last.length) {
          var rem = selected[selected.length - 1];
          selected = selected.filter(function(t) { return t.id !== rem.id; });
          last[last.length - 1].remove();
          syncHidden();
        }
        return;
      }
      items.forEach(function(it, i) { it.classList.toggle('is-focused', i === focusedIdx); });
    });

    document.addEventListener('click', function(e) {
      if (!picker.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
      }
    });

    picker.addEventListener('click', function() { input.focus(); });
  });
})();
</script>
