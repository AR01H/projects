<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

$active_tab    = sanitize_key( $_GET['tab'] ?? 'business' );
$saved         = isset( $_GET['saved'] ) ? (int) $_GET['saved'] : 0;
$import_result = isset( $_GET['imported'] ) ? sanitize_text_field( wp_unslash( $_GET['imported'] ) ) : '';

$s               = ch_get_settings();
$enquiry_types   = ch_get_enquiry_types();
$occasions       = ch_get_occasions();
$badges          = ch_get_hero_badges();
$sugarcane_stats = ch_get_sugarcane_stats();
$nutrition_facts = ch_get_nutrition_facts();
$events_why      = ch_get_events_why();
$about_mvv       = ch_get_about_mvv();
$about_quality   = ch_get_about_quality();
$events_gallery  = ch_get_events_gallery();
$franchise_gallery = ch_get_franchise_gallery();
$sugarcane_gallery = ch_get_sugarcane_gallery();

$tabs = [
	'business'  => '📋 Business Details',
	'contact'   => '📬 Contact Form',
	'booking'   => '📅 Booking Wizard',
	'badges'    => '🏷️ Hero Badges',
	'galleries' => '🖼️ Gallery Images',
	'sugarcane' => '📊 Why Sugarcane',
	'eventswhy' => '🎯 Events Why',
	'about'     => '🏢 About Page',
	'import'    => '📥 Import CSV',
];
?>
<div class="wrap ch-admin-wrap ch-cs-wrap">
	<h1>🎛️ Content Settings</h1>
	<p style="color:#666;margin-bottom:1.5rem;">Manage all previously hardcoded content from one place. Each tab saves independently.</p>

	<?php if ( $saved ) : ?>
		<div class="ch-notice ch-notice--success">✅ Settings saved successfully.</div>
	<?php endif; ?>
	<?php if ( $import_result ) : ?>
		<div class="ch-notice ch-notice--success">📥 <?php echo esc_html( $import_result ); ?></div>
	<?php endif; ?>

	<!-- ── Tab Nav ────────────────────────────────────────────────────────── -->
	<nav class="ch-cs-tabs">
		<?php foreach ( $tabs as $key => $label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ch-content-settings', 'tab' => $key ], admin_url( 'admin.php' ) ) ); ?>"
				class="ch-cs-tab <?php echo $active_tab === $key ? 'active' : ''; ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<!-- ══════════════════════════════════════════════════════════════════════
	     TAB 1 - Business Details
	     ══════════════════════════════════════════════════════════════════════ -->
	<?php if ( $active_tab === 'business' ) : ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ch_content_settings_business' ); ?>
		<input type="hidden" name="action" value="ch_content_settings_business">

		<div class="ch-card">
			<h2>🕒 Opening Hours &amp; Response Time</h2>
			<p class="ch-cs-desc">These appear on the Contact page and in form messages.</p>

			<div class="ch-row">
				<label>Opening Hours</label>
				<input type="text" name="business_hours"
					value="<?php echo esc_attr( $s['business_hours'] ?? 'Mon–Sat · 9am–9pm' ); ?>"
					placeholder="e.g. Mon–Sat · 9am–9pm">
				<span class="ch-cs-hint">Shown in contact info strip</span>
			</div>
			<div class="ch-row">
				<label>Response Time</label>
				<input type="text" name="response_time"
					value="<?php echo esc_attr( $s['response_time'] ?? 'within 24 hours' ); ?>"
					placeholder="e.g. within 24 hours">
				<span class="ch-cs-hint">Used in "we reply X" messages</span>
			</div>
			<div class="ch-row">
				<label>Coverage Area</label>
				<input type="text" name="address"
					value="<?php echo esc_attr( $s['address'] ?? 'Available across the UK' ); ?>"
					placeholder="e.g. Available across the UK">
			</div>
			<div class="ch-row">
				<label>Events &amp; Hire Info Text</label>
				<input type="text" name="events_info_text"
					value="<?php echo esc_attr( $s['events_info_text'] ?? 'Available across the UK for events, weddings & community gatherings' ); ?>"
					placeholder="Short line shown in contact section">
			</div>
			<div class="ch-row">
				<label>Franchise Info Text</label>
				<input type="text" name="franchise_info_text"
					value="<?php echo esc_attr( $s['franchise_info_text'] ?? 'Franchise enquiries warmly welcomed - reach out today' ); ?>"
					placeholder="Short line shown in contact section">
			</div>
		</div>

		<?php submit_button( '💾 Save Business Details', 'primary', 'submit', false ); ?>
	</form>

	<!-- ══════════════════════════════════════════════════════════════════════
	     TAB 2 - Contact Form Enquiry Types
	     ══════════════════════════════════════════════════════════════════════ -->
	<?php elseif ( $active_tab === 'contact' ) : ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ch_content_settings_contact' ); ?>
		<input type="hidden" name="action" value="ch_content_settings_contact">

		<div class="ch-card">
			<h2>📬 Contact Form - Enquiry Types</h2>
			<p class="ch-cs-desc">These are the options shown in the "I'm enquiring about" dropdown on the contact form. Each item needs a <strong>Value</strong> (no spaces, lowercase) and a <strong>Label</strong> (what the user sees).</p>

			<div class="ch-rep-header">
				<span>Value <small>(internal key)</small></span>
				<span>Label <small>(shown to visitor)</small></span>
				<span></span>
			</div>

			<div class="ch-repeater" id="ch-enquiry-repeater">
				<?php foreach ( $enquiry_types as $i => $et ) : ?>
				<div class="ch-rep-row">
					<input type="text" name="enquiry_types[<?php echo $i; ?>][value]"
						value="<?php echo esc_attr( $et['value'] ); ?>"
						placeholder="e.g. event" class="ch-rep-val">
					<input type="text" name="enquiry_types[<?php echo $i; ?>][label]"
						value="<?php echo esc_attr( $et['label'] ); ?>"
						placeholder="e.g. Event / Stall Hire" class="ch-rep-lbl">
					<button type="button" class="ch-rep-remove" title="Remove">✕</button>
				</div>
				<?php endforeach; ?>
			</div>

			<button type="button" class="ch-rep-add button" data-target="ch-enquiry-repeater" data-prefix="enquiry_types">
				+ Add Enquiry Type
			</button>

			<p class="ch-cs-hint" style="margin-top:1rem;">
				CSV format: <code>value,label</code> - one row per type.<br>
				<a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ch-content-settings', 'tab' => 'import' ], admin_url( 'admin.php' ) ) ); ?>">Go to CSV Import →</a>
			</p>
		</div>

		<?php submit_button( '💾 Save Enquiry Types', 'primary', 'submit', false ); ?>
	</form>

	<!-- ══════════════════════════════════════════════════════════════════════
	     TAB 3 - Booking Wizard Occasions
	     ══════════════════════════════════════════════════════════════════════ -->
	<?php elseif ( $active_tab === 'booking' ) : ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ch_content_settings_booking' ); ?>
		<input type="hidden" name="action" value="ch_content_settings_booking">

		<div class="ch-card">
			<h2>📅 Booking Wizard - Occasion Options</h2>
			<p class="ch-cs-desc">These are the occasion types shown in Step 3 of the booking wizard. Add or remove as needed - drag to reorder.</p>

			<div class="ch-rep-header ch-rep-header--single">
				<span>Occasion Name</span>
				<span></span>
			</div>

			<div class="ch-repeater ch-repeater--single" id="ch-occasion-repeater">
				<?php foreach ( $occasions as $i => $occ ) : ?>
				<div class="ch-rep-row ch-rep-row--single">
					<input type="text" name="occasions[<?php echo $i; ?>]"
						value="<?php echo esc_attr( $occ ); ?>"
						placeholder="e.g. Birthday Party">
					<button type="button" class="ch-rep-remove" title="Remove">✕</button>
				</div>
				<?php endforeach; ?>
			</div>

			<button type="button" class="ch-rep-add button" data-target="ch-occasion-repeater" data-prefix="occasions" data-single="1">
				+ Add Occasion
			</button>

			<p class="ch-cs-hint" style="margin-top:1rem;">
				CSV format: single column <code>occasion</code> - one row per occasion.<br>
				<a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ch-content-settings', 'tab' => 'import' ], admin_url( 'admin.php' ) ) ); ?>">Go to CSV Import →</a>
			</p>
		</div>

		<?php submit_button( '💾 Save Occasions', 'primary', 'submit', false ); ?>
	</form>

	<!-- ══════════════════════════════════════════════════════════════════════
	     TAB 4 - Hero Badges
	     ══════════════════════════════════════════════════════════════════════ -->
	<?php elseif ( $active_tab === 'badges' ) : ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ch_content_settings_badges' ); ?>
		<input type="hidden" name="action" value="ch_content_settings_badges">

		<div class="ch-card">
			<h2>🏷️ Hero Section - Badges</h2>
			<p class="ch-cs-desc">Short trust badges shown under the hero headline (e.g. "No Added Sugar"). Add as many as you like.</p>

			<div class="ch-rep-header ch-rep-header--single">
				<span>Badge Text</span>
				<span></span>
			</div>

			<div class="ch-repeater ch-repeater--single" id="ch-badge-repeater">
				<?php foreach ( $badges as $i => $badge ) : ?>
				<div class="ch-rep-row ch-rep-row--single">
					<input type="text" name="hero_badges[<?php echo $i; ?>]"
						value="<?php echo esc_attr( $badge ); ?>"
						placeholder="e.g. Pressed Live">
					<button type="button" class="ch-rep-remove" title="Remove">✕</button>
				</div>
				<?php endforeach; ?>
			</div>

			<button type="button" class="ch-rep-add button" data-target="ch-badge-repeater" data-prefix="hero_badges" data-single="1">
				+ Add Badge
			</button>

			<p class="ch-cs-hint" style="margin-top:1rem;">
				CSV format: single column <code>badge</code> - one row per badge.<br>
				<a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ch-content-settings', 'tab' => 'import' ], admin_url( 'admin.php' ) ) ); ?>">Go to CSV Import →</a>
			</p>
		</div>

		<?php submit_button( '💾 Save Hero Badges', 'primary', 'submit', false ); ?>
	</form>

	<!-- ══════════════════════════════════════════════════════════════════════
	     TAB 5 - Gallery Images
	     ══════════════════════════════════════════════════════════════════════ -->
	<?php elseif ( $active_tab === 'galleries' ) : ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ch_content_settings_galleries' ); ?>
		<input type="hidden" name="action" value="ch_content_settings_galleries">

		<?php
		$gallery_sections = [
			[ 'key' => 'events',    'label' => '🎪 Events Gallery',    'data' => $events_gallery ],
			[ 'key' => 'franchise', 'label' => '🤝 Franchise Gallery', 'data' => $franchise_gallery ],
			[ 'key' => 'sugarcane', 'label' => '🌿 Why Sugarcane Gallery', 'data' => $sugarcane_gallery ],
		];
		foreach ( $gallery_sections as $gs ) :
		?>
		<div class="ch-card">
			<h2><?php echo esc_html( $gs['label'] ); ?></h2>
			<p class="ch-cs-desc">Each row = one image card. <strong>Image URL</strong> is required; label and description are optional.</p>

			<div class="ch-rep-header" style="grid-template-columns:2fr 1fr 1fr 36px;">
				<span>Image URL</span><span>Label</span><span>Description</span><span></span>
			</div>
			<div class="ch-repeater ch-repeater--gallery" id="ch-<?php echo esc_attr( $gs['key'] ); ?>-repeater">
				<?php foreach ( $gs['data'] as $i => $img ) :
					$img = (array) $img;
				?>
				<div class="ch-rep-row ch-rep-row--gallery">
					<input type="text" name="gallery_<?php echo esc_attr( $gs['key'] ); ?>[<?php echo $i; ?>][src]"
						value="<?php echo esc_attr( $img['src'] ?? '' ); ?>" placeholder="https://...">
					<input type="text" name="gallery_<?php echo esc_attr( $gs['key'] ); ?>[<?php echo $i; ?>][label]"
						value="<?php echo esc_attr( $img['label'] ?? '' ); ?>" placeholder="e.g. Wedding Setup">
					<input type="text" name="gallery_<?php echo esc_attr( $gs['key'] ); ?>[<?php echo $i; ?>][desc]"
						value="<?php echo esc_attr( $img['desc'] ?? '' ); ?>" placeholder="Short caption">
					<button type="button" class="ch-rep-remove" title="Remove">✕</button>
				</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="ch-rep-add button"
				data-target="ch-<?php echo esc_attr( $gs['key'] ); ?>-repeater"
				data-prefix="gallery_<?php echo esc_attr( $gs['key'] ); ?>"
				data-columns="src,label,desc">
				+ Add Image
			</button>
		</div>
		<?php endforeach; ?>

		<?php submit_button( '💾 Save Gallery Images', 'primary', 'submit', false ); ?>
	</form>

	<!-- ══════════════════════════════════════════════════════════════════════
	     TAB 6 - Why Sugarcane (Stats + Nutrition)
	     ══════════════════════════════════════════════════════════════════════ -->
	<?php elseif ( $active_tab === 'sugarcane' ) : ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ch_content_settings_sugarcane' ); ?>
		<input type="hidden" name="action" value="ch_content_settings_sugarcane">

		<div class="ch-card">
			<h2>📊 Stats Bar (4 numbers shown at top of Why Sugarcane page)</h2>
			<div class="ch-rep-header" style="grid-template-columns:1fr 1fr 36px;">
				<span>Number / Value</span><span>Label</span><span></span>
			</div>
			<div class="ch-repeater" id="ch-stats-repeater">
				<?php foreach ( $sugarcane_stats as $i => $stat ) :
					$stat = (array) $stat;
				?>
				<div class="ch-rep-row">
					<input type="text" name="sugarcane_stats[<?php echo $i; ?>][num]"
						value="<?php echo esc_attr( $stat['num'] ?? '' ); ?>" placeholder="e.g. 2,000+">
					<input type="text" name="sugarcane_stats[<?php echo $i; ?>][label]"
						value="<?php echo esc_attr( $stat['label'] ?? '' ); ?>" placeholder="e.g. Years of Tradition">
					<button type="button" class="ch-rep-remove" title="Remove">✕</button>
				</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="ch-rep-add button" data-target="ch-stats-repeater" data-prefix="sugarcane_stats" data-columns="num,label">
				+ Add Stat
			</button>
		</div>

		<div class="ch-card">
			<h2>🧪 Nutrition Facts Table</h2>
			<p class="ch-cs-desc">Shown in the "What's Inside Every Sip" section.</p>
			<div class="ch-rep-header" style="grid-template-columns:1fr 1fr 1.5fr 36px;">
				<span>Nutrient Name</span><span>Value</span><span>Note</span><span></span>
			</div>
			<div class="ch-repeater ch-repeater--nutrition" id="ch-nutrition-repeater">
				<?php foreach ( $nutrition_facts as $i => $nf ) :
					$nf = (array) $nf;
				?>
				<div class="ch-rep-row ch-rep-row--nutrition">
					<input type="text" name="nutrition_facts[<?php echo $i; ?>][name]"
						value="<?php echo esc_attr( $nf['name'] ?? '' ); ?>" placeholder="e.g. 🍬 Natural Sugars">
					<input type="text" name="nutrition_facts[<?php echo $i; ?>][value]"
						value="<?php echo esc_attr( $nf['value'] ?? '' ); ?>" placeholder="e.g. ~13–15g">
					<input type="text" name="nutrition_facts[<?php echo $i; ?>][note]"
						value="<?php echo esc_attr( $nf['note'] ?? '' ); ?>" placeholder="Short note">
					<button type="button" class="ch-rep-remove" title="Remove">✕</button>
				</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="ch-rep-add button" data-target="ch-nutrition-repeater" data-prefix="nutrition_facts" data-columns="name,value,note">
				+ Add Row
			</button>
			<div class="ch-row" style="margin-top:1.2rem;">
				<label>Disclaimer Text</label>
				<input type="text" name="nutrition_disclaimer"
					value="<?php echo esc_attr( get_option( 'ch_nutrition_disclaimer', '* Values are approximate for 350ml fresh-pressed yellow cane, no additives.' ) ); ?>"
					placeholder="Footnote shown below the nutrition table">
			</div>
		</div>

		<?php submit_button( '💾 Save Why Sugarcane', 'primary', 'submit', false ); ?>
	</form>

	<!-- ══════════════════════════════════════════════════════════════════════
	     TAB 7 - Events "Why Choose Us"
	     ══════════════════════════════════════════════════════════════════════ -->
	<?php elseif ( $active_tab === 'eventswhy' ) : ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ch_content_settings_eventswhy' ); ?>
		<input type="hidden" name="action" value="ch_content_settings_eventswhy">

		<div class="ch-card">
			<h2>🖼️ Section Image</h2>
			<div class="ch-row">
				<label>Image URL</label>
				<input type="url" name="events_why_image"
					value="<?php echo esc_attr( $events_why['image'] ?? '' ); ?>"
					placeholder="https://...">
			</div>
		</div>

		<div class="ch-card">
			<h2>🎯 Why Choose Us Items</h2>
			<p class="ch-cs-desc">Each item shows an icon, bold title, and description text in the Events page "Why Choose Us" section.</p>
			<div class="ch-rep-header" style="grid-template-columns:60px 1fr 2fr 36px;">
				<span>Icon</span><span>Title</span><span>Description</span><span></span>
			</div>
			<div class="ch-repeater ch-repeater--eventswhy" id="ch-eventswhy-repeater">
				<?php foreach ( (array) ( $events_why['items'] ?? [] ) as $i => $item ) :
					$item = (array) $item;
				?>
				<div class="ch-rep-row ch-rep-row--eventswhy">
					<input type="text" name="events_why_items[<?php echo $i; ?>][icon]"
						value="<?php echo esc_attr( $item['icon'] ?? '' ); ?>" placeholder="🌿" style="text-align:center;font-size:1.3rem;">
					<input type="text" name="events_why_items[<?php echo $i; ?>][title]"
						value="<?php echo esc_attr( $item['title'] ?? '' ); ?>" placeholder="Item title">
					<input type="text" name="events_why_items[<?php echo $i; ?>][text]"
						value="<?php echo esc_attr( $item['text'] ?? '' ); ?>" placeholder="Description text">
					<button type="button" class="ch-rep-remove" title="Remove">✕</button>
				</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="ch-rep-add button" data-target="ch-eventswhy-repeater" data-prefix="events_why_items" data-columns="icon,title,text">
				+ Add Item
			</button>
		</div>

		<?php submit_button( '💾 Save Events Why', 'primary', 'submit', false ); ?>
	</form>

	<!-- ══════════════════════════════════════════════════════════════════════
	     TAB 8 - About Page
	     ══════════════════════════════════════════════════════════════════════ -->
	<?php elseif ( $active_tab === 'about' ) : ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ch_content_settings_about' ); ?>
		<input type="hidden" name="action" value="ch_content_settings_about">

		<div class="ch-card">
			<h2>🎯 Mission / Vision / Values Cards</h2>
			<p class="ch-cs-desc">Three cards shown in the "Our Foundation" carousel on the About page.</p>
			<div class="ch-rep-header" style="grid-template-columns:60px 1fr 2fr 36px;">
				<span>Icon</span><span>Title</span><span>Text</span><span></span>
			</div>
			<div class="ch-repeater ch-repeater--mvv" id="ch-mvv-repeater">
				<?php foreach ( $about_mvv as $i => $card ) :
					$card = (array) $card;
				?>
				<div class="ch-rep-row ch-rep-row--mvv">
					<input type="text" name="about_mvv[<?php echo $i; ?>][icon]"
						value="<?php echo esc_attr( $card['icon'] ?? '' ); ?>" placeholder="🎯" style="text-align:center;font-size:1.3rem;">
					<input type="text" name="about_mvv[<?php echo $i; ?>][title]"
						value="<?php echo esc_attr( $card['title'] ?? '' ); ?>" placeholder="e.g. Our Mission">
					<input type="text" name="about_mvv[<?php echo $i; ?>][text]"
						value="<?php echo esc_attr( $card['text'] ?? '' ); ?>" placeholder="Card description">
					<button type="button" class="ch-rep-remove" title="Remove">✕</button>
				</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="ch-rep-add button" data-target="ch-mvv-repeater" data-prefix="about_mvv" data-columns="icon,title,text">
				+ Add Card
			</button>
		</div>

		<div class="ch-card">
			<h2>✅ Quality Commitment List</h2>
			<p class="ch-cs-desc">Bullet-point list shown in the "Our Quality Promise" section on the About page.</p>
			<div class="ch-rep-header ch-rep-header--single">
				<span>Quality Point</span><span></span>
			</div>
			<div class="ch-repeater ch-repeater--single" id="ch-quality-repeater">
				<?php foreach ( $about_quality as $i => $item ) : ?>
				<div class="ch-rep-row ch-rep-row--single">
					<input type="text" name="about_quality[<?php echo $i; ?>]"
						value="<?php echo esc_attr( $item ); ?>" placeholder="e.g. Pressed fresh to order, never pre-made">
					<button type="button" class="ch-rep-remove" title="Remove">✕</button>
				</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="ch-rep-add button" data-target="ch-quality-repeater" data-prefix="about_quality" data-single="1">
				+ Add Point
			</button>
		</div>

		<?php submit_button( '💾 Save About Page', 'primary', 'submit', false ); ?>
	</form>

	<!-- ══════════════════════════════════════════════════════════════════════
	     TAB 9 - CSV Import
	     ══════════════════════════════════════════════════════════════════════ -->
	<?php elseif ( $active_tab === 'import' ) : ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
		<?php wp_nonce_field( 'ch_content_settings_import' ); ?>
		<input type="hidden" name="action" value="ch_content_settings_import">

		<div class="ch-card">
			<h2>📥 Import from CSV</h2>
			<p class="ch-cs-desc">Upload a CSV file to populate any of the repeatable sections. Choose what you're importing and whether to replace or append to existing data.</p>

			<div class="ch-row">
				<label>Import Type</label>
				<select name="import_type" id="ch-import-type" style="padding:.5rem .8rem;border:1px solid #ddd;border-radius:4px;max-width:280px;">
					<option value="enquiry_types">Contact Form - Enquiry Types</option>
					<option value="occasions">Booking Wizard - Occasions</option>
					<option value="hero_badges">Hero - Badges</option>
				</select>
			</div>

			<div class="ch-row">
				<label>CSV File</label>
				<input type="file" name="csv_file" accept=".csv,text/csv" required style="flex:1;">
			</div>

			<div class="ch-row">
				<label>Import Mode</label>
				<label style="display:flex;align-items:center;gap:.4rem;font-weight:normal;cursor:pointer;">
					<input type="radio" name="import_mode" value="replace" checked> Replace all existing data
				</label>
				<label style="display:flex;align-items:center;gap:.4rem;font-weight:normal;cursor:pointer;margin-left:1.5rem;">
					<input type="radio" name="import_mode" value="append"> Append to existing data
				</label>
			</div>
		</div>

		<div class="ch-card">
			<h2>📄 CSV Format Guide</h2>
			<div class="ch-cs-format-grid">

				<div class="ch-cs-format-box">
					<h4>Contact Form - Enquiry Types</h4>
					<p>Two columns: <code>value</code> and <code>label</code>. First row is header (skipped).</p>
					<pre>value,label
general,General Enquiry
event,Event / Stall Hire
wedding,Wedding or Asian Celebration
franchise,Franchise Opportunity
other,Something Else</pre>
				</div>

				<div class="ch-cs-format-box">
					<h4>Booking Wizard - Occasions</h4>
					<p>One column: <code>occasion</code>. First row is header (skipped).</p>
					<pre>occasion
Wedding / Walima
Mehndi / Sangeet
Eid Celebration
Birthday Party
Corporate Event</pre>
				</div>

				<div class="ch-cs-format-box">
					<h4>Hero - Badges</h4>
					<p>One column: <code>badge</code>. First row is header (skipped).</p>
					<pre>badge
No Added Sugar
No Preservatives
Pressed Live
Served Chilled</pre>
				</div>

			</div>
		</div>

		<?php submit_button( '📥 Import CSV', 'primary', 'submit', false ); ?>
	</form>
	<?php endif; ?>
</div>

<style>
/* ── Tab nav ────────────────────────────────────────────────────────────────── */
.ch-cs-wrap { max-width:960px; }
.ch-cs-tabs { display:flex; gap:.3rem; margin-bottom:1.5rem; border-bottom:2px solid #e0e0e0; padding-bottom:0; flex-wrap:wrap; }
.ch-cs-tab { display:inline-block; padding:.55rem 1.1rem; border-radius:6px 6px 0 0; text-decoration:none; color:#555; font-size:.85rem; font-weight:600; border:1px solid transparent; border-bottom:none; margin-bottom:-2px; transition:all .15s; }
.ch-cs-tab:hover { background:#f0f0f0; color:#222; }
.ch-cs-tab.active { background:#fff; border-color:#e0e0e0; color:#2d5a1b; border-bottom-color:#fff; }

/* ── Cards & rows ───────────────────────────────────────────────────────────── */
.ch-cs-desc { color:#666; margin-bottom:1.2rem; font-size:.88rem; }
.ch-cs-hint { color:#888; font-size:.78rem; display:block; }
.ch-row { display:flex; gap:.8rem; align-items:center; margin-bottom:.9rem; flex-wrap:wrap; }
.ch-row label { min-width:160px; font-weight:600; font-size:.85rem; }
.ch-row input[type="text"], .ch-row input[type="tel"], .ch-row input[type="email"] { flex:1; padding:.45rem .6rem; border:1px solid #ddd; border-radius:4px; min-width:180px; }

/* ── Repeater ───────────────────────────────────────────────────────────────── */
.ch-rep-header { display:grid; grid-template-columns:1fr 1fr 36px; gap:.5rem; padding:.3rem .4rem; font-size:.75rem; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:.04em; margin-bottom:.3rem; }
.ch-rep-header--single { grid-template-columns:1fr 36px; }
.ch-repeater { display:flex; flex-direction:column; gap:.4rem; margin-bottom:.8rem; }
.ch-rep-row { display:grid; grid-template-columns:1fr 1fr 36px; gap:.5rem; align-items:center; background:#f9f9f9; border:1px solid #e8e8e8; border-radius:6px; padding:.5rem .6rem; }
.ch-rep-row--single { grid-template-columns:1fr 36px; }
.ch-rep-row input { padding:.4rem .6rem; border:1px solid #ddd; border-radius:4px; width:100%; box-sizing:border-box; font-size:.88rem; }
.ch-rep-remove { width:32px; height:32px; background:#fff; border:1px solid #ddd; border-radius:4px; cursor:pointer; color:#c00; font-size:.85rem; line-height:1; display:flex; align-items:center; justify-content:center; transition:all .15s; padding:0; }
.ch-rep-remove:hover { background:#c00; color:#fff; border-color:#c00; }
.ch-rep-add { margin-top:.2rem; }

/* ── Multi-column repeater rows ─────────────────────────────────────────────── */
.ch-rep-row--gallery    { grid-template-columns: 2fr 1fr 1fr 36px; }
.ch-rep-row--nutrition  { grid-template-columns: 1fr 1fr 1.5fr 36px; }
.ch-rep-row--eventswhy  { grid-template-columns: 60px 1fr 2fr 36px; }
.ch-rep-row--mvv        { grid-template-columns: 60px 1fr 2fr 36px; }

/* ── CSV format boxes ───────────────────────────────────────────────────────── */
.ch-cs-format-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:1rem; }
.ch-cs-format-box { background:#f8f8f8; border:1px solid #e0e0e0; border-radius:6px; padding:1rem; }
.ch-cs-format-box h4 { margin:0 0 .4rem; font-size:.9rem; color:#2d5a1b; }
.ch-cs-format-box p { font-size:.8rem; color:#666; margin:0 0 .5rem; }
.ch-cs-format-box pre { background:#1e1e1e; color:#c8e830; padding:.7rem; border-radius:4px; font-size:.72rem; overflow-x:auto; margin:0; line-height:1.6; }
</style>

<script>
(function(){
	/* ── Remove row ─────────────────────────────────────────────────────── */
	document.addEventListener('click', function(e){
		if (!e.target.classList.contains('ch-rep-remove')) return;
		var row = e.target.closest('.ch-rep-row');
		if (!row) return;
		row.remove();
		reindex(e.target.closest('.ch-repeater'));
	});

	/* ── Add row ────────────────────────────────────────────────────────── */
	document.querySelectorAll('.ch-rep-add').forEach(function(btn){
		btn.addEventListener('click', function(){
			var repId   = btn.dataset.target;
			var prefix  = btn.dataset.prefix;
			var isSingle= btn.dataset.single === '1';
			var columns = btn.dataset.columns ? btn.dataset.columns.split(',') : [];
			var rep     = document.getElementById(repId);
			if (!rep) return;
			var idx = rep.querySelectorAll('.ch-rep-row').length;
			var row = document.createElement('div');
			var placeholders = { src:'https://...', label:'Label', desc:'Caption', num:'e.g. 100+', name:'Nutrient', value:'~10g', note:'Short note', icon:'🌿', title:'Title', text:'Description', quality:'Point' };

			if (isSingle) {
				row.className = 'ch-rep-row ch-rep-row--single';
				row.innerHTML = '<input type="text" name="' + prefix + '[' + idx + ']" placeholder="Type here..." />'
					+ '<button type="button" class="ch-rep-remove" title="Remove">✕</button>';
			} else if (columns.length >= 2) {
				row.className = 'ch-rep-row';
				var inputs = columns.map(function(col){
					var ph = placeholders[col] || col;
					var st = col === 'icon' ? ' style="text-align:center;font-size:1.3rem;"' : '';
					return '<input type="text" name="' + prefix + '[' + idx + '][' + col + ']" placeholder="' + ph + '"' + st + '>';
				}).join('');
				row.innerHTML = inputs + '<button type="button" class="ch-rep-remove" title="Remove">✕</button>';
			} else {
				row.className = 'ch-rep-row';
				row.innerHTML =
					'<input type="text" name="' + prefix + '[' + idx + '][value]" placeholder="key" />' +
					'<input type="text" name="' + prefix + '[' + idx + '][label]" placeholder="Label" />' +
					'<button type="button" class="ch-rep-remove" title="Remove">✕</button>';
			}
			rep.appendChild(row);
			row.querySelector('input').focus();
		});
	});

	/* ── Reindex names after remove ─────────────────────────────────────── */
	function reindex(rep){
		if (!rep) return;
		rep.querySelectorAll('.ch-rep-row').forEach(function(row, idx){
			row.querySelectorAll('input').forEach(function(inp){
				inp.name = inp.name.replace(/\[\d+\]/, '[' + idx + ']');
			});
		});
	}
})();
</script>
