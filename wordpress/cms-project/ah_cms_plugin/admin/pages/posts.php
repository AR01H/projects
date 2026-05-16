<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$notice = '';

// ── Post templates ────────────────────────────────────────────────────────────
function ah_post_templates(): array {
	return array(
		'blog' => array(
			'label'   => 'Blog Post',
			'icon'    => '✍️',
			'desc'    => 'Standard blog post with intro, body sections and conclusion',
			'excerpt' => 'A short summary of what this post covers — edit to match your topic.',
			'content' => '<!-- wp:paragraph -->
<p>Start with a strong opening paragraph that hooks the reader and tells them what they\'ll learn from this post.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Main Section Heading</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Expand on your main point here. Use clear, simple language and back it up with examples or data.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Second Section</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Continue with more detail. Break complex ideas into short paragraphs for easy reading.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Key Takeaways</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul><li>First important point from this post</li><li>Second important point</li><li>Third important point</li></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p><strong>Ready to take the next step?</strong> <a href="/free-consultation/">Book a free consultation</a> and we\'ll help you get started.</p>
<!-- /wp:paragraph -->',
		),

		'news' => array(
			'label'   => 'News Article',
			'icon'    => '📰',
			'desc'    => 'News-style format with headline, lead paragraph and body',
			'excerpt' => 'Brief summary of the news — who, what, when, where.',
			'content' => '<!-- wp:paragraph -->
<p><strong>Lead paragraph:</strong> Summarise the key news in one or two sentences. Cover who, what, when, and why it matters.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Expand on the story here. Provide context, background, and additional detail that readers need to understand the news fully.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>What This Means for You</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Explain the practical impact for your audience. How does this news affect them directly?</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>For more information or to speak with one of our experts, <a href="/contact/">contact us today</a>.</p>
<!-- /wp:paragraph -->',
		),

		'guide' => array(
			'label'   => 'Step-by-Step Guide',
			'icon'    => '📋',
			'desc'    => 'How-to guide with numbered steps and tips',
			'excerpt' => 'A complete guide to help you understand and navigate the process.',
			'content' => '<!-- wp:paragraph -->
<p>This guide walks you through the complete process step by step. Whether you\'re doing this for the first time or need a refresher, follow these steps for the best results.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Step 1: Get Prepared</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Before you start, make sure you have everything in order. Explain what they need to prepare here.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Step 2: Take Action</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Walk through the main action in detail. Include any important notes or warnings they should be aware of.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Step 3: Review and Confirm</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Describe what to check or confirm once the main action is done.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Common Questions</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><strong>Q: Add a common question here?</strong><br>A: Provide a clear and helpful answer.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Need help? <a href="/free-consultation/">Speak to an expert</a> — it\'s free and there\'s no obligation.</p>
<!-- /wp:paragraph -->',
		),

		'casestudy' => array(
			'label'   => 'Client Story / Case Study',
			'icon'    => '🏆',
			'desc'    => 'Client success story with challenge, solution and result',
			'excerpt' => 'How we helped a client achieve their goal — read the full story.',
			'content' => '<!-- wp:paragraph -->
<p>Every client has a unique story. Here\'s how we helped one family navigate their property journey from start to successful completion.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>The Challenge</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Describe the client\'s situation and what problem they were facing. What made it difficult? What were they worried about?</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Our Approach</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Explain how you stepped in and what you did differently. What specific advice, service, or action made the difference?</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>The Result</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Share the outcome — did they save money? Complete faster? Avoid a costly mistake? Use specific numbers or outcomes where possible.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>"Add a genuine client quote here — it builds trust and makes the story real."</p><cite>— Client Name, Location</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>Want results like these? <a href="/free-consultation/">Book your free consultation</a> today.</p>
<!-- /wp:paragraph -->',
		),

		'faq' => array(
			'label'   => 'FAQ / Q&amp;A Post',
			'icon'    => '❓',
			'desc'    => 'Question and answer format — great for SEO',
			'excerpt' => 'Answers to the most common questions about this topic.',
			'content' => '<!-- wp:paragraph -->
<p>We get asked these questions all the time. Here are clear, honest answers to help you understand exactly what to expect.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Q: What is the first question?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A: Give a clear, helpful answer here. Avoid jargon and speak directly to what the reader actually needs to know.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Q: What is the second question?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A: Another helpful answer. Keep each answer focused on one point.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Q: What is the third question?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A: Continue the pattern. You can add as many Q&amp;A pairs as you need.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Still have questions? <a href="/contact/">Get in touch</a> — we\'re happy to help.</p>
<!-- /wp:paragraph -->',
		),
	);
}

// ── POST: create from template → redirect to Gutenberg ───────────────────────
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_posts_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_posts_nonce'], 'ah_create_post' ) ) wp_die( 'Security check failed.' );

	$tpl_key  = sanitize_key( $_POST['template_key'] ?? 'blog' );
	$tpls     = ah_post_templates();
	$tpl      = $tpls[ $tpl_key ] ?? reset( $tpls );
	$title    = sanitize_text_field( $_POST['post_title'] ?? '' ) ?: $tpl['label'];
	$excerpt  = sanitize_textarea_field( $tpl['excerpt'] );
	$content  = $tpl['content'];

	$new_id = wp_insert_post( array(
		'post_type'    => 'post',
		'post_status'  => 'draft',
		'post_title'   => $title,
		'post_content' => $content,
		'post_excerpt' => $excerpt,
	) );

	if ( $new_id && ! is_wp_error( $new_id ) ) {
		wp_redirect( get_edit_post_link( $new_id, 'redirect' ) );
		exit;
	}
	$notice = 'Could not create post. Please try again.';
}

// ── GET: trash ────────────────────────────────────────────────────────────────
if ( isset( $_GET['trash_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_trash_post' ) ) {
	wp_trash_post( (int) $_GET['trash_id'] );
	$notice = 'Post moved to trash.';
}

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
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-edit"></span> Posts / Blog</h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

<?php /* ══════════════ TEMPLATES VIEW ══════════════ */ ?>
<?php if ( $action === 'templates' ) :
  $tpls = ah_post_templates();
?>
  <div class="ah-table-top" style="margin-bottom:0;">
    <p style="color:var(--ah-muted);margin:0;">Pick a template — your post will be created as a draft and open in the WordPress editor ready to customise.</p>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-posts' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">&larr; Back</a>
  </div>
  <p style="margin:8px 0 24px;"></p>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
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
            <div class="ah-form-row" style="margin-bottom:16px;">
              <label style="font-size:.8rem;margin-bottom:4px;display:block;font-weight:600;">Post Title</label>
              <input type="text" name="post_title" placeholder="<?php echo esc_attr( $tpl['label'] ); ?>" style="width:100%;box-sizing:border-box;">
            </div>
            <button type="submit" class="ah-btn ah-btn-primary" style="width:100%;justify-content:center;">
              Use This Template &rarr;
            </button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<?php /* ══════════════ LIST VIEW ══════════════ */ ?>
<?php else : ?>

  <div class="ah-table-top">
    <form class="ah-search-form" method="get">
      <input type="hidden" name="page" value="ah-posts">
      <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search posts…">
      <select name="status">
        <option value="">All Statuses</option>
        <?php foreach ( array( 'publish' => 'Published', 'draft' => 'Draft', 'private' => 'Private', 'pending' => 'Pending' ) as $sv => $sl ) : ?>
          <option value="<?php echo $sv; ?>" <?php selected( $status_f, $sv ); ?>><?php echo $sl; ?></option>
        <?php endforeach; ?>
      </select>
      <button class="ah-btn ah-btn-secondary">Filter</button>
    </form>
    <div style="display:flex;gap:8px;">
      <a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>" class="ah-btn ah-btn-secondary">+ Blank Post</a>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-posts', 'action' => 'templates' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary">📋 From Template</a>
    </div>
  </div>

  <?php if ( empty( $posts_list ) ) : ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:8px;">
      <a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"
         class="ah-card" style="text-decoration:none;color:inherit;text-align:center;padding:36px 24px;transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
        <div style="font-size:2.5rem;margin-bottom:12px;">✍️</div>
        <h3 style="margin:0 0 8px;">Blank Post</h3>
        <p style="color:var(--ah-muted);margin:0;font-size:.85rem;">Open the WordPress editor with a blank post — write freely.</p>
      </a>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-posts', 'action' => 'templates' ), admin_url( 'admin.php' ) ) ); ?>"
         class="ah-card" style="text-decoration:none;color:inherit;text-align:center;padding:36px 24px;transition:box-shadow .15s;border-top:3px solid var(--ah-primary);" onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
        <div style="font-size:2.5rem;margin-bottom:12px;">📋</div>
        <h3 style="margin:0 0 8px;">From Template</h3>
        <p style="color:var(--ah-muted);margin:0;font-size:.85rem;">Choose a pre-filled template — Blog, News, Guide, Case Study, FAQ.</p>
      </a>
    </div>
  <?php else : ?>
    <div class="ah-table-wrap">
      <table class="ah-table">
        <thead>
          <tr><th>Title</th><th>Categories</th><th>Status</th><th>Author</th><th>Modified</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ( $posts_list as $p ) :
            $cats   = get_the_category( $p->ID );
            $author = get_the_author_meta( 'display_name', $p->post_author );
            $badge  = array( 'publish' => 'active', 'draft' => 'draft', 'private' => 'inactive', 'pending' => 'draft' );
            $label  = array( 'publish' => 'Published', 'draft' => 'Draft', 'private' => 'Private', 'pending' => 'Pending' );
          ?>
            <tr>
              <td>
                <strong><?php echo esc_html( $p->post_title ?: '(no title)' ); ?></strong>
                <?php if ( $p->post_excerpt ) : ?>
                  <small style="color:var(--ah-muted);display:block;"><?php echo esc_html( wp_trim_words( $p->post_excerpt, 10 ) ); ?></small>
                <?php endif; ?>
              </td>
              <td><small><?php echo $cats ? esc_html( implode( ', ', wp_list_pluck( $cats, 'name' ) ) ) : '—'; ?></small></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $badge[ $p->post_status ] ?? 'draft' ); ?>"><?php echo esc_html( $label[ $p->post_status ] ?? $p->post_status ); ?></span></td>
              <td><small><?php echo esc_html( $author ); ?></small></td>
              <td><small><?php echo esc_html( wp_date( 'M j, Y', strtotime( $p->post_modified ) ) ); ?></small></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                <?php if ( $p->post_status === 'publish' ) : ?>
                  <a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm">View</a>
                <?php endif; ?>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-posts', 'trash_id' => $p->ID ), admin_url( 'admin.php' ) ), 'ah_trash_post' ) ); ?>"
                   class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Move to trash?');">Trash</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ( $pages_count > 1 ) : ?>
      <div style="margin-top:16px;display:flex;gap:6px;">
        <?php for ( $pg = 1; $pg <= $pages_count; $pg++ ) : ?>
          <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-posts', 'paged' => $pg ), admin_url( 'admin.php' ) ) ); ?>"
             class="ah-btn ah-btn-sm <?php echo $pg === $paged ? 'ah-btn-primary' : 'ah-btn-secondary'; ?>"><?php echo $pg; ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

<?php endif; ?>
</div>
