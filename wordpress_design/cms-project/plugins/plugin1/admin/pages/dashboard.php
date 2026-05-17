<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

// Gather stats
$pages_model   = new AH_Pages_Model();
$posts_model   = new AH_Posts_Model();
$reviews_model = new AH_Reviews_Model();
$team_model    = new AH_Team_Model();
$services_model = new AH_Services_Model();
$faqs_model    = new AH_Faqs_Model();
$media_model   = new AH_Media_Model();

$total_pages    = $pages_model->count();
$total_posts    = $posts_model->count();
$total_reviews  = $reviews_model->count();
$total_team     = $team_model->count();
$total_services = $services_model->count();
$total_faqs     = $faqs_model->count();
$total_media    = $media_model->count();


// Recent posts
$recent_posts = $posts_model->get_paginated( 1, array( 'order' => 'DESC', 'limit' => 5 ) )['items'];
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-admin-home"></span> <?php esc_html_e( 'CMS Dashboard', 'ah-theme' ); ?></h1>

  <!-- Stats Grid -->
  <div class="ah-stats-grid">
    <?php
    $stats = array(
      array( 'label' => 'Pages',         'value' => $total_pages,    'icon' => 'dashicons-admin-page',    'link' => admin_url('admin.php?page=ah-pages') ),
      array( 'label' => 'Posts',         'value' => $total_posts,    'icon' => 'dashicons-edit',           'link' => admin_url('admin.php?page=ah-posts') ),
      array( 'label' => 'Services',      'value' => $total_services, 'icon' => 'dashicons-hammer',         'link' => admin_url('admin.php?page=ah-services') ),
      array( 'label' => 'Reviews',       'value' => $total_reviews,  'icon' => 'dashicons-star-filled',    'link' => admin_url('admin.php?page=ah-reviews') ),
      array( 'label' => 'Team Members',  'value' => $total_team,     'icon' => 'dashicons-groups',         'link' => admin_url('admin.php?page=ah-team') ),
      array( 'label' => 'FAQs',          'value' => $total_faqs,     'icon' => 'dashicons-editor-help',    'link' => admin_url('admin.php?page=ah-faqs') ),
      array( 'label' => 'Media Files',   'value' => $total_media,    'icon' => 'dashicons-images-alt2',    'link' => admin_url('admin.php?page=ah-media') ),
    );
    foreach ( $stats as $s ) : ?>
      <a href="<?php echo esc_url( $s['link'] ); ?>" style="text-decoration:none;">
        <div class="ah-stat-card">
          <div class="stat-icon dashicons <?php echo esc_attr( $s['icon'] ); ?>"></div>
          <div class="stat-number"><?php echo esc_html( $s['value'] ); ?></div>
          <div class="stat-label"><?php echo esc_html( $s['label'] ); ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <!-- Quick Links -->
    <div class="ah-card">
      <div class="ah-card-header">
        <h2><?php esc_html_e( 'Quick Actions', 'ah-theme' ); ?></h2>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <?php
        $links = array(
          array( 'url' => 'ah-posts&action=add',     'label' => '+ New Post',     'icon' => 'dashicons-edit'         ),
          array( 'url' => 'ah-services&action=add',  'label' => '+ New Service',  'icon' => 'dashicons-hammer'       ),
          array( 'url' => 'ah-reviews&action=add',   'label' => '+ New Review',   'icon' => 'dashicons-star-filled'  ),
          array( 'url' => 'ah-faqs&action=add',      'label' => '+ New FAQ',      'icon' => 'dashicons-editor-help'  ),
          array( 'url' => 'ah-team&action=add',      'label' => '+ New Member',   'icon' => 'dashicons-admin-users'  ),
          array( 'url' => 'ah-settings',             'label' => 'Site Settings',  'icon' => 'dashicons-admin-settings'),
        );
        foreach ( $links as $l ) : ?>
          <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $l['url'] ) ); ?>" class="ah-btn ah-btn-secondary" style="justify-content:center;">
            <span class="dashicons <?php echo esc_attr( $l['icon'] ); ?>"></span> <?php echo esc_html( $l['label'] ); ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

  </div><!-- /grid -->
</div>
