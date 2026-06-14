<?php
/**
 * Site-Wide Notices Management
 * Handle storing/retrieving notice data from database
 */
defined( 'ABSPATH' ) || exit;

class AH_Notice_Helper {

	const OPTION_KEY = 'ah_important_notice';

	/**
	 * Get the current active notice.
	 */
	public static function get_notice(): array {
		$stored = get_option( self::OPTION_KEY, '' );
		if ( ! empty( $stored ) ) {
			$notice = json_decode( $stored, true );
			if ( is_array( $notice ) ) {
				return $notice;
			}
		}
		return self::get_defaults();
	}

	/**
	 * Save notice data.
	 * Generates a content hash so the frontend can detect when notice content changes.
	 */
	public static function save_notice( array $data ): bool {
		$title        = sanitize_text_field( wp_unslash( $data['title']        ?? 'Important Update' ) );
		$message      = sanitize_text_field( wp_unslash( $data['message']      ?? '' ) );
		$button_label = sanitize_text_field( wp_unslash( $data['button_label'] ?? '' ) );
		$button_url   = esc_url_raw( wp_unslash( $data['button_url']           ?? '' ) );

		// Hash changes whenever title/message/button content changes → forces re-show.
		$hash = substr( md5( $title . '|' . $message . '|' . $button_label . '|' . $button_url ), 0, 12 );

		$notice = array(
			'enabled'      => ! empty( $data['enabled'] ),
			'id'           => sanitize_key( $data['id'] ?? 'default' ),
			'title'        => $title,
			'message'      => $message,
			'image'        => esc_url_raw( wp_unslash( $data['image'] ?? '' ) ),
			'button_label' => $button_label,
			'button_url'   => $button_url,
			'hash'         => $hash,
		);

		return update_option( self::OPTION_KEY, wp_json_encode( $notice ) );
	}

	/**
	 * Get default notice structure.
	 */
	public static function get_defaults(): array {
		return array(
			'enabled'      => false,
			'id'           => 'default',
			'title'        => 'Important Update',
			'message'      => '',
			'image'        => '',
			'button_label' => '',
			'button_url'   => '',
			'hash'         => '',
		);
	}

	/**
	 * Disable notice.
	 */
	public static function disable(): bool {
		$notice            = self::get_notice();
		$notice['enabled'] = false;
		return update_option( self::OPTION_KEY, wp_json_encode( $notice ) );
	}

	/**
	 * Clear notice completely.
	 */
	public static function clear(): bool {
		return delete_option( self::OPTION_KEY );
	}

	/**
	 * Output the frontend popup HTML + JS in wp_footer.
	 *
	 * Show rules (localStorage key: ah_notice_seen):
	 *   - Shows once per day per visitor.
	 *   - If notice content changes (new hash), shows again even on the same day.
	 *   - Dismiss by clicking X or the backdrop; stores date + hash so it won't repeat.
	 */
	public static function render_frontend_popup(): void {
		$notice = self::get_notice();

		if ( empty( $notice['enabled'] ) || empty( $notice['hash'] ) ) {
			return;
		}

		$title        = esc_html( $notice['title']        ?? '' );
		$message      = esc_html( $notice['message']      ?? '' );
		$image        = esc_url( $notice['image']         ?? '' );
		$btn_label    = esc_html( $notice['button_label'] ?? '' );
		$btn_url      = esc_url( $notice['button_url']    ?? '' );
		$hash         = esc_attr( $notice['hash']         ?? '' );

		?>
		<div id="ah-notice-popup" role="dialog" aria-modal="true" aria-labelledby="ah-notice-title" style="display:none;position:fixed;inset:0;z-index:99999;align-items:center;justify-content:center;padding:1rem;">

			<div id="ah-notice-backdrop" style="position:absolute;inset:0;background:rgba(10,25,47,.6);backdrop-filter:blur(3px);"></div>

			<div style="position:relative;z-index:1;background:#fff;border-radius:16px;max-width:520px;width:100%;padding:0;overflow:hidden;box-shadow:0 24px 64px rgba(10,25,47,.28);animation:ahNoticeIn .28s ease both;">

				<?php if ( $image ) : ?>
				<div style="width:100%;height:180px;overflow:hidden;background:var(--color-primary,#0a192f);">
					<img src="<?php echo $image; ?>" alt="" style="width:100%;height:100%;object-fit:cover;opacity:.85;">
				</div>
				<?php else : ?>
				<div style="width:100%;height:6px;background:linear-gradient(90deg,var(--color-primary,#0a192f),var(--color-accent,#3b82f6));"></div>
				<?php endif; ?>

				<div style="padding:1.75rem 2rem 2rem;">

					<button id="ah-notice-close" aria-label="Close notice" style="position:absolute;top:14px;right:14px;background:rgba(255,255,255,.9);border:none;border-radius:50%;width:32px;height:32px;font-size:18px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#444;box-shadow:0 2px 6px rgba(0,0,0,.15);z-index:2;">&times;</button>

					<div style="display:inline-block;background:var(--color-primary,#0a192f);color:#fff;font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:.25rem .7rem;border-radius:4px;margin-bottom:.9rem;">📢 Notice</div>

					<h2 id="ah-notice-title" style="margin:0 0 .6rem;font-size:1.35rem;font-weight:700;color:var(--color-primary,#0a192f);line-height:1.3;"><?php echo $title; ?></h2>

					<?php if ( $message ) : ?>
					<p style="margin:0 0 1.4rem;color:#555;font-size:.97rem;line-height:1.6;"><?php echo $message; ?></p>
					<?php endif; ?>

					<div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
						<?php if ( $btn_label && $btn_url ) : ?>
						<a href="<?php echo $btn_url; ?>" style="display:inline-block;background:var(--color-primary,#0a192f);color:#fff;padding:.6rem 1.4rem;border-radius:8px;font-size:.9rem;font-weight:600;text-decoration:none;">
							<?php echo $btn_label; ?>
						</a>
						<?php endif; ?>
						<button id="ah-notice-dismiss" style="background:none;border:none;color:#888;font-size:.9rem;cursor:pointer;padding:.6rem 0;text-decoration:underline;">Dismiss</button>
					</div>

				</div>
			</div>
		</div>

		<style>
		@keyframes ahNoticeIn {
			from { opacity:0; transform:translateY(24px) scale(.97); }
			to   { opacity:1; transform:none; }
		}
		@media (max-width:540px) {
			#ah-notice-popup > div:last-child { border-radius:12px; margin:.5rem; }
		}
		</style>

		<script>
		(function () {
			var notice = { hash: <?php echo wp_json_encode( $hash ); ?> };
			var KEY    = 'ah_notice_seen';
			var today  = new Date().toISOString().slice(0, 10);

			try {
				var seen = JSON.parse(localStorage.getItem(KEY) || '{}');
				if (seen.date === today && seen.hash === notice.hash) return;
			} catch (e) {}

			var popup = document.getElementById('ah-notice-popup');
			if (!popup) return;
			popup.style.display = 'flex';

			function dismiss() {
				popup.style.display = 'none';
				try {
					localStorage.setItem(KEY, JSON.stringify({ date: today, hash: notice.hash }));
				} catch (e) {}
			}

			document.getElementById('ah-notice-close').addEventListener('click', dismiss);
			document.getElementById('ah-notice-dismiss').addEventListener('click', dismiss);
			document.getElementById('ah-notice-backdrop').addEventListener('click', dismiss);

			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape') dismiss();
			});
		})();
		</script>
		<?php
	}
}
