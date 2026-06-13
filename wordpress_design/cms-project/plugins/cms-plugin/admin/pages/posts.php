<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$notice = '';

// ── Sections array → Gutenberg block HTML ─────────────────────────────────────
function ah_sections_to_blocks( array $sections ): string {
	$out = '';
	foreach ( $sections as $s ) {
		switch ( $s['type'] ?? '' ) {
			case 'heading':
				$lvl  = in_array( (int) ( $s['level'] ?? 2 ), [ 2, 3, 4 ], true ) ? (int) $s['level'] : 2;
				$text = esc_html( $s['text'] ?? '' );
				$tag  = "h{$lvl}";
				$out .= "<!-- wp:heading {\"level\":{$lvl}} -->\n<{$tag}>{$text}</{$tag}>\n<!-- /wp:heading -->\n\n";
				break;
			case 'paragraph':
				$text = wp_kses_post( $s['text'] ?? '' );
				$out .= "<!-- wp:html -->\n{$text}\n<!-- /wp:html -->\n\n";
				break;
			case 'list':
				$items = array_values( array_filter( (array) ( $s['items'] ?? [] ) ) );
				if ( $items ) {
					$ordered = ! empty( $s['ordered'] );
					$tag     = $ordered ? 'ol' : 'ul';
					$attr    = $ordered ? ' {"ordered":true}' : '';
					$lis     = implode( '', array_map( fn( $i ) => '<li>' . esc_html( $i ) . '</li>', $items ) );
					$out    .= "<!-- wp:list{$attr} -->\n<{$tag}>{$lis}</{$tag}>\n<!-- /wp:list -->\n\n";
				}
				break;
			case 'table':
				$headers = (array) ( $s['headers'] ?? [] );
				$rows    = (array) ( $s['rows'] ?? [] );
				$thead   = '';
				if ( $headers ) {
					$ths   = implode( '', array_map( fn( $h ) => '<th>' . esc_html( $h ) . '</th>', $headers ) );
					$thead = "<thead><tr>{$ths}</tr></thead>";
				}
				$tbody_rows = '';
				foreach ( $rows as $row ) {
					$tds         = implode( '', array_map( fn( $c ) => '<td>' . esc_html( $c ) . '</td>', (array) $row ) );
					$tbody_rows .= "<tr>{$tds}</tr>";
				}
				$tbody = $tbody_rows ? "<tbody>{$tbody_rows}</tbody>" : '';
				if ( $thead || $tbody ) {
					$out .= "<!-- wp:table -->\n<figure class=\"wp-block-table\"><table>{$thead}{$tbody}</table></figure>\n<!-- /wp:table -->\n\n";
				}
				break;
			case 'quote':
				$text     = wp_kses_post( $s['text'] ?? '' );
				$cite     = esc_html( $s['cite'] ?? '' );
				$cite_tag = $cite ? "<cite>- {$cite}</cite>" : '';
				$out     .= "<!-- wp:html -->\n<blockquote class=\"wp-block-quote\">{$text}{$cite_tag}</blockquote>\n<!-- /wp:html -->\n\n";
				break;
			case 'cta':
				$text = esc_html( $s['text'] ?? 'Learn More' );
				$url  = esc_url( $s['url'] ?? '#' );
				$out .= "<!-- wp:buttons -->\n<div class=\"wp-block-buttons\"><!-- wp:button -->\n<div class=\"wp-block-button\"><a class=\"wp-block-button__link\" href=\"{$url}\">{$text}</a></div>\n<!-- /wp:button --></div>\n<!-- /wp:buttons -->\n\n";
				break;
		}
	}
	return trim( $out );
}

// ── Default sections per template type ────────────────────────────────────────
function ah_template_default_sections( string $tpl_key, array $overrides = [] ): array {
	switch ( $tpl_key ) {
		case 'blog':
			return [
				[ 'type' => 'paragraph', 'text' => "Start with a strong opening paragraph that hooks the reader and tells them what they'll learn from this post." ],
				[ 'type' => 'heading',   'level' => '2', 'text' => 'Main Section Heading' ],
				[ 'type' => 'paragraph', 'text' => 'Expand on your main point here. Use clear, simple language and back it up with examples or data.' ],
				[ 'type' => 'heading',   'level' => '2', 'text' => 'Key Takeaways' ],
				[ 'type' => 'list',      'ordered' => false, 'items' => [ 'First important point from this post', 'Second important point', 'Third important point' ] ],
				[ 'type' => 'cta',       'text' => 'Book a Free Consultation', 'url' => '/free-consultation/' ],
			];
		case 'news':
			$lead = $overrides['lead_paragraph'] ?? 'Summarise the key news in one or two sentences. Cover who, what, when, and why it matters.';
			return [
				[ 'type' => 'paragraph', 'text' => $lead ],
				[ 'type' => 'paragraph', 'text' => 'Expand on the story here. Provide context, background, and additional detail that readers need to understand the news fully.' ],
				[ 'type' => 'heading',   'level' => '2', 'text' => 'What This Means for You' ],
				[ 'type' => 'paragraph', 'text' => 'Explain the practical impact for your audience. How does this news affect them directly?' ],
				[ 'type' => 'cta',       'text' => 'Contact Us Today', 'url' => '/contact/' ],
			];
		case 'guide':
			$count    = max( 2, min( 10, (int) ( $overrides['step_count'] ?? 3 ) ) );
			$sections = [ [ 'type' => 'paragraph', 'text' => "This guide walks you through the complete process step by step. Follow these steps for the best results." ] ];
			for ( $i = 1; $i <= $count; $i++ ) {
				if ( $i === 1 )          $title = 'Get Prepared';
				elseif ( $i === $count ) $title = 'Review and Confirm';
				else                     $title = 'Take Action';
				$sections[] = [ 'type' => 'heading',   'level' => '2', 'text' => "Step {$i}: {$title}" ];
				$sections[] = [ 'type' => 'paragraph', 'text' => "Describe step {$i} in detail here. Include any tips or warnings the reader should know." ];
			}
			$sections[] = [ 'type' => 'cta', 'text' => 'Speak to an Expert', 'url' => '/free-consultation/' ];
			return $sections;
		case 'casestudy':
			$name  = $overrides['client_name']  ?? 'Client Name, Location';
			$quote = $overrides['client_quote'] ?? 'Add a genuine client quote here - it builds trust and makes the story real.';
			return [
				[ 'type' => 'heading',   'level' => '2', 'text' => 'The Challenge' ],
				[ 'type' => 'paragraph', 'text' => "Describe the client's situation and what problem they were facing. What made it difficult?" ],
				[ 'type' => 'heading',   'level' => '2', 'text' => 'Our Approach' ],
				[ 'type' => 'paragraph', 'text' => 'Explain how you stepped in and what you did differently. What specific action made the difference?' ],
				[ 'type' => 'heading',   'level' => '2', 'text' => 'The Result' ],
				[ 'type' => 'paragraph', 'text' => 'Share the outcome - use specific numbers or outcomes where possible.' ],
				[ 'type' => 'quote',     'text' => $quote, 'cite' => $name ],
				[ 'type' => 'cta',       'text' => 'Book Your Free Consultation', 'url' => '/free-consultation/' ],
			];
		case 'faq':
			$topic    = $overrides['faq_topic'] ?? '';
			$count    = max( 2, min( 10, (int) ( $overrides['faq_count'] ?? 3 ) ) );
			$intro    = 'We get asked these questions' . ( $topic ? ' about ' . $topic : '' ) . ' all the time. Here are clear, honest answers.';
			$sections = [ [ 'type' => 'paragraph', 'text' => $intro ] ];
			for ( $i = 1; $i <= $count; $i++ ) {
				$sections[] = [ 'type' => 'heading',   'level' => '2', 'text' => "Q: Add your question {$i} here?" ];
				$sections[] = [ 'type' => 'paragraph', 'text' => 'A: Provide a clear, helpful answer here. Avoid jargon and speak directly to what the reader needs to know.' ];
			}
			$sections[] = [ 'type' => 'cta', 'text' => 'Get in Touch', 'url' => '/contact/' ];
			return $sections;
		default:
			return [];
	}
}

// ── Render a section card (PHP - for both new and loaded sections) ─────────────
function ah_render_section_card( array $s, bool $first = false, bool $last = false ): void {
	$type   = $s['type'] ?? '';
	$labels = [
		'heading'   => '📝 Heading',
		'paragraph' => '¶ Paragraph',
		'list'      => '• List',
		'table'     => '⊞ Table',
		'quote'     => '" Quote',
		'cta'       => '⬡ CTA Button',
	];
	$label  = $labels[ $type ] ?? $type;
	$cell   = 'padding:4px;border:1px solid #cbd5e1;';
	?>
	<div class="ah-card ah-section-card" data-type="<?php echo esc_attr( $type ); ?>" style="margin-bottom:12px;padding:16px;">
	  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
	    <strong style="font-size:.85rem;"><?php echo esc_html( $label ); ?></strong>
	    <div>
	      <button type="button" class="ah-sec-up ah-btn ah-btn-secondary ah-btn-sm"<?php echo $first ? ' disabled' : ''; ?>>↑</button>
	      <button type="button" class="ah-sec-dn ah-btn ah-btn-secondary ah-btn-sm"<?php echo $last ? ' disabled' : ''; ?>>↓</button>
	      <button type="button" class="ah-sec-rm ah-btn ah-btn-danger ah-btn-sm" style="margin-left:4px;">✕ Remove</button>
	    </div>
	  </div>
	  <div class="ah-section-fields">
	    <?php
	    switch ( $type ) {
	        case 'heading': ?>
	            <div style="display:flex;gap:8px;">
	              <select class="ah-sec-level" style="width:90px;">
	                <?php foreach ( [ 2, 3, 4 ] as $l ) : ?>
	                  <option value="<?php echo $l; ?>" <?php selected( (int) ( $s['level'] ?? 2 ), $l ); ?>>H<?php echo $l; ?></option>
	                <?php endforeach; ?>
	              </select>
	              <input type="text" class="ah-sec-text" value="<?php echo esc_attr( $s['text'] ?? '' ); ?>" placeholder="Section heading…" style="flex:1;box-sizing:border-box;">
	            </div>
	        <?php break;

	        case 'paragraph': ?>
	            <textarea class="ah-sec-text" rows="4" placeholder="Write your paragraph here…" style="width:100%;box-sizing:border-box;"><?php echo esc_textarea( $s['text'] ?? '' ); ?></textarea>
	        <?php break;

	        case 'list':
	            $ordered = ! empty( $s['ordered'] );
	            $items   = (array) ( $s['items'] ?? [ '' ] );
	            ?>
	            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
	              <label style="font-size:.8rem;font-weight:600;">Type:</label>
	              <select class="ah-sec-list-type">
	                <option value="0" <?php echo ! $ordered ? 'selected' : ''; ?>>• Bullet</option>
	                <option value="1" <?php echo $ordered ? 'selected' : ''; ?>>1. Numbered</option>
	              </select>
	            </div>
	            <div class="ah-list-items">
	              <?php foreach ( $items as $item ) : ?>
	                <div class="ah-list-item" style="display:flex;gap:6px;margin-bottom:6px;">
	                  <input type="text" class="ah-list-item-text" value="<?php echo esc_attr( $item ); ?>" placeholder="List item…" style="flex:1;box-sizing:border-box;">
	                  <button type="button" class="ah-list-rm ah-btn ah-btn-danger ah-btn-sm">✕</button>
	                </div>
	              <?php endforeach; ?>
	            </div>
	            <button type="button" class="ah-list-add ah-btn ah-btn-secondary ah-btn-sm" style="margin-top:4px;">+ Add Item</button>
	        <?php break;

	        case 'table':
	            $headers = (array) ( $s['headers'] ?? [ 'Column 1', 'Column 2' ] );
	            $rows    = (array) ( $s['rows'] ?? [ [ '', '' ] ] );
	            ?>
	            <div style="margin-bottom:8px;">
	              <button type="button" class="ah-table-add-row ah-btn ah-btn-secondary ah-btn-sm">+ Row</button>
	              <button type="button" class="ah-table-add-col ah-btn ah-btn-secondary ah-btn-sm" style="margin-left:6px;">+ Column</button>
	            </div>
	            <div style="overflow-x:auto;">
	              <table class="ah-table-editor" style="border-collapse:collapse;width:100%;">
	                <thead>
	                  <tr>
	                    <?php foreach ( $headers as $h ) : ?>
	                      <th style="<?php echo $cell; ?>background:#f8fafc;"><input type="text" value="<?php echo esc_attr( $h ); ?>" placeholder="Header" style="width:100%;min-width:80px;box-sizing:border-box;"></th>
	                    <?php endforeach; ?>
	                  </tr>
	                </thead>
	                <tbody>
	                  <?php foreach ( $rows as $row ) : ?>
	                    <tr>
	                      <?php foreach ( (array) $row as $cell_val ) : ?>
	                        <td style="<?php echo $cell; ?>"><input type="text" value="<?php echo esc_attr( $cell_val ); ?>" placeholder="Cell" style="width:100%;min-width:80px;box-sizing:border-box;"></td>
	                      <?php endforeach; ?>
	                    </tr>
	                  <?php endforeach; ?>
	                </tbody>
	              </table>
	            </div>
	        <?php break;

	        case 'quote': ?>
	            <textarea class="ah-sec-text" rows="3" placeholder="Quote text…" style="width:100%;box-sizing:border-box;margin-bottom:8px;font-style:italic;"><?php echo esc_textarea( $s['text'] ?? '' ); ?></textarea>
	            <input type="text" class="ah-sec-cite" value="<?php echo esc_attr( $s['cite'] ?? '' ); ?>" placeholder="Attribution / Author (optional)" style="width:100%;box-sizing:border-box;">
	        <?php break;

	        case 'cta': ?>
	            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
	              <div>
	                <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Button Text</label>
	                <input type="text" class="ah-sec-cta-text" value="<?php echo esc_attr( $s['text'] ?? '' ); ?>" placeholder="Book a Free Consultation" style="width:100%;box-sizing:border-box;">
	              </div>
	              <div>
	                <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">URL</label>
	                <input type="text" class="ah-sec-cta-url" value="<?php echo esc_attr( $s['url'] ?? '' ); ?>" placeholder="/contact/" style="width:100%;box-sizing:border-box;">
	              </div>
	            </div>
	        <?php break;
	    }
	    ?>
	  </div>
	</div>
	<?php
}

// ── Helpers used by template POST ─────────────────────────────────────────────
function ah_generate_guide_content( int $count ): string {
	return ah_sections_to_blocks( ah_template_default_sections( 'guide', [ 'step_count' => $count ] ) );
}
function ah_generate_faq_content( string $topic, int $count ): string {
	return ah_sections_to_blocks( ah_template_default_sections( 'faq', [ 'faq_topic' => $topic, 'faq_count' => $count ] ) );
}

// ── Post templates ────────────────────────────────────────────────────────────
function ah_post_templates(): array {
	return array(
		'blog' => array(
			'label'   => 'Blog Post',
			'icon'    => '✍️',
			'desc'    => 'Standard blog post with intro, body sections and conclusion',
			'excerpt' => 'A short summary of what this post covers - edit to match your topic.',
			'fields'  => array(
				array( 'type' => 'text',     'name' => 'post_title',    'label' => 'Post Title',    'placeholder' => 'My Blog Post Title',  'required' => true ),
				array( 'type' => 'textarea', 'name' => 'post_excerpt',  'label' => 'Short Summary', 'hint' => 'shown in listings',          'rows' => 2, 'placeholder' => 'A short summary of what this post covers…' ),
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
				array( 'type' => 'textarea', 'name' => 'lead_paragraph', 'label' => 'Lead Paragraph', 'hint' => 'who, what, when, where', 'rows' => 3, 'placeholder' => 'Summarise the key news in 1–2 sentences…' ),
			),
		),
		'guide' => array(
			'label'   => 'Step-by-Step Guide',
			'icon'    => '📋',
			'desc'    => 'How-to guide with numbered steps and tips',
			'excerpt' => 'A complete guide to help you understand and navigate the process.',
			'fields'  => array(
				array( 'type' => 'text',   'name' => 'post_title', 'label' => 'Guide Title',     'placeholder' => 'How to Get Started With…', 'required' => true ),
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
				array( 'type' => 'textarea', 'name' => 'client_quote', 'label' => 'Client Quote',     'rows' => 2, 'placeholder' => '"The team made the whole process so easy…"' ),
			),
		),
		'faq' => array(
			'label'   => 'FAQ / Q&amp;A Post',
			'icon'    => '❓',
			'desc'    => 'Question and answer format - great for SEO',
			'excerpt' => 'Answers to the most common questions about this topic.',
			'fields'  => array(
				array( 'type' => 'text',   'name' => 'post_title', 'label' => 'Post Title',          'placeholder' => 'Common Questions About…', 'required' => true ),
				array( 'type' => 'text',   'name' => 'faq_topic',  'label' => 'Topic / Subject',     'placeholder' => 'e.g. first home buyer grants' ),
				array( 'type' => 'number', 'name' => 'faq_count',  'label' => 'Number of Q&A Pairs', 'min' => 2, 'max' => 10, 'default' => 3 ),
			),
		),
	);
}

function ah_render_template_field( array $f ): void {
	$style = 'width:100%;box-sizing:border-box;';
	?>
	<div class="ah-form-row" style="margin-bottom:10px;">
	  <label style="font-size:.8rem;margin-bottom:4px;display:block;font-weight:600;">
	    <?php echo esc_html( $f['label'] ); ?>
	    <?php if ( ! empty( $f['hint'] ) ) : ?><small style="font-weight:400;opacity:.65;">(<?php echo esc_html( $f['hint'] ); ?>)</small><?php endif; ?>
	  </label>
	  <?php if ( $f['type'] === 'text' ) : ?>
	    <input type="text" name="<?php echo esc_attr( $f['name'] ); ?>" placeholder="<?php echo esc_attr( $f['placeholder'] ?? '' ); ?>" style="<?php echo $style; ?>" <?php echo ! empty( $f['required'] ) ? 'required' : ''; ?>>
	  <?php elseif ( $f['type'] === 'textarea' ) : ?>
	    <textarea name="<?php echo esc_attr( $f['name'] ); ?>" rows="<?php echo esc_attr( $f['rows'] ?? 2 ); ?>" placeholder="<?php echo esc_attr( $f['placeholder'] ?? '' ); ?>" style="<?php echo $style; ?>"></textarea>
	  <?php elseif ( $f['type'] === 'number' ) : ?>
	    <input type="number" name="<?php echo esc_attr( $f['name'] ); ?>" min="<?php echo esc_attr( $f['min'] ?? 1 ); ?>" max="<?php echo esc_attr( $f['max'] ?? 10 ); ?>" value="<?php echo esc_attr( $f['default'] ?? 3 ); ?>" style="<?php echo $style; ?>">
	  <?php elseif ( $f['type'] === 'category' ) : ?>
	    <?php wp_dropdown_categories( array( 'name' => $f['name'], 'show_option_none' => '- No Category -', 'option_none_value' => 0, 'hide_empty' => false, 'style' => $style ) ); ?>
	  <?php endif; ?>
	</div>
	<?php
}

// ── POST: save from custom editor ─────────────────────────────────────────────
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_custom_editor_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_custom_editor_nonce'], 'ah_save_custom_post' ) ) wp_die( 'Security check failed.' );

	$post_id     = (int) ( $_POST['post_id'] ?? 0 );
	$title       = sanitize_text_field( $_POST['post_title'] ?? '' );
	$excerpt     = sanitize_textarea_field( $_POST['post_excerpt'] ?? '' );
	$raw_status  = sanitize_key( $_POST['post_status'] ?? 'draft' );
	$status      = in_array( $raw_status, [ 'draft', 'publish', 'private', 'pending' ], true ) ? $raw_status : 'draft';
	$pub_date    = sanitize_text_field( $_POST['post_date'] ?? '' );
	$feat_img_id = (int) ( $_POST['featured_image_id'] ?? 0 );
	$cats        = array_map( 'intval', (array) ( $_POST['post_categories'] ?? [] ) );
	$tags_raw    = sanitize_text_field( $_POST['post_tags'] ?? '' );
	$sec_raw     = wp_unslash( $_POST['sections_json'] ?? '[]' );
	$sections    = json_decode( $sec_raw, true );
	if ( ! is_array( $sections ) ) $sections = [];
	$content     = ah_sections_to_blocks( $sections );

	$args = array(
		'ID'           => $post_id,
		'post_title'   => $title,
		'post_content' => $content,
		'post_excerpt' => $excerpt,
		'post_status'  => $status,
	);
	if ( $pub_date ) {
		$args['post_date']     = $pub_date;
		$args['post_date_gmt'] = get_gmt_from_date( $pub_date );
	}
	wp_update_post( $args );
	wp_set_post_categories( $post_id, $cats );
	if ( $tags_raw ) wp_set_post_tags( $post_id, $tags_raw );
	( new AH_Content_Taxonomy_Model() )->sync_terms( 'wp_post', $post_id, $_POST['taxonomy_ids'] ?? array() );
	if ( $feat_img_id ) set_post_thumbnail( $post_id, $feat_img_id );
	else delete_post_thumbnail( $post_id );
	update_post_meta( $post_id, '_ah_sections', wp_slash( wp_json_encode( $sections ) ) );
	update_post_meta( $post_id, '_ah_editor_mode', 'custom' );
	update_post_meta( $post_id, '_ah_is_featured',  ! empty( $_POST['is_featured'] )  ? '1' : '0' );
	update_post_meta( $post_id, '_ah_is_popular',   ! empty( $_POST['is_popular'] )   ? '1' : '0' );
	update_post_meta( $post_id, '_ah_is_suggested', ! empty( $_POST['is_suggested'] ) ? '1' : '0' );

	AH_Admin_Bootstrap::redirect( add_query_arg( [ 'page' => 'ah-posts', 'action' => 'edit-custom', 'id' => $post_id, 'saved' => 1 ], admin_url( 'admin.php' ) ) );
}

// ── POST: create from template ────────────────────────────────────────────────
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_posts_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_posts_nonce'], 'ah_create_post' ) ) wp_die( 'Security check failed.' );

	$tpl_key     = sanitize_key( $_POST['template_key'] ?? 'blog' );
	$editor_mode = sanitize_key( $_POST['editor_mode'] ?? 'gutenberg' );
	$tpls        = ah_post_templates();
	$tpl         = $tpls[ $tpl_key ] ?? reset( $tpls );
	$title       = sanitize_text_field( $_POST['post_title'] ?? '' ) ?: $tpl['label'];
	$excerpt     = sanitize_textarea_field( $_POST['post_excerpt'] ?? '' ) ?: $tpl['excerpt'];
	$category    = (int) ( $_POST['post_category'] ?? 0 );

	$overrides = [];
	switch ( $tpl_key ) {
		case 'news':
			$overrides['lead_paragraph'] = sanitize_textarea_field( $_POST['lead_paragraph'] ?? '' );
			if ( $overrides['lead_paragraph'] && ! ( $_POST['post_excerpt'] ?? '' ) ) $excerpt = $overrides['lead_paragraph'];
			break;
		case 'guide':
			$overrides['step_count'] = max( 2, min( 10, (int) ( $_POST['step_count'] ?? 3 ) ) );
			break;
		case 'casestudy':
			$overrides['client_name']  = sanitize_text_field( $_POST['client_name'] ?? '' );
			$overrides['client_quote'] = sanitize_textarea_field( $_POST['client_quote'] ?? '' );
			break;
		case 'faq':
			$overrides['faq_topic'] = sanitize_text_field( $_POST['faq_topic'] ?? '' );
			$overrides['faq_count'] = max( 2, min( 10, (int) ( $_POST['faq_count'] ?? 3 ) ) );
			break;
	}

	$sections = ah_template_default_sections( $tpl_key, $overrides );
	$content  = ah_sections_to_blocks( $sections );

	$insert_args = array(
		'post_type'    => 'post',
		'post_status'  => 'draft',
		'post_title'   => $title,
		'post_content' => $content,
		'post_excerpt' => $excerpt,
	);
	if ( $category ) $insert_args['post_category'] = [ $category ];

	$new_id = wp_insert_post( $insert_args );
	if ( $new_id && ! is_wp_error( $new_id ) ) {
		update_post_meta( $new_id, '_ah_editor_mode', $editor_mode );
		if ( $editor_mode === 'custom' ) {
			update_post_meta( $new_id, '_ah_sections', wp_slash( wp_json_encode( $sections ) ) );
			AH_Admin_Bootstrap::redirect( add_query_arg( [ 'page' => 'ah-posts', 'action' => 'edit-custom', 'id' => $new_id ], admin_url( 'admin.php' ) ) );
		} else {
			AH_Admin_Bootstrap::redirect( get_edit_post_link( $new_id, 'redirect' ) );
		}
	}
	$notice = 'Could not create post. Please try again.';
}

// ── GET: trash ────────────────────────────────────────────────────────────────
if ( isset( $_GET['trash_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_trash_post' ) ) {
	wp_trash_post( (int) $_GET['trash_id'] );
	AH_Admin_Bootstrap::redirect( add_query_arg( [ 'page' => 'ah-posts', 'trashed' => 1 ], admin_url( 'admin.php' ) ) );
}
if ( isset( $_GET['trashed'] ) ) $notice = 'Post moved to trash.';
if ( isset( $_GET['saved'] ) )   $notice = 'Post saved successfully.';

// ── Setup ─────────────────────────────────────────────────────────────────────
$action      = sanitize_key( $_GET['action'] ?? 'list' );
$paged       = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$search      = sanitize_text_field( $_GET['s'] ?? '' );
$status_f    = sanitize_key( $_GET['status'] ?? '' );
$q_args      = array(
	'post_type'      => 'post',
	'post_status'    => $status_f ?: array( 'publish', 'draft', 'private', 'pending' ),
	'posts_per_page' => 20,
	'paged'          => $paged,
	'orderby'        => 'modified',
	'order'          => 'DESC',
);
if ( $search ) $q_args['s'] = $search;
$q           = new WP_Query( $q_args );
$posts_list  = $q->posts;
$total       = $q->found_posts;
$pages_count = (int) ceil( $total / 20 );

if ( $action === 'edit-custom' ) {
	wp_enqueue_editor();
}
?>
<div class="wrap ah-wrap">
<h1><span class="dashicons dashicons-edit"></span> Posts / Blog</h1>
<?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

<?php /* ══════════════ TEMPLATES VIEW ══════════════ */ ?>
<?php if ( $action === 'templates' ) :
  $tpls = ah_post_templates();
?>
  <div class="ah-table-top" style="margin-bottom:0;">
    <p style="color:var(--ah-muted);margin:0;">Fill in the fields below, then choose how you'd like to edit your post.</p>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-posts' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">&larr; Back</a>
  </div>
  <p style="margin:8px 0 24px;"></p>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
    <?php foreach ( $tpls as $tpl_key => $tpl ) : ?>
      <div class="ah-card" style="padding:0;overflow:hidden;">
        <div style="background:var(--ah-primary,#1e40af);color:#fff;padding:20px 24px;">
          <div style="font-size:2rem;margin-bottom:8px;"><?php echo $tpl['icon']; ?></div>
          <h3 style="margin:0 0 4px;color:#fff;"><?php echo esc_html( $tpl['label'] ); ?></h3>
          <p style="margin:0;opacity:.8;font-size:.82rem;"><?php echo esc_html( $tpl['desc'] ); ?></p>
        </div>
        <div style="padding:20px 24px;">
          <form method="post">
            <?php wp_nonce_field( 'ah_create_post', 'ah_posts_nonce' ); ?>
            <input type="hidden" name="template_key" value="<?php echo esc_attr( $tpl_key ); ?>">
            <?php foreach ( $tpl['fields'] as $field ) : ?>
              <?php ah_render_template_field( $field ); ?>
            <?php endforeach; ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;">
              <button type="submit" name="editor_mode" value="custom"
                      class="ah-btn ah-btn-secondary" style="justify-content:center;font-size:.82rem;">
                📝 Form Editor
              </button>
              <button type="submit" name="editor_mode" value="gutenberg"
                      class="ah-btn ah-btn-primary" style="justify-content:center;font-size:.82rem;">
                🖊 WP Editor
              </button>
            </div>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<?php /* ══════════════ CUSTOM FORM EDITOR ══════════════ */ ?>
<?php elseif ( $action === 'edit-custom' ) :
  $edit_id     = (int) ( $_GET['id'] ?? 0 );
  if ( ! $edit_id ) { AH_Admin_Bootstrap::redirect( admin_url( 'admin.php?page=ah-posts&action=templates' ) ); }
  $post        = get_post( $edit_id );
  if ( ! $post ) { AH_Admin_Bootstrap::redirect( admin_url( 'admin.php?page=ah-posts' ) ); }

  $saved_sections_raw = get_post_meta( $edit_id, '_ah_sections', true );
  $saved_sections     = $saved_sections_raw ? json_decode( $saved_sections_raw, true ) : [];
  if ( ! is_array( $saved_sections ) ) $saved_sections = [];

  $post_cats      = wp_get_post_categories( $edit_id );
  $post_tags_obj  = wp_get_post_tags( $edit_id, [ 'fields' => 'names' ] );
  $tags_str       = implode( ', ', $post_tags_obj );
  $feat_img_id    = (int) get_post_thumbnail_id( $edit_id );
  $feat_img_url   = $feat_img_id ? wp_get_attachment_image_url( $feat_img_id, 'medium' ) : '';
  $all_cats       = get_categories( [ 'hide_empty' => false ] );
  $pub_date_raw   = $post->post_status === 'future' ? $post->post_date : '';
  $is_featured    = (bool) get_post_meta( $edit_id, '_ah_is_featured', true );
  $is_popular     = (bool) get_post_meta( $edit_id, '_ah_is_popular', true );
  $is_suggested   = (bool) get_post_meta( $edit_id, '_ah_is_suggested', true );

  $wp_edit_url    = get_edit_post_link( $edit_id );
  $section_count  = count( $saved_sections );
?>
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-posts' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">&larr; All Posts</a>
    <div style="display:flex;align-items:center;gap:8px;">
      <span class="ah-badge ah-badge-<?php echo esc_attr( $post->post_status === 'publish' ? 'active' : 'draft' ); ?>"><?php echo esc_html( ucfirst( $post->post_status ) ); ?></span>
      <a href="<?php echo esc_url( $wp_edit_url ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Switch to WP Editor</a>
      <?php if ( $post->post_status === 'publish' ) : ?>
        <a href="<?php echo esc_url( get_permalink( $edit_id ) ); ?>" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm">View Post ↗</a>
      <?php endif; ?>
    </div>
  </div>

  <form id="ah-custom-editor-form" method="post">
    <?php wp_nonce_field( 'ah_save_custom_post', 'ah_custom_editor_nonce' ); ?>
    <input type="hidden" name="post_id" value="<?php echo esc_attr( $edit_id ); ?>">
    <textarea id="ah-sections-json" name="sections_json" style="display:none;"></textarea>

    <div style="display:grid;grid-template-columns:1fr 290px;gap:20px;align-items:start;">

      <!-- ── Main editor column ── -->
      <div>
        <!-- Title & Excerpt -->
        <div class="ah-card" style="padding:20px;margin-bottom:16px;">
          <div class="ah-form-row" style="margin-bottom:12px;">
            <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Post Title</label>
            <input type="text" name="post_title" value="<?php echo esc_attr( $post->post_title ); ?>" placeholder="Post title…" style="width:100%;box-sizing:border-box;font-size:1.25rem;font-weight:600;">
          </div>
          <div class="ah-form-row">
            <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Short Summary <small style="font-weight:400;opacity:.65;">(excerpt shown in listings)</small></label>
            <textarea name="post_excerpt" rows="2" placeholder="A short summary of what this post covers…" style="width:100%;box-sizing:border-box;"><?php echo esc_textarea( $post->post_excerpt ); ?></textarea>
          </div>
        </div>

        <!-- Sections builder -->
        <div id="ah-sections-builder">
          <?php
          foreach ( $saved_sections as $idx => $sec ) {
              ah_render_section_card( $sec, $idx === 0, $idx === $section_count - 1 );
          }
          ?>
        </div>

        <!-- Add section toolbar -->
        <div class="ah-card" style="padding:16px;margin-bottom:16px;">
          <p style="margin:0 0 10px;font-size:.85rem;font-weight:600;color:var(--ah-muted);">Add a section:</p>
          <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-sec" data-type="heading">+ Heading</button>
            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-sec" data-type="paragraph">+ Paragraph</button>
            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-sec" data-type="list">+ List</button>
            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-sec" data-type="table">+ Table</button>
            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-sec" data-type="quote">+ Quote</button>
            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-sec" data-type="cta">+ CTA Button</button>
          </div>
        </div>
      </div>

      <!-- ── Sidebar ── -->
      <div>
        <!-- Save actions -->
        <div class="ah-card" style="padding:16px;margin-bottom:12px;">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
            <button type="submit" name="post_status_override" value="draft" onclick="document.querySelector('[name=post_status]').value='draft';"
                    class="ah-btn ah-btn-secondary" style="justify-content:center;">Save Draft</button>
            <button type="submit" name="post_status_override" value="publish" onclick="document.querySelector('[name=post_status]').value='publish';"
                    class="ah-btn ah-btn-primary" style="justify-content:center;">Publish</button>
          </div>
          <div class="ah-form-row">
            <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Status</label>
            <select name="post_status" style="width:100%;box-sizing:border-box;">
              <?php foreach ( [ 'draft' => 'Draft', 'publish' => 'Published', 'private' => 'Private', 'pending' => 'Pending Review' ] as $sv => $sl ) : ?>
                <option value="<?php echo $sv; ?>" <?php selected( $post->post_status, $sv ); ?>><?php echo $sl; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="ah-form-row" style="margin-top:8px;">
            <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Publish Date <small style="font-weight:400;opacity:.65;">(leave blank for now)</small></label>
            <input type="datetime-local" name="post_date" value="<?php echo esc_attr( $pub_date_raw ); ?>" style="width:100%;box-sizing:border-box;">
          </div>
        </div>

        <!-- Post Settings -->
        <div class="ah-card" style="padding:16px;margin-bottom:12px;">
          <h4 style="margin:0 0 10px;font-size:.9rem;">Post Settings</h4>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;font-weight:600;margin-bottom:6px;">
            <input type="checkbox" name="is_featured" value="1" <?php checked( $is_featured ); ?>>
            ⭐ Featured Post
          </label>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;font-weight:600;margin-bottom:6px;">
            <input type="checkbox" name="is_popular" value="1" <?php checked( $is_popular ); ?>>
            🔥 Popular Post
          </label>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;font-weight:600;">
            <input type="checkbox" name="is_suggested" value="1" <?php checked( $is_suggested ); ?>>
            💡 Suggested Post
          </label>
        </div>

        <!-- Categories -->
        <div class="ah-card" style="padding:16px;margin-bottom:12px;">
          <h4 style="margin:0 0 10px;font-size:.9rem;">Categories</h4>
          <div style="max-height:150px;overflow-y:auto;">
            <?php if ( $all_cats ) : ?>
              <?php foreach ( $all_cats as $cat ) : ?>
                <label style="display:flex;align-items:center;gap:6px;margin-bottom:6px;cursor:pointer;font-size:.85rem;">
                  <input type="checkbox" name="post_categories[]" value="<?php echo esc_attr( $cat->term_id ); ?>"
                         <?php checked( in_array( $cat->term_id, $post_cats, true ) ); ?>>
                  <?php echo esc_html( $cat->name ); ?>
                  <small style="opacity:.6;">(<?php echo (int) $cat->count; ?>)</small>
                </label>
              <?php endforeach; ?>
            <?php else : ?>
              <p style="font-size:.82rem;opacity:.6;margin:0;">No categories yet - <a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=category' ) ); ?>">add one</a>.</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Tags -->
        <div class="ah-card" style="padding:16px;margin-bottom:12px;">
          <h4 style="margin:0 0 10px;font-size:.9rem;">Tags</h4>
          <input type="text" name="post_tags" value="<?php echo esc_attr( $tags_str ); ?>" placeholder="tag1, tag2, tag3" style="width:100%;box-sizing:border-box;">
          <p style="font-size:.75rem;opacity:.6;margin:4px 0 0;">Separate with commas.</p>
        </div>

        <!-- Global Terms -->
        <div class="ah-card" style="padding:16px;margin-bottom:12px;">
          <h4 style="margin:0 0 10px;font-size:.9rem;">CMS Taxonomy Terms</h4>
          <?php ( new AH_Content_Taxonomy_Model() )->render_picker( 'wp_post', $edit_id ); ?>
        </div>

        <!-- Featured Image -->
        <div class="ah-card" style="padding:16px;margin-bottom:12px;">
          <h4 style="margin:0 0 10px;font-size:.9rem;">Featured Image</h4>
          <div class="ah-image-picker" style="width:100%;">
            <input type="hidden" name="featured_image_id" class="ah-image-id" value="<?php echo esc_attr( $feat_img_id ); ?>">
            <img class="ah-image-preview<?php echo $feat_img_url ? ' visible' : ''; ?>" src="<?php echo esc_url( $feat_img_url ); ?>" style="<?php echo $feat_img_url ? 'display:block;' : ''; ?>width:100%;height:auto;border-radius:4px;margin-bottom:8px;">
            <?php if ( ! $feat_img_url ) : ?>
              <div class="ah-image-placeholder" style="text-align:center;padding:20px;border:2px dashed #e2e8f0;border-radius:4px;margin-bottom:8px;cursor:pointer;">
                <span class="dashicons dashicons-format-image" style="font-size:2rem;display:block;margin-bottom:4px;opacity:.4;"></span>
                <span style="font-size:.82rem;opacity:.5;">Click to choose image</span>
              </div>
            <?php endif; ?>
            <div style="display:flex;gap:6px;">
              <button type="button" class="ah-pick-image ah-btn ah-btn-secondary ah-btn-sm" style="flex:1;"><?php echo $feat_img_url ? 'Change Image' : 'Choose Image'; ?></button>
              <?php if ( $feat_img_url ) : ?>
                <button type="button" class="ah-remove-image ah-btn ah-btn-danger ah-btn-sm">✕</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div><!-- /sidebar -->
    </div><!-- /grid -->
  </form>

<style>
.ah-section-card .wp-editor-wrap { max-width: none; }
.ah-section-card .wp-editor-container textarea.wp-editor-area { border-radius: 0; }
</style>

<script>
(function($){
  var richEditorCounter = 0;
  var richTextSelector = '.ah-section-card[data-type="paragraph"] .ah-sec-text';

  function assignRichEditorId($el) {
    if ($el.attr('id')) return $el.attr('id');
    richEditorCounter += 1;
    $el.attr('id', 'ah-post-section-editor-' + richEditorCounter);
    return $el.attr('id');
  }

  function initRichEditors($scope) {
    if (!window.wp || !wp.editor) {
      window.setTimeout(function() {
        initRichEditors($scope);
      }, 250);
      return;
    }
    $scope = $scope && $scope.length ? $scope : $(document);
    $scope.find(richTextSelector).each(function() {
      var $el = $(this);
      var id = assignRichEditorId($el);
      if ($el.data('editorReady')) return;
      $el.data('editorReady', 1);
      wp.editor.initialize(id, {
        tinymce: {
          wpautop: true,
          toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,image,undo,redo',
          toolbar2: '',
          setup: function(editor) {
            editor.on('change keyup undo redo SetContent', function() {
              editor.save();
            });
          }
        },
        quicktags: true,
        mediaButtons: true
      });
    });
  }

  function destroyRichEditors($scope) {
    $scope = $scope && $scope.length ? $scope : $(document);
    $scope.find(richTextSelector).each(function() {
      var id = this.id;
      if (!id) return;
      if (window.tinymce && tinymce.get(id)) {
        tinymce.get(id).save();
        tinymce.get(id).remove();
      }
      if (window.QTags && QTags.instances && QTags.instances[id]) {
        delete QTags.instances[id];
      }
      $(this).removeData('editorReady');
    });
  }

  function syncRichEditors() {
    $(richTextSelector).each(function() {
      if (this.id && window.tinymce && tinymce.get(this.id)) {
        tinymce.get(this.id).save();
      }
    });
  }
  // ── Section card templates ──────────────────────────────────────────────────
  var sectionTemplates = {
    heading: function() {
      return buildCard('heading', '📝 Heading',
        '<div style="display:flex;gap:8px;">' +
        '<select class="ah-sec-level" style="width:90px;"><option value="2">H2</option><option value="3">H3</option><option value="4">H4</option></select>' +
        '<input type="text" class="ah-sec-text" placeholder="Section heading…" style="flex:1;box-sizing:border-box;">' +
        '</div>'
      );
    },
    paragraph: function() {
      return buildCard('paragraph', '¶ Paragraph',
        '<textarea class="ah-sec-text" rows="4" placeholder="Write your paragraph here…" style="width:100%;box-sizing:border-box;"></textarea>'
      );
    },
    list: function() {
      return buildCard('list', '• List',
        '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">' +
        '<label style="font-size:.8rem;font-weight:600;">Type:</label>' +
        '<select class="ah-sec-list-type"><option value="0">• Bullet</option><option value="1">1. Numbered</option></select>' +
        '</div>' +
        '<div class="ah-list-items">' +
        '<div class="ah-list-item" style="display:flex;gap:6px;margin-bottom:6px;">' +
        '<input type="text" class="ah-list-item-text" placeholder="List item…" style="flex:1;box-sizing:border-box;">' +
        '<button type="button" class="ah-list-rm ah-btn ah-btn-danger ah-btn-sm">✕</button>' +
        '</div></div>' +
        '<button type="button" class="ah-list-add ah-btn ah-btn-secondary ah-btn-sm" style="margin-top:4px;">+ Add Item</button>'
      );
    },
    table: function() {
      var cell = 'padding:4px;border:1px solid #cbd5e1;';
      return buildCard('table', '⊞ Table',
        '<div style="margin-bottom:8px;">' +
        '<button type="button" class="ah-table-add-row ah-btn ah-btn-secondary ah-btn-sm">+ Row</button>' +
        '<button type="button" class="ah-table-add-col ah-btn ah-btn-secondary ah-btn-sm" style="margin-left:6px;">+ Column</button>' +
        '</div>' +
        '<div style="overflow-x:auto;"><table class="ah-table-editor" style="border-collapse:collapse;width:100%;">' +
        '<thead><tr>' +
        '<th style="' + cell + 'background:#f8fafc;"><input type="text" placeholder="Header 1" style="width:100%;min-width:80px;box-sizing:border-box;"></th>' +
        '<th style="' + cell + 'background:#f8fafc;"><input type="text" placeholder="Header 2" style="width:100%;min-width:80px;box-sizing:border-box;"></th>' +
        '</tr></thead>' +
        '<tbody><tr>' +
        '<td style="' + cell + '"><input type="text" placeholder="Cell" style="width:100%;min-width:80px;box-sizing:border-box;"></td>' +
        '<td style="' + cell + '"><input type="text" placeholder="Cell" style="width:100%;min-width:80px;box-sizing:border-box;"></td>' +
        '</tr></tbody></table></div>'
      );
    },
    quote: function() {
      return buildCard('quote', '" Quote',
        '<textarea class="ah-sec-text" rows="3" placeholder="Quote text…" style="width:100%;box-sizing:border-box;margin-bottom:8px;font-style:italic;"></textarea>' +
        '<input type="text" class="ah-sec-cite" placeholder="Attribution / Author (optional)" style="width:100%;box-sizing:border-box;">'
      );
    },
    cta: function() {
      return buildCard('cta', '⬡ CTA Button',
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">' +
        '<div><label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Button Text</label>' +
        '<input type="text" class="ah-sec-cta-text" placeholder="Book a Free Consultation" style="width:100%;box-sizing:border-box;"></div>' +
        '<div><label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">URL</label>' +
        '<input type="text" class="ah-sec-cta-url" placeholder="/contact/" style="width:100%;box-sizing:border-box;"></div>' +
        '</div>'
      );
    }
  };

  function buildCard(type, label, fieldsHtml) {
    return $(
      '<div class="ah-card ah-section-card" data-type="' + type + '" style="margin-bottom:12px;padding:16px;">' +
      '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">' +
      '<strong style="font-size:.85rem;">' + label + '</strong>' +
      '<div>' +
      '<button type="button" class="ah-sec-up ah-btn ah-btn-secondary ah-btn-sm">↑</button> ' +
      '<button type="button" class="ah-sec-dn ah-btn ah-btn-secondary ah-btn-sm">↓</button> ' +
      '<button type="button" class="ah-sec-rm ah-btn ah-btn-danger ah-btn-sm" style="margin-left:4px;">✕ Remove</button>' +
      '</div></div>' +
      '<div class="ah-section-fields">' + fieldsHtml + '</div>' +
      '</div>'
    );
  }

  // ── Add section ─────────────────────────────────────────────────────────────
  $(document).on('click', '.ah-add-sec', function() {
    var type = $(this).data('type');
    if (sectionTemplates[type]) {
      var $card = sectionTemplates[type]();
      $('#ah-sections-builder').append($card);
      initRichEditors($card);
    }
  });

  // ── Move up/down ────────────────────────────────────────────────────────────
  $(document).on('click', '.ah-sec-up', function() {
    var $card = $(this).closest('.ah-section-card');
    var $prev = $card.prev('.ah-section-card');
    if ($prev.length) $prev.before($card);
  });
  $(document).on('click', '.ah-sec-dn', function() {
    var $card = $(this).closest('.ah-section-card');
    var $next = $card.next('.ah-section-card');
    if ($next.length) $next.after($card);
  });

  // ── Remove section ──────────────────────────────────────────────────────────
  $(document).on('click', '.ah-sec-rm', function() {
    if (confirm('Remove this section?')) {
      var $card = $(this).closest('.ah-section-card');
      destroyRichEditors($card);
      $card.remove();
    }
  });

  // ── List: add/remove items ──────────────────────────────────────────────────
  $(document).on('click', '.ah-list-add', function() {
    var $items = $(this).siblings('.ah-list-items');
    $items.append(
      '<div class="ah-list-item" style="display:flex;gap:6px;margin-bottom:6px;">' +
      '<input type="text" class="ah-list-item-text" placeholder="List item…" style="flex:1;box-sizing:border-box;">' +
      '<button type="button" class="ah-list-rm ah-btn ah-btn-danger ah-btn-sm">✕</button>' +
      '</div>'
    );
    $items.find('.ah-list-item:last input').focus();
  });
  $(document).on('click', '.ah-list-rm', function() {
    var $items = $(this).closest('.ah-list-items');
    if ($items.find('.ah-list-item').length > 1) $(this).closest('.ah-list-item').remove();
    else alert('At least one item is required.');
  });

  // ── Table: add row/column ───────────────────────────────────────────────────
  var cellStyle = 'padding:4px;border:1px solid #cbd5e1;';
  $(document).on('click', '.ah-table-add-row', function() {
    var $table = $(this).closest('.ah-section-card').find('.ah-table-editor');
    var cols   = $table.find('thead tr th').length || 2;
    var $tr    = $('<tr>');
    for (var i = 0; i < cols; i++) {
      $tr.append('<td style="' + cellStyle + '"><input type="text" placeholder="Cell" style="width:100%;min-width:80px;box-sizing:border-box;"></td>');
    }
    $table.find('tbody').append($tr);
  });
  $(document).on('click', '.ah-table-add-col', function() {
    var $table = $(this).closest('.ah-section-card').find('.ah-table-editor');
    var cols   = $table.find('thead tr th').length + 1;
    $table.find('thead tr').append('<th style="' + cellStyle + 'background:#f8fafc;"><input type="text" placeholder="Header ' + cols + '" style="width:100%;min-width:80px;box-sizing:border-box;"></th>');
    $table.find('tbody tr').each(function() {
      $(this).append('<td style="' + cellStyle + '"><input type="text" placeholder="Cell" style="width:100%;min-width:80px;box-sizing:border-box;"></td>');
    });
  });

  // ── Serialize sections → JSON on submit ─────────────────────────────────────
  $('#ah-custom-editor-form').on('submit', function() {
    syncRichEditors();
    var sections = [];
    $('#ah-sections-builder .ah-section-card').each(function() {
      var $c   = $(this);
      var type = $c.data('type');
      var s    = { type: type };
      switch (type) {
        case 'heading':
          s.level = $c.find('.ah-sec-level').val();
          s.text  = $c.find('.ah-sec-text').val();
          break;
        case 'paragraph':
          s.text = $c.find('.ah-sec-text').val();
          break;
        case 'list':
          s.ordered = $c.find('.ah-sec-list-type').val() === '1';
          s.items   = [];
          $c.find('.ah-list-item-text').each(function() { if (this.value.trim()) s.items.push(this.value); });
          break;
        case 'table':
          s.headers = [];
          $c.find('thead th input').each(function() { s.headers.push(this.value); });
          s.rows = [];
          $c.find('tbody tr').each(function() {
            var row = [];
            $(this).find('td input').each(function() { row.push(this.value); });
            s.rows.push(row);
          });
          break;
        case 'quote':
          s.text = $c.find('.ah-sec-text').val();
          s.cite = $c.find('.ah-sec-cite').val();
          break;
        case 'cta':
          s.text = $c.find('.ah-sec-cta-text').val();
          s.url  = $c.find('.ah-sec-cta-url').val();
          break;
      }
      sections.push(s);
    });
    $('#ah-sections-json').val(JSON.stringify(sections));
  });

  initRichEditors($('#ah-sections-builder'));
  $(window).on('load', function() {
    initRichEditors($('#ah-sections-builder'));
  });

})(jQuery);
</script>

<?php /* ══════════════ LIST VIEW ══════════════ */ ?>
<?php else : ?>

  <div class="ah-table-top">
    <form class="ah-search-form" method="get">
      <input type="hidden" name="page" value="ah-posts">
      <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search posts…">
      <select name="status">
        <option value="">All Statuses</option>
        <?php foreach ( [ 'publish' => 'Published', 'draft' => 'Draft', 'private' => 'Private', 'pending' => 'Pending' ] as $sv => $sl ) : ?>
          <option value="<?php echo $sv; ?>" <?php selected( $status_f, $sv ); ?>><?php echo $sl; ?></option>
        <?php endforeach; ?>
      </select>
      <button class="ah-btn ah-btn-secondary">Filter</button>
    </form>
    <div style="display:flex;gap:8px;">
      <a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>" class="ah-btn ah-btn-secondary">+ Blank Post</a>
      <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ah-posts', 'action' => 'templates' ], admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary">📋 From Template</a>
    </div>
  </div>

  <?php if ( empty( $posts_list ) ) : ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:8px;">
      <a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"
         class="ah-card" style="text-decoration:none;color:inherit;text-align:center;padding:36px 24px;transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
        <div style="font-size:2.5rem;margin-bottom:12px;">✍️</div>
        <h3 style="margin:0 0 8px;">Blank Post</h3>
        <p style="color:var(--ah-muted);margin:0;font-size:.85rem;">Open the WordPress editor with a blank post - write freely.</p>
      </a>
      <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ah-posts', 'action' => 'templates' ], admin_url( 'admin.php' ) ) ); ?>"
         class="ah-card" style="text-decoration:none;color:inherit;text-align:center;padding:36px 24px;transition:box-shadow .15s;border-top:3px solid var(--ah-primary);" onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
        <div style="font-size:2.5rem;margin-bottom:12px;">📋</div>
        <h3 style="margin:0 0 8px;">From Template</h3>
        <p style="color:var(--ah-muted);margin:0;font-size:.85rem;">Choose a pre-filled template - Blog, News, Guide, Case Study, FAQ.</p>
      </a>
    </div>
  <?php else : ?>
    <div class="ah-table-wrap">
      <table class="ah-table">
        <thead>
          <tr><th>Title</th><th>WP Categories</th><th>CMS Terms</th><th>Status</th><th>Editor</th><th>Author</th><th>Modified</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php
          $qe_tax_model  = class_exists( 'AH_Content_Taxonomy_Model' ) ? new AH_Content_Taxonomy_Model() : null;
          $qe_tax_groups = $qe_tax_model ? $qe_tax_model->get_active_terms_grouped() : [];
          foreach ( $posts_list as $p ) :
            $cats        = get_the_category( $p->ID );
            $author      = get_the_author_meta( 'display_name', $p->post_author );
            $editor_mode = get_post_meta( $p->ID, '_ah_editor_mode', true ) ?: 'gutenberg';
            $edit_url    = $editor_mode === 'custom'
              ? add_query_arg( [ 'page' => 'ah-posts', 'action' => 'edit-custom', 'id' => $p->ID ], admin_url( 'admin.php' ) )
              : get_edit_post_link( $p->ID );
            $badge       = [ 'publish' => 'active', 'draft' => 'draft', 'private' => 'inactive', 'pending' => 'draft' ];
            $label       = [ 'publish' => 'Published', 'draft' => 'Draft', 'private' => 'Private', 'pending' => 'Pending' ];
            $qe_term_ids   = $qe_tax_model ? $qe_tax_model->get_term_ids( 'wp_post', (int) $p->ID ) : [];
            $qe_is_feat    = (bool) get_post_meta( $p->ID, '_ah_is_featured', true );
            $qe_is_popular = (bool) get_post_meta( $p->ID, '_ah_is_popular', true );
            $qe_is_sug     = (bool) get_post_meta( $p->ID, '_ah_is_suggested', true );
            $qe_hl_raw     = get_post_meta( $p->ID, '_ah_highlight_links', true );
            $qe_hl_links   = json_decode( $qe_hl_raw ?: '[]', true );
            if ( ! is_array( $qe_hl_links ) ) $qe_hl_links = [];
          ?>
            <tr>
              <td>
                <strong><?php echo esc_html( $p->post_title ?: '(no title)' ); ?></strong>
                <?php if ( $p->post_excerpt ) : ?>
                  <small style="color:var(--ah-muted);display:block;"><?php echo esc_html( wp_trim_words( $p->post_excerpt, 10 ) ); ?></small>
                <?php endif; ?>
              </td>
              <td><small><?php echo $cats ? esc_html( implode( ', ', wp_list_pluck( $cats, 'name' ) ) ) : '-'; ?></small></td>
              <td><?php ( new AH_Content_Taxonomy_Model() )->render_badges( 'wp_post', (int) $p->ID ); ?></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $badge[ $p->post_status ] ?? 'draft' ); ?>"><?php echo esc_html( $label[ $p->post_status ] ?? $p->post_status ); ?></span></td>
              <td><small style="opacity:.7;"><?php echo $editor_mode === 'custom' ? '📝 Form' : '🖊 WP'; ?></small></td>
              <td><small><?php echo esc_html( $author ); ?></small></td>
              <td><small><?php echo esc_html( wp_date( 'M j, Y', strtotime( $p->post_modified ) ) ); ?></small></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( $edit_url ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-qe-open" data-id="<?php echo esc_attr( $p->ID ); ?>">Edit Meta</button>
                <?php if ( $p->post_status === 'publish' ) : ?>
                  <a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm">View</a>
                <?php endif; ?>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'page' => 'ah-posts', 'trash_id' => $p->ID ], admin_url( 'admin.php' ) ), 'ah_trash_post' ) ); ?>"
                   class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Move to trash?');">Trash</a>
              </td>
            </tr>
            <tr class="ah-qe-holder">
              <td colspan="8" style="padding:0;border:0;">
                <div class="ah-qe-modal" id="ah-qe-<?php echo esc_attr( $p->ID ); ?>" role="dialog" aria-modal="true" aria-hidden="true">
                  <div class="ah-qe-backdrop" data-id="<?php echo esc_attr( $p->ID ); ?>"></div>
                  <div class="ah-qe-card" role="document">

                    <!-- Header -->
                    <header class="ah-qe-head">
                      <div class="ah-qe-head-l">
                        <span class="ah-qe-pill">Meta</span>
                        <strong class="ah-qe-head-title"><?php echo esc_html( wp_trim_words( $p->post_title ?: '(no title)', 10 ) ); ?></strong>
                      </div>
                      <button type="button" class="ah-qe-x ah-qe-close" data-id="<?php echo esc_attr( $p->ID ); ?>" aria-label="Close">&times;</button>
                    </header>

                    <!-- Scrollable body -->
                    <div class="ah-qe-body">

                      <div class="ah-qe-grid">
                        <!-- Flags -->
                        <section class="ah-qe-sec ah-qe-sec--flags">
                          <div class="ah-qe-sec-h">Post Flags</div>
                          <label class="ah-qe-flag">
                            <input type="checkbox" class="ah-qe-featured" <?php checked( $qe_is_feat ); ?> style="accent-color:#f59e0b;">
                            <span>⭐ Featured</span>
                          </label>
                          <label class="ah-qe-flag">
                            <input type="checkbox" class="ah-qe-popular" <?php checked( $qe_is_popular ); ?> style="accent-color:#ef4444;">
                            <span>🔥 Popular</span>
                          </label>
                          <label class="ah-qe-flag ah-qe-flag--last">
                            <input type="checkbox" class="ah-qe-suggested" <?php checked( $qe_is_sug ); ?> style="accent-color:#3b82f6;">
                            <span>💡 Suggested</span>
                          </label>
                        </section>

                        <!-- CMS Taxonomy Terms -->
                        <section class="ah-qe-sec">
                          <div class="ah-qe-sec-h">CMS Taxonomy Terms</div>
                          <?php if ( ! empty( $qe_tax_groups ) ) : ?>
                            <div style="display:flex;flex-direction:column;gap:10px;">
                            <?php foreach ( $qe_tax_groups as $grp ) : ?>
                              <?php if ( empty( $grp['items'] ) ) continue; ?>
                              <div>
                                <div class="ah-qe-sub-h"><?php echo esc_html( $grp['label'] ); ?></div>
                                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                  <?php foreach ( $grp['items'] as $term ) :
                                    $checked = in_array( (int) $term->id, $qe_term_ids, true );
                                  ?>
                                    <label class="ah-qe-chip<?php echo $checked ? ' is-on' : ''; ?>">
                                      <input type="checkbox" class="ah-qe-term" value="<?php echo esc_attr( $term->id ); ?>" <?php checked( $checked ); ?> style="margin:0;display:none;">
                                      <?php echo esc_html( $term->name ); ?>
                                    </label>
                                  <?php endforeach; ?>
                                </div>
                              </div>
                            <?php endforeach; ?>
                            </div>
                          <?php else : ?>
                            <p style="font-size:.82rem;color:#94a3b8;margin:0;">No taxonomy terms yet - <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-taxonomy' ) ); ?>">add some →</a></p>
                          <?php endif; ?>
                        </section>
                      </div>

                      <!-- Highlight Links -->
                      <section class="ah-qe-sec" style="margin-top:14px;">
                        <div class="ah-qe-sec-h">🔗 Highlight Links <span class="ah-qe-hint">(shown as highlight buttons in the blog sidebar)</span></div>
                        <div id="ah-qe-hl-rows-<?php echo esc_attr( $p->ID ); ?>">
                          <?php foreach ( $qe_hl_links as $hl ) : ?>
                          <div class="ah-qe-hl-row" style="display:flex;gap:6px;margin-bottom:6px;align-items:center;">
                            <input type="text" class="ah-qe-hl-name" value="<?php echo esc_attr( $hl['name'] ?? '' ); ?>" placeholder="Label"
                                   style="flex:1;min-width:0;padding:6px 9px;border:1px solid #d1dae8;border-radius:6px;font-size:.82rem;outline:none;">
                            <input type="text" class="ah-qe-hl-url"  value="<?php echo esc_attr( $hl['url'] ?? '' ); ?>"  placeholder="/slug/ or URL"
                                   style="flex:1.6;min-width:0;padding:6px 9px;border:1px solid #d1dae8;border-radius:6px;font-size:.82rem;outline:none;">
                            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-qe-hl-remove" style="flex-shrink:0;padding:3px 8px;">✕</button>
                          </div>
                          <?php endforeach; ?>
                        </div>
                        <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-qe-hl-add"
                                data-id="<?php echo esc_attr( $p->ID ); ?>"
                                style="margin-top:4px;font-size:.78rem;">+ Add Link</button>
                      </section>

                      <!-- Related Content (calculators, articles, static components, external/support) -->
                      <section class="ah-qe-sec" style="margin-top:14px;">
                        <div class="ah-qe-sec-h">🧩 Related Content <span class="ah-qe-hint">(articles, calculators, static components, external &amp; support links - grouped by section)</span></div>
                        <?php ( new AH_Related_Links_Model() )->render_admin_panel( 'wp_post', (int) $p->ID ); ?>
                      </section>

                    </div><!-- /.ah-qe-body -->

                    <!-- Sticky footer -->
                    <footer class="ah-qe-foot">
                      <button type="button" class="ah-btn ah-btn-secondary ah-qe-close" data-id="<?php echo esc_attr( $p->ID ); ?>">Cancel</button>
                      <button type="button" class="ah-btn ah-btn-primary ah-qe-save" data-id="<?php echo esc_attr( $p->ID ); ?>">Save Changes</button>
                    </footer>

                  </div><!-- /.ah-qe-card -->
                </div><!-- /.ah-qe-modal -->
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ( $pages_count > 1 ) : ?>
      <div style="margin-top:16px;display:flex;gap:6px;">
        <?php for ( $pg = 1; $pg <= $pages_count; $pg++ ) : ?>
          <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ah-posts', 'paged' => $pg ], admin_url( 'admin.php' ) ) ); ?>"
             class="ah-btn ah-btn-sm <?php echo $pg === $paged ? 'ah-btn-primary' : 'ah-btn-secondary'; ?>"><?php echo $pg; ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

<?php endif; ?>

<style>
/* ── Edit-Meta modal ─────────────────────────────────────────────────────── */
body.ah-qe-lock { overflow: hidden; }
.ah-qe-holder td { padding: 0 !important; border: 0 !important; }
.ah-qe-modal { display: none; position: fixed; inset: 0; z-index: 100000; }
.ah-qe-modal.is-open { display: block; }
.ah-qe-backdrop { position: absolute; inset: 0; background: rgba(15,23,42,.55); }
.ah-qe-card {
  position: relative; z-index: 1;
  width: min(940px, 94vw); max-height: 90vh;
  margin: 5vh auto 0;
  display: flex; flex-direction: column;
  background: #fff; border-radius: 14px; overflow: hidden;
  box-shadow: 0 24px 70px rgba(2,6,23,.35);
  animation: ahQePop .18s ease;
}
@keyframes ahQePop { from { opacity: 0; transform: translateY(16px) scale(.985); } to { opacity: 1; transform: none; } }
.ah-qe-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 15px 20px; border-bottom: 1px solid #eef2f9;
  background: linear-gradient(180deg,#f8faff,#fff);
  flex: 0 0 auto;
}
.ah-qe-head-l { display: flex; align-items: center; gap: 10px; min-width: 0; }
.ah-qe-head-title { font-size: .95rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ah-qe-pill { font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #1e40af; background: #e8efff; padding: 3px 9px; border-radius: 5px; flex: 0 0 auto; }
.ah-qe-x { border: 0; background: transparent; font-size: 1.5rem; line-height: 1; cursor: pointer; color: #64748b; padding: 0 6px; border-radius: 6px; }
.ah-qe-x:hover { background: #f1f5f9; color: #0f172a; }
.ah-qe-body { padding: 18px 20px; overflow-y: auto; }
.ah-qe-foot { padding: 13px 20px; border-top: 1px solid #eef2f9; background: #fafbff; display: flex; justify-content: flex-end; gap: 10px; flex: 0 0 auto; }
.ah-qe-grid { display: grid; grid-template-columns: 210px 1fr; gap: 16px; align-items: start; }
@media (max-width: 782px) { .ah-qe-grid { grid-template-columns: 1fr; } }
.ah-qe-sec { background: #fff; border: 1px solid #e2ecf9; border-radius: 10px; padding: 13px 15px; }
.ah-qe-sec-h { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #64748b; margin-bottom: 10px; }
.ah-qe-hint { font-weight: 400; font-size: .68rem; text-transform: none; letter-spacing: 0; color: #94a3b8; }
.ah-qe-sub-h { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #94a3b8; margin-bottom: 5px; }
.ah-qe-flag { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: .85rem; padding: 6px 0; border-bottom: 1px solid #f1f5f9; }
.ah-qe-flag input { width: 15px; height: 15px; }
.ah-qe-flag--last { border-bottom: 0; }
.ah-qe-chip { display: inline-flex; align-items: center; gap: 4px; font-size: .78rem; padding: 4px 11px; border: 1px solid #d1dae8; border-radius: 20px; cursor: pointer; background: #fff; user-select: none; transition: all .12s; }
.ah-qe-chip:hover { border-color: #b9c6e0; }
.ah-qe-chip.is-on { border-color: #4f7cf5; background: #e8efff; color: #1a49c4; }
</style>

<script>
(function($){
  function openModal(id){
    $('.ah-qe-modal.is-open').removeClass('is-open').attr('aria-hidden','true');
    $('#ah-qe-' + id).addClass('is-open').attr('aria-hidden','false');
    $('body').addClass('ah-qe-lock');
  }
  function closeModals(){
    $('.ah-qe-modal.is-open').removeClass('is-open').attr('aria-hidden','true');
    $('body').removeClass('ah-qe-lock');
  }
  $(document).on('click', '.ah-qe-open', function() {
    openModal($(this).data('id'));
  });
  $(document).on('click', '.ah-qe-close, .ah-qe-backdrop', function() {
    closeModals();
  });
  /* Esc closes the open modal */
  $(document).on('keydown', function(e){
    if (e.key === 'Escape' || e.keyCode === 27) closeModals();
  });
  /* Chip toggle: click label → toggle checkbox + restyle via class */
  $(document).on('click', '.ah-qe-chip', function(e) {
    e.preventDefault();
    var $chip = $(this), $cb = $chip.find('.ah-qe-term');
    var checked = !$cb.prop('checked');
    $cb.prop('checked', checked);
    $chip.toggleClass('is-on', checked);
  });
  /* Highlight Links: add row */
  $(document).on('click', '.ah-qe-hl-add', function() {
    var id = $(this).data('id');
    $('#ah-qe-hl-rows-' + id).append(
      '<div class="ah-qe-hl-row" style="display:flex;gap:6px;margin-bottom:5px;align-items:center;">' +
      '<input type="text" class="ah-qe-hl-name" placeholder="Label" style="flex:1;min-width:0;padding:4px 8px;border:1px solid #d1dae8;border-radius:4px;font-size:.82rem;outline:none;">' +
      '<input type="text" class="ah-qe-hl-url"  placeholder="/slug/ or URL" style="flex:1.6;min-width:0;padding:4px 8px;border:1px solid #d1dae8;border-radius:4px;font-size:.82rem;outline:none;">' +
      '<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-qe-hl-remove" style="flex-shrink:0;padding:3px 8px;">✕</button>' +
      '</div>'
    );
  });
  /* Highlight Links: remove row */
  $(document).on('click', '.ah-qe-hl-remove', function() {
    $(this).closest('.ah-qe-hl-row').remove();
  });

  /* Related Content: add row (clone the per-panel hidden template) */
  $(document).on('click', '.ah-rl-add', function() {
    var $wrap = $(this).closest('.ah-rl-wrap');
    var $new  = $wrap.children('.ah-rl-template').children('.ah-rl-row').first().clone();
    $wrap.children('.ah-rl-rows').append($new);
  });
  /* Related Content: remove row */
  $(document).on('click', '.ah-rl-remove', function() {
    $(this).closest('.ah-rl-row').remove();
  });

  $(document).on('click', '.ah-qe-save', function() {
    var $btn = $(this), id = $btn.data('id'), $row = $('#ah-qe-' + id);
    var taxIds = [];
    $row.find('.ah-qe-term:checked').each(function() { taxIds.push($(this).val()); });
    /* Collect highlight link pairs */
    var hlLinks = [];
    $row.find('#ah-qe-hl-rows-' + id + ' .ah-qe-hl-row').each(function() {
      var name = $.trim($(this).find('.ah-qe-hl-name').val());
      var url  = $.trim($(this).find('.ah-qe-hl-url').val());
      if (name || url) hlLinks.push({ name: name, url: url });
    });
    /* Collect Related Content rows (real rows only, not the hidden template) */
    var relatedLinks = [];
    $row.find('.ah-rl-wrap .ah-rl-rows .ah-rl-row').each(function() {
      var $r     = $(this);
      var url    = $.trim($r.find('.ah-rl-url').val());
      var target = $r.find('.ah-rl-target').val();
      if (!url && !target) return; // skip empty rows
      relatedLinks.push({
        link_type:     $r.find('.ah-rl-type').val(),
        target:        target,
        url:           url,
        label:         $.trim($r.find('.ah-rl-label').val()),
        container:     $.trim($r.find('.ah-rl-container').val()),
        target_window: $r.find('.ah-rl-window').val(),
        sort_order:    $r.find('.ah-rl-order').val()
      });
    });
    $btn.text('Saving…').prop('disabled', true);
    $.post(ahAdmin.ajaxUrl, {
      action:          'ah_quick_save_post_meta',
      nonce:           ahAdmin.nonce,
      post_id:         id,
      is_featured:     $row.find('.ah-qe-featured').is(':checked')  ? 1 : 0,
      is_popular:      $row.find('.ah-qe-popular').is(':checked')   ? 1 : 0,
      is_suggested:    $row.find('.ah-qe-suggested').is(':checked') ? 1 : 0,
      taxonomy_ids:    taxIds,
      highlight_links: JSON.stringify(hlLinks),
      related_links:   JSON.stringify(relatedLinks)
    }, function(res) {
      if (res.success) {
        window.location.reload();
      } else {
        alert('Error: ' + (res.data && res.data.message ? res.data.message : 'Save failed.'));
        $btn.text('Save Changes').prop('disabled', false);
      }
    }).fail(function() {
      alert('Network error. Please try again.');
      $btn.text('Save Changes').prop('disabled', false);
    });
  });
})(jQuery);
</script>
</div>
