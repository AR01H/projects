<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model  = new AH_Settings_Model();
$notice = '';
$n_type = 'success';
$group  = sanitize_key( $_GET['tab'] ?? $_GET['group'] ?? 'general' );

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

		// Try image thumbnail first (works for images/GIFs).
		$url = wp_get_attachment_image_url( $img_id, 'medium' );
		if ( $url ) {
			return esc_url( $url );
		}

		// Fall back to raw attachment URL (works for videos/audio).
		$url = wp_get_attachment_url( $img_id );
		if ( $url ) {
			return esc_url( $url );
		}

		return '';
	}
}
?>
<div class="wrap ah-wrap">
  <?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'admin-settings', 'Site Settings', 'Store site-wide key-value settings organised by group.' ); ?>

  <?php if ( $notice ) : ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::notice( $notice, $n_type ); ?>
  <?php endif; ?>

  <?php
  $settings_tabs = array();
  foreach ( $g_list as $g ) {
    $settings_tabs[ $g ] = ucfirst( $g );
  }
  \Ah\Cms\Admin\Components\AdminComponents::tabBarUrl( $settings_tabs, $group );
  ?>

  <?php ob_start(); ?>
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
            <?php \Ah\Cms\Admin\Components\AdminComponents::mediaField( 'image_settings[' . esc_attr( $sk ) . ']', '', $val, array( 'id' => 'setting_' . esc_attr( $sk ), 'type' => 'media' ) ); ?>
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
          <button type="submit" form="delete_setting_<?php echo esc_attr( $sk ); ?>" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-confirm="Delete setting &quot;<?php echo esc_js( $sk ); ?>&quot;?" title="Delete setting" style="position:absolute;top:0;right:0;">
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
  <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Site Settings', ob_get_clean() ); ?>

  <?php ob_start(); ?>
    <form method="post">
      <?php wp_nonce_field( 'ah_save_settings', 'ah_settings_nonce' ); ?>
      <input type="hidden" name="add_setting" value="1">
      <?php
      $group_select = '<select name="new_group">';
      foreach ( $all_groups as $ag ) {
        $group_select .= '<option value="' . esc_attr( $ag ) . '"' . selected( $ag, $group, false ) . '>' . esc_html( ucfirst( $ag ) ) . '</option>';
      }
      $group_select .= '</select>';
      ?>
      <?php
      $type_select = '<select name="new_type">';
      foreach ( array( 'text','email','url','phone','textarea','color','image','toggle','json' ) as $ft ) {
        $type_select .= '<option value="' . esc_attr( $ft ) . '">' . esc_html( ucfirst( $ft ) ) . '</option>';
      }
      $type_select .= '</select>';
      ?>
      <?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
        array( 'Key * <small style="font-weight:400;color:var(--ah-muted);">(e.g. site_tagline)</small>', '<input type="text" name="new_key" placeholder="setting_key" pattern="[a-z0-9_]+" required>' ),
        array( 'Label', '<input type="text" name="new_label" placeholder="Human-readable label">' ),
        array( 'Group', $group_select ),
        array( 'Field Type', $type_select ),
        array( 'Default Value', '<input type="text" name="new_val" placeholder="">' ),
      ) ); ?>
      <button type="submit" class="ah-btn ah-btn-primary">
        <span class="dashicons dashicons-plus-alt"></span> Add Setting
      </button>
    </form>
  <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Add New Setting', ob_get_clean() ); ?>
</div>
