<?php
defined( 'ABSPATH' ) || exit;

$saved = isset( $_GET['saved'] );

// ── Load current values ───────────────────────────────────────────────────────
$contact   = ah_get_contact_settings();
$properties = get_option( 'ah_featured_properties', [] );
if ( is_string( $properties ) ) $properties = json_decode( $properties, true ) ?: [];

$html_blocks_raw = get_option( 'ah_html_blocks', [] );
if ( is_string( $html_blocks_raw ) ) $html_blocks_raw = json_decode( $html_blocks_raw, true ) ?: [];

$featured_posts_opt = get_option( 'ah_featured_post_ids', '' );
$trust_signals = ah_get_trust_signals();

// Blog post suggestions for picker
$all_posts = get_posts( [ 'numberposts' => 50, 'post_status' => 'publish' ] );

// Static pages
$static_pages      = ah_get_static_pages();
$static_quick_opt  = get_option( 'ah_static_quick_links', '' );
$static_page_sugg  = array_map( fn( $p ) => [ 'id' => $p['slug'], 'label' => $p['label'] ], $static_pages );
$post_suggestions = array_map( function( $p ) {
	return [ 'id' => (string) $p->ID, 'label' => $p->post_title ];
}, $all_posts );

?>
<div class="wrap ah-admin-wrap">

  <div class="ah-admin-header">
    <div class="ah-admin-logo">✦</div>
    <div>
      <h1>Content Controls</h1>
      <p>Manage featured posts, news bar, properties, HTML blocks, and contact form settings.</p>
    </div>
  </div>

  <?php if ( $saved ) : ?>
  <div class="ah-admin-notice ah-admin-notice--success">✓ Content settings saved.</div>
  <?php endif; ?>

  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php wp_nonce_field( 'ah_theme_content' ); ?>
    <input type="hidden" name="action" value="ah_theme_content">

    <!-- ── Featured Blog Posts ───────────────────────────────────────────── -->
    <div class="ah-admin-box" style="margin-bottom:20px">
      <h2>📝 Featured Blog Posts</h2>
      <p style="color:#64748b;font-size:.875rem;margin-bottom:16px">
        Select posts to highlight with a gold card on the home page.
        Leave empty to auto-highlight the newest post.
      </p>
      <div class="ah-picker-wrap">
        <div class="ah-tag-picker" data-picker="featured_post_ids"
             data-suggestions='<?php echo esc_attr( wp_json_encode( $post_suggestions ) ); ?>'>
          <input type="text" class="ah-tag-picker__input" placeholder="Type a post title…" autocomplete="off">
        </div>
        <div class="ah-suggestions" style="display:none"></div>
      </div>
      <input type="hidden" name="featured_post_ids" class="ah-picker-value"
             value="<?php echo esc_attr( $featured_posts_opt ); ?>">
      <p style="font-size:.78rem;color:#94a3b8;margin-top:6px">
        Currently: <code><?php echo esc_html( $featured_posts_opt ?: '(auto - newest post)' ); ?></code>
      </p>
    </div>

    <!-- ── Trust Bar Signals ─────────────────────────────────────────────── -->
    <div class="ah-admin-box" style="margin-bottom:20px">
      <h2>⭐ Trust Bar (Auto-Scrolling Strip)</h2>
      <p style="color:#64748b;font-size:.875rem;margin-bottom:16px">
        Items that scroll across the trust bar below the hero. Format: <code>icon | text</code> - use any emoji as the icon.
      </p>
      <div id="trust-rows">
        <?php foreach ( $trust_signals as $i => $sig ) :
          $sig = is_array($sig) ? $sig : (array) $sig;
        ?>
        <div class="ah-nav-row">
          <input type="text" name="trust_signals[<?php echo $i; ?>][icon]" value="<?php echo esc_attr( $sig['icon'] ?? '' ); ?>" placeholder="⭐" style="width:60px" title="Emoji icon">
          <input type="text" name="trust_signals[<?php echo $i; ?>][text]" value="<?php echo esc_attr( $sig['text'] ?? '' ); ?>" placeholder="Trust signal text" style="flex:1;min-width:300px">
          <button type="button" class="button ah-nav-remove-row">✕</button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="button" id="add-trust-row" style="margin-top:10px">+ Add Signal</button>
    </div>

    <!-- ── Featured Properties ───────────────────────────────────────────── -->
    <div class="ah-admin-box" style="margin-bottom:20px">
      <h2>🏡 Property Showcase</h2>
      <p style="color:#64748b;font-size:.875rem;margin-bottom:16px">
        Properties shown in the 3D carousel on the home page. Edit each row.
      </p>
      <div id="prop-rows">
        <?php foreach ( $properties as $i => $prop ) :
          $prop = is_array($prop) ? $prop : (array) $prop;
        ?>
        <div class="ah-nav-row" style="flex-wrap:wrap;gap:8px">
          <input type="text" name="properties[<?php echo $i; ?>][emoji]"    value="<?php echo esc_attr( $prop['emoji']    ?? '' ); ?>" placeholder="🏡" style="width:55px" title="Emoji">
          <input type="text" name="properties[<?php echo $i; ?>][price]"    value="<?php echo esc_attr( $prop['price']    ?? '' ); ?>" placeholder="£850k" style="width:90px">
          <input type="text" name="properties[<?php echo $i; ?>][location]" value="<?php echo esc_attr( $prop['location'] ?? '' ); ?>" placeholder="Richmond" style="width:140px">
          <input type="text" name="properties[<?php echo $i; ?>][area]"     value="<?php echo esc_attr( $prop['area']     ?? '' ); ?>" placeholder="South West London" style="width:180px">
          <input type="text" name="properties[<?php echo $i; ?>][saved]"    value="<?php echo esc_attr( $prop['saved']    ?? '' ); ?>" placeholder="Saved £20k" style="width:110px">
          <input type="text" name="properties[<?php echo $i; ?>][type]"     value="<?php echo esc_attr( $prop['type']     ?? '' ); ?>" placeholder="Detached" style="width:110px">
          <input type="number" name="properties[<?php echo $i; ?>][beds]"   value="<?php echo esc_attr( $prop['beds']     ?? '' ); ?>" placeholder="Beds" style="width:60px" min="1" max="20">
          <input type="text" name="properties[<?php echo $i; ?>][result]"   value="<?php echo esc_attr( $prop['result']   ?? '' ); ?>" placeholder="Result summary" style="width:220px">
          <button type="button" class="button ah-nav-remove-row">✕</button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="button" id="add-prop-row" style="margin-top:10px">+ Add Property</button>
    </div>

    <!-- ── Custom HTML Blocks ────────────────────────────────────────────── -->
    <div class="ah-admin-box" style="margin-bottom:20px">
      <h2>🧩 Custom HTML Blocks</h2>
      <p style="color:#64748b;font-size:.875rem;margin-bottom:16px">
        Insert custom HTML into specific zones on the site. Use any HTML - scripts, embeds, banners.
      </p>
      <?php
      $block_defs = [
          'above_footer'  => [ 'label' => 'Above Footer',  'desc' => 'Shown on every page just above the footer' ],
          'below_hero'    => [ 'label' => 'Below Hero',    'desc' => 'Inserted after the home page hero section' ],
          'global_banner' => [ 'label' => 'Global Banner', 'desc' => 'Full-width bar shown at the very top of every page (above nav)' ],
      ];
      foreach ( $block_defs as $bkey => $bdef ) : ?>
      <div style="margin-bottom:20px">
        <label style="display:block;font-weight:600;font-size:.9rem;margin-bottom:4px">
          <?php echo esc_html( $bdef['label'] ); ?>
          <span style="font-weight:400;color:#94a3b8;font-size:.8rem;margin-left:6px"><?php echo esc_html( $bdef['desc'] ); ?></span>
        </label>
        <textarea name="html_block[<?php echo esc_attr( $bkey ); ?>]" rows="4"
                  style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:10px;font-size:.8rem;font-family:monospace"><?php echo esc_textarea( $html_blocks_raw[ $bkey ] ?? '' ); ?></textarea>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── Static Pages Manager ─────────────────────────────────────────── -->
    <div class="ah-admin-box" style="margin-bottom:20px">
      <h2>📄 Static Pages</h2>
      <p style="color:#64748b;font-size:.875rem;margin-bottom:16px">
        Static HTML pages stored in <code>static/</code> inside the theme.
        Edit their content at <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-static-pages' ) ); ?>" target="_blank">CMS → Static HTML Pages ↗</a>.
        Use the picker below to select pages that appear as <strong>Quick Links</strong> in the navigation and footer.
      </p>

      <?php if ( empty( $static_pages ) ) : ?>
        <div class="ah-admin-notice ah-admin-notice--warn">
          No static pages found yet. Run <strong>Install Mock Data</strong> to seed 7 demo pages (stamp duty calculator, mortgage calculator, glossary, and more).
        </div>
      <?php else : ?>
        <table class="ah-admin-table" style="margin-bottom:16px">
          <thead><tr><th>Page</th><th>Slug</th><th>WP Page</th><th>URL</th></tr></thead>
          <tbody>
            <?php foreach ( $static_pages as $sp ) : ?>
            <tr>
              <td style="font-weight:600"><?php echo esc_html( $sp['label'] ); ?></td>
              <td><code><?php echo esc_html( $sp['slug'] ); ?></code></td>
              <td>
                <?php if ( $sp['has_wp_page'] ) : ?>
                  <span class="ah-badge ah-badge--ok">✓ Created</span>
                <?php else : ?>
                  <span class="ah-badge ah-badge--warn">Missing</span>
                <?php endif; ?>
              </td>
              <td><a href="<?php echo esc_url( $sp['url'] ); ?>" target="_blank" style="font-size:.8rem;color:#b7791f"><?php echo esc_html( $sp['url'] ); ?></a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <label style="font-weight:600;font-size:.875rem;display:block;margin-bottom:8px">Quick Links (shown in nav dropdowns and footer)</label>
      <div class="ah-picker-wrap">
        <div class="ah-tag-picker" data-picker="static_quick_links"
             data-suggestions='<?php echo esc_attr( wp_json_encode( $static_page_sugg ) ); ?>'>
          <input type="text" class="ah-tag-picker__input" placeholder="Type a page name…" autocomplete="off">
        </div>
        <div class="ah-suggestions" style="display:none"></div>
      </div>
      <input type="hidden" name="static_quick_links" class="ah-picker-value"
             value="<?php echo esc_attr( is_string( $static_quick_opt ) ? $static_quick_opt : '' ); ?>">
      <p style="font-size:.78rem;color:#94a3b8;margin-top:6px">
        Currently: <code><?php echo esc_html( ( is_string( $static_quick_opt ) && $static_quick_opt ) ? $static_quick_opt : '(none selected)' ); ?></code>
      </p>
    </div>

    <!-- ── Contact Form Settings ─────────────────────────────────────────── -->
    <div class="ah-admin-box" style="margin-bottom:20px">
      <h2>📬 Contact Form Settings</h2>
      <table class="ah-admin-table">
        <tbody>
          <tr>
            <th style="width:200px">Recipient Email</th>
            <td><input type="email" name="contact[recipient_email]" value="<?php echo esc_attr( $contact['recipient_email'] ?? '' ); ?>" class="regular-text"></td>
          </tr>
          <tr>
            <th>Email Subject Prefix</th>
            <td><input type="text" name="contact[subject_prefix]" value="<?php echo esc_attr( $contact['subject_prefix'] ?? '' ); ?>" class="regular-text" placeholder="[Advaith Homes Enquiry]"></td>
          </tr>
          <tr>
            <th>Thank-You Message</th>
            <td><textarea name="contact[thank_you_msg]" rows="3" style="width:100%;border:1px solid #e2e8f0;border-radius:6px;padding:8px"><?php echo esc_textarea( $contact['thank_you_msg'] ?? '' ); ?></textarea></td>
          </tr>
          <tr>
            <th>Show Phone Field</th>
            <td>
              <label class="ah-toggle">
                <input type="checkbox" name="contact[show_phone]" value="1" <?php checked( ! empty( $contact['show_phone'] ) ); ?>>
                <span class="ah-toggle__track"></span>
              </label>
            </td>
          </tr>
          <tr>
            <th>Show Budget Field</th>
            <td>
              <label class="ah-toggle">
                <input type="checkbox" name="contact[show_budget]" value="1" <?php checked( ! empty( $contact['show_budget'] ) ); ?>>
                <span class="ah-toggle__track"></span>
              </label>
            </td>
          </tr>
          <tr>
            <th>Show Timeline Field</th>
            <td>
              <label class="ah-toggle">
                <input type="checkbox" name="contact[show_timeline]" value="1" <?php checked( ! empty( $contact['show_timeline'] ) ); ?>>
                <span class="ah-toggle__track"></span>
              </label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p class="submit" style="margin-top:0">
      <?php submit_button( 'Save Content Settings', 'primary', 'submit', false ); ?>
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="button" style="margin-left:8px">View Site →</a>
    </p>
  </form>
</div>

<style>
.ah-nav-row { display:flex; align-items:center; gap:8px; padding:8px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:8px; }
.ah-nav-row input[type="text"],.ah-nav-row input[type="number"] { border:1.5px solid #e2e8f0; border-radius:6px; padding:5px 8px; font-size:.85rem; }
.ah-nav-row input:focus { border-color:#b7791f; outline:none; }
.ah-nav-remove-row { color:#dc2626; border-color:#fca5a5; }
</style>

<script>
(function() {
  'use strict';

  // Remove property row
  document.addEventListener('click', function(e) {
    if (e.target.matches('.ah-nav-remove-row')) {
      e.target.closest('.ah-nav-row').remove();
    }
  });

  // Add property row
  document.getElementById('add-prop-row').addEventListener('click', function() {
    var rows   = document.getElementById('prop-rows');
    var count  = rows.querySelectorAll('.ah-nav-row').length;
    var row    = document.createElement('div');
    row.className = 'ah-nav-row';
    var fields = [
      {n:'emoji',    w:'55px',  p:'🏡'},
      {n:'price',    w:'90px',  p:'£850k'},
      {n:'location', w:'140px', p:'Richmond'},
      {n:'area',     w:'180px', p:'South West London'},
      {n:'saved',    w:'110px', p:'Saved £20k'},
      {n:'type',     w:'110px', p:'Detached'},
      {n:'result',   w:'220px', p:'Result summary'},
    ];
    row.innerHTML = fields.map(function(f) {
      return '<input type="text" name="properties[' + count + '][' + f.n + ']" placeholder="' + f.p + '" style="width:' + f.w + '">';
    }).join('') +
      '<input type="number" name="properties[' + count + '][beds]" placeholder="Beds" style="width:60px" min="1" max="20">' +
      '<button type="button" class="button ah-nav-remove-row">✕</button>';
    rows.appendChild(row);
  });

  // Tag picker for featured posts (same pattern as sections page)
  document.querySelectorAll('.ah-tag-picker').forEach(function(picker) {
    var pickerName  = picker.getAttribute('data-picker');
    var suggestions = JSON.parse(picker.getAttribute('data-suggestions') || '[]');
    var input       = picker.querySelector('.ah-tag-picker__input');
    var dropdown    = picker.parentElement.querySelector('.ah-suggestions');
    var hiddenInput = picker.parentElement.parentElement.querySelector('.ah-picker-value[name="' + pickerName + '"]');
    var selected    = [];
    var focusedIdx  = -1;

    var existing = (hiddenInput.value || '').split(',').filter(Boolean);
    existing.forEach(function(id) {
      var match = suggestions.find(function(s) { return s.id === id; });
      if (match) addTag(match);
    });

    function renderDropdown(q) {
      q = q.toLowerCase().trim();
      if (!q) { dropdown.style.display='none'; return; }
      var results = suggestions.filter(function(s) {
        return s.label.toLowerCase().includes(q) && !selected.find(function(t) { return t.id===s.id; });
      }).slice(0,8);
      if (!results.length) { dropdown.style.display='none'; return; }
      dropdown.innerHTML = results.map(function(s,i) {
        var hi = s.label.replace(new RegExp('('+q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+')','gi'),'<mark>$1</mark>');
        return '<div class="ah-suggestion-item" data-id="'+s.id+'" data-label="'+s.label.replace(/"/g,'&quot;')+'">'+hi+'</div>';
      }).join('');
      dropdown.style.display='block'; focusedIdx=-1;
      dropdown.querySelectorAll('.ah-suggestion-item').forEach(function(item) {
        item.addEventListener('mousedown', function(e) {
          e.preventDefault();
          addTag({id:item.dataset.id,label:item.dataset.label});
          input.value=''; dropdown.style.display='none';
        });
      });
    }
    function addTag(item) {
      if (selected.find(function(t){return t.id===item.id;})) return;
      selected.push(item);
      var tag=document.createElement('span'); tag.className='ah-tag-picker__tag';
      tag.innerHTML='<span>'+item.label+'</span><button type="button" aria-label="Remove">×</button>';
      tag.querySelector('button').addEventListener('click',function(){
        selected=selected.filter(function(t){return t.id!==item.id;});
        tag.remove(); sync();
      });
      picker.insertBefore(tag,input); sync();
    }
    function sync() { hiddenInput.value=selected.map(function(t){return t.id;}).join(','); }

    input.addEventListener('input',function(){renderDropdown(input.value);});
    input.addEventListener('keydown',function(e){
      var items=dropdown.querySelectorAll('.ah-suggestion-item');
      if (e.key==='ArrowDown'){e.preventDefault();focusedIdx=Math.min(focusedIdx+1,items.length-1);}
      else if (e.key==='ArrowUp'){e.preventDefault();focusedIdx=Math.max(focusedIdx-1,0);}
      else if (e.key==='Enter'&&focusedIdx>=0){
        e.preventDefault();var item=items[focusedIdx];
        if(item){addTag({id:item.dataset.id,label:item.dataset.label});input.value='';dropdown.style.display='none';}
        return;
      } else if (e.key==='Backspace'&&!input.value&&selected.length){
        var last=picker.querySelectorAll('.ah-tag-picker__tag');
        if(last.length){var rem=selected[selected.length-1];selected=selected.filter(function(t){return t.id!==rem.id;});last[last.length-1].remove();sync();}
        return;
      }
      items.forEach(function(it,i){it.classList.toggle('is-focused',i===focusedIdx);});
    });
    document.addEventListener('click',function(e){
      if(!picker.contains(e.target)&&!dropdown.contains(e.target)) dropdown.style.display='none';
    });
    picker.addEventListener('click',function(){input.focus();});
  });

  // Add trust signal row
  document.getElementById('add-trust-row').addEventListener('click', function() {
    var rows  = document.getElementById('trust-rows');
    var count = rows.querySelectorAll('.ah-nav-row').length;
    var row   = document.createElement('div');
    row.className = 'ah-nav-row';
    row.innerHTML =
      '<input type="text" name="trust_signals[' + count + '][icon]" placeholder="⭐" style="width:60px" title="Emoji icon">' +
      '<input type="text" name="trust_signals[' + count + '][text]" placeholder="Trust signal text" style="flex:1;min-width:300px">' +
      '<button type="button" class="button ah-nav-remove-row">✕</button>';
    rows.appendChild(row);
  });
})();
</script>
