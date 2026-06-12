<?php
/**
 * Coming Soon page template.
 *
 * Available variables:
 *   $smm  TemplateData - helper object (site name, assets URL, etc.)
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
$custom_html = $smm->custom_coming_soon_html();
if ( ! empty( $custom_html ) ) {
	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow">
		<title><?php echo esc_html( $smm->page_title( __( 'Coming Soon', 'site-mode-manager' ) ) ); ?></title>
		<?php
		/**
		 * Action: smm_coming_soon_head
		 * Add custom <head> content (analytics, fonts, etc.).
		 */
		do_action( 'smm_coming_soon_head' );
		?>
	</head>
	<body>
		<?php
		// Output the custom HTML - already sanitized in settings.
		echo $custom_html; // phpcs:ignore WordPress.Security.EscapeOutput
		?>
		<?php
		/**
		 * Action: smm_coming_soon_footer
		 * Add scripts / pixels before </body>.
		 */
		do_action( 'smm_coming_soon_footer' );
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
	<title><?php echo esc_html( $smm->page_title( __( 'Coming Soon', 'site-mode-manager' ) ) ); ?></title>
	<style>
		/* ── Reset ───────────────────────────────────────────────────── */
		*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

		/* ── Base ────────────────────────────────────────────────────── */
		:root {
			--smm-bg-from: #0f0c29;
			--smm-bg-to:   #302b63;
			--smm-accent:  #a78bfa;
			--smm-text:    #f5f3ff;
			--smm-muted:   #c4b5fd;
			--smm-card-bg: rgba(255,255,255,0.06);
			--smm-radius:  1rem;
		}

		html, body {
			height: 100%;
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
			background: linear-gradient(135deg, var(--smm-bg-from), var(--smm-bg-to));
			color: var(--smm-text);
		}

		/* ── Layout ──────────────────────────────────────────────────── */
		.smm-page {
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			padding: 2rem 1.5rem;
			text-align: center;
		}

		/* ── Card ────────────────────────────────────────────────────── */
		.smm-card {
			background: var(--smm-card-bg);
			backdrop-filter: blur(12px);
			border: 1px solid rgba(255,255,255,0.12);
			border-radius: var(--smm-radius);
			padding: 3rem 2.5rem;
			max-width: 540px;
			width: 100%;
		}

		/* ── Icon ────────────────────────────────────────────────────── */
		.smm-icon {
			font-size: 4rem;
			line-height: 1;
			margin-bottom: 1.25rem;
			display: block;
		}

		/* ── Headings ────────────────────────────────────────────────── */
		.smm-site-name {
			font-size: 0.875rem;
			font-weight: 600;
			letter-spacing: 0.1em;
			text-transform: uppercase;
			color: var(--smm-accent);
			margin-bottom: 0.5rem;
		}

		.smm-heading {
			font-size: clamp(2rem, 5vw, 3rem);
			font-weight: 800;
			line-height: 1.15;
			margin-bottom: 1rem;
		}

		.smm-subheading {
			font-size: 1.05rem;
			color: var(--smm-muted);
			line-height: 1.6;
			margin-bottom: 2rem;
		}

		/* ── Divider ─────────────────────────────────────────────────── */
		.smm-divider {
			border: none;
			border-top: 1px solid rgba(255,255,255,0.12);
			margin: 2rem 0;
		}

		/* ── Footer ──────────────────────────────────────────────────── */
		.smm-footer {
			margin-top: 2.5rem;
			font-size: 0.8rem;
			color: rgba(255,255,255,0.3);
		}

		/* ── Responsive ──────────────────────────────────────────────── */
		@media (max-width: 480px) {
			.smm-card { padding: 2rem 1.5rem; }
		}
	</style>
	<?php
	/**
	 * Action: smm_coming_soon_head
	 * Add custom <head> content (analytics, fonts, etc.).
	 */
	do_action( 'smm_coming_soon_head' );
	?>
</head>
<body>

<main class="smm-page" role="main">
	<div class="smm-card">

		<span class="smm-icon" role="img" aria-label="<?php esc_attr_e( 'Rocket', 'site-mode-manager' ); ?>">🚀</span>

		<p class="smm-site-name"><?php echo esc_html( $smm->site_name() ); ?></p>

		<h1 class="smm-heading">
			<?php esc_html_e( 'Something awesome is coming', 'site-mode-manager' ); ?>
		</h1>

		<p class="smm-subheading">
			<?php
			echo esc_html(
				apply_filters(
					'smm_coming_soon_message',
					__( 'We\'re working hard to bring you something great. Check back soon.', 'site-mode-manager' )
				)
			);
			?>
		</p>

		<?php
		// Partial: optional email opt-in / countdown timer.
		\SiteModeManager\smm_get_partial( 'coming-soon-extra.php', $smm );
		?>

	</div><!-- .smm-card -->

	<footer class="smm-footer">
		&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $smm->site_name() ); ?>
	</footer>
</main>

<?php
/**
 * Action: smm_coming_soon_footer
 * Add scripts / pixels before </body>.
 */
do_action( 'smm_coming_soon_footer' );
?>
</body>
</html>
