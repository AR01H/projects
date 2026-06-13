<?php
/**
 * calculators/calculators.php - Calculator engine (shortcode + isolated renderer).
 *
 * Goal: embed a single calculator anywhere with [ah_calculator key="stamp-duty"]
 * and have ONLY the calculator appear - no header, footer, sidebar or page
 * content - fully isolated so its CSS/JS can neither affect nor be affected by
 * the host page.
 *
 * How it stays isolated: the shortcode outputs an <iframe> pointing at
 * home_url('/?ah_calc=KEY'). That request is intercepted on template_redirect
 * and answered with a minimal standalone HTML document containing just the
 * calculator view + its own CSS/JS, then exits before WordPress ever loads the
 * theme's header/footer. The iframe auto-resizes to its content via postMessage.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/registry.php';

/** Is $key a registered AND enabled calculator? */
function adn_calculator_exists( $key ) {
	$all = adn_calculators();
	if ( ! is_string( $key ) || '' === $key || ! isset( $all[ $key ] ) ) {
		return false;
	}
	return adn_calculator_is_enabled( $key );
}

/**
 * Whether a calculator is enabled (Manage Calculator → Calculator List).
 * Default - before the list has ever been saved - is "all enabled".
 */
function adn_calculator_is_enabled( $key ) {
	$meta = get_option( 'adn_calculators_meta' );
	if ( ! is_array( $meta ) || ! isset( $meta[ $key ] ) || ! array_key_exists( 'enabled', (array) $meta[ $key ] ) ) {
		return true;
	}
	return ! empty( $meta[ $key ]['enabled'] );
}

/**
 * Per-calculator admin meta merged with registry defaults:
 * { enabled, label, help, guide_label, guide_url }.
 */
function adn_calculator_meta( $key ) {
	$all  = adn_calculators();
	$base = isset( $all[ $key ] ) ? $all[ $key ] : array();

	$defaults = array(
		'enabled'     => 1,
		'label'       => isset( $base['label'] ) ? $base['label'] : $key,
		'help'        => '',
		'guide_label' => '',
		'guide_url'   => '',
	);

	$meta = get_option( 'adn_calculators_meta' );
	$row  = ( is_array( $meta ) && isset( $meta[ $key ] ) && is_array( $meta[ $key ] ) ) ? $meta[ $key ] : array();

	return array_merge( $defaults, array_filter( $row, static function ( $v ) {
		return '' !== $v && null !== $v;
	} ) );
}

/** Include a calculator view, realpath-guarded to /calculators/views/. */
function adn_load_calculator_view( $key ) {
	$base = realpath( ADN_THEME_DIR . '/calculators/views' );
	$file = realpath( ADN_THEME_DIR . '/calculators/views/' . $key . '.php' );
	if ( $base && $file && 0 === strpos( $file, $base ) && is_file( $file ) ) {
		include $file;
	}
}

/**
 * Intercept ?ah_calc=KEY and render ONLY that calculator, then stop. Runs at an
 * early priority so it answers even when the site is in coming-soon mode.
 */
function adn_calculator_maybe_render() {
	if ( ! isset( $_GET['ah_calc'] ) || '' === $_GET['ah_calc'] ) {
		return;
	}
	$key = sanitize_key( wp_unslash( $_GET['ah_calc'] ) );
	nocache_headers();

	if ( ! adn_calculator_exists( $key ) ) {
		status_header( 404 );
		echo '<!doctype html><meta charset="utf-8"><p style="font:14px/1.5 system-ui,sans-serif;padding:24px;color:#b91c1c">Calculator not found.</p>';
		exit;
	}

	adn_render_calculator_standalone( $key );
	exit;
}

/** Print the minimal standalone document for one calculator (the iframe target). */
function adn_render_calculator_standalone( $key ) {
	$all  = adn_calculators();
	$calc = $all[ $key ];
	$ver  = defined( 'ADN_THEME_VERSION' ) ? ADN_THEME_VERSION : '1.0';
	$css  = ADN_THEME_URI . '/calculators/assets/calculators.css?v=' . $ver;
	$js   = ADN_THEME_URI . '/calculators/assets/calc-' . $key . '.js?v=' . $ver;
	$has_js = file_exists( ADN_THEME_DIR . '/calculators/assets/calc-' . $key . '.js' );

	header( 'Content-Type: text/html; charset=utf-8' );
	?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex,nofollow">
	<title><?php echo esc_html( $calc['title'] ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( $css ); ?>">
</head>
<body class="ah-calc-body ah-calc-<?php echo esc_attr( $key ); ?>">
	<div class="ah-calc-wrap"><?php adn_load_calculator_view( $key ); ?></div>
	<?php
	$meta = function_exists( 'adn_calculator_meta' ) ? adn_calculator_meta( $key ) : array();
	if ( ! empty( $meta['help'] ) || ! empty( $meta['guide_url'] ) ) :
		?>
		<div class="ah-calc-extra">
			<?php if ( ! empty( $meta['help'] ) ) : ?>
				<p class="ah-calc-help"><?php echo esc_html( $meta['help'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $meta['guide_url'] ) ) : ?>
				<a class="ah-calc-guide" href="<?php echo esc_url( $meta['guide_url'] ); ?>" target="_top">
					<?php echo esc_html( ! empty( $meta['guide_label'] ) ? $meta['guide_label'] : 'Read the guide →' ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<?php if ( $has_js ) : ?>
		<script src="<?php echo esc_url( $js ); ?>"></script>
	<?php endif; ?>
	<script>
	/* Report content height to the host page so the iframe can auto-resize. */
	(function () {
		var key = <?php echo wp_json_encode( $key ); ?>;
		function report() {
			var h = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
			parent.postMessage({ ahCalc: true, key: key, height: h }, '*');
		}
		window.addEventListener('load', report);
		window.addEventListener('resize', report);
		document.addEventListener('input', report);
		document.addEventListener('change', report);
		document.addEventListener('click', report);
		setInterval(report, 700);
	})();
	</script>
</body>
</html>
	<?php
}

/**
 * [ah_calculator key="stamp-duty" height="560"]
 * Outputs an isolated, auto-resizing iframe holding just that calculator.
 */
function adn_calculator_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'key'    => '',
			'height' => '560',
		),
		$atts,
		'ah_calculator'
	);

	$key = sanitize_key( $atts['key'] );
	if ( ! adn_calculator_exists( $key ) ) {
		return current_user_can( 'edit_posts' )
			? '<p style="color:#b91c1c;font-size:.9rem">[ah_calculator] unknown calculator key: "' . esc_html( $atts['key'] ) . '"</p>'
			: '';
	}

	$all = adn_calculators();
	$src = home_url( '/?ah_calc=' . rawurlencode( $key ) );
	$uid = 'ahcalc_' . $key . '_' . wp_rand( 1000, 9999 );
	$h   = max( 200, (int) $atts['height'] );

	ob_start();
	?>
	<iframe id="<?php echo esc_attr( $uid ); ?>" class="ah-calc-frame"
		src="<?php echo esc_url( $src ); ?>"
		title="<?php echo esc_attr( $all[ $key ]['title'] ); ?>"
		loading="lazy" scrolling="no"
		style="width:100%;border:0;height:<?php echo (int) $h; ?>px;overflow:hidden;display:block"></iframe>
	<script>
	(function () {
		var frame = document.getElementById(<?php echo wp_json_encode( $uid ); ?>);
		if (!frame) { return; }
		window.addEventListener('message', function (e) {
			var d = e.data;
			if (d && d.ahCalc && d.key === <?php echo wp_json_encode( $key ); ?> && d.height) {
				frame.style.height = (parseInt(d.height, 10) + 2) + 'px';
			}
		});
	})();
	</script>
	<?php
	return ob_get_clean();
}

add_shortcode( 'ah_calculator', 'adn_calculator_shortcode' );
add_action( 'template_redirect', 'adn_calculator_maybe_render', 0 );
