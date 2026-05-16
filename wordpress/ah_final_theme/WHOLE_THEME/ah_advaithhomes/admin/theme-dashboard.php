<?php
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/mock_data/seeder.php';

$theme   = wp_get_theme();
$counts  = AH_Theme_Seeder::table_counts();
$plugin_ok = class_exists( 'AH_Admin_Bootstrap' ) || defined( 'AH_PLUGIN_VERSION' );
?>
<div class="wrap ah-admin-wrap">

  <div class="ah-admin-header">
    <div class="ah-admin-logo">AH</div>
    <div>
      <h1><?php echo esc_html( $theme->get('Name') ); ?> <span style="font-weight:400;font-size:1rem;color:#94a3b8">v<?php echo esc_html( $theme->get('Version') ); ?></span></h1>
      <p><?php esc_html_e( 'Content-first WordPress theme for Advaith Homes', 'ah-theme' ); ?></p>
    </div>
  </div>

  <!-- Status cards -->
  <div class="ah-admin-cards">
    <?php
    $card_data = [
      [ 'label' => 'CMS Plugin',       'value' => $plugin_ok ? 'Active'    : 'Not Active', 'class' => $plugin_ok ? 'ok' : 'warn', 'sub' => $plugin_ok ? 'ah_cms_plugin is running' : 'Install & activate ah_cms_plugin' ],
      [ 'label' => 'Services',         'value' => $counts['services'] ?? '—', 'class' => ( ($counts['services'] ?? 0) > 0 ) ? 'ok' : 'warn', 'sub' => 'rows in DB' ],
      [ 'label' => 'Team Members',     'value' => $counts['team']     ?? '—', 'class' => ( ($counts['team'] ?? 0) > 0 )     ? 'ok' : 'warn', 'sub' => 'rows in DB' ],
      [ 'label' => 'Reviews',          'value' => $counts['reviews']  ?? '—', 'class' => ( ($counts['reviews'] ?? 0) > 0 )  ? 'ok' : 'warn', 'sub' => 'rows in DB' ],
      [ 'label' => 'FAQs',             'value' => $counts['faqs']     ?? '—', 'class' => ( ($counts['faqs'] ?? 0) > 0 )     ? 'ok' : 'warn', 'sub' => 'rows in DB' ],
      [ 'label' => 'Home Settings',    'value' => $counts['ah_home_settings']   !== '—' ? 'Set' : 'Missing', 'class' => $counts['ah_home_settings']   !== '—' ? 'ok' : 'warn', 'sub' => 'wp_option' ],
      [ 'label' => 'Process Steps',    'value' => $counts['ah_process_steps']   !== '—' ? 'Set' : 'Missing', 'class' => $counts['ah_process_steps']   !== '—' ? 'ok' : 'warn', 'sub' => 'wp_option' ],
      [ 'label' => 'Site Stats',       'value' => $counts['ah_site_stats']      !== '—' ? 'Set' : 'Missing', 'class' => $counts['ah_site_stats']      !== '—' ? 'ok' : 'warn', 'sub' => 'wp_option' ],
    ];
    foreach ( $card_data as $c ) :
    ?>
    <div class="ah-admin-card ah-admin-card--<?php echo esc_attr( $c['class'] ); ?>">
      <div class="ah-admin-card__label"><?php echo esc_html( $c['label'] ); ?></div>
      <div class="ah-admin-card__value"><?php echo esc_html( $c['value'] ); ?></div>
      <div class="ah-admin-card__sub"><?php echo esc_html( $c['sub'] ); ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Quick actions -->
  <div class="ah-admin-box">
    <h2>Quick Actions</h2>
    <p style="margin-bottom:20px;color:#64748b;font-size:.9rem">
      If the site is showing empty content, install mock data first. Remove it when you're ready to add real content via the CMS Portal.
    </p>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-theme-mock' ) ); ?>" class="button button-primary button-hero">
        ⬆ Install Mock Data
      </a>
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-theme-cleanup' ) ); ?>" class="button button-hero">
        🗑 Cleanup Data
      </a>
      <?php if ( $plugin_ok ) : ?>
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-dashboard' ) ); ?>" class="button button-hero">
        ⚙ CMS Portal →
      </a>
      <?php endif; ?>
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="button button-hero">
        🌐 View Site
      </a>
    </div>
  </div>

  <!-- Content source table -->
  <div class="ah-admin-box">
    <h2>Data Sources</h2>
    <table class="ah-admin-table">
      <thead>
        <tr>
          <th>Content Type</th>
          <th>Primary Source</th>
          <th>Fallback</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sources = [
          [ 'type' => 'Services',       'primary' => 'CMS Portal → Services',        'fallback' => 'ah_mock_services()',         'count' => $counts['services'] ],
          [ 'type' => 'Team Members',   'primary' => 'CMS Portal → Team',            'fallback' => 'ah_mock_team()',             'count' => $counts['team'] ],
          [ 'type' => 'Reviews',        'primary' => 'CMS Portal → Reviews',         'fallback' => 'ah_mock_reviews()',          'count' => $counts['reviews'] ],
          [ 'type' => 'FAQs',           'primary' => 'CMS Portal → FAQs',            'fallback' => 'ah_mock_faqs()',             'count' => $counts['faqs'] ],
          [ 'type' => 'News Bar',       'primary' => 'CMS Portal → News Bar',        'fallback' => 'ah_mock_news_bar_items()',   'count' => $counts['news_bar'] ?? null ],
          [ 'type' => 'Hero/Stats',     'primary' => 'WP Option: ah_home_settings',  'fallback' => 'ah_mock_home_settings_array()', 'opt' => $counts['ah_home_settings'] ],
          [ 'type' => 'Process Steps',  'primary' => 'WP Option: ah_process_steps',  'fallback' => 'ah_mock_process_steps()',   'opt' => $counts['ah_process_steps'] ],
          [ 'type' => 'Site Stats',     'primary' => 'WP Option: ah_site_stats',     'fallback' => 'ah_mock_site_stats()',      'opt' => $counts['ah_site_stats'] ],
          [ 'type' => 'Guide Categories','primary' => 'WP Option: ah_guide_categories','fallback' => 'ah_mock_guide_categories_array()', 'opt' => $counts['ah_guide_categories'] ],
          [ 'type' => 'Nav Topics',     'primary' => 'WP Option: ah_nav_*_topics',   'fallback' => 'ah_mock_nav_*_topics()',    'opt' => $counts['ah_nav_buying_topics'] ?? '—' ],
        ];
        foreach ( $sources as $src ) :
          if ( array_key_exists( 'count', $src ) ) {
            $status_class = ( $src['count'] === null ) ? 'warn' : ( $src['count'] > 0 ? 'ok' : 'warn' );
            $status_label = ( $src['count'] === null ) ? 'Table missing' : ( $src['count'] > 0 ? $src['count'] . ' rows' : 'Empty — using fallback' );
          } else {
            $status_class = ( $src['opt'] !== '—' ) ? 'ok' : 'warn';
            $status_label = ( $src['opt'] !== '—' ) ? 'Option set' : 'Missing — using fallback';
          }
        ?>
        <tr>
          <td><strong><?php echo esc_html( $src['type'] ); ?></strong></td>
          <td style="color:#64748b;font-size:.82rem"><?php echo esc_html( $src['primary'] ); ?></td>
          <td style="color:#94a3b8;font-size:.78rem;font-family:monospace"><?php echo esc_html( $src['fallback'] ); ?></td>
          <td><span class="ah-badge ah-badge--<?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>
