<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

$home    = ch_get_home_settings();
$steps   = ch_get_order_steps();
$sizes   = ch_get_menu_sizes();
$faqs    = ch_get_faqs( '', 20 );
$benefits= ch_get_benefits();
$pkgs    = ch_get_hire_packages();
$locs    = ch_get_franchise_locations();
$marquee = ch_get_marquee_items();
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
				'hero_headline'  => [ 'Main Headline (HTML allowed)', 'text', 'Pressed Fresh.<span class="accent">Served Cool.</span>' ],
				'hero_brand'     => [ 'Brand Sub-title', 'text', 'The Cane House' ],
				'hero_desc'      => [ 'Description', 'text', 'Fresh sugarcane juice pressed live...' ],
				'hero_cta_label' => [ 'CTA 1 Label', 'text', '🥤 Build Your Juice' ],
				'hero_cta_url'   => [ 'CTA 1 URL', 'text', '#build' ],
				'hero_cta2_label'=> [ 'CTA 2 Label', 'text', 'Hire for Events →' ],
				'hero_cta2_url'  => [ 'CTA 2 URL', 'text', '#hire' ],
				'hero_badge_1'   => [ 'Badge 1', 'text', 'No Added Sugar' ],
				'hero_badge_2'   => [ 'Badge 2', 'text', 'No Preservatives' ],
				'hero_badge_3'   => [ 'Badge 3', 'text', 'Pressed Live' ],
				'hero_badge_4'   => [ 'Badge 4', 'text', 'Served Chilled' ],
			] as $key => [ $label, $type, $placeholder ] ) : ?>
				<div class="ch-row">
					<label><?php echo esc_html( $label ); ?></label>
					<input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $key ); ?>"
						value="<?php echo esc_attr( $home[ $key ] ?? '' ); ?>"
						placeholder="<?php echo esc_attr( $placeholder ); ?>">
				</div>
			<?php endforeach; ?>
		</div>

		<!-- MARQUEE -->
		<div class="ch-card">
			<h2>🎞️ Marquee Items</h2>
			<p style="font-size:.85rem;color:#666;margin-bottom:.5rem;">One item per line.</p>
			<textarea name="marquee_items" rows="8" style="width:100%;"><?php echo esc_textarea( implode( "\n", $marquee ) ); ?></textarea>
		</div>

		<!-- ORDER STEPS -->
		<div class="ch-card">
			<h2>📋 How to Order Steps</h2>
			<?php foreach ( $steps as $idx => $step ) :
				$step = (array) $step;
			?>
				<div style="background:#f9f9f9;border-radius:6px;padding:1rem;margin-bottom:.8rem;">
					<div class="ch-row">
						<label>Step Number</label>
						<input type="text" name="order_steps[<?php echo $idx; ?>][num]" value="<?php echo esc_attr( $step['num'] ?? '' ); ?>" style="width:60px;">
					</div>
					<div class="ch-row">
						<label>Emoji</label>
						<input type="text" name="order_steps[<?php echo $idx; ?>][emoji]" value="<?php echo esc_attr( $step['emoji'] ?? '' ); ?>" style="width:60px;">
					</div>
					<div class="ch-row">
						<label>Title</label>
						<input type="text" name="order_steps[<?php echo $idx; ?>][title]" value="<?php echo esc_attr( $step['title'] ?? '' ); ?>">
					</div>
					<div class="ch-row">
						<label>Description</label>
						<textarea name="order_steps[<?php echo $idx; ?>][desc]" rows="2" style="width:100%;flex:1;"><?php echo esc_textarea( $step['desc'] ?? '' ); ?></textarea>
					</div>
					<label style="font-size:.8rem;">
						<input type="checkbox" name="order_steps[<?php echo $idx; ?>][highlight]" value="1" <?php checked( ! empty( $step['highlight'] ) ); ?>>
						Highlight step (lime border)
					</label>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- MENU SIZES -->
		<div class="ch-card">
			<h2>🧃 Menu Sizes &amp; Pricing</h2>
			<?php foreach ( $sizes as $idx => $sz ) :
				$sz = (array) $sz;
			?>
				<div style="background:#f9f9f9;border-radius:6px;padding:.8rem;margin-bottom:.6rem;display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
					<input type="text" name="menu_sizes[<?php echo $idx; ?>][icon]"  value="<?php echo esc_attr( $sz['icon']  ?? '' ); ?>" placeholder="🥤" style="width:50px;">
					<input type="text" name="menu_sizes[<?php echo $idx; ?>][name]"  value="<?php echo esc_attr( $sz['name']  ?? '' ); ?>" placeholder="Name" style="width:160px;">
					<input type="text" name="menu_sizes[<?php echo $idx; ?>][desc]"  value="<?php echo esc_attr( $sz['desc']  ?? '' ); ?>" placeholder="Description" style="flex:1;min-width:180px;">
					<input type="text" name="menu_sizes[<?php echo $idx; ?>][price]" value="<?php echo esc_attr( $sz['price'] ?? '' ); ?>" placeholder="£5.50" style="width:70px;">
					<input type="text" name="menu_sizes[<?php echo $idx; ?>][badge]" value="<?php echo esc_attr( $sz['badge'] ?? '' ); ?>" placeholder="Badge" style="width:80px;">
					<label style="font-size:.8rem;"><input type="checkbox" name="menu_sizes[<?php echo $idx; ?>][featured]" value="1" <?php checked( ! empty( $sz['featured'] ) ); ?>> Featured</label>
				</div>
			<?php endforeach; ?>
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
