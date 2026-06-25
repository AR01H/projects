<?php
defined( 'ABSPATH' ) || exit;

$m = new AH_Visitor_Model();

// Handle prune action
if ( isset( $_POST['adn_prune_visitors'], $_POST['_wpnonce'] )
	&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'adn_prune_visitors' ) ) {
	$days = (int) ( $_POST['prune_days'] ?? 90 );
	if ( 0 === $days ) {
		$pruned = $m->truncate();
		echo '<div class="notice notice-success is-dismissible"><p>All visitor records deleted (' . intval( $pruned ) . ' rows removed).</p></div>';
	} else {
		$pruned = $m->prune( $days );
		echo '<div class="notice notice-success is-dismissible"><p>Deleted ' . intval( $pruned ) . ' records older than ' . intval( $days ) . ' days.</p></div>';
	}
}

// Stats
$total         = $m->total();
$total_unique  = $m->total_unique();
$today         = $m->today();
$today_unique  = $m->today_unique();
$this_month    = $m->this_month();
$monthly       = $m->monthly( 12 );
$top_pages     = $m->top_pages( 25 );
$ip_list       = $m->ip_summary( 100 );
$recent        = $m->recent( 30 );

// Active tab
$tab = sanitize_key( $_GET['vtab'] ?? 'overview' );
?>
<div class="wrap">
<h1 style="display:flex;align-items:center;gap:10px;">
	<span dashicons dashicons-chart-bar style="font-size:28px;"></span>
	Visitor Stats
</h1>

<style>
.vs-stat-row { display:flex; gap:16px; flex-wrap:wrap; margin:18px 0; }
.vs-stat-card { background:#fff; border:1px solid #ddd; border-radius:8px; padding:16px 22px; min-width:130px; flex:1; }
.vs-stat-card .vs-num { font-size:2rem; font-weight:700; color:#1e3a2f; line-height:1.1; }
.vs-stat-card .vs-label { font-size:0.78rem; color:#6b7280; margin-top:4px; }
.vs-tabs { display:flex; gap:0; border-bottom:2px solid #ddd; margin-bottom:20px; }
.vs-tab { padding:9px 18px; font-size:0.88rem; font-weight:500; color:#555; text-decoration:none; border-bottom:2px solid transparent; margin-bottom:-2px; }
.vs-tab.active { color:#1e3a2f; border-bottom-color:#1e3a2f; font-weight:600; }
.vs-tab:hover { color:#1e3a2f; }
.vs-table { width:100%; border-collapse:collapse; font-size:0.87rem; }
.vs-table th { background:#f3f4f6; padding:8px 12px; text-align:left; font-weight:600; color:#374151; border-bottom:2px solid #e5e7eb; }
.vs-table td { padding:7px 12px; border-bottom:1px solid #f0f0f0; color:#374151; }
.vs-table tr:hover td { background:#fafafa; }
.vs-bar-wrap { display:flex; align-items:center; gap:8px; }
.vs-bar { height:8px; border-radius:4px; background:#1e3a2f; min-width:2px; }
.vs-monthly-grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(160px,1fr)); gap:10px; margin-top:10px; }
.vs-month-card { background:#fff; border:1px solid #e5e7eb; border-radius:6px; padding:12px; }
.vs-month-card .vs-mn { font-weight:600; font-size:0.85rem; color:#374151; }
.vs-month-card .vs-mc { font-size:1.4rem; font-weight:700; color:#1e3a2f; }
.vs-month-card .vs-mu { font-size:0.76rem; color:#9ca3af; }
</style>

<?php
$base_url = admin_url( 'admin.php?page=ah-visitors' );
$tabs = array(
	'overview' => 'Overview',
	'pages'    => 'Top Pages',
	'monthly'  => 'Monthly',
	'ips'      => 'IP Addresses',
	'recent'   => 'Recent Visits',
	'manage'   => 'Manage',
);
echo '<div class="vs-tabs">';
foreach ( $tabs as $key => $label ) {
	$active = $tab === $key ? ' active' : '';
	echo '<a href="' . esc_url( $base_url . '&vtab=' . $key ) . '" class="vs-tab' . $active . '">' . esc_html( $label ) . '</a>';
}
echo '</div>';
?>

<?php /* ── OVERVIEW ── */ ?>
<?php if ( 'overview' === $tab ) : ?>

<div class="vs-stat-row">
	<div class="vs-stat-card"><div class="vs-num"><?php echo number_format( $total ); ?></div><div class="vs-label">Total Visits</div></div>
	<div class="vs-stat-card"><div class="vs-num"><?php echo number_format( $total_unique ); ?></div><div class="vs-label">Unique IPs (all time)</div></div>
	<div class="vs-stat-card"><div class="vs-num"><?php echo number_format( $today ); ?></div><div class="vs-label">Visits Today</div></div>
	<div class="vs-stat-card"><div class="vs-num"><?php echo number_format( $today_unique ); ?></div><div class="vs-label">Unique IPs Today</div></div>
	<div class="vs-stat-card"><div class="vs-num"><?php echo number_format( $this_month ); ?></div><div class="vs-label">This Month</div></div>
</div>

<h3>Last 12 Months</h3>
<div class="vs-monthly-grid">
<?php foreach ( array_reverse( $monthly ) as $row ) : ?>
	<div class="vs-month-card">
		<div class="vs-mn"><?php echo esc_html( $row['month'] ); ?></div>
		<div class="vs-mc"><?php echo number_format( (int) $row['visits'] ); ?></div>
		<div class="vs-mu"><?php echo number_format( (int) $row['unique_visitors'] ); ?> unique</div>
	</div>
<?php endforeach; ?>
<?php if ( empty( $monthly ) ) : ?>
	<p style="color:#9ca3af;grid-column:1/-1;">No data yet.</p>
<?php endif; ?>
</div>

<?php endif; ?>

<?php /* ── TOP PAGES ── */ ?>
<?php if ( 'pages' === $tab ) : ?>

<h3>Top Pages by Visits</h3>
<?php
$max_v = ! empty( $top_pages ) ? (int) $top_pages[0]['visits'] : 1;
?>
<table class="vs-table">
	<thead><tr>
		<th>#</th><th>Page Slug</th><th>Visits</th><th>Unique Visitors</th><th>Traffic Bar</th>
	</tr></thead>
	<tbody>
	<?php foreach ( $top_pages as $i => $row ) : ?>
		<tr>
			<td><?php echo $i + 1; ?></td>
			<td><a href="<?php echo esc_url( $row['page_url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $row['page_slug'] ?: $row['page_url'] ); ?></a></td>
			<td><?php echo number_format( (int) $row['visits'] ); ?></td>
			<td><?php echo number_format( (int) $row['unique_visitors'] ); ?></td>
			<td>
				<div class="vs-bar-wrap">
					<div class="vs-bar" style="width:<?php echo esc_attr( round( ( (int) $row['visits'] / $max_v ) * 200 ) ); ?>px;"></div>
					<span style="font-size:0.78rem;color:#9ca3af;"><?php echo round( ( (int) $row['visits'] / max( 1, $total ) ) * 100, 1 ); ?>%</span>
				</div>
			</td>
		</tr>
	<?php endforeach; ?>
	<?php if ( empty( $top_pages ) ) : ?><tr><td colspan="5" style="color:#9ca3af;text-align:center;padding:24px;">No data yet.</td></tr><?php endif; ?>
	</tbody>
</table>

<?php endif; ?>

<?php /* ── MONTHLY ── */ ?>
<?php if ( 'monthly' === $tab ) : ?>

<h3>Monthly Breakdown (last 12 months)</h3>
<table class="vs-table">
	<thead><tr><th>Month</th><th>Total Visits</th><th>Unique Visitors</th></tr></thead>
	<tbody>
	<?php foreach ( array_reverse( $monthly ) as $row ) : ?>
		<tr>
			<td><?php echo esc_html( $row['month'] ); ?></td>
			<td><?php echo number_format( (int) $row['visits'] ); ?></td>
			<td><?php echo number_format( (int) $row['unique_visitors'] ); ?></td>
		</tr>
	<?php endforeach; ?>
	<?php if ( empty( $monthly ) ) : ?><tr><td colspan="3" style="color:#9ca3af;text-align:center;padding:24px;">No data yet.</td></tr><?php endif; ?>
	</tbody>
</table>

<?php endif; ?>

<?php /* ── IP ADDRESSES ── */ ?>
<?php if ( 'ips' === $tab ) : ?>

<h3>IP Address Summary (top 100)</h3>
<table class="vs-table">
	<thead><tr><th>IP Address</th><th>Total Visits</th><th>First Seen</th><th>Last Seen</th></tr></thead>
	<tbody>
	<?php foreach ( $ip_list as $row ) : ?>
		<tr>
			<td><code><?php echo esc_html( $row['ip_address'] ); ?></code></td>
			<td><?php echo number_format( (int) $row['visits'] ); ?></td>
			<td><?php echo esc_html( $row['first_seen'] ); ?></td>
			<td><?php echo esc_html( $row['last_seen'] ); ?></td>
		</tr>
	<?php endforeach; ?>
	<?php if ( empty( $ip_list ) ) : ?><tr><td colspan="4" style="color:#9ca3af;text-align:center;padding:24px;">No data yet.</td></tr><?php endif; ?>
	</tbody>
</table>

<?php endif; ?>

<?php /* ── RECENT VISITS ── */ ?>
<?php if ( 'recent' === $tab ) : ?>

<h3>Last 30 Visits</h3>
<table class="vs-table">
	<thead><tr><th>Time</th><th>IP</th><th>Page</th><th>Referrer</th></tr></thead>
	<tbody>
	<?php foreach ( $recent as $row ) : ?>
		<tr>
			<td style="white-space:nowrap;"><?php echo esc_html( $row['visited_at'] ); ?></td>
			<td><code><?php echo esc_html( $row['ip_address'] ); ?></code></td>
			<td><a href="<?php echo esc_url( $row['page_url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $row['page_slug'] ?: $row['page_url'] ); ?></a></td>
			<td style="font-size:0.8rem;color:#9ca3af;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo esc_attr( $row['referrer'] ); ?>"><?php echo esc_html( $row['referrer'] ?: '—' ); ?></td>
		</tr>
	<?php endforeach; ?>
	<?php if ( empty( $recent ) ) : ?><tr><td colspan="4" style="color:#9ca3af;text-align:center;padding:24px;">No data yet.</td></tr><?php endif; ?>
	</tbody>
</table>

<?php endif; ?>

<?php /* ── MANAGE ── */ ?>
<?php if ( 'manage' === $tab ) : ?>

<h3>Prune Old Records</h3>
<p style="color:#6b7280;font-size:0.88rem;">Delete visitor logs older than a set number of days. This cannot be undone.</p>
<form method="post">
	<?php wp_nonce_field( 'adn_prune_visitors' ); ?>
	<table class="form-table" style="max-width:400px;">
		<tr>
			<th><label for="prune_days">Delete records older than</label></th>
			<td>
				<select name="prune_days" id="prune_days">
					<option value="0">Everything (clear all)</option>
					<option value="1">1 day</option>
					<option value="7">1 week</option>
					<option value="30">30 days</option>
					<option value="60">60 days</option>
					<option value="90" selected>90 days</option>
					<option value="180">180 days</option>
					<option value="365">1 year</option>
				</select>
			</td>
		</tr>
	</table>
	<p>
		<button type="submit" name="adn_prune_visitors" class="button button-secondary" onclick="return confirm('Delete old visitor records? This cannot be undone.');">
			Prune Records
		</button>
	</p>
</form>

<hr style="margin:28px 0;">
<h3>Current Database Size</h3>
<?php
global $wpdb;
$tbl  = $wpdb->prefix . 'ah_visitor_logs';
$size = $wpdb->get_row( $wpdb->prepare(
	"SELECT table_rows AS rows, ROUND((data_length + index_length)/1024/1024, 2) AS size_mb
	 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = %s",
	$tbl
), ARRAY_A );
if ( $size ) {
	echo '<p>Table <code>' . esc_html( $tbl ) . '</code>: approx. <strong>' . esc_html( number_format( (int) $size['rows'] ) ) . ' rows</strong>, <strong>' . esc_html( $size['size_mb'] ) . ' MB</strong>.</p>';
}
?>

<?php endif; ?>

</div>
