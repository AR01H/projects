<?php
/**
 * components/parts/sidebar_news_search.php - Sidebar search box.
 *
 * No props required - renders a static search input.
 * JS (news.js) hooks into #newsSearchInput for live filtering.
 * Usage: adn_component( 'parts/sidebar_news_search' );
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="news-sb-box">
	<div class="news-sb-title"><?php echo esc_html__( 'Search News', ADN_TEXT_DOMAIN ); ?></div>
	<div class="news-search-wrap">
		<input
			type="search"
			id="newsSearchInput"
			class="news-search-input"
			placeholder="<?php echo esc_attr__( 'Search articles…', ADN_TEXT_DOMAIN ); ?>"
			autocomplete="off"
			aria-label="<?php echo esc_attr__( 'Search news articles', ADN_TEXT_DOMAIN ); ?>"
		/>
		<span class="news-search-icon">🔍</span>
	</div>
</div>
