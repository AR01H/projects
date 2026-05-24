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
foreach ( [ 'reviews', 'faqs', 'news_bar' ] as $name ) {
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
      $content_csvs = [
        'reviews'  => 'Customer reviews',
        'faqs'     => 'FAQ entries',
        'news-bar' => 'News bar / marquee items',
        'journal'  => 'Journal blog posts (WP posts)',
      ];
      $csv_map_for_count = [
        'reviews'  => 'reviews',
        'faqs'     => 'faqs',
        'news-bar' => 'news_bar',
        'journal'  => null,
      ];
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
          <?php foreach ( $content_csvs as $key => $label ) :
            $csv_key = $csv_map_for_count[ $key ];
            $n       = $csv_key ? ( $csv_counts[ $csv_key ] ?? 0 ) : null;
            $has_csv = $n === null || $n > 0;
          ?>
            <li style="border-bottom:1px solid #f1f5f9">
              <label style="display:flex;align-items:center;gap:10px;padding:8px 12px;cursor:pointer">
                <input type="checkbox" name="seed_types[]" value="<?php echo esc_attr( $key ); ?>" checked
                       style="width:15px;height:15px;accent-color:#b7791f;flex-shrink:0">
                <span style="flex:1;font-size:.82rem;color:#374151"><?php echo esc_html( $label ); ?></span>
                <?php if ( $n !== null && $n > 0 ) : ?>
                  <span style="font-size:.75rem;color:#16a34a;white-space:nowrap">✓ <?php echo esc_html( $n ); ?> rows</span>
                <?php elseif ( $n === null ) : ?>
                  <span style="font-size:.75rem;color:#64748b;white-space:nowrap">WP posts</span>
                <?php else : ?>
                  <span style="font-size:.75rem;color:#dc2626;white-space:nowrap">✗ empty CSV</span>
                <?php endif; ?>
              </label>
            </li>
          <?php endforeach; ?>
        </ul>

        <button type="submit" class="button button-primary"
                style="width:100%;padding:9px;font-size:.9rem"
                onclick="var checked=document.querySelectorAll('#ch-seed-form input[type=checkbox]:checked').length;if(!checked){alert('Select at least one item.');return false;}return confirm('Import selected mock data?')">
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
          [ 'label' => 'FAQs',                 'csv_key' => 'faqs',     'db_key' => 'faqs'                 ],
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
        $csv_files = [
          'reviews.csv'  => [ 'key' => 'reviews',  'purpose' => 'Customer reviews (author_name, location, review_text, rating, result, status)' ],
          'faqs.csv'     => [ 'key' => 'faqs',     'purpose' => 'FAQs (topic, question, answer, status, sort_order)' ],
          'news_bar.csv' => [ 'key' => 'news_bar', 'purpose' => 'News ticker messages (message, status, sort_order)' ],
        ];
        foreach ( $csv_files as $file => $info ) :
          $n = $csv_counts[ $info['key'] ] ?? 0;
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
          <td style="padding:6px 12px;color:#64748b"><?php echo esc_html( $info['purpose'] ); ?></td>
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
