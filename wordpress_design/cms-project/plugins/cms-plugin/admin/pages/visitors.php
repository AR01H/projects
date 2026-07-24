<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Admin\Components\AdminComponents;

$m = new AH_Visitor_Model();
$notice = '';

// Handle prune action
if ( isset( $_POST['adn_prune_visitors'], $_POST['_wpnonce'] )
	&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'adn_prune_visitors' ) ) {
	$days = (int) ( $_POST['prune_days'] ?? 90 );
	if ( 0 === $days ) {
		$pruned = $m->truncate();
		$notice = 'All visitor records deleted (' . intval( $pruned ) . ' rows removed).';
	} else {
		$pruned = $m->prune( $days );
		$notice = 'Deleted ' . intval( $pruned ) . ' records older than ' . intval( $days ) . ' days.';
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
<div class="wrap ah-wrap">
<?php AdminComponents::pageHeader( 'chart-bar', 'Visitor Stats', 'Track site visitors, page views, and referral sources in real time.' ); ?>
<?php if ( $notice ) : ?><?php AdminComponents::notice( $notice, 'success' ); ?><?php endif; ?>

<?php AdminComponents::tabBarUrl( array(
	'overview' => 'Overview',
	'pages'    => 'Top Pages',
	'monthly'  => 'Monthly',
	'ips'      => 'IP Addresses',
	'recent'   => 'Recent Visits',
	'manage'   => 'Manage',
), $tab, 'ah-visitors', 'vtab' ); ?>

<?php /* ── OVERVIEW ── */ ?>
<?php if ( 'overview' === $tab ) : ?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;margin:18px 0;">
	<?php AdminComponents::statCard( number_format( $total ), 'Total Visits', 'chart-bar' ); ?>
	<?php AdminComponents::statCard( number_format( $total_unique ), 'Unique IPs (all time)', 'admin-users' ); ?>
	<?php AdminComponents::statCard( number_format( $today ), 'Visits Today', 'chart-bar' ); ?>
	<?php AdminComponents::statCard( number_format( $today_unique ), 'Unique IPs Today', 'admin-users' ); ?>
	<?php AdminComponents::statCard( number_format( $this_month ), 'This Month', 'calendar-alt' ); ?>
</div>

<?php AdminComponents::card( 'Last 12 Months', '' ); ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;margin-top:-16px;">
<?php foreach ( array_reverse( $monthly ) as $row ) : ?>
	<div class="ah-card" style="padding:12px;text-align:center;">
		<div style="font-weight:600;font-size:0.85rem;color:var(--ah-text);"><?php echo esc_html( $row['month'] ); ?></div>
		<div style="font-size:1.4rem;font-weight:700;color:var(--ah-primary);"><?php echo number_format( (int) $row['visits'] ); ?></div>
		<div style="font-size:0.76rem;color:var(--ah-muted);"><?php echo number_format( (int) $row['unique_visitors'] ); ?> unique</div>
	</div>
<?php endforeach; ?>
<?php if ( empty( $monthly ) ) : ?>
	<p style="color:var(--ah-muted);grid-column:1/-1;">No data yet.</p>
<?php endif; ?>
</div>

<?php endif; ?>

<?php /* ── TOP PAGES ── */ ?>
<?php if ( 'pages' === $tab ) : ?>

<?php
$max_v = ! empty( $top_pages ) ? (int) $top_pages[0]['visits'] : 1;
$top_rows = array();
foreach ( $top_pages as $i => $row ) {
	$r = new \stdClass();
	$r->rank = $i + 1;
	$r->page_url = $row['page_url'];
	$r->page_slug = $row['page_slug'] ?: $row['page_url'];
	$r->visits = number_format( (int) $row['visits'] );
	$r->unique = number_format( (int) $row['unique_visitors'] );
	$r->bar_width = round( ( (int) $row['visits'] / $max_v ) * 200 );
	$r->bar_pct = round( ( (int) $row['visits'] / max( 1, $total ) ) * 100, 1 );
	$top_rows[] = $r;
}
ob_start();
AdminComponents::dataTable( array(
	'columns' => array(
		array( 'label' => '#', 'style' => 'width:40px', 'render' => function ( $r ) { return $r->rank; } ),
		array( 'label' => 'Page Slug', 'render' => function ( $r ) {
			return '<a href="' . esc_url( $r->page_url ) . '" target="_blank" rel="noopener">' . esc_html( $r->page_slug ) . '</a>';
		} ),
		array( 'label' => 'Visits', 'render' => function ( $r ) { return $r->visits; } ),
		array( 'label' => 'Unique Visitors', 'render' => function ( $r ) { return $r->unique; } ),
		array( 'label' => 'Traffic Bar', 'render' => function ( $r ) {
			return '<div style="display:flex;align-items:center;gap:8px;"><div style="height:8px;border-radius:4px;background:var(--ah-primary);width:' . esc_attr( $r->bar_width ) . 'px;"></div><span style="font-size:0.78rem;color:var(--ah-muted);">' . $r->bar_pct . '%</span></div>';
		} ),
	),
	'items'         => $top_rows,
	'empty_message' => 'No data yet.',
) );
AdminComponents::card( 'Top Pages by Visits', ob_get_clean() ); ?>

<?php endif; ?>

<?php /* ── MONTHLY ── */ ?>
<?php if ( 'monthly' === $tab ) : ?>

<?php
$monthly_rows = array();
foreach ( array_reverse( $monthly ) as $row ) {
	$r = new \stdClass();
	$r->month = $row['month'];
	$r->visits = number_format( (int) $row['visits'] );
	$r->unique = number_format( (int) $row['unique_visitors'] );
	$monthly_rows[] = $r;
}
ob_start();
AdminComponents::dataTable( array(
	'columns' => array(
		array( 'label' => 'Month', 'render' => function ( $r ) { return esc_html( $r->month ); } ),
		array( 'label' => 'Total Visits', 'render' => function ( $r ) { return $r->visits; } ),
		array( 'label' => 'Unique Visitors', 'render' => function ( $r ) { return $r->unique; } ),
	),
	'items'         => $monthly_rows,
	'empty_message' => 'No data yet.',
) );
AdminComponents::card( 'Monthly Breakdown (last 12 months)', ob_get_clean() ); ?>

<?php endif; ?>

<?php /* ── IP ADDRESSES ── */ ?>
<?php if ( 'ips' === $tab ) : ?>

<?php
$ip_rows = array();
foreach ( $ip_list as $row ) {
	$r = new \stdClass();
	$r->ip = $row['ip_address'];
	$r->visits = number_format( (int) $row['visits'] );
	$r->first = $row['first_seen'];
	$r->last = $row['last_seen'];
	$ip_rows[] = $r;
}
ob_start();
AdminComponents::dataTable( array(
	'columns' => array(
		array( 'label' => 'IP Address', 'render' => function ( $r ) { return '<code>' . esc_html( $r->ip ) . '</code>'; } ),
		array( 'label' => 'Total Visits', 'render' => function ( $r ) { return $r->visits; } ),
		array( 'label' => 'First Seen', 'render' => function ( $r ) { return esc_html( $r->first ); } ),
		array( 'label' => 'Last Seen', 'render' => function ( $r ) { return esc_html( $r->last ); } ),
	),
	'items'         => $ip_rows,
	'empty_message' => 'No data yet.',
) );
AdminComponents::card( 'IP Address Summary (top 100)', ob_get_clean() ); ?>

<?php endif; ?>

<?php /* ── RECENT VISITS ── */ ?>
<?php if ( 'recent' === $tab ) : ?>

<?php
$recent_rows = array();
foreach ( $recent as $row ) {
	$r = new \stdClass();
	$r->time = $row['visited_at'];
	$r->ip = $row['ip_address'];
	$r->page_url = $row['page_url'];
	$r->page_slug = $row['page_slug'] ?: $row['page_url'];
	$r->referrer = $row['referrer'] ?: '-';
	$recent_rows[] = $r;
}
ob_start();
AdminComponents::dataTable( array(
	'columns' => array(
		array( 'label' => 'Time', 'style' => 'white-space:nowrap', 'render' => function ( $r ) { return esc_html( $r->time ); } ),
		array( 'label' => 'IP', 'render' => function ( $r ) { return '<code>' . esc_html( $r->ip ) . '</code>'; } ),
		array( 'label' => 'Page', 'render' => function ( $r ) {
			return '<a href="' . esc_url( $r->page_url ) . '" target="_blank" rel="noopener">' . esc_html( $r->page_slug ) . '</a>';
		} ),
		array( 'label' => 'Referrer', 'style' => 'font-size:0.8rem;color:var(--ah-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;', 'render' => function ( $r ) {
			return '<span title="' . esc_attr( $r->referrer ) . '">' . esc_html( $r->referrer ) . '</span>';
		} ),
	),
	'items'         => $recent_rows,
	'empty_message' => 'No data yet.',
) );
AdminComponents::card( 'Last 30 Visits', ob_get_clean() ); ?>

<?php endif; ?>

<?php /* ── MANAGE ── */ ?>
<?php if ( 'manage' === $tab ) : ?>

<?php ob_start(); ?>
<form method="post">
	<?php wp_nonce_field( 'adn_prune_visitors' ); ?>
	<?php
	$prune_select = '<select name="prune_days" id="prune_days" style="max-width:400px;">'
		. '<option value="0">Everything (clear all)</option>'
		. '<option value="1">1 day</option>'
		. '<option value="7">1 week</option>'
		. '<option value="30">30 days</option>'
		. '<option value="60">60 days</option>'
		. '<option value="90" selected>90 days</option>'
		. '<option value="180">180 days</option>'
		. '<option value="365">1 year</option>'
		. '</select>';
	AdminComponents::formRow( 'Delete records older than', $prune_select );
	?>
	<p>
		<button type="submit" name="adn_prune_visitors" class="ah-btn ah-btn-danger ah-confirm-delete" data-title="Prune Visitor Records" data-confirm="Old visitor logs will be deleted permanently.">
			Prune Records
		</button>
	</p>
</form>
<?php AdminComponents::card( 'Prune Old Records', ob_get_clean() ); ?>

<?php
global $wpdb;
$tbl  = $wpdb->prefix . 'ah_visitor_logs';
$size = $wpdb->get_row( $wpdb->prepare(
	"SELECT table_rows AS rows, ROUND((data_length + index_length)/1024/1024, 2) AS size_mb
	 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = %s",
	$tbl
), ARRAY_A );
if ( $size ) {
	AdminComponents::card( 'Current Database Size', '<p>Table <code>' . esc_html( $tbl ) . '</code>: approx. <strong>' . esc_html( number_format( (int) $size['rows'] ) ) . ' rows</strong>, <strong>' . esc_html( $size['size_mb'] ) . ' MB</strong>.</p>' );
}
?>

<?php endif; ?>

</div>
