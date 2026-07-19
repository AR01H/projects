<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use AHEcommerce\Database\Product_Repository;

$repo   = new Product_Repository();
$notice = '';
$action = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['ah_product_nonce'] ?? '', 'ah_save_product' ) ) wp_die( 'Security check failed.' );
	
	$data = array(
		'title'       => sanitize_text_field( $_POST['title'] ?? '' ),
		'description' => wp_kses_post( $_POST['description'] ?? '' ),
		'type'        => sanitize_key( $_POST['type'] ?? 'simple' ),
		'sku'         => sanitize_text_field( $_POST['sku'] ?? '' ),
		'price'       => isset( $_POST['price'] ) && $_POST['price'] !== '' ? floatval( $_POST['price'] ) : null,
		'status'      => sanitize_key( $_POST['status'] ?? 'draft' ),
	);
	
	if ( $edit_id ) {
		$repo->update( $edit_id, $data );
		$product_id = $edit_id;
		$notice = 'Product updated successfully.';
	} else {
		$product_id = $repo->insert( $data );
		$notice = 'Product created successfully.';
	}
	
	// Save Meta Data
	if ( $product_id ) {
		$meta_fields = array(
			'subtitle', 'barcode', 'gtin', 'isbn', 'upc', 'mpn', 'manufacturer', 'supplier',
			'highlights', 'features', 'specifications', 'ingredients', 'material',
			'seo_title', 'seo_desc', 'admin_notes',
			'permalink', 'visibility_admin', 'visibility_theme', 'searchable',
			'linked_categories', 'linked_tags', 'linked_upsells', 'linked_crosssells',
			'linked_brands', 'linked_occasions', 'linked_festivals', 'dynamic_rules',
			'sale_price', 'wholesale_price', 'pdf_manuals', 'certificates', 'downloadable_files',
			'enable_reviews', 'avg_rating', 'review_count', 'delivery_method', 'custom_attributes'
		);
		foreach ( $meta_fields as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$val = is_array( $_POST[ $key ] ) ? array_map( 'wp_kses_post', $_POST[ $key ] ) : wp_kses_post( $_POST[ $key ] );
				$repo->update_meta( $product_id, $key, $val );
			}
		}
	}
	
	$action = 'list';
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_product' ) ) {
	$repo->delete( (int) $_GET['delete_id'] );
	$notice = 'Product deleted successfully.';
}

?>
<style>
	.ah-tabs { display: flex; border-bottom: 1px solid #ccd0d4; margin-bottom: 20px; }
	.ah-tab-btn { padding: 10px 20px; cursor: pointer; border: 1px solid transparent; border-bottom: none; background: #f1f1f1; margin-right: 5px; border-radius: 4px 4px 0 0; font-weight: 600; }
	.ah-tab-btn.active { background: #fff; border-color: #ccd0d4; margin-bottom: -1px; }
	.ah-tab-content { display: none; padding: 10px 0; }
	.ah-tab-content.active { display: block; }
	.ah-form-wrap { background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
	.ah-form-row { margin-bottom: 20px; }
	.ah-form-row label { display: block; font-weight: bold; margin-bottom: 5px; }
	.ah-form-row input[type="text"], .ah-form-row input[type="number"], .ah-form-row select, .ah-form-row textarea { width: 100%; max-width: 600px; }
	.ah-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 800px; }
	.ah-grid-2 input, .ah-grid-2 select { width: 100%; max-width: 100%; }
</style>

<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-products"></span> <?php esc_html_e( 'Products', 'ah-ecommerce' ); ?></h1>
	<?php if ( $notice ) : ?><div class="ah-notice ah-notice-success" style="padding:10px; background:#d4edda; color:#155724; border-left:4px solid #28a745; margin-bottom: 20px;"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

	<?php if ( $action === 'list' ) :
		$search = sanitize_text_field( $_GET['s'] ?? '' );
		$paged  = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
		$result = $repo->get_paginated( $paged, 20, $search );
		$items  = $result['items']; 
	?>
		<div class="ah-table-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
			<form class="ah-search-form" method="get">
				<input type="hidden" name="page" value="ah-products">
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search Products…">
				<button class="ah-btn ah-btn-secondary button">Filter</button>
			</form>
			<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-products', 'action' => 'add' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary button button-primary">+ Add Product</a>
		</div>
		<div class="ah-table-wrap">
			<table class="ah-table wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Title</th>
						<th>SKU</th>
						<th>Price</th>
						<th>Type</th>
						<th>Status</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $items ) ) : ?>
						<tr><td colspan="6">No products found.</td></tr>
					<?php else : foreach ( $items as $product ) : ?>
						<tr data-id="<?php echo esc_attr( $product->id ); ?>">
							<td><strong><?php echo esc_html( $product->title ); ?></strong></td>
							<td><?php echo $product->sku ? esc_html( $product->sku ) : '<span style="color:#a0a0a0;">-</span>'; ?></td>
							<td><?php echo $product->price ? esc_html( '$' . number_format( $product->price, 2 ) ) : '<span style="color:#a0a0a0;">-</span>'; ?></td>
							<td><span class="ah-badge" style="background:#e2e8f0; padding:3px 8px; border-radius:4px; font-size:12px;"><?php echo esc_html( ucfirst( $product->type ) ); ?></span></td>
							<td><span class="ah-badge ah-badge-<?php echo esc_attr( $product->status ); ?>" style="background: <?php echo $product->status === 'published' ? '#dcfce7' : '#fef3c7'; ?>; color: <?php echo $product->status === 'published' ? '#166534' : '#92400e'; ?>; padding:3px 8px; border-radius:4px; font-size:12px;"><?php echo esc_html( ucfirst( $product->status ) ); ?></span></td>
							<td class="row-actions">
								<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-products', 'action' => 'edit', 'id' => $product->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm button button-small">Edit</a>
								<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-products', 'delete_id' => $product->id ), admin_url( 'admin.php' ) ), 'ah_del_product' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm button button-small" style="color:#dc3232; border-color:#dc3232;" onclick="return confirm('Delete product?');">Delete</a>
							</td>
						</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
		
	<?php elseif ( $action === 'add' || $action === 'edit' ) : 
		$product = null;
		$meta = array();
		if ( $edit_id ) {
			$product = $repo->get( $edit_id );
			if ( ! $product ) {
				echo '<div class="ah-notice ah-notice-error">Product not found.</div>';
				return;
			}
			$meta = (array) $product->meta;
		}
	?>
		<div class="ah-form-wrap">
			<form method="post" class="ah-form">
				<?php wp_nonce_field( 'ah_save_product', 'ah_product_nonce' ); ?>
				
				<div class="ah-form-actions" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
					<h2><?php echo $edit_id ? 'Edit Product' : 'Add New Product'; ?></h2>
					<div>
						<button type="submit" class="button button-primary button-large">Save Product</button>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-products' ) ); ?>" class="button button-secondary button-large">Cancel</a>
					</div>
				</div>

				<div class="ah-tabs">
					<div class="ah-tab-btn active" data-target="tab-general">General</div>
					<div class="ah-tab-btn" data-target="tab-inventory">Inventory</div>
					<div class="ah-tab-btn" data-target="tab-attributes">Attributes</div>
					<div class="ah-tab-btn" data-target="tab-variations">Variations</div>
					<div class="ah-tab-btn" data-target="tab-linked">Linked Items</div>
					<div class="ah-tab-btn" data-target="tab-media">Media</div>
					<div class="ah-tab-btn" data-target="tab-seo">SEO</div>
					<div class="ah-tab-btn" data-target="tab-reviews">Reviews</div>
					<div class="ah-tab-btn" data-target="tab-notes">Notes</div>
				</div>

				<!-- GENERAL TAB -->
				<div class="ah-tab-content active" id="tab-general">
					<div class="ah-form-row">
						<label>Product Title <span class="required" style="color:red;">*</span></label>
						<input type="text" name="title" value="<?php echo esc_attr( $product->title ?? '' ); ?>" required class="large-text regular-text">
					</div>
					<div class="ah-form-row">
						<label>Permalink (Slug)</label>
						<input type="text" name="permalink" value="<?php echo esc_attr( $meta['permalink'] ?? '' ); ?>" class="regular-text" placeholder="e.g. awesome-product">
					</div>
					<div class="ah-form-row">
						<label>Subtitle</label>
						<input type="text" name="subtitle" value="<?php echo esc_attr( $meta['subtitle'] ?? '' ); ?>" class="large-text regular-text">
					</div>
					<div class="ah-form-row">
						<label>Description (Long)</label>
						<?php wp_editor( $product->description ?? '', 'product_desc', array( 'textarea_name' => 'description', 'media_buttons' => true ) ); ?>
					</div>
					<div class="ah-grid-2" style="grid-template-columns: 1fr 1fr 1fr;">
						<div class="ah-form-row">
							<label>Regular Price</label>
							<input type="number" step="0.01" name="price" value="<?php echo esc_attr( $product->price ?? '' ); ?>" class="regular-text">
						</div>
						<div class="ah-form-row">
							<label>Sale Price</label>
							<input type="number" step="0.01" name="sale_price" value="<?php echo esc_attr( $meta['sale_price'] ?? '' ); ?>" class="regular-text">
						</div>
						<div class="ah-form-row">
							<label>Wholesale Price</label>
							<input type="number" step="0.01" name="wholesale_price" value="<?php echo esc_attr( $meta['wholesale_price'] ?? '' ); ?>" class="regular-text">
						</div>
					</div>
					<div class="ah-grid-2">
						<div class="ah-form-row">
							<label>Product Type</label>
							<select name="type">
								<option value="simple" <?php selected( $product->type ?? '', 'simple' ); ?>>Simple Product</option>
								<option value="variable" <?php selected( $product->type ?? '', 'variable' ); ?>>Variable Product</option>
								<option value="grouped" <?php selected( $product->type ?? '', 'grouped' ); ?>>Grouped Product</option>
								<option value="bundle" <?php selected( $product->type ?? '', 'bundle' ); ?>>Bundle</option>
								<option value="digital" <?php selected( $product->type ?? '', 'digital' ); ?>>Digital Product</option>
							</select>
						</div>
						<div class="ah-form-row">
							<label>Status</label>
							<select name="status">
								<option value="public" <?php selected( $product->status ?? '', 'public' ); ?>>Public (Published)</option>
								<option value="draft" <?php selected( $product->status ?? '', 'draft' ); ?>>Draft</option>
								<option value="pending" <?php selected( $product->status ?? '', 'pending' ); ?>>Pending Review</option>
								<option value="inactive" <?php selected( $product->status ?? '', 'inactive' ); ?>>Inactive (Hidden)</option>
								<option value="archived" <?php selected( $product->status ?? '', 'archived' ); ?>>Archived</option>
							</select>
						</div>
					</div>
					<div class="ah-form-row">
						<label>Visibility Settings</label>
						<label style="font-weight:normal; display:inline-block; margin-right: 15px;"><input type="checkbox" name="visibility_admin" value="1" <?php checked( $meta['visibility_admin'] ?? '', '1' ); ?>> Visible in Admin</label>
						<label style="font-weight:normal; display:inline-block; margin-right: 15px;"><input type="checkbox" name="visibility_theme" value="1" <?php checked( $meta['visibility_theme'] ?? '', '1' ); ?>> Visible in Theme</label>
						<label style="font-weight:normal; display:inline-block; margin-right: 15px;"><input type="checkbox" name="searchable" value="1" <?php checked( $meta['searchable'] ?? '', '1' ); ?>> Searchable</label>
					</div>
				</div>

				<!-- INVENTORY TAB -->
				<div class="ah-tab-content" id="tab-inventory">
					<div class="ah-form-row">
						<label>Delivery Method</label>
						<select name="delivery_method">
							<option value="physical" <?php selected( $meta['delivery_method'] ?? '', 'physical' ); ?>>Physical Shipping</option>
							<option value="digital" <?php selected( $meta['delivery_method'] ?? '', 'digital' ); ?>>Digital Download</option>
							<option value="email" <?php selected( $meta['delivery_method'] ?? '', 'email' ); ?>>Email Delivery (Gift Card / Coupon)</option>
							<option value="local" <?php selected( $meta['delivery_method'] ?? '', 'local' ); ?>>Local Pickup / Service</option>
						</select>
					</div>
					<div class="ah-grid-2">
						<div class="ah-form-row">
							<label>SKU (Stock Keeping Unit)</label>
							<input type="text" name="sku" value="<?php echo esc_attr( $product->sku ?? '' ); ?>" class="regular-text">
						</div>
						<div class="ah-form-row">
							<label>Barcode</label>
							<input type="text" name="barcode" value="<?php echo esc_attr( $meta['barcode'] ?? '' ); ?>" class="regular-text">
						</div>
						<div class="ah-form-row">
							<label>GTIN</label>
							<input type="text" name="gtin" value="<?php echo esc_attr( $meta['gtin'] ?? '' ); ?>" class="regular-text">
						</div>
						<div class="ah-form-row">
							<label>ISBN</label>
							<input type="text" name="isbn" value="<?php echo esc_attr( $meta['isbn'] ?? '' ); ?>" class="regular-text">
						</div>
						<div class="ah-form-row">
							<label>UPC</label>
							<input type="text" name="upc" value="<?php echo esc_attr( $meta['upc'] ?? '' ); ?>" class="regular-text">
						</div>
						<div class="ah-form-row">
							<label>MPN</label>
							<input type="text" name="mpn" value="<?php echo esc_attr( $meta['mpn'] ?? '' ); ?>" class="regular-text">
						</div>
						<div class="ah-form-row">
							<label>Manufacturer</label>
							<input type="text" name="manufacturer" value="<?php echo esc_attr( $meta['manufacturer'] ?? '' ); ?>" class="regular-text">
						</div>
						<div class="ah-form-row">
							<label>Supplier</label>
							<input type="text" name="supplier" value="<?php echo esc_attr( $meta['supplier'] ?? '' ); ?>" class="regular-text">
						</div>
					</div>
				</div>

				<!-- ATTRIBUTES TAB -->
				<div class="ah-tab-content" id="tab-attributes">
					<div class="ah-form-row">
						<label>Highlights (One per line)</label>
						<textarea name="highlights" rows="3"><?php echo esc_textarea( $meta['highlights'] ?? '' ); ?></textarea>
					</div>
					<div class="ah-form-row">
						<label>Features</label>
						<textarea name="features" rows="4"><?php echo esc_textarea( $meta['features'] ?? '' ); ?></textarea>
					</div>
					<div class="ah-form-row">
						<label>Specifications</label>
						<textarea name="specifications" rows="4"><?php echo esc_textarea( $meta['specifications'] ?? '' ); ?></textarea>
					</div>
					<div class="ah-grid-2">
						<div class="ah-form-row">
							<label>Ingredients</label>
							<textarea name="ingredients" rows="3"><?php echo esc_textarea( $meta['ingredients'] ?? '' ); ?></textarea>
						</div>
						<div class="ah-form-row">
							<label>Material</label>
							<textarea name="material" rows="3"><?php echo esc_textarea( $meta['material'] ?? '' ); ?></textarea>
						</div>
					</div>
					<div class="ah-form-row">
						<label>Custom Attributes (e.g., Color: Red | Size: XL)</label>
						<textarea name="custom_attributes" rows="5" placeholder="Key: Value (One per line)"><?php echo esc_textarea( $meta['custom_attributes'] ?? '' ); ?></textarea>
						<p class="description">Define custom arbitrary attributes for this product. Format as Key: Value</p>
					</div>
				</div>

				<!-- VARIATIONS TAB (Amazon Style) -->
				<div class="ah-tab-content" id="tab-variations">
					<div class="ah-notice ah-notice-info">
						<strong>Variable Product Grid:</strong> Define options like Color and Size, then generate variations to set unique SKUs, Prices, and Stock levels for every possible combination (e.g., Red-Large).
					</div>
					<div class="ah-form-row">
						<label>Variant Options (e.g. Color: Red, Blue | Size: S, M, L)</label>
						<textarea name="variant_options" rows="3" placeholder="Color: Red, Blue, Green\nSize: S, M, L"></textarea>
					</div>
					<div class="ah-form-row">
						<button type="button" class="button button-secondary">Generate Variation Grid</button>
					</div>
					<table class="ah-table wp-list-table widefat fixed striped" style="margin-top: 15px;">
						<thead>
							<tr>
								<th>Variant</th>
								<th>SKU</th>
								<th>Price</th>
								<th>Stock</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<tr><td colspan="5" style="text-align: center;">Click Generate to build the variant combinations matrix.</td></tr>
						</tbody>
					</table>
				</div>

				<!-- LINKED ITEMS TAB -->
				<div class="ah-tab-content" id="tab-linked">
					<div class="ah-grid-2">
						<div class="ah-form-row">
							<label>Linked Brands</label>
							<input type="text" name="linked_brands" value="<?php echo esc_attr( $meta['linked_brands'] ?? '' ); ?>" class="regular-text" placeholder="e.g. Apple, Nike">
						</div>
						<div class="ah-form-row">
							<label>Linked Occasions</label>
							<input type="text" name="linked_occasions" value="<?php echo esc_attr( $meta['linked_occasions'] ?? '' ); ?>" class="regular-text" placeholder="e.g. Wedding, Birthday">
						</div>
						<div class="ah-form-row">
							<label>Linked Festivals</label>
							<input type="text" name="linked_festivals" value="<?php echo esc_attr( $meta['linked_festivals'] ?? '' ); ?>" class="regular-text" placeholder="e.g. Diwali, Christmas">
						</div>
						<div class="ah-form-row">
							<label>Dynamic Product Rules</label>
							<input type="text" name="dynamic_rules" value="<?php echo esc_attr( $meta['dynamic_rules'] ?? '' ); ?>" class="regular-text" placeholder="Bundle Component IDs">
						</div>
					</div>
					<div class="ah-form-row">
						<label>Linked Categories (IDs or Slugs)</label>
						<input type="text" name="linked_categories" value="<?php echo esc_attr( $meta['linked_categories'] ?? '' ); ?>" class="regular-text" placeholder="e.g. 10, 15, electronics">
						<p class="description">Select the categories this product belongs to.</p>
					</div>
					<div class="ah-form-row">
						<label>Tags (Comma separated)</label>
						<input type="text" name="linked_tags" value="<?php echo esc_attr( $meta['linked_tags'] ?? '' ); ?>" class="regular-text" placeholder="e.g. sale, featured, new">
					</div>
					<div class="ah-form-row">
						<label>Upsells (Product IDs)</label>
						<input type="text" name="linked_upsells" value="<?php echo esc_attr( $meta['linked_upsells'] ?? '' ); ?>" class="regular-text" placeholder="e.g. 101, 105">
						<p class="description">Products which you recommend instead of the currently viewed product, for example, products that are more profitable or better quality or more expensive.</p>
					</div>
					<div class="ah-form-row">
						<label>Cross-sells (Product IDs)</label>
						<input type="text" name="linked_crosssells" value="<?php echo esc_attr( $meta['linked_crosssells'] ?? '' ); ?>" class="regular-text" placeholder="e.g. 50, 52">
						<p class="description">Products which you promote in the cart, based on the current product.</p>
					</div>
				</div>

				<!-- MEDIA TAB -->
				<div class="ah-tab-content" id="tab-media">
					<div class="ah-form-row" style="display:flex; align-items:center; gap:10px;">
						<div style="flex:1;">
							<label>Featured Image URL (or Media ID)</label>
							<input type="text" id="featured_image" name="featured_image" value="<?php echo esc_attr( $meta['featured_image'] ?? '' ); ?>">
						</div>
						<button type="button" class="button button-secondary ah-upload-btn" data-target="#featured_image" style="margin-top:20px;">Upload Image</button>
					</div>
					<div class="ah-form-row">
						<label>Gallery (Comma separated Media IDs or URLs)</label>
						<div style="display:flex; gap:10px;">
							<textarea id="gallery" name="gallery" rows="3" style="flex:1;"><?php echo esc_textarea( $meta['gallery'] ?? '' ); ?></textarea>
							<button type="button" class="button button-secondary ah-upload-btn" data-target="#gallery" data-multiple="true" style="align-self:flex-start;">Upload Images</button>
						</div>
					</div>
					<div class="ah-form-row">
						<label>Video URL (YouTube/Vimeo)</label>
						<input type="text" name="video_url" value="<?php echo esc_attr( $meta['video_url'] ?? '' ); ?>">
					</div>
					<div class="ah-form-row">
						<label>3D Model URL (.glb or .gltf)</label>
						<div style="display:flex; gap:10px;">
							<input type="text" id="model_3d_url" name="model_3d_url" value="<?php echo esc_attr( $meta['model_3d_url'] ?? '' ); ?>" style="flex:1;">
							<button type="button" class="button button-secondary ah-upload-btn" data-target="#model_3d_url">Upload 3D Model</button>
						</div>
					</div>
					<div class="ah-form-row">
						<label>PDF Manuals (Comma separated Media IDs or URLs)</label>
						<div style="display:flex; gap:10px;">
							<input type="text" id="pdf_manuals" name="pdf_manuals" value="<?php echo esc_attr( $meta['pdf_manuals'] ?? '' ); ?>" style="flex:1;">
							<button type="button" class="button button-secondary ah-upload-btn" data-target="#pdf_manuals" data-multiple="true">Upload PDFs</button>
						</div>
					</div>
					<div class="ah-form-row">
						<label>Certificates (Comma separated Media IDs or URLs)</label>
						<input type="text" name="certificates" value="<?php echo esc_attr( $meta['certificates'] ?? '' ); ?>">
					</div>
					<div class="ah-form-row">
						<label>Downloadable Files (For Digital Delivery - Media IDs or URLs)</label>
						<div style="display:flex; gap:10px;">
							<input type="text" id="downloadable_files" name="downloadable_files" value="<?php echo esc_attr( $meta['downloadable_files'] ?? '' ); ?>" style="flex:1;">
							<button type="button" class="button button-secondary ah-upload-btn" data-target="#downloadable_files" data-multiple="true">Upload File</button>
						</div>
					</div>
				</div>

				<!-- SEO TAB -->
				<div class="ah-tab-content" id="tab-seo">
					<div class="ah-form-row">
						<label>SEO Meta Title</label>
						<input type="text" name="seo_title" value="<?php echo esc_attr( $meta['seo_title'] ?? '' ); ?>">
					</div>
					<div class="ah-form-row">
						<label>SEO Meta Description</label>
						<textarea name="seo_desc" rows="3"><?php echo esc_textarea( $meta['seo_desc'] ?? '' ); ?></textarea>
					</div>
				</div>

				<!-- REVIEWS TAB -->
				<div class="ah-tab-content" id="tab-reviews">
					<div class="ah-form-row">
						<label style="font-weight:normal;"><input type="checkbox" name="enable_reviews" value="1" <?php checked( $meta['enable_reviews'] ?? '', '1' ); ?>> Enable Product Reviews</label>
					</div>
					<div class="ah-grid-2">
						<div class="ah-form-row">
							<label>Rating Override (e.g., 4.5)</label>
							<input type="number" step="0.1" max="5" min="0" name="avg_rating" value="<?php echo esc_attr( $meta['avg_rating'] ?? '' ); ?>" class="regular-text">
							<p class="description">Manually force the average rating.</p>
						</div>
						<div class="ah-form-row">
							<label>Review Count Override</label>
							<input type="number" step="1" name="review_count" value="<?php echo esc_attr( $meta['review_count'] ?? '' ); ?>" class="regular-text">
						</div>
					</div>
					<hr>
					<h2>Individual Reviews Management</h2>
					<p>Manage actual customer reviews, add custom comments, and review images.</p>
					
					<div class="card" style="padding:15px; margin-bottom: 20px;">
						<h3>+ Add Individual Review</h3>
						<div class="ah-grid-2" style="margin-bottom:10px;">
							<div><label>Reviewer Name</label><input type="text" class="regular-text"></div>
							<div><label>Rating (1-5)</label><input type="number" step="1" max="5" min="1" value="5" class="small-text"></div>
						</div>
						<div style="margin-bottom:10px;">
							<label>Review Comment</label>
							<textarea rows="3" style="width:100%;"></textarea>
						</div>
						<div style="margin-bottom:10px; display:flex; gap:10px; align-items:center;">
							<div style="flex:1;"><label>Review Image URL</label><input type="text" class="regular-text" style="width:100%;" id="review_img_input"></div>
							<button type="button" class="button ah-upload-btn" data-target="#review_img_input" style="margin-top:15px;">Upload Image</button>
						</div>
						<button type="button" class="button button-primary">Save Review</button>
					</div>

					<table class="ah-table wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th>Reviewer</th>
								<th>Rating</th>
								<th>Comment</th>
								<th>Image</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><strong>John Doe</strong></td>
								<td>⭐⭐⭐⭐⭐</td>
								<td>Great product, highly recommend it!</td>
								<td>-</td>
								<td><a href="#" class="button button-small">Delete</a></td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- NOTES TAB -->
				<div class="ah-tab-content" id="tab-notes">
					<div class="ah-form-row">
						<label>Admin Internal Notes</label>
						<textarea name="admin_notes" rows="5"><?php echo esc_textarea( $meta['admin_notes'] ?? '' ); ?></textarea>
					</div>
				</div>
				
			</form>
		</div>
		<script>
			// Tabs Logic
			document.querySelectorAll('.ah-tab-btn').forEach(btn => {
				btn.addEventListener('click', () => {
					document.querySelectorAll('.ah-tab-btn, .ah-tab-content').forEach(el => el.classList.remove('active'));
					btn.classList.add('active');
					document.getElementById(btn.dataset.target).classList.add('active');
				});
			});

			// WP Media Uploader Logic
			jQuery(document).ready(function($){
				$('.ah-upload-btn').click(function(e) {
					e.preventDefault();
					var button = $(this);
					var target = $(button.data('target'));
					var isMultiple = button.data('multiple') ? true : false;
					
					var mediaUploader = wp.media({
						title: 'Select Media',
						button: { text: 'Use this media' },
						multiple: isMultiple
					});
					
					mediaUploader.on('select', function() {
						var attachments = mediaUploader.state().get('selection').map(
							function(attachment) {
								attachment = attachment.toJSON();
								return attachment.url; // Or attachment.id
							}
						);
						
						var currentVal = target.val();
						if(isMultiple && currentVal) {
							target.val(currentVal + ', ' + attachments.join(', '));
						} else {
							target.val(attachments.join(', '));
						}
					});
					
					mediaUploader.open();
				});
			});
		</script>
	<?php endif; ?>
</div>
