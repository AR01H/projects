<?php
namespace Adn\Theme\Shared;

defined( 'ABSPATH' ) || exit;

/**
 * NewsletterBuilder - Shared newsletter CTA widget builder.
 *
 * Standardizes the newsletter signup widget across all pages.
 * Uses adn_term() for i18n with SITE_* constants as fallbacks.
 */
class NewsletterBuilder {

	/**
	 * Full newsletter CTA widget.
	 *
	 * Used by: GuidesListing, Tools, TopicCategory, Home (bottom banner)
	 *
	 * @param array $overrides Optional key-value overrides for any field.
	 */
	public static function cta( array $overrides = array() ): array {
		$defaults = array(
			'icon'         => '📬',
			'title'        => self::term( 'cta.newsletter_heading', defined( 'SITE_NEWSLETTER_TITLE' ) ? SITE_NEWSLETTER_TITLE : 'Stay Informed, Stay Ahead' ),
			'description'  => self::term( 'newsletter.description', defined( 'SITE_NEWSLETTER_DESC' ) ? SITE_NEWSLETTER_DESC : 'Get the latest guides and updates delivered to your inbox.' ),
			'placeholder'  => self::term( 'placeholders.newsletter_email', defined( 'SITE_NEWSLETTER_PH' ) ? SITE_NEWSLETTER_PH : 'Your email address' ),
			'button_label' => self::term( 'buttons.subscribe', defined( 'SITE_BTN_SUBSCRIBE' ) ? SITE_BTN_SUBSCRIBE : 'Subscribe' ),
			'note'         => self::term( 'sidebar.newsletter_note', defined( 'SITE_NEWSLETTER_NOTE' ) ? SITE_NEWSLETTER_NOTE : 'No spam. Unsubscribe anytime.' ),
		);

		return array_merge( $defaults, $overrides );
	}

	/**
	 * Minimal newsletter CTA (for sidebar widgets).
	 *
	 * Used by: SidebarBuilder::newsletterCta()
	 */
	public static function sidebarCta(): array {
		return array(
			'heading'      => adn_term( 'sidebar.newsletter_heading', 'Stay Updated' ),
			'description'  => adn_term( 'sidebar.newsletter_desc', 'Get the latest guides and expert tips delivered to your inbox.' ),
			'placeholder'  => adn_term( 'sidebar.newsletter_placeholder', 'Your email address' ),
			'button_label' => adn_term( 'sidebar.newsletter_btn', 'Subscribe' ),
			'note'         => adn_term( 'sidebar.newsletter_note', 'No spam. Unsubscribe anytime.' ),
		);
	}

	/**
	 * Resolve term with constant fallback.
	 */
	private static function term( string $key, string $fallback ): string {
		return adn_term( $key, $fallback );
	}
}
