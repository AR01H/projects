<?php
/**
 * Template Name: Blog Listing
 *
 * Property News & Insights (mockup #4): inline title + parent-term group tabs
 * (All / Buying / Selling / …) + featured insight + article grid + load more.
 * Grouping uses the shared parent-term helpers, so a tab shows only that group.
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/blog.php';
?>
<section class="ghub bins">
  <div class="container">

    <header class="ghub-head">
      <h1 class="ghub-head__title">Property News &amp; Insights</h1>
      <p class="ghub-head__sub">Expert analysis, market updates and practical advice.</p>
    </header>

    <?php get_template_part( 'components/parent-term-tabs', null, [
      'terms'     => $data['parent_terms'],
      'active'    => $data['active_group'],
      'base_url'  => $data['base_url'],
      'param'     => 'group',
      'all_label' => 'All',
    ] ); ?>

    <?php get_template_part( 'components/blog/insights-grid', null, $data ); ?>

  </div>
</section>
<?php
get_template_part( 'components/cta-section' );
get_footer();
