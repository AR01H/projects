<?php
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/mock_data/seeder.php';

$counts  = AH_Theme_Seeder::table_counts();
$msg     = isset( $_GET['msg'] ) ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : '';
$cleaned = ! empty( $_GET['cleaned'] );
?>
<div class="wrap ah-admin-wrap">

  <div class="ah-admin-header">
    <div class="ah-admin-logo" style="background:#dc2626">🗑</div>
    <div>
      <h1><?php echo esc_html( TXT_CLEANUP_DATA ); ?></h1>
      <p><?php echo esc_html( TXT_REMOVE_ALL_MOCK_SEEDED_DATA_FROM_CMS_TABLES_AND_WO ); ?></p>
    </div>
  </div>

  <?php if ( $cleaned && $msg ) : ?>
    <div class="ah-admin-notice ah-admin-notice--success"><?php echo esc_html( $msg ); ?></div>
  <?php endif; ?>

  <!-- Current data summary -->
  <div class="ah-admin-box">
    <h2>Current Data in Database</h2>
    <table class="ah-admin-table">
      <thead>
        <tr><th>Table / Option</th><th>Rows / Status</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php
        $rows = [
          [ 'name' => 'services (DB table)',   'val' => $counts['services'] ],
          [ 'name' => 'team (DB table)',        'val' => $counts['team'] ],
          [ 'name' => 'reviews (DB table)',     'val' => $counts['reviews'] ],
          [ 'name' => 'faqs (DB table)',        'val' => $counts['faqs'] ],
          [ 'name' => 'news_bar (DB table)',    'val' => $counts['news_bar'] ?? null ],
          [ 'name' => 'ah_site_settings',       'val' => get_option('ah_site_settings') ? '✓ set' : '-' ],
          [ 'name' => 'ah_home_settings',       'val' => $counts['ah_home_settings'] ],
          [ 'name' => 'ah_guide_nav',           'val' => $counts['ah_guide_nav'] ],
          [ 'name' => 'ah_guide_categories',    'val' => $counts['ah_guide_categories'] ],
          [ 'name' => 'ah_nav_buying_topics',   'val' => get_option('ah_nav_buying_topics') ? '✓ set' : '-' ],
          [ 'name' => 'ah_nav_finance_topics',  'val' => get_option('ah_nav_finance_topics') ? '✓ set' : '-' ],
          [ 'name' => 'ah_nav_legal_topics',    'val' => get_option('ah_nav_legal_topics') ? '✓ set' : '-' ],
          [ 'name' => 'ah_process_steps',       'val' => $counts['ah_process_steps'] ],
          [ 'name' => 'ah_site_stats',          'val' => $counts['ah_site_stats'] ],
        ];
        foreach ( $rows as $r ) :
          $empty = empty( $r['val'] ) || $r['val'] === '-' || $r['val'] === 0 || $r['val'] === null;
          $cls   = $r['val'] === null ? 'warn' : ( $empty ? 'missing' : 'ok' );
          $label = $r['val'] === null ? 'Table missing' : ( $empty ? 'Empty / not set' : (string) $r['val'] );
        ?>
        <tr>
          <td style="font-family:monospace;font-size:.82rem"><?php echo esc_html( $r['name'] ); ?></td>
          <td><span class="ah-badge ah-badge--<?php echo esc_attr( $cls ); ?>"><?php echo esc_html( $label ); ?></span></td>
          <td style="font-size:.78rem;color:#94a3b8"><?php echo $empty ? 'Nothing to remove' : 'Will be cleared'; ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Warning -->
  <div class="ah-admin-notice ah-admin-notice--warn">
    ⚠️ <strong>Warning:</strong> This will <strong>TRUNCATE</strong> all CMS content tables and <strong>DELETE</strong> all WordPress options created by the seeder.
    This cannot be undone. Any real content you have added via the CMS Portal will also be removed.
  </div>

  <!-- Action form -->
  <div class="ah-admin-box">
    <h2>Run Cleanup</h2>
    <p style="margin-bottom:20px;color:#64748b;font-size:.9rem">
      Use this when switching from demo data to real content. After cleanup, go to the CMS Portal and add your actual services, team, reviews, and FAQs.
    </p>
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'ah_theme_cleanup' ); ?>
        <input type="hidden" name="action" value="ah_theme_cleanup">
        <button type="submit" class="button button-hero ah-btn-danger"
                onclick="return confirm('This will DELETE all mock data and truncate all content tables. This cannot be undone. Continue?')">
          🗑 Delete All Mock Data
        </button>
      </form>
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-theme-mock' ) ); ?>" class="button button-hero">
        ← Back to Install Mock Data
      </a>
    </div>
  </div>

  <!-- CMS Portal link -->
  <?php if ( defined( 'AH_PLUGIN_VERSION' ) || class_exists( 'AH_Admin_Bootstrap' ) ) : ?>
  <div class="ah-admin-box">
    <h2>Add Real Content</h2>
    <p style="color:#64748b;font-size:.9rem;margin-bottom:16px">
      After cleanup, add your real content via the CMS Portal:
    </p>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <a href="<?php echo esc_url( admin_url('admin.php?page=ah-services') ); ?>"  class="button">Services</a>
      <a href="<?php echo esc_url( admin_url('admin.php?page=ah-team') ); ?>"      class="button">Team</a>
      <a href="<?php echo esc_url( admin_url('admin.php?page=ah-reviews') ); ?>"   class="button">Reviews</a>
      <a href="<?php echo esc_url( admin_url('admin.php?page=ah-faqs') ); ?>"      class="button">FAQs</a>
      <a href="<?php echo esc_url( admin_url('admin.php?page=ah-news-bar') ); ?>"  class="button">News Bar</a>
      <a href="<?php echo esc_url( admin_url('admin.php?page=ah-home') ); ?>"      class="button">Home Sections</a>
      <a href="<?php echo esc_url( admin_url('admin.php?page=ah-settings') ); ?>"  class="button">Site Settings</a>
    </div>
  </div>
  <?php endif; ?>

</div>
