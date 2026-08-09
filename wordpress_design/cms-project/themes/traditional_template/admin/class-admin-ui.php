<?php
/**
 * admin/class-admin-ui.php - Reusable UI components for TT Admin.
 */
defined( 'ABSPATH' ) || exit;

class App_Admin_UI {

	// -------------------------------------------------------------------------
	// Basic text input
	// -------------------------------------------------------------------------
	public static function text_field( string $name, string $label, $value = '', bool $required = false ): string {
		$req = $required ? ' required' : '';
		return sprintf(
			'<tr><th scope="row"><label for="%s">%s</label></th><td><input type="text" id="%s" name="%s" class="regular-text" value="%s"%s></td></tr>',
			esc_attr( $name ), esc_html( $label ),
			esc_attr( $name ), esc_attr( $name ),
			esc_attr( (string) $value ), $req
		);
	}

	// -------------------------------------------------------------------------
	// Textarea
	// -------------------------------------------------------------------------
	public static function textarea_field( string $name, string $label, $value = '', bool $required = false ): string {
		$req = $required ? ' required' : '';
		return sprintf(
			'<tr><th scope="row"><label for="%s">%s</label></th><td><textarea id="%s" name="%s" rows="4" class="large-text"%s>%s</textarea></td></tr>',
			esc_attr( $name ), esc_html( $label ),
			esc_attr( $name ), esc_attr( $name ), $req,
			esc_textarea( (string) $value )
		);
	}

	// -------------------------------------------------------------------------
	// Number input
	// -------------------------------------------------------------------------
	public static function number_field( string $name, string $label, $value = '0' ): string {
		return sprintf(
			'<tr><th scope="row"><label for="%s">%s</label></th><td><input type="number" id="%s" name="%s" value="%s" class="small-text"></td></tr>',
			esc_attr( $name ), esc_html( $label ),
			esc_attr( $name ), esc_attr( $name ),
			esc_attr( (string) $value )
		);
	}

	// -------------------------------------------------------------------------
	// Image / Video upload field — uses WordPress media uploader
	// -------------------------------------------------------------------------
	public static function media_field( string $name, string $label, $value = '', string $type = 'image' ): string {
		$uid      = 'tt_media_' . sanitize_key( $name );
		$btn_text = ( $type === 'video' ) ? 'Select Video' : 'Select Image';
		$preview  = '';
		if ( ! empty( $value ) ) {
			if ( $type === 'video' ) {
				$preview = sprintf( '<video src="%s" style="max-width:320px;max-height:180px;display:block;margin-top:6px;" controls></video>', esc_attr( (string) $value ) );
			} else {
				$preview = sprintf( '<img src="%s" id="%s_preview" style="max-width:240px;max-height:160px;display:block;margin-top:6px;border:1px solid #ddd;border-radius:4px;" />', esc_attr( (string) $value ), esc_attr( $uid ) );
			}
		} else {
			$preview = sprintf( '<img id="%s_preview" style="max-width:240px;max-height:160px;display:%s;margin-top:6px;border:1px solid #ddd;border-radius:4px;" />', esc_attr( $uid ), 'none' );
		}
		$html  = '<tr>';
		$html .= '<th scope="row"><label for="' . esc_attr( $uid ) . '">' . esc_html( $label ) . '</label></th>';
		$html .= '<td>';
		$html .= '<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">';
		$html .= '<input type="text" id="' . esc_attr( $uid ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '" class="regular-text tt-media-url">';
		$html .= '<button type="button" class="button tt-media-upload-btn" data-target="' . esc_attr( $uid ) . '" data-type="' . esc_attr( $type ) . '">' . esc_html( $btn_text ) . '</button>';
		if ( ! empty( $value ) ) {
			$html .= '<button type="button" class="button tt-media-remove-btn" data-target="' . esc_attr( $uid ) . '">Remove</button>';
		}
		$html .= '</div>';
		$html .= $preview;
		$html .= '</td>';
		$html .= '</tr>';
		return $html;
	}

	// -------------------------------------------------------------------------
	// Select / dropdown
	// -------------------------------------------------------------------------
	public static function select_field( string $name, string $label, array $options, $selected = '' ): string {
		$opts = '';
		foreach ( $options as $val => $text ) {
			$sel   = selected( (string) $selected, (string) $val, false );
			$opts .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $val ), $sel, esc_html( $text ) );
		}
		return sprintf(
			'<tr><th scope="row"><label for="%s">%s</label></th><td><select id="%s" name="%s">%s</select></td></tr>',
			esc_attr( $name ), esc_html( $label ),
			esc_attr( $name ), esc_attr( $name ), $opts
		);
	}

	// -------------------------------------------------------------------------
	// Submit button
	// -------------------------------------------------------------------------
	public static function submit_button( string $label = 'Save Changes' ): string {
		return '<p class="submit"><button type="submit" class="button button-primary">' . esc_html( $label ) . '</button></p>';
	}

	// -------------------------------------------------------------------------
	// Section card wrapper
	// -------------------------------------------------------------------------
	public static function card_start( string $title ): string {
		return '<div class="tt-card postbox" style="padding:16px 20px;margin-bottom:20px;"><h3 class="hndle" style="margin:0 0 12px;">' . esc_html( $title ) . '</h3><div class="inside">';
	}

	public static function card_end(): string {
		return '</div></div>';
	}

	// -------------------------------------------------------------------------
	// Enqueue the media uploader JS (call once per page in your view)
	// -------------------------------------------------------------------------
	public static function enqueue_media_uploader(): void {
		static $enqueued = false;
		if ( $enqueued ) return;
		$enqueued = true;
		wp_enqueue_media();
		add_action( 'admin_footer', array( __CLASS__, 'media_uploader_script' ) );
	}

	public static function media_uploader_script(): void {
		?>
		<script>
		(function($){
			// Open media frame on button click
			$(document).on('click', '.tt-media-upload-btn', function(e){
				e.preventDefault();
				var btn    = $(this);
				var target = btn.data('target');
				var type   = btn.data('type') || 'image';
				var frame  = wp.media({
					title   : type === 'video' ? 'Select or Upload Video' : 'Select or Upload Image',
					button  : { text: type === 'video' ? 'Use this video' : 'Use this image' },
					library : { type: type },
					multiple: false
				});
				frame.on('select', function(){
					var attachment = frame.state().get('selection').first().toJSON();
					var url        = attachment.url;
					$('#' + target).val(url).trigger('change');
					if (type === 'image') {
						var preview = $('#' + target + '_preview');
						if (preview.length) { preview.attr('src', url).show(); }
						else { $('<img id="'+target+'_preview" style="max-width:240px;max-height:160px;display:block;margin-top:6px;border:1px solid #ddd;border-radius:4px;" />').attr('src', url).insertAfter(btn.closest('div')); }
					} else {
						var vp = $('#' + target + '_vpreview');
						if (vp.length) { vp.attr('src', url).show(); }
						else { $('<video id="'+target+'_vpreview" style="max-width:320px;max-height:180px;display:block;margin-top:6px;" controls/>').attr('src', url).insertAfter(btn.closest('div')); }
					}
				});
				frame.open();
			});

			// Remove media
			$(document).on('click', '.tt-media-remove-btn', function(e){
				e.preventDefault();
				var target = $(this).data('target');
				$('#' + target).val('').trigger('change');
				$('#' + target + '_preview').hide().attr('src', '');
				$('#' + target + '_vpreview').hide().attr('src', '');
			});
		}(jQuery));
		</script>
		<?php
	}
}
