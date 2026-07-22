<?php
/**
 * Shortcode: [ah_product_listing]
 * Advanced product listing with search, filter, sort, and pagination.
 * Theme-independent — all styles are inline.
 */
defined( 'ABSPATH' ) || exit;

use AHEcommerce\Modules\Products\Product_Repository;
use AHEcommerce\Commerce\Sales\Sale_Service;
use AHEcommerce\Commerce\Inventory\Inventory_Service;
use AHEcommerce\Commerce\Wishlist\Wishlist_Service;
use AHEcommerce\Commerce\Compare\Compare_Service;

$repo = \AH_Ecommerce::container()->get( Product_Repository::class );

// Parse shortcode attributes.
$atts = shortcode_atts( array(
	'per_page'  => 12,
	'columns'   => 3,
	'show_search'   => 'yes',
	'show_filters'  => 'yes',
	'show_sort'     => 'yes',
	'show_compare'  => 'yes',
	'show_wishlist' => 'yes',
	'category'      => '',
), $atts, 'ah_product_listing' );

// Get current state from URL.
$current_page = max( 1, (int) ( $_GET['ah_page'] ?? 1 ) );
$search       = sanitize_text_field( $_GET['ah_search'] ?? '' );
$sort         = sanitize_key( $_GET['ah_sort'] ?? 'newest' );
$filter_cat   = sanitize_text_field( $_GET['ah_category'] ?? $atts['category'] );
$filter_type  = sanitize_key( $_GET['ah_type'] ?? '' );
$filter_price_min = isset( $_GET['ah_price_min'] ) ? (float) $_GET['ah_price_min'] : '';
$filter_price_max = isset( $_GET['ah_price_max'] ) ? (float) $_GET['ah_price_max'] : '';

// Build query.
global $wpdb;
$table = $wpdb->prefix . 'ah_ecommerce_products';
$meta  = $wpdb->prefix . 'ah_ecommerce_product_meta';

$where  = "p.status = 'published'";
$args   = array();

if ( $search ) {
	$where .= " AND (p.title LIKE %s OR p.sku LIKE %s)";
	$like   = '%' . $wpdb->esc_like( $search ) . '%';
	$args[] = $like;
	$args[] = $like;
}
if ( $filter_type ) {
	$where .= " AND p.type = %s";
	$args[] = $filter_type;
}
if ( $filter_price_min !== '' ) {
	$where .= " AND p.price >= %f";
	$args[] = $filter_price_min;
}
if ( $filter_price_max !== '' ) {
	$where .= " AND p.price <= %f";
	$args[] = $filter_price_max;
}
if ( $filter_cat ) {
	$where .= " AND EXISTS (SELECT 1 FROM {$meta} m WHERE m.product_id = p.id AND m.meta_key = 'linked_categories' AND m.meta_value LIKE %s)";
	$args[] = '%' . $wpdb->esc_like( $filter_cat ) . '%';
}

// Sort.
$order_map = array(
	'newest'    => 'p.created_at DESC',
	'oldest'    => 'p.created_at ASC',
	'price_low' => 'p.price ASC',
	'price_high'=> 'p.price DESC',
	'name_az'   => 'p.title ASC',
	'name_za'   => 'p.title DESC',
);
$order_by = $order_map[ $sort ] ?? 'p.created_at DESC';

$offset = ( $current_page - 1 ) * $atts['per_page'];

// Count.
$count_query = "SELECT COUNT(*) FROM {$table} p WHERE {$where}";
$total = (int) ( ! empty( $args )
	? $wpdb->get_var( $wpdb->prepare( $count_query, ...$args ) )
	: $wpdb->get_var( $count_query )
);
$total_pages = max( 1, ceil( $total / $atts['per_page'] ) );

// Fetch.
$query_args   = $args;
$query_args[] = $atts['per_page'];
$query_args[] = $offset;
$query = "SELECT p.* FROM {$table} p WHERE {$where} ORDER BY {$order_by} LIMIT %d OFFSET %d";
$products = $wpdb->get_results( $wpdb->prepare( $query, ...$query_args ) );

// Get categories for filter dropdown.
$categories = $wpdb->get_col( "SELECT DISTINCT meta_value FROM {$meta} WHERE meta_key = 'linked_categories' AND meta_value != ''" );
$all_cats   = array();
foreach ( $categories as $cat_string ) {
	foreach ( explode( ',', $cat_string ) as $c ) {
		$c = trim( $c );
		if ( $c ) $all_cats[ $c ] = $c;
	}
}
ksort( $all_cats );

// Build base URL for pagination / filters.
$base_url = remove_query_arg( array( 'ah_page', 'ah_search', 'ah_sort', 'ah_category', 'ah_type', 'ah_price_min', 'ah_price_max' ) );

wp_enqueue_script( 'jquery' );
?>
<style>
.ah-listing-wrap { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
.ah-listing-bar { display:flex; flex-wrap:wrap; gap:12px; align-items:center; margin-bottom:20px; padding:15px; background:#f7fafc; border-radius:8px; }
.ah-listing-bar input, .ah-listing-bar select { padding:8px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; }
.ah-listing-bar input[type="search"] { min-width:250px; }
.ah-listing-grid { display:grid; grid-template-columns:repeat(<?php echo (int) $atts['columns']; ?>, 1fr); gap:20px; }
.ah-listing-card { border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; background:#fff; transition:transform 0.2s, box-shadow 0.2s; position:relative; }
.ah-listing-card:hover { transform:translateY(-3px); box-shadow:0 8px 25px rgba(0,0,0,0.1); }
.ah-listing-card img { width:100%; height:220px; object-fit:cover; }
.ah-listing-card-body { padding:15px; }
.ah-listing-card-title { font-size:16px; font-weight:600; margin:0 0 6px; color:#111827; }
.ah-listing-card-price { font-size:18px; font-weight:700; color:#dc2626; }
.ah-listing-card-price .orig { text-decoration:line-through; color:#9ca3af; font-size:14px; margin-left:6px; }
.ah-listing-badge { position:absolute; top:10px; left:10px; padding:4px 10px; border-radius:5px; font-size:11px; font-weight:700; color:#fff; }
.ah-badge-sale { background:#dc2626; }
.ah-badge-new { background:#2563eb; }
.ah-badge-oos { background:#6b7280; }
.ah-listing-actions { display:flex; gap:6px; margin-top:10px; }
.ah-listing-actions button, .ah-listing-actions a { flex:1; padding:8px; border:1px solid #d1d5db; border-radius:6px; background:#fff; cursor:pointer; font-size:12px; text-align:center; text-decoration:none; color:#374151; transition:background 0.2s; }
.ah-listing-actions button:hover, .ah-listing-actions a:hover { background:#f3f4f6; }
.ah-listing-paginate { display:flex; justify-content:center; gap:8px; margin-top:30px; }
.ah-listing-paginate a, .ah-listing-paginate span { padding:8px 14px; border-radius:6px; text-decoration:none; font-size:14px; border:1px solid #d1d5db; }
.ah-listing-paginate .current { background:#111827; color:#fff; border-color:#111827; }
</style>

<div class="ah-listing-wrap">
	<?php if ( $atts['show_search'] === 'yes' || $atts['show_filters'] === 'yes' || $atts['show_sort'] === 'yes' ) : ?>
	<form method="get" class="ah-listing-bar" action="<?php echo esc_url( $base_url ); ?>">
		<?php if ( $atts['show_search'] === 'yes' ) : ?>
			<input type="search" name="ah_search" value="<?php echo esc_attr( $search ); ?>" placeholder="Search products...">
		<?php endif; ?>
		<?php if ( $atts['show_sort'] === 'yes' ) : ?>
			<select name="ah_sort">
				<option value="newest" <?php selected( $sort, 'newest' ); ?>>Newest</option>
				<option value="price_low" <?php selected( $sort, 'price_low' ); ?>>Price: Low → High</option>
				<option value="price_high" <?php selected( $sort, 'price_high' ); ?>>Price: High → Low</option>
				<option value="name_az" <?php selected( $sort, 'name_az' ); ?>>Name: A → Z</option>
				<option value="name_za" <?php selected( $sort, 'name_za' ); ?>>Name: Z → A</option>
			</select>
		<?php endif; ?>
		<?php if ( $atts['show_filters'] === 'yes' && ! empty( $all_cats ) ) : ?>
			<select name="ah_category">
				<option value="">All Categories</option>
				<?php foreach ( $all_cats as $cat ) : ?>
					<option value="<?php echo esc_attr( $cat ); ?>" <?php selected( $filter_cat, $cat ); ?>><?php echo esc_html( $cat ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>
		<?php if ( $atts['show_filters'] === 'yes' ) : ?>
			<input type="number" name="ah_price_min" value="<?php echo esc_attr( $filter_price_min ); ?>" placeholder="Min $" style="width:90px;">
			<input type="number" name="ah_price_max" value="<?php echo esc_attr( $filter_price_max ); ?>" placeholder="Max $" style="width:90px;">
		<?php endif; ?>
		<button type="submit" style="padding:8px 18px; background:#111827; color:#fff; border:none; border-radius:6px; cursor:pointer;">Search</button>
	</form>
	<?php endif; ?>

	<p style="color:#6b7280; margin-bottom:15px;"><?php echo number_format( $total ); ?> product<?php echo $total !== 1 ? 's' : ''; ?> found</p>

	<?php if ( empty( $products ) ) : ?>
		<p style="text-align:center; padding:60px; color:#9ca3af;">No products match your criteria.</p>
	<?php else : ?>
		<div class="ah-listing-grid">
			<?php foreach ( $products as $product ) :
				$meta_data = array();
				$meta_rows = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$meta} WHERE product_id = %d", $product->id ) );
				foreach ( $meta_rows as $m ) { $meta_data[ $m->meta_key ] = maybe_unserialize( $m->meta_value ); }
				$image   = ! empty( $meta_data['featured_image'] ) ? $meta_data['featured_image'] : 'https://via.placeholder.com/400x300?text=No+Image';
				$price   = (float) $product->price;
				$sale    = Sale_Service::get_sale_price( $product->id );
				$in_stock = Inventory_Service::is_in_stock( $product->id );
				$wished  = Wishlist_Service::is_in_wishlist( $product->id );
				$comparing = Compare_Service::is_comparing( $product->id );
				$created  = strtotime( $product->created_at );
				$is_new   = ( time() - $created ) < ( 14 * DAY_IN_SECONDS );
			?>
				<div class="ah-listing-card">
					<?php if ( $sale !== null ) : ?><span class="ah-listing-badge ah-badge-sale">SALE</span>
					<?php elseif ( $is_new ) : ?><span class="ah-listing-badge ah-badge-new">NEW</span>
					<?php elseif ( ! $in_stock ) : ?><span class="ah-listing-badge ah-badge-oos">OUT OF STOCK</span>
					<?php endif; ?>
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $product->title ); ?>">
					<div class="ah-listing-card-body">
						<h3 class="ah-listing-card-title"><?php echo esc_html( $product->title ); ?></h3>
						<div class="ah-listing-card-price">
							$<?php echo number_format( $sale !== null ? $sale : $price, 2 ); ?>
							<?php if ( $sale !== null ) : ?><span class="orig">$<?php echo number_format( $price, 2 ); ?></span><?php endif; ?>
						</div>
						<div class="ah-listing-actions">
							<button class="ah-add-to-cart-btn" data-product_id="<?php echo esc_attr( $product->id ); ?>" <?php echo ! $in_stock ? 'disabled' : ''; ?>>
								<?php echo $in_stock ? 'Add to Cart' : 'Out of Stock'; ?>
							</button>
							<?php if ( $atts['show_wishlist'] === 'yes' ) : ?>
								<button class="ah-wishlist-toggle <?php echo $wished ? 'active' : ''; ?>" data-product-id="<?php echo esc_attr( $product->id ); ?>" title="Wishlist">
									<?php echo $wished ? '♥' : '♡'; ?>
								</button>
							<?php endif; ?>
							<?php if ( $atts['show_compare'] === 'yes' ) : ?>
								<button class="ah-compare-toggle <?php echo $comparing ? 'active' : ''; ?>" data-product-id="<?php echo esc_attr( $product->id ); ?>" title="Compare">
									⚖
								</button>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="ah-listing-paginate">
				<?php for ( $i = 1; $i <= $total_pages; $i++ ) :
					$url = add_query_arg( array_merge( array(
						'ah_page' => $i,
						'ah_search' => $search,
						'ah_sort' => $sort,
						'ah_category' => $filter_cat,
						'ah_price_min' => $filter_price_min,
						'ah_price_max' => $filter_price_max,
					) ), $base_url );
				?>
					<a href="<?php echo esc_url( $url ); ?>" class="<?php echo $i === $current_page ? 'current' : ''; ?>"><?php echo $i; ?></a>
				<?php endfor; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('.ah-add-to-cart-btn').on('click', function(e) {
		e.preventDefault();
		var $btn = $(this);
		if ($btn.hasClass('loading') || $btn.prop('disabled')) return;
		$btn.addClass('loading').text('Adding...');
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
			action: 'ah_add_to_cart', product_id: $btn.data('product_id'), qty: 1,
			nonce: '<?php echo wp_create_nonce("ah_cart_nonce"); ?>'
		}, function(r) {
			$btn.removeClass('loading');
			if (r.success) { $btn.text('Added!'); setTimeout(function(){ $btn.text('Add to Cart'); }, 2000); }
			else { alert(r.data.message); $btn.text('Add to Cart'); }
		});
	});
	$('.ah-wishlist-toggle').on('click', function() {
		var $btn = $(this), adding = !$btn.hasClass('active');
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
			action: adding ? 'ah_add_to_wishlist' : 'ah_remove_from_wishlist',
			product_id: $btn.data('product-id'),
			nonce: '<?php echo wp_create_nonce("ah_cart_nonce"); ?>'
		}, function(r) { if (r.success) { $btn.toggleClass('active').text($btn.hasClass('active') ? '♥' : '♡'); } });
	});
	$('.ah-compare-toggle').on('click', function() {
		var $btn = $(this), adding = !$btn.hasClass('active');
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
			action: adding ? 'ah_compare_add' : 'ah_compare_remove',
			product_id: $btn.data('product-id'),
			nonce: '<?php echo wp_create_nonce("ah_cart_nonce"); ?>'
		}, function(r) {
			if (r.success || r.count !== undefined) { $btn.toggleClass('active'); }
			if (r.message) alert(r.message);
		});
	});
});
</script>
