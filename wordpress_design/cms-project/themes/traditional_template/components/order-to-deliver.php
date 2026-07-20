<?php
defined( 'ABSPATH' ) || exit;

$products     = nt_data( 'delivery_products' ) ?: [];
$content      = nt_data( 'content' )['order_to_deliver'] ?? [];
$otd_tag      = $content['tag'] ?? '';
$otd_heading  = $content['heading'] ?? '';
$otd_sub      = $content['body'] ?? '';
$otd_image    = $content['image'] ?? 'https://placehold.co/600x800';
$otd_features = $content['features'] ?? [];

$time_slots = [ 'Morning (8am-12pm)', 'Afternoon (12pm-5pm)', 'Evening (5pm-8pm)', 'Flexible','Now' ];
?>

<!-- ═══ ORDER-TO-DELIVER BANNER ════════════════════════════════════════════ -->
<section id="order-to-deliver" class="nt-frn-section nt-otd-section">
	<div class="container">
		<div class="nt-frn-card nt-otd-card fade-up">

			<!-- Left: image -->
			<div class="nt-frn-visual">
				<img src="<?php echo esc_url( $otd_image ); ?>" alt="<?php echo esc_attr( $content['image_alt'] ?? 'Delivery Service' ); ?>" loading="lazy">
				<div class="nt-frn-visual-badge">Order &amp; Deliver 🌿</div>
			</div>

			<!-- Right: content -->
			<div class="nt-frn-content">
				<div class="section-tag" style="color:var(--client-color-7);"><?php echo esc_html( $otd_tag ); ?></div>
				<h2 class="nt-frn-title"><?php echo wp_kses( $otd_heading, [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ] ); ?></h2>
				<p class="nt-frn-sub"><?php echo esc_html( $otd_sub ); ?></p>

				<?php if ( ! empty( $otd_features ) ) : ?>
				<ul class="nt-frn-features">
					<?php foreach ( $otd_features as $feat ) :
						$feat = (array) $feat;
					?>
						<li><?php echo esc_html( $feat['icon'] ?? '' ); ?> <?php echo esc_html( $feat['text'] ?? '' ); ?></li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>

				<button type="button" class="nt-frn-open btn-lime nt-otd-open" id="nt-otd-open">
					🥤 Order Now
				</button>
			</div>

		</div>
	</div>
</section>

<?php ob_start(); ?>
		<div class="nt-bk-modal-scroll">

<?php
/* ── Build Custom Product Selector for Step 1 ────────────────────────────────── */
ob_start();
?>
	<h3 class="nt-bk-step-title">What would you like? 🥤</h3>
	<p class="nt-bk-step-desc">Select items and set the quantity for each.</p>

	<div class="nt-otd-products">
		<?php foreach ( $products as $p ) :
			$p    = (array) $p;
			$name = $p['name'] ?? '';
			$slug = sanitize_key( $name );
		?>
		<label class="nt-otd-product-row" for="otd-item-<?php echo esc_attr( $slug ); ?>">
			<input type="checkbox" id="otd-item-<?php echo esc_attr( $slug ); ?>"
				name="otd_items[]" value="<?php echo esc_attr( $name ); ?>"
				class="nt-otd-product-chk">
			<span class="nt-otd-product-icon"><?php echo esc_html( $p['icon'] ?? '🌿' ); ?></span>
			<span class="nt-otd-product-info">
				<span class="nt-otd-product-name"><?php echo esc_html( $name ); ?></span>
				<span class="nt-otd-product-desc"><?php echo esc_html( $p['desc'] ?? '' ); ?></span>
			</span>
			<span class="nt-otd-product-size"><?php echo esc_html( $p['size'] ?? '' ); ?></span>
			<span class="nt-otd-qty-wrap">
				<button type="button" class="nt-otd-qty-btn nt-otd-qty-minus" aria-label="Decrease">−</button>
				<input type="number" name="otd_qty[<?php echo esc_attr( $name ); ?>]"
					class="nt-otd-qty-input" value="1" min="1" max="99"
					aria-label="Quantity for <?php echo esc_attr( $name ); ?>">
				<button type="button" class="nt-otd-qty-btn nt-otd-qty-plus" aria-label="Increase">+</button>
			</span>
		</label>
		<?php endforeach; ?>
	</div>

	<div class="nt-bk-nav">
		<span></span>
		<button type="button" class="nt-bk-next btn-lime" data-next="2">Next: Delivery →</button>
	</div>
<?php
$step1_custom_html = ob_get_clean();

/* ── Generic Multistep Form ──────────────────────────────────────────────────── */
$json_path = get_template_directory() . '/admin/data/form_order.json';
$form_data = [];
if ( file_exists( $json_path ) ) {
	$form_data = json_decode( file_get_contents( $json_path ), true ) ?: [];
}

// Add the custom first step
array_unshift( $form_data['steps'], [
	'title'       => 'Select Items',
	'custom_html' => $step1_custom_html,
] );

get_template_part( 'components/parts/generic-multistep-form', null, $form_data );
?>

		</div><!-- .nt-bk-modal-scroll -->
<?php
$modal_content = ob_get_clean();
get_template_part( 'components/parts/generic-dialog', null, [
	'id'      => 'nt-otd-modal',
	'title'   => 'Order to Deliver',
	'content' => $modal_content
] );
?>
