<?php

define("COMING_SOON",FALSE);
define("COMING_SOON_PAGE_SLUG",'coming-soon');
define('LOCAL_CACHE_VERSION',date('d')+12);

// ── Images ───────────────────────────────────────────────────────────────────
define( 'THEME_DEFAULT_HERO_IMG', '/assets/images/default/hero.jpg' );
define( 'THEME_DEFAULT_CATEGORY_IMG', '/assets/images/default/category.jpg' );
define( 'THEME_DEFAULT_CALC_IMG', '/assets/images/default/calculator.jpg' );
define( 'THEME_DEFAULT_NEWS_IMG', '/assets/images/default/news.jpg' );
define( 'THEME_DEFAULT_GENERIC_IMG', '/assets/images/default/generic.png' );
define( 'THEME_DEFAULT_TOPIC_IMG', '/assets/images/default/topic.jpg' );

// ── Pagination limits ────────────────────────────────────────────────────────
define( 'ADN_TOPIC_ARTICLES_PER_PAGE', 12 ); // articles per page on topic category page

// REST API
define( 'ADN_API_NS', 'v1' ); // base namespace → /api/v1/...
define( 'ADN_API_PER_PAGE', 9 );           // default page size for list endpoints