<?php
defined( 'ABSPATH' ) || exit;

handle_defined( 'CLIENT_PRIMARY_TITLE', 'Advaith' );
handle_defined( 'CLIENT_SECONDARY_TITLE', 'Homes' );
handle_defined( 'CLIENT_FULL_TITLE',CLIENT_PRIMARY_TITLE . ' ' . CLIENT_SECONDARY_TITLE);
handle_defined( 'CLIENT_SHORT_TITLE', 'AH' );
handle_defined(	'CLIENT_ENQUIRY_SUBJECT_PREFIX','[' . CLIENT_FULL_TITLE . ' Enquiry]');

// Centralized Terminology Constants - everything is a Post
handle_defined( 'AH_TERM_SINGULAR',       'Post' );
handle_defined( 'AH_TERM_PLURAL',         'Posts' );
handle_defined( 'AH_TERM_LOWER',          'post' );
handle_defined( 'AH_TERM_LOWER_PLURAL',   'posts' );

// Primary Contact Info Constants
handle_defined( 'CLIENT_PHONE', '+44 7747 223762' );
handle_defined( 'CLIENT_EMAIL', 'contact@advaithhomes.co.uk' );
handle_defined( 'CLIENT_ADDRESS', 'London & Nationwide' );

// Email Routing Constants
handle_defined( 'EMAIL_GENERAL', 'general@advaithhomes.com' );
handle_defined( 'EMAIL_COMPLAINT', 'complaint@advaithhomes.com' );
handle_defined( 'EMAIL_SALES', 'sales@advaithhomes.com' );
handle_defined( 'EMAIL_SUPPORT', 'support@advaithhomes.com' );
handle_defined( 'EMAIL_MEDIA', 'media@advaithhomes.com' );
handle_defined( 'EMAIL_OTHER', 'contact@advaithhomes.com' );

// NIF Hero Background Image Card
handle_defined( 'NIF_HERO_BG_ALT', 'Property consultants in conversation' );
handle_defined( 'NIF_HERO_TITLE_SPAN', 'Make Smarter ' );
handle_defined( 'NIF_HERO_TITLE_EM', 'Property Decisions' );
handle_defined( 'NIF_HERO_DESC', 'Navigating the UK housing market can be complex, but having the right information makes all the difference. With unbiased market data, expert guidance, and practical tools, you can make confident decisions based on facts - whether youre buying your first home, investing, or exploring the market.' );
handle_defined( 'NIF_HERO_CARD1_TEXT', 'Browse Guides' );
handle_defined( 'NIF_HERO_CARD2_TEXT', 'Mortgages' );

// Homepage Bento Grid Tiles
function get_client_hp_tiles() {
	return [
		'contact' => [
			'icon'   => '💬',
			'title'  => 'Contact Us',
			'desc'   => '',
			'url'    => '/contact/',
			'cta'    => 'Get in touch',
			'color'  => '#0f172a',
			'image'  => 'assets/images/backgrounds/mini_contact.png',
		],
		'support' => [
			'icon'   => '🛡️',
			'title'  => 'Get Support',
			'desc'   => '',
			'url'    => '/contact/?enquiry_type=support',
			'cta'    => 'Get help',
			'color'  => '#1e3a8a',
			'image'  => 'assets/images/backgrounds/mini_help.png',
		],
		'services' => [
			'icon'   => '🛠️',
			'title'  => 'Our Services',
			'desc'   => '',
			'url'    => '/services/',
			'cta'    => 'View services',
			'color'  => '#14532d',
			'image'  => 'assets/images/backgrounds/mini_services.png',
		],
		'multiinfo' => [
			'icon'   => '📚',
			'title'  => 'Info Hub',
			'desc'   => '',
			'url'    => '/multiinfo/',
			'cta'    => 'Explore topics',
			'color'  => '#78350f',
			'image'  => 'assets/images/backgrounds/mini_infohub.png',
		],
		'guides' => [
			'icon'   => '📖',
			'title'  => 'Guides to Know',
			'desc'   => '',
			'url'    => '/guides/',
			'cta'    => 'Browse guides',
			'color'  => '#581c87',
			'image'  => 'assets/images/backgrounds/mini_guides.png',
		],
	];
}
// --- Automated Extracted Text Constants ---
handle_defined( 'TXT_BACK_TO_HOME', 'Back to Home' );
handle_defined( 'TXT_BROWSE_GUIDES', 'Browse Guides' );
handle_defined( 'TXT_CLEANUP_DATA', 'Cleanup Data' );
handle_defined( 'TXT_REMOVE_ALL_MOCK_SEEDED_DATA_FROM_CMS_TABLES_AND_WO', 'Remove all mock/seeded data from CMS tables and WordPress options.' );
handle_defined( 'TXT_CONTENT_FIRST_WORDPRESS_THEME_FOR_S', 'Content-first WordPress theme for %s' );
handle_defined( 'TXT_GUIDE_TO_USE', 'Guide To Use' );
handle_defined( 'TXT_REFERENCE_DOCS_RULES_AND_SNIPPETS_FOR_THIS_THEME', 'Reference docs, rules, and snippets for this theme' );
handle_defined( 'TXT_TAXONOMY_MANAGER', 'Taxonomy Manager' );
handle_defined( 'TXT_MANAGE_TAXONOMY_TYPES_AND_THEIR_TERMS_STORED_IN_TH', 'Manage taxonomy types and their terms - stored in the CMS plugin tables.' );
handle_defined( 'TXT_THE_CMS_PLUGIN_IS_NOT_ACTIVE_ACTIVATE_IT_TO_MANAGE', 'The CMS plugin is not active. Activate it to manage taxonomy types and terms.' );
handle_defined( 'TXT_SAVED_SUCCESSFULLY', 'Saved successfully.' );
handle_defined( 'TXT_TAXONOMY_TYPES', 'Taxonomy Types' );
handle_defined( 'TXT_STORED_IN_WP_AH_TAXONOMY_TYPES', 'stored in wp_ah_taxonomy_types' );
handle_defined( 'TXT_NO_TYPES_YET_USE_INSTALL_MOCK_DATA_TO_SEED_THE_DEF', 'No types yet. Use Install Mock Data to seed the defaults, or add one below.' );
handle_defined( 'TXT_NAME', 'Name' );
handle_defined( 'TXT_SLUG', 'Slug' );
handle_defined( 'TXT_DESCRIPTION', 'Description' );
handle_defined( 'TXT_TERMS', 'Terms' );
handle_defined( 'TXT_DELETE', 'Delete' );
handle_defined( 'TXT_ADD_NEW_TYPE', '+ Add New Type' );
handle_defined( 'TXT_NAME_1', 'Name *' );
handle_defined( 'TXT_SLUG_AUTO_IF_BLANK', 'Slug (auto if blank)' );
handle_defined( 'TXT_ADD_TYPE', 'Add Type' );
handle_defined( 'TXT_TAXONOMY_TERMS', 'Taxonomy Terms' );
handle_defined( 'TXT_STORED_IN_WP_AH_TAXONOMIES', 'stored in wp_ah_taxonomies' );
handle_defined( 'TXT_NO_TERMS_YET_ADD_A_TYPE_FIRST_THEN_ADD_TERMS_TO_IT', 'No terms yet. Add a type first, then add terms to it.' );
handle_defined( 'TXT_TERM_NAME', 'Term Name' );
handle_defined( 'TXT_TYPE', 'Type' );
handle_defined( 'TXT_STATUS', 'Status' );
handle_defined( 'TXT_ADD_NEW_TERM', '+ Add New Term' );
handle_defined( 'TXT_TYPE_1', 'Type *' );
handle_defined( 'TXT_SELECT_TYPE', '- select type -' );
handle_defined( 'TXT_TERM_NAME_1', 'Term Name *' );
handle_defined( 'TXT_ADD_TERM', 'Add Term' );
handle_defined( 'TXT_ADD_AT_LEAST_ONE_TYPE_BEFORE_ADDING_TERMS', 'Add at least one type before adding terms.' );
handle_defined( 'TXT_BROWSE_BY_TOPIC', 'Browse by Topic' );
handle_defined( 'TXT_ALL_TOPICS', 'All Topics' );
handle_defined( 'TXT_NO_SUB_TOPICS_YET', 'No sub-topics yet' );
handle_defined( 'TXT_ALL', 'All' );
handle_defined( 'TXT_LATEST_NEWS', 'Latest News' );
handle_defined( 'TXT_SEE_ALL', 'See all' );
handle_defined( 'TXT_MARKET_PULSE', 'Market Pulse' );
handle_defined( 'TXT_UP', 'up' );
handle_defined( 'TXT_DOWN', 'down' );
handle_defined( 'TXT_POPULAR_NOW', 'Popular Now' );
handle_defined( 'TXT_NEED_TO_BE_A_PART', 'Need to be a part?' );
handle_defined( 'TXT_YOUR_EMAIL_ADDRESS', 'Your email address' );
handle_defined( 'TXT_PERSONALISED_GUIDANCE', 'Personalised Guidance' );
handle_defined( 'TXT_I_NEED_ADVICE_ON', 'I need advice on…' );
handle_defined( 'TXT_LEARN_MORE', 'Learn more' );
handle_defined( 'TXT_SUBCATEGORIES', 'Subcategories' );
handle_defined( 'TXT_IN_S', 'In: %s' );
handle_defined( 'TXT_FULL_PAGE', 'Full page' );
handle_defined( 'TXT_EXPLORE_TOPICS', 'Explore Topics' );
handle_defined( 'TXT_IN_BRIEF', 'In Brief' );
handle_defined( 'TXT_CONTINUE_READING', 'Continue reading' );
handle_defined( 'TXT_PAGE_NAVIGATION', 'Page navigation' );
handle_defined( 'TXT_MORE_S', 'More %s' );
handle_defined( 'TXT_VIEW_ALL_S', 'View all %s' );
handle_defined( 'TXT_EDITOR_S_PICKS', 'Editors Picks' );
handle_defined( 'TXT_READ_STORY', 'Read Story' );
handle_defined( 'TXT_READ', 'Read' );
handle_defined( 'TXT_BREAKING_NEWS', 'Breaking News' );
handle_defined( 'TXT_ALL_NEWS', 'All news' );
handle_defined( 'TXT_CONTINUE_READING_1', 'Continue Reading' );
handle_defined( 'TXT_TOPICS', 'Topics' );
handle_defined( 'TXT_FILTER_BY_TOPIC', 'Filter by topic' );
handle_defined( 'TXT_GUIDES_RESOURCES', 'Guides & Resources' );
handle_defined( 'TXT_FEATURED_ARTICLES', 'Featured articles' );
handle_defined( 'TXT_TOP_STORY', 'Top Story' );
handle_defined( 'TXT_READ_FULL_ARTICLE', 'Read full article' );
handle_defined( 'TXT_FILTER_NEWS_BY_CATEGORY', 'Filter news by category' );
handle_defined( 'TXT_FREE_TOOLS_GUIDES', 'Free Tools & Guides' );
handle_defined( 'TXT_TOOLS_AND_RESOURCES', 'Tools and resources' );
handle_defined( 'TXT_SUGGESTED_GUIDES', 'Suggested guides' );
handle_defined( 'TXT_EXPLORE_MORE', 'Explore More' );
handle_defined( 'TXT_YOU_MIGHT_ALSO_LIKE', 'You Might Also Like' );
handle_defined( 'TXT_ALL_GUIDES', 'All Guides →' );
handle_defined( 'TXT_READ_1', 'Read →' );
handle_defined( 'TXT_SEARCH_GUIDES_NEWS_TOPICS', 'Search guides, news, topics…' );
handle_defined( 'TXT_SHARE_ON_X', 'Share on X' );
handle_defined( 'TXT_SHARE_ON_LINKEDIN', 'Share on LinkedIn' );
handle_defined( 'TXT_KEEP_READING', 'Keep Reading' );
handle_defined( 'TXT_CATEGORIES_IN_THIS_TOPIC', 'Categories in this topic' );
handle_defined( 'TXT_BROWSE_ALL_TOPICS', 'Browse all topics' );
handle_defined( 'TXT_NEED_HELP', 'Need Help?' );
handle_defined( 'TXT_SPEAK_WITH_ONE_OF_OUR_PROPERTY_EXPERTS_FOR_PERSONA', 'Speak with one of our property experts for personalised guidance.' );
handle_defined( 'TXT_GET_IN_TOUCH', 'Get in Touch' );
handle_defined( 'TXT_EXPLORE', 'Explore' );
handle_defined( 'TXT_LATEST_ARTICLE', 'Latest Article' );
handle_defined( 'TXT_READ_MORE', 'Read more' );
handle_defined( 'TXT_PRIMARY_NAVIGATION', 'Primary Navigation' );
handle_defined( 'TXT_FOOTER_NAVIGATION', 'Footer Navigation' );
handle_defined( 'TXT_HIGHLIGHT_LINKS', 'Highlight Links' );
handle_defined( 'TXT_LABEL', 'Label' );
handle_defined( 'TXT_ADD_LINK', 'Add Link' );
handle_defined( 'TXT_HIGHLIGHTER_NAMES', 'Highlighter Names' );
handle_defined( 'TXT_HIGHLIGHTER_NAME', 'Highlighter Name' );
handle_defined( 'TXT_ADD_HIGHLIGHTER_NAME', 'Add Highlighter Name' );
handle_defined( 'TXT_NEW_HIGHLIGHTER_NAME', 'New Highlighter Name' );
handle_defined( 'TXT_SEARCH_HIGHLIGHTER_NAMES', 'Search Highlighter Names' );
handle_defined( 'TXT_ALL_HIGHLIGHTER_NAMES', 'All Highlighter Names' );
handle_defined( 'TXT_EDIT_HIGHLIGHTER_NAME', 'Edit Highlighter Name' );
handle_defined( 'TXT_UPDATE_HIGHLIGHTER_NAME', 'Update Highlighter Name' );
handle_defined( 'TXT_NO_HIGHLIGHTER_NAMES_FOUND', 'No highlighter names found.' );
handle_defined( 'TXT_DATAPROTECTED', 'DataProtected' );
handle_defined( 'TXT_ADD_DATAPROTECTED_LEVEL', 'Add DataProtected Level' );
handle_defined( 'TXT_NEW_DATAPROTECTED_LEVEL', 'New DataProtected Level' );
handle_defined( 'TXT_SEARCH_DATAPROTECTED_LEVELS', 'Search DataProtected Levels' );
handle_defined( 'TXT_ALL_DATAPROTECTED_LEVELS', 'All DataProtected Levels' );
handle_defined( 'TXT_EDIT_DATAPROTECTED_LEVEL', 'Edit DataProtected Level' );
handle_defined( 'TXT_UPDATE_DATAPROTECTED_LEVEL', 'Update DataProtected Level' );
handle_defined( 'TXT_NO_LEVELS_FOUND', 'No levels found.' );
handle_defined( 'TXT_OVERVIEW', 'Overview' );
handle_defined( 'TXT_SECTION_CONTROLS', 'Section Controls' );
handle_defined( 'TXT_NAVIGATION', 'Navigation' );
handle_defined( 'TXT_CONTENT_CONTROLS', 'Content Controls' );
handle_defined( 'TXT_CONTACT_SUBMISSIONS', 'Contact Submissions' );
handle_defined( 'TXT_INSTALL_MOCK_DATA', 'Install Mock Data' );
handle_defined( 'TXT_NEWS_ARTICLE', 'News article' );
handle_defined( 'TXT_BACK_TO_ALL_NEWS', 'Back to All News' );
handle_defined( 'TXT_ALL_NEWS_1', 'All News' );
handle_defined( 'TXT_STAY_INFORMED', 'Stay Informed' );
handle_defined( 'TXT_NEWS', 'News' );
handle_defined( 'TXT_MARKET_UPDATES_PROPERTY_INSIGHTS_AND_BUYING_TIPS_E', 'Market updates, property insights, and buying tips - everything in one place, ordered by date.' );
handle_defined( 'TXT_FILTER_BY_CATEGORY', 'Filter by category' );
handle_defined( 'TXT_NEWS_ARTICLES', 'News articles' );
handle_defined( 'TXT_NEWS_NAVIGATION', 'News navigation' );
handle_defined( 'TXT_NO_NEWS_YET', 'No news yet' );
handle_defined( 'TXT_NOTHING_IN_THIS_CATEGORY_YET', 'Nothing in this category yet.' );
handle_defined( 'TXT_CHECK_BACK_SOON_FOR_UPDATES', 'Check back soon for updates.' );
handle_defined( 'TXT_VIEW_ALL', 'View all →' );
handle_defined( 'TXT_GUIDES_LISTING', 'Guides listing' );
handle_defined( 'TXT_GUIDES', 'guides' );
handle_defined( 'TXT_GUIDES_NAVIGATION', 'Guides navigation' );
handle_defined( 'TXT_BROWSE_BY_TOPIC_1', 'Browse by topic' );
handle_defined( 'TXT_GUIDE_TOPICS', 'Guide topics' );
handle_defined( 'TXT_GET_IN_TOUCH_1', 'Get In Touch' );
handle_defined( 'TXT_ALL_RIGHTS_RESERVED', 'All rights reserved.' );
handle_defined( 'TXT_MAIN_NAVIGATION', 'Main Navigation' );
handle_defined( 'TXT_SEARCH', 'Search' );
handle_defined( 'TXT_OPEN_MENU', 'Open menu' );
handle_defined( 'TXT_CLOSE_SEARCH', 'Close search' );
handle_defined( 'TXT_MOBILE_NAVIGATION', 'Mobile Navigation' );
handle_defined( 'TXT_CONTENTS', 'Contents' );
handle_defined( 'TXT_USEFUL_LINKS', 'Useful Links' );
handle_defined( 'TXT_ALL_BUYING_GUIDES', 'All Buying Guides' );
handle_defined( 'TXT_OUR_SERVICES', 'Our Services' );
handle_defined( 'TXT_CLIENT_STORIES', 'Client Stories' );
handle_defined( 'TXT_TOPIC', 'Topic' );
handle_defined( 'TXT_FULL_TOPIC_PAGE', 'Full topic page' );
handle_defined( 'TXT_FREE_CONSULTATION', 'Free Consultation' );
handle_defined( 'TXT_BOOK_A_FREE_CALL', 'Book a Free Call →' );
handle_defined( 'TXT_FEATURED_GUIDES', 'Featured Guides' );
handle_defined( 'TXT_LATEST_GUIDES', 'Latest Guides' );
handle_defined( 'TXT_NOTHING_HERE_YET', 'Nothing here yet' );
handle_defined( 'TXT_TOPIC_INFORMATION', 'Topic information' );
handle_defined( 'TXT_ARTICLES', 'Articles' );
handle_defined( 'TXT_HOME', 'Home' );
handle_defined( 'TXT_NOTHING_PUBLISHED_YET', 'Nothing published yet' );
handle_defined( 'TXT_TOPIC_SIDEBAR', 'Topic sidebar' );
handle_defined( 'TXT_MARKET_INFORMATION_AND_TOOLS', 'Market information and tools' );
handle_defined( 'TXT_VIEW_ALL_TOPICS', 'View All Topics →' );
handle_defined( 'TXT_READY_TO_PUT_THIS_INTO_PRACTICE_SPEAK_TO_A_BUYER_S', 'Ready to put this into practice? Speak to a buyer agent - free, no obligation.' );
handle_defined( 'TXT_ADD_AN_INTERNAL_NOTE', 'Add an internal note…' );
handle_defined( 'TXT_SEARCH_NAME_EMAIL_MESSAGE', 'Search name, email, message…' );
handle_defined( 'TXT_EMAIL_SENT', 'Email sent' );
handle_defined( 'TXT_NOT_SENT', 'Not sent' );
handle_defined( 'TXT_TYPE_A_POST_TITLE', 'Type a post title…' );
handle_defined( 'TXT_EMPTY', '⭐' );
handle_defined( 'TXT_EMOJI_ICON', 'Emoji icon' );
handle_defined( 'TXT_TRUST_SIGNAL_TEXT', 'Trust signal text' );
handle_defined( 'TXT_EMPTY_1', '🏡' );
handle_defined( 'TXT_EMOJI', 'Emoji' );
handle_defined( 'TXT_850K', '£850k' );
handle_defined( 'TXT_RICHMOND', 'Richmond' );
handle_defined( 'TXT_SOUTH_WEST_LONDON', 'South West London' );
handle_defined( 'TXT_SAVED_20K', 'Saved £20k' );
handle_defined( 'TXT_DETACHED', 'Detached' );
handle_defined( 'TXT_BEDS', 'Beds' );
handle_defined( 'TXT_RESULT_SUMMARY', 'Result summary' );
handle_defined( 'TXT_TYPE_A_PAGE_NAME', 'Type a page name…' );
handle_defined( 'TXT_BLOG_CATEGORIES', 'Blog categories' );
handle_defined( 'TXT_BLOG_NAVIGATION', 'Blog navigation' );
handle_defined( 'TXT_NEWSLETTER', 'Newsletter' );
handle_defined( 'TXT_FILTER_TAB_ACTIVE', ' filter-tab--active' );
handle_defined( 'TXT_CLIENT_REVIEWS', 'Client reviews' );
handle_defined( 'TXT_CONTACT_FORM_AND_DETAILS', 'Contact form and details' );
handle_defined( 'TXT_JANE_SMITH', 'Jane Smith' );
handle_defined( 'TXT_TELL_US_MORE_TIMELINE_REQUIREMENTS_ANYTHING_HELPFUL', 'Tell us more - timeline, requirements, anything helpful…' );
handle_defined( 'TXT_OUR_LOCATION_ON_GOOGLE_MAPS', 'Our location on Google Maps' );
handle_defined( 'TXT_CONTACT_FAQ', 'Contact FAQ' );
handle_defined( 'TXT_CONTENT_OVERVIEW', 'Content overview' );
handle_defined( 'TXT_CONTENT_SUMMARY', 'Content summary' );
handle_defined( 'TXT_CORE_SETTINGS', 'Core settings' );
handle_defined( 'TXT_LIVE_SIGNALS', 'Live signals' );
handle_defined( 'TXT_PUBLISHED_CONTENT', 'Published content' );
handle_defined( 'TXT_STRUCTURED_CONTENT', 'Structured content' );
handle_defined( 'TXT_TOOLS_AND_RESOURCES', 'Tools and resources' );
handle_defined( 'TXT_PROCESS_AND_NUMBERS', 'Process and numbers' );
handle_defined( 'TXT_ALL_FAQS', 'All FAQs' );
handle_defined( 'TXT_HOW_WE_WORK', 'How we work' );
handle_defined( 'TXT_LATEST_FROM_THE_BLOG', 'Latest from the blog' );
handle_defined( 'TXT_EXPAND', 'Expand' );
handle_defined( 'TXT_VIEW_DETAILS', 'View details' );
handle_defined( 'TXT_ALL_SERVICES', 'All services' );
handle_defined( 'TXT_FACEBOOK', 'Facebook' );
handle_defined( 'TXT_TWITTER_X', 'Twitter/X' );
handle_defined( 'TXT_INSTAGRAM', 'Instagram' );
handle_defined( 'TXT_YOUTUBE', 'YouTube' );
handle_defined( 'TXT_LINKEDIN', 'LinkedIn' );
handle_defined( 'TXT_E_G_HIGHLIGHT_NAMES', 'e.g. Highlight Names' );
handle_defined( 'TXT_E_G_HIGHLIGHT_NAMES_1', 'e.g. highlight-names' );
handle_defined( 'TXT_OPTIONAL_DESCRIPTION', 'Optional description' );
handle_defined( 'TXT_E_G_RELATED_ARTICLES', 'e.g. Related Articles' );
handle_defined( 'TXT_E_G_RELATED_ARTICLES_1', 'e.g. related-articles' );
handle_defined( 'TXT_BLOG_POSTS', 'Blog posts' );
handle_defined( 'TXT_E_G_450000', 'e.g. 450000' );
handle_defined( 'TXT_E_G_300000', 'e.g. 300000' );
handle_defined( 'TXT_E_G_4_5', 'e.g. 4.5' );
handle_defined( 'TXT_E_G_25', 'e.g. 25' );
handle_defined( 'TXT_OUR_STORY', 'Our story' );
handle_defined( 'TXT_REMOVE', 'Remove' );
handle_defined( 'TXT_SHARE_THIS_POST', 'Share this post' );
handle_defined( 'TXT_SHARE_OPTIONS', 'Share options' );
handle_defined( 'TXT_SHARE_ON_WHATSAPP', 'Share on WhatsApp' );
handle_defined( 'TXT_COPY_LINK', 'Copy link' );
handle_defined( 'TXT_MORE_SHARE_OPTIONS', 'More share options' );
handle_defined( 'TXT_ARCHIVE_SIDEBAR', 'Archive sidebar' );
handle_defined( 'TXT_CALL_TO_ACTION', 'Call to action' );
handle_defined( 'TXT_FREQUENTLY_ASKED_QUESTIONS', 'Frequently asked questions' );
handle_defined( 'TXT_GUIDE_CATEGORIES', 'Guide categories' );
handle_defined( 'TXT_HERO', 'Hero' );
handle_defined( 'TXT_BREADCRUMB', 'Breadcrumb' );
handle_defined( 'TXT_FEATURED_PROPERTIES', 'Featured properties' );
handle_defined( 'TXT_PREVIOUS_PROPERTY', 'Previous property' );
handle_defined( 'TXT_NEXT_PROPERTY', 'Next property' );
handle_defined( 'TXT_CLIENT_SUCCESS_STORIES', 'Client success stories' );
handle_defined( 'TXT_PREVIOUS_STORY', 'Previous story' );
handle_defined( 'TXT_NEXT_STORY', 'Next story' );
handle_defined( 'TXT_BACK_TO_TOP', 'Back to top' );
handle_defined( 'TXT_SERVICES', 'Services' );
handle_defined( 'TXT_MEET_THE_TEAM', 'Meet the team' );
handle_defined( 'TXT_CLIENT_TESTIMONIALS', 'Client testimonials' );
handle_defined( 'TXT_WHY_CLIENTS_TRUST_US', 'Why clients trust us' );
handle_defined( 'TXT_POSTS_NAVIGATION', 'Posts navigation' );
handle_defined( 'TXT_NEWS_UPDATES', 'News updates' );
handle_defined( 'TXT_WE_RE_WORKING_ON_GREAT_CONTENT_CHECK_BACK_SOON', 'Were working on great content - check back soon.' );
handle_defined( 'TXT_NO_POSTS_IN_THIS_TOPIC_YET_TRY_ANOTHER_CATEGORY', 'No posts in this topic yet. Try another category.' );
handle_defined( 'TXT_WE_RE_WORKING_ON_GREAT_CONTENT_CHECK_BACK_SHORTLY', 'Were working on great content - check back shortly.' );