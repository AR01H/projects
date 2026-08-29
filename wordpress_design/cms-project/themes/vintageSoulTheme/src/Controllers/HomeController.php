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
			'testimonials'       => ( new TestimonialService() )->featured(),
			'testimonials_title' => TerminologyService::label( 'testimonials_section_title' ),
			'testimonials_tag'   => TerminologyService::label( 'testimonials_section_tag' ),
			'testimonials_meta'  => JsonFileProvider::read( 'data/content/testimonials.json' ),
		);

		foreach ( self::SECTIONS as $key => $file ) {
			$data[ $key ] = JsonFileProvider::read( 'data/content/' . $file );
		}

		return $data;
	}

	private function prepare_hero(): array {
		$data   = JsonFileProvider::read( 'data/content/hero.json' );
		$slides = is_array( $data['slides'] ?? null ) ? $data['slides'] : array();

		foreach ( $slides as &$slide ) {
			$slide          = (array) $slide;
			$slide['media'] = $this->resolve_media_urls( (array) ( $slide['media'] ?? array() ) );
		}
		unset( $slide );

		return array(
			'enabled'  => ! empty( $data['enabled'] ) && ! empty( $slides ),
			'settings' => (array) ( $data['settings'] ?? array() ),
			'slides'   => $slides,
		);
	}

	/**
	 * A theme-relative path (e.g. "assets/videos/hero_bg.mp4") becomes a real
	 * URL here; anything already starting with "http" is left untouched -
	 * same rule FranchiseController applies to its own hero image.
	 */
	private function resolve_media_urls( array $media ): array {
		foreach ( array( 'src', 'mobile_src', 'poster' ) as $key ) {
			$value = (string) ( $media[ $key ] ?? '' );
			if ( '' !== $value && 0 !== strpos( $value, 'http' ) ) {
				$media[ $key ] = VINTAGESOUL_URI . '/' . ltrim( $value, '/' );
			}
		}
		return $media;
	}
}
