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
				array( 'key' => 'heading_1', 'type' => 'text', 'label' => __( 'Heading line 1', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'heading_accent', 'type' => 'text', 'label' => __( 'Heading line 2 (accent)', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'heading_3', 'type' => 'text', 'label' => __( 'Heading line 3', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'description', 'type' => 'textarea', 'label' => __( 'Description', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'cta1_label', 'type' => 'text', 'label' => __( 'Primary button label', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'cta1_url', 'type' => 'text', 'label' => __( 'Primary button URL', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'cta2_label', 'type' => 'text', 'label' => __( 'Secondary button label', ADN_TEXT_DOMAIN ) ),
				array( 'key' => 'cta2_url', 'type' => 'text', 'label' => __( 'Secondary button URL', ADN_TEXT_DOMAIN ) ),
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
			),
		),
	);
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
