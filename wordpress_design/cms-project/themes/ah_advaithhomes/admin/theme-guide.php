<?php
defined( 'ABSPATH' ) || exit;

/**
 * Guide To Use - tabbed reference page.
 *
 * HOW TO ADD A NEW TAB:
 *   1. Add an entry to $guide_tabs below.
 *   2. Write the content inside the 'render' callable.
 *   Done - nav and URL routing are automatic.
 */

$guide_tabs = [

	// ── Sample 1 ────────────────────────────────────────────────────────────
	'sample1' => [
		'label' => 'Sample 1',
		'icon'  => '📋',
		'render' => function() { ?>
			<div class="ah-admin-box">
				<h2>Sample Tab 1 - Rules &amp; Conventions</h2>
				<p style="color:#374151;font-size:.9rem;margin-bottom:20px;">
					This tab is a placeholder to show the tab system works. Replace the content
					inside the <code>'render'</code> callable in <code>admin/theme-guide.php</code>.
				</p>

				<table class="ah-admin-table">
					<thead>
						<tr>
							<th>Rule</th>
							<th>Detail</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong>Always escape output</strong></td>
							<td>Use <code>esc_html()</code>, <code>esc_url()</code>, <code>esc_attr()</code></td>
							<td><span class="ah-badge ah-badge--ok">Required</span></td>
						</tr>
						<tr>
							<td><strong>DB queries via $wpdb</strong></td>
							<td>Use <code>$wpdb->prepare()</code> for all user-supplied values</td>
							<td><span class="ah-badge ah-badge--ok">Required</span></td>
						</tr>
						<tr>
							<td><strong>Options naming</strong></td>
							<td>Prefix all option keys with <code>ah_</code></td>
							<td><span class="ah-badge ah-badge--warn">Convention</span></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="ah-admin-box">
				<h2>Code Snippet Example</h2>
				<p style="color:#374151;font-size:.875rem;margin-bottom:12px;">Get a theme option safely:</p>
				<pre style="background:#1e293b;color:#e2e8f0;padding:16px 20px;border-radius:8px;font-size:.8rem;overflow-x:auto;line-height:1.65;">$raw  = get_option( 'ah_my_option', '[]' );
$data = json_decode( $raw, true ) ?: [];</pre>
			</div>
		<?php },
	],

	// ── Sample 2 ────────────────────────────────────────────────────────────
	'sample2' => [
		'label' => 'Sample 2',
		'icon'  => '🔧',
		'render' => function() { ?>
			<div class="ah-admin-box">
				<h2>Sample Tab 2 - Snippets &amp; References</h2>
				<p style="color:#374151;font-size:.9rem;margin-bottom:20px;">
					Another placeholder tab. Add as many tabs as you need by copying the array
					entry pattern in <code>admin/theme-guide.php</code>.
				</p>

				<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
					<?php
					$cards = [
						[ 'icon' => '🗄️', 'title' => 'Custom Tables',   'text' => 'All CMS tables are prefixed with wp_ah_. Use AH_DB_Helper::table() to get the full name.' ],
						[ 'icon' => '🎨', 'title' => 'CSS Variables',   'text' => 'Brand colours live in assets/css/variables.css as --client-color-* tokens.' ],
						[ 'icon' => '🧩', 'title' => 'Components',      'text' => 'Reusable partials are in /components/. Load via get_template_part().' ],
						[ 'icon' => '📦', 'title' => 'Block Renderers', 'text' => 'Page builder blocks are handled in ah_render_builder_block() in template-builder-page.php.' ],
					];
					foreach ( $cards as $c ) : ?>
					<div class="ah-admin-card" style="display:flex;align-items:flex-start;gap:14px;padding:18px 20px;">
						<span style="font-size:1.8rem;flex-shrink:0;"><?php echo $c['icon']; ?></span>
						<div>
							<div style="font-weight:700;font-size:.9rem;margin-bottom:5px;"><?php echo esc_html( $c['title'] ); ?></div>
							<div style="font-size:.8rem;color:#64748b;line-height:1.55;"><?php echo esc_html( $c['text'] ); ?></div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="ah-admin-box">
				<h2>Useful Admin URLs</h2>
				<table class="ah-admin-table">
					<thead><tr><th>Page</th><th>URL</th></tr></thead>
					<tbody>
						<?php
						$links = [
							[ 'Section Controls', admin_url( 'admin.php?page=ah-theme-sections' ) ],
							[ 'Content Controls', admin_url( 'admin.php?page=ah-theme-content'  ) ],
							[ 'Install Mock Data', admin_url( 'admin.php?page=ah-theme-mock'    ) ],
							[ 'Cleanup Data',      admin_url( 'admin.php?page=ah-theme-cleanup' ) ],
						];
						foreach ( $links as [ $label, $url ] ) : ?>
						<tr>
							<td><?php echo esc_html( $label ); ?></td>
							<td><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $url ); ?></a></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php },
	],

	// ── Memorize ────────────────────────────────────────────────────────────
	'memorize' => [
		'label' => 'Memorize',
		'icon'  => '🧠',
		'render' => function() { ?>

			<!-- Page Builder URL params -->
			<div class="ah-admin-box">
				<h2>📄 Page Builder Template - URL Parameters</h2>
				<p style="color:#374151;font-size:.875rem;margin-bottom:16px;">
					File: <code>plugins/plugin1/templates/template-builder-page.php</code><br>
					Append these to any builder-page URL to control what gets rendered.
				</p>
				<table class="ah-admin-table">
					<thead>
						<tr><th>Parameter</th><th>Value</th><th>Effect</th><th>Example</th></tr>
					</thead>
					<tbody>
						<tr>
							<td><code>bare</code></td>
							<td><code>1</code></td>
							<td>Strips <strong>both</strong> header and footer. Outputs a full but minimal HTML document with <code>wp_head()</code> so styles still load.</td>
							<td><code>/my-page/?bare=1</code></td>
						</tr>
						<tr>
							<td><code>no_header</code></td>
							<td><code>1</code></td>
							<td>Skips <code>get_header()</code> only. Outputs minimal <code>&lt;html&gt;&lt;head&gt;…&lt;body&gt;</code> with <code>wp_head()</code>.</td>
							<td><code>/my-page/?no_header=1</code></td>
						</tr>
						<tr>
							<td><code>no_footer</code></td>
							<td><code>1</code></td>
							<td>Skips <code>get_footer()</code> only. Still calls <code>wp_footer()</code> so scripts load, then closes <code>&lt;/body&gt;&lt;/html&gt;</code>.</td>
							<td><code>/my-page/?no_footer=1</code></td>
						</tr>
					</tbody>
				</table>
				<p style="margin-top:14px;font-size:.825rem;color:#64748b;">
					💡 <code>bare=1</code> is shorthand for <code>no_header=1&amp;no_footer=1</code> combined.
					Use it for iframes, popups, or AJAX-loaded panels where the theme chrome is not needed.
				</p>
			</div>

			<!-- Static Page Template URL params -->
			<div class="ah-admin-box">
				<h2>🖼️ Static HTML Page Template - URL Parameters</h2>
				<p style="color:#374151;font-size:.875rem;margin-bottom:16px;">
					File: <code>themes/ah_advaithhomes/template-static-page.php</code><br>
					WordPress template: <em>"Static HTML Page"</em> - assign it in Page Attributes.<br>
					HTML source file is loaded from <code>themes/ah_advaithhomes/static/&lt;page-slug&gt;.html</code>.
				</p>
				<table class="ah-admin-table">
					<thead>
						<tr><th>Parameter</th><th>Value</th><th>Effect</th><th>Example</th></tr>
					</thead>
					<tbody>
						<tr>
							<td><code>bare</code></td>
							<td><code>1</code></td>
							<td>Strips header + footer, keeps styles &amp; scripts via <code>wp_head()</code> / <code>wp_footer()</code>.</td>
							<td><code>/my-static-page/?bare=1</code></td>
						</tr>
						<tr>
							<td><code>no_header</code></td>
							<td><code>1</code></td>
							<td>Skips theme header only.</td>
							<td><code>/my-static-page/?no_header=1</code></td>
						</tr>
						<tr>
							<td><code>no_footer</code></td>
							<td><code>1</code></td>
							<td>Skips footer + CTA section only.</td>
							<td><code>/my-static-page/?no_footer=1</code></td>
						</tr>
						<tr>
							<td><code>iframe</code></td>
							<td><code>true</code></td>
							<td>Renders the static HTML inside a self-resizing <code>&lt;iframe srcdoc&gt;</code> instead of inline. Useful for sandboxing third-party HTML.</td>
							<td><code>/my-static-page/?iframe=true</code></td>
						</tr>
					</tbody>
				</table>
				<p style="margin-top:14px;font-size:.825rem;color:#64748b;">
					💡 Combine freely: <code>?bare=1&amp;iframe=true</code> gives a clean page with the static HTML sandboxed inside an iframe - perfect for embedding in a modal.
				</p>
			</div>

			<!-- Quick combos cheatsheet -->
			<div class="ah-admin-box">
				<h2>⚡ Common Use-Case Combos</h2>
				<table class="ah-admin-table">
					<thead>
						<tr><th>Use Case</th><th>URL to use</th></tr>
					</thead>
					<tbody>
						<tr>
							<td>Embed in an <code>&lt;iframe&gt;</code> inside another page</td>
							<td><code>?bare=1</code></td>
						</tr>
						<tr>
							<td>Open in a JS popup / modal (no nav chrome)</td>
							<td><code>?bare=1</code></td>
						</tr>
						<tr>
							<td>Show page without top navigation only</td>
							<td><code>?no_header=1</code></td>
						</tr>
						<tr>
							<td>Show page without footer / CTA only</td>
							<td><code>?no_footer=1</code></td>
						</tr>
						<tr>
							<td>Static HTML page sandboxed in iframe, no chrome</td>
							<td><code>?bare=1&amp;iframe=true</code> <em>(static template only)</em></td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Theme CSS classes cheatsheet -->
			<div class="ah-admin-box">
				<h2>🎨 Theme CSS Classes - Quick Reference</h2>
				<p style="color:#374151;font-size:.875rem;margin-bottom:20px;">
					These classes are available on every page where the theme stylesheets load
					(<code>assets/css/base.css</code>, <code>components.css</code>).
					Use them in page builder blocks, page templates, and static HTML files instead of writing inline styles.
				</p>

				<h3 style="font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:20px 0 8px;">Layout</h3>
				<table class="ah-admin-table">
					<thead><tr><th>Class</th><th>What it does</th></tr></thead>
					<tbody>
						<tr><td><code>.container</code></td><td>Max-width 1280 px, horizontal padding, centered</td></tr>
						<tr><td><code>.container--sm</code></td><td>Max-width 800 px</td></tr>
						<tr><td><code>.container--md</code></td><td>Max-width 1040 px</td></tr>
						<tr><td><code>.container--xl</code></td><td>Max-width 1480 px</td></tr>
						<tr><td><code>.section</code></td><td>Vertical padding using <code>--section-py</code></td></tr>
						<tr><td><code>.section--sm</code></td><td>60 % of default section padding</td></tr>
						<tr><td><code>.section--lg</code></td><td>140 % of default section padding</td></tr>
						<tr><td><code>.section--alt</code></td><td>Light alternate background (<code>--bg-alt</code>)</td></tr>
						<tr><td><code>.section--dark</code></td><td>Dark slate background, white text</td></tr>
						<tr><td><code>.section--pattern</code></td><td>Grid-line overlay + subtle brand tint</td></tr>
						<tr><td><code>.section__header</code></td><td>Bottom-margin block for heading + desc group</td></tr>
						<tr><td><code>.section__eyebrow</code></td><td>Small all-caps accent label above heading</td></tr>
						<tr><td><code>.section__title</code></td><td>Display-font large heading</td></tr>
						<tr><td><code>.section__desc</code></td><td>Muted body text below heading</td></tr>
					</tbody>
				</table>

				<h3 style="font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:20px 0 8px;">Grid Helpers</h3>
				<table class="ah-admin-table">
					<thead><tr><th>Class</th><th>What it does</th></tr></thead>
					<tbody>
						<tr><td><code>.grid-2</code></td><td>2-column CSS grid, 24 px gap</td></tr>
						<tr><td><code>.grid-3</code></td><td>3-column CSS grid, 24 px gap</td></tr>
						<tr><td><code>.grid-4</code></td><td>4-column CSS grid, 24 px gap</td></tr>
						<tr><td><code>.grid-auto</code></td><td>Auto-fill, min 280 px columns, 24 px gap</td></tr>
					</tbody>
				</table>

				<h3 style="font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:20px 0 8px;">Buttons</h3>
				<table class="ah-admin-table">
					<thead><tr><th>Class</th><th>What it does</th></tr></thead>
					<tbody>
						<tr><td><code>.btn</code></td><td>Base button - required on all button variants</td></tr>
						<tr><td><code>.btn-primary</code></td><td>Dark accent fill</td></tr>
						<tr><td><code>.btn-gold</code></td><td>Gold brand fill</td></tr>
						<tr><td><code>.btn-outline</code></td><td>Transparent + accent border</td></tr>
						<tr><td><code>.btn-ghost</code></td><td>Transparent, subtle hover</td></tr>
						<tr><td><code>.btn-white</code></td><td>White fill - use on dark backgrounds</td></tr>
						<tr><td><code>.btn-sm</code></td><td>Smaller padding + font size</td></tr>
						<tr><td><code>.btn-lg</code></td><td>Larger padding + font size</td></tr>
						<tr><td><code>.btn-block</code></td><td>Full-width, centered text</td></tr>
					</tbody>
				</table>

				<h3 style="font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:20px 0 8px;">Cards</h3>
				<table class="ah-admin-table">
					<thead><tr><th>Class</th><th>What it does</th></tr></thead>
					<tbody>
						<tr><td><code>.card</code></td><td>White box, border, radius, hover shadow + lift</td></tr>
						<tr><td><code>.card__img</code></td><td>16:9 image wrapper with zoom-on-hover</td></tr>
						<tr><td><code>.card__body</code></td><td>24 px padding content area</td></tr>
						<tr><td><code>.card__eyebrow</code></td><td>Small uppercase label in accent colour</td></tr>
						<tr><td><code>.card__title</code></td><td>Display font heading inside card</td></tr>
						<tr><td><code>.card__excerpt</code></td><td>Muted secondary text paragraph</td></tr>
						<tr><td><code>.card__meta</code></td><td>Flex row for date/category/author chips</td></tr>
						<tr><td><code>.card__footer</code></td><td>Bordered bottom action row</td></tr>
					</tbody>
				</table>

				<h3 style="font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:20px 0 8px;">Typography &amp; Utilities</h3>
				<table class="ah-admin-table">
					<thead><tr><th>Class</th><th>What it does</th></tr></thead>
					<tbody>
						<tr><td><code>.text-center</code></td><td>Centre-aligns text</td></tr>
						<tr><td><code>.text-right</code></td><td>Right-aligns text</td></tr>
						<tr><td><code>.fw-700</code></td><td>Bold weight</td></tr>
					</tbody>
				</table>

				<p style="margin-top:16px;font-size:.825rem;color:#64748b;">
					💡 Example - a gold CTA button: <code>&lt;a href="#" class="btn btn-gold"&gt;Book a Call&lt;/a&gt;</code>
				</p>
			</div>

		<?php },
	],

];

// ── Active tab resolution ───────────────────────────────────────────────────
$active_tab = sanitize_key( $_GET['tab'] ?? '' );
if ( ! isset( $guide_tabs[ $active_tab ] ) ) {
	$active_tab = array_key_first( $guide_tabs );
}
$tab_base_url = admin_url( 'admin.php?page=ah-theme-guide' );
?>
<div class="wrap ah-admin-wrap">

  <div class="ah-admin-header">
    <div class="ah-admin-logo">AH</div>
    <div>
      <h1><?php esc_html_e( 'Guide To Use', 'ah-theme' ); ?></h1>
      <p><?php esc_html_e( 'Reference docs, rules, and snippets for this theme', 'ah-theme' ); ?></p>
    </div>
  </div>

  <!-- Tab nav -->
  <nav class="nav-tab-wrapper" style="margin-bottom:24px;">
    <?php foreach ( $guide_tabs as $slug => $tab ) :
      $is_active = $slug === $active_tab;
      $url       = add_query_arg( 'tab', $slug, $tab_base_url );
    ?>
    <a href="<?php echo esc_url( $url ); ?>"
       class="nav-tab<?php echo $is_active ? ' nav-tab-active' : ''; ?>"
       style="display:inline-flex;align-items:center;gap:6px;">
      <span><?php echo $tab['icon']; ?></span>
      <?php echo esc_html( $tab['label'] ); ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- Active tab content -->
  <?php ( $guide_tabs[ $active_tab ]['render'] )(); ?>

</div>
