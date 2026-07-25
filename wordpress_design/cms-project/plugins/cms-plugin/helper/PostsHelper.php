<?php
defined( 'ABSPATH' ) || exit;

/**
 * Helper functions for Posts admin page.
 */
class AH_Posts_Helper {

	/**
	 * Convert sections array to Gutenberg block HTML.
	 */
	public static function sections_to_blocks( array $sections ): string {
		$out = '';
		foreach ( $sections as $s ) {
			switch ( $s['type'] ?? '' ) {
				case 'heading':
					$lvl  = in_array( (int) ( $s['level'] ?? 2 ), array( 2, 3, 4 ), true ) ? (int) $s['level'] : 2;
					$text = esc_html( $s['text'] ?? '' );
					$tag  = "h{$lvl}";
					$out .= "<!-- wp:heading {\"level\":{$lvl}} -->\n<{$tag}>{$text}</{$tag}>\n<!-- /wp:heading -->\n\n";
					break;
				case 'paragraph':
					$text = wp_kses_post( $s['text'] ?? '' );
					$out .= "<!-- wp:html -->\n{$text}\n<!-- /wp:html -->\n\n";
					break;
				case 'list':
					$items = array_values( array_filter( (array) ( $s['items'] ?? array() ) ) );
					if ( $items ) {
						$ordered = ! empty( $s['ordered'] );
						$tag     = $ordered ? 'ol' : 'ul';
						$attr    = $ordered ? ' {"ordered":true}' : '';
						$lis     = implode( '', array_map( function ( $i ) { return '<li>' . esc_html( $i ) . '</li>'; }, $items ) );
						$out    .= "<!-- wp:list{$attr} -->\n<{$tag}>{$lis}</{$tag}>\n<!-- /wp:list -->\n\n";
					}
					break;
				case 'image':
					$url = esc_url( $s['url'] ?? '' );
					if ( $url ) {
						$alt = esc_attr( $s['alt'] ?? '' );
						$out .= "<!-- wp:image -->\n<figure><img src=\"{$url}\" alt=\"{$alt}\"/></figure>\n<!-- /wp:image -->\n\n";
					}
					break;
				case 'html':
					$html = wp_kses_post( $s['html'] ?? '' );
					if ( $html ) {
						$out .= "<!-- wp:html -->\n{$html}\n<!-- /wp:html -->\n\n";
					}
					break;
			}
		}
		return $out;
	}

	/**
	 * Get post template definitions.
	 */
	public static function post_templates(): array {
		return array(
			'blog' => array(
				'label'   => 'Blog Post',
				'icon'    => '✍️',
				'desc'    => 'Standard blog post with intro, body sections and conclusion',
				'excerpt' => 'A short summary of what this post covers - edit to match your topic.',
				'fields'  => array(
					array( 'type' => 'text',     'name' => 'post_title',    'label' => 'Post Title',    'placeholder' => 'My Blog Post Title',  'required' => true ),
					array( 'type' => 'textarea', 'name' => 'post_excerpt',  'label' => 'Short Summary', 'hint' => 'shown in listings',          'rows' => 2, 'placeholder' => 'A short summary of what this post covers...' ),
					array( 'type' => 'category', 'name' => 'post_category', 'label' => 'Category' ),
				),
			),
			'news' => array(
				'label'   => 'News Article',
				'icon'    => '📰',
				'desc'    => 'News-style format with headline, lead paragraph and body',
				'excerpt' => 'Brief summary of the news - who, what, when, where.',
				'fields'  => array(
					array( 'type' => 'text',     'name' => 'post_title',     'label' => 'Headline',       'placeholder' => 'Company News: Your Headline Here', 'required' => true ),
					array( 'type' => 'textarea', 'name' => 'lead_paragraph', 'label' => 'Lead Paragraph', 'hint' => 'who, what, when, where', 'rows' => 3, 'placeholder' => 'Summarise the key news in 1-2 sentences...' ),
				),
			),
			'guide' => array(
				'label'   => 'Step-by-Step Guide',
				'icon'    => '📋',
				'desc'    => 'How-to guide with numbered steps and tips',
				'excerpt' => 'A complete guide to help you understand and navigate the process.',
				'fields'  => array(
					array( 'type' => 'text',   'name' => 'post_title', 'label' => 'Guide Title',     'placeholder' => 'How to Get Started With...', 'required' => true ),
					array( 'type' => 'number', 'name' => 'step_count', 'label' => 'Number of Steps', 'min' => 2, 'max' => 10, 'default' => 3 ),
				),
			),
			'casestudy' => array(
				'label'   => 'Client Story / Case Study',
				'icon'    => '🏆',
				'desc'    => 'Client success story with challenge, solution and result',
				'excerpt' => 'How we helped a client achieve their goal - read the full story.',
				'fields'  => array(
					array( 'type' => 'text',     'name' => 'post_title',   'label' => 'Case Study Title', 'placeholder' => 'How We Helped [Client] Achieve [Goal]', 'required' => true ),
					array( 'type' => 'text',     'name' => 'client_name',  'label' => 'Client Name',      'placeholder' => 'e.g. Jane Smith, Melbourne' ),
					array( 'type' => 'textarea', 'name' => 'client_quote', 'label' => 'Client Quote',     'rows' => 2, 'placeholder' => '"The team made the whole process so easy..."' ),
				),
			),
			'faq' => array(
				'label'   => 'FAQ / Q&amp;A Post',
				'icon'    => '❓',
				'desc'    => 'Frequently asked questions format',
				'excerpt' => 'Answers to common questions about your product or service.',
				'fields'  => array(
					array( 'type' => 'text',     'name' => 'post_title', 'label' => 'FAQ Title',     'placeholder' => 'Frequently Asked Questions About...', 'required' => true ),
					array( 'type' => 'text',     'name' => 'faq_topic',  'label' => 'FAQ Topic',     'placeholder' => 'e.g. Installation, Pricing' ),
					array( 'type' => 'number',   'name' => 'faq_count',  'label' => 'Number of FAQs', 'min' => 3, 'max' => 20, 'default' => 5 ),
				),
			),
		);
	}

	/**
	 * Generate guide content from template.
	 */
	public static function generate_guide_content( int $count ): string {
		$sections = self::template_default_sections( 'guide', array( 'step_count' => $count ) );
		return self::sections_to_blocks( $sections );
	}

	/**
	 * Generate FAQ content from template.
	 */
	public static function generate_faq_content( string $topic, int $count ): string {
		$sections = self::template_default_sections( 'faq', array( 'faq_topic' => $topic, 'faq_count' => $count ) );
		return self::sections_to_blocks( $sections );
	}

	/**
	 * Get default sections for a template.
	 */
	public static function template_default_sections( string $tpl_key, array $overrides = array() ): array {
		$sections = array();
		switch ( $tpl_key ) {
			case 'guide':
				$count = $overrides['step_count'] ?? 3;
				$sections[] = array( 'type' => 'heading', 'text' => 'Getting Started', 'level' => 2 );
				$sections[] = array( 'type' => 'paragraph', 'text' => 'Follow these steps to complete the process.' );
				for ( $i = 1; $i <= $count; $i++ ) {
					$sections[] = array( 'type' => 'heading', 'text' => "Step {$i}", 'level' => 3 );
					$sections[] = array( 'type' => 'paragraph', 'text' => "Describe step {$i} here." );
				}
				break;
			case 'faq':
				$topic = $overrides['faq_topic'] ?? 'this topic';
				$count = $overrides['faq_count'] ?? 5;
				$sections[] = array( 'type' => 'heading', 'text' => "Frequently Asked Questions about {$topic}", 'level' => 2 );
				for ( $i = 1; $i <= $count; $i++ ) {
					$sections[] = array( 'type' => 'heading', 'text' => "Question {$i}?", 'level' => 3 );
					$sections[] = array( 'type' => 'paragraph', 'text' => "Answer to question {$i}." );
				}
				break;
		}
		return $sections;
	}
}
