<?php
/**
 * admin/settings-schemas.php - declarative field schemas for the theme's
 * settings tabs. ONE schema drives both the rendered form and the save handler,
 * so adding a settings tab means adding a schema here + a 3-line view file.
 *
 * Field types: text | textarea | number | toggle | select | checklist
 *   select/checklist 'options' may be an array (value => label) OR the name of
 *   a function returning one (for dynamic lists like CMS topics / calculators).
 */

defined( 'ABSPATH' ) || exit;

/**
 * @return array<string,array{option:string,title:string,intro:string,fields:array}>
 */
function adn_settings_schemas() {
	return array(

		'home_hero' => array(
			'option' => 'adn_home_hero',
			'title'  => __( 'Hero & Intro', ADN_TEXT_DOMAIN ),
			'intro'  => __( 'Overrides the home hero. Leave a field blank to keep the theme default.', ADN_TEXT_DOMAIN ),
			'fields' => array(
				/* ── Heading & copy ── */
				array( 'key' => 'heading_1',      'type' => 'text',     'label' => __( 'Heading line 1', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'heading_accent',  'type' => 'text',     'label' => __( 'Heading line 2 (accent colour)', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'heading_3',       'type' => 'text',     'label' => __( 'Heading line 3', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'description',     'type' => 'textarea', 'label' => __( 'Description', ADN_TEXT_DOMAIN ) ),
				/* ── Call-to-action buttons ── */
				array( 'key' => 'cta1_label', 'type' => 'text', 'label' => __( 'Primary button label', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'cta1_url',   'type' => 'text', 'label' => __( 'Primary button URL', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'cta2_label', 'type' => 'text', 'label' => __( 'Secondary button label', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'cta2_url',   'type' => 'text', 'label' => __( 'Secondary button URL', ADN_TEXT_DOMAIN ) ),
				/* ── Circle diagram ── */
				array(
					'key'   => 'diagram_center_icon',
					'type'  => 'text',
					'label' => __( 'Diagram - centre icon', ADN_TEXT_DOMAIN ),
					'desc'  => __( 'Emoji or Font Awesome class (e.g. 🏡 or fa-house). Leave blank to keep default.', ADN_TEXT_DOMAIN ),
				),
				array( 'key' => 'diagram_center_line1', 'type' => 'text', 'label' => __( 'Diagram - centre text line 1', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'diagram_center_line2', 'type' => 'text', 'label' => __( 'Diagram - centre text line 2', ADN_TEXT_DOMAIN ) ),
				array(
					'key'   => 'diagram_nodes',
					'type'  => 'textarea',
					'label' => __( 'Diagram - nodes (steps around the circle)', ADN_TEXT_DOMAIN ),
					'desc'  => __( 'One node per line - maximum 8. Format: icon|Label  e.g. 🏡|Find & View  - nodes appear in order around the circle. Extra lines beyond 8 are ignored.', ADN_TEXT_DOMAIN ),
				),
			),
		),

		'home_sections' => array(
			'option' => 'adn_home_sections',
			'title'  => __( 'Home Sections', ADN_TEXT_DOMAIN ),
			'intro'  => __( 'Show or hide each section of the home page.', ADN_TEXT_DOMAIN ),
			'fields' => array(
				array( 'key' => 'hero',        'type' => 'toggle', 'label' => __( 'Hero', ADN_TEXT_DOMAIN ), 'default' => 1 ),
				array( 'key' => 'journey',     'type' => 'toggle', 'label' => __( 'Journey cards', ADN_TEXT_DOMAIN ), 'default' => 1 ),
				array( 'key' => 'news',        'type' => 'toggle', 'label' => __( 'News / Regulations / Hot topics', ADN_TEXT_DOMAIN ), 'default' => 1 ),
				array( 'key' => 'calculators', 'type' => 'toggle', 'label' => __( 'Calculators', ADN_TEXT_DOMAIN ), 'default' => 1 ),
				array( 'key' => 'guides',      'type' => 'toggle', 'label' => __( 'Guides & Insights', ADN_TEXT_DOMAIN ), 'default' => 1 ),
				array( 'key' => 'newsletter',  'type' => 'toggle', 'label' => __( 'Newsletter', ADN_TEXT_DOMAIN ), 'default' => 1 ),
				array( 'key' => 'spotlights',  'type' => 'toggle', 'label' => __( 'Spotlights widget', ADN_TEXT_DOMAIN ), 'default' => 1 ),
				array( 'key' => 'spotlight_term', 'type' => 'select', 'label' => __( 'Spotlight term (home)', ADN_TEXT_DOMAIN ),
					'default' => '', 'options' => 'adn_settings_spotlight_term_options',
					'desc' => __( 'Which spotlight group to show on the home page. Manage groups in CMS Plugin → Spotlights.', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'featured_in_section', 'type' => 'select', 'label' => __( 'Featured In strip', ADN_TEXT_DOMAIN ),
					'default' => '', 'options' => 'adn_settings_fi_section_options',
					'desc' => __( 'Which logo strip to show on the home page. Manage strips in CMS Plugin → Featured In.', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'marquee_enabled', 'type' => 'toggle', 'label' => __( 'Marquee bar', ADN_TEXT_DOMAIN ), 'default' => 0,
					'desc' => __( 'Show a scrolling trust/highlight bar below the hero.', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'marquee_mode', 'type' => 'select', 'label' => __( 'Marquee mode', ADN_TEXT_DOMAIN ),
					'default' => 'string',
					'options' => array( 'string' => __( 'Plain text (✓ prefix)', ADN_TEXT_DOMAIN ), 'icon' => __( 'Icon + label + note', ADN_TEXT_DOMAIN ) ) ),
				array( 'key' => 'marquee_items', 'type' => 'textarea', 'label' => __( 'Marquee items', ADN_TEXT_DOMAIN ),
					'desc' => __( 'One item per line. Plain text mode: write text only. Icon mode: emoji|Label text|Subtitle note', ADN_TEXT_DOMAIN ) ),
			),
		),

		'home_featured' => array(
			'option' => 'adn_home_featured',
			'title'  => __( 'Featured Guides & Topics', ADN_TEXT_DOMAIN ),
			'intro'  => __( 'Pick which Guide topics feed the home "Guides & Insights" cards and how many to show. Leave all unticked to use the latest from every topic.', ADN_TEXT_DOMAIN ),
			'fields' => array(
				array( 'key' => 'topics', 'type' => 'checklist', 'label' => __( 'Topics', ADN_TEXT_DOMAIN ), 'options' => 'adn_settings_topic_options' ),
				array( 'key' => 'count', 'type' => 'number', 'label' => __( 'Articles to show', ADN_TEXT_DOMAIN ), 'default' => 5, 'min' => 1, 'max' => 12 ),
			),
		),

		'calculators_general' => array(
			'option' => 'adn_calculators_general',
			'title'  => __( 'Calculators - Heading & Banner', ADN_TEXT_DOMAIN ),
			'intro'  => __( 'The heading, banner image and intro shown on the calculators page.', ADN_TEXT_DOMAIN ),
			'fields' => array(
				array( 'key' => 'main_heading', 'type' => 'text', 'label' => __( 'Main heading', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'subheading', 'type' => 'text', 'label' => __( 'Sub heading', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'thumbnail', 'type' => 'text', 'label' => __( 'Banner / thumbnail image URL', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'intro', 'type' => 'textarea', 'label' => __( 'Intro text', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'marquee_enabled', 'type' => 'toggle', 'label' => __( 'Marquee bar', ADN_TEXT_DOMAIN ), 'default' => 0,
					'desc' => __( 'Show a scrolling bar inside the hero bottom strip. Replaces the default trust icons.', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'marquee_mode', 'type' => 'select', 'label' => __( 'Marquee mode', ADN_TEXT_DOMAIN ),
					'default' => 'string',
					'options' => array( 'string' => __( 'Plain text (✓ prefix)', ADN_TEXT_DOMAIN ), 'icon' => __( 'Icon + label + note', ADN_TEXT_DOMAIN ) ) ),
				array( 'key' => 'marquee_items', 'type' => 'textarea', 'label' => __( 'Marquee items', ADN_TEXT_DOMAIN ),
					'desc' => __( 'One item per line. Plain text mode: write text only. Icon mode: emoji|Label text|Subtitle note', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'featured_in_section', 'type' => 'select', 'label' => __( 'Featured In strip', ADN_TEXT_DOMAIN ),
					'default' => '', 'options' => 'adn_settings_fi_section_options',
					'desc' => __( 'Which logo strip to show on this page. Manage strips in CMS Plugin → Featured In.', ADN_TEXT_DOMAIN ) ),
			),
		),
	);
}

/** Dynamic select options: all Featured In sections (id => heading [id]). */
function adn_settings_fi_section_options() {
	$options = array( '' => __( '- None (hide strip) -', ADN_TEXT_DOMAIN ) );
	$raw     = get_option( 'ah_featured_in_sections', '' );
	$all     = $raw ? json_decode( $raw, true ) : array();
	if ( is_array( $all ) ) {
		foreach ( $all as $s ) {
			$sid = isset( $s['id'] ) ? (string) $s['id'] : '';
			if ( '' === $sid ) { continue; }
			$label           = ( isset( $s['heading'] ) && '' !== $s['heading'] ) ? $s['heading'] : $sid;
			$options[ $sid ] = $label . '  [' . $sid . ']';
		}
	}
	return $options;
}

/** Dynamic select options: all active spotlight terms (slug => name). */
function adn_settings_spotlight_term_options() {
	$options = array( '' => __( '- None -', ADN_TEXT_DOMAIN ) );
	if ( class_exists( 'AH_Spotlight_Terms_Model' ) ) {
		foreach ( ( new AH_Spotlight_Terms_Model() )->get_all_active() as $term ) {
			$options[ (string) $term->slug ] = (string) $term->name;
		}
	}
	return $options;
}

/** Dynamic checklist options: every Guide topic, labelled "Parent › Topic". */
function adn_settings_topic_options() {
	$options = array();
	if ( function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
		foreach ( adn_cms_guide_parents( 20 ) as $parent ) {
			foreach ( adn_cms_topics( (int) $parent->id, 50 ) as $topic ) {
				$options[ (int) $topic->id ] = $parent->name . ' › ' . $topic->name;
			}
		}
	}
	return $options;
}

/** Dynamic checklist options: every registered calculator. */
function adn_settings_calculator_options() {
	$options = array();
	if ( function_exists( 'adn_calculators' ) ) {
		foreach ( adn_calculators() as $key => $calc ) {
			$options[ $key ] = isset( $calc['label'] ) ? $calc['label'] : $key;
		}
	}
	return $options;
}
