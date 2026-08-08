<?php
/**
 * Shared SVG filter definitions, output once per page from footer.php.
 *
 * These are the "bump" filters: a turbulence field is lit by a distant light
 * and multiplied back over the photo, so the image looks pressed into textured
 * stock rather than printed flat. Two seeds and two light angles, dealt out to
 * a subset of images by initRoughPhotos() in assets/js/common.js - only SOME
 * photos are bumped, so the relief reads as a property of the paper rather
 * than a effect applied to everything.
 *
 * Kept here rather than inline in a component because a filter id must be
 * unique per document: emitting it from a repeated section would duplicate the
 * id and the reference would bind to whichever copy came first.
 *
 * GENERIC: nothing here is tied to a subject or industry.
 */
defined( 'ABSPATH' ) || exit;
?>
<svg class="app-svg-defs" width="0" height="0" aria-hidden="true" focusable="false">
	<defs>
		<filter id="app-bump-1" x="0" y="0" width="100%" height="100%" color-interpolation-filters="sRGB">
			<feTurbulence type="fractalNoise" baseFrequency="0.62" numOctaves="3" seed="5" result="grain"/>
			<feDiffuseLighting in="grain" lighting-color="#ffffff" surfaceScale="1.5" result="relief">
				<feDistantLight azimuth="232" elevation="62"/>
			</feDiffuseLighting>
			<feComposite in="relief" in2="SourceGraphic" operator="arithmetic"
			             k1="1.02" k2="0" k3="0" k4="-0.02"/>
		</filter>

		<filter id="app-bump-2" x="0" y="0" width="100%" height="100%" color-interpolation-filters="sRGB">
			<feTurbulence type="fractalNoise" baseFrequency="0.42" numOctaves="4" seed="23" result="grain"/>
			<feDiffuseLighting in="grain" lighting-color="#fffaf0" surfaceScale="2.1" result="relief">
				<feDistantLight azimuth="145" elevation="55"/>
			</feDiffuseLighting>
			<feComposite in="relief" in2="SourceGraphic" operator="arithmetic"
			             k1="1.05" k2="0" k3="0" k4="-0.04"/>
		</filter>
	</defs>
</svg>
