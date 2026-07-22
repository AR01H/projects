<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use AHEcommerce\Commerce\QA\QA_Service;

$tab    = sanitize_key( $_GET['tab'] ?? 'pending' );
$notice = '';

if ( isset( $_GET['action'] ) && isset( $_GET['qid'] ) ) {
	check_admin_referer( 'ah_qa_action_' . $_GET['qid'] );
	$sub_action = sanitize_text_field( $_GET['action'] );
	$qid        = (int) $_GET['qid'];
	switch ( $sub_action ) {
		case 'approve': QA_Service::approve_question( $qid ); $notice = 'Question approved.'; break;
		case 'reject':  QA_Service::reject_question( $qid );  $notice = 'Question rejected.';  break;
		case 'delete':  QA_Service::delete_question( $qid );  $notice = 'Question deleted.';   break;
	}
}

$page = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
if ( $tab === 'pending' ) {
	$result = QA_Service::get_pending( $page );
} else {
	global $wpdb;
	$q_table = $wpdb->prefix . 'ah_ecommerce_product_questions';
	$where   = $tab === 'approved' ? "WHERE q.status = 'approved'" : '';
	$offset  = ( $page - 1 ) * 20;
	$result  = array(
		'items' => $wpdb->get_results( "SELECT q.*, p.title AS product_name FROM {$q_table} q LEFT JOIN {$wpdb->prefix}ah_ecommerce_products p ON q.product_id = p.id {$where} ORDER BY q.created_at DESC LIMIT 20 OFFSET {$offset}" ),
		'meta'  => array( 'current_page' => $page, 'per_page' => 20, 'total_items' => 0, 'total_pages' => 1 ),
	);
}
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-admin-comments"></span> <?php esc_html_e( 'Product Q&A', 'ah-ecommerce' ); ?></h1>
	<?php if ( $notice ) : ?><div class="ah-notice ah-notice-success" style="padding:10px; background:#d4edda; color:#155724; border-left:4px solid #28a745; margin-bottom:20px;"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

	<h2 class="nav-tab-wrapper">
		<a href="?page=ah-qa&tab=pending" class="nav-tab <?php echo $tab === 'pending' ? 'nav-tab-active' : ''; ?>">Pending</a>
		<a href="?page=ah-qa&tab=approved" class="nav-tab <?php echo $tab === 'approved' ? 'nav-tab-active' : ''; ?>">Approved</a>
		<a href="?page=ah-qa&tab=all" class="nav-tab <?php echo $tab === 'all' ? 'nav-tab-active' : ''; ?>">All</a>
	</h2>

	<?php foreach ( $result['items'] as $q ) : ?>
		<div style="background:#fff; padding:15px; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:15px;">
			<div style="display:flex; justify-content:space-between; align-items:flex-start;">
				<div>
					<strong style="font-size:15px;"><?php echo esc_html( $q->question ); ?></strong>
					<p style="margin:4px 0 0; font-size:13px; color:#6b7280;">
						Product: <strong><?php echo esc_html( $q->product_name ?? '#' . $q->product_id ); ?></strong>
						· by <?php echo esc_html( $q->questioner_name ); ?>
						(<?php echo esc_html( $q->questioner_email ); ?>)
						· <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $q->created_at ) ) ); ?>
					</p>
				</div>
				<div style="display:flex; gap:5px; white-space:nowrap;">
					<?php if ( $q->status !== 'approved' ) : ?>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( "admin.php?page=ah-qa&action=approve&qid={$q->id}" ), "ah_qa_action_{$q->id}" ) ); ?>" class="button button-small">Approve</a>
					<?php endif; ?>
					<?php if ( $q->status !== 'rejected' ) : ?>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( "admin.php?page=ah-qa&action=reject&qid={$q->id}" ), "ah_qa_action_{$q->id}" ) ); ?>" class="button button-small">Reject</a>
					<?php endif; ?>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( "admin.php?page=ah-qa&action=delete&qid={$q->id}" ), "ah_qa_action_{$q->id}" ) ); ?>" class="button button-small" style="color:#dc3232;" onclick="return confirm('Delete?');">Delete</a>
				</div>
			</div>

			<!-- Answer form -->
			<div style="margin-top:12px; padding:12px; background:#f0f9ff; border-radius:6px;">
				<strong>Answer this question:</strong>
				<textarea id="ah-qa-answer-<?php echo $q->id; ?>" rows="2" style="width:100%; margin:6px 0; padding:8px; border:1px solid #ccc; border-radius:4px;" placeholder="Type your answer..."></textarea>
				<button class="button button-primary ah-qa-answer-btn" data-qid="<?php echo $q->id; ?>">Post Answer</button>
			</div>

			<!-- Existing answers -->
			<?php
			global $wpdb;
			$a_table = $wpdb->prefix . 'ah_ecommerce_product_answers';
			$answers = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$a_table} WHERE question_id = %d ORDER BY created_at ASC", $q->id ) );
			if ( ! empty( $answers ) ) :
			?>
				<div style="margin-top:10px; padding-left:20px;">
					<?php foreach ( $answers as $a ) : ?>
						<div style="padding:8px 0; border-top:1px solid #e5e7eb; display:flex; gap:8px;">
							<span style="background:<?php echo $a->is_admin ? '#16a34a' : '#6b7280'; ?>; color:#fff; padding:2px 8px; border-radius:3px; font-size:11px; height:fit-content;">
								<?php echo $a->is_admin ? 'Staff' : 'User'; ?>
							</span>
							<div>
								<p style="margin:0;"><?php echo wp_kses_post( $a->answer ); ?></p>
								<p style="margin:2px 0 0; font-size:11px; color:#9ca3af;"><?php echo esc_html( $a->answerer_name ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>

	<?php if ( empty( $result['items'] ) ) : ?>
		<p style="text-align:center; color:#9ca3af; padding:40px;">No questions found.</p>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('.ah-qa-answer-btn').on('click', function() {
		var qid = $(this).data('qid');
		var answer = $('#ah-qa-answer-' + qid).val().trim();
		if (!answer) { alert('Please type an answer.'); return; }
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
			action: 'ah_question_answer',
			question_id: qid,
			answer: answer,
			nonce: '<?php echo wp_create_nonce("ah_cart_nonce"); ?>'
		}, function(r) {
			if (r.success) { window.location.reload(); }
			else { alert(r.message || 'Failed.'); }
		});
	});
});
</script>
