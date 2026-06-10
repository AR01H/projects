<?php
defined( 'ABSPATH' ) || exit;

/**
 * Runtime display fallbacks for The Cane House theme.
 *
 * These run ONLY when a DB option (written by Theme Admin → Install Mock Data)
 * is empty. They contain NO hardcoded content - every value is read from the
 * CSV "import sheets" in mock_data/csv/ through CH_Data, the single source of
 * truth. To change any of this, edit the matching CSV.
 *
 * CH_Data is required in functions.php before any of these are ever called,
 * so the static calls below always resolve at render time.
 */

function ch_mock_default_settings(): array {
	return CH_Data::settings();
}

function ch_mock_home_settings_array(): array {
	return CH_Data::home_settings();
}

function ch_mock_menu_sizes(): array {
	return CH_Data::menu_sizes();
}

function ch_mock_cane_types(): array {
	return CH_Data::cane_types();
}

function ch_mock_textures(): array {
	return CH_Data::textures();
}

function ch_mock_flavours(): array {
	return CH_Data::flavours();
}

function ch_mock_order_steps(): array {
	return CH_Data::order_steps();
}

function ch_mock_marquee_items(): array {
	return CH_Data::marquee_items();
}

function ch_mock_reviews(): array {
	// Reviews are owned by the DB / CMS plugin; there is no CSV runtime fallback.
	return [];
}

// FAQs are owned by the CMS plugin (ah_faqs table) - no theme-level FAQ data
// or fallback. ch_get_faqs() reads them directly from the plugin.

function ch_mock_benefits(): array {
	return CH_Data::benefits();
}

function ch_mock_hire_packages(): array {
	return CH_Data::hire_packages();
}

function ch_mock_hire_features(): array {
	return CH_Data::hire_features();
}

function ch_mock_franchise_locations(): array {
	return CH_Data::franchise_locations();
}

function ch_mock_juice_showcase(): array {
	return CH_Data::juice_showcase();
}

function ch_mock_story_settings(): array {
	return CH_Data::story_settings();
}

function ch_mock_events_gallery(): array {
	return CH_Data::events_gallery();
}

function ch_mock_franchise_gallery(): array {
	return CH_Data::franchise_gallery();
}

function ch_mock_about_gallery(): array {
	return CH_Data::about_gallery();
}

function ch_mock_events_media_gallery(): array {
	return CH_Data::events_media_gallery();
}

function ch_mock_franchise_media_gallery(): array {
	return CH_Data::franchise_media_gallery();
}

function ch_mock_sugarcane_gallery(): array {
	return CH_Data::sugarcane_gallery();
}

function ch_mock_sugarcane_stats(): array {
	return CH_Data::sugarcane_stats();
}

function ch_mock_nutrition_facts(): array {
	return CH_Data::nutrition_facts();
}

function ch_mock_about_quality(): array {
	return CH_Data::about_quality();
}

function ch_mock_events_why(): array {
	return CH_Data::events_why();
}

function ch_mock_enquiry_types(): array {
	return CH_Data::enquiry_types();
}

function ch_mock_occasions(): array {
	return CH_Data::occasions();
}
