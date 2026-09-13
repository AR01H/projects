<?php
/**
 * admin/tabs/additionals/SubRandomCtas.php - Random CTA Messages Admin Management
 *
 * 100% DB-driven management with master Enable/Disable switch, item toggles,
 * live visual card previews, and full CRUD.
 */

defined( 'ABSPATH' ) || exit;

$all_ctas   = class_exists( 'ADN_Additionals_Handler' ) ? ADN_Additionals_Handler::get_all_ctas() : array();
$settings   = class_exists( 'ADN_Additionals_Handler' ) ? ADN_Additionals_Handler::get_settings() : array( 'enabled' => 1, 'auto_inject' => 1 );
$is_enabled = ! empty( $settings['enabled'] );
?>

<div class="adn-ctas-manager" style="max-width:1200px;">

	<!-- Master Feature Status & Action Banner -->
	<div class="card" style="max-width:none;margin-bottom:20px;padding:18px 24px;border-left:5px solid <?php echo $is_enabled ? '#16a34a' : '#94a3b8'; ?>;background:<?php echo $is_enabled ? '#f0fdf4' : '#f8fafc'; ?>;">
		<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
			<div style="display:flex;align-items:center;gap:14px;">
				<span style="font-size:28px;display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:12px;background:<?php echo $is_enabled ? '#dcfce7' : '#e2e8f0'; ?>;color:<?php echo $is_enabled ? '#15803d' : '#64748b'; ?>;">
					<i class="fa-solid fa-shuffle"></i>
				</span>
				<div>
					<div style="display:flex;align-items:center;gap:10px;">
						<h3 style="margin:0;font-size:1.15rem;color:#0f172a;">
							<?php esc_html_e( 'Random Mini CTAs', ADN_TEXT_DOMAIN ); ?>
						</h3>
						<?php if ( $is_enabled ) : ?>
							<span style="background:#16a34a;color:#ffffff;font-size:11px;font-weight:700;padding:2px 10px;border-radius:12px;text-transform:uppercase;letter-spacing:0.5px;">
								● <?php esc_html_e( 'Enabled', ADN_TEXT_DOMAIN ); ?>
							</span>
						<?php else : ?>
							<span style="background:#64748b;color:#ffffff;font-size:11px;font-weight:700;padding:2px 10px;border-radius:12px;text-transform:uppercase;letter-spacing:0.5px;">
								○ <?php esc_html_e( 'Disabled', ADN_TEXT_DOMAIN ); ?>
							</span>
						<?php endif; ?>
					</div>
					<p style="margin:4px 0 0 0;font-size:13px;color:#475569;">
						<?php if ( $is_enabled ) : ?>
							<?php esc_html_e( 'Feature is ACTIVE. Rotating mini CTAs are randomly placed between sections across pages.', ADN_TEXT_DOMAIN ); ?>
						<?php else : ?>
							<?php esc_html_e( 'Feature is DISABLED. All random CTAs are hidden and will not display on the website.', ADN_TEXT_DOMAIN ); ?>
						<?php endif; ?>
					</p>
				</div>
			</div>

			<!-- Actions: Master Toggle, Add New & Reset -->
			<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
				<!-- Add New Button -->
				<button type="button" class="button button-primary" onclick="adnOpenCtaModal();" style="font-weight:600;padding:4px 14px;">
					<i class="fa-solid fa-plus"></i> <?php esc_html_e( 'Add New CTA', ADN_TEXT_DOMAIN ); ?>
				</button>

				<!-- Master Toggle Button -->
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
					<?php wp_nonce_field( 'adn_toggle_cta_master' ); ?>
					<input type="hidden" name="action" value="adn_toggle_cta_master">
					<?php if ( $is_enabled ) : ?>
						<button type="submit" class="button button-secondary" style="color:#b91c1c;border-color:#fca5a5;background:#fef2f2;font-weight:600;padding:4px 14px;">
							<i class="fa-solid fa-power-off"></i> <?php esc_html_e( 'Disable Feature', ADN_TEXT_DOMAIN ); ?>
						</button>
					<?php else : ?>
						<button type="submit" class="button button-secondary" style="color:#15803d;border-color:#86efac;background:#ffffff;font-weight:600;padding:4px 14px;">
							<i class="fa-solid fa-power-off"></i> <?php esc_html_e( 'Enable Feature', ADN_TEXT_DOMAIN ); ?>
						</button>
					<?php endif; ?>
				</form>
			</div>
		</div>
	</div>

	<!-- CTAs Cards Grid View -->
	<div style="margin-bottom:28px;">
		<h3 style="font-size:1.1rem;margin-bottom:12px;">
			<?php printf( esc_html__( 'CTA Messages Pool (%d items)', ADN_TEXT_DOMAIN ), count( $all_ctas ) ); ?>
		</h3>

		<?php if ( empty( $all_ctas ) ) : ?>
			<div class="card" style="padding:24px;text-align:center;color:#6b7280;">
				<p><?php esc_html_e( 'No Random CTAs in database. Click "+ Add New CTA Message" to create one.', ADN_TEXT_DOMAIN ); ?></p>
			</div>
		<?php else : ?>
			<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(360px, 1fr));gap:16px;">
				<?php foreach ( $all_ctas as $c ) :
					$c_id     = $c['id'] ?? 'cta';
					$c_hdg    = $c['heading'] ?? '';
					$c_msg    = $c['message'] ?? '';
					$c_icon   = $c['icon'] ?? 'fa-solid fa-sparkles';
					$c_color  = $c['color'] ?? '#1e3a2f';
					$c_btn    = $c['button_name'] ?? 'Learn More';
					$c_url    = $c['button_url'] ?? '#';
					$c_status = $c['status'] ?? 'active';
					$is_active = ( 'active' === $c_status );
				?>
				<div class="card adn-cta-admin-card" style="margin:0;padding:16px;border-radius:10px;border-left:4px solid <?php echo esc_attr( $c_color ); ?>;opacity:<?php echo $is_active ? '1' : '0.55'; ?>;display:flex;flex-direction:column;justify-content:space-between;background:<?php echo $is_active ? '#ffffff' : '#f8fafc'; ?>;">
					<div>
						<!-- Top row: Status, Color indicator & Icon -->
						<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
							<div style="display:flex;align-items:center;gap:8px;">
								<span style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;background:rgba(0,0,0,0.05);color:<?php echo esc_attr( $c_color ); ?>;font-size:16px;">
									<i class="<?php echo esc_attr( $c_icon ); ?>"></i>
								</span>
								<span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:600;color:#475569;">
									<span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:<?php echo esc_attr( $c_color ); ?>;"></span>
									<code><?php echo esc_html( $c_color ); ?></code>
								</span>
							</div>
							<div>
								<?php if ( $is_active ) : ?>
									<span style="color:#16a34a;font-size:12px;font-weight:700;">● <?php esc_html_e( 'Active', ADN_TEXT_DOMAIN ); ?></span>
								<?php else : ?>
									<span style="color:#94a3b8;font-size:12px;font-weight:700;">○ <?php esc_html_e( 'Disabled', ADN_TEXT_DOMAIN ); ?></span>
								<?php endif; ?>
							</div>
						</div>

						<!-- Heading & Message -->
						<?php if ( ! empty( $c_hdg ) ) : ?>
							<h4 style="margin:0 0 4px 0;font-size:14px;font-weight:700;color:#1e293b;">
								<?php echo esc_html( $c_hdg ); ?>
							</h4>
						<?php endif; ?>
						<p style="margin:0 0 12px 0;font-size:13px;color:#4b5563;line-height:1.4;">
							<?php echo esc_html( $c_msg ); ?>
						</p>

						<!-- Button Tag -->
						<div style="margin-bottom:14px;">
							<span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px;background:<?php echo esc_attr( $c_color ); ?>;color:#ffffff;">
								<?php echo esc_html( $c_btn ); ?> &rarr;
							</span>
							<code style="font-size:11px;color:#6b7280;margin-left:6px;"><?php echo esc_html( $c_url ); ?></code>
						</div>
					</div>

					<!-- Bottom Action Controls -->
					<div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid #f1f5f9;padding-top:10px;margin-top:auto;">
						<span style="font-size:11px;color:#94a3b8;font-family:monospace;">ID: <?php echo esc_html( $c_id ); ?></span>
						<div style="display:flex;gap:6px;">
							<!-- Toggle Status -->
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
								<?php wp_nonce_field( 'adn_toggle_random_cta' ); ?>
								<input type="hidden" name="action" value="adn_toggle_random_cta">
								<input type="hidden" name="id" value="<?php echo esc_attr( $c_id ); ?>">
								<button type="submit" class="button button-small" title="<?php echo $is_active ? esc_attr__( 'Disable this message', ADN_TEXT_DOMAIN ) : esc_attr__( 'Enable this message', ADN_TEXT_DOMAIN ); ?>">
									<?php echo $is_active ? '<i class="fa-solid fa-pause"></i> ' . esc_html__( 'Disable', ADN_TEXT_DOMAIN ) : '<i class="fa-solid fa-play"></i> ' . esc_html__( 'Enable', ADN_TEXT_DOMAIN ); ?>
								</button>
							</form>

							<!-- Edit Button -->
							<button type="button" class="button button-small"
								onclick='adnEditCta(<?php echo wp_json_encode( $c ); ?>);'>
								<i class="fa-solid fa-pen"></i> <?php esc_html_e( 'Edit', ADN_TEXT_DOMAIN ); ?>
							</button>

							<!-- Delete Button -->
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
								onsubmit="return confirm('<?php echo esc_js( __( 'Delete this CTA message?', ADN_TEXT_DOMAIN ) ); ?>');"
								style="margin:0;">
								<?php wp_nonce_field( 'adn_delete_random_cta' ); ?>
								<input type="hidden" name="action" value="adn_delete_random_cta">
								<input type="hidden" name="id" value="<?php echo esc_attr( $c_id ); ?>">
								<button type="submit" class="button button-small button-link-delete" style="color:#ef4444;" title="<?php esc_attr_e( 'Delete', ADN_TEXT_DOMAIN ); ?>">
									<i class="fa-solid fa-trash"></i>
								</button>
							</form>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<!-- Add / Edit Modal Drawer -->
	<div id="adnCtaModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);z-index:99999;align-items:center;justify-content:center;">
		<div style="background:#ffffff;border-radius:14px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);max-width:580px;width:90%;max-height:90vh;overflow-y:auto;padding:24px;position:relative;">

			<div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e2e8f0;padding-bottom:12px;margin-bottom:16px;">
				<h3 id="adnCtaModalTitle" style="margin:0;font-size:1.2rem;">
					<i class="fa-solid fa-pen-to-square" style="color:#c9a84c;margin-right:6px;"></i>
					<?php esc_html_e( 'Add New Random CTA', ADN_TEXT_DOMAIN ); ?>
				</h3>
				<button type="button" onclick="adnCloseCtaModal();" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;">&times;</button>
			</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="adnCtaForm">
				<?php wp_nonce_field( 'adn_save_random_cta' ); ?>
				<input type="hidden" name="action" value="adn_save_random_cta">
				<input type="hidden" name="orig_id" id="adn_cta_orig_id" value="">
				<input type="hidden" name="color_name" id="adn_cta_color_name" value="default">

				<!-- Heading (Question / Hook) -->
				<div style="margin-bottom:14px;">
					<label for="adn_cta_heading" style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">
						<?php esc_html_e( 'Heading / Question Hook', ADN_TEXT_DOMAIN ); ?>
					</label>
					<input type="text" name="heading" id="adn_cta_heading" class="widefat"
						placeholder="<?php esc_attr_e( 'e.g. Want to Save Thousands on Your UK Home?', ADN_TEXT_DOMAIN ); ?>" required>
				</div>

				<!-- Message Body -->
				<div style="margin-bottom:14px;">
					<label for="adn_cta_message" style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">
						<?php esc_html_e( 'Message Copy', ADN_TEXT_DOMAIN ); ?> <span style="color:#ef4444;">*</span>
					</label>
					<textarea name="message" id="adn_cta_message" rows="3" class="widefat"
						placeholder="<?php esc_attr_e( 'Brief, persuasive value proposition or advice copy...', ADN_TEXT_DOMAIN ); ?>" required></textarea>
				</div>

				<!-- Single Intuitive Color Selection -->
				<div style="margin-bottom:14px;">
					<label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">
						<?php esc_html_e( 'Accent Color', ADN_TEXT_DOMAIN ); ?>
					</label>
					<div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
						<input type="color" id="adn_cta_color_picker" value="#1e3a2f" style="width:40px;height:36px;padding:0;border:1px solid #cbd5e1;border-radius:6px;cursor:pointer;" onchange="adnSetColor(this.value);">
						<input type="text" name="color" id="adn_cta_color" value="#1e3a2f" style="max-width:140px;font-family:monospace;font-weight:600;" oninput="document.getElementById('adn_cta_color_picker').value=this.value;">
					</div>

					<!-- Quick Clickable Palettes -->
					<div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
						<span style="font-size:11px;color:#64748b;font-weight:600;margin-right:2px;"><?php esc_html_e( 'Presets:', ADN_TEXT_DOMAIN ); ?></span>
						<button type="button" class="button button-small" onclick="adnSetColor('#1e3a2f', 'emerald');" style="background:#1e3a2f;color:#fff;border:none;">Emerald</button>
						<button type="button" class="button button-small" onclick="adnSetColor('#1a3c5e', 'navy');" style="background:#1a3c5e;color:#fff;border:none;">Navy</button>
						<button type="button" class="button button-small" onclick="adnSetColor('#c9a84c', 'gold');" style="background:#c9a84c;color:#1a1a1a;border:none;">Gold</button>
						<button type="button" class="button button-small" onclick="adnSetColor('#0f766e', 'teal');" style="background:#0f766e;color:#fff;border:none;">Teal</button>
						<button type="button" class="button button-small" onclick="adnSetColor('#7c2d12', 'bronze');" style="background:#7c2d12;color:#fff;border:none;">Bronze</button>
						<button type="button" class="button button-small" onclick="adnSetColor('#312e81', 'indigo');" style="background:#312e81;color:#fff;border:none;">Indigo</button>
						<button type="button" class="button button-small" onclick="adnSetColor('#25d366', 'whatsapp');" style="background:#25d366;color:#fff;border:none;">WhatsApp</button>
					</div>
				</div>

				<!-- Icon Selection with Live Preview & Clickable Auto-Suggestions -->
				<div style="margin-bottom:14px;">
					<label for="adn_cta_icon" style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">
						<?php esc_html_e( 'Icon', ADN_TEXT_DOMAIN ); ?>
					</label>
					<div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
						<span id="adn_icon_preview" style="display:inline-flex;align-items:center;justify-content:center;width:38px;height:36px;border-radius:6px;background:#f1f5f9;border:1px solid #cbd5e1;font-size:16px;color:#1e3a2f;">
							<i class="fa-solid fa-sparkles"></i>
						</span>
						<input type="text" name="icon" id="adn_cta_icon" class="widefat" list="adn_icon_datalist"
							placeholder="fa-solid fa-sparkles" value="fa-solid fa-sparkles" oninput="adnUpdateIconPreview(this.value);" required>
					</div>

					<!-- Datalist for typing auto-suggestions -->
					<datalist id="adn_icon_datalist">
						<!-- Finance & Savings -->
						<option value="fa-solid fa-piggy-bank">Piggy Bank (Savings / Cost Reductions)</option>
						<option value="fa-solid fa-calculator">Calculator (Budget / Mortgage / ROI)</option>
						<option value="fa-solid fa-coins">Coins (Cash / Down Payment)</option>
						<option value="fa-solid fa-wallet">Wallet (Affordability / Budget Planning)</option>
						<option value="fa-solid fa-percent">Percent (Discounts / Interest Rates)</option>
						<option value="fa-solid fa-chart-line">Chart Line (Investment / Capital Growth)</option>
						<option value="fa-solid fa-receipt">Receipt (Quotes / Cost Breakdown)</option>
						
						<!-- Contact & Booking -->
						<option value="fa-brands fa-whatsapp">WhatsApp (Direct Instant Chat)</option>
						<option value="fa-solid fa-phone">Phone (Direct Advisory Call)</option>
						<option value="fa-solid fa-phone-volume">Phone Volume (Toll-Free / Immediate Callback)</option>
						<option value="fa-solid fa-headset">Headset (Dedicated Client Support)</option>
						<option value="fa-solid fa-comments">Comments (Consultation & Q&A)</option>
						<option value="fa-solid fa-calendar-check">Calendar Check (Book Appointment / Viewing)</option>
						<option value="fa-solid fa-envelope">Envelope (Inquiry / Free Information Pack)</option>
						<option value="fa-solid fa-video">Video (Virtual Tour / Video Call)</option>

						<!-- Property & Housing -->
						<option value="fa-solid fa-house-chimney">House Chimney (Residential Family Home)</option>
						<option value="fa-solid fa-city">City (Apartments / Prime Developments)</option>
						<option value="fa-solid fa-building">Building (Commercial / Off-Plan)</option>
						<option value="fa-solid fa-key">Key (Handover / Ownership)</option>
						<option value="fa-solid fa-magnifying-glass-location">Search Location (Property Search)</option>
						<option value="fa-solid fa-location-dot">Location Dot (Area & Neighborhood)</option>
						<option value="fa-solid fa-door-open">Open Door (Open House / Viewing)</option>
						<option value="fa-solid fa-ruler-combined">Ruler Combined (Floor Plans / Architecture)</option>
						<option value="fa-solid fa-truck-moving">Moving Truck (Relocation Assistance)</option>

						<!-- Legal & Trust -->
						<option value="fa-solid fa-file-shield">Legal Shield (Conveyancing / Protection)</option>
						<option value="fa-solid fa-file-contract">Contract (Legal Agreements / Signatures)</option>
						<option value="fa-solid fa-shield-halved">Shield (Safety & Buyer Guarantee)</option>
						<option value="fa-solid fa-scale-balanced">Scales (Conveyancing / Law)</option>
						<option value="fa-solid fa-handshake">Handshake (Partnership & Trust)</option>
						<option value="fa-solid fa-award">Award (Certified / Accreditation)</option>
						<option value="fa-solid fa-user-tie">Expert Advisor (Professional Guidance)</option>
						<option value="fa-solid fa-user-check">Verified Agent (Trusted Professional)</option>

						<!-- Guidance, Tools & Deals -->
						<option value="fa-solid fa-compass">Compass (Step-by-Step Buyer Roadmap)</option>
						<option value="fa-solid fa-book-open-reader">Book / Guide (Buyer eBook / Brochure)</option>
						<option value="fa-solid fa-clipboard-check">Clipboard (Due Diligence Checklist)</option>
						<option value="fa-solid fa-graduation-cap">Graduation Cap (Buyer Education & Tips)</option>
						<option value="fa-solid fa-lightbulb">Lightbulb (Smart Hacks & Advice)</option>
						<option value="fa-solid fa-tags">Tags (Exclusive Deals & Special Offers)</option>
						<option value="fa-solid fa-gift">Gift (Free Consultation / Welcome Bonus)</option>
						<option value="fa-solid fa-star">Star (Top-Rated / Customer Reviews)</option>
						<option value="fa-solid fa-bolt">Bolt (Fast-Track / Instant Access)</option>
						<option value="fa-solid fa-clock">Clock (Limited Time Offer)</option>
						<option value="fa-solid fa-sparkles">Sparkles (Featured Highlight)</option>
					</datalist>

					<!-- Clickable Quick Icon Chips -->
					<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;max-height:190px;overflow-y:auto;">
						<!-- Row 1: Finance & Savings -->
						<div style="margin-bottom:8px;">
							<span style="font-size:10px;font-weight:700;text-transform:uppercase;color:#64748b;display:block;margin-bottom:4px;letter-spacing:0.5px;"><?php esc_html_e( '💰 Savings & Finance', ADN_TEXT_DOMAIN ); ?></span>
							<div style="display:flex;gap:5px;flex-wrap:wrap;">
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-piggy-bank');"><i class="fa-solid fa-piggy-bank"></i> Savings</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-calculator');"><i class="fa-solid fa-calculator"></i> Calculator</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-coins');"><i class="fa-solid fa-coins"></i> Cash/Deposit</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-wallet');"><i class="fa-solid fa-wallet"></i> Budget</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-percent');"><i class="fa-solid fa-percent"></i> Rates</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-chart-line');"><i class="fa-solid fa-chart-line"></i> Growth/ROI</button>
							</div>
						</div>

						<!-- Row 2: Contact & Direct Connect -->
						<div style="margin-bottom:8px;">
							<span style="font-size:10px;font-weight:700;text-transform:uppercase;color:#64748b;display:block;margin-bottom:4px;letter-spacing:0.5px;"><?php esc_html_e( '📞 Contact & Booking', ADN_TEXT_DOMAIN ); ?></span>
							<div style="display:flex;gap:5px;flex-wrap:wrap;">
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-brands fa-whatsapp');"><i class="fa-brands fa-whatsapp" style="color:#25d366;"></i> WhatsApp</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-phone');"><i class="fa-solid fa-phone"></i> Call</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-calendar-check');"><i class="fa-solid fa-calendar-check"></i> Book Call</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-comments');"><i class="fa-solid fa-comments"></i> Chat</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-headset');"><i class="fa-solid fa-headset"></i> Support</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-video');"><i class="fa-solid fa-video"></i> Video Tour</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-envelope');"><i class="fa-solid fa-envelope"></i> Email Pack</button>
							</div>
						</div>

						<!-- Row 3: Property & Architecture -->
						<div style="margin-bottom:8px;">
							<span style="font-size:10px;font-weight:700;text-transform:uppercase;color:#64748b;display:block;margin-bottom:4px;letter-spacing:0.5px;"><?php esc_html_e( '🏠 Property & Search', ADN_TEXT_DOMAIN ); ?></span>
							<div style="display:flex;gap:5px;flex-wrap:wrap;">
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-house-chimney');"><i class="fa-solid fa-house-chimney"></i> House</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-city');"><i class="fa-solid fa-city"></i> Apartments</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-key');"><i class="fa-solid fa-key"></i> Keys</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-magnifying-glass-location');"><i class="fa-solid fa-magnifying-glass-location"></i> Find Home</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-location-dot');"><i class="fa-solid fa-location-dot"></i> Location</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-door-open');"><i class="fa-solid fa-door-open"></i> Viewings</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-ruler-combined');"><i class="fa-solid fa-ruler-combined"></i> Floor Plan</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-truck-moving');"><i class="fa-solid fa-truck-moving"></i> Moving</button>
							</div>
						</div>

						<!-- Row 4: Trust, Legal & Quality -->
						<div style="margin-bottom:8px;">
							<span style="font-size:10px;font-weight:700;text-transform:uppercase;color:#64748b;display:block;margin-bottom:4px;letter-spacing:0.5px;"><?php esc_html_e( '🛡️ Trust & Legal', ADN_TEXT_DOMAIN ); ?></span>
							<div style="display:flex;gap:5px;flex-wrap:wrap;">
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-file-shield');"><i class="fa-solid fa-file-shield"></i> Legal Shield</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-file-contract');"><i class="fa-solid fa-file-contract"></i> Contract</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-scale-balanced');"><i class="fa-solid fa-scale-balanced"></i> Conveyancing</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-shield-halved');"><i class="fa-solid fa-shield-halved"></i> Guarantee</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-handshake');"><i class="fa-solid fa-handshake"></i> Trust/Partner</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-award');"><i class="fa-solid fa-award"></i> Certified</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-user-tie');"><i class="fa-solid fa-user-tie"></i> Advisor</button>
							</div>
						</div>

						<!-- Row 5: Guides, Offers & Perks -->
						<div>
							<span style="font-size:10px;font-weight:700;text-transform:uppercase;color:#64748b;display:block;margin-bottom:4px;letter-spacing:0.5px;"><?php esc_html_e( '✨ Guides, Deals & Perks', ADN_TEXT_DOMAIN ); ?></span>
							<div style="display:flex;gap:5px;flex-wrap:wrap;">
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-compass');"><i class="fa-solid fa-compass"></i> Roadmap</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-book-open-reader');"><i class="fa-solid fa-book-open-reader"></i> Free Guide</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-clipboard-check');"><i class="fa-solid fa-clipboard-check"></i> Checklist</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-lightbulb');"><i class="fa-solid fa-lightbulb"></i> Tips & Advice</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-tags');"><i class="fa-solid fa-tags"></i> Deals</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-gift');"><i class="fa-solid fa-gift"></i> Free Perk</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-star');"><i class="fa-solid fa-star"></i> Top Rated</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-bolt');"><i class="fa-solid fa-bolt"></i> Fast-Track</button>
								<button type="button" class="button button-small" onclick="adnSelectIcon('fa-solid fa-sparkles');"><i class="fa-solid fa-sparkles"></i> Featured</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Button Text & Destination Link -->
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
					<div>
						<label for="adn_cta_btn_name" style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">
							<?php esc_html_e( 'Button Text', ADN_TEXT_DOMAIN ); ?>
						</label>
						<input type="text" name="button_name" id="adn_cta_btn_name" class="widefat" placeholder="e.g. Book Free Call" value="Learn More" required>
					</div>
					<div>
						<label for="adn_cta_btn_url" style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">
							<?php esc_html_e( 'Button Link (URL)', ADN_TEXT_DOMAIN ); ?>
						</label>
						<input type="text" name="button_url" id="adn_cta_btn_url" class="widefat" placeholder="e.g. /contact/ or https://wa.me/..." value="/contact/" required>
					</div>
				</div>

				<!-- Unique ID & Status -->
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
					<div>
						<label for="adn_cta_id" style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">
							<?php esc_html_e( 'Identifier (Slug)', ADN_TEXT_DOMAIN ); ?>
						</label>
						<input type="text" name="id" id="adn_cta_id" class="widefat" placeholder="e.g. save_money">
					</div>
					<div>
						<label for="adn_cta_status" style="display:block;font-weight:600;margin-bottom:4px;font-size:13px;">
							<?php esc_html_e( 'Status', ADN_TEXT_DOMAIN ); ?>
						</label>
						<select name="status" id="adn_cta_status" class="widefat">
							<option value="active"><?php esc_html_e( 'Active (In Rotation)', ADN_TEXT_DOMAIN ); ?></option>
							<option value="inactive"><?php esc_html_e( 'Disabled (Hidden)', ADN_TEXT_DOMAIN ); ?></option>
						</select>
					</div>
				</div>

				<!-- Modal Actions -->
				<div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e2e8f0;padding-top:16px;">
					<button type="button" class="button button-secondary" onclick="adnCloseCtaModal();">
						<?php esc_html_e( 'Cancel', ADN_TEXT_DOMAIN ); ?>
					</button>
					<button type="submit" class="button button-primary button-large">
						<i class="fa-solid fa-check"></i> <?php esc_html_e( 'Save CTA Message', ADN_TEXT_DOMAIN ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>

</div>

<script>
function adnOpenCtaModal() {
	document.getElementById('adnCtaModalTitle').innerHTML = '<i class="fa-solid fa-plus" style="color:#c9a84c;margin-right:6px;"></i> <?php echo esc_js( __( 'Add New Random CTA', ADN_TEXT_DOMAIN ) ); ?>';
	document.getElementById('adn_cta_orig_id').value = '';
	document.getElementById('adn_cta_id').value = '';
	document.getElementById('adn_cta_heading').value = '';
	document.getElementById('adn_cta_message').value = '';
	document.getElementById('adn_cta_icon').value = 'fa-solid fa-sparkles';
	adnUpdateIconPreview('fa-solid fa-sparkles');
	adnSetColor('#1e3a2f', 'emerald');
	document.getElementById('adn_cta_btn_name').value = 'Learn More';
	document.getElementById('adn_cta_btn_url').value = '/contact/';
	document.getElementById('adn_cta_status').value = 'active';

	var modal = document.getElementById('adnCtaModal');
	modal.style.display = 'flex';
}

function adnCloseCtaModal() {
	document.getElementById('adnCtaModal').style.display = 'none';
}

function adnEditCta(item) {
	if (!item) return;
	document.getElementById('adnCtaModalTitle').innerHTML = '<i class="fa-solid fa-pen" style="color:#c9a84c;margin-right:6px;"></i> <?php echo esc_js( __( 'Edit Random CTA', ADN_TEXT_DOMAIN ) ); ?>';
	document.getElementById('adn_cta_orig_id').value = item.id || '';
	document.getElementById('adn_cta_id').value = item.id || '';
	document.getElementById('adn_cta_heading').value = item.heading || '';
	document.getElementById('adn_cta_message').value = item.message || '';
	var icon = item.icon || 'fa-solid fa-sparkles';
	document.getElementById('adn_cta_icon').value = icon;
	adnUpdateIconPreview(icon);
	adnSetColor(item.color || '#1e3a2f', item.color_name || 'default');
	document.getElementById('adn_cta_btn_name').value = item.button_name || 'Learn More';
	document.getElementById('adn_cta_btn_url').value = item.button_url || '#';
	document.getElementById('adn_cta_status').value = item.status || 'active';

	var modal = document.getElementById('adnCtaModal');
	modal.style.display = 'flex';
}

function adnSetColor(hex, name) {
	document.getElementById('adn_cta_color').value = hex;
	document.getElementById('adn_cta_color_picker').value = hex;
	document.getElementById('adn_cta_color_name').value = name || 'default';
}

function adnSelectIcon(iconClass) {
	document.getElementById('adn_cta_icon').value = iconClass;
	adnUpdateIconPreview(iconClass);
}

function adnUpdateIconPreview(iconClass) {
	var preview = document.getElementById('adn_icon_preview');
	if (!preview) return;
	preview.innerHTML = '<i class="' + (iconClass || 'fa-solid fa-sparkles') + '"></i>';
}
</script>
