<?php defined( 'ABSPATH' ) || exit; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php wp_nonce_field( 'ch_content_settings_eventswhy' ); ?>
	<input type="hidden" name="action" value="ch_content_settings_eventswhy">

	<div class="ch-card">
		<h2>🖼️ Section Image</h2>
		<div class="ch-row">
			<label>Image URL</label>
			<input type="url" name="events_why_image"
				value="<?php echo esc_attr( $events_why['image'] ?? '' ); ?>"
				placeholder="https://...">
		</div>
	</div>

	<div class="ch-card">
		<h2>🎯 Why Choose Us Items</h2>
		<p class="ch-cs-desc">Each item shows an icon, bold title, and description text in the Events page "Why Choose Us" section.</p>

		<div class="ch-rep-header" style="grid-template-columns:60px 1fr 2fr 36px;">
			<span>Icon</span><span>Title</span><span>Description</span><span></span>
		</div>
		<div class="ch-repeater ch-repeater--eventswhy" id="ch-eventswhy-repeater">
			<?php foreach ( (array) ( $events_why['items'] ?? [] ) as $i => $item ) :
				$item = (array) $item;
			?>
			<div class="ch-rep-row ch-rep-row--eventswhy">
				<input type="text" name="events_why_items[<?php echo $i; ?>][icon]"
					value="<?php echo esc_attr( $item['icon'] ?? '' ); ?>" placeholder="🌿" style="text-align:center;font-size:1.3rem;">
				<input type="text" name="events_why_items[<?php echo $i; ?>][title]"
					value="<?php echo esc_attr( $item['title'] ?? '' ); ?>" placeholder="Item title">
				<input type="text" name="events_why_items[<?php echo $i; ?>][text]"
					value="<?php echo esc_attr( $item['text'] ?? '' ); ?>" placeholder="Description text">
				<button type="button" class="ch-rep-remove" title="Remove">✕</button>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="ch-rep-add button" data-target="ch-eventswhy-repeater" data-prefix="events_why_items" data-columns="icon,title,text">
			+ Add Item
		</button>
	</div>

	<?php submit_button( '💾 Save Events Why', 'primary', 'submit', false ); ?>
</form>
