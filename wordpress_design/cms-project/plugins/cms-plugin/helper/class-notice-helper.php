<?php
defined( 'ABSPATH' ) || exit;

class AH_Notice_Helper {

	// Legacy option key - kept so old data isn't lost but no longer read by the frontend.
	const OPTION_KEY = 'ah_important_notice';

	private static function current_slug(): string {
		$qv = (string) get_query_var( 'adn_cat_slug', '' );
		if ( '' !== $qv ) return sanitize_key( $qv );
		$obj = get_queried_object();
		if ( $obj instanceof WP_Post ) return sanitize_key( $obj->post_name );
		$path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
		return sanitize_key( explode( '/', $path )[0] ?? '' );
	}

	/**
	 * Render all active notices that match the current page.
	 * Notices are queued client-side - one popup shows at a time.
	 */
	public static function render_frontend_popup(): void {
		if ( ! class_exists( 'AH_Site_Notices_Model' ) ) return;

		$model   = new AH_Site_Notices_Model();
		$all     = $model->get_active();
		$current = self::current_slug();

		$to_render = array();
		foreach ( $all as $n ) {
			// Slug filter (server-side short-circuit).
			if ( $n->scope === 'slugs' && ! empty( $n->slugs ) ) {
				$allowed = array_map( static function( $s ) { return trim( $s, " /\t\n\r" ); }, explode( ',', $n->slugs ) );
				if ( ! in_array( $current, $allowed, true ) ) continue;
			}
			$to_render[] = $n;
		}

		if ( empty( $to_render ) ) return;

		// Build JS payload - one entry per notice.
		$js_notices = array();
		foreach ( $to_render as $n ) {
			$dismiss_slug = ( $n->scope === 'slugs' ) ? $current : '';
			$js_notices[] = array(
				'id'         => (int) $n->id,
				'trigger'    => $n->trigger_type,
				'delay'      => (int) $n->trigger_delay,
				'scroll'     => (int) ( $n->trigger_scroll ?? 0 ),
				'freq'       => $n->frequency,
				'freq_mins'  => max( 1, (int) ( $n->frequency_custom_mins ?? 60 ) ),
				'slug'       => $dismiss_slug,
				'device'     => $n->device ?? 'all',
				'show_from'  => $n->show_from  ?? '',
				'show_until' => $n->show_until ?? '',
				'auto_close' => (int) ( $n->auto_close ?? 0 ),
				'hash'       => substr( md5( $n->title . $n->message . $n->button_label . $n->button_url ), 0, 12 ),
			);
		}
		?>
		<?php
		$badge_palette = array(
			'green'  => array( 'bg' => '#dcfce7', 'color' => '#15803d' ),
			'red'    => array( 'bg' => '#fee2e2', 'color' => '#b91c1c' ),
			'blue'   => array( 'bg' => '#dbeafe', 'color' => '#1d4ed8' ),
			'orange' => array( 'bg' => '#ffedd5', 'color' => '#c2410c' ),
			'purple' => array( 'bg' => '#ede9fe', 'color' => '#7c3aed' ),
		);
		$resolve_badge = static function( string $color ) use ( $badge_palette ): array {
			if ( isset( $badge_palette[ $color ] ) ) return $badge_palette[ $color ];
			if ( preg_match( '/^#[0-9a-fA-F]{6}$/', $color ) ) {
				$r = hexdec( substr( $color, 1, 2 ) );
				$g = hexdec( substr( $color, 3, 2 ) );
				$b = hexdec( substr( $color, 5, 2 ) );
				return array( 'bg' => "rgba($r,$g,$b,0.13)", 'color' => $color );
			}
			return $badge_palette['green'];
		};
		foreach ( $to_render as $n ) :
			$id         = (int) $n->id;
			$title      = esc_html( $n->title );
			$message    = esc_html( $n->message ?? '' );
			$image      = esc_url( $n->image ?? '' );
			$btn_label  = esc_html( $n->button_label ?? '' );
			$btn_url    = esc_url( $n->button_url ?? '' );
			$badge      = esc_html( $n->badge_text ?? '' );
			$bpal       = $resolve_badge( $n->badge_color ?? 'green' );
			$is_corner  = ( $n->position ?? 'modal' ) === 'corner';
		?>
		<?php if ( $is_corner ) : ?>
		<div id="ah-sn-<?php echo $id; ?>" class="ah-sn-popup ah-sn-corner" role="dialog" aria-label="<?php echo $title; ?>"
		     style="display:none;position:fixed;bottom:24px;right:24px;z-index:99999;width:320px;max-width:calc(100vw - 32px);">
			<div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 12px 40px rgba(10,25,47,.22);border:1px solid rgba(0,0,0,.06);">
				<?php if ( $image ) : ?>
				<div style="width:100%;height:160px;overflow:hidden;background:#f3f4f6;position:relative;">
					<img src="<?php echo $image; ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
					<?php if ( $badge ) : ?>
					<span style="position:absolute;top:10px;left:12px;background:<?php echo esc_attr( $bpal['bg'] ); ?>;color:<?php echo esc_attr( $bpal['color'] ); ?>;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;letter-spacing:.04em;text-transform:uppercase;"><?php echo $badge; ?></span>
					<?php endif; ?>
				</div>
				<?php endif; ?>
				<div style="padding:14px 16px 16px;position:relative;">
					<?php if ( $badge && ! $image ) : ?>
					<span style="display:inline-block;background:<?php echo esc_attr( $bpal['bg'] ); ?>;color:<?php echo esc_attr( $bpal['color'] ); ?>;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;letter-spacing:.04em;text-transform:uppercase;margin-bottom:8px;"><?php echo $badge; ?></span>
					<?php endif; ?>
					<button class="ah-sn-close" data-id="<?php echo $id; ?>" aria-label="Close"
					        style="position:absolute;top:10px;right:10px;background:#f3f4f6;border:none;border-radius:50%;width:26px;height:26px;font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#6b7280;">&times;</button>
					<h3 style="margin:0 28px 6px 0;font-size:1rem;font-weight:700;color:var(--color-primary,#0a192f);line-height:1.35;"><?php echo $title; ?></h3>
					<?php if ( $message ) : ?>
					<p style="margin:0 0 12px;color:#6b7280;font-size:.82rem;line-height:1.55;"><?php echo $message; ?></p>
					<?php endif; ?>
					<?php if ( $btn_label && $btn_url ) : ?>
					<a href="<?php echo $btn_url; ?>" style="display:inline-block;background:var(--color-primary,#0a192f);color:#fff;padding:.45rem 1.1rem;border-radius:8px;font-size:.82rem;font-weight:600;text-decoration:none;"><?php echo $btn_label; ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php else : ?>
		<div id="ah-sn-<?php echo $id; ?>" class="ah-sn-popup ah-sn-modal" role="dialog" aria-modal="true"
		     style="display:none;position:fixed;inset:0;z-index:99999;align-items:center;justify-content:center;padding:1rem;">
			<div class="ah-sn-backdrop" data-id="<?php echo $id; ?>"
			     style="position:absolute;inset:0;background:rgba(10,25,47,.55);backdrop-filter:blur(3px);"></div>
			<div style="position:relative;z-index:1;background:#fff;border-radius:18px;max-width:520px;width:100%;overflow:hidden;box-shadow:0 24px 64px rgba(10,25,47,.28);">
				<?php if ( $image ) : ?>
				<div style="width:100%;aspect-ratio:16/9;overflow:hidden;background:var(--color-primary,#0a192f);position:relative;">
					<img src="<?php echo $image; ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
					<?php if ( $badge ) : ?>
					<span style="position:absolute;top:14px;left:16px;background:<?php echo esc_attr( $bpal['bg'] ); ?>;color:<?php echo esc_attr( $bpal['color'] ); ?>;font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;letter-spacing:.05em;text-transform:uppercase;"><?php echo $badge; ?></span>
					<?php endif; ?>
				</div>
				<?php else : ?>
				<div style="width:100%;height:5px;background:linear-gradient(90deg,var(--color-primary,#2d5a44),<?php echo esc_attr( $bpal['bg'] ); ?>);"></div>
				<?php endif; ?>
				<div style="padding:1.75rem 2rem 2rem;position:relative;">
					<?php if ( $badge && ! $image ) : ?>
					<span style="display:inline-block;background:<?php echo esc_attr( $bpal['bg'] ); ?>;color:<?php echo esc_attr( $bpal['color'] ); ?>;font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;letter-spacing:.05em;text-transform:uppercase;margin-bottom:10px;"><?php echo $badge; ?></span>
					<?php endif; ?>
					<button class="ah-sn-close" data-id="<?php echo $id; ?>" aria-label="Close"
					        style="position:absolute;top:16px;right:16px;background:#f3f4f6;border:none;border-radius:50%;width:32px;height:32px;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#6b7280;">&times;</button>
					<h2 style="margin:0 36px .6rem 0;font-size:1.35rem;font-weight:700;color:var(--color-primary,#0a192f);line-height:1.3;"><?php echo $title; ?></h2>
					<?php if ( $message ) : ?>
					<p style="margin:0 0 1.4rem;color:#555;font-size:.95rem;line-height:1.6;"><?php echo $message; ?></p>
					<?php endif; ?>
					<?php if ( $btn_label && $btn_url ) : ?>
					<a href="<?php echo $btn_url; ?>" style="display:inline-block;background:var(--color-primary,#0a192f);color:#fff;padding:.6rem 1.4rem;border-radius:8px;font-size:.9rem;font-weight:600;text-decoration:none;"><?php echo $btn_label; ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
		<?php endforeach; ?>

		<style>
		.ah-sn-modal { display:none; }
		.ah-sn-modal.ah-sn-show { display:flex !important; animation:ahSnModalIn .28s ease both; }
		@keyframes ahSnModalIn { from { opacity:0; } to { opacity:1; } }
		.ah-sn-modal.ah-sn-show > div:nth-child(2) { animation:ahSnCardIn .3s ease both; }
		@keyframes ahSnCardIn { from { opacity:0;transform:translateY(28px) scale(.96); } to { opacity:1;transform:none; } }

		.ah-sn-corner { display:none; }
		.ah-sn-corner.ah-sn-show { display:block !important; animation:ahSnCornerIn .35s cubic-bezier(.22,.68,0,1.2) both; }
		@keyframes ahSnCornerIn { from { opacity:0;transform:translateY(40px); } to { opacity:1;transform:none; } }

		@media (max-width:540px) {
			.ah-sn-corner { bottom:12px !important; right:12px !important; width:calc(100vw - 24px) !important; }
		}
		</style>

		<script>
		(function () {
			var notices  = <?php echo wp_json_encode( $js_notices ); ?>;
			var today    = new Date().toISOString().slice(0, 10);
			var nowMs    = Date.now();
			var queue    = [];
			var showing  = false;
			var isMobile = window.innerWidth < 768;

			// ?ah_preview_notice=ID bypasses all dismiss/frequency checks for that notice.
			var previewId = (new URLSearchParams(window.location.search)).get('ah_preview_notice');

			function storageKey(n) {
				return n.slug ? ('ah_sn_' + n.id + '__' + n.slug) : ('ah_sn_' + n.id);
			}

			function inDateRange(n) {
				if (!n.show_from && !n.show_until) return true;
				if (n.show_from  && today < n.show_from)  return false;
				if (n.show_until && today > n.show_until) return false;
				return true;
			}

			function matchesDevice(n) {
				if (n.device === 'mobile')  return isMobile;
				if (n.device === 'desktop') return !isMobile;
				return true;
			}

			// ── Filter which notices should show this visit ──────────────────
			notices.forEach(function (n) {
				// Preview mode: force-show regardless of everything.
				if (previewId && String(n.id) === previewId) { queue.push(n); return; }

				if (!inDateRange(n))   return;
				if (!matchesDevice(n)) return;

				if (n.freq === 'always') { queue.push(n); return; }

				var key   = storageKey(n);
				var store = n.freq === 'session' ? sessionStorage : localStorage;
				try {
					var seen = JSON.parse(store.getItem(key) || 'null');
					if (!seen) { queue.push(n); return; }

					if (n.freq === 'daily'     && seen.date === today && seen.hash === n.hash) return;
					if (n.freq === 'weekly'    && seen.ts && (nowMs - seen.ts) < 7 * 86400000) return;
					if (n.freq === 'once_ever' && seen.done) return;
					if (n.freq === 'session'   && seen.shown) return;
					if (n.freq === 'custom'    && seen.ts && (nowMs - seen.ts) < n.freq_mins * 60000) return;
				} catch (e) {}

				queue.push(n);
			});

			if (!queue.length) return;

			// When multiple notices are eligible, pick one at random so all get exposure over time.
			if (queue.length > 1) {
				queue = [queue[Math.floor(Math.random() * queue.length)]];
			}

			function dismiss(n) {
				var el = document.getElementById('ah-sn-' + n.id);
				if (el) el.classList.remove('ah-sn-show');

				if (n.freq !== 'always') {
					var key   = storageKey(n);
					var store = n.freq === 'session' ? sessionStorage : localStorage;
					var record = { date: today, hash: n.hash, shown: true, ts: Date.now(), done: true };
					try { store.setItem(key, JSON.stringify(record)); } catch (e) {}
				}

				showing = false;
				showNext();
			}

			function showPopup(n) {
				var el = document.getElementById('ah-sn-' + n.id);
				if (!el) { showing = false; showNext(); return; }
				el.classList.add('ah-sn-show');
				showing = true;

				var closeBtn = el.querySelector('.ah-sn-close');
				var backdrop = el.querySelector('.ah-sn-backdrop');
				if (closeBtn) closeBtn.addEventListener('click', function () { dismiss(n); });
				if (backdrop) backdrop.addEventListener('click', function () { dismiss(n); });
				document.addEventListener('keydown', function kh(e) {
					if (e.key === 'Escape') { document.removeEventListener('keydown', kh); dismiss(n); }
				});

				// Auto-close timer.
				if (n.auto_close > 0) {
					setTimeout(function () { dismiss(n); }, n.auto_close * 1000);
					// Countdown ring on close button.
					if (closeBtn) {
						closeBtn.setAttribute('title', 'Closes in ' + n.auto_close + 's');
						var remaining = n.auto_close;
						var cd = setInterval(function () {
							remaining--;
							if (remaining <= 0) { clearInterval(cd); return; }
							closeBtn.setAttribute('title', 'Closes in ' + remaining + 's');
						}, 1000);
					}
				}
			}

			function scheduleNotice(n, cb) {
				if (n.trigger === 'exit-intent') {
					var fired = false;
					// Track cursor via mousemove - fires when cursor reaches top 20px.
					// This is more reliable than mouseleave which can miss fast movements.
					var moveHandler = function (e) {
						if (fired || e.clientY > 20) return;
						fired = true;
						document.removeEventListener('mousemove', moveHandler);
						document.removeEventListener('mouseleave', leaveHandler);
						cb();
					};
					// Also catch fast movements that skip the 20px zone.
					var leaveHandler = function (e) {
						if (fired || e.clientY > 50) return;
						fired = true;
						document.removeEventListener('mousemove', moveHandler);
						document.removeEventListener('mouseleave', leaveHandler);
						cb();
					};
					document.addEventListener('mousemove',  moveHandler);
					document.addEventListener('mouseleave', leaveHandler);
					// Mobile / touchscreen fallback: 20 s.
					setTimeout(function () {
						if (!fired) {
							fired = true;
							document.removeEventListener('mousemove',  moveHandler);
							document.removeEventListener('mouseleave', leaveHandler);
							cb();
						}
					}, 20000);

				} else if (n.trigger === 'delay') {
					setTimeout(cb, (n.delay || 5) * 1000);

				} else if (n.trigger === 'scroll') {
					var pct = n.scroll || 50;
					var fired = false;
					var onScroll = function () {
						if (fired) return;
						var scrolled = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
						if (scrolled >= pct) {
							fired = true;
							window.removeEventListener('scroll', onScroll);
							cb();
						}
					};
					window.addEventListener('scroll', onScroll, { passive: true });

				} else {
					cb(); // immediate
				}
			}

			function showNext() {
				if (showing || !queue.length) return;
				var n = queue.shift();
				scheduleNotice(n, function () { showPopup(n); });
			}

			showNext();
		})();
		</script>
		<?php
	}

	// ── Legacy stubs kept so old callers don't fatal ──────────────────────────
	public static function get_notice(): array { return array(); }
	public static function save_notice( array $data ): bool { return true; }
	public static function get_defaults(): array { return array(); }
	public static function disable(): bool { return true; }
	public static function clear(): bool { return true; }
}
