<?php
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/mock_data/seeder.php';

$counts  = AH_Theme_Seeder::table_counts();
$msg     = isset( $_GET['msg'] )  ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : '';
$seeded  = ! empty( $_GET['seeded'] );
$op_type = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : '';

// ── CSV row counts ────────────────────────────────────────────────────────────
$csv_counts = [];
foreach ( [ 'blog-posts', 'reviews', 'client-stories', 'services', 'team', 'faqs', 'news-bar', 'properties', 'taxonomy-types', 'taxonomy-terms', 'pages' ] as $name ) {
	$rows = AH_Data::load_csv( $name );
	$csv_counts[ $name ] = count( $rows );
}

// ── Plugin active? ────────────────────────────────────────────────────────────
$plugin_active = class_exists( 'AH_DB_Helper' );
?>
<div class="wrap ah-admin-wrap">

  <div class="ah-admin-header">
    <div class="ah-admin-logo">⬆</div>
    <div>
      <h1>Mock Data & Schema Installer</h1>
      <p>Install the database schema and demo content for <?php echo esc_html( CLIENT_FULL_TITLE ); ?>. All demo content is driven by CSV files in <code>mock_data/csv/</code> - edit CSVs before installing to customise.</p>
    </div>
  </div>

  <?php if ( $seeded && $msg ) : ?>
    <div class="ah-admin-notice ah-admin-notice--<?php echo $op_type === 'schema' ? 'success' : 'success'; ?>">
      <?php echo $op_type === 'schema' ? '🏗️' : '✅'; ?>
      <?php echo esc_html( $msg ); ?>
    </div>
  <?php endif; ?>

  <!-- ── TWO MAIN ACTION BUTTONS ─────────────────────────────────────────── -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">

    <!-- Button 1: Schema + Setup -->
    <div class="ah-admin-box" style="border-top:3px solid #0284c7;margin-bottom:0">
      <h2 style="color:#0284c7">🏗️ Step 1 - Install Schema & Setup</h2>
      <p style="font-size:.875rem;color:#64748b;margin-bottom:16px">
        Creates database tables, mandatory WP pages (Home, About, Services, Contact…), legal pages (Privacy Policy, Cookie Policy…),
        taxonomy types &amp; terms, and baseline site settings.<br>
        <strong>Safe to run anytime - idempotent.</strong> Does not install demo content.
      </p>
      <ul style="font-size:.82rem;color:#475569;margin:0 0 20px 16px;line-height:1.8">
        <li>DB tables: services, team, reviews, FAQs, news_bar</li>
        <li>WP pages: <?php echo $csv_counts['pages']; ?> pages from pages.csv</li>
        <li>Taxonomy types: <?php echo $csv_counts['taxonomy-types']; ?> types, <?php echo $csv_counts['taxonomy-terms']; ?> terms from CSVs</li>
        <li>Basic site settings (phone, email, address)</li>
      </ul>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'ah_theme_schema' ); ?>
        <input type="hidden" name="action" value="ah_theme_schema">
        <button type="submit" class="button button-primary"
                style="background:#0284c7;border-color:#0369a1;width:100%;padding:10px;font-size:.95rem"
                onclick="return confirm('Install schema and setup pages/taxonomies?')">
          🏗️ Install Schema &amp; Setup
        </button>
      </form>
    </div>

    <!-- Button 2: Mock Data -->
    <div class="ah-admin-box" style="border-top:3px solid #b7791f;margin-bottom:0">
      <h2 style="color:#b7791f">📦 Step 2 - Install Mock Data</h2>
      <p style="font-size:.875rem;color:#64748b;margin-bottom:16px">
        Seeds all demo content from the CSVs in <code>mock_data/csv/</code>.
        <strong>Sections with empty CSVs are skipped.</strong> Existing records (same slug / name) are skipped - no duplicates created.
        <?php if ( ! $plugin_active ) : ?>
          <br><span style="color:#d97706">⚠️ CMS plugin not active - taxonomy seeding and plugin table data will be skipped.</span>
        <?php endif; ?>
      </p>
      <?php
      $content_csvs = [
        'blog-posts'     => 'Blog posts',
        'client-stories' => 'Client stories',
        'reviews'        => 'Reviews',
        'services'       => 'Services',
        'team'           => 'Team members',
        'faqs'           => 'FAQs',
        'news-bar'       => 'News bar items',
        'properties'     => 'Featured properties',
      ];
      ?>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="ah-seed-form">
        <?php wp_nonce_field( 'ah_theme_seed' ); ?>
        <input type="hidden" name="action" value="ah_theme_seed">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
          <span style="font-size:.78rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Select data to import</span>
          <span style="font-size:.78rem;">
            <a href="#" style="color:#b7791f;text-decoration:none;" onclick="document.querySelectorAll('#ah-seed-form input[type=checkbox]:not(:disabled)').forEach(c=>c.checked=true);return false;">All</a>
            &nbsp;/&nbsp;
            <a href="#" style="color:#b7791f;text-decoration:none;" onclick="document.querySelectorAll('#ah-seed-form input[type=checkbox]:not(:disabled)').forEach(c=>c.checked=false);return false;">None</a>
          </span>
        </div>

        <ul style="list-style:none;margin:0 0 16px;padding:0;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;">
          <?php foreach ( $content_csvs as $csv => $label ) :
            $n       = $csv_counts[ $csv ];
            $has_csv = $n > 0;
          ?>
            <li style="border-bottom:1px solid #f1f5f9;last-child{border:none}">
              <label style="display:flex;align-items:center;gap:10px;padding:9px 14px;cursor:<?php echo $has_csv ? 'pointer' : 'default'; ?>;<?php echo ! $has_csv ? 'opacity:.45;' : ''; ?>">
                <input type="checkbox" name="seed_types[]" value="<?php echo esc_attr( $csv ); ?>"
                       <?php echo $has_csv ? 'checked' : 'disabled'; ?>
                       style="width:15px;height:15px;accent-color:#b7791f;flex-shrink:0;">
                <span style="flex:1;font-size:.82rem;color:#374151;"><?php echo esc_html( $label ); ?></span>
                <?php if ( $has_csv ) : ?>
                  <span style="font-size:.75rem;color:#16a34a;white-space:nowrap;">✓ <?php echo esc_html( $n ); ?> rows</span>
                <?php else : ?>
                  <span style="font-size:.75rem;color:#dc2626;white-space:nowrap;">✗ empty</span>
                <?php endif; ?>
              </label>
            </li>
          <?php endforeach; ?>
        </ul>

        <button type="submit" class="button button-primary button-hero"
                style="width:100%;padding:10px;font-size:.95rem"
                onclick="var checked=document.querySelectorAll('#ah-seed-form input[type=checkbox]:checked').length;if(!checked){alert('Please select at least one item to import.');return false;}return confirm('Import selected mock data? Duplicates will be skipped automatically.');">
          📦 Install Selected
        </button>
      </form>
    </div>

  </div><!-- /grid -->

  <!-- ── CURRENT STATUS ──────────────────────────────────────────────────── -->
  <div class="ah-admin-box">
    <h2>Current Database Status</h2>
    <table class="ah-admin-table">
      <thead>
        <tr><th>Content</th><th>CSV rows</th><th>DB rows / option</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php
        $status_rows = [
          [ 'label' => 'Services',         'csv' => 'services',       'db' => $counts['services'] ],
          [ 'label' => 'Team Members',     'csv' => 'team',           'db' => $counts['team'] ],
          [ 'label' => 'Reviews',          'csv' => 'reviews',        'db' => $counts['reviews'] ],
          [ 'label' => 'FAQs',             'csv' => 'faqs',           'db' => $counts['faqs'] ],
          [ 'label' => 'News Bar Items',   'csv' => 'news-bar',       'db' => $counts['news_bar'] ?? null ],
          [ 'label' => 'Blog Posts (WP)',  'csv' => 'blog-posts',     'db' => wp_count_posts()->publish ?? 0 ],
          [ 'label' => 'Client Stories',   'csv' => 'client-stories', 'db' => null ],
          [ 'label' => 'Hero Settings',    'csv' => null,             'db' => get_option('ah_home_settings') ? '✓' : null ],
          [ 'label' => 'Site Settings',    'csv' => null,             'db' => get_option('ah_site_settings') ? '✓' : null ],
          [ 'label' => 'Taxonomy Types',   'csv' => 'taxonomy-types', 'db' => $plugin_active ? null : 'Plugin off' ],
        ];
        foreach ( $status_rows as $r ) :
          $csv_n   = $r['csv'] ? ( $csv_counts[ $r['csv'] ] ?? 0 ) : '-';
          $db_val  = $r['db'];
          $has     = $db_val !== null && $db_val !== 0 && $db_val !== '0' && $db_val !== false;
          $missing = $db_val === null;
        ?>
        <tr>
          <td><strong><?php echo esc_html( $r['label'] ); ?></strong></td>
          <td style="font-size:.82rem;color:#64748b"><?php echo is_int( $csv_n ) ? esc_html( $csv_n . ' rows' ) : esc_html( $csv_n ); ?></td>
          <td style="font-weight:600"><?php echo $db_val !== null ? esc_html( (string) $db_val ) : '-'; ?></td>
          <td>
            <?php if ( $has ) : ?>
              <span class="ah-badge ah-badge--ok">✓ Has data</span>
            <?php elseif ( $missing ) : ?>
              <span class="ah-badge ah-badge--warn">Table missing / plugin off</span>
            <?php else : ?>
              <span class="ah-badge ah-badge--missing">Empty</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ── CSV FILES ───────────────────────────────────────────────────────── -->
  <div class="ah-admin-box">
    <h2>CSV Files (mock_data/csv/)</h2>
    <p style="font-size:.875rem;color:#64748b;margin-bottom:12px">
      Edit these files to customise demo content before installing. Empty or missing CSVs cause that section to be skipped during mock data install.
    </p>
    <table class="ah-admin-table">
      <thead><tr><th>File</th><th>Rows</th><th>Purpose</th></tr></thead>
      <tbody>
        <?php
        $csv_files = [
          'blog-posts.csv'      => 'Blog articles (slug-based dedup)',
          'client-stories.csv'  => 'Client case studies as WP posts',
          'reviews.csv'         => 'Client reviews (dedup by author name)',
          'services.csv'        => 'Services offered (dedup by title)',
          'team.csv'            => 'Team member profiles (dedup by name)',
          'faqs.csv'            => 'Frequently asked questions (dedup by question)',
          'news-bar.csv'        => 'News ticker messages',
          'properties.csv'      => 'Featured property examples',
          'taxonomy-types.csv'  => 'Taxonomy type definitions (used in schema install)',
          'taxonomy-terms.csv'  => 'Taxonomy terms per type (used in schema install)',
          'pages.csv'           => 'Extra WP pages to create (used in schema install)',
        ];
        foreach ( $csv_files as $file => $purpose ) :
          $key = str_replace( '.csv', '', $file );
          $n   = $csv_counts[ $key ] ?? 0;
        ?>
        <tr>
          <td><code style="font-size:.8rem"><?php echo esc_html( $file ); ?></code></td>
          <td>
            <?php if ( $n > 0 ) : ?>
              <span class="ah-badge ah-badge--ok"><?php echo esc_html( $n ); ?> rows</span>
            <?php else : ?>
              <span class="ah-badge ah-badge--missing">Empty / missing</span>
            <?php endif; ?>
          </td>
          <td style="font-size:.82rem;color:#64748b"><?php echo esc_html( $purpose ); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ── NOTES ───────────────────────────────────────────────────────────── -->
  <div class="ah-admin-notice ah-admin-notice--warn">
    ⚠️ <strong>Duplicate protection:</strong> If a blog post slug, review author name, service title, team member name, or FAQ question already exists, it is skipped - not duplicated.
    Run <a href="<?php echo esc_url( admin_url('admin.php?page=ah-theme-cleanup') ); ?>">Cleanup Data</a> first if you want a clean slate, then re-run mock data install.
  </div>

  <div class="ah-admin-notice ah-admin-notice--warn" style="margin-top:8px">
    🗑️ <strong>This data is demo-only.</strong> The entire <code>mock_data/</code> folder can be deleted when you no longer need the installer. Existing installed data will remain unaffected.
  </div>

</div>
