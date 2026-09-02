<?php
/**
 * Live Vintage Time, Weather & Location Widget
 *
 * Displays user's laptop 24-hour time, dynamic real-time weather condition, and detected IP location.
 *
 * @package VintageSoulTheme
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="vst-live-badge" id="vst-header-live-badge" title="Live Local Time &amp; Weather">
	<div class="vst-live-badge__inner">
		<!-- Pulsing Live Ambient Dot -->
		<span class="vst-live-badge__pulse" aria-hidden="true"></span>
		
		<!-- Location -->
		<span class="vst-live-badge__item vst-live-badge__location" id="vst-live-location">
			<span class="vst-live-badge__icon" aria-hidden="true">📍</span>
			<span class="vst-live-badge__text">Sutton, London</span>
		</span>

		<span class="vst-live-badge__divider" aria-hidden="true">•</span>

		<!-- Dynamic Weather -->
		<span class="vst-live-badge__item vst-live-badge__weather" id="vst-live-weather">
			<span class="vst-live-badge__icon" id="vst-weather-icon" aria-hidden="true">☀️</span>
			<span class="vst-live-badge__text" id="vst-weather-text">21°C</span>
		</span>

		<span class="vst-live-badge__divider" aria-hidden="true">•</span>

		<!-- 24-Hour Laptop Time (Hours & Minutes Only) -->
		<span class="vst-live-badge__item vst-live-badge__time-box">
			<span class="vst-live-badge__icon" aria-hidden="true">⏱️</span>
			<strong class="vst-live-badge__time" id="vst-live-time">00:00</strong>
		</span>
	</div>
</div>
