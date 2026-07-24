<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

// Gather stats
$pages_model   = new AH_Pages_Model();
$posts_model   = new AH_Posts_Model();
$reviews_model = new AH_Reviews_Model();
$faqs_model    = new AH_Faqs_Model();
$media_model   = new AH_Media_Model();

$total_pages    = $pages_model->count();
$total_posts    = $posts_model->count();
$total_reviews  = $reviews_model->count();
$total_faqs     = $faqs_model->count();
$total_media    = $media_model->count();


// Recent posts
$recent_posts = $posts_model->get_paginated( 1, array( 'order' => 'DESC', 'limit' => 5 ) )['items'];
?>
<div class="wrap ah-wrap">
  <?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'admin-home', 'CMS Dashboard', 'Overview of your site content, recent activity, and quick actions.' ); ?>

  <!-- Stats Grid -->
  <div class="ah-stats-grid">
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-pages' ) ); ?>" style="text-decoration:none;"><?php \Ah\Cms\Admin\Components\AdminComponents::statCard( $total_pages, 'Pages', 'dashicons-admin-page' ); ?></a>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-posts' ) ); ?>" style="text-decoration:none;"><?php \Ah\Cms\Admin\Components\AdminComponents::statCard( $total_posts, 'Posts', 'dashicons-edit' ); ?></a>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-reviews' ) ); ?>" style="text-decoration:none;"><?php \Ah\Cms\Admin\Components\AdminComponents::statCard( $total_reviews, 'Reviews', 'dashicons-star-filled' ); ?></a>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-faqs' ) ); ?>" style="text-decoration:none;"><?php \Ah\Cms\Admin\Components\AdminComponents::statCard( $total_faqs, 'FAQs', 'dashicons-editor-help' ); ?></a>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-media' ) ); ?>" style="text-decoration:none;"><?php \Ah\Cms\Admin\Components\AdminComponents::statCard( $total_media, 'Media Files', 'dashicons-images-alt2' ); ?></a>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <?php ob_start(); ?>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <?php
        $links = array(
          array( 'url' => 'ah-posts&action=add',     'label' => '+ New Post',     'icon' => 'dashicons-edit'         ),
          array( 'url' => 'ah-reviews&action=add',   'label' => '+ New Review',   'icon' => 'dashicons-star-filled'  ),
          array( 'url' => 'ah-faqs&action=add',      'label' => '+ New FAQ',      'icon' => 'dashicons-editor-help'  ),
          array( 'url' => 'ah-cms-settings',         'label' => 'Site Settings',  'icon' => 'dashicons-admin-settings'),
        );
        foreach ( $links as $l ) : ?>
          <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $l['url'] ) ); ?>" class="ah-btn ah-btn-secondary" style="justify-content:center;">
            <span class="dashicons <?php echo esc_attr( $l['icon'] ); ?>"></span> <?php echo esc_html( $l['label'] ); ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Quick Actions', ob_get_clean() ); ?>
  </div><!-- /grid -->
</div>
