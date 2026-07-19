<?php
/**
 * Admin View: Sellers (Marketplace)
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1>Sellers (Marketplace Dashboard)</h1>
	
	<div class="ah-tabs">
		<div class="ah-tab-nav">
			<div class="ah-tab-btn active" data-target="tab-vendors">Vendors</div>
			<div class="ah-tab-btn" data-target="tab-payouts">Payouts</div>
			<div class="ah-tab-btn" data-target="tab-commissions">Commissions</div>
		</div>

		<div class="ah-tab-content active" id="tab-vendors">
			<h2>Vendor Management</h2>
			<div class="ah-grid-3" style="margin-bottom: 20px;">
				<div class="card" style="padding:15px; border-left: 4px solid #0073aa;"><strong>Total Vendors:</strong> 12</div>
				<div class="card" style="padding:15px; border-left: 4px solid #ffba00;"><strong>Pending Approval:</strong> 3</div>
				<div class="card" style="padding:15px; border-left: 4px solid #46b450;"><strong>Active Stores:</strong> 9</div>
			</div>
			
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Store Name</th>
						<th>Owner</th>
						<th>Products</th>
						<th>Total Sales</th>
						<th>Status</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong>TechGadgets Hub</strong></td>
						<td>user@techgadgetshub.com</td>
						<td>145</td>
						<td>$12,450.00</td>
						<td><span style="color:green; font-weight:bold;">Active</span></td>
						<td><a href="#" class="button button-small">Manage</a></td>
					</tr>
					<tr>
						<td><strong>Fashion World</strong></td>
						<td>fashion@gmail.com</td>
						<td>0</td>
						<td>$0.00</td>
						<td><span style="color:orange; font-weight:bold;">Pending</span></td>
						<td><a href="#" class="button button-small">Approve</a></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="ah-tab-content" id="tab-payouts">
			<h2>Withdrawal Requests</h2>
			<p>Vendors requesting payouts for their wallet balance.</p>
			<!-- Placeholder for payouts table -->
		</div>

		<div class="ah-tab-content" id="tab-commissions">
			<h2>Commission Settings</h2>
			<div class="ah-form-row">
				<label>Default Global Commission Rate (%)</label>
				<input type="number" step="0.1" value="10.0" class="regular-text">
				<p class="description">The default percentage taken from vendor sales.</p>
			</div>
			<button class="button button-primary">Save Settings</button>
		</div>
	</div>

	<script>
		document.querySelectorAll('.ah-tab-btn').forEach(btn => {
			btn.addEventListener('click', () => {
				document.querySelectorAll('.ah-tab-btn, .ah-tab-content').forEach(el => el.classList.remove('active'));
				btn.classList.add('active');
				document.getElementById(btn.dataset.target).classList.add('active');
			});
		});
	</script>
</div>
