<?php
/**
 * Order to Deliver - vintage banner + multi-step order modal.
 *
 * Banner content comes from admin/data/content.json -> order_to_deliver.
 * The "Order Now" button opens the reusable multi-step wizard
 * (components/parts/form-modal.php) built from admin/data/form_order.json,
 * with a product-pick step prepended from admin/data/delivery_products.json.
 * The wizard submits via the generic 'lead_submit' handler. Nothing hardcoded.
 */
defined( 'ABSPATH' ) || exit;

$content      = nt_data( 'content' )['order_to_deliver'] ?? array();
$otd_tag      = $content['tag'] ?? 'Order & Deliver';
$otd_heading  = $content['heading'] ?? '';
$otd_sub      = $content['body'] ?? '';
$otd_image    = $content['image'] ?? '';
$otd_alt      = $content['image_alt'] ?? 'Fresh delivery';
$otd_features = $content['features'] ?? array();

// Build the wizard config: JSON steps + a product-pick step in front.
$config   = nt_data( 'form_order' );
$products = nt_data( 'delivery_products' ) ?: array();
$options  = array( '' => __( 'Choose a flavour…', NT_TEXT_DOMAIN ) );
foreach ( $products as $p ) {
	$p    = (array) $p;
	$name = $p['name'] ?? '';
	if ( '' !== $name ) {
		$options[ $name ] = $name;
	}
}
if ( ! empty( $options ) && ! empty( $config['steps'] ) ) {
	array_unshift( $config['steps'], array(
		'title'  => 'Your Order',
		'desc'   => 'Pick the flavour you would like delivered.',
		'fields' => array(
			array( 'type' => 'select', 'id' => 'otd_flavour', 'name' => 'otd_flavour', 'label' => 'Flavour', 'required' => true, 'options' => $options ),
			array( 'type' => 'number', 'id' => 'otd_qty', 'name' => 'otd_qty', 'label' => 'How many?', 'placeholder' => '1' ),
		),
	) );
}
?>

<section id="order-to-deliver" class="nt-otd-section">
	<div class="container">
		<div class="nt-otd-card">

			<div class="nt-otd-visual">
				<?php if ( $otd_image ) : ?>
					<img src="<?php echo esc_url( $otd_image ); ?>" alt="<?php echo esc_attr( $otd_alt ); ?>" loading="lazy">
				<?php endif; ?>
				<span class="nt-otd-badge">Order &amp; Deliver 🌿</span>
			</div>

			<div class="nt-otd-content">
				<span class="nt-section-tag"><?php echo esc_html( $otd_tag ); ?></span>
				<h2 class="nt-otd-title"><?php echo wp_kses( $otd_heading, array( 'span' => array( 'class' => array() ), 'em' => array() ) ); ?></h2>
				<p class="nt-otd-sub"><?php echo esc_html( $otd_sub ); ?></p>

				<?php if ( ! empty( $otd_features ) ) : ?>
					<ul class="nt-otd-features">
						<?php foreach ( $otd_features as $feat ) :
							$feat = (array) $feat; ?>
							<li><span aria-hidden="true"><?php echo esc_html( $feat['icon'] ?? '✓' ); ?></span> <?php echo esc_html( $feat['text'] ?? '' ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<button type="button" class="btn nt-otd-open" data-nt-open="nt-order-modal">🥤 Order Now</button>
			</div>

		</div>
	</div>
</section>

<?php
get_template_part( 'components/parts/form-modal', null, array(
	'id'     => 'nt-order-modal',
	'title'  => __( 'Order Fresh Now 🌿', NT_TEXT_DOMAIN ),
	'sub'    => __( 'A few quick steps and we will bring it to you.', NT_TEXT_DOMAIN ),
	'config' => $config,
) );
