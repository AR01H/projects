<?php

namespace VintageSoul\Support;

defined( 'ABSPATH' ) || exit;

class IconHelper {

	public static function get( string $name, string $color = 'currentColor', int $size = 20 ): string {
		$name = strtolower( trim( $name ) );

		$icons = array(
			// Navigation, Direction & Actions
			'pin' => '<svg class="vst-icon vst-icon--pin" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
			'calendar' => '<svg class="vst-icon vst-icon--calendar" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><circle cx="8" cy="14" r="1" fill="' . $color . '"/><circle cx="12" cy="14" r="1" fill="' . $color . '"/><circle cx="16" cy="14" r="1" fill="' . $color . '"/></svg>',
			'clock' => '<svg class="vst-icon vst-icon--clock" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
			'search' => '<svg class="vst-icon vst-icon--search" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
			'arrow-right' => '<svg class="vst-icon vst-icon--arrow-right" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
			'arrow-left' => '<svg class="vst-icon vst-icon--arrow-left" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>',
			'chevron-down' => '<svg class="vst-icon vst-icon--chevron-down" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>',
			'chevron-right' => '<svg class="vst-icon vst-icon--chevron-right" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>',
			'close' => '<svg class="vst-icon vst-icon--close" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',

			// Botanical & Drinks
			'sugarcane' => '<svg class="vst-icon vst-icon--sugarcane" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M8 5c0 3 4 4 4 7M16 9c0 3-4 4-4 7M8 15c0 3 4 3 4 5M16 17c0 2-4 2-4 4"/></svg>',
			'leaf' => '<svg class="vst-icon vst-icon--leaf" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>',
			'plant' => '<svg class="vst-icon vst-icon--plant" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V10M12 10c0-4 4-7 9-7 0 5-3 9-9 9zM12 14c0-3-3-6-7-6 0 4 2 7 7 7z"/></svg>',
			'wheat' => '<svg class="vst-icon vst-icon--wheat" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 22 16 8"/><path d="M3.47 12.53 5 11l1.53 1.53a3.5 3.5 0 0 1 0 4.94L5 19l-1.53-1.53a3.5 3.5 0 0 1 0-4.94Z"/><path d="M7.47 8.53 9 7l1.53 1.53a3.5 3.5 0 0 1 0 4.94L9 15l-1.53-1.53a3.5 3.5 0 0 1 0-4.94Z"/><path d="M11.47 4.53 13 3l1.53 1.53a3.5 3.5 0 0 1 0 4.94L13 11l-1.53-1.53a3.5 3.5 0 0 1 0-4.94Z"/></svg>',
			'cup' => '<svg class="vst-icon vst-icon--cup" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>',
			'drink' => '<svg class="vst-icon vst-icon--drink" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 2h10l-2 18H9L7 2z"/><line x1="5" y1="6" x2="19" y2="6"/><path d="M15 2l2-1"/></svg>',
			'bottles' => '<svg class="vst-icon vst-icon--bottles" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h4v4l2 3v11H4V10l2-3V3z"/><path d="M14 3h4v4l2 3v11h-8V10l2-3V3z"/></svg>',
			'flame' => '<svg class="vst-icon vst-icon--flame" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>',

			// Event, Enterprise & Buildings
			'building' => '<svg class="vst-icon vst-icon--building" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/></svg>',
			'machine' => '<svg class="vst-icon vst-icon--machine" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
			'stall' => '<svg class="vst-icon vst-icon--stall" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/><path d="M2 9h20"/></svg>',
			'store' => '<svg class="vst-icon vst-icon--store" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/></svg>',
			'tent' => '<svg class="vst-icon vst-icon--tent" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20h18L12 4 3 20z"/><path d="M12 4v16"/><path d="M9 20l3-6 3 6"/></svg>',
			'handshake' => '<svg class="vst-icon vst-icon--handshake" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 11V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4"/><path d="M14 13l2-2 4 4-2 2-4-4z"/><path d="M18 17l4-4"/></svg>',
			'growth' => '<svg class="vst-icon vst-icon--growth" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>',
			'book' => '<svg class="vst-icon vst-icon--book" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
			'pack' => '<svg class="vst-icon vst-icon--pack" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
			'delivery' => '<svg class="vst-icon vst-icon--delivery" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
			'scooter' => '<svg class="vst-icon vst-icon--scooter" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="18" r="3"/><circle cx="18" cy="18" r="3"/><path d="M6 15h11a2 2 0 0 0 2-2V7"/><path d="M15 7h4"/><path d="M9 18l3-9h4"/></svg>',

			// Community & Wellness
			'heart' => '<svg class="vst-icon vst-icon--heart" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
			'users' => '<svg class="vst-icon vst-icon--users" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
			'award' => '<svg class="vst-icon vst-icon--award" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>',
			'zap' => '<svg class="vst-icon vst-icon--zap" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
			'shield' => '<svg class="vst-icon vst-icon--shield" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
			'droplet' => '<svg class="vst-icon vst-icon--droplet" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>',
			'hourglass' => '<svg class="vst-icon vst-icon--hourglass" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/></svg>',
			'sparkles' => '<svg class="vst-icon vst-icon--sparkles" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/><path d="M20 3v4"/><path d="M22 5h-4"/></svg>',

			// Contact & Social
			'phone' => '<svg class="vst-icon vst-icon--phone" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
			'whatsapp' => '<svg class="vst-icon vst-icon--whatsapp" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
			'mail' => '<svg class="vst-icon vst-icon--mail" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
			'globe' => '<svg class="vst-icon vst-icon--globe" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
			'check' => '<svg class="vst-icon vst-icon--check" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
			'star' => '<svg class="vst-icon vst-icon--star" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="' . $color . '" stroke="' . $color . '" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
			'instagram' => '<svg class="vst-icon vst-icon--instagram" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>',
			'youtube' => '<svg class="vst-icon vst-icon--youtube" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><polygon points="10 15 15 12 10 9"/></svg>',
			'tiktok' => '<svg class="vst-icon vst-icon--tiktok" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>',
		);

		return $icons[ $name ] ?? '';
	}

	public static function render( string $input, string $color = 'currentColor', int $size = 16 ): string {
		$clean = trim( $input );
		
		// Map common emoji to vector icon names
		$emoji_map = array(
			'📍' => 'pin',
			'📅' => 'calendar',
			'🗓️' => 'calendar',
			'🌾' => 'sugarcane',
			'🥤' => 'drink',
			'☕' => 'cup',
			'🎪' => 'tent',
			'📖' => 'book',
			'📚' => 'book',
			'✉️' => 'mail',
			'📧' => 'mail',
			'📞' => 'phone',
			'☎️' => 'phone',
			'🌿' => 'leaf',
			'🌱' => 'plant',
			'⭐' => 'star',
			'★' => 'star',
			'✓' => 'check',
			'🏪' => 'store',
			'❤️' => 'heart',
			'👥' => 'users',
			'🎉' => 'award',
			'🏛️' => 'store',
			'🏢' => 'building',
			'🔥' => 'flame',
			'⚙️' => 'machine',
			'⚡' => 'zap',
			'💧' => 'droplet',
			'✨' => 'sparkles',
		);

		if ( isset( $emoji_map[ $clean ] ) ) {
			return self::get( $emoji_map[ $clean ], $color, $size );
		}

		$got = self::get( $clean, $color, $size );
		if ( '' !== $got ) {
			return $got;
		}

		if ( str_starts_with( $clean, '<svg' ) ) {
			return $clean;
		}

		return esc_html( $clean );
	}
}
