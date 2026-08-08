<?php
/**
 * components/careers.php - open roles.
 *
 * GENERIC: any list of positions - counter staff, drivers, growers, office.
 * Each role expands to its own detail (a native <details>, so it works with
 * no JS at all) and applies through the shared lead form in a dialog.
 *
 * One application dialog is built per role from NT_Dialog::render_args(), so
 * the enquiry arrives already labelled with the job title - no separate
 * handler, no per-role JSON form.
 *
 * Data: admin/data/<source>.json (default `careers`)
 *   { tag, title, sub, apply_label, empty_text, form,
 *     items[] { title, location, type, salary, summary,
 *               responsibilities[], requirements[], closes } }
 *
 * Args:
 *   source string  Which JSON file to read.
 */

defined( 'ABSPATH' ) || exit;

$nt_src   = ( isset( $source ) && $source ) ? (string) $source : 'careers';
$nt_data  = app_data( $nt_src );
$nt_items = ( is_array( $nt_data ) && ! empty( $nt_data['items'] ) ) ? (array) $nt_data['items'] : array();

$nt_tag   = (string) ( $nt_data['tag'] ?? '' );
$nt_title = (string) ( $nt_data['title'] ?? '' );
$nt_sub   = (string) ( $nt_data['sub'] ?? '' );
$nt_apply = (string) ( $nt_data['apply_label'] ?? NT_Ui::label( 'apply' ) );
$nt_form  = (string) ( $nt_data['form'] ?? 'form_apply' );
$nt_empty = (string) ( $nt_data['empty_text'] ?? '' );

if ( empty( $nt_items ) && '' === $nt_empty ) {
	return;
}
?>
<section class="app-careers" id="<?php echo esc_attr( sanitize_html_class( $nt_src ) ); ?>">
	<div class="container">

		<?php if ( $nt_tag || $nt_title || $nt_sub ) : ?>
			<div class="app-section-center">
				<?php if ( $nt_tag ) : ?><div class="app-section-tag"><?php echo esc_html( $nt_tag ); ?></div><?php endif; ?>
				<?php if ( $nt_title ) : ?><h2 class="section-title"><?php echo wp_kses( $nt_title, array( 'em' => array() ) ); ?></h2><?php endif; ?>
				<?php if ( $nt_sub ) : ?><p class="section-body"><?php echo esc_html( $nt_sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( empty( $nt_items ) ) : ?>
			<?php
			// No openings today - say so warmly rather than rendering an
			// empty shelf, and keep the speculative route open.
			app_alert( array(
				'tone'       => 'note',
				'icon'       => 'briefcase',
				'body'       => $nt_empty,
				'link_label' => $nt_data['empty_link_label'] ?? '',
				'link_url'   => $nt_data['empty_link_url'] ?? '',
			) );
			?>
		<?php else : ?>
			<div class="app-careers__list">
				<?php
				foreach ( $nt_items as $nt_i => $nt_job ) :
					$nt_job      = (array) $nt_job;
					$nt_job_name = trim( (string) ( $nt_job['title'] ?? '' ) );
					if ( '' === $nt_job_name ) {
						continue;
					}
					// One application dialog per role, registered as DATA. The
					// browser builds it when the button is pressed, and the job
					// title travels with the enquiry - so the shared lead
					// handler needs no per-role code and the page carries no
					// dialog markup for roles nobody opens.
					$nt_job_dialog = app_dialog_add( 'apply-' . sanitize_title( $nt_job_name ) . '-' . $nt_i, array(
						'kicker' => (string) ( $nt_data['dialog_kicker'] ?? '' ),
						'title'  => $nt_job_name,
						'tone'   => 'question',
						'icon'   => 'briefcase',
						'size'   => 'md',
						'body'   => (string) ( $nt_data['dialog_text'] ?? '' ),
						'form'   => $nt_form,
					) );
					?>
					<details class="app-job">
						<summary class="app-job__summary">
							<span class="app-job__head">
								<span class="app-job__title"><?php echo esc_html( $nt_job_name ); ?></span>
								<span class="app-job__meta">
									<?php if ( ! empty( $nt_job['location'] ) ) : ?>
										<span class="app-job__chip"><?php NT_Icons::render( 'pin' ); ?><?php echo esc_html( $nt_job['location'] ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $nt_job['type'] ) ) : ?>
										<span class="app-job__chip"><?php NT_Icons::render( 'clock' ); ?><?php echo esc_html( $nt_job['type'] ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $nt_job['salary'] ) ) : ?>
										<span class="app-job__chip"><?php NT_Icons::render( 'coin' ); ?><?php echo esc_html( $nt_job['salary'] ); ?></span>
									<?php endif; ?>
								</span>
							</span>
							<span class="app-job__toggle" aria-hidden="true"><?php NT_Icons::render( 'chevron-down' ); ?></span>
						</summary>

						<div class="app-job__body">
							<?php if ( ! empty( $nt_job['summary'] ) ) : ?>
								<p class="app-job__text"><?php echo esc_html( $nt_job['summary'] ); ?></p>
							<?php endif; ?>

							<?php
							foreach ( array(
								'responsibilities' => (string) ( $nt_data['labels']['responsibilities'] ?? '' ),
								'requirements'     => (string) ( $nt_data['labels']['requirements'] ?? '' ),
							) as $nt_list_key => $nt_list_label ) :
								if ( empty( $nt_job[ $nt_list_key ] ) ) {
									continue;
								}
								?>
								<div class="app-job__block">
									<?php if ( '' !== $nt_list_label ) : ?>
										<h4 class="app-job__subhead"><?php echo esc_html( $nt_list_label ); ?></h4>
									<?php endif; ?>
									<ul class="app-job__points">
										<?php foreach ( (array) $nt_job[ $nt_list_key ] as $nt_point ) : ?>
											<li><?php NT_Icons::render( 'check' ); ?><span><?php echo esc_html( $nt_point ); ?></span></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endforeach; ?>

							<div class="app-job__foot">
								<?php if ( ! empty( $nt_job['closes'] ) ) : ?>
									<span class="app-job__closes">
										<?php NT_Icons::render( 'calendar' ); ?>
										<?php echo esc_html( $nt_job['closes'] ); ?>
									</span>
								<?php endif; ?>

								<button type="button" class="app-job__apply"
								        data-nt-dialog-open="<?php echo esc_attr( $nt_job_dialog ); ?>"
								        aria-haspopup="dialog">
									<?php echo esc_html( $nt_apply ); ?>
									<?php NT_Icons::render( 'arrow-right' ); ?>
								</button>
							</div>
						</div>
					</details>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
