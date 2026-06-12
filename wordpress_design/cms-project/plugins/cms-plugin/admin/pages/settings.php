<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model  = new AH_Settings_Model();
$notice = '';
$n_type = 'success';
$group  = sanitize_key( $_GET['group'] ?? 'general' );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_settings_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_settings_nonce'], 'ah_save_settings' ) ) wp_die( 'Security check failed.' );

	if ( isset( $_POST['delete_setting_key'] ) ) {
		global $wpdb;
		$del_key = sanitize_key( $_POST['delete_setting_key'] );
		$wpdb->delete( $wpdb->prefix . 'ah_site_settings', array( 'setting_key' => $del_key ) );
		$notice = 'Setting "' . esc_html( $del_key ) . '" deleted.';

	} elseif ( isset( $_POST['add_setting'] ) ) {
		$new_key   = sanitize_key( $_POST['new_key'] ?? '' );
		$new_label = sanitize_text_field( $_POST['new_label'] ?? '' );
		$new_group = sanitize_key( $_POST['new_group'] ?? 'general' );
		$new_type  = in_array( $_POST['new_type'] ?? 'text', array( 'text','textarea','image','color','url','email','phone','toggle','json' ), true ) ? $_POST['new_type'] : 'text';
		$new_val   = sanitize_textarea_field( $_POST['new_val'] ?? '' );
		if ( $new_key ) {
			global $wpdb;
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$wpdb->prefix}ah_site_settings` WHERE setting_key = %s", $new_key ) );
			if ( $exists ) {
				$notice = 'A setting with key "' . esc_html( $new_key ) . '" already exists.'; $n_type = 'error';
			} else {
				$wpdb->insert( $wpdb->prefix . 'ah_site_settings', array( 'setting_key' => $new_key, 'setting_val' => $new_val, 'field_type' => $new_type, 'group_name' => $new_group, 'label' => $new_label ?: $new_key ) );
				$notice = 'Setting "' . esc_html( $new_key ) . '" added.'; $group = $new_group;
			}
		} else { $notice = 'Setting key is required.'; $n_type = 'error'; }

	} else {
		foreach ( $_POST['settings'] ?? array() as $key => $val ) {
			$model->set_value( sanitize_key( $key ), sanitize_textarea_field( $val ) );
		}
		foreach ( $_POST['image_settings'] ?? array() as $key => $val ) {
			$model->set_value( sanitize_key( $key ), sanitize_text_field( $val ) );
		}
		$notice = 'Settings saved successfully.';
	}
}

$groups  = $model->get_all_grouped();
$hidden_groups = array( 'design', 'notifications' );
$g_list  = array_values( array_diff( array_keys( $groups ), $hidden_groups ) );
if ( ! in_array( $group, $g_list, true ) ) $group = $g_list[0] ?? 'general';
$current     = $groups[ $group ] ?? array();
$all_groups  = array_unique( array_merge( $g_list, array( 'general','contact','social','seo' ) ) );
sort( $all_groups );

if ( ! function_exists( 'ah_settings_image_url' ) ) {
	function ah_settings_image_url( string $value ): string {
		if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
			return esc_url( $value );
		}

		$img_id = absint( $value );
		if ( ! $img_id ) {
			return '';
		}

		$url = wp_get_attachment_image_url( $img_id, 'medium' );
		if ( $url ) {
			return esc_url( $url );
		}

		return class_exists( 'AH_Media_Model' ) ? ( new AH_Media_Model() )->get_url( $img_id ) : '';
	}
}
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Site Settings', 'ah-theme' ); ?></h1>

  <?php if ( $notice ) : ?>
    <?php echo ah_form_set_highlighted( $notice, $n_type ); ?>
  <?php endif; ?>

  <div class="ah-tabs">
    <?php foreach ( $g_list as $g ) : ?>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-settings', 'group' => $g ), admin_url( 'admin.php' ) ) ); ?>"
         class="ah-tab <?php echo $g === $group ? 'active' : ''; ?>">
        <?php echo esc_html( ucfirst( $g ) ); ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="ah-card">
    <form method="post">
      <?php wp_nonce_field( 'ah_save_settings', 'ah_settings_nonce' ); ?>
      <?php if ( empty( $current ) ) : ?>
        <p style="color:var(--ah-muted);margin:0;">No settings in this group yet. Add one below.</p>
      <?php endif; ?>
      <?php foreach ( $current as $setting ) :
        $sk  = $setting->setting_key ?? '';
        $val = $setting->setting_val ?? '';
        $ft  = $setting->field_type  ?? 'text';
      ?>
        <div class="ah-form-row" style="position:relative;">
          <label for="setting_<?php echo esc_attr( $sk ); ?>">
            <?php echo esc_html( $setting->label ?: $sk ); ?>
            <small style="color:var(--ah-muted);font-weight:400;">(<?php echo esc_html( $sk ); ?>)</small>
          </label>
          <?php if ( $ft === 'textarea' ) : ?>
            <textarea id="setting_<?php echo esc_attr( $sk ); ?>" name="settings[<?php echo esc_attr( $sk ); ?>]" rows="4"><?php echo esc_textarea( $val ); ?></textarea>
          <?php elseif ( $ft === 'image' ) : ?>
            <div class="ah-image-picker">
              <?php $img_url = ah_settings_image_url( (string) $val ); ?>
              <img src="<?php echo esc_url( $img_url ); ?>" class="ah-image-preview <?php echo $img_url ? 'visible' : ''; ?>" alt="">
              <div class="ah-image-picker-btns">
                <input type="hidden" id="setting_<?php echo esc_attr( $sk ); ?>" class="ah-image-id" name="image_settings[<?php echo esc_attr( $sk ); ?>]" value="<?php echo esc_attr( $val ); ?>">
                <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Choose Image</button>
                <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
              </div>
            </div>
          <?php elseif ( $ft === 'color' ) : ?>
            <input type="text" id="setting_<?php echo esc_attr( $sk ); ?>" name="settings[<?php echo esc_attr( $sk ); ?>]" value="<?php echo esc_attr( $val ); ?>" class="wp-color-picker-field">
          <?php elseif ( $ft === 'toggle' ) : ?>
            <select id="setting_<?php echo esc_attr( $sk ); ?>" name="settings[<?php echo esc_attr( $sk ); ?>]">
              <option value="1" <?php selected( $val, '1' ); ?>>Enabled</option>
              <option value="0" <?php selected( $val, '0' ); ?>>Disabled</option>
            </select>
          <?php else :
            $it = in_array( $ft, array( 'email','url' ), true ) ? $ft : ( $ft === 'phone' ? 'tel' : 'text' );
          ?>
            <input type="<?php echo esc_attr( $it ); ?>" id="setting_<?php echo esc_attr( $sk ); ?>" name="settings[<?php echo esc_attr( $sk ); ?>]" value="<?php echo esc_attr( $val ); ?>">
          <?php endif; ?>
          <button type="submit" form="delete_setting_<?php echo esc_attr( $sk ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" title="Delete setting" style="position:absolute;top:0;right:0;" onclick="return confirm('Delete setting &quot;<?php echo esc_js( $sk ); ?>&quot;?');">
            <span class="dashicons dashicons-trash" style="font-size:14px;width:14px;height:14px;margin:0;"></span>
          </button>
        </div>
      <?php endforeach; ?>
      <?php if ( ! empty( $current ) ) : ?>
        <button type="submit" class="ah-btn ah-btn-primary">
          <span class="dashicons dashicons-saved"></span> Save Settings
        </button>
      <?php endif; ?>
    </form>
    <?php foreach ( $current as $setting ) :
      $sk = $setting->setting_key ?? '';
    ?>
      <form id="delete_setting_<?php echo esc_attr( $sk ); ?>" method="post" style="display:none;">
        <?php wp_nonce_field( 'ah_save_settings', 'ah_settings_nonce' ); ?>
        <input type="hidden" name="delete_setting_key" value="<?php echo esc_attr( $sk ); ?>">
      </form>
    <?php endforeach; ?>
  </div>

  <div class="ah-card" style="margin-top:20px;border-top:3px solid var(--ah-primary);">
    <div class="ah-card-header"><h2>Add New Setting</h2></div>
    <form method="post">
      <?php wp_nonce_field( 'ah_save_settings', 'ah_settings_nonce' ); ?>
      <input type="hidden" name="add_setting" value="1">
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
        <div class="ah-form-row">
          <label>Key * <small style="font-weight:400;color:var(--ah-muted);">(e.g. site_tagline)</small></label>
          <input type="text" name="new_key" placeholder="setting_key" pattern="[a-z0-9_]+" required>
        </div>
        <div class="ah-form-row"><label>Label</label><input type="text" name="new_label" placeholder="Human-readable label"></div>
        <div class="ah-form-row">
          <label>Group</label>
          <select name="new_group">
            <?php foreach ( $all_groups as $ag ) : ?>
              <option value="<?php echo esc_attr( $ag ); ?>" <?php selected( $ag, $group ); ?>><?php echo esc_html( ucfirst( $ag ) ); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="ah-form-row">
          <label>Field Type</label>
          <select name="new_type">
            <?php foreach ( array( 'text','email','url','phone','textarea','color','image','toggle','json' ) as $ft ) : ?>
              <option value="<?php echo $ft; ?>"><?php echo ucfirst( $ft ); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="ah-form-row" style="grid-column:span 2;"><label>Default Value</label><input type="text" name="new_val" placeholder=""></div>
      </div>
      <button type="submit" class="ah-btn ah-btn-primary">
        <span class="dashicons dashicons-plus-alt"></span> Add Setting
      </button>
    </form>
  </div>
</div>
