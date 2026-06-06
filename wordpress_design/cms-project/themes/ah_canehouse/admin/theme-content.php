<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

$home        = ch_get_home_settings();
$faqs        = ch_get_faqs( '', 20 );

$pkgs        = ch_get_hire_packages();
$locs        = ch_get_franchise_locations();
$marquee     = ch_get_marquee_items();
$badges      = ch_get_hero_badges();

$s           = ch_get_settings();
?>
<div class="wrap ch-admin-wrap">
	<h1>📝 Content &amp; Menu</h1>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="ch-notice ch-notice--success">✅ Content saved successfully.</div>
	<?php endif; ?>

	<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
		<?php wp_nonce_field( 'ch_theme_content' ); ?>
		<input type="hidden" name="action" value="ch_theme_content">

		<!-- HERO -->
		<div class="ch-card">
			<h2>🌿 Hero Section</h2>
			<?php foreach ( [
				'hero_tag'       => [ 'Top Tag Line', 'text', '100% Natural · No Additives · Pressed Live' ],
				'hero_headline'  => [ 'Main Headline (HTML allowed)', 'text', 'Freshly Pressed.<span class="accent"> Naturally Refreshing.</span>' ],
				'hero_brand'     => [ 'Brand Sub-title', 'text', 'The Cane House' ],
				'hero_desc'      => [ 'Description', 'text', 'Fresh sugarcane juice pressed live...' ],
				'hero_cta_label' => [ 'CTA 1 Label', 'text', '🥤 Build Your Juice' ],
				'hero_cta_url'   => [ 'CTA 1 URL', 'text', '#build' ],
				'hero_cta2_label'=> [ 'CTA 2 Label', 'text', 'Hire for Events →' ],
				'hero_cta2_url'  => [ 'CTA 2 URL', 'text', '#hire' ],
			] as $key => [ $label, $type, $placeholder ] ) : ?>
				<div class="ch-row">
					<label><?php echo esc_html( $label ); ?></label>
					<input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $key ); ?>"
						value="<?php echo esc_attr( $home[ $key ] ?? '' ); ?>"
						placeholder="<?php echo esc_attr( $placeholder ); ?>">
				</div>
			<?php endforeach; ?>
		</div>

		<!-- HERO BADGES -->
		<div class="ch-card">
			<h2>🏷️ Hero Badges</h2>
			<p style="font-size:.85rem;color:#666;margin-bottom:.5rem;">One badge per line. Shown under the hero headline.</p>
			<textarea name="hero_badges" rows="4" style="width:100%;"><?php echo esc_textarea( implode( "\n", $badges ) ); ?></textarea>
		</div>

		<!-- MARQUEE -->
		<div class="ch-card">
			<h2>🎞️ Marquee Items</h2>
			<p style="font-size:.85rem;color:#666;margin-bottom:.5rem;">One item per line.</p>
			<textarea name="marquee_items" rows="8" style="width:100%;"><?php echo esc_textarea( implode( "\n", $marquee ) ); ?></textarea>
		</div>

		<!-- HIRE PACKAGES -->
		<div class="ch-card">
			<h2>🎪 Event Hire Packages</h2>
			<?php foreach ( $pkgs as $idx => $pkg ) :
				$pkg = (array) $pkg;
				$pkg_items = implode( "\n", (array) ( $pkg['items'] ?? [] ) );
			?>
				<div style="background:#f9f9f9;border-radius:6px;padding:1rem;margin-bottom:.8rem;">
					<div class="ch-row">
						<label>Icon</label>
						<input type="text" name="hire_packages[<?php echo $idx; ?>][icon]" value="<?php echo esc_attr( $pkg['icon'] ?? '' ); ?>" style="width:60px;">
					</div>
					<div class="ch-row">
						<label>Title</label>
						<input type="text" name="hire_packages[<?php echo $idx; ?>][title]" value="<?php echo esc_attr( $pkg['title'] ?? '' ); ?>">
					</div>
					<div class="ch-row">
						<label>Description</label>
						<textarea name="hire_packages[<?php echo $idx; ?>][desc]" rows="2" style="width:100%;flex:1;"><?php echo esc_textarea( $pkg['desc'] ?? '' ); ?></textarea>
					</div>
					<div class="ch-row">
						<label>List Items<br><small>(one per line)</small></label>
						<textarea name="hire_packages[<?php echo $idx; ?>][items]" rows="4" style="width:100%;flex:1;"><?php echo esc_textarea( $pkg_items ); ?></textarea>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- FRANCHISE LOCATIONS -->
		<div class="ch-card">
			<h2>📍 Franchise Locations</h2>
			<p style="font-size:.85rem;color:#666;margin-bottom:.5rem;">Locations shown in the scrolling franchise marquee.</p>
			<div id="ch-loc-wrap">
				<?php foreach ( $locs as $idx => $loc ) :
					$loc = (array) $loc;
				?>
					<div style="display:flex;gap:.5rem;margin-bottom:.4rem;align-items:center;">
						<input type="text" name="franchise_locations[<?php echo $idx; ?>][icon]" value="<?php echo esc_attr( $loc['icon'] ?? '📍' ); ?>" style="width:50px;">
						<input type="text" name="franchise_locations[<?php echo $idx; ?>][name]" value="<?php echo esc_attr( $loc['name'] ?? '' ); ?>" placeholder="City - Area" style="flex:1;">
						<button type="button" onclick="this.closest('div').remove()" class="button" style="color:red;">✕</button>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" id="ch-add-loc" class="button" style="margin-top:.5rem;">+ Add Location</button>
		</div>

		<!-- HOMEPAGE DISPLAY LIMITS -->
		<?php
		$home_limits = $s['home_limits'] ?? [];
		if ( is_string( $home_limits ) ) $home_limits = json_decode( $home_limits, true ) ?: [];
		$hl_on = function ( $key ) use ( $home_limits ) {
			return isset( $home_limits[ $key . '_limit' ] ) ? (bool) $home_limits[ $key . '_limit' ] : true;
		};
		$hl_count = function ( $key, $def ) use ( $home_limits ) {
			return isset( $home_limits[ $key . '_count' ] ) ? (int) $home_limits[ $key . '_count' ] : $def;
		};
		?>
		<div class="ch-card">
			<h2>🏠 Homepage Display Limits</h2>
			<p style="font-size:.83rem;color:#666;margin-bottom:1rem;">
				Control how many items each section shows on the homepage. When <strong>"Limit on homepage"</strong> is
				ticked, only the chosen number is shown with a <em>"View all"</em> button to the full page.
				Untick to show <strong>all</strong> items on the homepage (no button).
			</p>
			<input type="hidden" name="home_limits_present" value="1">

			<?php
			$limit_rows = [
				'faqs'        => [ 'label' => 'FAQs',                  'def' => 6, 'page' => '/faqs/' ],
			];
			foreach ( $limit_rows as $key => $row ) : ?>
				<div class="ch-row" style="align-items:center;">
					<label style="min-width:200px;"><?php echo esc_html( $row['label'] ); ?></label>
					<label style="font-size:.85rem;display:flex;align-items:center;gap:.4rem;min-width:auto;flex:0 0 auto;">
						<input type="checkbox" name="home_limits[<?php echo $key; ?>_limit]" value="1" <?php checked( $hl_on( $key ) ); ?>>
						Limit on homepage
					</label>
					<span style="font-size:.85rem;color:#666;">Show first</span>
					<input type="number" min="1" max="50" style="width:70px;flex:0 0 auto;min-width:0;"
						name="home_limits[<?php echo $key; ?>_count]"
						value="<?php echo esc_attr( $hl_count( $key, $row['def'] ) ); ?>">
					<span style="font-size:.78rem;color:#999;">(rest on <code><?php echo esc_html( $row['page'] ); ?></code>)</span>
				</div>
			<?php endforeach; ?>
		</div>

		<?php submit_button( 'Save All Content', 'primary', 'submit', false ); ?>
	</form>
</div>

<script>
let chLocIdx = <?php echo count( $locs ); ?>;
document.getElementById('ch-add-loc').addEventListener('click', function() {
	const wrap = document.getElementById('ch-loc-wrap');
	const row  = document.createElement('div');
	row.style.cssText = 'display:flex;gap:.5rem;margin-bottom:.4rem;align-items:center;';
	row.innerHTML = `
		<input type="text" name="franchise_locations[${chLocIdx}][icon]" value="📍" style="width:50px;">
		<input type="text" name="franchise_locations[${chLocIdx}][name]" placeholder="City - Area" style="flex:1;">
		<button type="button" onclick="this.closest('div').remove()" class="button" style="color:red;">✕</button>`;
	wrap.appendChild(row);
	chLocIdx++;
});


</script>
