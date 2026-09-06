<?php
/**
 * One-off seed script: inserts the FAQ content that was removed from the
 * theme's *.json files into wp_ch_ah_faqs, now that FaqBridgeService reads
 * from there. Safe to re-run - it deletes its own previously-seeded rows
 * first (matched by attached_slug + section) before re-inserting.
 */

$mysqli = new mysqli( 'localhost', 'root', '', 'thecanehouse' );
if ( $mysqli->connect_errno ) {
	fwrite( STDERR, "DB connect failed: {$mysqli->connect_error}\n" );
	exit( 1 );
}

$table = 'wp_ch_ah_faqs';

$sets = array(
	// Home - Global, section "Common Questions"
	array(
		'slug'    => null,
		'section' => 'Common Questions',
		'items'   => array(
			array( 'What events do you cater for?', 'We cater for weddings, birthday parties, college fests, corporate events, festivals, and private gatherings of any size.' ),
			array( 'Where do you provide your service?', 'We currently serve Sutton, London, Surrey, and surrounding areas across the United Kingdom.' ),
			array( 'Can I book The Cane House for a wedding?', 'Yes! Our live artisanal sugarcane pressing cart is a unique and popular live beverage counter for wedding receptions.' ),
			array( 'How far in advance should I book?', 'We recommend booking at least 2 to 4 weeks in advance, especially during the busy summer and wedding season.' ),
			array( 'Do you provide custom event packages?', 'Yes, we tailor beverage menus, cup branding, and live counter setups to match your event theme and guest count.' ),
			array( 'Can I order sugarcane juice online?', 'Yes, we provide fresh chilled doorstep delivery in eco-friendly bottles across our local delivery radius.' ),
			array( 'Do you offer franchise opportunities?', 'Yes! We offer low-investment franchise setups including city kiosks, event carts, and full training and operational support.' ),
			array( 'How can I make an enquiry?', 'You can fill out our enquiry form on the website, call us at +44 7770 461 999, or email thecanehouseuk@gmail.com.' ),
		),
	),
	// Contact - slug 'contact', section "Booking & Enquiry Questions"
	array(
		'slug'    => 'contact',
		'section' => 'Booking & Enquiry Questions',
		'items'   => array(
			array( 'How do I book The Cane House for my wedding or party?', 'Simply fill out the form above with your event date, location, and estimated guest count. Our team will get back to you within 24 hours with package options and availability.' ),
			array( 'What is included with the live sugarcane bar?', 'Our live bar includes our traditional press machine, trained baristas, fresh premium sugarcane stalks, biodegradable cups, and a full range of natural flavor infusions.' ),
			array( 'How far in advance should I book?', 'We recommend booking at least 2 to 4 weeks in advance during peak summer and wedding seasons to secure your preferred date.' ),
			array( 'Where are you currently serving?', 'We are based in Sutton, London, and cater across Greater London, Surrey, and surrounding areas. For larger events and franchises, nationwide service is available.' ),
		),
	),
	// Events - slug 'events', section "Booking & Service Answers"
	array(
		'slug'    => 'events',
		'section' => 'Booking & Service Answers',
		'items'   => array(
			array( 'What power and space do you need at the venue?', 'Our specialized commercial cold-press bars only require a standard 13A UK plug socket and a 2m x 2m flat floor space. We bring our own water supply and waste containers.' ),
			array( 'Can you cater outdoor weddings and garden parties?', 'Yes! We operate indoor and outdoor events with all-weather vintage market canopies.' ),
			array( 'How far in advance should we book?', 'We recommend booking 2 to 6 weeks in advance to guarantee fresh cane allocation, though we do accommodate short-notice bookings based on schedule.' ),
			array( 'Are your pressers hygiene certified?', 'All our event artisans hold Level 2 Food Safety certification and adhere strictly to our 5-Star Food Hygiene rating.' ),
		),
	),
	// Franchise - slug 'franchise', section "Partnership Q&A"
	array(
		'slug'    => 'franchise',
		'section' => 'Partnership Q&A',
		'items'   => array(
			array( 'What is the initial investment required?', 'Investment packages are custom-tailored to your desired format (Mobile Vintage Cart, Mall Kiosk, or Flagship Storefront), including commercial cold-press equipment, starter inventory, and complete training.' ),
			array( 'How does the sugarcane stalk supply chain work?', 'We manage centralized agricultural imports and dispatch fresh, temperature-controlled, mature organic sugarcane stalks directly to your local operating hub on a weekly schedule.' ),
			array( 'Do I need prior food & beverage experience?', 'No prior hospitality experience is necessary. Our intensive 2-week training program covers machine operation, hygiene standards, recipe mastery, inventory control, and customer service.' ),
			array( 'Are exclusive territory rights available?', 'Yes, franchise partners receive protected geographic territory rights based on agreed postcode clusters and population demographics.' ),
		),
	),
	// History - slug 'history', section "Sugarcane & Health Questions"
	array(
		'slug'    => 'history',
		'section' => 'Sugarcane & Health Questions',
		'items'   => array(
			array( 'Is sugarcane juice safe for diabetics?', 'In moderation, and best discussed with your doctor - it is naturally sweet with no added sugar, but still a source of natural sugars.' ),
			array( 'Does sugarcane juice have calories?', "Yes, naturally - it's a whole-plant drink, not a diet product, but it has none of the artificial additives found in most soft drinks." ),
			array( 'How is your juice different?', 'Pressed fresh in front of you, from cane sourced the same day - never bottled, never stored.' ),
			array( 'Is your juice 100% natural?', 'Yes - no added sugar, colours, or preservatives, ever.' ),
			array( 'How should I store it?', "It's best enjoyed fresh, right after pressing - like any fresh juice, it doesn't keep well overnight." ),
		),
	),
);

$total_inserted = 0;

foreach ( $sets as $set ) {
	$slug    = $set['slug'];
	$section = $set['section'];

	// Idempotent re-run: clear out rows this script previously seeded for this slug+section.
	if ( null === $slug ) {
		$del = $mysqli->prepare( "DELETE FROM {$table} WHERE attached_slug IS NULL AND page_id IS NULL AND section = ?" );
		$del->bind_param( 's', $section );
	} else {
		$del = $mysqli->prepare( "DELETE FROM {$table} WHERE attached_slug = ? AND section = ?" );
		$del->bind_param( 'ss', $slug, $section );
	}
	$del->execute();
	$del->close();

	$sort_order = 0;
	foreach ( $set['items'] as $item ) {
		list( $question, $answer ) = $item;

		$stmt = $mysqli->prepare(
			"INSERT INTO {$table} (question, answer, attached_slug, section, sort_order, status) VALUES (?, ?, ?, ?, ?, 'active')"
		);
		$stmt->bind_param( 'ssssi', $question, $answer, $slug, $section, $sort_order );
		if ( ! $stmt->execute() ) {
			fwrite( STDERR, "Insert failed for \"{$question}\": {$stmt->error}\n" );
		} else {
			++$total_inserted;
		}
		$stmt->close();
		++$sort_order;
	}
}

echo "Seeded {$total_inserted} FAQ rows into {$table}.\n";
$mysqli->close();
