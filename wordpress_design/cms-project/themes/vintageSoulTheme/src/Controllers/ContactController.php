<?php
namespace VintageSoul\Controllers;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\PluginBridgeService;
use VintageSoul\Services\Plugins\FaqBridgeService;
use VintageSoul\Services\Plugins\PageBridgeService;
use VintageSoul\Services\SettingsService;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

final class ContactController {

	public function prepare(): array {
		$data = JsonFileProvider::read( 'data/content/contact-page.json' );
		if ( empty( $data ) ) {
			$data = JsonFileProvider::read( 'data/content/contact-info.json' );
		}

		$hero_raw = (array) ( $data['hero'] ?? array() );
		if ( ! empty( $hero_raw['image'] ) ) {
			$hero_raw['image'] = UrlHelper::resolve( (string) $hero_raw['image'] );
		}

		$hero = PageBridgeService::resolve_hero( 'contact', $hero_raw );

		$form_cfg      = (array) ( $data['form'] ?? array() );
		$form_key      = (string) ( $form_cfg['key'] ?? 'contact' );
		$rendered_form = PluginBridgeService::render_form( $form_key );
		$plugin_cfg    = PluginBridgeService::get_form_config( $form_key );

		$raw_info = (array) ( $data['contact_info'] ?? array() );

		// FAQ list: CMS FAQ Builder (admin.php?page=ah-faqs, attached to slug
		// "contact", Section per contact-page.json's "faqs_section") first,
		// falls back to contact-page.json's flat faqs array.
		$faqs_section = (string) ( $data['faqs_section'] ?? '' );
		$faqs_cms     = FaqBridgeService::get_items( 'contact', $faqs_section );
		$faqs         = ! empty( $faqs_cms ) ? $faqs_cms : (array) ( $data['faqs'] ?? array() );

		// Dynamic contact information resolved from CMS Site Settings DB table (wp_ch_ah_site_settings)
		// with multi-tier JSON fallback (contact-page.json & contact-info.json)
		$contact_info = array(
			'phone'        => SettingsService::phone() ?: (string) ( $raw_info['phone'] ?? '' ),
			'email'        => SettingsService::email() ?: (string) ( $raw_info['email'] ?? '' ),
			'whatsapp'     => SettingsService::whatsapp() ?: (string) ( $raw_info['whatsapp'] ?? '' ),
			'whatsapp_url' => SettingsService::whatsapp_url() ?: (string) ( $raw_info['whatsapp_url'] ?? '' ),
			'address'      => SettingsService::address() ?: (string) ( $raw_info['address'] ?? '' ),
			'hours'        => SettingsService::opening_hours() ?: (string) ( $raw_info['hours'] ?? '' ),
			'maps_url'     => SettingsService::google_maps_url() ?: (string) ( $raw_info['maps_url'] ?? '' ),
			'socials'      => ! empty( SettingsService::socials() ) ? SettingsService::socials() : (array) ( $raw_info['socials'] ?? array() ),
		);

		return array(
			'hero'          => $hero,
			'contact_info'  => $contact_info,
			'event_bar'     => (array) ( $data['event_bar'] ?? array() ),
			'enquiry_types' => (array) ( $data['enquiry_types'] ?? array() ),
			'faqs'          => $faqs,
			'form'          => array_merge( $plugin_cfg, $form_cfg ),
			'rendered_form' => $rendered_form,
		);
	}
}
