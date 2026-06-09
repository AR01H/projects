<?php
defined( 'ABSPATH' ) || exit;

/**
 * Central control point for the theme's design variants.
 *
 * Driven by THEME_DESIGN_STATUS ('modern' | 'traditional' | 'vintage') which is
 * defined in includes/core_settings.php.
 *
 * Responsibilities:
 *   1. Map each design → body class, design stylesheet, Google-fonts URL.
 *   2. Provide a "slot" override registry so individual sections/components can
 *      receive design-specific classes WITHOUT editing the shared component and
 *      WITHOUT scattering `if ( THEME_DESIGN_STATUS === ... )` across templates.
 *
 * ── Adding a brand-new design ────────────────────────────────────────────────
 *   1. Add an entry to self::$designs.
 *   2. Create assets/css/design-<name>.css scoped under body.design-<name>.
 *   3. (Optional) add slot overrides under self::$overrides['<name>'].
 *   4. Set THEME_DESIGN_STATUS = '<name>'.
 *
 * ── Per-section / per-page override (two ways) ───────────────────────────────
 *   A) Config-driven slot (preferred - central, no template conditionals):
 *        // register once, here in $overrides:
 *        'traditional' => [ 'hero' => 'ch-hero--parchment', ... ]
 *        // then in any template:
 *        <section class="ch-hero <?php echo ch_design_slot( 'hero' ); ?>">
 *
 *   B) Inline per-call (one-off, design value chosen at the call-site):
 *        <div class="ch-card <?php echo ch_design_class( '', 'ch-card--aged' ); ?>">
 *
 *   Both emit nothing in designs that have no override, so the shared/default
 *   look is never disturbed.
 */
class CH_Design_Config {

	/** @var array<string,array{body_class:string,css_file:?string,fonts_url:?string}> */
	private static array $designs = [
		'modern' => [
			'body_class' => 'design-modern',
			'css_file'   => null,
			'fonts_url'  => null,
		],
		'traditional' => [
			'body_class' => 'design-traditional',
			'css_file'   => '/assets/css/design-traditional.css',
			'fonts_url'  => 'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap',
		],
		'vintage' => [
			'body_class' => 'design-vintage',
			'css_file'   => null,
			'fonts_url'  => null,
		],
	];

	/**
	 * Per-design, per-slot extra classes. Keyed by design → slot → class string.
	 * Slots are arbitrary names a template asks for via ch_design_slot('name').
	 * A missing design or slot simply yields '' (no change to the default look).
	 *
	 * @var array<string,array<string,string>>
	 */
	private static array $overrides = [
		'traditional' => [
			// Section / layout slots - opt-in hooks for parchment-specific tweaks.
			'hero'        => 'ch-hero--trad',
			'page_hero'   => 'ch-page-hero--trad',
			'section'     => 'ch-section--trad',
			'card'        => 'ch-card--trad',
			'photo'       => 'ch-photo--trad',      // polaroid/taped photo frame
			'divider'     => 'ch-divider--trad',    // ornamental ❦ rule
			'stamp'       => 'ch-stamp--trad',       // circular rubber-stamp badge
			'carousel'    => 'ch-carousel--trad',
			'form'        => 'ch-form--trad',
        ],
		// 'vintage' => [ ... ] when that design is built
	];

	public static function current(): string {
		$d = defined( 'THEME_DESIGN_STATUS' ) ? (string) THEME_DESIGN_STATUS : 'modern';
		return isset( self::$designs[ $d ] ) ? $d : 'modern';
	}

	public static function is( string $design ): bool {
		return self::current() === $design;
	}

	public static function get(): array {
		return self::$designs[ self::current() ];
	}

	public static function body_class(): string {
		return self::get()['body_class'];
	}

	public static function css_file(): ?string {
		return self::get()['css_file'];
	}

	public static function fonts_url(): ?string {
		return self::get()['fonts_url'];
	}

	/**
	 * Extra class(es) registered for a named slot in the current design.
	 * Returns '' when the current design defines no override for that slot.
	 */
	public static function slot( string $slot ): string {
		return self::$overrides[ self::current() ][ $slot ] ?? '';
	}

	/**
	 * Pick a class by design at the call-site. Pass the value for whichever
	 * designs you care about; the rest fall back to ''.
	 *
	 * @param array<string,string> $by_design e.g. [ 'traditional' => 'x', 'vintage' => 'y' ]
	 */
	public static function pick( array $by_design ): string {
		return $by_design[ self::current() ] ?? '';
	}
}

/* ── Template helper functions ─────────────────────────────────────────────────
 * Thin wrappers so templates stay readable. All are echo-safe (return strings).
 * ──────────────────────────────────────────────────────────────────────────── */

if ( ! function_exists( 'ch_design' ) ) {
	/** Current design key: 'modern' | 'traditional' | 'vintage'. */
	function ch_design(): string {
		return CH_Design_Config::current();
	}
}

if ( ! function_exists( 'ch_design_is' ) ) {
	/** True when the active design matches $design. */
	function ch_design_is( string $design ): bool {
		return CH_Design_Config::is( $design );
	}
}

if ( ! function_exists( 'ch_design_slot' ) ) {
	/** Echo-ready extra class for a registered slot (config-driven override). */
	function ch_design_slot( string $slot ): string {
		return CH_Design_Config::slot( $slot );
	}
}

if ( ! function_exists( 'ch_design_class' ) ) {
	/**
	 * Inline per-call override. Provide the class to use for each design as
	 * needed; omitted designs emit nothing.
	 *
	 *   ch_design_class( modern: '', traditional: 'ch-card--aged' )
	 *   ch_design_class( '', 'ch-card--aged', 'ch-card--retro' )
	 */
	function ch_design_class( string $modern = '', string $traditional = '', string $vintage = '' ): string {
		return CH_Design_Config::pick( [
			'modern'      => $modern,
			'traditional' => $traditional,
			'vintage'     => $vintage,
		] );
	}
}
