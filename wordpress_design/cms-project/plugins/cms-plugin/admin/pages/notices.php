<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

// Load media uploader assets on add/edit screens.
if ( in_array( sanitize_key( $_GET['action'] ?? 'list' ), array( 'add', 'edit' ), true ) ) {
	wp_enqueue_media();
}

$model   = new AH_Site_Notices_Model();
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );
$flash   = sanitize_text_field( $_GET['flash'] ?? '' );

// Server-side validation errors returned via redirect query arg from bootstrap handler.
$errors = array();
if ( ! empty( $_GET['err'] ) ) {
	foreach ( explode( '|', rawurldecode( sanitize_text_field( $_GET['err'] ) ) ) as $e ) {
		$e = trim( $e );
		if ( $e !== '' ) $errors[] = $e;
	}
}
?>
<div class="wrap ah-wrap">
	<?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'megaphone', 'Site Notices', 'Display sitewide announcement bars with scheduling and targeting.' ); ?>
	<?php
	$_flash_map = array( 'saved' => 'Notice saved.', 'deleted' => 'Notice deleted.', 'updated' => 'Notice updated.' );
	if ( $flash && isset( $_flash_map[ $flash ] ) ) :
		\Ah\Cms\Admin\Components\AdminComponents::notice( $_flash_map[ $flash ], 'success' );
	endif;
	?>
	<?php if ( ! empty( $errors ) ) : ?>
	<?php \Ah\Cms\Admin\Components\AdminComponents::notice( 'Please fix: ' . implode( ', ', $errors ), 'error' ); ?>
	<?php endif; ?>

<?php if ( $action === 'list' ) :
	$search = sanitize_text_field( $_GET['s'] ?? '' );
	$status = sanitize_key( $_GET['status'] ?? '' );
	$result = $model->get_paginated_list( AH_Pagination::current_page(), $search, $status );
	$items  = $result['items']; $meta = $result['meta'];
?>
	<?php \Ah\Cms\Admin\Components\AdminComponents::filterBar( array(
		'page_slug'          => 'ah-notices',
		'search_placeholder' => 'Search notices…',
		'search_value'       => $search,
		'filters'            => array(
			array(
				'name'     => 'status',
				'options'  => array( '' => 'All Status', 'active' => 'Active', 'inactive' => 'Inactive' ),
				'selected' => $status,
			),
		),
		'add_url'   => add_query_arg( array( 'page' => 'ah-notices', 'action' => 'add' ), admin_url( 'admin.php' ) ),
		'add_label' => '+ Add Notice',
	) ); ?>

	<?php \Ah\Cms\Admin\Components\AdminComponents::dataTable( array(
		'columns' => array(
			array( 'label' => 'Title', 'render' => function ( $item ) {
				$html = '<strong>' . esc_html( $item->title ) . '</strong>';
				if ( $item->message ) {
					$html .= '<br><small style="color:var(--ah-muted);">' . esc_html( wp_trim_words( $item->message, 10 ) ) . '</small>';
				}
				return $html;
			} ),
			array( 'label' => 'Trigger', 'render' => function ( $item ) {
				$html = '';
				if ( $item->trigger_type === 'immediate' ) $html .= '<span class="ah-badge">On Load</span>';
				if ( $item->trigger_type === 'exit-intent' ) $html .= '<span class="ah-badge" style="background:#fef9c3;color:#92400e;">Exit Intent</span>';
				if ( $item->trigger_type === 'delay' ) $html .= '<span class="ah-badge" style="background:#ede9fe;color:#5b21b6;">After ' . (int) $item->trigger_delay . 's</span>';
				return $html;
			} ),
			array( 'label' => 'Scope', 'render' => function ( $item ) {
				if ( $item->scope === 'slugs' && $item->slugs ) {
					return '<small style="font-family:monospace;">' . esc_html( $item->slugs ) . '</small>';
				}
				return '<em style="color:var(--ah-muted);">All pages</em>';
			} ),
			array( 'label' => 'Frequency', 'render' => function ( $item ) {
				$_freq_labels = array(
					'daily'     => 'Daily',
					'weekly'    => 'Weekly',
					'session'   => 'Per session',
					'once_ever' => 'Once ever',
					'always'    => 'Every visit',
				);
				if ( $item->frequency === 'custom' ) {
					$_cm = (int) ( $item->frequency_custom_mins ?? 60 );
					return esc_html( 'Every ' . floor( $_cm / 60 ) . 'h ' . ( $_cm % 60 ) . 'm' );
				}
				return esc_html( $_freq_labels[ $item->frequency ] ?? ucfirst( $item->frequency ) );
			} ),
			array( 'label' => 'Status', 'render' => function ( $item ) {
				return \Ah\Cms\Admin\Components\AdminComponents::statusBadge( $item->status );
			} ),
		),
		'items'         => $items,
		'empty_message' => 'No notices yet. Click + Add Notice to create one.',
		'actions'       => function ( $item ) {
			$edit_url = add_query_arg( array( 'page' => 'ah-notices', 'action' => 'edit', 'id' => $item->id ), admin_url( 'admin.php' ) );
			$toggle_url = esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'ah_toggle_notice', 'toggle_id' => $item->id ), admin_url( 'admin-post.php' ) ), 'ah_toggle_sn' ) );
			$del_url = esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'ah_delete_notice', 'delete_id' => $item->id ), admin_url( 'admin-post.php' ) ), 'ah_del_sn' ) );
			$toggle_label = $item->status === 'active' ? 'Pause' : 'Enable';
			return '<a href="' . esc_url( $edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>'
				 . '<a href="' . $toggle_url . '" class="ah-btn ah-btn-secondary ah-btn-sm">' . esc_html( $toggle_label ) . '</a>'
				 . '<a href="' . $del_url . '" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete &quot;' . esc_attr( $item->title ) . '&quot;" data-confirm="This notice will be permanently removed.">Delete</a>';
			// Note: cannot use confirmDelete() here because the URL is already built in the callback
		},
	) ); ?>

	<?php echo AH_Pagination::render( $meta ); ?>

<?php else :
	$item = $edit_id ? $model->find( $edit_id ) : null;
?>
	<div class="nl-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px;">
		<?php \Ah\Cms\Admin\Components\AdminComponents::backLink( admin_url( 'admin.php?page=ah-notices' ), '← Back to notices' ); ?>
		<h2 style="margin:0;font-size:1.15rem;"><?php echo $item ? 'Edit Notice' : 'New Notice'; ?></h2>
		<div style="display:flex;gap:8px;">
			<?php if ( $item ) : ?>
			<?php \Ah\Cms\Admin\Components\AdminComponents::confirmDelete(
				admin_url( 'admin-post.php' ) . '?action=ah_delete_notice&delete_id=' . $item->id,
				'ah_del_sn'
			); ?>
			<?php endif; ?>
			<button type="submit" form="ah-notice-form" class="ah-btn ah-btn-primary ah-btn-sm">Save Notice</button>
		</div>
	</div>

	<?php if ( ! empty( $errors ) ) : ?>
	<?php \Ah\Cms\Admin\Components\AdminComponents::notice( 'Please fix: ' . implode( ', ', $errors ), 'error' ); ?>
	<?php endif; ?>

	<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start;">

		<!-- ── Left: form fields ──────────────────────────────────────────── -->
		<form id="ah-notice-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action"  value="ah_save_notice">
			<input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
			<?php wp_nonce_field( 'ah_save_site_notice', 'ah_sn_nonce' ); ?>

			<!-- Content -->
			<?php
			$_badge_name_hex = array( 'green'=>'#15803d','red'=>'#b91c1c','blue'=>'#1d4ed8','orange'=>'#c2410c','purple'=>'#7c3aed' );
			$_badge_raw      = $item->badge_color ?? 'green';
			$_badge_hex      = $_badge_name_hex[ $_badge_raw ] ?? ( preg_match( '/^#[0-9a-fA-F]{6}$/', $_badge_raw ) ? $_badge_raw : '#15803d' );
			?>
			<?php ob_start(); ?>
				<?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
					array( 'Title *', '<input type="text" id="pv-title" name="title" value="' . esc_attr( $item->title ?? '' ) . '" placeholder="e.g. Limited Time Offer" required>' ),
					array( 'Status',
						'<select name="status">'
						. '<option value="active"' . selected( $item->status ?? 'active', 'active', false ) . '>&#9679; Active</option>'
						. '<option value="inactive"' . selected( $item->status ?? '', 'inactive', false ) . '>&#9675; Draft</option>'
						. '</select>'
					),
				) ); ?>

				<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Message <small>(short description below the title)</small>', '<textarea id="pv-message" name="message" rows="2" placeholder="e.g. Get 20% off all bookings this weekend">' . esc_textarea( $item->message ?? '' ) . '</textarea>' ); ?>

				<?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
					array( 'Badge Label', '<input type="text" id="pv-badge-text" name="badge_text" value="' . esc_attr( $item->badge_text ?? '' ) . '" placeholder="e.g. New, Hot Deal, Important">' ),
					array( 'Badge Colour', '<div style="display:flex;gap:10px;align-items:center;margin-top:6px;"><input type="color" name="badge_color" id="pv-badge-color" value="' . esc_attr( $_badge_hex ) . '" style="width:48px;height:36px;padding:2px 3px;border:1px solid #d1d5db;border-radius:6px;cursor:pointer;background:#fff;"><span id="pv-badge-color-sample" style="padding:3px 12px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;">Badge</span></div>' ),
				) ); ?>

				<?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
					array( 'Button Label', '<input type="text" id="pv-btn-label" name="button_label" value="' . esc_attr( $item->button_label ?? '' ) . '" placeholder="e.g. Book Now">' ),
					array( 'Button URL', '<input type="text" id="pv-btn-url" name="button_url" value="' . esc_attr( $item->button_url ?? '' ) . '" placeholder="/contact or #section or https://…">' ),
				) ); ?>

				<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Banner Image <small>(optional · 520×280 recommended)</small>',
					'<div style="display:flex;gap:8px;align-items:center;">'
					. '<input type="text" id="pv-image" name="image" value="' . esc_attr( $item->image ?? '' ) . '" placeholder="Paste URL or use picker →" style="flex:1;">'
					. '<button type="button" id="ah-sn-media-btn" class="ah-btn ah-btn-secondary ah-btn-sm" style="white-space:nowrap;">&#128247; Choose</button>'
					. '</div>'
					. '<div id="pv-img-thumb" style="margin-top:6px;' . ( ( $item->image ?? '' ) ? '' : 'display:none;' ) . '">'
					. '<img src="' . esc_url( $item->image ?? '' ) . '" style="max-height:80px;border-radius:6px;border:1px solid #e5e7eb;">'
					. '<button type="button" id="ah-sn-img-clear" style="margin-left:6px;background:none;border:none;color:#b91c1c;cursor:pointer;font-size:12px;">&#10005; Remove</button>'
					. '</div>'
				); ?>
			<?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Content', ob_get_clean() ); ?>

			<!-- Appearance -->
			<?php ob_start(); ?>
				<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Popup Style',
					'<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:8px;">'
					. '<label class="ah-style-card" style="cursor:pointer;border:2px solid #e5e7eb;border-radius:10px;padding:12px;display:flex;gap:10px;align-items:flex-start;transition:border-color .15s;">'
					. '<input type="radio" name="position" value="modal" id="pv-pos-modal" ' . checked( $item->position ?? 'modal', 'modal', false ) . ' style="margin-top:3px;flex-shrink:0;">'
					. '<div><strong style="font-size:.9rem;">Centre modal</strong><br><small style="color:var(--ah-muted);">Full-screen overlay, centred popup. Great for important announcements.</small>'
					. '<div style="margin-top:8px;background:#f3f4f6;border-radius:6px;padding:8px;font-size:10px;text-align:center;color:#6b7280;"><div style="background:#fff;border-radius:4px;padding:4px 8px;display:inline-block;box-shadow:0 1px 4px rgba(0,0,0,.12);">📋 Notice popup</div></div></div></label>'
					. '<label class="ah-style-card" style="cursor:pointer;border:2px solid #e5e7eb;border-radius:10px;padding:12px;display:flex;gap:10px;align-items:flex-start;transition:border-color .15s;">'
					. '<input type="radio" name="position" value="corner" id="pv-pos-corner" ' . checked( $item->position ?? '', 'corner', false ) . ' style="margin-top:3px;flex-shrink:0;">'
					. '<div><strong style="font-size:.9rem;">Corner card</strong><br><small style="color:var(--ah-muted);">Slides up from bottom-right. No backdrop - less intrusive.</small>'
					. '<div style="margin-top:8px;background:#f3f4f6;border-radius:6px;padding:8px;text-align:right;font-size:10px;color:#6b7280;height:32px;position:relative;"><div style="background:#fff;border-radius:4px;padding:3px 7px;display:inline-block;box-shadow:0 1px 4px rgba(0,0,0,.12);position:absolute;bottom:6px;right:6px;">📌 card</div></div></div></label>'
					. '</div>'
				); ?>
			<?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Appearance', ob_get_clean() ); ?>

			<!-- Behaviour -->
			<?php ob_start(); ?>

				<?php
				$_triggers_html = '<div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;margin-top:8px;">';
				$triggers = array(
					'immediate'   => array( '⚡', 'On page load',   'Shows immediately when the page opens' ),
					'exit-intent' => array( '🚪', 'Exit intent',    'Fires when cursor moves toward the browser bar' ),
					'delay'       => array( '⏱', 'After delay',    'Wait N seconds before showing' ),
					'scroll'      => array( '📜', 'On scroll',      'Show after visitor scrolls X% down the page' ),
				);
				foreach ( $triggers as $tval => $td ) {
					$_triggers_html .= '<label class="ah-trig-card" style="cursor:pointer;border:2px solid #e5e7eb;border-radius:8px;padding:10px;display:flex;flex-direction:column;align-items:center;text-align:center;gap:4px;transition:border-color .15s;">';
					$_triggers_html .= '<input type="radio" name="trigger_type" value="' . esc_attr( $tval ) . '" ' . checked( $item->trigger_type ?? 'immediate', $tval, false ) . ' style="display:none;">';
					$_triggers_html .= '<span style="font-size:22px;">' . $td[0] . '</span>';
					$_triggers_html .= '<strong style="font-size:.82rem;">' . esc_html( $td[1] ) . '</strong>';
					$_triggers_html .= '<small style="color:var(--ah-muted);font-size:.75rem;">' . esc_html( $td[2] ) . '</small>';
					$_triggers_html .= '</label>';
				}
				$_triggers_html .= '</div>';
				$_triggers_html .= '<div id="sn-delay-row" style="margin-top:10px;display:' . ( ( $item->trigger_type ?? '' ) === 'delay' ? 'flex' : 'none' ) . ';align-items:center;gap:8px;background:#f9fafb;padding:10px 12px;border-radius:8px;">';
				$_triggers_html .= '<label style="font-size:.88rem;color:var(--ah-muted);">Show after</label>';
				$_triggers_html .= '<input type="number" name="trigger_delay" value="' . (int) ( $item->trigger_delay ?? 5 ) . '" min="1" max="300" style="width:70px;">';
				$_triggers_html .= '<label style="font-size:.88rem;color:var(--ah-muted);">seconds</label>';
				$_triggers_html .= '</div>';
				$_triggers_html .= '<div id="sn-scroll-row" style="margin-top:10px;display:' . ( ( $item->trigger_type ?? '' ) === 'scroll' ? 'flex' : 'none' ) . ';align-items:center;gap:8px;background:#f9fafb;padding:10px 12px;border-radius:8px;">';
				$_triggers_html .= '<label style="font-size:.88rem;color:var(--ah-muted);">Show after visitor scrolls</label>';
				$_triggers_html .= '<input type="number" name="trigger_scroll" value="' . (int) ( $item->trigger_scroll ?? 50 ) . '" min="0" max="100" style="width:70px;">';
				$_triggers_html .= '<label style="font-size:.88rem;color:var(--ah-muted);">% of the page</label>';
				$_triggers_html .= '</div>';
				\Ah\Cms\Admin\Components\AdminComponents::formRow( 'When to show', $_triggers_html );
			?>

				<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Which pages',
					'<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">'
					. '<label class="ah-scope-card" style="cursor:pointer;border:2px solid #e5e7eb;border-radius:8px;padding:10px 12px;display:flex;gap:8px;align-items:center;transition:border-color .15s;">'
					. '<input type="radio" name="scope" value="all" ' . checked( $item->scope ?? 'all', 'all', false ) . '>'
					. '<div><strong style="font-size:.88rem;">All pages</strong><br><small style="color:var(--ah-muted);">Shows on every page of the site</small></div>'
					. '</label>'
					. '<label class="ah-scope-card" style="cursor:pointer;border:2px solid #e5e7eb;border-radius:8px;padding:10px 12px;display:flex;gap:8px;align-items:center;transition:border-color .15s;">'
					. '<input type="radio" name="scope" value="slugs" ' . checked( $item->scope ?? '', 'slugs', false ) . '>'
					. '<div><strong style="font-size:.88rem;">Specific pages</strong><br><small style="color:var(--ah-muted);">Target by slug (comma-separated)</small></div>'
					. '</label>'
					. '</div>'
					. '<div id="sn-slugs-row" style="margin-top:8px;display:' . ( ( $item->scope ?? 'all' ) === 'slugs' ? 'block' : 'none' ) . ';">'
					. '<input type="text" name="slugs" value="' . esc_attr( $item->slugs ?? '' ) . '" placeholder="buying, selling, guides" style="width:100%;">'
					. '<small style="color:var(--ah-muted);">Enter slugs without slashes. Each page tracks its own dismiss - closing on /buying won\'t hide it on /selling.</small>'
					. '</div>'
				); ?>

				<?php
				$_freqs_html = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">';
				$freqs = array(
					'daily'     => array( '📅', 'Once per day',     'Resets at midnight. Resets if content changes.' ),
					'weekly'    => array( '🗓', 'Once per week',    'Resets 7 days after last dismiss.' ),
					'session'   => array( '🔄', 'Per session',      'Resets when visitor closes their tab.' ),
					'once_ever' => array( '🔒', 'Only once - ever', 'Permanently dismissed. Never shows again.' ),
					'always'    => array( '♾', 'Every page load',  'No dismiss memory. Use sparingly.' ),
					'custom'    => array( '⏰', 'Custom interval',  'Show again after your own time (e.g. every 2 hrs).' ),
				);
				$_custom_total = (int) ( $item->frequency_custom_mins ?? 60 );
				$_custom_hrs   = (int) floor( $_custom_total / 60 );
				$_custom_min   = $_custom_total % 60;
				foreach ( $freqs as $fval => $fd ) {
					$_freqs_html .= '<label class="ah-freq-opt" style="cursor:pointer;border:2px solid #e5e7eb;border-radius:8px;padding:10px 12px;display:flex;gap:10px;align-items:flex-start;transition:border-color .15s;">';
					$_freqs_html .= '<input type="radio" name="frequency" value="' . esc_attr( $fval ) . '" ' . checked( $item->frequency ?? 'daily', $fval, false ) . ' style="margin-top:3px;flex-shrink:0;">';
					$_freqs_html .= '<div><span style="font-size:15px;">' . $fd[0] . '</span> <strong style="font-size:.85rem;">' . esc_html( $fd[1] ) . '</strong><br><small style="color:var(--ah-muted);">' . esc_html( $fd[2] ) . '</small></div>';
					$_freqs_html .= '</label>';
				}
				$_freqs_html .= '</div>';
				$_freqs_html .= '<div id="sn-freq-custom-row" style="margin-top:10px;display:' . ( ( $item->frequency ?? '' ) === 'custom' ? 'flex' : 'none' ) . ';align-items:center;gap:8px;background:#f9fafb;padding:10px 12px;border-radius:8px;flex-wrap:wrap;">';
				$_freqs_html .= '<label style="font-size:.88rem;color:var(--ah-muted);">Show again every</label>';
				$_freqs_html .= '<input type="number" id="sn-freq-hours" value="' . esc_attr( $_custom_hrs ) . '" min="0" max="999" style="width:70px;">';
				$_freqs_html .= '<label style="font-size:.88rem;color:var(--ah-muted);">hrs</label>';
				$_freqs_html .= '<input type="number" id="sn-freq-mins" value="' . esc_attr( $_custom_min ) . '" min="0" max="59" style="width:70px;">';
				$_freqs_html .= '<label style="font-size:.88rem;color:var(--ah-muted);">mins</label>';
				$_freqs_html .= '<input type="hidden" name="frequency_custom_mins" id="sn-freq-custom-mins" value="' . esc_attr( max( 1, $_custom_total ) ) . '">';
				$_freqs_html .= '<small style="color:var(--ah-muted);width:100%;margin-top:2px;">Stored in visitor\'s browser. Minimum 1 minute.</small>';
				$_freqs_html .= '</div>';
				\Ah\Cms\Admin\Components\AdminComponents::formRow( 'How often', $_freqs_html );
				?>

				<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Device targeting <small>(who sees this notice)</small>',
					'<div style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap;">'
					. '<label style="cursor:pointer;border:2px solid #e5e7eb;border-radius:8px;padding:8px 14px;display:flex;align-items:center;gap:7px;font-size:.88rem;transition:border-color .15s;" class="ah-device-card">'
					. '<input type="radio" name="device" value="all" ' . checked( $item->device ?? 'all', 'all', false ) . '> 🖥 All devices</label>'
					. '<label style="cursor:pointer;border:2px solid #e5e7eb;border-radius:8px;padding:8px 14px;display:flex;align-items:center;gap:7px;font-size:.88rem;transition:border-color .15s;" class="ah-device-card">'
					. '<input type="radio" name="device" value="desktop" ' . checked( $item->device ?? '', 'desktop', false ) . '> 💻 Desktop only</label>'
					. '<label style="cursor:pointer;border:2px solid #e5e7eb;border-radius:8px;padding:8px 14px;display:flex;align-items:center;gap:7px;font-size:.88rem;transition:border-color .15s;" class="ah-device-card">'
					. '<input type="radio" name="device" value="mobile" ' . checked( $item->device ?? '', 'mobile', false ) . '> 📱 Mobile only</label>'
					. '</div>'
				); ?>

				<?php
				$_date_html = '';
				$_months = array( 1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec' );
				$_cur_yr = (int) gmdate('Y');
				$_years  = range( $_cur_yr, $_cur_yr + 5 );
				ob_start();
				foreach ( array( 'show_from' => 'From', 'show_until' => 'Until' ) as $_fn => $_fl ) :
					$_dt  = $item->$_fn ?? '';
					$_day = $_dt ? (int) gmdate('j', strtotime($_dt)) : 0;
					$_mon = $_dt ? (int) gmdate('n', strtotime($_dt)) : 0;
					$_yr  = $_dt ? (int) gmdate('Y', strtotime($_dt)) : 0;
				?>
				<div style="display:flex;align-items:center;gap:8px;margin-top:10px;flex-wrap:wrap;">
					<span style="font-size:.82rem;font-weight:600;width:36px;color:var(--ah-muted);"><?php echo esc_html( $_fl ); ?></span>
					<select class="ah-date-part" data-field="<?php echo esc_attr( $_fn ); ?>" data-part="d" style="width:72px;">
						<option value="0">Day</option>
						<?php for ( $d = 1; $d <= 31; $d++ ) : ?><option value="<?php echo $d; ?>" <?php selected( $_day, $d ); ?>><?php echo $d; ?></option><?php endfor; ?>
					</select>
					<select class="ah-date-part" data-field="<?php echo esc_attr( $_fn ); ?>" data-part="m" style="width:80px;">
						<option value="0">Month</option>
						<?php foreach ( $_months as $mn => $ml ) : ?><option value="<?php echo $mn; ?>" <?php selected( $_mon, $mn ); ?>><?php echo esc_html( $ml ); ?></option><?php endforeach; ?>
					</select>
					<select class="ah-date-part" data-field="<?php echo esc_attr( $_fn ); ?>" data-part="y" style="width:90px;">
						<option value="0">Year</option>
						<?php foreach ( $_years as $yr ) : ?><option value="<?php echo $yr; ?>" <?php selected( $_yr, $yr ); ?>><?php echo $yr; ?></option><?php endforeach; ?>
					</select>
					<input type="hidden" name="<?php echo esc_attr( $_fn ); ?>" class="ah-date-hidden" data-field="<?php echo esc_attr( $_fn ); ?>" value="<?php echo esc_attr( $_dt ); ?>">
				</div>
				<?php endforeach; ?>
				<div style="margin-top:8px;">
					<button type="button" id="ah-sn-clear-dates" class="ah-btn ah-btn-secondary ah-btn-sm">✕ Clear both dates</button>
				</div>
				<small style="color:var(--ah-muted);">Notice hides itself outside this range even when status is Active.</small>
				<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Active date range <small>(leave blank = always show)</small>', ob_get_clean() ); ?>

				<?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
				array( 'Auto-close <small>(0 = visitor must dismiss manually)</small>',
					'<div style="display:flex;align-items:center;gap:8px;margin-top:6px;">'
					. '<input type="number" name="auto_close" value="' . (int) ( $item->auto_close ?? 0 ) . '" min="0" max="120" style="width:80px;">'
					. '<span style="color:var(--ah-muted);font-size:.88rem;">seconds - notice closes itself (0 = off)</span>'
					. '</div>'
				),
				array( 'Sort Order <small>(lower shows first)</small>',
					'<input type="number" name="sort_order" value="' . (int) ( $item->sort_order ?? 0 ) . '">'
				),
			) ); ?>
			<?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Behaviour', ob_get_clean() ); ?>

			<div id="ah-sn-form-errors" style="display:none;margin-bottom:14px;background:#fef2f2;border-left:4px solid #dc2626;padding:12px 16px;border-radius:6px;color:#b91c1c;font-size:.92rem;"></div>

			<div style="display:flex;align-items:center;gap:12px;">
				<button type="submit" id="ah-sn-submit" class="ah-btn ah-btn-primary">Save Notice</button>
				<a href="<?php echo esc_url( add_query_arg( 'page', 'ah-notices', admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary">← Cancel</a>
			</div>
		</form>

		<!-- ── Right: live preview ────────────────────────────────────────── -->
		<div style="position:sticky;top:80px;">
			<?php ob_start(); ?>
				<div style="margin-top:8px;">
					<div id="pv-wrap" style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#f3f4f6;min-height:200px;display:flex;align-items:center;justify-content:center;padding:16px;position:relative;">
						<div id="pv-popup" style="background:#fff;border-radius:12px;width:100%;overflow:hidden;box-shadow:0 8px 32px rgba(10,25,47,.18);">
							<div id="pv-img-bar" style="position:relative;">
								<img id="pv-img-el" src="" alt="" style="width:100%;height:120px;object-fit:cover;display:none;">
								<div id="pv-color-bar" style="height:5px;background:linear-gradient(90deg,#2d5a44,#3b82f6);"></div>
								<span id="pv-badge-on-img" style="display:none;position:absolute;top:8px;left:10px;font-size:10px;font-weight:700;padding:2px 9px;border-radius:20px;letter-spacing:.04em;text-transform:uppercase;"></span>
							</div>
							<div style="padding:12px 14px 14px;position:relative;">
								<button style="position:absolute;top:8px;right:8px;background:#f3f4f6;border:none;border-radius:50%;width:24px;height:24px;font-size:14px;color:#6b7280;cursor:default;">×</button>
								<span id="pv-badge-above" style="display:none;font-size:10px;font-weight:700;padding:2px 9px;border-radius:20px;letter-spacing:.04em;text-transform:uppercase;margin-bottom:7px;display:inline-block;"></span>
								<div id="pv-title-el" style="font-size:.95rem;font-weight:700;color:#0a192f;padding-right:24px;line-height:1.3;">Notice Title</div>
								<div id="pv-message-el" style="font-size:.8rem;color:#6b7280;margin-top:5px;line-height:1.5;display:none;"></div>
								<div id="pv-btn-wrap" style="margin-top:10px;display:none;">
									<a id="pv-btn-el" href="#" onclick="return false;" style="display:inline-block;background:#0a192f;color:#fff;padding:.35rem 1rem;border-radius:7px;font-size:.8rem;font-weight:600;text-decoration:none;">Book Now</a>
								</div>
							</div>
						</div>
						<div id="pv-corner-badge" style="display:none;position:absolute;bottom:16px;right:16px;background:#fff;border-radius:10px;padding:8px 12px;box-shadow:0 4px 16px rgba(0,0,0,.15);font-size:.78rem;color:#0a192f;font-weight:600;max-width:160px;">Corner preview</div>
					</div>
					<p style="font-size:.78rem;color:var(--ah-muted);margin:6px 0 0;text-align:center;">Preview updates as you type</p>
				</div>
			<?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Live Preview', ob_get_clean() ); ?>
		</div>
	</div>

	<style>
	.ah-style-card:has(input:checked),
	.ah-trig-card:has(input:checked),
	.ah-scope-card:has(input:checked),
	.ah-freq-opt:has(input:checked),
	.ah-device-card:has(input:checked) { border-color:var(--color-primary,#0a192f) !important; background:#f8faff; }
</style>
<?php endif; ?>
</div>

<script>
jQuery(function ($) {

	// ── Generic card highlight helper ─────────────────────────────────────
	function highlightCards(cardClass, radioName) {
		$(cardClass).removeClass('ah-sn-card-sel');
		$('input[name="' + radioName + '"]:checked').closest(cardClass).addClass('ah-sn-card-sel');
	}

	// ── Show/hide conditional rows ────────────────────────────────────────
	function syncTriggerRows() {
		var v = $('input[name="trigger_type"]:checked').val();
		$('#sn-delay-row').toggle( v === 'delay' );
		$('#sn-scroll-row').toggle( v === 'scroll' );
		highlightCards('.ah-trig-card', 'trigger_type');
	}
	$('input[name="trigger_type"]').on('change', syncTriggerRows);
	$('.ah-trig-card').on('click', function () {
		$(this).find('input[type="radio"]').prop('checked', true).trigger('change');
		syncTriggerRows();
	});
	syncTriggerRows(); // run on load to show row + highlight if editing existing notice

	$('input[name="scope"]').on('change', function () {
		$('#sn-slugs-row').toggle( $(this).val() === 'slugs' );
		highlightCards('.ah-scope-card', 'scope');
	});
	$('.ah-scope-card').on('click', function () {
		$(this).find('input[type="radio"]').prop('checked', true).trigger('change');
	});
	highlightCards('.ah-scope-card', 'scope');

	$('input[name="position"]').on('change', function () { highlightCards('.ah-style-card', 'position'); });
	$('.ah-style-card').on('click', function () {
		$(this).find('input[type="radio"]').prop('checked', true).trigger('change');
	});
	highlightCards('.ah-style-card', 'position');

	$('input[name="device"]').on('change', function () { highlightCards('.ah-device-card', 'device'); });
	$('.ah-device-card').on('click', function () {
		$(this).find('input[type="radio"]').prop('checked', true).trigger('change');
	});
	highlightCards('.ah-device-card', 'device');

	// ── Custom frequency row + card highlight ─────────
	function syncFreqCustomRow() {
		var v = $('input[name="frequency"]:checked').val();
		$('#sn-freq-custom-row').toggle( v === 'custom' );
		highlightCards('.ah-freq-opt', 'frequency');
	}
	$('input[name="frequency"]').on('change', syncFreqCustomRow);
	$('.ah-freq-opt').on('click', function () {
		$(this).find('input[type="radio"]').prop('checked', true).trigger('change');
		syncFreqCustomRow();
	});
	syncFreqCustomRow(); // run on load — highlights saved selection + shows custom row if needed

	function syncCustomMins() {
		var h = parseInt($('#sn-freq-hours').val(), 10) || 0;
		var m = parseInt($('#sn-freq-mins').val(), 10) || 0;
		var total = h * 60 + m;
		if (total < 1) total = 1;
		$('#sn-freq-custom-mins').val(total);
	}
	$('#sn-freq-hours, #sn-freq-mins').on('input change', syncCustomMins);


	// ── Date selects → hidden ISO field ──────────────────────────────────
	function syncDateField(fieldName) {
		var d = $('select.ah-date-part[data-field="' + fieldName + '"][data-part="d"]').val();
		var m = $('select.ah-date-part[data-field="' + fieldName + '"][data-part="m"]').val();
		var y = $('select.ah-date-part[data-field="' + fieldName + '"][data-part="y"]').val();
		var val = '';
		if (d > 0 && m > 0 && y > 0) {
			val = y + '-' + String(m).padStart(2,'0') + '-' + String(d).padStart(2,'0');
		}
		$('input.ah-date-hidden[data-field="' + fieldName + '"]').val(val);
	}
	$('.ah-date-part').on('change', function () {
		syncDateField($(this).data('field'));
	});

	// ── Clear dates ───────────────────────────────────────────────────────
	$('#ah-sn-clear-dates').on('click', function () {
		$('.ah-date-part').val('0');
		$('.ah-date-hidden').val('');
	});

	// ── Media picker ─────────────────────────────────────────────────────
	var mediaFrame;
	$('#ah-sn-media-btn').on('click', function (e) {
		e.preventDefault();
		if (mediaFrame) { mediaFrame.open(); return; }
		mediaFrame = wp.media({ title: 'Choose Banner Image', button: { text: 'Use image' }, multiple: false, library: { type: 'image' } });
		mediaFrame.on('select', function () {
			var att = mediaFrame.state().get('selection').first().toJSON();
			$('#pv-image').val(att.url).trigger('input');
		});
		mediaFrame.open();
	});

	$('#ah-sn-img-clear').on('click', function () {
		$('#pv-image').val('').trigger('input');
	});

	// ── Live preview ──────────────────────────────────────────────────────
	function hexToRgba(hex, alpha) {
		var r = parseInt(hex.slice(1,3), 16) || 0;
		var g = parseInt(hex.slice(3,5), 16) || 0;
		var b = parseInt(hex.slice(5,7), 16) || 0;
		return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
	}

	function syncBadgeColorSample() {
		var hex = $('#pv-badge-color').val() || '#15803d';
		$('#pv-badge-color-sample').css({ background: hexToRgba(hex, 0.13), color: hex });
	}
	$('#pv-badge-color').on('input change', syncBadgeColorSample);
	syncBadgeColorSample();

	function updatePreview() {
		var title      = $.trim( $('#pv-title').val() )   || 'Notice Title';
		var message    = $.trim( $('#pv-message').val() );
		var badgeText  = $.trim( $('#pv-badge-text').val() );
		var badgeColor = $('#pv-badge-color').val() || '#15803d';
		var pal        = { bg: hexToRgba(badgeColor, 0.13), color: badgeColor };
		var btnLabel   = $.trim( $('#pv-btn-label').val() );
		var imgUrl     = $.trim( $('#pv-image').val() );
		var isCorner   = $('#pv-pos-corner').is(':checked');

		// Title & message
		$('#pv-title-el').text(title);
		if (message) { $('#pv-message-el').text(message).show(); } else { $('#pv-message-el').hide(); }

		// Button
		if (btnLabel) { $('#pv-btn-el').text(btnLabel); $('#pv-btn-wrap').show(); } else { $('#pv-btn-wrap').hide(); }

		// Image / colour bar
		if (imgUrl) {
			$('#pv-img-el').attr('src', imgUrl).show();
			$('#pv-color-bar').hide();
			$('#pv-thumb').show();
		} else {
			$('#pv-img-el').hide();
			$('#pv-color-bar').show();
		}
		// Image thumb in form
		if (imgUrl) {
			$('#pv-img-thumb img').attr('src', imgUrl);
			$('#pv-img-thumb').show();
		} else {
			$('#pv-img-thumb').hide();
		}

		// Badge
		if (badgeText) {
			if (imgUrl) {
				$('#pv-badge-on-img').text(badgeText).css({ background: pal.bg, color: pal.color }).show();
				$('#pv-badge-above').hide();
			} else {
				$('#pv-badge-on-img').hide();
				$('#pv-badge-above').text(badgeText).css({ background: pal.bg, color: pal.color }).show();
			}
		} else {
			$('#pv-badge-on-img, #pv-badge-above').hide();
		}

		// Corner vs modal layout hint
		if (isCorner) {
			$('#pv-popup').css({ maxWidth: '220px', margin: '0 0 0 auto' });
			$('#pv-wrap').css({ justifyContent: 'flex-end', alignItems: 'flex-end' });
		} else {
			$('#pv-popup').css({ maxWidth: '100%', margin: '0' });
			$('#pv-wrap').css({ justifyContent: 'center', alignItems: 'center' });
		}
	}

	// Trigger preview update on any input change
	$('#ah-notice-form').on('input change', updatePreview);
	updatePreview(); // initial render

	// ── Form validation ───────────────────────────────────────────────────
	$('#ah-sn-submit').closest('form').on('submit', function (e) {
		syncCustomMins(); // ensure hidden field reflects current hours/mins before POST
		var errs = [];
		if (!$.trim($('#pv-title').val())) errs.push('Title is required.');
		if ($('input[name="trigger_type"]:checked').val() === 'delay') {
			var d = parseInt($('input[name="trigger_delay"]').val(), 10);
			if (isNaN(d) || d < 1) errs.push('Delay must be at least 1 second.');
		}
		if ($('input[name="scope"]:checked').val() === 'slugs' && !$.trim($('input[name="slugs"]').val())) {
			errs.push('Enter at least one slug when "Specific pages" is chosen.');
		}
		if (errs.length) {
			e.preventDefault();
			$('#ah-sn-form-errors').html('<strong>Please fix:</strong><ul style="margin:.3rem 0 0 1.2rem;padding:0;"><li>' + errs.join('</li><li>') + '</li></ul>').show();
			window.scrollTo({ top: 0, behavior: 'smooth' });
		}
	});
});
</script>
<style>
.ah-field-error { border-color:#dc2626 !important; background:#fff5f5 !important; box-shadow:0 0 0 2px rgba(220,38,38,.15) !important; }
.ah-trig-card { text-align:center; }
.ah-trig-card input { display:none; }
.ah-sn-card-sel { border-color:#0a192f !important; background:#f8faff !important; }
</style>
