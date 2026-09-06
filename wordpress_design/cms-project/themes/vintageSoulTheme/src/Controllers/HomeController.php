<?php
namespace VintageSoul\Controllers;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\NavigationService;
use VintageSoul\Services\TerminologyService;
use VintageSoul\Services\TestimonialService;

defined( 'ABSPATH' ) || exit;

final class HomeController {

	private const SECTIONS = array(
		'ticker'           => 'ticker.json',
		'intro'            => 'intro.json',
		'stats'            => 'stats.json',
		'story'            => 'story.json',
		'sourcing'         => 'sourcing.json',
		'products'         => 'products.json',
		'certifications'   => 'certifications.json',
		'benefits'         => 'health-benefits.json',
		'serve_steps'      => 'serve-steps.json',
		'gallery'          => 'gallery.json',
		'memories'         => 'memories.json',
		'order_steps'      => 'order-steps.json',
		'events'           => 'events-features.json',
		'franchise_teaser' => 'franchise-teaser.json',
		'franchise'        => 'franchise.json',
		'showcases'        => 'showcases.json',
		'community'        => 'community.json',
		'logo_strip'       => 'logo-strip.json',
		'combo_upsell'     => 'combo-upsell.json',
		'video_showcase'   => 'video-showcase.json',
		'enquiry'          => 'enquiry-prompt.json',
		'faqs'             => 'faqs.json',
		'contact'          => 'contact-info.json',
		'closing'          => 'closing-quote.json',
	);

	public function prepare(): array {
		$data = array(
			'nav'                => NavigationService::menu( 'primary' ),
			'hero'               => $this->prepare_hero(),
			'testimonials'       => JsonFileProvider::read( 'data/content/testimonials.json' ),
			'testimonials_title' => TerminologyService::label( 'testimonials_section_title' ),
			'testimonials_tag'   => TerminologyService::label( 'testimonials_section_tag' ),
			'testimonials_meta'  => JsonFileProvider::read( 'data/content/testimonials.json' ),
		);

		foreach ( self::SECTIONS as $key => $file ) {
			$data[ $key ] = JsonFileProvider::read( 'data/content/' . $file );
		}

		// FAQ list: CMS FAQ Builder (admin.php?page=ah-faqs, left on "Global",
		// Section per faqs.json's "section") first, falls back to faqs.json's
		// items. Which section to pull is config (from the JSON), not code.
		$faqs_section = (string) ( $data['faqs']['section'] ?? '' );
		$faqs_cms     = \VintageSoul\Services\Plugins\FaqBridgeService::get_items( '', $faqs_section );
		if ( ! empty( $faqs_cms ) ) {
			$data['faqs']['items'] = $faqs_cms;
		}

		// Dynamic contact information resolved from CMS Site Settings DB table (wp_ch_ah_site_settings)
		$data['contact'] = array(
			'title'        => (string) ( $data['contact']['title'] ?? "Let's Connect" ),
			'tagline'      => \VintageSoul\Services\SettingsService::tagline_fallback(),
			'phone'        => \VintageSoul\Services\SettingsService::phone(),
			'email'        => \VintageSoul\Services\SettingsService::email(),
			'whatsapp'     => \VintageSoul\Services\SettingsService::whatsapp(),
			'whatsapp_url' => \VintageSoul\Services\SettingsService::whatsapp_url(),
			'address'      => \VintageSoul\Services\SettingsService::address(),
			'hours'        => \VintageSoul\Services\SettingsService::opening_hours(),
			'socials'      => \VintageSoul\Services\SettingsService::socials(),
			'social_links' => \VintageSoul\Services\SettingsService::socials(),
		);

		return $data;
	}

	private function prepare_hero(): array {
		return \VintageSoul\Services\Plugins\BannerBridgeService::get_hero();
	}
}
