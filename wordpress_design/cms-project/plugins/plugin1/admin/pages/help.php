<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$active_tab = sanitize_key( $_GET['tab'] ?? 'quick-start' );
?>
<div class="wrap ah-wrap">

<style>
.ah-help-hero{background:linear-gradient(135deg,#1e40af 0%,#3b82f6 100%);color:#fff;padding:32px 36px;border-radius:10px;margin-bottom:24px;display:flex;align-items:center;gap:20px;}
.ah-help-hero h1{margin:0 0 6px;color:#fff;font-size:1.6rem;}
.ah-help-hero p{margin:0;opacity:.85;font-size:.95rem;}
.ah-help-hero .ah-hero-icon{font-size:3.5rem;flex-shrink:0;}
.ah-help-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px;}
.ah-help-tab{padding:8px 16px;border:2px solid #e2e8f0;border-radius:6px;background:#fff;cursor:pointer;font-size:.85rem;font-weight:500;color:#475569;text-decoration:none;display:inline-block;transition:all .15s;}
.ah-help-tab:hover,.ah-help-tab.active{border-color:#1e40af;background:#1e40af;color:#fff;}
.ah-help-panel{display:none;}
.ah-help-panel.active{display:block;}
.ah-help-section{margin-bottom:28px;}
.ah-help-section h2{font-size:1.1rem;font-weight:700;margin:0 0 12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;color:#1e293b;}
.ah-help-section h3{font-size:.95rem;font-weight:600;margin:16px 0 6px;color:#334155;}
.ah-help-section p,.ah-help-section li{font-size:.875rem;line-height:1.7;color:#475569;}
.ah-help-section ul,.ah-help-section ol{padding-left:20px;margin:6px 0 12px;}
.ah-help-section li{margin-bottom:4px;}
.ah-step{display:flex;gap:14px;margin-bottom:14px;align-items:flex-start;}
.ah-step-num{background:#1e40af;color:#fff;border-radius:50%;width:26px;height:26px;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:700;flex-shrink:0;margin-top:2px;}
.ah-step-body{flex:1;}
.ah-step-body strong{display:block;font-size:.875rem;margin-bottom:2px;color:#1e293b;}
.ah-step-body p{margin:0;font-size:.82rem;}
.ah-tip{background:#f0f9ff;border-left:3px solid #38bdf8;padding:10px 14px;border-radius:0 6px 6px 0;margin:12px 0;font-size:.82rem;color:#0c4a6e;line-height:1.6;}
.ah-tip strong{color:#0369a1;}
.ah-warn{background:#fffbeb;border-left:3px solid #f59e0b;padding:10px 14px;border-radius:0 6px 6px 0;margin:12px 0;font-size:.82rem;color:#78350f;line-height:1.6;}
.ah-feature-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin:12px 0;}
.ah-feature-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;text-align:center;}
.ah-feature-card .icon{font-size:1.8rem;margin-bottom:6px;}
.ah-feature-card strong{display:block;font-size:.82rem;margin-bottom:4px;color:#1e293b;}
.ah-feature-card p{font-size:.75rem;color:#64748b;margin:0;}
.ah-kbd{display:inline-block;background:#f1f5f9;border:1px solid #cbd5e1;border-radius:3px;padding:1px 6px;font-size:.78rem;font-family:monospace;color:#334155;}
.ah-section-ref{display:grid;grid-template-columns:auto 1fr;gap:6px 16px;font-size:.82rem;margin:8px 0;}
.ah-section-ref dt{font-weight:600;color:#1e293b;white-space:nowrap;}
.ah-section-ref dd{color:#475569;margin:0;}
</style>

<!-- Hero -->
<div class="ah-help-hero">
  <div class="ah-hero-icon">📖</div>
  <div>
    <h1>Help &amp; Guide</h1>
    <p>Everything you need to know about managing your website with the CMS ADMIN. Pick a topic below to get started.</p>
  </div>
</div>

<!-- Tabs -->
<div class="ah-help-tabs">
  <?php
  $tabs = [
    'quick-start'   => '🚀 Quick Start',
    'content'       => '📝 Content',
    'page-sections' => '🏠 Page Sections',
    'post-editor'   => '✍️ Post Editor',
    'tools'         => '🛠 Tools',
    'troubleshoot'  => '🔧 Troubleshooting',
  ];
  foreach ( $tabs as $slug => $label ) : ?>
    <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ah-help', 'tab' => $slug ], admin_url( 'admin.php' ) ) ); ?>"
       class="ah-help-tab ah-tab-link<?php echo $active_tab === $slug ? ' active' : ''; ?>"
       data-tab="help-<?php echo esc_attr( $slug ); ?>"
       data-page="ah-help">
      <?php echo $label; ?>
    </a>
  <?php endforeach; ?>
</div>

<!-- ══════════ QUICK START ══════════ -->
<div id="help-quick-start" class="ah-help-panel<?php echo $active_tab === 'quick-start' ? ' active' : ''; ?>">
  <div class="ah-card" style="padding:24px;">

    <div class="ah-help-section">
      <h2>Welcome to CMS ADMIN</h2>
      <p>This CMS gives you full control over your website without needing to touch any code. Everything from the homepage hero to blog posts to contact forms can be managed from this admin panel.</p>
      <div class="ah-tip"><strong>Tip:</strong> All changes are saved directly to the database. There is no "preview mode" — changes go live as soon as you save.</div>
    </div>

    <div class="ah-help-section">
      <h2>What Each Section Does</h2>
      <div class="ah-feature-grid">
        <div class="ah-feature-card"><div class="icon">⚙️</div><strong>Site Settings</strong><p>Logo, contact details, social links, footer text</p></div>
        <div class="ah-feature-card"><div class="icon">📰</div><strong>News Bar</strong><p>Scrolling ticker messages across the top of the site</p></div>
        <div class="ah-feature-card"><div class="icon">🏠</div><strong>Home Sections</strong><p>Hero, services preview, stats, CTAs on the homepage</p></div>
        <div class="ah-feature-card"><div class="icon">🛎</div><strong>Services</strong><p>Service cards, features, pricing highlights</p></div>
        <div class="ah-feature-card"><div class="icon">👥</div><strong>Team Members</strong><p>Staff photos, names, roles, bio text</p></div>
        <div class="ah-feature-card"><div class="icon">⭐</div><strong>Reviews</strong><p>Customer testimonials and star ratings</p></div>
        <div class="ah-feature-card"><div class="icon">❓</div><strong>FAQs</strong><p>Frequently asked questions and answers</p></div>
        <div class="ah-feature-card"><div class="icon">✍️</div><strong>Posts / Blog</strong><p>Blog posts, news articles, guides, case studies</p></div>
        <div class="ah-feature-card"><div class="icon">🏆</div><strong>Client Stories</strong><p>Case study / success story pages</p></div>
        <div class="ah-feature-card"><div class="icon">🧩</div><strong>Page Builder</strong><p>Create custom landing pages visually</p></div>
        <div class="ah-feature-card"><div class="icon">📋</div><strong>Form Builder</strong><p>Build contact/enquiry forms with a shortcode</p></div>
        <div class="ah-feature-card"><div class="icon">📁</div><strong>File Links</strong><p>Manage downloadable PDF and document links</p></div>
        <div class="ah-feature-card"><div class="icon">📥</div><strong>Data Import</strong><p>Bulk import content from CSV files</p></div>
        <div class="ah-feature-card"><div class="icon">📋</div><strong>Audit Log</strong><p>See who changed what and when</p></div>
      </div>
    </div>

    <div class="ah-help-section">
      <h2>Your First Steps</h2>
      <div class="ah-step">
        <div class="ah-step-num">1</div>
        <div class="ah-step-body"><strong>Set up Site Settings</strong><p>Add your logo, business name, phone number, email, address, and social media links. These feed into the header and footer automatically.</p></div>
      </div>
      <div class="ah-step">
        <div class="ah-step-num">3</div>
        <div class="ah-step-body"><strong>Fill in the Homepage</strong><p>Go to Home Sections and update each tab — Hero, Services, Stats, About, and CTA sections. This is what visitors see first.</p></div>
      </div>
      <div class="ah-step">
        <div class="ah-step-num">4</div>
        <div class="ah-step-body"><strong>Add your Services</strong><p>Head to Services and add each service your business offers with a title, description, icon, and features list.</p></div>
      </div>
      <div class="ah-step">
        <div class="ah-step-num">5</div>
        <div class="ah-step-body"><strong>Write your first Blog Post</strong><p>Go to Posts / Blog → From Template, choose a template type, fill in the fields, and choose your editor. Your post will open as a draft ready to complete.</p></div>
      </div>
    </div>

  </div>
</div>

<!-- ══════════ CONTENT ══════════ -->
<div id="help-content" class="ah-help-panel<?php echo $active_tab === 'content' ? ' active' : ''; ?>">
  <div class="ah-card" style="padding:24px;">

    <div class="ah-help-section">
      <h2>News Bar</h2>
      <p>The news bar is a scrolling ticker that appears at the top of your website. Use it for promotions, announcements, or urgent notices.</p>
      <h3>Adding an Item</h3>
      <div class="ah-step">
        <div class="ah-step-num">1</div>
        <div class="ah-step-body"><strong>Click "+ Add Item"</strong><p>Opens the add form.</p></div>
      </div>
      <div class="ah-step">
        <div class="ah-step-num">2</div>
        <div class="ah-step-body"><strong>Fill in the Text</strong><p>This is the message shown in the ticker. Keep it short — under 100 characters works best.</p></div>
      </div>
      <div class="ah-step">
        <div class="ah-step-num">3</div>
        <div class="ah-step-body"><strong>Add a Link (optional)</strong><p>Paste a URL to make the text clickable. Choose "New Tab" if linking to an external site.</p></div>
      </div>
      <div class="ah-step">
        <div class="ah-step-num">4</div>
        <div class="ah-step-body"><strong>Set Dates (optional)</strong><p>Use Start Date and End Date to automatically show/hide a time-sensitive item. Leave blank to show always.</p></div>
      </div>
      <div class="ah-step">
        <div class="ah-step-num">5</div>
        <div class="ah-step-body"><strong>Set Status to Active</strong><p>Only Active items appear on the site. Set to Inactive to hide without deleting.</p></div>
      </div>
      <div class="ah-tip"><strong>Reordering:</strong> Drag the ☰ handle on the left of each row to change the order items appear in the ticker.</div>
    </div>

    <div class="ah-help-section">
      <h2>Posts / Blog</h2>
      <p>The Posts section manages all blog content — articles, news, guides, case studies, and FAQ posts. See the <strong>Post Editor</strong> tab for a detailed guide on creating and editing posts.</p>
      <h3>Post Statuses</h3>
      <dl class="ah-section-ref">
        <dt>Draft</dt><dd>Saved but not visible to site visitors.</dd>
        <dt>Published</dt><dd>Live on the website — anyone can read it.</dd>
        <dt>Private</dt><dd>Only logged-in admin users can see it.</dd>
        <dt>Pending</dt><dd>Ready for review before publishing.</dd>
      </dl>
      <h3>Moving a Post to Trash</h3>
      <p>Click the <strong>Trash</strong> button on the posts list. The post is moved to the WordPress trash and can be recovered from <em>Posts → Trash</em> in the standard WordPress admin.</p>
    </div>

    <div class="ah-help-section">
      <h2>Categories &amp; Tags</h2>
      <p>Organise your posts using categories (broad topics) and tags (specific keywords).</p>
      <ul>
        <li><strong>Categories</strong> — hierarchical groups. A post should have at least one. Create them under Categories &amp; Tags before writing the post.</li>
        <li><strong>Tags</strong> — flat keywords. Add as many as relevant. In the Form Editor, type tags separated by commas.</li>
      </ul>
      <div class="ah-tip"><strong>SEO benefit:</strong> Good categories and tags help search engines understand what your content is about.</div>
    </div>

  </div>
</div>

<!-- ══════════ PAGE SECTIONS ══════════ -->
<div id="help-page-sections" class="ah-help-panel<?php echo $active_tab === 'page-sections' ? ' active' : ''; ?>">
  <div class="ah-card" style="padding:24px;">

    <div class="ah-help-section">
      <h2>How Page Sections Work</h2>
      <p>Each page on your website is built from sections that you control here. Changes are saved immediately and appear on the live site. Most section pages use <strong>tabs</strong> at the top to separate different parts of that page.</p>
      <div class="ah-tip"><strong>Images:</strong> All image fields use the WordPress Media Library. Click "Choose Image" to open it, select or upload your photo, and click "Use this image". The preview updates immediately.</div>
    </div>

    <div class="ah-help-section">
      <h2>Home Sections</h2>
      <dl class="ah-section-ref">
        <dt>Hero</dt><dd>The large banner at the top of the homepage. Set the headline, subtext, background image, and call-to-action button.</dd>
        <dt>Services</dt><dd>A preview grid of your main services shown on the homepage. Controls which services appear and the section heading.</dd>
        <dt>Stats</dt><dd>Numbers/metrics bar (e.g. "500+ clients", "10 years experience"). Add each stat with a value, label, and optional icon.</dd>
        <dt>About Snippet</dt><dd>A short "About Us" teaser on the homepage linking to the full About page.</dd>
        <dt>CTA</dt><dd>A prominent call-to-action banner encouraging visitors to book or contact you.</dd>
      </dl>
    </div>

    <div class="ah-help-section">
      <h2>Services</h2>
      <p>Each service record has:</p>
      <ul>
        <li><strong>Title</strong> — name of the service</li>
        <li><strong>Slug</strong> — auto-generated URL-friendly name (used in the service's page URL)</li>
        <li><strong>Short Description</strong> — shown in service cards/grids</li>
        <li><strong>Full Description</strong> — detailed text shown on the service's own page</li>
        <li><strong>Icon</strong> — dashicon or image used in listings</li>
        <li><strong>Features list</strong> — bullet points of what's included</li>
        <li><strong>Status</strong> — Active services appear on the site; Inactive are hidden</li>
        <li><strong>Sort Order</strong> — controls the display order. Lower numbers appear first</li>
      </ul>
    </div>

    <div class="ah-help-section">
      <h2>Team Members</h2>
      <p>Each team member record includes name, role/title, bio, and a photo. Team members are displayed on the About page and anywhere the team section shortcode or block is used. Use <strong>Sort Order</strong> to control who appears first.</p>
    </div>

    <div class="ah-help-section">
      <h2>Reviews</h2>
      <p>Add customer testimonials with a name, rating (1–5 stars), review text, and optional company/location. Reviews with <strong>Active</strong> status appear in the reviews section and homepage testimonial slider.</p>
      <div class="ah-tip"><strong>Best practice:</strong> Use real first names and genuine quotes. Specificity builds trust — "saved us $4,000" is more compelling than "saved us money".</div>
    </div>

    <div class="ah-help-section">
      <h2>FAQs</h2>
      <p>Each FAQ has a question and an answer. You can:</p>
      <ul>
        <li>Group FAQs by category using the Category field</li>
        <li>Set display order using Sort Order</li>
        <li>Toggle Active/Inactive without deleting</li>
      </ul>
      <div class="ah-tip"><strong>SEO tip:</strong> FAQs that match common Google searches can drive significant organic traffic. Write questions exactly how customers ask them.</div>
    </div>

    <div class="ah-help-section">
      <h2>Client Stories</h2>
      <p>Structured case study pages with Challenge, Approach, and Result sections. Unlike blog posts, Client Stories follow a fixed format and appear in the dedicated Client Stories section of the site.</p>
    </div>

    <div class="ah-help-section">
      <h2>About Page</h2>
      <p>Controls all content on the About page including the main headline, intro text, mission statement, values list, and the team section. Changes here only affect the About page.</p>
    </div>

  </div>
</div>

<!-- ══════════ POST EDITOR ══════════ -->
<div id="help-post-editor" class="ah-help-panel<?php echo $active_tab === 'post-editor' ? ' active' : ''; ?>">
  <div class="ah-card" style="padding:24px;">

    <div class="ah-help-section">
      <h2>Creating a Post from a Template</h2>
      <p>Go to <strong>Posts / Blog → From Template</strong>. You'll see five template cards — each is pre-structured for a specific type of post:</p>
      <dl class="ah-section-ref">
        <dt>✍️ Blog Post</dt><dd>Standard article with intro, sections, and key takeaways.</dd>
        <dt>📰 News Article</dt><dd>News format with a lead paragraph and "What This Means" section.</dd>
        <dt>📋 Step-by-Step Guide</dt><dd>Numbered steps — set how many steps you need (2–10).</dd>
        <dt>🏆 Client Story</dt><dd>Challenge → Approach → Result structure with a client quote.</dd>
        <dt>❓ FAQ Post</dt><dd>Multiple Q&amp;A pairs — set topic and how many pairs you need (2–10).</dd>
      </dl>
      <p>Fill in the template fields, then click one of the two editor buttons:</p>
      <ul>
        <li><strong>📝 Form Editor</strong> — opens the structured section-based editor (see below)</li>
        <li><strong>🖊 WP Editor</strong> — opens the standard WordPress block (Gutenberg) editor</li>
      </ul>
      <div class="ah-tip"><strong>Which editor should I use?</strong> Use the Form Editor for simple, structured posts. Use the WP Editor when you need advanced formatting, embeds, galleries, or custom blocks.</div>
    </div>

    <div class="ah-help-section">
      <h2>The Form Editor</h2>
      <p>The Form Editor lets you build post content using structured sections — no drag-and-drop complexity. Each section type has its own focused fields.</p>

      <h3>Section Types</h3>
      <dl class="ah-section-ref">
        <dt>📝 Heading</dt><dd>Choose H2, H3, or H4, then type the heading text. H2 is the main section level; H3 is a sub-section.</dd>
        <dt>¶ Paragraph</dt><dd>A block of body text. Write naturally — line breaks within the textarea become &lt;br&gt; tags.</dd>
        <dt>• List</dt><dd>Choose Bullet or Numbered. Add as many items as you need with "+ Add Item". Remove any item with ✕.</dd>
        <dt>⊞ Table</dt><dd>Starts as a 2×1 table. Use "+ Row" to add rows and "+ Column" to add columns. Click each cell to type into it.</dd>
        <dt>" Quote</dt><dd>A styled blockquote. Add the quote text and an optional attribution line (e.g. "Jane Smith, Melbourne").</dd>
        <dt>⬡ CTA Button</dt><dd>A call-to-action button. Set the button label and the URL it links to (e.g. <span class="ah-kbd">/contact/</span> or <span class="ah-kbd">/free-consultation/</span>).</dd>
      </dl>

      <h3>Adding and Reordering Sections</h3>
      <ul>
        <li>Click any <strong>+ Section</strong> button at the bottom of the editor to add a new section below the existing ones.</li>
        <li>Use <strong>↑</strong> and <strong>↓</strong> buttons on each section card to move it up or down.</li>
        <li>Click <strong>✕ Remove</strong> to permanently remove a section.</li>
      </ul>

      <h3>The Sidebar</h3>
      <dl class="ah-section-ref">
        <dt>Save Draft</dt><dd>Saves the post without making it live. Use this while you're still writing.</dd>
        <dt>Publish</dt><dd>Makes the post live on the website immediately.</dd>
        <dt>Status</dt><dd>Manually set the post status (Draft, Published, Private, Pending Review).</dd>
        <dt>Publish Date</dt><dd>Schedule a future publish date/time. Leave blank to publish immediately.</dd>
        <dt>Categories</dt><dd>Tick one or more categories this post belongs to.</dd>
        <dt>Tags</dt><dd>Type comma-separated keywords, e.g. <span class="ah-kbd">property, mortgage, guide</span>.</dd>
        <dt>Featured Image</dt><dd>The main image shown in post listings and at the top of the post. Click "Choose Image" to open the Media Library.</dd>
      </dl>

      <h3>Switching to the WP Editor</h3>
      <p>At the top right of the Form Editor, click <strong>"Switch to WP Editor"</strong>. Your saved content (blocks) will open in Gutenberg. You can edit there and switch back — though any changes made in Gutenberg won't re-appear in the Form Editor's section cards (they'll still save correctly to the post).</p>
    </div>

    <div class="ah-help-section">
      <h2>The WordPress (Gutenberg) Editor</h2>
      <p>The standard WordPress block editor. Useful for:</p>
      <ul>
        <li>Adding image galleries</li>
        <li>Embedding YouTube videos or tweets</li>
        <li>Complex multi-column layouts</li>
        <li>Custom HTML blocks</li>
        <li>Reusable blocks shared across posts</li>
      </ul>
      <p>Common keyboard shortcuts in Gutenberg:</p>
      <ul>
        <li><span class="ah-kbd">Enter</span> — start a new paragraph block</li>
        <li><span class="ah-kbd">Ctrl + Z</span> — undo</li>
        <li><span class="ah-kbd">Ctrl + Shift + D</span> — duplicate the selected block</li>
        <li><span class="ah-kbd">/</span> at the start of a new block — search for a block type</li>
      </ul>
    </div>

    <div class="ah-help-section">
      <h2>How the Edit Button Remembers Your Editor</h2>
      <p>When you return to the Posts list and click <strong>Edit</strong>, the CMS automatically opens the same editor you used when creating the post — Form Editor or WP Editor. You'll see a small indicator in the "Editor" column: <strong>📝 Form</strong> or <strong>🖊 WP</strong>.</p>
    </div>

  </div>
</div>

<!-- ══════════ TOOLS ══════════ -->
<div id="help-tools" class="ah-help-panel<?php echo $active_tab === 'tools' ? ' active' : ''; ?>">
  <div class="ah-card" style="padding:24px;">

    <div class="ah-help-section">
      <h2>Page Builder</h2>
      <p>Create fully custom landing pages without writing code. Pages built here are served at their own URL (slug) and are separate from WordPress pages.</p>
      <h3>Creating a Page</h3>
      <div class="ah-step">
        <div class="ah-step-num">1</div>
        <div class="ah-step-body"><strong>Click "+ New Page"</strong><p>Enter the page title — the slug is auto-generated.</p></div>
      </div>
      <div class="ah-step">
        <div class="ah-step-num">2</div>
        <div class="ah-step-body"><strong>Use the visual editor</strong><p>Add rows, columns, and content blocks (text, image, button, divider, etc.).</p></div>
      </div>
      <div class="ah-step">
        <div class="ah-step-num">3</div>
        <div class="ah-step-body"><strong>Set the slug</strong><p>The slug determines the URL. For example slug <span class="ah-kbd">free-consultation</span> creates <span class="ah-kbd">yoursite.com/free-consultation</span>.</p></div>
      </div>
      <div class="ah-step">
        <div class="ah-step-num">4</div>
        <div class="ah-step-body"><strong>Set Status to Active</strong><p>Only Active pages are accessible to visitors.</p></div>
      </div>
      <div class="ah-tip"><strong>Tip:</strong> Builder pages bypass the WordPress theme template. They use the standalone page template from the plugin, so they load fast and look consistent regardless of theme.</div>
    </div>

    <div class="ah-help-section">
      <h2>Form Builder</h2>
      <p>Build contact forms and embed them anywhere using a shortcode.</p>
      <h3>Creating a Form</h3>
      <div class="ah-step">
        <div class="ah-step-num">1</div>
        <div class="ah-step-body"><strong>Click "+ New Form"</strong><p>Give the form a name (e.g. "Main Contact Form").</p></div>
      </div>
      <div class="ah-step">
        <div class="ah-step-num">2</div>
        <div class="ah-step-body"><strong>Add fields</strong><p>Click "+ Add Field" to add text inputs, email, phone, textarea, select dropdowns, checkboxes, and more.</p></div>
      </div>
      <div class="ah-step">
        <div class="ah-step-num">3</div>
        <div class="ah-step-body"><strong>Configure notifications</strong><p>Set the email address that receives submissions and the subject line.</p></div>
      </div>
      <div class="ah-step">
        <div class="ah-step-num">4</div>
        <div class="ah-step-body"><strong>Copy the shortcode</strong><p>After saving, you'll see a shortcode like <span class="ah-kbd">[ah_form id="3"]</span>. Paste this into any post, page, or Page Builder text block.</p></div>
      </div>
      <div class="ah-warn"><strong>Note:</strong> The shortcode only works on pages where jQuery is loaded. If pasting into a Page Builder page, use a "HTML" or "Shortcode" block type.</div>
    </div>

    <div class="ah-help-section">
      <h2>File Links</h2>
      <p>Upload PDFs or link to documents that visitors can download. Each file link record has a label, URL (or uploaded file), and an optional description. File links are displayed on the designated downloads/resources section of the site and can also be referenced in post content.</p>
    </div>

    <div class="ah-help-section">
      <h2>Data Import</h2>
      <p>Bulk-load content from a CSV file. Each import type (Services, FAQs, Team Members, etc.) has its own CSV template you can download, fill in, and upload.</p>
      <div class="ah-warn"><strong>Warning:</strong> Imports add new records — they don't update existing ones. Running the same import twice will create duplicates. Double-check your CSV before importing.</div>
    </div>

    <div class="ah-help-section">
      <h2>Admin Actions</h2>
      <p>One-click maintenance tasks such as clearing caches, regenerating thumbnails, running database migrations, or resetting specific data. Each action has a description — read it carefully before running. Some actions cannot be undone.</p>
    </div>

    <div class="ah-help-section">
      <h2>Audit Log</h2>
      <p>A read-only record of every significant action taken through this CMS — who added a service, who deleted a review, who changed site settings, and when. Useful for tracking changes and troubleshooting.</p>
    </div>

  </div>
</div>

<!-- ══════════ TROUBLESHOOTING ══════════ -->
<div id="help-troubleshoot" class="ah-help-panel<?php echo $active_tab === 'troubleshoot' ? ' active' : ''; ?>">
  <div class="ah-card" style="padding:24px;">

    <div class="ah-help-section">
      <h2>Common Issues &amp; Fixes</h2>

      <h3>My changes aren't appearing on the website</h3>
      <ul>
        <li>Hard-refresh the page: <span class="ah-kbd">Ctrl + Shift + R</span> (Windows) or <span class="ah-kbd">Cmd + Shift + R</span> (Mac).</li>
        <li>If your site uses a caching plugin (e.g. WP Rocket, W3 Total Cache), clear the cache from that plugin's settings.</li>
        <li>Check that the record Status is set to <strong>Active</strong> or <strong>Published</strong> — Inactive/Draft records are hidden.</li>
      </ul>

      <h3>The image I uploaded isn't showing</h3>
      <ul>
        <li>Make sure you clicked <strong>"Use this image"</strong> in the media library popup — just selecting it isn't enough.</li>
        <li>Check the image preview appeared in the picker (a thumbnail should show below the button).</li>
        <li>Save the record. The image is stored by its WordPress attachment ID — if you skip saving, the selection is lost.</li>
        <li>If the image shows in the admin but not on the site, clear your site's cache.</li>
      </ul>

      <h3>I saved a post but it's not live</h3>
      <ul>
        <li>Check the Post Status in the sidebar or list view — it may be set to <strong>Draft</strong>.</li>
        <li>In the Form Editor, use the <strong>Publish</strong> button (not "Save Draft") to make it live.</li>
        <li>In the WP Editor, click the blue <strong>Publish</strong> button at the top right.</li>
      </ul>

      <h3>The Form Editor sections disappeared after I switched to WP Editor</h3>
      <p>The Form Editor loads sections from saved meta. If you edited content in the WP Editor and saved there, the block content is stored in the post but the structured section data in meta was not updated. The post content is still correct — the section cards just won't reflect the Gutenberg edits. Continue editing in the WP Editor for that post, or re-build sections in the Form Editor.</p>

      <h3>A shortcode like [ah_form id="3"] is showing as plain text</h3>
      <ul>
        <li>Make sure the shortcode is in a <strong>Shortcode block</strong> in Gutenberg, not inside a Paragraph block.</li>
        <li>In Page Builder, use an <strong>HTML</strong> or <strong>Shortcode</strong> content block, not a plain Text block.</li>
        <li>The CMS ADMIN plugin must be active — check <em>Plugins</em> in WordPress admin.</li>
      </ul>

      <h3>A menu item isn't appearing on the website</h3>
      <ul>
        <li>Check the item Status is set to <strong>Active</strong>.</li>
        <li>Confirm you selected the correct menu type (Header vs Footer).</li>
        <li>Try reordering and saving — this re-syncs the menu data with the theme.</li>
      </ul>

      <h3>The News Bar isn't showing</h3>
      <ul>
        <li>At least one item must have Status = <strong>Active</strong>.</li>
        <li>Check Start Date / End Date — an item with a future Start Date or a past End Date will not show even if Active.</li>
        <li>The theme must include the news bar component. If the bar is missing entirely, confirm the theme calls <code>do_action('ah_newsbar')</code> in its header template.</li>
      </ul>

      <h3>I can't log in to the admin</h3>
      <p>This CMS uses standard WordPress authentication. Go to <span class="ah-kbd">yoursite.com/wp-admin</span> and use your WordPress username and password. If you've forgotten your password, use the "Lost your password?" link on the login page.</p>
    </div>

    <div class="ah-help-section">
      <h2>Need More Help?</h2>
      <p>If you're stuck on something not covered here, check the following in order:</p>
      <ol>
        <li>Re-read the relevant section above carefully — the answer is usually there.</li>
        <li>Check the <strong>Audit Log</strong> to see if a recent change caused the issue.</li>
        <li>Try the action in a different browser (rules out browser-specific caching).</li>
        <li>Contact your website developer with a description of: what you were doing, what you expected to happen, and what actually happened.</li>
      </ol>
      <div class="ah-tip"><strong>When contacting support</strong>, include a screenshot of the problem and the URL of the page where it occurs. This speeds up troubleshooting significantly.</div>
    </div>

  </div>
</div>

</div><!-- .wrap -->

<script>
(function(){
  // Simple tab switching without relying on the data-page attribute match
  var tabs   = document.querySelectorAll('.ah-help-tab');
  var panels = document.querySelectorAll('.ah-help-panel');

  tabs.forEach(function(tab) {
    tab.addEventListener('click', function(e) {
      e.preventDefault();
      var target = this.getAttribute('data-tab');
      tabs.forEach(function(t) { t.classList.remove('active'); });
      panels.forEach(function(p) { p.classList.remove('active'); });
      this.classList.add('active');
      var panel = document.getElementById(target);
      if (panel) panel.classList.add('active');
    });
  });
})();
</script>
