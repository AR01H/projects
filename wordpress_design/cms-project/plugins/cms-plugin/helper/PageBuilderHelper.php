<?php
defined( 'ABSPATH' ) || exit;

/**
 * Helper functions for Page Builder admin page.
 */
class AH_Page_Builder_Helper {

	/**
	 * Get page builder template presets.
	 */
	public static function templates(): array {
		return array(
			'faq' => array(
				'label' => 'FAQ Page', 'icon' => '❓', 'desc' => 'Hero + accordion + links + CTA',
				'blocks' => array(
					array( 'type' => 'hero', 'data' => array( 'heading' => 'Frequently Asked Questions', 'subheading' => 'Find answers to the most common property questions.', 'bg' => 'light' ) ),
					array( 'type' => 'faq', 'data' => array( 'heading' => 'Buying a Property', 'items' => array(
						array( 'q' => 'How long does buying a property take?', 'a' => 'The average property purchase takes 8-12 weeks from offer to completion.' ),
						array( 'q' => 'Do I need a solicitor?', 'a' => 'Yes, a conveyancing solicitor is required to handle the legal transfer of ownership.' ),
						array( 'q' => 'What is stamp duty?', 'a' => 'Stamp Duty Land Tax (SDLT) is a tax payable on property purchases above £250,000.' ),
					) ) ),
					array( 'type' => 'faq', 'data' => array( 'heading' => 'Selling a Property', 'items' => array(
						array( 'q' => 'How do I value my property?', 'a' => 'We offer free, accurate market valuations based on comparable sales.' ),
						array( 'q' => 'What fees are involved in selling?', 'a' => 'Typical costs include estate agent fees (1-3%), conveyancing, and mortgage charges.' ),
					) ) ),
					array( 'type' => 'cta_banner', 'data' => array( 'heading' => 'Still Have Questions?', 'text' => 'Speak to one of our experts for personalised advice.', 'btn1_text' => 'Book Free Call', 'btn1_url' => '/free-consultation/', 'theme' => 'dark' ) ),
				),
			),
			'guide' => array(
				'label' => 'Guide / Article', 'icon' => '📖', 'desc' => 'Hero + rich text + links + CTA',
				'blocks' => array(
					array( 'type' => 'hero', 'data' => array( 'heading' => 'First-Time Buyers Guide', 'subheading' => 'Everything you need to know about buying your first home.', 'bg' => 'light' ) ),
					array( 'type' => 'text_block', 'data' => array( 'content' => '<p>This guide walks you through every stage of buying your first home.</p>' ) ),
					array( 'type' => 'links_list', 'data' => array( 'heading' => 'Related Guides', 'cols' => '2', 'links' => array(
						array( 'label' => 'Stamp Duty Guide', 'url' => '/guides/stamp-duty/', 'icon' => '💷', 'desc' => 'How much will you pay?' ),
						array( 'label' => 'Mortgage Guide', 'url' => '/guides/mortgages/', 'icon' => '🏦', 'desc' => 'Types, rates and how to apply' ),
					) ) ),
					array( 'type' => 'cta_banner', 'data' => array( 'heading' => 'Need Help?', 'text' => 'Our experts are happy to answer your questions.', 'btn1_text' => 'Book Consultation', 'btn1_url' => '/free-consultation/', 'theme' => 'gold' ) ),
				),
			),
		);
	}
}
