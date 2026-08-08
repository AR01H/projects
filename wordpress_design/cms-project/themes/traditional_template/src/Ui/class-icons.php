<?php
/**
 * src/Ui/class-icons.php
 *
 * FEATURE: Icon kit
 * -----------------
 * ONE inline-SVG icon set for the whole theme. Before this, sections used
 * emoji (which render in fixed OS colours and cannot be themed) or one-off
 * inline SVG copied between files.
 *
 * Every icon here is stroke-based and painted with `currentColor`, so it
 * inherits the parchment/gold/ink palette of whatever component holds it -
 * no icon needs its own colour rule, and a re-skin to another brand changes
 * nothing here.
 *
 * Usage (PHP):   app_icon( 'download' );            // echo
 *                $svg = app_icon( 'download', '', false );  // return
 * Usage (data):  any JSON `icon` key may name an icon from self::names().
 *
 * SAFETY: the SVG bodies are hard-coded constants in this file, never user
 * input, so they are echoed as-is. Only the caller-supplied class is dynamic
 * and that is escaped.
 *
 * @package NT\Ui
 */

defined( 'ABSPATH' ) || exit;

class NT_Icons {

	/**
	 * Default viewBox every path below is drawn against.
	 */
	public const VIEWBOX = '0 0 24 24';

	/**
	 * name => inner SVG markup (paths only; the <svg> wrapper is added by get()).
	 *
	 * @var array<string,string>
	 */
	protected static $paths = array(

		// ── Tones (used by dialogs + alerts) ──
		'info'      => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 7.6v.6"/>',
		'success'   => '<circle cx="12" cy="12" r="9"/><path d="m8.2 12.3 2.6 2.6 5-5.4"/>',
		'warning'   => '<path d="M12 4.2 2.9 19.3h18.2L12 4.2Z"/><path d="M12 10v4"/><path d="M12 17.1v.4"/>',
		'error'     => '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6"/><path d="m15 9-6 6"/>',
		'note'      => '<path d="M5 3.6h14v16.8l-3.5-2.4-3.5 2.4-3.5-2.4L5 20.4Z"/><path d="M9 8.4h6"/><path d="M9 12.2h6"/>',
		'question'  => '<circle cx="12" cy="12" r="9"/><path d="M9.6 9.4a2.5 2.5 0 1 1 3.3 2.4c-.6.2-.9.8-.9 1.4v.5"/><path d="M12 16.6v.4"/>',

		// ── Actions / chrome ──
		'close'     => '<path d="m6 6 12 12"/><path d="M18 6 6 18"/>',
		'check'     => '<path d="m5 12.5 4.5 4.5L19 7"/>',
		'plus'      => '<path d="M12 5v14"/><path d="M5 12h14"/>',
		'minus'     => '<path d="M5 12h14"/>',
		'chevron-right' => '<path d="m9.5 5.5 6.5 6.5-6.5 6.5"/>',
		'chevron-down'  => '<path d="m5.5 9.5 6.5 6.5 6.5-6.5"/>',
		'arrow-right'   => '<path d="M4.5 12h15"/><path d="m13.5 6 6 6-6 6"/>',
		'external'  => '<path d="M14 4.5h5.5V10"/><path d="M19.5 4.5 11 13"/><path d="M18 14.5v4a1 1 0 0 1-1 1H5.5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h4"/>',
		'copy'      => '<rect x="9" y="9" width="10.5" height="10.5" rx="2"/><path d="M6 15H5.5a1 1 0 0 1-1-1V5.5a1 1 0 0 1 1-1H14a1 1 0 0 1 1 1V6"/>',
		'search'    => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 3.5 3.5"/>',
		'menu'      => '<path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/>',

		// ── Contact / place ──
		'phone'     => '<path d="M6.2 4.5h3l1.4 3.6-2 1.5a11.4 11.4 0 0 0 5.3 5.3l1.5-2 3.6 1.4v3a1.5 1.5 0 0 1-1.7 1.5C10.6 18.1 5.9 13.4 4.7 6.2A1.5 1.5 0 0 1 6.2 4.5Z"/>',
		'mail'      => '<rect x="3" y="5.5" width="18" height="13" rx="2"/><path d="m3.6 6.8 8.4 6 8.4-6"/>',
		'pin'       => '<path d="M12 21s6.5-6.1 6.5-10.5a6.5 6.5 0 1 0-13 0C5.5 14.9 12 21 12 21Z"/><circle cx="12" cy="10.4" r="2.4"/>',
		'clock'     => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.2v5.1l3.2 1.9"/>',
		'chat'      => '<path d="M20 12.5a7.5 7.5 0 0 1-11 6.6L4.5 20.5l1.4-4.4A7.5 7.5 0 1 1 20 12.5Z"/>',

		// ── Content / documents ──
		'file'      => '<path d="M13.5 3.5H7a1.5 1.5 0 0 0-1.5 1.5v14A1.5 1.5 0 0 0 7 20.5h10a1.5 1.5 0 0 0 1.5-1.5V8.5Z"/><path d="M13.5 3.5v5h5"/>',
		'download'  => '<path d="M12 4v10.5"/><path d="m7.6 10.4 4.4 4.4 4.4-4.4"/><path d="M4.5 19.5h15"/>',
		'image'     => '<rect x="3.5" y="5" width="17" height="14" rx="2"/><circle cx="8.8" cy="10" r="1.6"/><path d="m4.5 17.5 4.7-4.4 3.3 3 2.6-2.3 4.4 3.9"/>',
		'play'      => '<circle cx="12" cy="12" r="8.5"/><path d="M10.4 8.8 15.5 12l-5.1 3.2Z"/>',
		'calendar'  => '<rect x="3.5" y="5.5" width="17" height="15" rx="2"/><path d="M3.5 10h17"/><path d="M8 3.5V7"/><path d="M16 3.5V7"/>',
		'tag'       => '<path d="M4.5 11.4V5a.5.5 0 0 1 .5-.5h6.4l8.1 8.1a1.5 1.5 0 0 1 0 2.1l-4.9 4.9a1.5 1.5 0 0 1-2.1 0Z"/><circle cx="8.6" cy="8.6" r="1.3"/>',
		'user'      => '<circle cx="12" cy="8.4" r="3.6"/><path d="M4.8 20a7.2 7.2 0 0 1 14.4 0"/>',

		// ── Commerce / trust ──
		'briefcase' => '<rect x="3.5" y="7.5" width="17" height="12" rx="2"/><path d="M9 7.5V6a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 6v1.5"/><path d="M3.5 12.5h17"/>',
		'award'     => '<circle cx="12" cy="9.5" r="5.5"/><path d="m8.6 14.2-1.4 6 4.8-2.5 4.8 2.5-1.4-6"/>',
		'shield'    => '<path d="M12 3.6 5.5 6v5.6c0 4 2.7 7.3 6.5 8.8 3.8-1.5 6.5-4.8 6.5-8.8V6Z"/><path d="m9.3 12 1.9 1.9 3.6-3.9"/>',
		'leaf'      => '<path d="M20 4.5C10.9 4 5 8.2 5 14.4A5.6 5.6 0 0 0 10.6 20c6.2 0 9.4-6.6 9.4-15.5Z"/><path d="M7.5 19.5C10 15 13.5 11.6 18 9.5"/>',
		'sparkle'   => '<path d="M12 3.5 13.8 9l5.7 1.8-5.7 1.8L12 18.5l-1.8-5.9L4.5 10.8 10.2 9Z"/>',
		'star'      => '<path d="m12 4 2.5 5.2 5.7.8-4.1 4 1 5.7-5.1-2.7-5.1 2.7 1-5.7-4.1-4 5.7-.8Z"/>',
		'quote'     => '<path d="M9.5 6.5C6.6 7.9 5 10.3 5 13.6c0 2.4 1.3 3.9 3.2 3.9 1.7 0 3-1.2 3-2.9s-1.1-2.8-2.7-2.8h-.4c.2-1.3 1.1-2.5 2.5-3.3Z"/><path d="M19 6.5c-2.9 1.4-4.5 3.8-4.5 7.1 0 2.4 1.3 3.9 3.2 3.9 1.7 0 3-1.2 3-2.9s-1.1-2.8-2.7-2.8h-.4c.2-1.3 1.1-2.5 2.5-3.3Z"/>',

		// ── Navigation / layout ──
		'home'      => '<path d="m3.5 10.6 8.5-6.8 8.5 6.8"/><path d="M5.8 9.3v9.4a1.3 1.3 0 0 0 1.3 1.3h9.8a1.3 1.3 0 0 0 1.3-1.3V9.3"/><path d="M10 20v-5.4h4V20"/>',
		'grid'      => '<rect x="4" y="4" width="7" height="7" rx="1.4"/><rect x="13" y="4" width="7" height="7" rx="1.4"/><rect x="4" y="13" width="7" height="7" rx="1.4"/><rect x="13" y="13" width="7" height="7" rx="1.4"/>',
		'list'      => '<path d="M9 6.5h11"/><path d="M9 12h11"/><path d="M9 17.5h11"/><path d="M4.6 6.5h.01"/><path d="M4.6 12h.01"/><path d="M4.6 17.5h.01"/>',
		'chevron-left'  => '<path d="M14.5 5.5 8 12l6.5 6.5"/>',
		'chevron-up'    => '<path d="m5.5 14.5 6.5-6.5 6.5 6.5"/>',
		'arrow-left'    => '<path d="M19.5 12h-15"/><path d="m10.5 6-6 6 6 6"/>',
		'arrow-up-right' => '<path d="M7.5 16.5 16.5 7.5"/><path d="M9 7.5h7.5V15"/>',
		'expand'    => '<path d="M9 4.5H4.5V9"/><path d="M15 19.5h4.5V15"/><path d="M19.5 9V4.5H15"/><path d="M4.5 15v4.5H9"/>',
		'filter'    => '<path d="M4 5.5h16l-6.2 7.3v5.4l-3.6 1.8v-7.2Z"/>',
		'sort'      => '<path d="M7 4.5v15"/><path d="m3.8 8 3.2-3.5L10.2 8"/><path d="M17 19.5v-15"/><path d="m13.8 16 3.2 3.5 3.2-3.5"/>',
		'refresh'   => '<path d="M19.5 12a7.5 7.5 0 1 1-2.2-5.3"/><path d="M19.5 4.5V9H15"/>',
		'link'      => '<path d="M10.2 13.8a3.5 3.5 0 0 0 5 0l2.6-2.6a3.5 3.5 0 0 0-5-5l-1.2 1.2"/><path d="M13.8 10.2a3.5 3.5 0 0 0-5 0l-2.6 2.6a3.5 3.5 0 0 0 5 5l1.2-1.2"/>',
		'anchor'    => '<circle cx="12" cy="5.6" r="2.1"/><path d="M12 7.7V21"/><path d="M4.5 13.5a7.5 7.5 0 0 0 15 0"/><path d="M4.5 13.5h3"/><path d="M19.5 13.5h-3"/>',

		// ── Media ──
		'camera'    => '<path d="M3.5 8.5h3.2l1.4-2.2h7.8l1.4 2.2h3.2v10a1 1 0 0 1-1 1H4.5a1 1 0 0 1-1-1Z"/><circle cx="12" cy="13.4" r="3.6"/>',
		'video'     => '<rect x="3" y="6.5" width="12.5" height="11" rx="2"/><path d="m15.5 11 5.5-3v8l-5.5-3Z"/>',
		'music'     => '<path d="M9 18V6.4l10-2v11.2"/><circle cx="6.6" cy="18" r="2.4"/><circle cx="16.6" cy="15.6" r="2.4"/>',
		'mic'       => '<rect x="9.4" y="3" width="5.2" height="10.4" rx="2.6"/><path d="M5.8 11.4a6.2 6.2 0 0 0 12.4 0"/><path d="M12 17.6V21"/>',
		'gallery'   => '<rect x="6.5" y="3.5" width="14" height="14" rx="2"/><path d="M3.5 7v12a1.5 1.5 0 0 0 1.5 1.5h12"/><path d="m8 14 3.4-3.2 2.4 2.2 2.2-2 3.4 3.1"/>',
		'pause'     => '<path d="M9.5 5.5v13"/><path d="M14.5 5.5v13"/>',
		'volume'    => '<path d="M5 9.5h3l4-3.2v11.4l-4-3.2H5Z"/><path d="M15.6 9.4a3.6 3.6 0 0 1 0 5.2"/><path d="M18 7a7 7 0 0 1 0 10"/>',

		// ── Commerce ──
		'cart'      => '<circle cx="9.6" cy="19.4" r="1.5"/><circle cx="17.4" cy="19.4" r="1.5"/><path d="M3.5 4.5h2.4l2.4 10.4h10l2-7.4H7"/>',
		'bag'       => '<path d="M5.5 8h13l1 12H4.5Z"/><path d="M8.8 10.4V7.2a3.2 3.2 0 0 1 6.4 0v3.2"/>',
		'card'      => '<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18"/><path d="M6.6 14.4h3.4"/>',
		'gift'      => '<rect x="3.5" y="9" width="17" height="4"/><path d="M5 13v7h14v-7"/><path d="M12 9v11"/><path d="M12 9S10.6 4.5 8.4 4.5a2.2 2.2 0 0 0 0 4.5Z"/><path d="M12 9s1.4-4.5 3.6-4.5a2.2 2.2 0 0 1 0 4.5Z"/>',
		'truck'     => '<path d="M3 6.5h10.5V16H3Z"/><path d="M13.5 9.5h3.9l2.6 3V16h-6.5Z"/><circle cx="7" cy="17.8" r="1.7"/><circle cx="16.6" cy="17.8" r="1.7"/>',
		'receipt'   => '<path d="M5.5 3.5h13v17l-2.2-1.4-2.2 1.4-2.1-1.4-2.2 1.4-2.1-1.4-2.2 1.4Z"/><path d="M9 8.4h6"/><path d="M9 12.2h6"/>',
		'coin'      => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.4v9.2"/><path d="M14.4 9.6a2.6 2.6 0 0 0-2.4-1.3c-1.5 0-2.6.8-2.6 2s1 1.7 2.6 2 2.7.8 2.7 2-1.1 2-2.7 2a2.7 2.7 0 0 1-2.5-1.3"/>',
		'percent'   => '<path d="m6 18 12-12"/><circle cx="7.6" cy="7.6" r="2.1"/><circle cx="16.4" cy="16.4" r="2.1"/>',

		// ── People / social ──
		'users'     => '<circle cx="9.4" cy="8.6" r="3.2"/><path d="M3.5 19.4a5.9 5.9 0 0 1 11.8 0"/><path d="M16 5.7a3.2 3.2 0 0 1 0 5.8"/><path d="M17.4 14.2a5.9 5.9 0 0 1 3.1 5.2"/>',
		'heart'     => '<path d="M12 20s-7.5-4.6-7.5-9.6A4.4 4.4 0 0 1 12 7.6a4.4 4.4 0 0 1 7.5 2.8C19.5 15.4 12 20 12 20Z"/>',
		'thumb-up'  => '<path d="M7.5 20V10.4l4-6.4a2 2 0 0 1 2.9 2.6l-1.4 3.4h4.6a1.8 1.8 0 0 1 1.8 2.2l-1.3 6A1.8 1.8 0 0 1 16.3 20Z"/><path d="M7.5 10.4H4.2V20h3.3"/>',
		'share'     => '<circle cx="17.4" cy="6" r="2.4"/><circle cx="6.6" cy="12" r="2.4"/><circle cx="17.4" cy="18" r="2.4"/><path d="m8.7 10.8 6.6-3.6"/><path d="m8.7 13.2 6.6 3.6"/>',
		'globe'     => '<circle cx="12" cy="12" r="8.5"/><path d="M3.5 12h17"/><path d="M12 3.5a13 13 0 0 1 0 17"/><path d="M12 3.5a13 13 0 0 0 0 17"/>',
		'whatsapp'  => '<path d="M20 11.6a8 8 0 0 1-11.9 7L3.9 20l1.5-4a8 8 0 1 1 14.6-4.4Z"/><path d="M8.9 9.1c0 3 2.3 5.3 5.3 5.3l.9-1.7-1.8-.7-.8.9a4.4 4.4 0 0 1-2-2l.9-.8-.7-1.8Z"/>',
		'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="4.6"/><circle cx="12" cy="12" r="3.9"/><path d="M16.9 7.1h.01"/>',
		'facebook'  => '<path d="M14.6 8.4V6.8c0-.8.5-1.1 1-1.1h1.6V3h-2.6a3.6 3.6 0 0 0-3.6 3.6v1.8H9v2.9h2v9.7h3.6v-9.7h2.3l.4-2.9Z"/>',

		// ── Food & farm (generic naturals — reusable for any produce brand) ──
		'cup'       => '<path d="M5.5 6.5h11l-1 12.2a1.4 1.4 0 0 1-1.4 1.3H7.9a1.4 1.4 0 0 1-1.4-1.3Z"/><path d="M16.2 9.5h1.6a2.5 2.5 0 0 1 0 5h-1.2"/>',
		'bottle'    => '<path d="M10.2 3.5h3.6v2.7c0 1.2 2.2 2.3 2.2 4.6v8.3a1.4 1.4 0 0 1-1.4 1.4H9.4A1.4 1.4 0 0 1 8 19.1v-8.3c0-2.3 2.2-3.4 2.2-4.6Z"/><path d="M8 13.4h8"/>',
		'sprout'    => '<path d="M12 20v-7.4"/><path d="M12 12.6C12 9.5 9.7 7.2 6.6 7.2c0 3.1 2.3 5.4 5.4 5.4Z"/><path d="M12 12.6c0-3.6 2.7-6.4 6.4-6.4 0 3.7-2.8 6.4-6.4 6.4Z"/>',
		'wheat'     => '<path d="M12 21V9.6"/><path d="M12 9.6c-2 0-3.4-1.5-3.4-3.5C10.6 6.1 12 7.6 12 9.6Z"/><path d="M12 9.6c2 0 3.4-1.5 3.4-3.5C13.4 6.1 12 7.6 12 9.6Z"/><path d="M12 14.6c-2 0-3.4-1.5-3.4-3.5 2 0 3.4 1.5 3.4 3.5Z"/><path d="M12 14.6c2 0 3.4-1.5 3.4-3.5-2 0-3.4 1.5-3.4 3.5Z"/>',
		'sun'       => '<circle cx="12" cy="12" r="4.2"/><path d="M12 3v2.2"/><path d="M12 18.8V21"/><path d="M3 12h2.2"/><path d="M18.8 12H21"/><path d="m5.6 5.6 1.6 1.6"/><path d="m16.8 16.8 1.6 1.6"/><path d="m18.4 5.6-1.6 1.6"/><path d="m7.2 16.8-1.6 1.6"/>',
		'drop'      => '<path d="M12 3.5s6 6.4 6 10.3a6 6 0 1 1-12 0C6 9.9 12 3.5 12 3.5Z"/>',
		'flame'     => '<path d="M12 21a6 6 0 0 0 6-6c0-4.5-6-12-6-12S6 10.5 6 15a6 6 0 0 0 6 6Z"/><path d="M12 21a2.6 2.6 0 0 0 2.6-2.6c0-2-2.6-4.9-2.6-4.9s-2.6 2.9-2.6 4.9A2.6 2.6 0 0 0 12 21Z"/>',
		'snow'      => '<path d="M12 3v18"/><path d="m4.2 7.5 15.6 9"/><path d="m19.8 7.5-15.6 9"/><path d="M9.4 4.6 12 6.9l2.6-2.3"/><path d="M9.4 19.4 12 17.1l2.6 2.3"/>',

		// ── Status / system ──
		'lock'      => '<rect x="4.8" y="10.4" width="14.4" height="9.6" rx="2"/><path d="M8.4 10.4V7.8a3.6 3.6 0 0 1 7.2 0v2.6"/>',
		'unlock'    => '<rect x="4.8" y="10.4" width="14.4" height="9.6" rx="2"/><path d="M8.4 10.4V7.8a3.6 3.6 0 0 1 7-1.2"/>',
		'eye'       => '<path d="M2.8 12S6.4 5.8 12 5.8 21.2 12 21.2 12 17.6 18.2 12 18.2 2.8 12 2.8 12Z"/><circle cx="12" cy="12" r="3"/>',
		'bell'      => '<path d="M18 16.6H6l1.4-2.3V10a4.6 4.6 0 0 1 9.2 0v4.3Z"/><path d="M10.2 19.4a2 2 0 0 0 3.6 0"/>',
		'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M12 3.5v2.2"/><path d="M12 18.3v2.2"/><path d="m5.9 5.9 1.6 1.6"/><path d="m16.5 16.5 1.6 1.6"/><path d="M3.5 12h2.2"/><path d="M18.3 12h2.2"/><path d="m5.9 18.1 1.6-1.6"/><path d="m16.5 7.5 1.6-1.6"/>',
		'trash'     => '<path d="M4.8 6.6h14.4"/><path d="M9.2 6.6V4.8a1 1 0 0 1 1-1h3.6a1 1 0 0 1 1 1v1.8"/><path d="M6.6 6.6 7.5 20h9l.9-13.4"/>',
		'edit'      => '<path d="M4.5 19.5h4l10.2-10.2a2.1 2.1 0 0 0-3-3L5.5 16.5Z"/><path d="m14.4 6.6 3 3"/>',
		'print'     => '<path d="M7 8.6V3.6h10v5"/><rect x="3.5" y="8.6" width="17" height="7.6" rx="1.6"/><path d="M7 13.4h10v7H7Z"/>',
		'help'      => '<circle cx="12" cy="12" r="9"/><path d="M9.6 9.4a2.5 2.5 0 1 1 3.3 2.4c-.6.2-.9.8-.9 1.4v.5"/><path d="M12 16.6v.4"/>',
		'spinner'   => '<path d="M12 3.5v3.2"/><path d="M12 17.3v3.2"/><path d="M3.5 12h3.2"/><path d="M17.3 12h3.2"/><path d="m6 6 2.3 2.3"/><path d="m15.7 15.7 2.3 2.3"/><path d="M18 6l-2.3 2.3"/><path d="M8.3 15.7 6 18"/>',
	);

	/**
	 * Every registered icon name. Handy for docs and admin pickers.
	 *
	 * @return string[]
	 */
	public static function names(): array {
		return array_keys( self::$paths );
	}

	/**
	 * Does an icon exist under this name?
	 */
	public static function exists( string $name ): bool {
		return isset( self::$paths[ $name ] );
	}

	/**
	 * Build one icon as an SVG string.
	 *
	 * @param string $name  Icon name from self::names().
	 * @param string $class Extra CSS class(es) on the <svg>.
	 * @param int    $size  Pixel box (width = height). 0 lets CSS size it.
	 * @return string SVG markup, or '' when the name is unknown.
	 */
	public static function get( string $name, string $class = '', int $size = 0 ): string {
		$name = strtolower( trim( $name ) );
		if ( ! isset( self::$paths[ $name ] ) ) {
			return '';
		}

		$classes = trim( 'app-icon app-icon--' . sanitize_html_class( $name ) . ' ' . $class );
		$dims    = $size > 0 ? ' width="' . (int) $size . '" height="' . (int) $size . '"' : '';

		return '<svg class="' . esc_attr( $classes ) . '" viewBox="' . self::VIEWBOX . '"' . $dims
			. ' fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"'
			. ' stroke-linejoin="round" aria-hidden="true" focusable="false">'
			. self::$paths[ $name ]
			. '</svg>';
	}

	/**
	 * Echo an icon. No-op for an unknown name.
	 */
	public static function render( string $name, string $class = '', int $size = 0 ): void {
		echo self::get( $name, $class, $size ); // phpcs:ignore WordPress.Security.EscapeOutput -- constant SVG, class escaped in get().
	}

	/**
	 * Render whatever a JSON `icon` value holds.
	 *
	 * Content editors write either an icon NAME ("download") or an emoji ("🎪").
	 * Named icons become themed inline SVG; anything else is printed as escaped
	 * text so existing emoji-based JSON keeps working untouched.
	 *
	 * @param string $value JSON icon value.
	 * @param string $class Extra CSS class(es).
	 * @return string
	 */
	public static function get_or_text( string $value, string $class = '' ): string {
		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}
		if ( self::exists( strtolower( $value ) ) ) {
			return self::get( $value, $class );
		}
		return '<span class="' . esc_attr( trim( 'app-icon-emoji ' . $class ) ) . '" aria-hidden="true">'
			. esc_html( $value ) . '</span>';
	}
}
