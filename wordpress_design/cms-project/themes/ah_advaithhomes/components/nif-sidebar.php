<?php

defined( 'ABSPATH' ) || exit;

get_template_part( 'components/aside-items/nif-sb', 'flash-updates', [
	'news_bar_items' => $args['news_bar_items'] ?? [],
] );

get_template_part( 'components/aside-items/nif-sb', 'market-pulse', [
	'site_stats' => $args['site_stats'] ?? [],
] );

get_template_part( 'components/aside-items/nif-sb', 'popular-now', [
	'popular_posts' => $args['popular_posts'] ?? [],
] );

get_template_part( 'components/aside-items/nif-sb', 'browse-topics', [
	'cats'               => $args['cats']               ?? [],
	'active_cat'         => $args['active_cat']         ?? '',
	'permalink'          => $args['permalink']           ?? get_permalink(),
	'parent_terms'       => $args['parent_terms']       ?? [],
	'active_parent_term' => $args['active_parent_term'] ?? '',
	'active_pt_obj'      => $args['active_pt_obj']      ?? null,
	'pt_child_cats'      => $args['pt_child_cats']      ?? [],
] );

get_template_part( 'components/aside-items/nif-sb', 'weekly-briefing' );
?>
<style>
  .news-ticker__inner {
    display: none;
  }
</style>