<?php defined( 'ABSPATH' ) || exit; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php wp_nonce_field( 'ch_content_settings_flavours' ); ?>
	<input type="hidden" name="action" value="ch_content_settings_flavours">

	<div class="ch-card">
		<h2>Flavours Items</h2>
		<p class="ch-cs-desc">Each item shows an emoji, bold Name, and Type text in the Flavours page.</p>

		<div class="ch-rep-header" style="grid-template-columns:60px 1fr 2fr 36px;">
			<span>Emoji</span><span>Name</span><span>Type</span><span></span>
		</div>
		<div class="ch-repeater ch-repeater--flavours" id="ch-flavours-repeater">
			<?php foreach ( (array) ( $flavours ?? [] ) as $i => $item ) :
				$item = (array) $item;
			?>
			<div class="ch-rep-row ch-rep-row--flavours" style="grid-template-columns:60px 1fr 2fr 36px;">
				<input style="width: 100% !important;" type="text" name="flavours_items[<?php echo $i; ?>][emoji]"
					value="<?php echo esc_attr( $item['emoji'] ?? '' ); ?>" placeholder="🌿" style="text-align:center;font-size:1.3rem;">
				<input style="width: 100% !important;" type="text" name="flavours_items[<?php echo $i; ?>][name]"
					value="<?php echo esc_attr( $item['name'] ?? '' ); ?>" placeholder="Item Name">
				<input style="width: 100% !important;" type="text" name="flavours_items[<?php echo $i; ?>][type]"
					value="<?php echo esc_attr( $item['type'] ?? '' ); ?>" placeholder="Type">
				<button type="button" class="ch-rep-remove" title="Remove">✕</button>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="ch-rep-add button" data-target="ch-flavours-repeater" data-prefix="flavours_items" data-columns="emoji,name,type">
			+ Add Item
		</button>
	</div>

	<?php submit_button( '💾 Save Flavours', 'primary', 'submit', false ); ?>
</form>
