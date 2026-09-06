<?php
namespace VintageSoul\Services\Plugins;

defined( 'ABSPATH' ) || exit;

/**
 * FaqBridgeService — Common bridge for every FAQ list in the theme.
 *
 * Reads from the CMS FAQ Builder (admin.php?page=ah-faqs&action=add) via its
 * AH_Faqs_Model, using the same two fields that admin form exposes:
 * - "Attached To" a page route slug (contact, events, franchise, history,
 *   …), or left on "Global" for no slug - used for the homepage's list.
 * - "Section" - a free-text label (e.g. "Common Questions", "Buying
 *   Questions") grouping FAQs within that slug/global scope. Optional;
 *   pass '' to ignore it and take every FAQ in that slug/global scope.
 *
 * Both the slug and the section to filter by are supplied by the caller
 * (read from that page's own JSON, never hard-coded here) so which CMS
 * bucket feeds a given page stays configurable in content, not code.
 *
 * Every *.json content file's "items" list is the fallback only - it stays
 * empty until this returns nothing, so the CMS admin is the source of
 * truth for FAQ content going forward. See data/content/faqs.json for the
 * fallback-file convention this follows.
 */
class FaqBridgeService {

	/**
	 * Get FAQ items for a page by its attached slug (or the global list
	 * when no slug is given), optionally narrowed to one "Section" label.
	 * Mapped to the shape faq/faq.php and sections/faq-section.php expect:
	 * { question, answer }.
	 *
	 * @return array<int, array{question: string, answer: string}>
	 */
	public static function get_items( string $slug = '', string $section = '' ): array {
		if ( ! class_exists( 'AH_Faqs_Model' ) ) {
			return array();
		}

		try {
			$model = new \AH_Faqs_Model();
			$rows  = '' !== $slug ? $model->get_by_slug( $slug ) : $model->get_global();
		} catch ( \Throwable $e ) {
			return array();
		}

		$rows = (array) $rows;

		if ( '' !== $section ) {
			$rows = array_filter(
				$rows,
				static function ( $row ) use ( $section ) {
					return trim( (string) ( ( (object) $row )->section ?? '' ) ) === trim( $section );
				}
			);
		}

		$items = array();
		foreach ( $rows as $row ) {
			$row = (object) $row;
			$q   = trim( (string) ( $row->question ?? '' ) );
			$a   = trim( (string) ( $row->answer ?? '' ) );
			if ( '' === $q || '' === $a ) {
				continue;
			}
			$items[] = array( 'question' => $q, 'answer' => $a );
		}

		return $items;
	}
}
