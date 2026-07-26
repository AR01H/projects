<?php
/**
 * ThemeSettings - Reusable settings engine for the theme admin.
 *
 * Given a schema id (see SettingsSchemas.php) it renders a standard WP settings
 * form and saves it through ONE shared admin-post handler.
 */
namespace Adn\Theme\Feature\Admin;

defined( 'ABSPATH' ) || exit;

class ThemeSettings {

	public const ACTION = 'adn_save_settings';

	/** Register the admin-post handler. */
	public static function init(): void {
		\add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handleSave' ) );
	}

	/** Saved values for a schema, or null when it has never been saved. */
	public static function raw( string $group_id ): ?array {
		$schemas = \adn_settings_schemas();
		if ( ! isset( $schemas[ $group_id ] ) ) {
			return null;
		}
		$value = \get_option( $schemas[ $group_id ]['option'], null );
		if ( null === $value ) {
			return null;
		}
		return \is_array( $value ) ? $value : array();
	}

	/** Resolve a select/checklist field's options (array, or function name → array). */
	private static function options( array $field ): array {
		$options = $field['options'] ?? array();
		if ( \is_string( $options ) && \function_exists( $options ) ) {
			$options = \call_user_func( $options );
		}
		return \is_array( $options ) ? $options : array();
	}

	/** Render the whole form for one schema group. */
	public static function render( string $group_id, string $tab, string $subtab = '' ): void {
		$schemas = \adn_settings_schemas();
		if ( ! isset( $schemas[ $group_id ] ) ) {
			echo '<div class="notice notice-error"><p>' . \esc_html__( 'Unknown settings group.', ADN_TEXT_DOMAIN ) . '</p></div>';
			return;
		}
		$schema = $schemas[ $group_id ];
		$saved  = self::raw( $group_id );
		?>
		<div class="card" style="max-width:none;">
			<h2><?php echo \esc_html( $schema['title'] ); ?></h2>
			<?php if ( ! empty( $schema['intro'] ) ) : ?>
				<p class="description"><?php echo \esc_html( $schema['intro'] ); ?></p>
			<?php endif; ?>

			<form method="post" action="<?php echo \esc_url( \admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo \esc_attr( self::ACTION ); ?>">
				<input type="hidden" name="group" value="<?php echo \esc_attr( $group_id ); ?>">
				<input type="hidden" name="tab" value="<?php echo \esc_attr( $tab ); ?>">
				<input type="hidden" name="subtab" value="<?php echo \esc_attr( $subtab ); ?>">
				<?php \wp_nonce_field( self::ACTION . '_' . $group_id ); ?>

				<table class="form-table" role="presentation"><tbody>
					<?php
					foreach ( $schema['fields'] as $field ) :
						$value = self::fieldValue( $field, $saved );
						?>
						<tr>
							<th scope="row"><?php echo \esc_html( $field['label'] ); ?></th>
							<td>
								<?php self::renderField( $field, $value ); ?>
								<?php if ( ! empty( $field['desc'] ) ) : ?>
									<p class="description"><?php echo \esc_html( $field['desc'] ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody></table>

				<?php \submit_button( \__( 'Save Changes', ADN_TEXT_DOMAIN ) ); ?>
			</form>
		</div>
		<?php
	}

	/** Current value for a field: saved value, else its (per-type) default. */
	private static function fieldValue( array $field, ?array $saved ): mixed {
		$key = $field['key'];
		if ( \is_array( $saved ) && \array_key_exists( $key, $saved ) ) {
			return $saved[ $key ];
		}
		if ( 'checklist' === $field['type'] ) {
			return ! empty( $field['default_all'] ) ? \array_keys( self::options( $field ) ) : array();
		}
		if ( 'toggle' === $field['type'] ) {
			return isset( $field['default'] ) ? (int) $field['default'] : 0;
		}
		return $field['default'] ?? '';
	}

	/** Render a single field by type. */
	private static function renderField( array $field, mixed $value ): void {
		$name = 'fields[' . $field['key'] . ']';

		switch ( $field['type'] ) {
			case 'textarea':
				printf(
					'<textarea name="%s" rows="3" class="large-text">%s</textarea>',
					\esc_attr( $name ),
					\esc_textarea( (string) $value )
				);
				break;

			case 'number':
				printf(
					'<input type="number" name="%s" value="%s" class="small-text"%s%s%s>',
					\esc_attr( $name ),
					\esc_attr( (string) $value ),
					isset( $field['min'] ) ? ' min="' . \esc_attr( $field['min'] ) . '"' : '',
					isset( $field['max'] ) ? ' max="' . \esc_attr( $field['max'] ) . '"' : '',
					isset( $field['step'] ) ? ' step="' . \esc_attr( $field['step'] ) . '"' : ''
				);
				break;

			case 'toggle':
				printf(
					'<label><input type="checkbox" name="%s" value="1" %s> %s</label>',
					\esc_attr( $name ),
					\checked( ! empty( $value ), true, false ),
					\esc_html__( 'Enabled', ADN_TEXT_DOMAIN )
				);
				break;

			case 'select':
				echo '<select name="' . \esc_attr( $name ) . '">';
				foreach ( self::options( $field ) as $ov => $ol ) {
					printf(
						'<option value="%s" %s>%s</option>',
						\esc_attr( $ov ),
						\selected( (string) $value, (string) $ov, false ),
						\esc_html( $ol )
					);
				}
				echo '</select>';
				break;

			case 'checklist':
				$selected = \is_array( $value ) ? \array_map( 'strval', $value ) : array();
				$options  = self::options( $field );
				if ( empty( $options ) ) {
					echo '<p class="description">' . \esc_html__( 'No options available yet.', ADN_TEXT_DOMAIN ) . '</p>';
					break;
				}
				echo '<fieldset>';
				foreach ( $options as $ov => $ol ) {
					printf(
						'<label style="display:block;margin:3px 0;"><input type="checkbox" name="%s[]" value="%s" %s> %s</label>',
						\esc_attr( $name ),
						\esc_attr( $ov ),
						\checked( \in_array( (string) $ov, $selected, true ), true, false ),
						\esc_html( $ol )
					);
				}
				echo '</fieldset>';
				break;

			case 'image':
				\wp_enqueue_media();
				$img_url = '';
				if ( $value ) {
					$img_url = \function_exists( 'ah_settings_image_url' )
						? \ah_settings_image_url( (string) $value )
						: ( \filter_var( $value, FILTER_VALIDATE_URL ) ? $value : \wp_get_attachment_image_url( $value, 'medium' ) );
				}
				self::renderImagePicker( $name, $value, $img_url );
				break;

			case 'media':
				\wp_enqueue_media();
				$resolved   = \adn_settings_media_url_type( (string) $value );
				$media_url  = $resolved['url'];
				$media_type = $resolved['type'];
				self::renderMediaPicker( $name, $value, $media_url, $media_type );
				break;

			case 'repeater':
				\wp_enqueue_media();
				$items      = \is_array( $value ) ? $value : array();
				$subfields  = $field['subfields'] ?? array();
				$repeater_id = 'adn-repeater-' . \sanitize_key( $field['key'] );
				self::renderRepeater( $name, $items, $subfields, $repeater_id );
				break;

			case 'text':
			default:
				printf(
					'<input type="text" name="%s" value="%s" class="regular-text">',
					\esc_attr( $name ),
					\esc_attr( (string) $value )
				);
		}
	}

	/** Render image picker field. */
	private static function renderImagePicker( string $name, mixed $value, string $img_url ): void {
		?>
		<div class="adn-image-picker" style="display:flex;align-items:flex-start;gap:15px;margin-bottom:5px;">
			<div style="width:120px;height:120px;border:1px solid #ddd;background:#f0f0f1;display:flex;align-items:center;justify-content:center;overflow:hidden;">
				<img src="<?php echo \esc_url( $img_url ); ?>" class="adn-image-preview" style="max-width:100%;max-height:100%;display:<?php echo $img_url ? 'block' : 'none'; ?>;" alt="">
			</div>
			<div>
				<input type="hidden" name="<?php echo \esc_attr( $name ); ?>" value="<?php echo \esc_attr( (string) $value ); ?>" class="adn-image-id">
				<button type="button" class="button adn-pick-image"><?php \esc_html_e( 'Choose Image', ADN_TEXT_DOMAIN ); ?></button>
				<button type="button" class="button adn-remove-image" style="color:#b32d2e;display:<?php echo $img_url ? 'inline-block' : 'none'; ?>;"><?php \esc_html_e( 'Remove', ADN_TEXT_DOMAIN ); ?></button>
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
	}

	/** Render media picker field (image / GIF / video). */
	private static function renderMediaPicker( string $name, mixed $value, string $media_url, string $media_type ): void {
		?>
		<div class="adn-media-picker" style="display:flex;align-items:flex-start;gap:15px;margin-bottom:5px;">
			<div style="width:120px;height:120px;border:1px solid #ddd;background:#f0f0f1;display:flex;align-items:center;justify-content:center;overflow:hidden;">
				<img src="<?php echo \esc_url( $media_url ); ?>" class="adn-media-preview-img" style="max-width:100%;max-height:100%;display:<?php echo ( $media_url && 'image' === $media_type ) ? 'block' : 'none'; ?>;" alt="">
				<video src="<?php echo \esc_url( $media_url ); ?>" class="adn-media-preview-video" muted loop autoplay playsinline style="max-width:100%;max-height:100%;display:<?php echo ( $media_url && 'video' === $media_type ) ? 'block' : 'none'; ?>;"></video>
			</div>
			<div>
				<input type="hidden" name="<?php echo \esc_attr( $name ); ?>" value="<?php echo \esc_attr( (string) $value ); ?>" class="adn-media-id">
				<button type="button" class="button adn-pick-media"><?php \esc_html_e( 'Choose Image / GIF / Video', ADN_TEXT_DOMAIN ); ?></button>
				<button type="button" class="button adn-remove-media" style="color:#b32d2e;display:<?php echo $media_url ? 'inline-block' : 'none'; ?>;"><?php \esc_html_e( 'Remove', ADN_TEXT_DOMAIN ); ?></button>
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
	}

	/** Render repeater field. */
	private static function renderRepeater( string $name, array $items, array $subfields, string $repeater_id ): void {
		?>
		<div class="adn-repeater" id="<?php echo \esc_attr( $repeater_id ); ?>">
			<div class="adn-repeater-items">
				<?php if ( ! empty( $items ) ) : ?>
					<?php foreach ( $items as $_idx => $_item ) : ?>
						<div class="adn-repeater-item" style="border:1px solid #ddd;padding:12px;margin-bottom:10px;background:#fafafa;position:relative;">
							<button type="button" class="button button-small adn-repeater-remove" style="position:absolute;top:8px;right:8px;color:#b32d2e;" title="<?php \esc_attr_e( 'Remove', ADN_TEXT_DOMAIN ); ?>">&times;</button>
							<?php foreach ( $subfields as $_sf ) :
								$_sf_key   = $_sf['key'];
								$_sf_type  = $_sf['type'] ?? 'text';
								$_sf_label = $_sf['label'] ?? $_sf_key;
								$_sf_desc  = $_sf['desc'] ?? '';
								$_sf_val   = $_item[ $_sf_key ] ?? '';
								$_sf_name  = $name . '[' . $_idx . '][' . $_sf_key . ']';
								?>
								<div style="margin-bottom:8px;">
									<label style="display:block;font-weight:600;margin-bottom:3px;"><?php echo \esc_html( $_sf_label ); ?></label>
									<?php if ( 'media' === $_sf_type ) :
										$_resolved  = \adn_settings_media_url_type( (string) $_sf_val );
										$_media_url = $_resolved['url'];
										$_media_tp  = $_resolved['type'];
										?>
										<div class="adn-media-picker" style="display:flex;align-items:flex-start;gap:10px;">
											<div style="width:80px;height:80px;border:1px solid #ddd;background:#f0f0f1;display:flex;align-items:center;justify-content:center;overflow:hidden;">
												<img src="<?php echo \esc_url( $_media_url ); ?>" class="adn-media-preview-img" style="max-width:100%;max-height:100%;display:<?php echo ( $_media_url && 'image' === $_media_tp ) ? 'block' : 'none'; ?>;" alt="">
												<video src="<?php echo \esc_url( $_media_url ); ?>" class="adn-media-preview-video" muted loop autoplay playsinline style="max-width:100%;max-height:100%;display:<?php echo ( $_media_url && 'video' === $_media_tp ) ? 'block' : 'none'; ?>;"></video>
											</div>
											<div>
												<input type="hidden" name="<?php echo \esc_attr( $_sf_name ); ?>" value="<?php echo \esc_attr( (string) $_sf_val ); ?>" class="adn-media-id">
												<button type="button" class="button adn-pick-media small"><?php \esc_html_e( 'Choose', ADN_TEXT_DOMAIN ); ?></button>
												<button type="button" class="button adn-remove-media small" style="color:#b32d2e;display:<?php echo $_media_url ? 'inline-block' : 'none'; ?>;"><?php \esc_html_e( 'Remove', ADN_TEXT_DOMAIN ); ?></button>
											</div>
										</div>
									<?php else : ?>
										<input type="text" name="<?php echo \esc_attr( $_sf_name ); ?>" value="<?php echo \esc_attr( (string) $_sf_val ); ?>" class="regular-text">
									<?php endif; ?>
									<?php if ( '' !== $_sf_desc ) : ?>
										<p class="description" style="margin-top:3px;"><?php echo \esc_html( $_sf_desc ); ?></p>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<button type="button" class="button adn-repeater-add" style="margin-top:5px;">
				<i class="fa-solid fa-plus" aria-hidden="true"></i>
				<?php \esc_html_e( 'Add Banner', ADN_TEXT_DOMAIN ); ?>
			</button>
		</div>
		<script>
		(function(){
			var wrap = document.getElementById(<?php echo \wp_json_encode( $repeater_id ); ?>);
			if (!wrap) return;
			var itemsWrap = wrap.querySelector('.adn-repeater-items');
			var addBtn    = wrap.querySelector('.adn-repeater-add');
			var idx       = itemsWrap.querySelectorAll('.adn-repeater-item').length;
			var subfields = <?php echo \wp_json_encode( $subfields ); ?>;
			var fieldName = <?php echo \wp_json_encode( $name ); ?>;

			function bindRemove(btn) {
				btn.addEventListener('click', function(e) {
					e.preventDefault();
					btn.closest('.adn-repeater-item').remove();
				});
			}
			wrap.querySelectorAll('.adn-repeater-remove').forEach(bindRemove);

			addBtn.addEventListener('click', function(e) {
				e.preventDefault();
				var html = '<div class="adn-repeater-item" style="border:1px solid #ddd;padding:12px;margin-bottom:10px;background:#fafafa;position:relative;">' +
					'<button type="button" class="button button-small adn-repeater-remove" style="position:absolute;top:8px;right:8px;color:#b32d2e;" title="Remove">&times;</button>';
				subfields.forEach(function(sf) {
					var sfName = fieldName + '[' + idx + '][' + sf.key + ']';
					html += '<div style="margin-bottom:8px;">' +
						'<label style="display:block;font-weight:600;margin-bottom:3px;">' + (sf.label || sf.key) + '</label>';
					if (sf.type === 'media') {
						html += '<div class="adn-media-picker" style="display:flex;align-items:flex-start;gap:10px;">' +
							'<div style="width:80px;height:80px;border:1px solid #ddd;background:#f0f0f1;display:flex;align-items:center;justify-content:center;">' +
								'<img src="" class="adn-media-preview-img" style="max-width:100%;max-height:100%;display:none;" alt="">' +
								'<video src="" class="adn-media-preview-video" muted loop autoplay playsinline style="max-width:100%;max-height:100%;display:none;"></video>' +
							'</div>' +
							'<div>' +
								'<input type="hidden" name="' + sfName + '" value="" class="adn-media-id">' +
								'<button type="button" class="button adn-pick-media small">Choose</button>' +
								'<button type="button" class="button adn-remove-media small" style="color:#b32d2e;display:none;">Remove</button>' +
							'</div></div>';
					} else {
						html += '<input type="text" name="' + sfName + '" value="" class="regular-text">';
					}
					if (sf.desc) html += '<p class="description" style="margin-top:3px;">' + sf.desc + '</p>';
					html += '</div>';
				});
				html += '</div>';
				var temp = document.createElement('div');
				temp.innerHTML = html;
				var newItem = temp.firstChild;
				itemsWrap.appendChild(newItem);
				bindRemove(newItem.querySelector('.adn-repeater-remove'));
				initMediaPickers(newItem);
				idx++;
			});

			function initMediaPickers(scope) {
				if (typeof wp === 'undefined' || !wp.media) return;
				(scope || wrap).querySelectorAll('.adn-media-picker').forEach(function(picker) {
					var $picker = jQuery(picker);
					var $input = $picker.find('.adn-media-id');
					var $previewImg = $picker.find('.adn-media-preview-img');
					var $previewVideo = $picker.find('.adn-media-preview-video');
					var $remove = $picker.find('.adn-remove-media');
					var frame;
					$picker.find('.adn-pick-media').on('click', function(e) {
						e.preventDefault();
						if (frame) { frame.open(); return; }
						frame = wp.media({ title: 'Select Image, GIF or Video', button: { text: 'Use this media' }, multiple: false, library: { type: ['image','video'] } });
						frame.on('select', function() {
							var att = frame.state().get('selection').first().toJSON();
							$input.val(att.id);
							if (att.type === 'video') {
								$previewVideo.attr('src', att.url)[0].load();
								$previewVideo.show();
								$previewImg.hide();
							} else {
								var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
								$previewImg.attr('src', url).show();
								$previewVideo.hide();
							}
							$remove.show();
						});
						frame.open();
					});
					$remove.on('click', function(e) {
						e.preventDefault();
						$input.val('');
						$previewImg.attr('src', '').hide();
						$previewVideo.attr('src', '').hide();
						$(this).hide();
					});
				});
			}
			initMediaPickers();
		})();
		</script>
		<?php
	}

	/** Shared admin-post handler: validate, sanitize per field-type, store, redirect. */
	public static function handleSave(): void {
		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_die( \esc_html__( 'Unauthorised', ADN_TEXT_DOMAIN ) );
		}

		$group   = isset( $_POST['group'] ) ? \sanitize_key( \wp_unslash( $_POST['group'] ) ) : '';
		\check_admin_referer( self::ACTION . '_' . $group );

		$schemas = \adn_settings_schemas();
		if ( ! isset( $schemas[ $group ] ) ) {
			\wp_die( \esc_html__( 'Unknown settings group.', ADN_TEXT_DOMAIN ) );
		}
		$schema = $schemas[ $group ];
		$raw    = isset( $_POST['fields'] ) && \is_array( $_POST['fields'] ) ? \wp_unslash( $_POST['fields'] ) : array();

		$clean = self::sanitizeFields( $schema['fields'], $raw );

		\update_option( $schema['option'], $clean );

		$tab    = isset( $_POST['tab'] ) ? \sanitize_key( \wp_unslash( $_POST['tab'] ) ) : 'dashboard';
		$subtab = isset( $_POST['subtab'] ) ? \sanitize_key( \wp_unslash( $_POST['subtab'] ) ) : '';
		$args   = array(
			'page'     => \ADN_Theme_Admin::tab_page_slug( $tab ),
			'adn_done' => 1,
			'adn_msg'  => \rawurlencode( \__( 'Settings saved.', ADN_TEXT_DOMAIN ) ),
		);
		if ( $subtab ) {
			$args['subtab'] = $subtab;
		}
		\wp_safe_redirect( \add_query_arg( $args, \admin_url( 'admin.php' ) ) );
		exit;
	}

	/** Sanitize all fields from raw POST input. */
	private static function sanitizeFields( array $fields, array $raw ): array {
		$clean = array();
		foreach ( $fields as $field ) {
			$key   = $field['key'];
			$input = $raw[ $key ] ?? null;

			switch ( $field['type'] ) {
				case 'textarea':
					$clean[ $key ] = \sanitize_textarea_field( (string) $input );
					break;

				case 'number':
					$n = \is_numeric( $input ) ? $input + 0 : 0;
					if ( isset( $field['min'] ) ) { $n = \max( $field['min'], $n ); }
					if ( isset( $field['max'] ) ) { $n = \min( $field['max'], $n ); }
					$clean[ $key ] = $n;
					break;

				case 'toggle':
					$clean[ $key ] = empty( $input ) ? 0 : 1;
					break;

				case 'checklist':
					$allowed       = \array_map( 'strval', \array_keys( self::options( $field ) ) );
					$picked        = \is_array( $input ) ? \array_map( 'strval', $input ) : array();
					$clean[ $key ] = \array_values( \array_intersect( $allowed, $picked ) );
					break;

				case 'select':
					$allowed       = \array_map( 'strval', \array_keys( self::options( $field ) ) );
					$clean[ $key ] = \in_array( (string) $input, $allowed, true ) ? (string) $input : '';
					break;

				case 'image':
				case 'media':
					$clean[ $key ] = \sanitize_text_field( (string) $input );
					break;

				case 'repeater':
					$items     = \is_array( $input ) ? $input : array();
					$subfields = $field['subfields'] ?? array();
					$clean_items = array();
					foreach ( $items as $_item ) {
						if ( ! \is_array( $_item ) ) { continue; }
						$_clean = array();
						foreach ( $subfields as $_sf ) {
							$_val = $_item[ $_sf['key'] ] ?? '';
							$_clean[ $_sf['key'] ] = \sanitize_text_field( (string) $_val );
						}
						$clean_items[] = $_clean;
					}
					$clean[ $key ] = $clean_items;
					break;

				case 'text':
				default:
					$clean[ $key ] = \sanitize_text_field( (string) $input );
			}
		}
		return $clean;
	}
}
