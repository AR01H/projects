<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

$home        = ch_get_home_settings();
$steps       = ch_get_order_steps();
$sizes       = ch_get_menu_sizes();
$faqs        = ch_get_faqs( '', 20 );
$benefits    = ch_get_benefits();
$pkgs        = ch_get_hire_packages();
$locs        = ch_get_franchise_locations();
$marquee     = ch_get_marquee_items();
$story_cards = ch_get_story_cards();
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
			<h2>🧃 Menu Sizes</h2>
			<p style="font-size:.85rem;color:#666;margin-bottom:.8rem;">Edit size names and descriptions. Pricing is managed separately in Site Settings.</p>
			<?php foreach ( $sizes as $idx => $sz ) :
				$sz = (array) $sz;
			?>
				<div style="background:#f9f9f9;border-radius:6px;padding:.8rem;margin-bottom:.6rem;display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
					<input type="text" name="menu_sizes[<?php echo $idx; ?>][icon]"  value="<?php echo esc_attr( $sz['icon']  ?? '' ); ?>" placeholder="🥤" style="width:50px;" title="Emoji icon">
					<input type="text" name="menu_sizes[<?php echo $idx; ?>][name]"  value="<?php echo esc_attr( $sz['name']  ?? '' ); ?>" placeholder="e.g. Regular (350ml)" style="width:180px;" title="Size name">
					<input type="text" name="menu_sizes[<?php echo $idx; ?>][desc]"  value="<?php echo esc_attr( $sz['desc']  ?? '' ); ?>" placeholder="Description" style="flex:1;min-width:200px;" title="Short description">
					<input type="hidden" name="menu_sizes[<?php echo $idx; ?>][price]" value="<?php echo esc_attr( $sz['price'] ?? '' ); ?>">
					<input type="text" name="menu_sizes[<?php echo $idx; ?>][badge]" value="<?php echo esc_attr( $sz['badge'] ?? '' ); ?>" placeholder="Badge (e.g. Popular)" style="width:110px;" title="Optional badge label">
					<label style="font-size:.8rem;white-space:nowrap;"><input type="checkbox" name="menu_sizes[<?php echo $idx; ?>][featured]" value="1" <?php checked( ! empty( $sz['featured'] ) ); ?>> Featured</label>
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

		<!-- BOOKING WIZARD -->
		<div class="ch-card">
			<h2>🎫 Booking Wizard</h2>
			<p style="font-size:.83rem;color:#666;margin-bottom:1rem;">
				Multi-step order form on the homepage (Size → Cane → Flavour → Event Details → Confirm).
				Submissions arrive as an enquiry message under <strong>Enquiry Submissions</strong>.
				Options come from your <strong>Menu Sizes</strong> (above) and the Cane Types / Flavours data.
			</p>
			<div class="ch-row">
				<label>Section Heading</label>
				<input type="text" name="booking_heading"
					value="<?php echo esc_attr( $s['booking_heading'] ?? 'Book Your Order' ); ?>"
					placeholder="Book Your Order">
			</div>
			<div class="ch-row">
				<label>Section Sub-text</label>
				<input type="text" name="booking_sub"
					value="<?php echo esc_attr( $s['booking_sub'] ?? '' ); ?>"
					placeholder="Build your perfect fresh cane juice order in a few easy steps.">
			</div>
			<div class="ch-row">
				<label>Banner Image URL</label>
				<input type="url" name="booking_image"
					value="<?php echo esc_attr( $s['booking_image'] ?? '' ); ?>"
					placeholder="https://… (paste from Media Library)">
				<p style="font-size:.75rem;color:#888;margin-top:.3rem;width:100%;">Shown on the right side of the booking banner. Leave blank for the default juice photo.</p>
			</div>
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
				Control how many items each section shows on the homepage. When <strong>“Limit on homepage”</strong> is
				ticked, only the chosen number is shown with a <em>“View all”</em> button to the full page.
				Untick to show <strong>all</strong> items on the homepage (no button).
			</p>
			<input type="hidden" name="home_limits_present" value="1">

			<?php
			$limit_rows = [
				'story_cards' => [ 'label' => 'Sugarcane Story Cards', 'def' => 4, 'page' => '/our-story/' ],
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

		<!-- STORY CARDS -->
		<div class="ch-card">
			<h2>🌿 Sugarcane Story Cards</h2>
			<p style="font-size:.83rem;color:#666;margin-bottom:1rem;">
				The interactive tabbed section on the homepage. Each card reveals info when clicked. Shown after the marquee.
			</p>

			<div class="ch-row">
				<label>Section Heading</label>
				<input type="text" name="story_cards_heading"
					value="<?php echo esc_attr( $s['story_cards_heading'] ?? 'The Sugarcane Story' ); ?>"
					placeholder="The Sugarcane Story">
			</div>
			<div class="ch-row">
				<label>Section Sub-text</label>
				<input type="text" name="story_cards_sub"
					value="<?php echo esc_attr( $s['story_cards_sub'] ?? '' ); ?>"
					placeholder="From ancient fields to your cup...">
			</div>

			<hr style="margin:1rem 0;border-color:#eee;">
			<p style="font-size:.82rem;color:#888;margin-bottom:.8rem;">
				<strong><?php echo count( $story_cards ); ?> cards.</strong>
				Each card has an emoji icon, short tab label, heading, body text, bullet facts, and optional image URL.
				Facts: one per line.
			</p>

			<div id="ch-story-cards-wrap">
			<?php foreach ( $story_cards as $ci => $card ) :
				$card  = (array) $card;
				$facts = implode( "\n", (array) ( $card['facts'] ?? [] ) );
			?>
			<div class="ch-sc-admin-card" style="background:#f9f9f9;border:1px solid #ddd;border-radius:8px;padding:1rem;margin-bottom:.8rem;position:relative;">
					<button type="button" class="button ch-sc-remove" style="position:absolute;top:.6rem;right:.6rem;color:#b32d2e;border-color:#b32d2e;">✕ Remove</button>
				<div style="display:grid;grid-template-columns:50px 120px 1fr;gap:.5rem;margin-bottom:.5rem;align-items:start;">
					<div>
						<label style="font-size:.7rem;color:#888;display:block;">Icon</label>
						<input type="text" name="story_cards[<?php echo $ci; ?>][icon]"
							value="<?php echo esc_attr( $card['icon'] ?? '' ); ?>"
							style="width:100%;text-align:center;font-size:1.3rem;padding:.3rem;">
					</div>
					<div>
						<label style="font-size:.7rem;color:#888;display:block;">Tab Label</label>
						<input type="text" name="story_cards[<?php echo $ci; ?>][label]"
							value="<?php echo esc_attr( $card['label'] ?? '' ); ?>"
							placeholder="e.g. Live Pressed" style="width:100%;padding:.3rem .5rem;">
					</div>
					<div>
						<label style="font-size:.7rem;color:#888;display:block;">Panel Heading</label>
						<input type="text" name="story_cards[<?php echo $ci; ?>][heading]"
							value="<?php echo esc_attr( $card['heading'] ?? '' ); ?>"
							placeholder="Full heading shown in panel" style="width:100%;padding:.3rem .5rem;">
					</div>
				</div>
				<input type="hidden" name="story_cards[<?php echo $ci; ?>][id]"
					value="<?php echo esc_attr( $card['id'] ?? '' ); ?>">
				<div style="margin-bottom:.5rem;">
					<label style="font-size:.7rem;color:#888;display:block;">Body Text</label>
					<textarea name="story_cards[<?php echo $ci; ?>][body]"
						rows="3" style="width:100%;padding:.4rem .6rem;font-size:.83rem;"><?php echo esc_textarea( $card['body'] ?? '' ); ?></textarea>
				</div>
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
					<div>
						<label style="font-size:.7rem;color:#888;display:block;">Bullet Facts (one per line)</label>
						<textarea name="story_cards[<?php echo $ci; ?>][facts]"
							rows="4" style="width:100%;padding:.4rem .6rem;font-size:.8rem;"><?php echo esc_textarea( $facts ); ?></textarea>
					</div>
					<div>
						<label style="font-size:.7rem;color:#888;display:block;">Images (one per line - rotates as a slideshow)</label>
						<?php
						$card_imgs = $card['images'] ?? ( ! empty( $card['image'] ) ? [ $card['image'] ] : [] );
						if ( is_string( $card_imgs ) ) $card_imgs = preg_split( '/[\r\n,]+/', $card_imgs );
						$card_imgs_text = implode( "\n", array_filter( (array) $card_imgs ) );
						?>
						<textarea name="story_cards[<?php echo $ci; ?>][images]" rows="3"
							placeholder="https://example.com/photo.jpg&#10;assets/images/story/cane.jpg&#10;my-photo.jpg"
							style="width:100%;padding:.4rem .5rem;font-size:.78rem;font-family:monospace;"><?php echo esc_textarea( $card_imgs_text ); ?></textarea>
						<p style="font-size:.7rem;color:#aaa;margin-top:.3rem;">
							Full URLs, or theme paths like <code>assets/images/story/cane.jpg</code>, or just a filename <code>cane.jpg</code> (looks in <code>/assets/images/</code>).
							Add several lines for an auto-rotating gallery. Leave blank for the animated emoji.
						</p>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div><!-- #ch-story-cards-wrap -->
		<button type="button" id="ch-add-story-card" class="button button-secondary" style="margin-bottom:1rem;">+ Add Story Card</button>
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

// ── Story Cards: add / remove ───────────────────────────────────────────────
(function () {
	const wrap   = document.getElementById('ch-story-cards-wrap');
	const addBtn = document.getElementById('ch-add-story-card');
	if (!wrap || !addBtn) return;

	function cardHTML(i) {
		return `
			<button type="button" class="button ch-sc-remove" style="position:absolute;top:.6rem;right:.6rem;color:#b32d2e;border-color:#b32d2e;">✕ Remove</button>
			<div style="display:grid;grid-template-columns:50px 120px 1fr;gap:.5rem;margin:0 6rem .5rem 0;align-items:start;">
				<div><label style="font-size:.7rem;color:#888;display:block;">Icon</label>
					<input type="text" name="story_cards[${i}][icon]" value="🌿" style="width:100%;text-align:center;font-size:1.3rem;padding:.3rem;"></div>
				<div><label style="font-size:.7rem;color:#888;display:block;">Tab Label</label>
					<input type="text" name="story_cards[${i}][label]" placeholder="e.g. Live Pressed" style="width:100%;padding:.3rem .5rem;"></div>
				<div><label style="font-size:.7rem;color:#888;display:block;">Panel Heading</label>
					<input type="text" name="story_cards[${i}][heading]" placeholder="Full heading shown in panel" style="width:100%;padding:.3rem .5rem;"></div>
			</div>
			<input type="hidden" name="story_cards[${i}][id]" value="">
			<div style="margin-bottom:.5rem;"><label style="font-size:.7rem;color:#888;display:block;">Body Text</label>
				<textarea name="story_cards[${i}][body]" rows="3" style="width:100%;padding:.4rem .6rem;font-size:.83rem;"></textarea></div>
			<div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
				<div><label style="font-size:.7rem;color:#888;display:block;">Bullet Facts (one per line)</label>
					<textarea name="story_cards[${i}][facts]" rows="4" style="width:100%;padding:.4rem .6rem;font-size:.8rem;"></textarea></div>
				<div><label style="font-size:.7rem;color:#888;display:block;">Images (one per line)</label>
					<textarea name="story_cards[${i}][images]" rows="3" placeholder="https://… or assets/images/story/cane.jpg" style="width:100%;padding:.4rem .5rem;font-size:.78rem;font-family:monospace;"></textarea></div>
			</div>`;
	}

	addBtn.addEventListener('click', function () {
		const i   = 'new' + Date.now();
		const div = document.createElement('div');
		div.className = 'ch-sc-admin-card';
		div.style.cssText = 'background:#f9f9f9;border:1px solid #ddd;border-radius:8px;padding:1rem;margin-bottom:.8rem;position:relative;';
		div.innerHTML = cardHTML(i);
		wrap.appendChild(div);
		div.scrollIntoView({ behavior: 'smooth', block: 'center' });
	});

	// Event delegation for remove buttons (works for existing + new cards)
	wrap.addEventListener('click', function (e) {
		if (e.target.classList.contains('ch-sc-remove')) {
			if (confirm('Remove this story card?')) e.target.closest('.ch-sc-admin-card').remove();
		}
	});
})();
</script>
