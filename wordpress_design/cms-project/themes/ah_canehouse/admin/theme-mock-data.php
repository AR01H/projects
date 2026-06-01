<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

require_once get_template_directory() . '/mock_data/seeder.php';

$counts  = CH_Theme_Seeder::table_counts();
$msg     = isset( $_GET['msg'] )  ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : '';
$seeded  = ! empty( $_GET['seeded'] );
$op_type = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : '';

// ── CSV row counts ────────────────────────────────────────────────────────────
$csv_counts = [];
foreach ( [ 'reviews', 'news_bar' ] as $name ) {
	$rows              = CH_Data::load_csv( $name );
	$csv_counts[$name] = count( $rows );
}
?>
<div class="wrap ch-admin-wrap">

  <div class="ch-admin-header" style="display:flex;align-items:center;gap:14px;margin-bottom:20px">
    <div style="font-size:2rem">🌿</div>
    <div>
      <h1 style="margin:0">Mock Data &amp; Schema Installer</h1>
      <p style="margin:4px 0 0;color:#64748b;font-size:.875rem">
        Install the database schema and demo content for The Cane House. Content is driven by CSV files in
        <code>mock_data/csv/</code> - edit CSVs before installing to customise.
      </p>
    </div>
  </div>

  <?php if ( $seeded && $msg ) : ?>
    <div class="notice notice-success is-dismissible" style="margin-bottom:16px">
      <p><?php echo $op_type === 'schema' ? '🏗️ ' : '✅ '; ?><?php echo esc_html( $msg ); ?></p>
    </div>
  <?php endif; ?>

  <!-- ── TWO MAIN ACTION BUTTONS ────────────────────────────────────────── -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">

    <!-- Step 1: Schema Only -->
    <div class="postbox" style="border-top:3px solid #0284c7;margin-bottom:0;padding:16px">
      <h2 style="color:#0284c7;font-size:1.1rem;margin:0 0 8px">🏗️ Step 1 – Install Schema &amp; Settings</h2>
      <p style="font-size:.83rem;color:#64748b;margin-bottom:14px">
        Creates database tables (reviews, FAQs, news bar, contact submissions) and saves baseline site
        settings, hero text, section visibility, and contact config.<br>
        <strong>Safe to run anytime - idempotent.</strong> Does not insert demo content rows.
      </p>
      <ul style="font-size:.8rem;color:#475569;margin:0 0 16px 16px;line-height:1.9">
        <li>DB tables: reviews, FAQs, news_bar, contact submissions</li>
        <li>Site settings (phone, email, tagline)</li>
        <li>Hero, section visibility, contact config</li>
      </ul>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'ch_theme_schema' ); ?>
        <input type="hidden" name="action" value="ch_theme_schema">
        <button type="submit" class="button button-primary"
                style="background:#0284c7;border-color:#0369a1;width:100%;padding:9px;font-size:.9rem"
                onclick="return confirm('Install schema and save baseline settings?')">
          🏗️ Install Schema &amp; Settings
        </button>
      </form>
    </div>

    <!-- Step 2: Mock Data -->
    <div class="postbox" style="border-top:3px solid #b7791f;margin-bottom:0;padding:16px">
      <h2 style="color:#b7791f;font-size:1.1rem;margin:0 0 8px">📦 Step 2 – Install Mock Data</h2>
      <p style="font-size:.83rem;color:#64748b;margin-bottom:14px">
        Seeds demo content from the CSVs in <code>mock_data/csv/</code>.
        Sections with no CSV rows are skipped automatically.
        ⚠️ Running multiple times may duplicate DB rows - use Cleanup first if re-seeding.
      </p>
      <?php
      // Every importable CSV type comes from the central registry in the seeder
      // (CH_Theme_Seeder::importable_types()). To add a new type, edit that one
      // list — this UI updates automatically. Navigation, footer and FAQs are
      // owned by the CMS plugin and are intentionally not listed here.
      $content_items = CH_Theme_Seeder::importable_types();
      ?>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="ch-seed-form">
        <?php wp_nonce_field( 'ch_theme_seed' ); ?>
        <input type="hidden" name="action" value="ch_theme_seed">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
          <span style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.04em">Select data to import</span>
          <span style="font-size:.75rem">
            <a href="#" style="color:#b7791f;text-decoration:none" onclick="document.querySelectorAll('#ch-seed-form input[type=checkbox]').forEach(c=>c.checked=true);return false">All</a>
            &nbsp;/&nbsp;
            <a href="#" style="color:#b7791f;text-decoration:none" onclick="document.querySelectorAll('#ch-seed-form input[type=checkbox]').forEach(c=>c.checked=false);return false">None</a>
          </span>
        </div>

        <ul style="list-style:none;margin:0 0 14px;padding:0;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden">
          <?php foreach ( $content_items as $key => $item ) :
            $rows   = CH_Data::load_csv( $item['csv'] );
            $n      = is_array( $rows ) ? count( $rows ) : 0;
            $append = ! empty( $item['append'] );
            // Overwrite-safe types are pre-ticked; append types (duplicate risk)
            // start unticked so a full "Install Selected" never silently dupes.
            $checked = ( $n > 0 && ! $append ) ? ' checked' : '';
          ?>
            <li style="border-bottom:1px solid #f1f5f9">
              <label style="display:flex;align-items:center;gap:10px;padding:8px 12px;cursor:pointer">
                <input type="checkbox" name="seed_types[]" value="<?php echo esc_attr( $key ); ?>"<?php echo $checked; ?>
                       style="width:15px;height:15px;accent-color:#b7791f;flex-shrink:0">
                <span style="flex:1;font-size:.82rem;color:#374151"><?php echo esc_html( $item['label'] ); ?></span>
                <?php if ( $append ) : ?>
                  <span title="Appends rows - may duplicate if run twice" style="font-size:.68rem;color:#b45309;background:#fef3c7;border-radius:4px;padding:1px 6px;white-space:nowrap">appends</span>
                <?php else : ?>
                  <span title="Overwrites - safe to run again" style="font-size:.68rem;color:#15803d;background:#dcfce7;border-radius:4px;padding:1px 6px;white-space:nowrap">overwrite</span>
                <?php endif; ?>
                <?php if ( $n > 0 ) : ?>
                  <span style="font-size:.75rem;color:#16a34a;white-space:nowrap">✓ <?php echo esc_html( $n ); ?> rows</span>
                <?php else : ?>
                  <span style="font-size:.75rem;color:#dc2626;white-space:nowrap">✗ empty CSV</span>
                <?php endif; ?>
              </label>
            </li>
          <?php endforeach; ?>
        </ul>

        <button type="submit" class="button button-primary"
                style="width:100%;padding:9px;font-size:.9rem"
                onclick="var checked=document.querySelectorAll('#ch-seed-form input[type=checkbox]:checked').length;if(!checked){alert('Select at least one item.');return false;}return confirm('Import selected mock data? Existing data for the selected items will be overwritten.')">
          📦 Install Selected
        </button>
      </form>
    </div>

  </div><!-- /grid -->

  <!-- ── CURRENT STATUS ───────────────────────────────────────────────────── -->
  <div class="postbox" style="padding:16px">
    <h2 style="font-size:1rem;margin:0 0 12px">Current Database Status</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.84rem">
      <thead>
        <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;text-align:left">
          <th style="padding:8px 12px">Content</th>
          <th style="padding:8px 12px">CSV rows</th>
          <th style="padding:8px 12px">DB rows</th>
          <th style="padding:8px 12px">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $status_rows = [
          [ 'label' => 'Reviews',              'csv_key' => 'reviews',  'db_key' => 'reviews'              ],
          [ 'label' => 'News Bar Items',        'csv_key' => 'news_bar', 'db_key' => 'news_bar'             ],
          [ 'label' => 'Contact Submissions',   'csv_key' => null,       'db_key' => 'contact_submissions'  ],
          [ 'label' => 'Journal Posts (WP)',    'csv_key' => null,       'db_key' => null                   ],
        ];
        foreach ( $status_rows as $r ) :
          $csv_n  = $r['csv_key'] ? ( $csv_counts[ $r['csv_key'] ] ?? '-' ) : '-';
          $db_val = $r['db_key']  ? ( $counts[ $r['db_key'] ] ?? null ) : null;
          if ( $r['label'] === 'Journal Posts (WP)' ) $db_val = (int) wp_count_posts()->publish;
          $has    = $db_val !== null && $db_val > 0;
          $color  = $has ? '#16a34a' : ( $db_val === null ? '#d97706' : '#dc2626' );
          $badge  = $has ? '✓ Has data' : ( $db_val === null ? 'Table missing' : 'Empty' );
        ?>
        <tr style="border-bottom:1px solid #f1f5f9">
          <td style="padding:7px 12px;font-weight:600"><?php echo esc_html( $r['label'] ); ?></td>
          <td style="padding:7px 12px;color:#64748b"><?php echo is_int( $csv_n ) ? esc_html( $csv_n . ' rows' ) : esc_html( $csv_n ); ?></td>
          <td style="padding:7px 12px;font-weight:600"><?php echo $db_val !== null ? esc_html( (string) $db_val ) : '-'; ?></td>
          <td style="padding:7px 12px"><span style="color:<?php echo esc_attr( $color ); ?>;font-size:.8rem;font-weight:600"><?php echo esc_html( $badge ); ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ── CSV FILES ─────────────────────────────────────────────────────────── -->
  <div class="postbox" style="padding:16px;margin-top:16px">
    <h2 style="font-size:1rem;margin:0 0 8px">CSV Files (mock_data/csv/)</h2>
    <p style="font-size:.83rem;color:#64748b;margin-bottom:10px">
      Edit these files to customise demo content before installing. Empty CSVs cause that section to be skipped.
    </p>
    <table style="width:100%;border-collapse:collapse;font-size:.82rem">
      <thead>
        <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;text-align:left">
          <th style="padding:7px 12px">File</th>
          <th style="padding:7px 12px">Rows</th>
          <th style="padding:7px 12px">Purpose</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Driven by the same central registry as the importer above, so every
        // supported CSV is listed automatically. Navigation, footer and FAQs are
        // managed by the CMS plugin, not the theme, so they don't appear.
        foreach ( CH_Theme_Seeder::importable_types() as $info ) :
          $rows = CH_Data::load_csv( $info['csv'] );
          $n    = is_array( $rows ) ? count( $rows ) : 0;
          $file = $info['csv'] . '.csv';
        ?>
        <tr style="border-bottom:1px solid #f1f5f9">
          <td style="padding:6px 12px"><code><?php echo esc_html( $file ); ?></code></td>
          <td style="padding:6px 12px">
            <?php if ( $n > 0 ) : ?>
              <span style="color:#16a34a;font-weight:600"><?php echo esc_html( $n ); ?> rows</span>
            <?php else : ?>
              <span style="color:#dc2626;font-weight:600">Empty / missing</span>
            <?php endif; ?>
          </td>
          <td style="padding:6px 12px;color:#64748b">
            <?php echo esc_html( $info['label'] ); ?>
            <span style="color:#94a3b8">— <?php echo esc_html( $info['cols'] ); ?></span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="notice notice-warning" style="margin-top:12px">
    <p>
      ⚠️ <strong>Duplicate protection:</strong> Reviews and FAQs have no dedup - running seed multiple times will insert duplicates.
      Use <a href="<?php echo esc_url( admin_url('admin.php?page=ch-theme-cleanup') ); ?>">Cleanup Data</a> first if re-seeding.
    </p>
  </div>

</div>
