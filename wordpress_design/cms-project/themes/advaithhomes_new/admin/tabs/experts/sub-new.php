<?php
/**
 * admin/tabs/experts/sub-new.php - Add or Edit a DB-stored expert / team member.
 *
 * Add mode  : no query params - shows a blank form.
 * Edit mode : ?edit_slug=SLUG - loads the existing DB row and pre-fills everything.
 */

defined( 'ABSPATH' ) || exit;

$edit_slug = isset( $_GET['edit_slug'] ) ? sanitize_key( wp_unslash( $_GET['edit_slug'] ) ) : '';
$row       = ( $edit_slug && class_exists( 'AH_Expert_DB' ) ) ? AH_Expert_DB::get( $edit_slug ) : null;
$is_edit   = ( null !== $row );

// Defaults.
$f_slug    = $is_edit ? $row['expert_slug']   : '';
$f_name    = $is_edit ? $row['name']          : '';
$f_title   = $is_edit ? $row['title']         : '';
$f_cat     = $is_edit ? $row['category']      : '';
$f_status  = $is_edit ? $row['status']        : 'active';
$f_photo   = $is_edit ? (int) $row['photo_id'] : 0;
$f_bio     = $is_edit ? $row['bio']           : '';
$f_rating  = $is_edit ? $row['rating']        : '';
$f_reviews = $is_edit ? $row['reviews_count'] : '';
$f_loc     = $is_edit ? $row['location']      : '';
$f_phone   = $is_edit ? $row['phone']         : '';
$f_email   = $is_edit ? $row['email']         : '';
$f_mega           = $is_edit ? $row['mega_html']      : '';
$f_banner_img_id  = $is_edit ? (int) $row['banner_image_id'] : 0;
$f_banner_img_url = ( $f_banner_img_id > 0 ) ? wp_get_attachment_image_url( $f_banner_img_id, 'large' ) : false;

// Banner marquee items: decode JSON → array of { icon, value, label }.
$f_banner_arr = array();
if ( $is_edit && ! empty( $row['banner_json'] ) ) {
	$_bd = json_decode( $row['banner_json'], true );
	if ( is_array( $_bd ) ) { $f_banner_arr = $_bd; }
}

// Bullets: decode JSON → one per line for textarea.
$f_bullets_arr = array();
if ( $is_edit && ! empty( $row['bullets'] ) ) {
	$dec = json_decode( $row['bullets'], true );
	if ( is_array( $dec ) ) { $f_bullets_arr = $dec; }
}
$f_bullets_text = implode( "\n", $f_bullets_arr );

// Client images: decode JSON → will be re-serialised into hidden field via JS.
$f_ci_arr = array();
if ( $is_edit && ! empty( $row['client_images'] ) ) {
	$dec = json_decode( $row['client_images'], true );
	if ( is_array( $dec ) ) { $f_ci_arr = $dec; }
}
$f_ci_json = wp_json_encode( $f_ci_arr );

// Photo preview.
$f_photo_url = ( $f_photo > 0 ) ? wp_get_attachment_image_url( $f_photo, 'thumbnail' ) : false;
?>
<div class="card" style="max-width:none;">
	<h2 style="margin-top:0;">
		<?php echo $is_edit
			? esc_html( sprintf( __( 'Edit Expert: %s', ADN_TEXT_DOMAIN ), $f_name ) )
			: esc_html__( 'Add New Expert', ADN_TEXT_DOMAIN ); ?>
	</h2>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="expertForm">
		<input type="hidden" name="action" value="adn_save_expert">
		<?php wp_nonce_field( 'adn_save_expert' ); ?>
		<?php if ( $is_edit ) : ?>
			<input type="hidden" name="edit_slug" value="<?php echo esc_attr( $f_slug ); ?>">
		<?php endif; ?>

		<?php /* ── Identity ────────────────────────────────────────────── */ ?>
		<div class="card" style="max-width:none;background:#fafafa;margin-bottom:24px;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'Identity', ADN_TEXT_DOMAIN ); ?></h3>
			<table class="form-table" role="presentation"><tbody>

				<?php if ( ! $is_edit ) : ?>
				<tr>
					<th scope="row">
						<label for="expert_slug"><?php esc_html_e( 'Slug', ADN_TEXT_DOMAIN ); ?> <span style="color:#b91c1c;">*</span></label>
					</th>
					<td>
						<input type="text" id="expert_slug" name="expert_slug" class="regular-text" required
							pattern="[a-z0-9\-]+" placeholder="e.g. john-smith" value="">
						<p class="description">
							<?php esc_html_e( 'Lowercase letters, numbers and hyphens only. Cannot be changed after saving. Used in the profile URL: ?ah_expert=slug.', ADN_TEXT_DOMAIN ); ?>
						</p>
					</td>
				</tr>
				<?php else : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Slug', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<code style="font-size:14px;"><?php echo esc_html( $f_slug ); ?></code>
						<span class="description">&nbsp;
							<?php esc_html_e( 'Slug cannot be changed after creation.', ADN_TEXT_DOMAIN ); ?>
							&nbsp;|&nbsp;
							<a href="<?php echo esc_url( home_url( '/?ah_expert=' . rawurlencode( $f_slug ) ) ); ?>" target="_blank">
								<?php esc_html_e( 'View profile ↗', ADN_TEXT_DOMAIN ); ?>
							</a>
						</span>
					</td>
				</tr>
				<?php endif; ?>

				<tr>
					<th scope="row">
						<label for="expert_name"><?php esc_html_e( 'Full Name', ADN_TEXT_DOMAIN ); ?> <span style="color:#b91c1c;">*</span></label>
					</th>
					<td>
						<input type="text" id="expert_name" name="name" class="regular-text" required
							value="<?php echo esc_attr( $f_name ); ?>" placeholder="Jane Smith">
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="expert_title"><?php esc_html_e( 'Specialisation / Title', ADN_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<input type="text" id="expert_title" name="title" class="regular-text"
							value="<?php echo esc_attr( $f_title ); ?>" placeholder="Senior Consultant">
						<p class="description"><?php esc_html_e( 'Shown below the name on cards and profile.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="expert_category"><?php esc_html_e( 'Category', ADN_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<input type="text" id="expert_category" name="category" class="regular-text"
							value="<?php echo esc_attr( $f_cat ); ?>" placeholder="consultant">
						<p class="description"><?php esc_html_e( 'Lowercase slug, e.g. consultant, advisor, specialist. Used by the category filter tabs.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Status', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<label>
							<input type="radio" name="status" value="active" <?php checked( $f_status, 'active' ); ?>>
							<?php esc_html_e( 'Active - shown on the listing page and profile', ADN_TEXT_DOMAIN ); ?>
						</label>
						&nbsp;&nbsp;
						<label>
							<input type="radio" name="status" value="inactive" <?php checked( $f_status, 'inactive' ); ?>>
							<?php esc_html_e( 'Inactive - hidden everywhere', ADN_TEXT_DOMAIN ); ?>
						</label>
					</td>
				</tr>

			</tbody></table>
		</div>

		<?php /* ── Photo ─────────────────────────────────────────────────── */ ?>
		<div class="card" style="max-width:none;background:#fafafa;margin-bottom:24px;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'Profile Photo', ADN_TEXT_DOMAIN ); ?></h3>
			<table class="form-table" role="presentation"><tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Photo', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<div style="display:flex;gap:12px;align-items:flex-start;">
							<div id="expert_photo_prev" style="min-width:80px;">
								<?php if ( $f_photo_url ) : ?>
									<img src="<?php echo esc_url( $f_photo_url ); ?>"
										style="width:80px;height:80px;object-fit:cover;border-radius:50%;display:block;" alt="">
								<?php else : ?>
									<div style="width:80px;height:80px;background:linear-gradient(135deg,#e8f0ec,#d4e6d8);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;">👤</div>
								<?php endif; ?>
							</div>
							<div>
								<input type="hidden" id="expert_photo_id" name="photo_id"
									value="<?php echo esc_attr( (string) $f_photo ); ?>">
								<button type="button" class="button expert-photo-select"
									data-target="expert_photo_id"
									data-preview="expert_photo_prev">
									<?php esc_html_e( 'Select / Change Photo', ADN_TEXT_DOMAIN ); ?>
								</button>
								<?php if ( $f_photo_url ) : ?>
									<button type="button" class="button expert-photo-remove"
										data-target="expert_photo_id"
										data-preview="expert_photo_prev"
										style="margin-left:4px;">
										<?php esc_html_e( 'Remove', ADN_TEXT_DOMAIN ); ?>
									</button>
								<?php endif; ?>
								<p class="description" style="margin-top:6px;">
									<?php esc_html_e( 'Shown as a circular avatar on cards and as a large photo on the profile page.', ADN_TEXT_DOMAIN ); ?>
								</p>
							</div>
						</div>
					</td>
				</tr>
			</tbody></table>
		</div>

		<?php /* ── Details ───────────────────────────────────────────────── */ ?>
		<div class="card" style="max-width:none;background:#fafafa;margin-bottom:24px;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'Details', ADN_TEXT_DOMAIN ); ?></h3>
			<table class="form-table" role="presentation"><tbody>

				<tr>
					<th scope="row">
						<label for="expert_bio"><?php esc_html_e( 'Bio / Description', ADN_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<textarea id="expert_bio" name="bio" class="large-text" rows="5"
							placeholder="<?php esc_attr_e( 'A short paragraph about this expert\'s background and expertise...', ADN_TEXT_DOMAIN ); ?>"><?php echo esc_textarea( $f_bio ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Shown on both the listing card and the full profile page.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="expert_location"><?php esc_html_e( 'Location', ADN_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<input type="text" id="expert_location" name="location" class="regular-text"
							value="<?php echo esc_attr( $f_loc ); ?>" placeholder="London, UK">
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="expert_phone"><?php esc_html_e( 'Phone', ADN_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<input type="text" id="expert_phone" name="phone" class="regular-text"
							value="<?php echo esc_attr( $f_phone ); ?>" placeholder="+44 20 1234 5678">
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="expert_email"><?php esc_html_e( 'Email', ADN_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<input type="email" id="expert_email" name="email" class="regular-text"
							value="<?php echo esc_attr( $f_email ); ?>" placeholder="jane@example.com">
						<p class="description"><?php esc_html_e( 'Contact form enquiries for this expert are sent here. Falls back to the site admin email if blank.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="expert_rating"><?php esc_html_e( 'Rating', ADN_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<input type="number" id="expert_rating" name="rating"
							style="width:80px;" min="0" max="5" step="0.1"
							value="<?php echo esc_attr( (string) $f_rating ); ?>" placeholder="4.9">
						<span style="margin-left:6px;color:#6b7280;font-size:13px;">/ 5.0</span>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="expert_reviews"><?php esc_html_e( 'Review Count', ADN_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<input type="number" id="expert_reviews" name="reviews_count"
							style="width:100px;" min="0" step="1"
							value="<?php echo esc_attr( (string) $f_reviews ); ?>" placeholder="47">
						<p class="description"><?php esc_html_e( 'Number shown in brackets next to the rating stars.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

			</tbody></table>
		</div>

		<?php /* ── Bullets ───────────────────────────────────────────────── */ ?>
		<div class="card" style="max-width:none;background:#fafafa;margin-bottom:24px;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'Bullet Points', ADN_TEXT_DOMAIN ); ?></h3>
			<p class="description" style="margin-bottom:12px;">
				<?php esc_html_e( 'One bullet per line. Shown as a "Specialises in" list on the profile page and as small pills on the card.', ADN_TEXT_DOMAIN ); ?>
			</p>
			<textarea name="bullets_text" id="bullets_text" class="large-text" rows="6"
				placeholder="<?php esc_attr_e( "15+ years experience\nRICS qualified\nFirst-time buyer specialist", ADN_TEXT_DOMAIN ); ?>"><?php echo esc_textarea( $f_bullets_text ); ?></textarea>
			<?php if ( ! empty( $f_bullets_arr ) ) : ?>
				<div class="ah-bullets-preview" style="margin-top:10px;display:flex;flex-wrap:wrap;gap:6px;">
					<?php foreach ( $f_bullets_arr as $_b ) : ?>
						<span style="background:#e8f5ee;border:1px solid #c6e8d2;border-radius:20px;padding:3px 12px;font-size:12px;color:#166534;">
							<?php echo esc_html( $_b ); ?>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php /* ── Cover / Banner Image (LinkedIn-style) ────────────── */ ?>
		<div class="card" style="max-width:none;background:#fafafa;margin-bottom:24px;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'Cover Banner Image', ADN_TEXT_DOMAIN ); ?></h3>
			<p class="description" style="margin-bottom:16px;">
				<?php esc_html_e( 'A wide background image shown behind the profile hero — like a LinkedIn cover photo. Recommended size: 1200×300 px.', ADN_TEXT_DOMAIN ); ?>
			</p>

			<div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap;">
				<div id="expert_banner_img_prev" style="flex:1;min-width:200px;max-width:480px;">
					<?php if ( $f_banner_img_url ) : ?>
						<img src="<?php echo esc_url( $f_banner_img_url ); ?>"
							style="width:100%;height:140px;object-fit:cover;border-radius:6px;display:block;border:1px solid #e5e7eb;" alt="">
					<?php else : ?>
						<div style="width:100%;height:140px;background:linear-gradient(135deg,#1d4ed8,#2d5a44);border-radius:6px;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.45);font-size:13px;">
							<?php esc_html_e( 'No cover image', ADN_TEXT_DOMAIN ); ?>
						</div>
					<?php endif; ?>
				</div>
				<div style="display:flex;flex-direction:column;gap:8px;padding-top:4px;">
					<input type="hidden" id="banner_image_id" name="banner_image_id"
						value="<?php echo esc_attr( (string) $f_banner_img_id ); ?>">
					<button type="button" class="button expert-banner-img-select"
						data-target="banner_image_id" data-preview="expert_banner_img_prev">
						<?php $f_banner_img_url ? esc_html_e( 'Change Cover Image', ADN_TEXT_DOMAIN ) : esc_html_e( 'Upload Cover Image', ADN_TEXT_DOMAIN ); ?>
					</button>
					<?php if ( $f_banner_img_url ) : ?>
					<button type="button" class="button expert-banner-img-remove"
						data-target="banner_image_id" data-preview="expert_banner_img_prev"
						data-empty-label="<?php esc_attr_e( 'No cover image', ADN_TEXT_DOMAIN ); ?>">
						<?php esc_html_e( 'Remove', ADN_TEXT_DOMAIN ); ?>
					</button>
					<?php endif; ?>
					<p class="description" style="margin:0;"><?php esc_html_e( 'JPEG / PNG, landscape orientation.', ADN_TEXT_DOMAIN ); ?></p>
				</div>
			</div>
		</div>

		<?php /* ── Scrolling Stats Marquee ─────────────────────────────── */ ?>
		<div class="card" style="max-width:none;background:#fafafa;margin-bottom:24px;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'Profile Stats Marquee', ADN_TEXT_DOMAIN ); ?></h3>
			<p class="description" style="margin-bottom:16px;">
				<?php esc_html_e( 'Items that scroll below the cover image (same marquee component as the home page). Each item: icon · value (bold, e.g. "15+") · label (subtitle, e.g. "Years Experience"). Leave empty to hide.', ADN_TEXT_DOMAIN ); ?>
			</p>

			<div style="display:flex;gap:6px;margin-bottom:8px;font-size:0.8rem;font-weight:600;color:#6b7280;padding:0 2px;">
				<span style="width:56px;"><?php esc_html_e( 'Icon', ADN_TEXT_DOMAIN ); ?></span>
				<span style="width:100px;"><?php esc_html_e( 'Value (bold)', ADN_TEXT_DOMAIN ); ?></span>
				<span style="flex:1;"><?php esc_html_e( 'Label (subtitle)', ADN_TEXT_DOMAIN ); ?></span>
				<span style="width:76px;"></span>
			</div>

			<div id="expert-banner-rows">
				<?php foreach ( $f_banner_arr as $_bi_idx => $_bi ) :
					$_bi_icon  = isset( $_bi['icon'] )  ? (string) $_bi['icon']  : '';
					$_bi_value = isset( $_bi['value'] ) ? (string) $_bi['value'] : '';
					$_bi_label = isset( $_bi['label'] ) ? (string) $_bi['label'] : '';
				?>
				<div class="expert-banner-row" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
					<input type="text" name="expert_banner_items[<?php echo (int) $_bi_idx; ?>][icon]"
						placeholder="icon" maxlength="4" class="small-text"
						value="<?php echo esc_attr( $_bi_icon ); ?>" style="width:50px;">
					<input type="text" name="expert_banner_items[<?php echo (int) $_bi_idx; ?>][value]"
						placeholder="15+" class="small-text"
						value="<?php echo esc_attr( $_bi_value ); ?>" style="width:90px;">
					<input type="text" name="expert_banner_items[<?php echo (int) $_bi_idx; ?>][label]"
						placeholder="<?php esc_attr_e( 'Years Experience', ADN_TEXT_DOMAIN ); ?>" class="regular-text"
						value="<?php echo esc_attr( $_bi_label ); ?>" style="flex:1;">
					<button type="button" class="button button-small expert-banner-remove"><?php esc_html_e( 'Remove', ADN_TEXT_DOMAIN ); ?></button>
				</div>
				<?php endforeach; ?>
			</div>

			<button type="button" id="expert-banner-add" class="button" style="margin-top:4px;">
				+ <?php esc_html_e( 'Add Stat', ADN_TEXT_DOMAIN ); ?>
			</button>

			<?php if ( ! empty( $f_banner_arr ) ) : ?>
			<div style="margin-top:14px;background:#1d4ed8;border-radius:6px;padding:10px 16px;display:flex;flex-wrap:wrap;gap:0;overflow:hidden;">
				<p style="font-size:0.75rem;color:rgba(255,255,255,.6);font-weight:600;width:100%;margin:0 0 6px;text-transform:uppercase;letter-spacing:.05em;">
					<?php esc_html_e( 'Marquee preview', ADN_TEXT_DOMAIN ); ?>
				</p>
				<div style="display:flex;flex-wrap:wrap;gap:0;width:100%;">
				<?php foreach ( $f_banner_arr as $_bp ) :
					$_bpv = isset( $_bp['value'] ) ? (string) $_bp['value'] : '';
					$_bpl = isset( $_bp['label'] ) ? (string) $_bp['label'] : '';
					if ( '' === $_bpv && '' === $_bpl ) { continue; }
				?>
				<div style="display:flex;align-items:center;gap:8px;padding:6px 18px 6px 0;border-right:1px solid rgba(255,255,255,.15);margin-right:18px;">
					<?php if ( ! empty( $_bp['icon'] ) ) : ?>
						<span style="font-size:1.2rem;"><?php echo esc_html( $_bp['icon'] ); ?></span>
					<?php endif; ?>
					<div>
						<strong style="font-size:0.95rem;color:#fff;display:block;line-height:1.1;"><?php echo esc_html( $_bpv ); ?></strong>
						<span style="font-size:0.72rem;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.04em;"><?php echo esc_html( $_bpl ); ?></span>
					</div>
				</div>
				<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
		</div>

		<?php /* ── Client Work ───────────────────────────────────────────── */ ?>
		<div class="card" style="max-width:none;background:#fafafa;margin-bottom:24px;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'Client Work Images', ADN_TEXT_DOMAIN ); ?></h3>
			<p class="description" style="margin-bottom:16px;">
				<?php esc_html_e( 'Add images from past client work. Each entry has an image and an optional caption. Shown as a grid on the profile page.', ADN_TEXT_DOMAIN ); ?>
			</p>

			<input type="hidden" name="client_images_json" id="client_images_json" value="<?php echo esc_attr( $f_ci_json ); ?>">

			<div id="ci_rows">
				<?php foreach ( $f_ci_arr as $_ci_idx => $_ci ) :
					$_ci_img_id  = isset( $_ci['image_id'] ) ? (int) $_ci['image_id'] : 0;
					$_ci_caption = isset( $_ci['caption'] )  ? (string) $_ci['caption'] : '';
					$_ci_img_url = $_ci_img_id > 0 ? wp_get_attachment_image_url( $_ci_img_id, 'thumbnail' ) : false;
				?>
					<div class="ci-row" data-index="<?php echo esc_attr( (string) $_ci_idx ); ?>"
						style="display:flex;gap:12px;align-items:center;margin-bottom:10px;padding:10px;background:#fff;border:1px solid #e5e7eb;border-radius:6px;">
						<div class="ci-img-prev" style="width:60px;height:60px;flex-shrink:0;">
							<?php if ( $_ci_img_url ) : ?>
								<img src="<?php echo esc_url( $_ci_img_url ); ?>"
									style="width:60px;height:60px;object-fit:cover;border-radius:4px;display:block;" alt="">
							<?php else : ?>
								<div style="width:60px;height:60px;background:#f3f4f6;border:1px dashed #d1d5db;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:11px;color:#9ca3af;"><?php esc_html_e( 'No img', ADN_TEXT_DOMAIN ); ?></div>
							<?php endif; ?>
						</div>
						<div style="flex:1;min-width:0;">
							<input type="hidden" class="ci-image-id" value="<?php echo esc_attr( (string) $_ci_img_id ); ?>">
							<button type="button" class="button button-small ci-select-img">
								<?php esc_html_e( 'Select Image', ADN_TEXT_DOMAIN ); ?>
							</button>
							<input type="text" class="ci-caption regular-text" style="margin-top:6px;display:block;"
								value="<?php echo esc_attr( $_ci_caption ); ?>"
								placeholder="<?php esc_attr_e( 'Caption (optional)', ADN_TEXT_DOMAIN ); ?>">
						</div>
						<button type="button" class="button button-small ci-remove-row" style="color:#b91c1c;border-color:#b91c1c;flex-shrink:0;">
							<?php esc_html_e( 'Remove', ADN_TEXT_DOMAIN ); ?>
						</button>
					</div>
				<?php endforeach; ?>
			</div>

			<button type="button" class="button" id="ci_add_row" style="margin-top:4px;">
				<?php esc_html_e( '+ Add Image', ADN_TEXT_DOMAIN ); ?>
			</button>
		</div>

		<?php /* ── Mega HTML ─────────────────────────────────────────────── */ ?>
		<div style="margin-bottom:28px;">
			<h3 style="margin-bottom:6px;"><?php esc_html_e( 'Custom Profile HTML (Mega HTML)', ADN_TEXT_DOMAIN ); ?></h3>
			<p class="description" style="margin-bottom:10px;">
				<?php esc_html_e( 'Optional full custom HTML injected into the profile page body. Rendered as-is (admin-trusted). Leave blank to use the standard layout.', ADN_TEXT_DOMAIN ); ?>
			</p>
			<textarea name="mega_html" rows="16"
				style="width:100%;font-family:'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace;font-size:13px;line-height:1.6;background:#1e1e1e;color:#d4d4d4;padding:14px 16px;border-radius:4px;border:1px solid #3c3c3c;tab-size:4;resize:vertical;"
				placeholder="<!-- Optional custom HTML section -->"><?php echo esc_textarea( $f_mega ); ?></textarea>
		</div>

		<?php /* ── Actions ───────────────────────────────────────────────── */ ?>
		<div style="display:flex;gap:12px;align-items:center;padding-top:8px;">
			<?php submit_button(
				$is_edit
					? __( 'Update Expert', ADN_TEXT_DOMAIN )
					: __( 'Save Expert', ADN_TEXT_DOMAIN ),
				'primary', 'submit', false
			); ?>
			<?php if ( $is_edit ) : ?>
				<a href="<?php echo esc_url( ADN_Theme_Admin::tab_url( 'experts', 'new' ) ); ?>" class="button">
					<?php esc_html_e( '+ Add Another', ADN_TEXT_DOMAIN ); ?>
				</a>
			<?php endif; ?>
			<a href="<?php echo esc_url( ADN_Theme_Admin::tab_url( 'experts', 'list' ) ); ?>" class="button">
				<?php esc_html_e( '← Back to List', ADN_TEXT_DOMAIN ); ?>
			</a>
		</div>

	</form>
</div>

<?php /* ── Media uploader + repeater JS ─────────────────────────────────── */ ?>
<script>
(function ($) {
	'use strict';

	/* ── Cover banner image uploader ── */
	var bannerImgFrame;
	$(document).on('click', '.expert-banner-img-select', function (e) {
		e.preventDefault();
		var $btn   = $(this);
		var tgt    = $btn.data('target');
		var prevId = $btn.data('preview');
		bannerImgFrame = wp.media({
			title:    '<?php echo esc_js( __( 'Select Cover Banner Image', ADN_TEXT_DOMAIN ) ); ?>',
			button:   { text: '<?php echo esc_js( __( 'Use as cover', ADN_TEXT_DOMAIN ) ); ?>' },
			multiple: false,
			library:  { type: 'image' }
		});
		bannerImgFrame.on('select', function () {
			var att = bannerImgFrame.state().get('selection').first().toJSON();
			$('#' + tgt).val(att.id);
			var src = (att.sizes && att.sizes.large) ? att.sizes.large.url : att.url;
			$('#' + prevId).html('<img src="' + src + '" style="width:100%;height:140px;object-fit:cover;border-radius:6px;display:block;border:1px solid #e5e7eb;" alt="">');
			$btn.text('<?php echo esc_js( __( 'Change Cover Image', ADN_TEXT_DOMAIN ) ); ?>');
			$btn.next('.expert-banner-img-remove').show();
		});
		bannerImgFrame.open();
	});
	$(document).on('click', '.expert-banner-img-remove', function (e) {
		e.preventDefault();
		var tgt       = $(this).data('target');
		var prevId    = $(this).data('preview');
		var emptyLbl  = $(this).data('empty-label') || '<?php echo esc_js( __( 'No cover image', ADN_TEXT_DOMAIN ) ); ?>';
		$('#' + tgt).val('0');
		$('#' + prevId).html('<div style="width:100%;height:140px;background:linear-gradient(135deg,#1d4ed8,#2d5a44);border-radius:6px;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.45);font-size:13px;">' + emptyLbl + '</div>');
		$(this).prev('.expert-banner-img-select').text('<?php echo esc_js( __( 'Upload Cover Image', ADN_TEXT_DOMAIN ) ); ?>');
		$(this).hide();
	});

	/* ── Profile photo uploader ── */
	var photoFrame;
	$(document).on('click', '.expert-photo-select', function (e) {
		e.preventDefault();
		var $btn   = $(this);
		var tgt    = $btn.data('target');
		var prevId = $btn.data('preview');
		photoFrame = wp.media({
			title:    '<?php echo esc_js( __( 'Select Profile Photo', ADN_TEXT_DOMAIN ) ); ?>',
			button:   { text: '<?php echo esc_js( __( 'Use this photo', ADN_TEXT_DOMAIN ) ); ?>' },
			multiple: false,
			library:  { type: 'image' }
		});
		photoFrame.on('select', function () {
			var att = photoFrame.state().get('selection').first().toJSON();
			$('#' + tgt).val(att.id);
			var src = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
			$('#' + prevId).html('<img src="' + src + '" style="width:80px;height:80px;object-fit:cover;border-radius:50%;display:block;" alt="">');
			$btn.next('.expert-photo-remove').show();
		});
		photoFrame.open();
	});
	$(document).on('click', '.expert-photo-remove', function (e) {
		e.preventDefault();
		var tgt    = $(this).data('target');
		var prevId = $(this).data('preview');
		$('#' + tgt).val('0');
		$('#' + prevId).html('<div style="width:80px;height:80px;background:linear-gradient(135deg,#e8f0ec,#d4e6d8);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;">&#128100;</div>');
		$(this).hide();
	});

	/* ── Client images repeater ── */
	var ciIndex  = <?php echo (int) count( $f_ci_arr ); ?>;
	var ciFrames = {};

	function ciRowHtml(idx) {
		return '<div class="ci-row" data-index="' + idx + '" style="display:flex;gap:12px;align-items:center;margin-bottom:10px;padding:10px;background:#fff;border:1px solid #e5e7eb;border-radius:6px;">'
			+ '<div class="ci-img-prev" style="width:60px;height:60px;flex-shrink:0;">'
			+   '<div style="width:60px;height:60px;background:#f3f4f6;border:1px dashed #d1d5db;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:11px;color:#9ca3af;"><?php echo esc_js( __( 'No img', ADN_TEXT_DOMAIN ) ); ?></div>'
			+ '</div>'
			+ '<div style="flex:1;min-width:0;">'
			+   '<input type="hidden" class="ci-image-id" value="0">'
			+   '<button type="button" class="button button-small ci-select-img"><?php echo esc_js( __( 'Select Image', ADN_TEXT_DOMAIN ) ); ?></button>'
			+   '<input type="text" class="ci-caption regular-text" style="margin-top:6px;display:block;" value="" placeholder="<?php echo esc_js( __( 'Caption (optional)', ADN_TEXT_DOMAIN ) ); ?>">'
			+ '</div>'
			+ '<button type="button" class="button button-small ci-remove-row" style="color:#b91c1c;border-color:#b91c1c;flex-shrink:0;"><?php echo esc_js( __( 'Remove', ADN_TEXT_DOMAIN ) ); ?></button>'
			+ '</div>';
	}

	$('#ci_add_row').on('click', function () {
		$('#ci_rows').append(ciRowHtml(ciIndex));
		ciIndex++;
	});

	$(document).on('click', '.ci-remove-row', function () {
		$(this).closest('.ci-row').remove();
	});

	$(document).on('click', '.ci-select-img', function (e) {
		e.preventDefault();
		var $row = $(this).closest('.ci-row');
		var idx  = $row.data('index');
		if (ciFrames[idx]) { ciFrames[idx].off('select'); }
		ciFrames[idx] = wp.media({
			title:    '<?php echo esc_js( __( 'Select Client Work Image', ADN_TEXT_DOMAIN ) ); ?>',
			button:   { text: '<?php echo esc_js( __( 'Use this image', ADN_TEXT_DOMAIN ) ); ?>' },
			multiple: false,
			library:  { type: 'image' }
		});
		ciFrames[idx].on('select', function () {
			var att = ciFrames[idx].state().get('selection').first().toJSON();
			$row.find('.ci-image-id').val(att.id);
			var src = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
			$row.find('.ci-img-prev').html('<img src="' + src + '" style="width:60px;height:60px;object-fit:cover;border-radius:4px;display:block;" alt="">');
		});
		ciFrames[idx].open();
	});

	/* ── Serialise client images to JSON on submit ── */
	$('#expertForm').on('submit', function () {
		var items = [];
		$('#ci_rows .ci-row').each(function () {
			var imgId   = parseInt($(this).find('.ci-image-id').val(), 10) || 0;
			var caption = $(this).find('.ci-caption').val() || '';
			items.push({ image_id: imgId, caption: caption });
		});
		$('#client_images_json').val(JSON.stringify(items));
	});

})(jQuery);
</script>

<script>
(function () {
	var bannerIdx  = <?php echo (int) count( $f_banner_arr ); ?>;
	var bannerWrap = document.getElementById( 'expert-banner-rows' );

	function bindBannerRemove( row ) {
		row.querySelector( '.expert-banner-remove' ).addEventListener( 'click', function () {
			row.remove();
		} );
	}

	document.querySelectorAll( '.expert-banner-row' ).forEach( bindBannerRemove );

	var addBtn = document.getElementById( 'expert-banner-add' );
	if ( addBtn ) {
		addBtn.addEventListener( 'click', function () {
			var row = document.createElement( 'div' );
			row.className = 'expert-banner-row';
			row.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:8px;';
			row.innerHTML =
				'<input type="text" name="expert_banner_items[' + bannerIdx + '][icon]" placeholder="icon" maxlength="4" class="small-text">' +
				'<input type="text" name="expert_banner_items[' + bannerIdx + '][value]" placeholder="15+" class="small-text">' +
				'<input type="text" name="expert_banner_items[' + bannerIdx + '][label]" placeholder="<?php echo esc_js( __( 'Years Experience', ADN_TEXT_DOMAIN ) ); ?>" class="regular-text">' +
				'<button type="button" class="button button-small expert-banner-remove"><?php echo esc_js( __( 'Remove', ADN_TEXT_DOMAIN ) ); ?></button>';
			bannerWrap.appendChild( row );
			bindBannerRemove( row );
			bannerIdx++;
		} );
	}
}());
</script>

