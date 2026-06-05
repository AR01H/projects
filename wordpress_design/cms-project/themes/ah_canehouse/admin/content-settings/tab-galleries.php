<?php defined( 'ABSPATH' ) || exit; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php wp_nonce_field( 'ch_content_settings_galleries' ); ?>
	<input type="hidden" name="action" value="ch_content_settings_galleries">

	<?php
	$gallery_sections = [
		[ 'key' => 'events',    'label' => '🎪 Events Gallery',    'data' => $events_gallery    ?? [] ],
		[ 'key' => 'franchise', 'label' => '🤝 Franchise Gallery', 'data' => $franchise_gallery ?? [] ],
		[ 'key' => 'about',     'label' => '📸 About Gallery',     'data' => $about_gallery     ?? [] ],
		[ 'key' => 'equipment', 'label' => '🛠️ Equipment Gallery', 'data' => $equipment_gallery ?? [] ],
	];
	foreach ( $gallery_sections as $gs ) :
	?>
	<div class="ch-card">
		<h2><?php echo esc_html( $gs['label'] ); ?></h2>
		<p class="ch-cs-desc">Each row = one image card. <strong>Image URL</strong> is required; label and description are optional.</p>

		<div class="ch-rep-header" style="grid-template-columns:2fr 1fr 1fr 36px;">
			<span>Image URL</span><span>Label</span><span>Description</span><span></span>
		</div>
		<div class="ch-repeater ch-repeater--gallery" id="ch-<?php echo esc_attr( $gs['key'] ); ?>-repeater">
			<?php foreach ( $gs['data'] as $i => $img ) :
				$img = (array) $img;
			?>
			<div class="ch-rep-row ch-rep-row--gallery">
				<input type="text" name="gallery_<?php echo esc_attr( $gs['key'] ); ?>[<?php echo $i; ?>][src]"
					value="<?php echo esc_attr( $img['src'] ?? '' ); ?>" placeholder="https://...">
				<input type="text" name="gallery_<?php echo esc_attr( $gs['key'] ); ?>[<?php echo $i; ?>][label]"
					value="<?php echo esc_attr( $img['label'] ?? '' ); ?>" placeholder="e.g. Wedding Setup">
				<input type="text" name="gallery_<?php echo esc_attr( $gs['key'] ); ?>[<?php echo $i; ?>][desc]"
					value="<?php echo esc_attr( $img['desc'] ?? '' ); ?>" placeholder="Short caption">
				<button type="button" class="ch-rep-remove" title="Remove">✕</button>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="ch-rep-add button"
			data-target="ch-<?php echo esc_attr( $gs['key'] ); ?>-repeater"
			data-prefix="gallery_<?php echo esc_attr( $gs['key'] ); ?>"
			data-columns="src,label,desc">
			+ Add Image
		</button>
	</div>
	<?php endforeach; ?>

	<?php submit_button( '💾 Save Gallery Images', 'primary', 'submit', false ); ?>
</form>
