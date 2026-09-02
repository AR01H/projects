<?php
/**
 * VintageSoulTheme - Realistic Botanical Wind-Flow Breeze Background
 *
 * Renders realistic organic sugarcane leaves drifting and gently floating on air currents,
 * swaying botanical grass stalks, and subtle golden sunlit pollen motes.
 *
 * Usage: View::component('background/parchment-botanical-bg', ['seed' => 42]);
 */

defined( 'ABSPATH' ) || exit;

$seed  = (int) ( $seed ?? 1 );
$class = isset( $class ) ? ' ' . (string) $class : '';
?>
<div class="vst-parchment-botanical-bg<?php echo esc_attr( $class ); ?>" aria-hidden="true">
	<!-- Realistic Botanical Wind Current Canvas -->
	<svg class="vst-breeze-canvas" viewBox="0 0 1600 1000" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
		<defs>
			<!-- Botanical Sugarcane Leaf Blade 1 (Classic Forest Green) -->
			<g id="vst-leaf-blade-a">
				<path d="M0,0 C35,-15 85,-22 140,-8 C95,16 45,18 0,0 Z" fill="#2d6e3c" opacity="0.22"/>
				<path d="M0,0 C45,-6 90,-8 138,-8" fill="none" stroke="#8e622d" stroke-width="0.8" opacity="0.32"/>
				<path d="M30,-4 C40,-12 55,-14 70,-15" fill="none" stroke="#8e622d" stroke-width="0.5" opacity="0.2"/>
				<path d="M60,-7 C75,-16 90,-16 105,-15" fill="none" stroke="#8e622d" stroke-width="0.5" opacity="0.2"/>
			</g>

			<!-- Botanical Sugarcane Leaf Blade 2 (Curved Emerald Ribbon) -->
			<g id="vst-leaf-blade-b">
				<path d="M0,0 C28,-20 70,-32 110,-18 C78,12 36,15 0,0 Z" fill="#3d854e" opacity="0.2"/>
				<path d="M0,0 C35,-12 72,-16 108,-18" fill="none" stroke="#caa06d" stroke-width="0.7" opacity="0.35"/>
			</g>

			<!-- Golden Autumn Dry Cane Leaf -->
			<g id="vst-leaf-blade-gold">
				<path d="M0,0 C30,-12 75,-18 120,-6 C85,14 40,15 0,0 Z" fill="#cb924d" opacity="0.22"/>
				<path d="M0,0 C40,-5 80,-7 118,-6" fill="none" stroke="#8e5f2b" stroke-width="0.7" opacity="0.35"/>
			</g>
		</defs>

		<!-- 1. Bottom Botanical Grass & Cane Stalks (Direct Groups for Smooth GPU-Accelerated Wind Sway) -->
		<g class="vst-grass-container">
			<!-- Left Grass Tuft Group -->
			<g class="vst-grass-stalk-left">
				<path d="M0,1000 C30,830 15,670 85,520" fill="none" stroke="#1b4d27" stroke-width="2.8" opacity="0.22"/>
				<path d="M20,1000 C60,860 85,730 160,600" fill="none" stroke="#2a6639" stroke-width="2.2" opacity="0.2"/>
				<path d="M0,1000 C-15,850 -5,710 45,570" fill="none" stroke="#3d854e" stroke-width="1.8" opacity="0.18"/>
				<path d="M10,1000 C40,900 30,800 110,710" fill="none" stroke="#1b4d27" stroke-width="1.5" opacity="0.16"/>
				<!-- Foliage Leaves attached to Stalks -->
				<g transform="translate(80, 530) rotate(-35) scale(0.75)">
					<use href="#vst-leaf-blade-a"/>
				</g>
				<g transform="translate(150, 620) rotate(20) scale(0.65)">
					<use href="#vst-leaf-blade-b"/>
				</g>
				<g transform="translate(40, 580) rotate(-25) scale(-0.7, 0.7)">
					<use href="#vst-leaf-blade-a"/>
				</g>
			</g>

			<!-- Right Grass Tuft Group -->
			<g class="vst-grass-stalk-right">
				<path d="M1600,1000 C1550,840 1575,690 1500,540" fill="none" stroke="#1b4d27" stroke-width="2.8" opacity="0.22"/>
				<path d="M1580,1000 C1535,870 1515,740 1445,620" fill="none" stroke="#2a6639" stroke-width="2.2" opacity="0.2"/>
				<path d="M1600,1000 C1620,860 1610,720 1550,590" fill="none" stroke="#3d854e" stroke-width="1.8" opacity="0.18"/>
				<!-- Foliage Leaves attached to Stalks -->
				<g transform="translate(1510, 550) rotate(-25) scale(-0.75, 0.75)">
					<use href="#vst-leaf-blade-b"/>
				</g>
				<g transform="translate(1455, 640) rotate(15) scale(0.65)">
					<use href="#vst-leaf-blade-a"/>
				</g>
			</g>
		</g>

		<!-- 2. Ambient Floating Botanical Leaves (Continually Swaying & Bobbing Across Screen) -->
		<g class="vst-breeze-ambient-group">
			<!-- Top-Left Floating Emerald Blade -->
			<g class="vst-ambient-leaf vst-ambient-leaf--1">
				<use href="#vst-leaf-blade-a" transform="scale(0.9)"/>
			</g>

			<!-- Top-Center Floating Golden Fragment -->
			<g class="vst-ambient-leaf vst-ambient-leaf--2">
				<use href="#vst-leaf-blade-gold" transform="scale(0.8)"/>
			</g>

			<!-- Top-Right Floating Curved Leaf -->
			<g class="vst-ambient-leaf vst-ambient-leaf--3">
				<use href="#vst-leaf-blade-b" transform="scale(0.95)"/>
			</g>

			<!-- Mid-Left Swaying Cane Leaf -->
			<g class="vst-ambient-leaf vst-ambient-leaf--4">
				<use href="#vst-leaf-blade-b" transform="scale(0.75)"/>
			</g>

			<!-- Mid-Right Floating Golden Leaf -->
			<g class="vst-ambient-leaf vst-ambient-leaf--5">
				<use href="#vst-leaf-blade-gold" transform="scale(0.85)"/>
			</g>

			<!-- Center Subtle Drifting Leaf -->
			<g class="vst-ambient-leaf vst-ambient-leaf--6">
				<use href="#vst-leaf-blade-a" transform="scale(0.7) rotate(40)"/>
			</g>
		</g>

		<!-- 3. Dynamic Wind Breeze Leaves Gliding Across Canvas -->
		<g class="vst-breeze-leaves-group">
			<g class="vst-drifting-leaf vst-drifting-leaf--1">
				<use href="#vst-leaf-blade-a" transform="scale(0.8)"/>
			</g>
			<g class="vst-drifting-leaf vst-drifting-leaf--2">
				<use href="#vst-leaf-blade-b" transform="scale(0.9)"/>
			</g>
			<g class="vst-drifting-leaf vst-drifting-leaf--3">
				<use href="#vst-leaf-blade-gold" transform="scale(0.75)"/>
			</g>
			<g class="vst-drifting-leaf vst-drifting-leaf--4">
				<use href="#vst-leaf-blade-a" transform="scale(0.7) rotate(180)"/>
			</g>
		</g>

		<!-- 4. Golden Sunlit Pollen Motes Floating in Air Flow -->
		<g class="vst-breeze-pollen-group">
			<circle class="vst-pollen-1" cx="300" cy="400" r="2.0" fill="#caa06d" opacity="0.4"/>
			<circle class="vst-pollen-2" cx="700" cy="250" r="1.6" fill="#f6d599" opacity="0.45"/>
			<circle class="vst-pollen-3" cx="1150" cy="550" r="2.2" fill="#caa06d" opacity="0.35"/>
			<circle class="vst-pollen-4" cx="500" cy="750" r="1.5" fill="#f6d599" opacity="0.4"/>
			<circle class="vst-pollen-5" cx="1350" cy="300" r="1.8" fill="#caa06d" opacity="0.38"/>
			<circle class="vst-pollen-6" cx="900" cy="800" r="2.0" fill="#f6d599" opacity="0.42"/>
		</g>
	</svg>
</div>
