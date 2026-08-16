<?php
namespace VintageSoul\Controllers;

use VintageSoul\Services\NavigationService;
use VintageSoul\Services\TerminologyService;
use VintageSoul\Services\TestimonialService;

defined( 'ABSPATH' ) || exit;

final class HomeController {

	public function prepare(): array {
		return array(
			'nav'                      => NavigationService::menu( 'primary' ),
			'testimonials'             => ( new TestimonialService() )->featured(),
			'testimonials_title'       => TerminologyService::label( 'testimonials_section_title' ),
			'testimonials_tag'         => TerminologyService::label( 'testimonials_section_tag' ),
		);
	}
}
