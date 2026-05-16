<?php
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/mock_data/seeder.php';

$counts = AH_Theme_Seeder::table_counts();
$msg    = isset( $_GET['msg'] ) ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : '';
$seeded = ! empty( $_GET['seeded'] );
?>
<div class="wrap ah-admin-wrap">

  <div class="ah-admin-header">
    <div class="ah-admin-logo">⬆</div>
    <div>
      <h1><?php esc_html_e( 'Install Mock Data', 'ah-theme' ); ?></h1>
      <p><?php esc_html_e( 'Populate all CMS tables and WordPress options with realistic demo content.', 'ah-theme' ); ?></p>
    </div>
  </div>

  <?php if ( $seeded && $msg ) : ?>
    <div class="ah-admin-notice ah-admin-notice--success"><?php echo esc_html( $msg ); ?></div>
  <?php endif; ?>

  <!-- What gets seeded -->
  <div class="ah-admin-box">
    <h2>What Will Be Installed</h2>
    <table class="ah-admin-table">
      <thead>
        <tr><th>Item</th><th>Type</th><th>Count</th><th>Current Status</th></tr>
      </thead>
      <tbody>
        <?php
        $items = [
          [ 'item' => 'Services',         'type' => 'DB table (services)',         'count' => 6,  'current' => $counts['services'] ],
          [ 'item' => 'Team Members',     'type' => 'DB table (team)',             'count' => 4,  'current' => $counts['team'] ],
          [ 'item' => 'Reviews',          'type' => 'DB table (reviews)',          'count' => 6,  'current' => $counts['reviews'] ],
          [ 'item' => 'FAQs',             'type' => 'DB table (faqs)',             'count' => 10, 'current' => $counts['faqs'] ],
          [ 'item' => 'News Bar Items',   'type' => 'DB table (news_bar)',         'count' => 5,  'current' => $counts['news_bar'] ?? null ],
          [ 'item' => 'Hero Settings',    'type' => 'WP option (ah_home_settings)','count' => 1,  'current' => $counts['ah_home_settings'] !== '—' ? '✓' : null ],
          [ 'item' => 'Site Settings',    'type' => 'WP option (ah_site_settings)','count' => 1,  'current' => get_option('ah_site_settings') ? '✓' : null ],
          [ 'item' => 'Process Steps',    'type' => 'WP option (ah_process_steps)','count' => 6,  'current' => $counts['ah_process_steps'] !== '—' ? '✓' : null ],
          [ 'item' => 'Site Stats',       'type' => 'WP option (ah_site_stats)',   'count' => 4,  'current' => $counts['ah_site_stats'] !== '—' ? '✓' : null ],
          [ 'item' => 'Guide Categories', 'type' => 'WP option (ah_guide_categories)','count' => 4,'current' => $counts['ah_guide_categories'] !== '—' ? '✓' : null ],
          [ 'item' => 'Nav Topics',       'type' => 'WP options (ah_nav_*)',       'count' => 3,  'current' => get_option('ah_nav_buying_topics') ? '✓' : null ],
          [ 'item' => 'Buying Guide Nav', 'type' => 'WP option (ah_guide_nav)',    'count' => 9,  'current' => $counts['ah_guide_nav'] !== '—' ? '✓' : null ],
          [ 'item' => 'Properties',      'type' => 'WP option (ah_featured_properties)', 'count' => 6, 'current' => get_option('ah_featured_properties') ? '✓' : null ],
          [ 'item' => 'Blog Posts',      'type' => 'WP posts (published)',        'count' => 3,  'current' => wp_count_posts()->publish >= 3 ? '✓' : null ],
          [ 'item' => 'Static Pages',    'type' => 'HTML files + WP pages',       'count' => 7,  'current' => ( count( glob( trailingslashit( get_template_directory() ) . 'static/*.html' ) ?: [] ) >= 7 ) ? '✓' : null ],
        ];
        foreach ( $items as $r ) :
          $has = ! empty( $r['current'] ) && $r['current'] !== '—' && $r['current'] !== 0 && $r['current'] !== null;
        ?>
        <tr>
          <td><strong><?php echo esc_html( $r['item'] ); ?></strong></td>
          <td style="font-size:.82rem;color:#64748b"><?php echo esc_html( $r['type'] ); ?></td>
          <td style="font-weight:600"><?php echo esc_html( $r['count'] ); ?></td>
          <td>
            <?php if ( $has ) : ?>
              <span class="ah-badge ah-badge--ok">✓ Already has data</span>
            <?php elseif ( $r['current'] === null ) : ?>
              <span class="ah-badge ah-badge--warn">Table missing (plugin off?)</span>
            <?php else : ?>
              <span class="ah-badge ah-badge--missing">Empty — will be seeded</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Warning -->
  <div class="ah-admin-notice ah-admin-notice--warn">
    ⚠️ <strong>Note:</strong> Running the seeder will <strong>create any missing DB tables</strong> automatically, then
    <strong>add</strong> rows (it does not truncate existing data first).
    If you want a clean install, run <a href="<?php echo esc_url( admin_url('admin.php?page=ah-theme-cleanup') ); ?>">Cleanup Data</a> first.
  </div>

  <!-- Action form -->
  <div class="ah-admin-box">
    <h2>Run Seeder</h2>
    <p style="margin-bottom:20px;color:#64748b;font-size:.9rem">
      This will populate all CMS tables and WordPress options with realistic mock data — services, team members, reviews, FAQs, news ticker items, settings, properties, blog posts, and 7 static HTML pages (stamp duty calculator, mortgage calculator, glossary, and more).
    </p>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
      <?php wp_nonce_field( 'ah_theme_seed' ); ?>
      <input type="hidden" name="action" value="ah_theme_seed">
      <button type="submit" class="button button-primary button-hero"
              onclick="return confirm('This will insert mock data into all CMS tables. Continue?')">
        ⬆ Install All Mock Data
      </button>
    </form>
  </div>

  <!-- Individual seeders -->
  <div class="ah-admin-box">
    <h2>Individual Seeders (PHP)</h2>
    <p style="margin-bottom:12px;color:#64748b;font-size:.875rem">
      You can also run individual seeders programmatically. Add to <code>functions.php</code> temporarily or run via WP-CLI:
    </p>
    <pre style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;font-size:.82rem;overflow-x:auto">
require_once get_template_directory() . '/mock_data/seeder.php';

// Seed everything at once:
AH_Theme_Seeder::seed_all();

// Or individual tables:
AH_Theme_Seeder::seed_services();
AH_Theme_Seeder::seed_team();
AH_Theme_Seeder::seed_reviews();
AH_Theme_Seeder::seed_faqs();
AH_Theme_Seeder::seed_news_bar();
AH_Theme_Seeder::seed_home_settings();
AH_Theme_Seeder::seed_process_steps();
AH_Theme_Seeder::seed_site_stats();
AH_Theme_Seeder::seed_properties();
AH_Theme_Seeder::seed_blog_posts();
AH_Theme_Seeder::seed_static_pages();  // writes 7 HTML files + WP pages
    </pre>
  </div>

  <!-- SQL download info -->
  <div class="ah-admin-box">
    <h2>SQL Seeder File</h2>
    <p style="color:#64748b;font-size:.9rem;margin-bottom:12px">
      For DB-only seeding (e.g. via phpMyAdmin), use the SQL file at:
    </p>
    <code style="display:block;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 14px;font-size:.85rem">
      <?php echo esc_html( get_template_directory() . '/mock_data/seeder.sql' ); ?>
    </code>
    <p style="margin-top:10px;font-size:.82rem;color:#94a3b8">
      Remember to replace <strong>{prefix}</strong> in the SQL file with your actual table prefix (e.g. <code>wp_ah_cms_plug_</code>).
    </p>
  </div>

</div>
