<?php
/**
 * Widget: Product Q&A — questions and answers
 * Include in product detail pages.
 *
 * Expected variables: $product_id (int)
 */
defined( 'ABSPATH' ) || exit;

use AHEcommerce\Commerce\QA\QA_Service;

$product_id = (int) ( $product_id ?? 0 );
if ( ! $product_id ) return;

$page = max( 1, (int) ( $_GET['qa_page'] ?? 1 ) );
$qa   = QA_Service::get_questions( $product_id, $page );
$nonce = wp_create_nonce( 'ah_cart_nonce' );
?>
<div id="ah-qa-section" style="margin-top:30px;">
	<h2 style="font-size:20px; margin-bottom:15px;">Questions & Answers (<?php echo $qa['meta']['total_items']; ?>)</h2>

	<?php if ( ! empty( $qa['items'] ) ) : ?>
		<div style="display:flex; flex-direction:column; gap:15px;">
			<?php foreach ( $qa['items'] as $q ) : ?>
				<div style="padding:15px; background:#f9fafb; border-radius:8px; border:1px solid #e5e7eb;">
					<div style="display:flex; gap:10px; align-items:flex-start;">
						<span style="background:#2563eb; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:700; white-space:nowrap;">Q</span>
						<div>
							<p style="margin:0 0 4px; font-weight:600;"><?php echo wp_kses_post( $q->question ); ?></p>
							<p style="margin:0; font-size:12px; color:#9ca3af;">by <?php echo esc_html( $q->questioner_name ); ?> · <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $q->created_at ) ) ); ?></p>
						</div>
					</div>
					<?php if ( ! empty( $q->answers ) ) : ?>
						<div style="margin-top:10px; margin-left:40px;">
							<?php foreach ( $q->answers as $a ) : ?>
								<div style="display:flex; gap:10px; align-items:flex-start; margin-top:8px; padding-top:8px; border-top:1px solid #e5e7eb;">
									<span style="background:<?php echo $a->is_admin ? '#16a34a' : '#6b7280'; ?>; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:700; white-space:nowrap;">A<?php echo $a->is_admin ? ' (Staff)' : ''; ?></span>
									<div>
										<p style="margin:0;"><?php echo wp_kses_post( $a->answer ); ?></p>
										<p style="margin:4px 0 0; font-size:12px; color:#9ca3af;"><?php echo esc_html( $a->answerer_name ); ?> · <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $a->created_at ) ) ); ?></p>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $qa['meta']['total_pages'] > 1 ) : ?>
			<div style="display:flex; gap:8px; justify-content:center; margin-top:20px;">
				<?php for ( $i = 1; $i <= $qa['meta']['total_pages']; $i++ ) :
					$url = add_query_arg( array( 'qa_page' => $i ), get_permalink() );
				?>
					<a href="<?php echo esc_url( $url ); ?>" style="padding:6px 12px; border:1px solid #d1d5db; border-radius:4px; text-decoration:none; <?php echo $i === $page ? 'background:#111827; color:#fff;' : ''; ?>"><?php echo $i; ?></a>
				<?php endfor; ?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<p style="color:#9ca3af;">No questions yet. Be the first to ask!</p>
	<?php endif; ?>

	<!-- Ask a Question Form -->
	<div style="margin-top:25px; padding:20px; background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px;">
		<h3 style="margin:0 0 12px; font-size:16px;">Ask a Question</h3>
		<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
			<input type="text" id="ah-qa-name" placeholder="Your Name *" required style="padding:8px 12px; border:1px solid #ccc; border-radius:4px;">
			<input type="email" id="ah-qa-email" placeholder="Your Email *" required style="padding:8px 12px; border:1px solid #ccc; border-radius:4px;">
		</div>
		<textarea id="ah-qa-question" rows="3" placeholder="Your question *" required style="width:100%; padding:8px 12px; border:1px solid #ccc; border-radius:4px; margin-bottom:10px; box-sizing:border-box;"></textarea>
		<button id="ah-qa-submit" style="padding:10px 20px; background:#2563eb; color:#fff; border:none; border-radius:4px; cursor:pointer; font-weight:600;">Submit Question</button>
		<p id="ah-qa-msg" style="margin:10px 0 0; font-size:13px; display:none;"></p>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#ah-qa-submit').on('click', function() {
		var name = $('#ah-qa-name').val().trim();
		var email = $('#ah-qa-email').val().trim();
		var question = $('#ah-qa-question').val().trim();
		if (!name || !email || !question) { alert('Please fill all fields.'); return; }
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
			action: 'ah_question_submit',
			product_id: <?php echo $product_id; ?>,
			name: name, email: email, question: question,
			nonce: '<?php echo $nonce; ?>'
		}, function(r) {
			var $msg = $('#ah-qa-msg').show();
			$msg.text(r.data.message).css('color', r.success ? '#166534' : '#991b1b');
			if (r.success) { $('#ah-qa-name, #ah-qa-email, #ah-qa-question').val(''); }
		});
	});
});
</script>
