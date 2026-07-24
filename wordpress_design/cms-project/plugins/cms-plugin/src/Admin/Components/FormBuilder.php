<?php
namespace Ah\Cms\Admin\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Fluent form builder for admin pages.
 *
 * Usage:
 *   $form = new FormBuilder( 'ah_save_faq', 'ah_faq_nonce' );
 *   $form->text( 'question', 'Question', $item->question ?? '' );
 *   $form->editor( 'answer', 'Answer', $item->answer ?? '' );
 *   $form->submit( 'Save FAQ' );
 *   $form->render();
 */
class FormBuilder {

	private string $action;
	private string $nonce_name;
	private string $method = 'post';
	private string $enctype = '';
	private array  $fields = [];
	private array  $errors = [];
	private string $submit_label = 'Save';
	private string $submit_class = 'ah-btn ah-btn-primary';
	private string $cancel_url = '';
	private array  $hidden = [];

	public function __construct( string $action, string $nonce_name ) {
		$this->action     = $action;
		$this->nonce_name = $nonce_name;
	}

	public function method( string $m ): self {
		$this->method = $m;
		return $this;
	}

	public function enctype( string $e ): self {
		$this->enctype = $e;
		return $this;
	}

	public function cancelUrl( string $url ): self {
		$this->cancel_url = $url;
		return $this;
	}

	public function hidden( string $name, string $value ): self {
		$this->hidden[ $name ] = $value;
		return $this;
	}

	public function errors( array $errors ): self {
		$this->errors = $errors;
		return $this;
	}

	// ── Field builders ──────────────────────────────────────────

	public function text( string $name, string $label, string $value = '', array $opts = [] ): self {
		$this->fields[] = $this->field( 'text', $name, $label, $value, $opts );
		return $this;
	}

	public function password( string $name, string $label, string $value = '', array $opts = [] ): self {
		$this->fields[] = $this->field( 'password', $name, $label, $value, $opts );
		return $this;
	}

	public function number( string $name, string $label, $value = '', array $opts = [] ): self {
		$this->fields[] = $this->field( 'number', $name, $label, $value, $opts );
		return $this;
	}

	public function textarea( string $name, string $label, string $value = '', array $opts = [] ): self {
		$opts['rows'] = $opts['rows'] ?? 4;
		$this->fields[] = $this->field( 'textarea', $name, $label, $value, $opts );
		return $this;
	}

	public function select( string $name, string $label, array $options, $selected = '', array $opts = [] ): self {
		$this->fields[] = [
			'type'     => 'select',
			'name'     => $name,
			'label'    => $label,
			'value'    => $selected,
			'options'  => $options,
			'opts'     => $opts,
		];
		return $this;
	}

	public function checkbox( string $name, string $label, bool $checked = false, array $opts = [] ): self {
		$this->fields[] = [
			'type'    => 'checkbox',
			'name'    => $name,
			'label'   => $label,
			'checked' => $checked,
			'opts'    => $opts,
		];
		return $this;
	}

	public function toggle( string $name, string $label, bool $on = false, array $opts = [] ): self {
		$this->fields[] = [
			'type'  => 'toggle',
			'name'  => $name,
			'label' => $label,
			'on'    => $on,
			'opts'  => $opts,
		];
		return $this;
	}

	public function radio( string $name, string $label, array $options, $selected = '', array $opts = [] ): self {
		$this->fields[] = [
			'type'    => 'radio',
			'name'    => $name,
			'label'   => $label,
			'value'   => $selected,
			'options' => $options,
			'opts'    => $opts,
		];
		return $this;
	}

	public function editor( string $name, string $label, string $value = '', array $opts = [] ): self {
		$this->fields[] = [
			'type'  => 'editor',
			'name'  => $name,
			'label' => $label,
			'value' => $value,
			'opts'  => $opts,
		];
		return $this;
	}

	public function image( string $name, string $label, $value = '', array $opts = [] ): self {
		$this->fields[] = [
			'type'  => 'image',
			'name'  => $name,
			'label' => $label,
			'value' => $value,
			'opts'  => $opts,
		];
		return $this;
	}

	public function textareaSmall( string $name, string $label, string $value = '', array $opts = [] ): self {
		$opts['rows'] = 2;
		$this->fields[] = $this->field( 'textarea', $name, $label, $value, $opts );
		return $this;
	}

	// ── Layout helpers ──────────────────────────────────────────

	/** Render fields in a 2-column grid. Pass array of [fieldCallables]. */
	public function grid( array $rows ): self {
		$this->fields[] = [ 'type' => 'grid', 'rows' => $rows ];
		return $this;
	}

	/** Section heading divider. */
	public function section( string $title ): self {
		$this->fields[] = [ 'type' => 'section', 'title' => $title ];
		return $this;
	}

	/** Raw HTML passthrough. */
	public function raw( string $html ): self {
		$this->fields[] = [ 'type' => 'raw', 'html' => $html ];
		return $this;
	}

	/** Submit button config. */
	public function submit( string $label, string $class = '' ): self {
		$this->submit_label = $label;
		$this->submit_class = $class ?: 'ah-btn ah-btn-primary';
		return $this;
	}

	// ── Render ──────────────────────────────────────────────────

	public function render(): void {
		$method = esc_attr( $this->method );
		$enctype = $this->enctype ? ' enctype="' . esc_attr( $this->enctype ) . '"' : '';
		echo '<form method="' . $method . '"' . $enctype . '>';
		wp_nonce_field( $this->action, $this->nonce_name );

		foreach ( $this->hidden as $k => $v ) {
			echo '<input type="hidden" name="' . esc_attr( $k ) . '" value="' . esc_attr( $v ) . '">';
		}

		if ( ! empty( $this->errors ) ) {
			echo '<div class="ah-notice ah-notice-error"><ul style="margin:0;padding-left:18px;">';
			foreach ( $this->errors as $err ) {
				echo '<li>' . esc_html( $err ) . '</li>';
			}
			echo '</ul></div>';
		}

		foreach ( $this->fields as $field ) {
			$this->renderField( $field );
		}

		echo '<div class="ah-form-actions" style="margin-top:18px;display:flex;gap:10px;align-items:center;">';
		echo '<button type="submit" class="' . esc_attr( $this->submit_class ) . '">' . esc_html( $this->submit_label ) . '</button>';
		if ( $this->cancel_url ) {
			echo '<a href="' . esc_url( $this->cancel_url ) . '" class="ah-btn ah-btn-secondary">Cancel</a>';
		}
		echo '</div>';

		echo '</form>';
	}

	// ── Internal ────────────────────────────────────────────────

	private function field( string $type, string $name, string $label, $value, array $opts ): array {
		return [
			'type'  => $type,
			'name'  => $name,
			'label' => $label,
			'value' => $value,
			'opts'  => $opts,
		];
	}

	private function renderField( array $f ): void {
		$type = $f['type'] ?? 'text';

		switch ( $type ) {
			case 'section':
				echo '<h3 style="margin:20px 0 10px;padding-bottom:8px;border-bottom:1px solid var(--ah-border);">' . esc_html( $f['title'] ) . '</h3>';
				return;

			case 'grid':
				echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">';
				foreach ( $f['rows'] as $row ) {
					if ( is_array( $row ) ) {
						foreach ( $row as $subfield ) {
							$this->renderField( $subfield );
						}
					}
				}
				echo '</div>';
				return;

			case 'raw':
				echo $f['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				return;
		}

		$name  = $f['name'] ?? '';
		$label = $f['label'] ?? '';
		$opts  = $f['opts'] ?? [];
		$help  = $opts['help'] ?? '';
		$req   = ! empty( $opts['required'] ) ? ' required' : '';
		$ph    = ! empty( $opts['placeholder'] ) ? ' placeholder="' . esc_attr( $opts['placeholder'] ) . '"' : '';
		$id    = $opts['id'] ?? $name;

		echo '<div class="ah-form-row">';
		echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $label );
		if ( $req ) { echo ' <span style="color:var(--ah-danger);">*</span>'; }
		echo '</label>';

		switch ( $type ) {
			case 'text':
			case 'password':
			case 'number':
				$min = isset( $opts['min'] ) ? ' min="' . esc_attr( $opts['min'] ) . '"' : '';
				$max = isset( $opts['max'] ) ? ' max="' . esc_attr( $opts['max'] ) . '"' : '';
				echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" value="' . esc_attr( $f['value'] ) . '"' . $ph . $req . $min . $max . '>';
				break;

			case 'textarea':
				$rows = $opts['rows'] ?? 4;
				echo '<textarea name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" rows="' . esc_attr( $rows ) . '"' . $ph . $req . '>';
				echo esc_textarea( $f['value'] );
				echo '</textarea>';
				break;

			case 'select':
				echo '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '"' . $req . '>';
				foreach ( $f['options'] as $k => $v ) {
					echo '<option value="' . esc_attr( $k ) . '"' . selected( $f['value'], $k, false ) . '>' . esc_html( $v ) . '</option>';
				}
				echo '</select>';
				break;

			case 'checkbox':
				$checked = $f['checked'] ? ' checked' : '';
				echo '<label style="display:flex;align-items:center;gap:8px;cursor:pointer;">';
				echo '<input type="checkbox" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" value="1"' . $checked . '>';
				echo '<span>' . esc_html( $label ) . '</span></label>';
				$label = '';
				break;

			case 'toggle':
				$checked = $f['on'] ? ' checked' : '';
				echo '<label class="ah-toggle">';
				echo '<input type="checkbox" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" value="1"' . $checked . '>';
				echo '<span class="ah-toggle-slider"></span></label>';
				$label = '';
				break;

			case 'radio':
				echo '<div class="ah-radio-group">';
				foreach ( $f['options'] as $k => $v ) {
					$sel = selected( $f['value'], $k, false );
					echo '<label class="ah-radio-option">';
					echo '<input type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $k ) . '"' . $sel . '>';
					echo '<span>' . esc_html( $v ) . '</span></label>';
				}
				echo '</div>';
				break;

			case 'editor':
				$ed_opts = array_merge( [
					'textarea_name' => $name,
					'media_buttons' => false,
					'teeny'         => true,
					'editor_height' => 200,
				], $opts );
				wp_editor( $f['value'], esc_attr( $id ), $ed_opts );
				break;

			case 'image':
				$val = $f['value'] ?? '';
				$url = is_numeric( $val ) ? wp_get_attachment_image_url( (int) $val, 'medium' ) : $val;
				echo '<div class="ah-image-picker">';
				echo '<input type="hidden" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" value="' . esc_attr( $val ) . '">';
				echo '<div class="ah-image-preview-wrap">';
				if ( $url ) {
					echo '<img class="ah-image-preview" src="' . esc_url( $url ) . '" alt="">';
				} else {
					echo '<div class="ah-image-placeholder"><i class="dashicons dashicons-format-image"></i></div>';
				}
				echo '</div>';
				echo '<div class="ah-image-picker-btns">';
				echo '<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image" data-target="' . esc_attr( $id ) . '">Choose Image</button>';
				echo '<button type="button" class="ah-btn ah-btn-danger ah-btn-sm ah-remove-image" data-target="' . esc_attr( $id ) . '">Remove</button>';
				echo '</div></div>';
				break;
		}

		if ( $help && 'checkbox' !== $type && 'toggle' !== $type ) {
			echo '<p class="description">' . esc_html( $help ) . '</p>';
		}
		echo '</div>';
	}
}
