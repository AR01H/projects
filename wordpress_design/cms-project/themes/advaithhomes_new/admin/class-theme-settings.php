<?php
defined( 'ABSPATH' ) || exit;

/**
 * ADN_Theme_Settings
 *
 * Reusable settings engine. Given a schema id (see settings-schemas.php) it
 * renders a standard WP settings form and saves it through ONE shared
 * admin-post handler - so each settings tab view is just:
 *
 *     ADN_Theme_Settings::render( 'home_hero', 'home', 'hero' );
 *
 * Values are stored in a single wp_option per schema (schema['option']).
 */
class ADN_Theme_Settings {

	const ACTION = 'adn_save_settings';

	public static function init() {
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle_save' ) );
	}

	/** Saved values for a schema, or null when it has never been saved. */
	public static function raw( $group_id ) {
		$schemas = adn_settings_schemas();
		if ( ! isset( $schemas[ $group_id ] ) ) {
			return null;
		}
		$value = get_option( $schemas[ $group_id ]['option'], null );
		if ( null === $value ) {
			return null;
		}
		return is_array( $value ) ? $value : array();
	}

	/** Resolve a select/checklist field's options (array, or function name → array). */
	private static function options( $field ) {
		$options = isset( $field['options'] ) ? $field['options'] : array();
		if ( is_string( $options ) && function_exists( $options ) ) {
			$options = call_user_func( $options );
		}
		return is_array( $options ) ? $options : array();
	}

	/** Render the whole form for one schema group. */
	public static function render( $group_id, $tab, $subtab = '' ) {
		$schemas = adn_settings_schemas();
		if ( ! isset( $schemas[ $group_id ] ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Unknown settings group.', ADN_TEXT_DOMAIN ) . '</p></div>';
			return;
		}
		$schema = $schemas[ $group_id ];
		$saved  = self::raw( $group_id );
		?>
		<div class="card" style="max-width:none;">
			<h2><?php echo esc_html( $schema['title'] ); ?></h2>
			<?php if ( ! empty( $schema['intro'] ) ) : ?>
				<p class="description"><?php echo esc_html( $schema['intro'] ); ?></p>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
				<input type="hidden" name="group" value="<?php echo esc_attr( $group_id ); ?>">
				<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>">
				<input type="hidden" name="subtab" value="<?php echo esc_attr( $subtab ); ?>">
				<?php wp_nonce_field( self::ACTION . '_' . $group_id ); ?>

				<table class="form-table" role="presentation"><tbody>
					<?php
					foreach ( $schema['fields'] as $field ) :
						$value = self::field_value( $field, $saved );
						?>
						<tr>
							<th scope="row"><?php echo esc_html( $field['label'] ); ?></th>
							<td>
								<?php self::render_field( $field, $value ); ?>
								<?php if ( ! empty( $field['desc'] ) ) : ?>
									<p class="description"><?php echo esc_html( $field['desc'] ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody></table>

				<?php submit_button( __( 'Save Changes', ADN_TEXT_DOMAIN ) ); ?>
			</form>
		</div>
		<?php
	}

	/** Current value for a field: saved value, else its (per-type) default. */
	private static function field_value( $field, $saved ) {
		$key = $field['key'];
		if ( is_array( $saved ) && array_key_exists( $key, $saved ) ) {
			return $saved[ $key ];
		}
		if ( 'checklist' === $field['type'] ) {
			return ! empty( $field['default_all'] ) ? array_keys( self::options( $field ) ) : array();
		}
		if ( 'toggle' === $field['type'] ) {
			return isset( $field['default'] ) ? (int) $field['default'] : 0;
		}
		return isset( $field['default'] ) ? $field['default'] : '';
	}

	private static function render_field( $field, $value ) {
		$name = 'fields[' . $field['key'] . ']';

		switch ( $field['type'] ) {
			case 'textarea':
				printf(
					'<textarea name="%s" rows="3" class="large-text">%s</textarea>',
					esc_attr( $name ),
					esc_textarea( (string) $value )
				);
				break;

			case 'number':
				printf(
					'<input type="number" name="%s" value="%s" class="small-text"%s%s%s>',
					esc_attr( $name ),
					esc_attr( (string) $value ),
					isset( $field['min'] ) ? ' min="' . esc_attr( $field['min'] ) . '"' : '',
					isset( $field['max'] ) ? ' max="' . esc_attr( $field['max'] ) . '"' : '',
					isset( $field['step'] ) ? ' step="' . esc_attr( $field['step'] ) . '"' : ''
				);
				break;

			case 'toggle':
				printf(
					'<label><input type="checkbox" name="%s" value="1" %s> %s</label>',
					esc_attr( $name ),
					checked( ! empty( $value ), true, false ),
					esc_html__( 'Enabled', ADN_TEXT_DOMAIN )
				);
				break;

			case 'select':
				echo '<select name="' . esc_attr( $name ) . '">';
				foreach ( self::options( $field ) as $ov => $ol ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $ov ),
						selected( (string) $value, (string) $ov, false ),
						esc_html( $ol )
					);
				}
				echo '</select>';
				break;

			case 'checklist':
				$selected = is_array( $value ) ? array_map( 'strval', $value ) : array();
				$options  = self::options( $field );
				if ( empty( $options ) ) {
					echo '<p class="description">' . esc_html__( 'No options available yet.', ADN_TEXT_DOMAIN ) . '</p>';
					break;
				}
				echo '<fieldset>';
				foreach ( $options as $ov => $ol ) {
					printf(
						'<label style="display:block;margin:3px 0;"><input type="checkbox" name="%s[]" value="%s" %s> %s</label>',
						esc_attr( $name ),
						esc_attr( $ov ),
						checked( in_array( (string) $ov, $selected, true ), true, false ),
						esc_html( $ol )
					);
				}
				echo '</fieldset>';
				break;

			case 'image':
				wp_enqueue_media();
				$img_url = '';
				if ( $value ) {
					$img_url = function_exists( 'ah_settings_image_url' ) 
						? ah_settings_image_url( (string) $value ) 
						: ( filter_var( $value, FILTER_VALIDATE_URL ) ? $value : wp_get_attachment_image_url( $value, 'medium' ) );
				}
				?>
				<div class="adn-image-picker" style="display:flex;align-items:flex-start;gap:15px;margin-bottom:5px;">
					<div style="width:120px;height:120px;border:1px solid #ddd;background:#f0f0f1;display:flex;align-items:center;justify-content:center;overflow:hidden;">
						<img src="<?php echo esc_url( $img_url ); ?>" class="adn-image-preview" style="max-width:100%;max-height:100%;display:<?php echo $img_url ? 'block' : 'none'; ?>;" alt="">
					</div>
					<div>
						<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" class="adn-image-id">
						<button type="button" class="button adn-pick-image"><?php esc_html_e( 'Choose Image', ADN_TEXT_DOMAIN ); ?></button>
						<button type="button" class="button adn-remove-image" style="color:#b32d2e;display:<?php echo $img_url ? 'inline-block' : 'none'; ?>;"><?php esc_html_e( 'Remove', ADN_TEXT_DOMAIN ); ?></button>
					</div>
				</div>
				<script>
				jQuery(document).ready(function($){
					if(typeof wp==='undefined' || !wp.media) return;
					$('.adn-image-picker').each(function(){
						var $wrap = $(this);
						var $input = $wrap.find('.adn-image-id');
						var $preview = $wrap.find('.adn-image-preview');
						var $remove = $wrap.find('.adn-remove-image');
						var frame;
						$wrap.find('.adn-pick-image').on('click', function(e){
							e.preventDefault();
							if(frame){ frame.open(); return; }
							frame = wp.media({ title: 'Select Image', button: { text: 'Use this image' }, multiple: false });
							frame.on('select', function(){
								var attachment = frame.state().get('selection').first().toJSON();
								$input.val(attachment.id);
								var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
								$preview.attr('src', url).show();
								$remove.show();
							});
							frame.open();
						});
						$remove.on('click', function(e){
							e.preventDefault();
							$input.val('');
							$preview.attr('src', '').hide();
							$(this).hide();
						});
					});
				});
				</script>
				<?php
				break;

			case 'media':
				wp_enqueue_media();
				$resolved   = adn_settings_media_url_type( (string) $value );
				$media_url  = $resolved['url'];
				$media_type = $resolved['type'];
				?>
				<div class="adn-media-picker" style="display:flex;align-items:flex-start;gap:15px;margin-bottom:5px;">
					<div style="width:120px;height:120px;border:1px solid #ddd;background:#f0f0f1;display:flex;align-items:center;justify-content:center;overflow:hidden;">
						<img src="<?php echo esc_url( $media_url ); ?>" class="adn-media-preview-img" style="max-width:100%;max-height:100%;display:<?php echo ( $media_url && 'image' === $media_type ) ? 'block' : 'none'; ?>;" alt="">
						<video src="<?php echo esc_url( $media_url ); ?>" class="adn-media-preview-video" muted loop autoplay playsinline style="max-width:100%;max-height:100%;display:<?php echo ( $media_url && 'video' === $media_type ) ? 'block' : 'none'; ?>;"></video>
					</div>
					<div>
						<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" class="adn-media-id">
						<button type="button" class="button adn-pick-media"><?php esc_html_e( 'Choose Image / GIF / Video', ADN_TEXT_DOMAIN ); ?></button>
						<button type="button" class="button adn-remove-media" style="color:#b32d2e;display:<?php echo $media_url ? 'inline-block' : 'none'; ?>;"><?php esc_html_e( 'Remove', ADN_TEXT_DOMAIN ); ?></button>
					</div>
				</div>
				<script>
				jQuery(document).ready(function($){
					if(typeof wp==='undefined' || !wp.media) return;
					$('.adn-media-picker').each(function(){
						var $wrap = $(this);
						var $input = $wrap.find('.adn-media-id');
						var $previewImg = $wrap.find('.adn-media-preview-img');
						var $previewVideo = $wrap.find('.adn-media-preview-video');
						var $remove = $wrap.find('.adn-remove-media');
						var frame;
						$wrap.find('.adn-pick-media').on('click', function(e){
							e.preventDefault();
							if(frame){ frame.open(); return; }
							frame = wp.media({ title: 'Select Image, GIF or Video', button: { text: 'Use this media' }, multiple: false, library: { type: ['image','video'] } });
							frame.on('select', function(){
								var attachment = frame.state().get('selection').first().toJSON();
								$input.val(attachment.id);
								if (attachment.type === 'video') {
									$previewVideo.attr('src', attachment.url)[0].load();
									$previewVideo.show();
									$previewImg.hide();
								} else {
									var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
									$previewImg.attr('src', url).show();
									$previewVideo.hide();
								}
								$remove.show();
							});
							frame.open();
						});
						$remove.on('click', function(e){
							e.preventDefault();
							$input.val('');
							$previewImg.attr('src','').hide();
							$previewVideo.attr('src','').hide();
							$(this).hide();
						});
					});
				});
				</script>
				<?php
				break;

			case 'text':
			default:
				printf(
					'<input type="text" name="%s" value="%s" class="regular-text">',
					esc_attr( $name ),
					esc_attr( (string) $value )
				);
		}
	}

	/** Shared admin-post handler: validate, sanitize per field-type, store, redirect. */
	public static function handle_save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		$group   = isset( $_POST['group'] ) ? sanitize_key( wp_unslash( $_POST['group'] ) ) : '';
		check_admin_referer( self::ACTION . '_' . $group );

		$schemas = adn_settings_schemas();
		if ( ! isset( $schemas[ $group ] ) ) {
			wp_die( esc_html__( 'Unknown settings group.', ADN_TEXT_DOMAIN ) );
		}
		$schema = $schemas[ $group ];
		$raw    = isset( $_POST['fields'] ) && is_array( $_POST['fields'] ) ? wp_unslash( $_POST['fields'] ) : array();

		$clean = array();
		foreach ( $schema['fields'] as $field ) {
			$key   = $field['key'];
			$input = isset( $raw[ $key ] ) ? $raw[ $key ] : null;

			switch ( $field['type'] ) {
				case 'textarea':
					$clean[ $key ] = sanitize_textarea_field( (string) $input );
					break;

				case 'number':
					$n = is_numeric( $input ) ? $input + 0 : 0;
					if ( isset( $field['min'] ) ) {
						$n = max( $field['min'], $n );
					}
					if ( isset( $field['max'] ) ) {
						$n = min( $field['max'], $n );
					}
					$clean[ $key ] = $n;
					break;

				case 'toggle':
					$clean[ $key ] = empty( $input ) ? 0 : 1;
					break;

				case 'checklist':
					$allowed       = array_map( 'strval', array_keys( self::options( $field ) ) );
					$picked        = is_array( $input ) ? array_map( 'strval', $input ) : array();
					$clean[ $key ] = array_values( array_intersect( $allowed, $picked ) );
					break;

				case 'select':
					$allowed       = array_map( 'strval', array_keys( self::options( $field ) ) );
					$clean[ $key ] = in_array( (string) $input, $allowed, true ) ? (string) $input : '';
					break;

				case 'image':
				case 'media':
					// Value is a wp.media attachment ID or a direct URL - plain text either way.
					$clean[ $key ] = sanitize_text_field( (string) $input );
					break;

				case 'text':
				default:
					$clean[ $key ] = sanitize_text_field( (string) $input );
			}
		}

		update_option( $schema['option'], $clean );

		$tab    = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : 'dashboard';
		$subtab = isset( $_POST['subtab'] ) ? sanitize_key( wp_unslash( $_POST['subtab'] ) ) : '';
		$args   = array(
			'page'     => ADN_Theme_Admin::tab_page_slug( $tab ),
			'adn_done' => 1,
			'adn_msg'  => rawurlencode( __( 'Settings saved.', ADN_TEXT_DOMAIN ) ),
		);
		if ( $subtab ) {
			$args['subtab'] = $subtab;
		}
		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}
}
