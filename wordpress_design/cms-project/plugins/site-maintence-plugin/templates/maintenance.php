<?php
/**
 * Maintenance page template.
 *
 * Served with HTTP 503 + Retry-After header (set by the Router).
 *
 * Available variables:
 *   $smm  TemplateData - helper object.
 *
 * @package SiteModeManager
 */

declare( strict_types=1 );

// Block direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var \SiteModeManager\TemplateData $smm */

// Check if custom HTML is set - if so, display it instead of the default template.
$custom_html = $smm->custom_maintenance_html();
if ( ! empty( $custom_html ) ) {
	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow">
		<title><?php echo esc_html( $smm->page_title( __( 'Maintenance', 'site-mode-manager' ) ) ); ?></title>
		<?php
		/**
		 * Action: smm_maintenance_head
		 * Add custom <head> content (analytics, fonts, etc.).
		 */
		do_action( 'smm_maintenance_head' );
		?>
	</head>
	<body>
		<?php
		// Output the custom HTML - already sanitized in settings.
		echo $custom_html; // phpcs:ignore WordPress.Security.EscapeOutput
		?>
		<?php
		/**
		 * Action: smm_maintenance_footer
		 * Add scripts / pixels before </body>.
		 */
		do_action( 'smm_maintenance_footer' );
		?>
	</body>
	</html>
	<?php
	return;
}

// Default template if no custom HTML is set.
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( $smm->page_title( __( 'Maintenance', 'site-mode-manager' ) ) ); ?></title>
	<style>
		/* ── Reset ───────────────────────────────────────────────────── */
		*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

		/* ── Base ────────────────────────────────────────────────────── */
		:root {
			--smm-bg-from: #1a1a2e;
			--smm-bg-to:   #16213e;
			--smm-accent:  #f97316;
			--smm-text:    #f1f5f9;
			--smm-muted:   #94a3b8;
			--smm-card-bg: rgba(255,255,255,0.05);
			--smm-radius:  1rem;
		}

		html, body {
			height: 100%;
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
			background: linear-gradient(135deg, var(--smm-bg-from), var(--smm-bg-to));
			color: var(--smm-text);
		}

		.smm-page {
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			padding: 2rem 1.5rem;
			text-align: center;
		}

		.smm-card {
			background: var(--smm-card-bg);
			backdrop-filter: blur(12px);
			border: 1px solid rgba(255,255,255,0.1);
			border-radius: var(--smm-radius);
			padding: 3rem 2.5rem;
			max-width: 540px;
			width: 100%;
		}

		/* ── Animated gear icon ──────────────────────────────────────── */
		.smm-icon {
			font-size: 4rem;
			line-height: 1;
			margin-bottom: 1.25rem;
			display: block;
			animation: smm-spin 6s linear infinite;
			transform-origin: center;
		}

		@keyframes smm-spin {
			0%   { transform: rotate(0deg);   }
			100% { transform: rotate(360deg); }
		}

		/* ── Badge ───────────────────────────────────────────────────── */
		.smm-badge {
			display: inline-flex;
			align-items: center;
			gap: 0.4rem;
			background: rgba(249,115,22,0.15);
			color: var(--smm-accent);
			border: 1px solid rgba(249,115,22,0.35);
			border-radius: 999px;
			padding: 0.25rem 0.85rem;
			font-size: 0.78rem;
			font-weight: 700;
			letter-spacing: 0.06em;
			text-transform: uppercase;
			margin-bottom: 1.25rem;
		}

		.smm-badge::before {
			content: '';
			display: inline-block;
			width: 6px;
			height: 6px;
			background: var(--smm-accent);
			border-radius: 50%;
			animation: smm-pulse 1.4s ease-in-out infinite;
		}

		@keyframes smm-pulse {
			0%, 100% { opacity: 1; transform: scale(1); }
			50%       { opacity: 0.5; transform: scale(0.8); }
		}

		/* ── Text ────────────────────────────────────────────────────── */
		.smm-site-name {
			font-size: 0.875rem;
			font-weight: 600;
			letter-spacing: 0.1em;
			text-transform: uppercase;
			color: var(--smm-accent);
			margin-bottom: 0.5rem;
		}

		.smm-heading {
			font-size: clamp(1.75rem, 5vw, 2.75rem);
			font-weight: 800;
			line-height: 1.15;
			margin-bottom: 1rem;
		}

		.smm-subheading {
			font-size: 1.05rem;
			color: var(--smm-muted);
			line-height: 1.6;
		}

		.smm-footer {
			margin-top: 2.5rem;
			font-size: 0.8rem;
			color: rgba(255,255,255,0.25);
		}

		@media (max-width: 480px) {
			.smm-card { padding: 2rem 1.5rem; }
		}
	</style>
	<?php do_action( 'smm_maintenance_head' ); ?>
</head>
<body>

<main class="smm-page" role="main">
	<div class="smm-card">

		<span class="smm-icon" role="img" aria-label="<?php esc_attr_e( 'Gear', 'site-mode-manager' ); ?>">⚙️</span>

		<div class="smm-badge"><?php esc_html_e( 'Maintenance', 'site-mode-manager' ); ?></div>

		<p class="smm-site-name"><?php echo esc_html( $smm->site_name() ); ?></p>

		<h1 class="smm-heading">
			<?php esc_html_e( 'We\'ll be right back', 'site-mode-manager' ); ?>
		</h1>

		<p class="smm-subheading">
			<?php
			echo esc_html(
				apply_filters(
					'smm_maintenance_message',
					__( 'We\'re performing scheduled maintenance. The site will be back online shortly - thank you for your patience.', 'site-mode-manager' )
				)
			);
			?>
		</p>

		<?php \SiteModeManager\smm_get_partial( 'maintenance-extra.php', $smm ); ?>

	</div><!-- .smm-card -->

	<footer class="smm-footer">
		&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $smm->site_name() ); ?>
	</footer>
</main>

<?php do_action( 'smm_maintenance_footer' ); ?>
</body>
</html>
