<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model   = new AH_Home_Model();
$pages_m = new AH_Pages_Model();
$media_m = new AH_Media_Model();
$notice  = '';

$home_page = $pages_m->get_by_type( 'home' );
$page_id   = $home_page ? (int) $home_page->id : 0;
$tab       = sanitize_key( $_GET['tab'] ?? 'hero' );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce( $_POST['ah_home_nonce'] ?? '', 'ah_save_home' ) ) {

	$shared_updated_by = get_current_user_id() ?: null;

	if ( isset( $_POST['save_hero'] ) ) {
		$model->save_hero( $page_id, array(
			'badge_text'         => sanitize_text_field( $_POST['badge_text'] ?? '' ),
			'heading'            => sanitize_text_field( $_POST['heading'] ?? '' ),
			'subheading'         => sanitize_textarea_field( $_POST['subheading'] ?? '' ),
			'description'        => sanitize_textarea_field( $_POST['description'] ?? '' ),
			'cta_primary_text'   => sanitize_text_field( $_POST['cta_primary_text'] ?? '' ),
			'cta_primary_url'    => esc_url_raw( $_POST['cta_primary_url'] ?? '' ),
			'cta_secondary_text' => sanitize_text_field( $_POST['cta_secondary_text'] ?? '' ),
			'cta_secondary_url'  => esc_url_raw( $_POST['cta_secondary_url'] ?? '' ),
			'image_id'           => (int) ( $_POST['image_id'] ?? 0 ) ?: null,
			'is_visible'         => (int) ( $_POST['is_visible'] ?? 1 ),
			'updated_by'         => $shared_updated_by,
		) );
		$notice = 'Hero section saved.';
	}

	if ( isset( $_POST['save_why_us'] ) ) {
		$model->save_why_us( $page_id, array(
			'heading'        => sanitize_text_field( $_POST['heading'] ?? '' ),
			'description'    => sanitize_textarea_field( $_POST['description'] ?? '' ),
			'more_link_text' => sanitize_text_field( $_POST['more_link_text'] ?? '' ),
			'more_link_url'  => esc_url_raw( $_POST['more_link_url'] ?? '' ),
			'is_visible'     => (int) ( $_POST['is_visible'] ?? 1 ),
		) );
		$notice = 'Why Us section saved.';
	}

	if ( isset( $_POST['save_guide'] ) ) {
		$model->save_guide( $page_id, array(
			'heading'        => sanitize_text_field( $_POST['heading'] ?? '' ),
			'image_id'       => (int) ( $_POST['image_id'] ?? 0 ) ?: null,
			'description'    => sanitize_textarea_field( $_POST['description'] ?? '' ),
			'more_link_text' => sanitize_text_field( $_POST['more_link_text'] ?? '' ),
			'more_link_url'  => esc_url_raw( $_POST['more_link_url'] ?? '' ),
			'is_visible'     => (int) ( $_POST['is_visible'] ?? 1 ),
		) );
		$guide = $model->get_guide( $page_id );
		if ( $guide ) {
			$pts = array_filter( array_map( 'sanitize_text_field', $_POST['guide_points'] ?? array() ) );
			$t   = AH_DB_Helper::table( 'section_guide_through_points' );
			AH_DB_Helper::delete_where( $t, array( 'guide_id' => (int) $guide->id ) );
			foreach ( $pts as $i => $pt ) {
				AH_DB_Helper::insert( $t, array( 'guide_id' => (int) $guide->id, 'point_text' => $pt, 'sort_order' => $i ) );
			}
		}
		$notice = 'Guide Through saved.';
	}

	if ( isset( $_POST['save_difference'] ) ) {
		$model->save_difference( $page_id, array(
			'heading'        => sanitize_text_field( $_POST['heading'] ?? '' ),
			'information'    => sanitize_textarea_field( $_POST['information'] ?? '' ),
			'more_link_text' => sanitize_text_field( $_POST['more_link_text'] ?? '' ),
			'more_link_url'  => esc_url_raw( $_POST['more_link_url'] ?? '' ),
			'is_visible'     => (int) ( $_POST['is_visible'] ?? 1 ),
		) );
		$notice = 'Difference section saved.';
	}
}

// Fetch current data
$hero       = $page_id ? $model->get_hero( $page_id ) : null;
$why_us     = $page_id ? $model->get_why_us( $page_id ) : null;
$why_cards  = $why_us ? $model->get_why_us_cards( (int) $why_us->id ) : array();
$guide      = $page_id ? $model->get_guide( $page_id ) : null;
$guide_pts  = $guide ? $model->get_guide_points( (int) $guide->id ) : array();
$difference = $page_id ? $model->get_difference( $page_id ) : null;
$diff_rows  = $difference ? $model->get_difference_rows( (int) $difference->id ) : array();

$hero_img   = $hero && $hero->image_id ? ( wp_get_attachment_image_url( (int) $hero->image_id, 'large' ) ?: '' ) : '';
$guide_img  = $guide && $guide->image_id ? ( wp_get_attachment_image_url( (int) $guide->image_id, 'medium' ) ?: '' ) : '';

$sections = array(
	'hero'       => 'Hero Section',
	'why_us'     => 'Why You Need Us',
	'guide'      => 'Guide Through',
	'difference' => 'Difference From Others'
);
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-admin-home"></span> <?php esc_html_e( 'Home Page Sections', 'ah-theme' ); ?></h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>
  <?php if ( ! $page_id ) : ?><div class="ah-notice ah-notice-warning">Home page not found. Create it in Pages Manager first.</div><?php return; endif; ?>

  <!-- Section tabs -->
  <div class="ah-section-tabs">
    <?php foreach ( $sections as $k => $label ) : ?>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-home', 'tab' => $k ), admin_url( 'admin.php' ) ) ); ?>" class="ah-section-tab <?php echo $tab === $k ? 'active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
    <?php endforeach; ?>
  </div>

  <!-- ── Hero ── -->
  <?php if ( $tab === 'hero' ) : ?>
    <div class="ah-card">
      <div class="ah-card-header"><h2>Hero Section</h2></div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_home', 'ah_home_nonce' ); ?>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
          <div>
            <div class="ah-form-row"><label>Badge Text</label><input type="text" name="badge_text" value="<?php echo esc_attr( $hero->badge_text ?? '' ); ?>" placeholder="e.g. '#1 Trusted'"></div>
            <div class="ah-form-row"><label>Main Heading *</label><input type="text" name="heading" value="<?php echo esc_attr( $hero->heading ?? '' ); ?>" required></div>
            <div class="ah-form-row"><label>Sub-heading</label><textarea name="subheading" rows="3"><?php echo esc_textarea( $hero->subheading ?? '' ); ?></textarea></div>
            <div class="ah-form-row"><label>Description</label><textarea name="description" rows="4" placeholder="Optional longer description below the sub-heading"><?php echo esc_textarea( $hero->description ?? '' ); ?></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
              <div class="ah-form-row"><label>Primary CTA Text</label><input type="text" name="cta_primary_text" value="<?php echo esc_attr( $hero->cta_primary_text ?? '' ); ?>"></div>
              <div class="ah-form-row"><label>Primary CTA URL</label><input type="url" name="cta_primary_url" value="<?php echo esc_attr( $hero->cta_primary_url ?? '' ); ?>"></div>
              <div class="ah-form-row"><label>Secondary CTA Text</label><input type="text" name="cta_secondary_text" value="<?php echo esc_attr( $hero->cta_secondary_text ?? '' ); ?>"></div>
              <div class="ah-form-row"><label>Secondary CTA URL</label><input type="url" name="cta_secondary_url" value="<?php echo esc_attr( $hero->cta_secondary_url ?? '' ); ?>"></div>
            </div>
            <div class="ah-form-row"><label>Visible</label><select name="is_visible"><option value="1" <?php selected( $hero->is_visible ?? 1, 1 ); ?>>Yes</option><option value="0">No</option></select></div>
          </div>
          <div>
            <div class="ah-form-row">
              <label>Hero Image (Right side)</label>
              <div class="ah-image-picker">
                <img src="<?php echo esc_url( $hero_img ); ?>" class="ah-image-preview <?php echo $hero_img ? 'visible' : ''; ?>" alt="" style="width:100%;aspect-ratio:16/9;height:auto;object-fit:cover;">
                <div class="ah-image-picker-btns">
                  <input type="hidden" class="ah-image-id" name="image_id" value="<?php echo esc_attr( $hero->image_id ?? 0 ); ?>">
                  <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Choose Image</button>
                  <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <button type="submit" name="save_hero" value="1" class="ah-btn ah-btn-primary">Save Hero Section</button>
      </form>
    </div>

  <!-- ── Why Us ── -->
  <?php elseif ( $tab === 'why_us' ) : ?>
    <div class="ah-card">
      <div class="ah-card-header"><h2>Why You Need Us - Section Header</h2></div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_home', 'ah_home_nonce' ); ?>
        <div class="ah-form-row"><label>Heading</label><input type="text" name="heading" value="<?php echo esc_attr( $why_us->heading ?? '' ); ?>"></div>
        <div class="ah-form-row"><label>Description <small>(2 sentences)</small></label><textarea name="description" rows="4"><?php echo esc_textarea( $why_us->description ?? '' ); ?></textarea></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="ah-form-row"><label>More Link Text</label><input type="text" name="more_link_text" value="<?php echo esc_attr( $why_us->more_link_text ?? '' ); ?>"></div>
          <div class="ah-form-row"><label>More Link URL</label><input type="url" name="more_link_url" value="<?php echo esc_attr( $why_us->more_link_url ?? '' ); ?>"></div>
        </div>
        <div class="ah-form-row"><label>Visible</label><select name="is_visible"><option value="1" <?php selected( $why_us->is_visible ?? 1, 1 ); ?>>Yes</option><option value="0">No</option></select></div>
        <button type="submit" name="save_why_us" value="1" class="ah-btn ah-btn-primary">Save Why Us Header</button>
      </form>
    </div>
    <p style="color:var(--ah-muted);margin-top:16px;">Why Us cards are managed via AJAX below. Refresh to see changes.</p>

  <!-- ── Guide Through ── -->
  <?php elseif ( $tab === 'guide' ) : ?>
    <div class="ah-card">
      <div class="ah-card-header"><h2>Guide Through Section</h2></div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_home', 'ah_home_nonce' ); ?>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
          <div>
            <div class="ah-form-row"><label>Heading</label><input type="text" name="heading" value="<?php echo esc_attr( $guide->heading ?? '' ); ?>"></div>
            <div class="ah-form-row"><label>Description</label><textarea name="description" rows="4"><?php echo esc_textarea( $guide->description ?? '' ); ?></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
              <div class="ah-form-row"><label>More Link Text</label><input type="text" name="more_link_text" value="<?php echo esc_attr( $guide->more_link_text ?? '' ); ?>"></div>
              <div class="ah-form-row"><label>More Link URL</label><input type="url" name="more_link_url" value="<?php echo esc_attr( $guide->more_link_url ?? '' ); ?>"></div>
            </div>
            <div class="ah-form-row">
              <label>Bullet Points</label>
              <div class="ah-repeater-container">
                <?php $pts = $guide_pts ?: array( (object) array( 'point_text' => '' ) );
                foreach ( $pts as $pt ) : ?>
                  <div class="ah-repeater-item" style="display:flex;gap:8px;align-items:center;padding:8px;margin-bottom:6px;">
                    <span class="ah-sort-handle">&#9776;</span>
                    <input type="text" name="guide_points[]" value="<?php echo esc_attr( $pt->point_text ); ?>" style="flex:1;" placeholder="Bullet point">
                    <button type="button" class="ah-repeater-remove">✕</button>
                  </div>
                <?php endforeach; ?>
              </div>
              <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-repeater">+ Add Point</button>
            </div>
            <div class="ah-form-row"><label>Visible</label><select name="is_visible"><option value="1" <?php selected( $guide->is_visible ?? 1, 1 ); ?>>Yes</option><option value="0">No</option></select></div>
          </div>
          <div>
            <div class="ah-form-row">
              <label>Image (Left Side)</label>
              <div class="ah-image-picker">
                <img src="<?php echo esc_url( $guide_img ); ?>" class="ah-image-preview <?php echo $guide_img ? 'visible' : ''; ?>" alt="" style="width:100%;aspect-ratio:16/9;height:auto;object-fit:cover;">
                <div class="ah-image-picker-btns">
                  <input type="hidden" class="ah-image-id" name="image_id" value="<?php echo esc_attr( $guide->image_id ?? 0 ); ?>">
                  <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Choose Image</button>
                  <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <button type="submit" name="save_guide" value="1" class="ah-btn ah-btn-primary">Save Guide Through</button>
      </form>
    </div>

  <!-- ── Difference ── -->
  <?php elseif ( $tab === 'difference' ) : ?>
    <div class="ah-card">
      <div class="ah-card-header"><h2>Difference From Others - Header</h2></div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_home', 'ah_home_nonce' ); ?>
        <div class="ah-form-row"><label>Heading</label><input type="text" name="heading" value="<?php echo esc_attr( $difference->heading ?? '' ); ?>"></div>
        <div class="ah-form-row"><label>Information</label><textarea name="information" rows="4"><?php echo esc_textarea( $difference->information ?? '' ); ?></textarea></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="ah-form-row"><label>More Link Text</label><input type="text" name="more_link_text" value="<?php echo esc_attr( $difference->more_link_text ?? '' ); ?>"></div>
          <div class="ah-form-row"><label>More Link URL</label><input type="url" name="more_link_url" value="<?php echo esc_attr( $difference->more_link_url ?? '' ); ?>"></div>
        </div>
        <div class="ah-form-row"><label>Visible</label><select name="is_visible"><option value="1" <?php selected( $difference->is_visible ?? 1, 1 ); ?>>Yes</option><option value="0">No</option></select></div>
        <button type="submit" name="save_difference" value="1" class="ah-btn ah-btn-primary">Save</button>
      </form>
    </div>

    <!-- Comparison Table Rows -->
    <?php if ( $difference ) : ?>
    <div class="ah-card" style="margin-top:20px;">
      <div class="ah-card-header"><h2>Comparison Table</h2></div>
      <table class="ah-table">
        <thead><tr><th>Feature</th><th>Our Value</th><th>Competitors</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ( $diff_rows as $row ) : ?>
            <tr>
              <td><?php echo esc_html( $row->feature_label ); ?></td>
              <td><?php echo esc_html( $row->us_value ); ?></td>
              <td><?php echo esc_html( $row->others_value ); ?></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $row->status ); ?>"><?php echo esc_html( $row->status ); ?></span></td>
              <td>
                <button class="ah-btn ah-btn-danger ah-btn-sm ah-delete-item" data-id="<?php echo esc_attr( $row->id ); ?>" data-model="section_difference_table">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p style="margin-top:12px;color:var(--ah-muted);font-size:13px;">Use the AJAX API to add comparison rows, or manage via the AH_Home_Model directly.</p>
    </div>
    <?php endif; ?>

  <?php else : ?>
    <div class="ah-card">
      <p style="color:var(--ah-muted);">Select a section tab above to manage its content. All remaining sections (Highlights, Stack, Experience, Why Required, Featured Properties) follow the same pattern and are editable via their dedicated API endpoints or future tab expansion.</p>
    </div>
  <?php endif; ?>
</div>
