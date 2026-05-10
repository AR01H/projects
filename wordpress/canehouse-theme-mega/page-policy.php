<?php
/**
 * Template Name: Policy Page
 * WHY: Privacy Policy, Terms & Conditions, Refund Policy all use this template.
 * Same header/footer as main site. Content edited in WP Admin → Pages → Edit.
 */
get_header(); ?>

<div class="policy-page-wrap">
  <div class="policy-container">

    <!-- Breadcrumb -->
    <div class="policy-breadcrumb">
      <a href="<?php echo home_url('/'); ?>">Home</a>
      <span>›</span>
      <span><?php the_title(); ?></span>
    </div>

    <!-- Page Content -->
    <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <article class="policy-content">
      <h1 class="policy-title"><?php the_title(); ?></h1>
      <div class="policy-meta">
        Last updated: <?php echo get_the_modified_date('d F Y'); ?>
      </div>
      <div class="policy-body">
        <?php the_content(); ?>
      </div>
    </article>
    <?php endwhile; endif; ?>

    <!-- Back link -->
    <div class="policy-back">
      <a href="<?php echo home_url('/'); ?>" class="policy-back-btn">← Back to Home</a>
    </div>

  </div>
</div>

<style>
.policy-page-wrap {
  background: #fdfff8;
  padding: 120px 0 60px;
  min-height: 80vh;
}
.policy-container {
  max-width: 820px;
  margin: 0 auto;
  padding: 0 24px;
}
.policy-breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #6b7280;
  margin-bottom: 28px;
}
.policy-breadcrumb a {
  color: #2d5a1b;
  text-decoration: none;
}
.policy-breadcrumb a:hover { text-decoration: underline; }
.policy-title {
  font-size: 2rem;
  font-weight: 900;
  color: #1a3a0a;
  margin: 0 0 8px;
  font-family: 'Nunito', sans-serif;
}
.policy-meta {
  font-size: 13px;
  color: #9ca3af;
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e5e7eb;
}
.policy-body {
  font-size: 15px;
  line-height: 1.85;
  color: #374151;
}
.policy-body h2 { font-size: 1.25rem; color: #1a3a0a; margin: 2rem 0 .75rem; font-family: 'Nunito', sans-serif; }
.policy-body h3 { font-size: 1.05rem; color: #2d5a1b; margin: 1.5rem 0 .5rem; }
.policy-body p  { margin: 0 0 1rem; }
.policy-body ul, .policy-body ol { padding-left: 1.5rem; margin: 0 0 1rem; }
.policy-body li { margin-bottom: .4rem; }
.policy-body a  { color: #2d5a1b; }
.policy-body strong { color: #1a3a0a; }
.policy-back { margin-top: 48px; padding-top: 24px; border-top: 1px solid #e5e7eb; }
.policy-back-btn {
  display: inline-flex; align-items: center; gap: 8px;
  color: #2d5a1b; text-decoration: none; font-weight: 700;
  font-size: 14px; padding: 10px 20px;
  border: 2px solid #2d5a1b; border-radius: 8px; transition: all .15s;
}
.policy-back-btn:hover { background: #2d5a1b; color: #fff; }
</style>

<?php get_footer(); ?>
