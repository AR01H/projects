<?php defined( 'ABSPATH' ) || exit; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php wp_nonce_field( 'ch_content_settings_about' ); ?>
	<input type="hidden" name="action" value="ch_content_settings_about">

	<div class="ch-card">
		<h2>🎯 Mission / Vision / Values Cards</h2>
		<p class="ch-cs-desc">Three cards shown in the "Our Foundation" carousel on the About page.</p>

		<div class="ch-rep-header" style="grid-template-columns:60px 1fr 2fr 36px;">
			<span>Icon</span><span>Title</span><span>Text</span><span></span>
		</div>
		<div class="ch-repeater ch-repeater--mvv" id="ch-mvv-repeater">
			<?php foreach ( $about_mvv as $i => $card ) :
				$card = (array) $card;
			?>
			<div class="ch-rep-row ch-rep-row--mvv">
				<input type="text" name="about_mvv[<?php echo $i; ?>][icon]"
					value="<?php echo esc_attr( $card['icon'] ?? '' ); ?>" placeholder="🎯" style="text-align:center;font-size:1.3rem;">
				<input type="text" name="about_mvv[<?php echo $i; ?>][title]"
					value="<?php echo esc_attr( $card['title'] ?? '' ); ?>" placeholder="e.g. Our Mission">
				<input type="text" name="about_mvv[<?php echo $i; ?>][text]"
					value="<?php echo esc_attr( $card['text'] ?? '' ); ?>" placeholder="Card description">
				<button type="button" class="ch-rep-remove" title="Remove">✕</button>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="ch-rep-add button" data-target="ch-mvv-repeater" data-prefix="about_mvv" data-columns="icon,title,text">
			+ Add Card
		</button>
	</div>

	<div class="ch-card">
		<h2>✅ Quality Commitment List</h2>
		<p class="ch-cs-desc">Bullet-point list shown in the "Our Quality Promise" section on the About page.</p>

		<div class="ch-rep-header ch-rep-header--single">
			<span>Quality Point</span><span></span>
		</div>
		<div class="ch-repeater ch-repeater--single" id="ch-quality-repeater">
			<?php foreach ( $about_quality as $i => $item ) : ?>
			<div class="ch-rep-row ch-rep-row--single">
				<input type="text" name="about_quality[<?php echo $i; ?>]"
					value="<?php echo esc_attr( $item ); ?>" placeholder="e.g. Pressed fresh to order, never pre-made">
				<button type="button" class="ch-rep-remove" title="Remove">✕</button>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="ch-rep-add button" data-target="ch-quality-repeater" data-prefix="about_quality" data-single="1">
			+ Add Point
		</button>
	</div>

	<?php submit_button( '💾 Save About Page', 'primary', 'submit', false ); ?>
</form>
