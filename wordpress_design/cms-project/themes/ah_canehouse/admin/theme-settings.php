<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
$s      = ch_get_settings();
$schema = ch_get_schema_settings();
?>
<div class="wrap ch-admin-wrap">
	<h1>🔧 Site Settings</h1>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="ch-notice ch-notice--success">✅ Settings saved successfully.</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ch_theme_settings' ); ?>
		<input type="hidden" name="action" value="ch_theme_settings">

		<!-- ── Contact & Business ──────────────────────────────────────────── -->
		<div class="ch-card">
			<h2>📞 Contact &amp; Business Info</h2>
			<?php
			$fields = [
				'phone'    => [ 'Phone Number',                      'tel',   CONTACT_NUMBER ],
				'email'    => [ 'Email Address',                     'email', CONTACT_EMAIL ],
				'address'  => [ 'Address / Coverage Area',           'text',  'Available across the UK' ],
				'website'  => [ 'Website URL',                       'text',  'www.thecanehouse.co.uk' ],
				'whatsapp' => [ 'WhatsApp Number (digits + country)', 'text',  WHATASPP_CONTACT_NUMBER ],
				'tagline'  => [ 'Business Tagline',                  'text',  'Pressed Fresh. Served Cool.' ],
			];
			foreach ( $fields as $key => [ $label, $type, $placeholder ] ) : ?>
				<div class="ch-row">
					<label for="ch-s-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
					<input type="<?php echo esc_attr( $type ); ?>"
						id="ch-s-<?php echo esc_attr( $key ); ?>"
						name="<?php echo esc_attr( $key ); ?>"
						value="<?php echo esc_attr( $s[ $key ] ?? '' ); ?>"
						placeholder="<?php echo esc_attr( $placeholder ); ?>">
				</div>
			<?php endforeach; ?>
		</div>

		<!-- ── Social Media ────────────────────────────────────────────────── -->
		<div class="ch-card">
			<h2>📲 Social Media Links</h2>
			<?php foreach ( [
				'instagram_url' => 'Instagram URL',
				'facebook_url'  => 'Facebook URL',
				'tiktok_url'    => 'TikTok URL',
				'youtube_url'   => 'YouTube URL',
			] as $key => $label ) : ?>
				<div class="ch-row">
					<label for="ch-s-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
					<input type="url"
						id="ch-s-<?php echo esc_attr( $key ); ?>"
						name="<?php echo esc_attr( $key ); ?>"
						value="<?php echo esc_attr( $s[ $key ] ?? '' ); ?>"
						placeholder="https://...">
				</div>
			<?php endforeach; ?>
		</div>

		<!-- ── Pricing Control ─────────────────────────────────────────────── -->
		<div class="ch-card">
			<h2>💷 Pricing Visibility</h2>
			<p style="color:#666;margin-bottom:1rem;">Control whether prices appear anywhere on the website. Turn off to keep pricing private - useful when quoting per-event or for franchise partners.</p>
			<div class="ch-row">
				<label>Show Prices on Website</label>
				<label class="ch-toggle">
					<input type="checkbox" name="show_prices" value="1" <?php checked( $s['show_prices'] ?? '', '1' ); ?>>
					<span class="ch-toggle-slider"></span>
					<span class="ch-toggle-label">Prices <?php echo ( ( $s['show_prices'] ?? '' ) === '1' ) ? '<strong style="color:green">Visible</strong>' : '<strong style="color:#c00">Hidden</strong>'; ?></span>
				</label>
			</div>
		</div>

		<!-- ── Certifications ──────────────────────────────────────────────── -->
		<div class="ch-card">
			<h2>🏛️ Certifications Section</h2>
			<p style="color:#666;margin-bottom:1rem;">Controls the "Government Certified" section shown on the homepage. Upload your certificate image and customise the badges.</p>

			<div class="ch-row">
				<label for="cert_heading">Section Heading</label>
				<input type="text" id="cert_heading" name="cert_heading"
					value="<?php echo esc_attr( $s['cert_heading'] ?? 'Government Certified &amp; Fully Compliant' ); ?>"
					placeholder="Government Certified &amp; Fully Compliant">
			</div>
			<div class="ch-row">
				<label for="cert_subtext">Section Sub-text</label>
				<textarea id="cert_subtext" name="cert_subtext" rows="3" style="width:100%;padding:.5rem;border:1px solid #ddd;border-radius:4px;"><?php echo esc_textarea( $s['cert_subtext'] ?? '' ); ?></textarea>
			</div>
			<div class="ch-row">
				<label for="cert_image_url">Certificate Image URL</label>
				<input type="url" id="cert_image_url" name="cert_image_url"
					value="<?php echo esc_attr( $s['cert_image_url'] ?? '' ); ?>"
					placeholder="https://... or use Media Library URL">
				<p style="font-size:.8rem;color:#888;margin-top:4px;">Upload your Food Hygiene Certificate image to the Media Library and paste the URL here.</p>
			</div>

			<h3 style="margin-top:1.5rem;font-size:1rem;">Certification Badges</h3>
			<p style="color:#666;font-size:.85rem;margin-bottom:1rem;">Each badge appears as a card in the section. Leave title blank to hide a badge.</p>

			<?php
			$cert_defaults = ch_get_certifications();
			for ( $i = 0; $i < 6; $i++ ) :
				$cert = $cert_defaults[ $i ] ?? [];
			?>
				<div style="background:#f8f8f8;border:1px solid #e0e0e0;border-radius:8px;padding:1rem;margin-bottom:1rem;">
					<div style="display:grid;grid-template-columns:60px 1fr 1fr;gap:.8rem;align-items:start;">
						<div>
							<label style="font-size:.75rem;color:#888;">Icon</label>
							<input type="text" name="cert[<?php echo $i; ?>][icon]"
								value="<?php echo esc_attr( $cert['icon'] ?? '' ); ?>"
								style="width:100%;padding:.4rem;text-align:center;font-size:1.4rem;border:1px solid #ddd;border-radius:4px;">
						</div>
						<div>
							<label style="font-size:.75rem;color:#888;">Title</label>
							<input type="text" name="cert[<?php echo $i; ?>][title]"
								value="<?php echo esc_attr( $cert['title'] ?? '' ); ?>"
								style="width:100%;padding:.4rem;border:1px solid #ddd;border-radius:4px;"
								placeholder="e.g. Food Hygiene Rating 5">
						</div>
						<div>
							<label style="font-size:.75rem;color:#888;">Badge Label</label>
							<input type="text" name="cert[<?php echo $i; ?>][badge]"
								value="<?php echo esc_attr( $cert['badge'] ?? '' ); ?>"
								style="width:100%;padding:.4rem;border:1px solid #ddd;border-radius:4px;"
								placeholder="e.g. Grade 5">
						</div>
					</div>
					<div style="margin-top:.6rem;">
						<label style="font-size:.75rem;color:#888;">Description</label>
						<input type="text" name="cert[<?php echo $i; ?>][desc]"
							value="<?php echo esc_attr( $cert['desc'] ?? '' ); ?>"
							style="width:100%;padding:.4rem;border:1px solid #ddd;border-radius:4px;"
							placeholder="Short description shown on the card">
					</div>
				</div>
			<?php endfor; ?>
		</div>

		<!-- ── Schema / SEO ────────────────────────────────────────────────── -->
		<div class="ch-card">
			<h2>🔍 Schema &amp; Structured Data (SEO)</h2>
			<p style="color:#666;margin-bottom:1rem;">Controls the JSON-LD structured data injected into the page <code>&lt;head&gt;</code>. Helps Google display rich results for your business.</p>

			<div class="ch-row">
				<label>Enable Schema Output</label>
				<label class="ch-toggle">
					<input type="checkbox" name="schema[enabled]" value="1" <?php checked( $schema['enabled'] ?? '1', '1' ); ?>>
					<span class="ch-toggle-slider"></span>
					<span class="ch-toggle-label">Schema JSON-LD <?php echo ( ( $schema['enabled'] ?? '1' ) === '1' ) ? '<strong style="color:green">Active</strong>' : '<strong style="color:#c00">Off</strong>'; ?></span>
				</label>
			</div>

			<?php
			$schema_fields = [
				'schema[name]'        => [ 'Business Name',    'text',  'The Cane House' ],
				'schema[description]' => [ 'Business Description', 'text', 'Fresh sugarcane juice pressed live, served cool.' ],
				'schema[phone]'       => [ 'Phone (schema)',   'tel',   CONTACT_NUMBER ],
				'schema[email]'       => [ 'Email (schema)',   'email', CONTACT_EMAIL ],
				'schema[area_served]' => [ 'Area Served',      'text',  'United Kingdom' ],
				'schema[price_range]' => [ 'Price Range (£ to £££)', 'text', '£' ],
			];
			foreach ( $schema_fields as $name => [ $label, $type, $placeholder ] ) :
				// Parse key for current value e.g. schema[name] → $schema['name']
				preg_match( '/\[(\w+)\]/', $name, $m );
				$val = $schema[ $m[1] ] ?? '';
			?>
				<div class="ch-row">
					<label><?php echo esc_html( $label ); ?></label>
					<input type="<?php echo esc_attr( $type ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
						value="<?php echo esc_attr( $val ); ?>"
						placeholder="<?php echo esc_attr( $placeholder ); ?>">
				</div>
			<?php endforeach; ?>

			<div class="ch-row">
				<label>Include Pricing in Schema</label>
				<label class="ch-toggle">
					<input type="checkbox" name="schema[include_price]" value="1" <?php checked( $schema['include_price'] ?? '0', '1' ); ?>>
					<span class="ch-toggle-slider"></span>
					<span class="ch-toggle-label">Prices in schema <?php echo ( ( $schema['include_price'] ?? '0' ) === '1' ) ? '<strong style="color:green">Included</strong>' : '<strong style="color:#c00">Excluded</strong>'; ?></span>
				</label>
			</div>

			<div class="ch-row">
				<label>Include Review Schema</label>
				<label class="ch-toggle">
					<input type="checkbox" name="schema[include_reviews]" value="1" <?php checked( $schema['include_reviews'] ?? '1', '1' ); ?>>
					<span class="ch-toggle-slider"></span>
					<span class="ch-toggle-label">Review schema <?php echo ( ( $schema['include_reviews'] ?? '1' ) === '1' ) ? '<strong style="color:green">Active</strong>' : '<strong style="color:#c00">Off</strong>'; ?></span>
				</label>
			</div>

			<div class="ch-row">
				<label>Schema Type</label>
				<select name="schema[type]" style="padding:.5rem .8rem;border:1px solid #ddd;border-radius:4px;">
					<?php foreach ( [ 'FoodEstablishment', 'FoodService', 'LocalBusiness', 'CafeOrCoffeeShop' ] as $type ) : ?>
						<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $schema['type'] ?? 'FoodEstablishment', $type ); ?>><?php echo esc_html( $type ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<details style="margin-top:1rem;">
				<summary style="cursor:pointer;font-weight:600;color:#2d5a1b;">👁 Preview current schema output</summary>
				<pre style="background:#1e1e1e;color:#c8e830;padding:1rem;border-radius:8px;overflow:auto;font-size:.75rem;margin-top:.8rem;"><?php
					echo esc_html( json_encode( ch_build_schema_json( false ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
				?></pre>
			</details>
		</div>

		<?php submit_button( '💾 Save All Settings', 'primary', 'submit', false ); ?>
	</form>
</div>

<style>
.ch-toggle { display:inline-flex; align-items:center; gap:.6rem; cursor:pointer; }
.ch-toggle input { position:absolute; opacity:0; width:0; height:0; }
.ch-toggle-slider { position:relative; display:inline-block; width:44px; height:24px; background:#ccc; border-radius:24px; transition:.3s; flex-shrink:0; }
.ch-toggle-slider::before { content:''; position:absolute; width:18px; height:18px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.3s; }
.ch-toggle input:checked + .ch-toggle-slider { background:#4a8c2a; }
.ch-toggle input:checked + .ch-toggle-slider::before { transform:translateX(20px); }
.ch-toggle-label { font-size:.9rem; color:#444; }
</style>
