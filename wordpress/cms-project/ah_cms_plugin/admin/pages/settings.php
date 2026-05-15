<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model   = new AH_Settings_Model();
$notice  = '';
$n_type  = 'success';
$group   = sanitize_key( $_GET['group'] ?? 'general' );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_settings_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_settings_nonce'], 'ah_save_settings' ) ) {
		wp_die( 'Security check failed.' );
	}
	$posted = $_POST['settings'] ?? array();
	foreach ( $posted as $key => $val ) {
		$model->set_value( sanitize_key( $key ), sanitize_textarea_field( $val ) );
	}
	// Handle image fields (media IDs)
	$image_keys = $_POST['image_settings'] ?? array();
	foreach ( $image_keys as $key => $val ) {
		$model->set_value( sanitize_key( $key ), sanitize_text_field( $val ) );
	}
	$notice = 'Settings saved successfully.';
}

$groups  = $model->get_all_grouped();
$g_list  = array_keys( $groups );
if ( ! in_array( $group, $g_list, true ) ) $group = $g_list[0] ?? 'general';
$current = $groups[ $group ] ?? array();
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Site Settings', 'ah-theme' ); ?></h1>

  <?php if ( $notice ) : ?>
    <div class="ah-notice ah-notice-<?php echo esc_attr( $n_type ); ?>"><?php echo esc_html( $notice ); ?></div>
  <?php endif; ?>

  <!-- Group Tabs -->
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
      <?php foreach ( $current as $setting ) : ?>
        <div class="ah-form-row">
          <label for="setting_<?php echo esc_attr( $setting->setting_key ); ?>">
            <?php echo esc_html( $setting->label ?: $setting->setting_key ); ?>
          </label>

          <?php if ( $setting->field_type === 'textarea' ) : ?>
            <textarea id="setting_<?php echo esc_attr( $setting->setting_key ); ?>"
                      name="settings[<?php echo esc_attr( $setting->setting_key ); ?>]"
                      rows="4"><?php echo esc_textarea( $setting->setting_val ); ?></textarea>

          <?php elseif ( $setting->field_type === 'image' ) : ?>
            <div class="ah-image-picker">
              <?php $img_id = (int) $setting->setting_val; ?>
              <?php $img_url = $img_id ? ( new AH_Media_Model() )->get_url( $img_id ) : ''; ?>
              <img src="<?php echo esc_url( $img_url ); ?>"
                   class="ah-image-preview <?php echo $img_url ? 'visible' : ''; ?>"
                   alt="">
              <div class="ah-image-picker-btns">
                <input type="hidden" class="ah-image-id" name="image_settings[<?php echo esc_attr( $setting->setting_key ); ?>]" value="<?php echo esc_attr( $setting->setting_val ); ?>">
                <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Choose Image</button>
                <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
              </div>
            </div>

          <?php elseif ( $setting->field_type === 'color' ) : ?>
            <input type="text" id="setting_<?php echo esc_attr( $setting->setting_key ); ?>"
                   name="settings[<?php echo esc_attr( $setting->setting_key ); ?>]"
                   value="<?php echo esc_attr( $setting->setting_val ); ?>"
                   class="wp-color-picker-field">

          <?php elseif ( $setting->field_type === 'toggle' ) : ?>
            <select name="settings[<?php echo esc_attr( $setting->setting_key ); ?>]">
              <option value="1" <?php selected( $setting->setting_val, '1' ); ?>>Enabled</option>
              <option value="0" <?php selected( $setting->setting_val, '0' ); ?>>Disabled</option>
            </select>

          <?php else : ?>
            <input type="<?php echo in_array( $setting->field_type, array( 'email', 'url', 'phone' ), true ) ? esc_attr( $setting->field_type ) : 'text'; ?>"
                   id="setting_<?php echo esc_attr( $setting->setting_key ); ?>"
                   name="settings[<?php echo esc_attr( $setting->setting_key ); ?>]"
                   value="<?php echo esc_attr( $setting->setting_val ); ?>">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <button type="submit" class="ah-btn ah-btn-primary">
        <span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'Save Settings', 'ah-theme' ); ?>
      </button>
    </form>
  </div>
</div>
