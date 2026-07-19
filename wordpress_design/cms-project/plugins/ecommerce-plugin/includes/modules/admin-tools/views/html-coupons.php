<?php
/**
 * Admin View: Coupons / Discounts
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-tickets-alt"></span> <?php esc_html_e( 'Coupons & Discounts', 'ah-ecommerce' ); ?></h1>
	
	<div class="ah-tabs">
		<div class="ah-tab-nav" style="display:flex; gap:15px; margin-bottom:20px; border-bottom:1px solid #ccc;">
			<div class="ah-tab-btn active" data-target="tab-standard" style="padding:10px; cursor:pointer;">Standard Coupons</div>
			<div class="ah-tab-btn" data-target="tab-bogo" style="padding:10px; cursor:pointer;">Buy X Get Y (BOGO)</div>
			<div class="ah-tab-btn" data-target="tab-flash" style="padding:10px; cursor:pointer;">Flash Sales</div>
			<div class="ah-tab-btn" data-target="tab-tier" style="padding:10px; cursor:pointer;">Tier / Bulk Discounts</div>
		</div>

		<!-- STANDARD COUPONS -->
		<div class="ah-tab-content active" id="tab-standard">
			<div class="ah-table-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
				<form class="ah-search-form" method="get">
					<input type="hidden" name="page" value="ah-coupons">
					<input type="search" name="s" value="" placeholder="Search Coupons…">
					<button class="button">Filter</button>
				</form>
				<button class="button button-primary">+ Add New Coupon</button>
			</div>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Code</th>
						<th>Coupon Type</th>
						<th>Coupon Amount</th>
						<th>Usage / Limit</th>
						<th>Expiry Date</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong>SUMMER20</strong></td>
						<td>Percentage discount</td>
						<td>20%</td>
						<td>14 / 100</td>
						<td>Dec 31, 2026</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- BOGO -->
		<div class="ah-tab-content" id="tab-bogo" style="display:none;">
			<h2>Buy X Get Y Rules</h2>
			<p>Configure complex Buy-1-Get-1 or Buy-2-Get-50%-Off rules.</p>
			<div class="card" style="padding:15px; border-left:4px solid #0073aa;">
				<div class="ah-form-row">
					<label>Rule Name</label>
					<input type="text" value="Buy 2 Shirts Get 1 Free" class="regular-text">
				</div>
				<div class="ah-form-row" style="margin-top:10px;">
					<label>Condition: Customer Buys (Quantity)</label>
					<input type="number" value="2" class="small-text"> 
					<span>of Category: <select><option>Shirts</option></select></span>
				</div>
				<div class="ah-form-row" style="margin-top:10px;">
					<label>Reward: Customer Gets (Quantity)</label>
					<input type="number" value="1" class="small-text"> 
					<span>at Discount: <select><option>100% (Free)</option><option>50%</option></select></span>
				</div>
				<button class="button button-secondary" style="margin-top:15px;">Save Rule</button>
			</div>
		</div>

		<!-- FLASH SALES -->
		<div class="ah-tab-content" id="tab-flash" style="display:none;">
			<h2>Flash Sales & Daily Deals</h2>
			<p>Schedule automatic price drops with countdown timers.</p>
			<div class="ah-grid-2" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
				<div class="card" style="padding:15px; border:1px solid #ccd0d4;">
					<h3>Weekend Blowout</h3>
					<p>Status: <span style="color:orange;">Scheduled</span></p>
					<p>Starts: Friday 12:00 AM<br>Ends: Sunday 11:59 PM</p>
					<p>Discount: Flat 30% Site-wide</p>
					<button class="button button-small">Edit Event</button>
				</div>
			</div>
			<button class="button button-primary" style="margin-top:15px;">+ Schedule Flash Sale</button>
		</div>

		<!-- TIER DISCOUNTS -->
		<div class="ah-tab-content" id="tab-tier" style="display:none;">
			<h2>Tier / Bulk Pricing</h2>
			<p>Automatically discount orders based on cart total or quantity.</p>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Spend Over ($)</th>
						<th>Or Buy Quantity</th>
						<th>Automatic Discount</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>$500.00</td>
						<td>10+ Items</td>
						<td>15% Off Cart</td>
						<td><a href="#" class="button button-small">Edit</a></td>
					</tr>
					<tr>
						<td>$1000.00</td>
						<td>25+ Items</td>
						<td>25% Off Cart + Free Shipping</td>
						<td><a href="#" class="button button-small">Edit</a></td>
					</tr>
				</tbody>
			</table>
			<button class="button button-primary" style="margin-top:15px;">+ Add Tier Rule</button>
		</div>

	</div>

	<script>
		document.querySelectorAll('.ah-tab-btn').forEach(btn => {
			btn.addEventListener('click', () => {
				document.querySelectorAll('.ah-tab-btn').forEach(el => el.classList.remove('active'));
				document.querySelectorAll('.ah-tab-content').forEach(el => el.style.display = 'none');
				btn.classList.add('active');
				document.getElementById(btn.dataset.target).style.display = 'block';
			});
		});
	</script>
</div>
