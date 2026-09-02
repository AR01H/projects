<?php
/**
 * VintageSoulTheme - Realistic Botanical Wind-Flow Breeze Background
 *
 * Renders realistic organic sugarcane leaves drifting on air currents,
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
			<!-- Botanical Sugarcane Leaf Blade 1 -->
			<g id="vst-leaf-blade-a">
				<path d="M0,0 C35,-15 85,-22 140,-8 C95,16 45,18 0,0 Z" fill="#2d6e3c" opacity="0.16"/>
				<path d="M0,0 C45,-6 90,-8 138,-8" fill="none" stroke="#8e622d" stroke-width="0.7" opacity="0.22"/>
				<path d="M30,-4 C40,-12 55,-14 70,-15" fill="none" stroke="#8e622d" stroke-width="0.4" opacity="0.15"/>
				<path d="M60,-7 C75,-16 90,-16 105,-15" fill="none" stroke="#8e622d" stroke-width="0.4" opacity="0.15"/>
			</g>

			<!-- Botanical Sugarcane Leaf Blade 2 (Curved Ribbon) -->
			<g id="vst-leaf-blade-b">
				<path d="M0,0 C28,-20 70,-32 110,-18 C78,12 36,15 0,0 Z" fill="#3d854e" opacity="0.14"/>
				<path d="M0,0 C35,-12 72,-16 108,-18" fill="none" stroke="#caa06d" stroke-width="0.6" opacity="0.25"/>
			</g>

			<!-- Golden Autumn Dry Cane Leaf -->
			<g id="vst-leaf-blade-gold">
				<path d="M0,0 C30,-12 75,-18 120,-6 C85,14 40,15 0,0 Z" fill="#cb924d" opacity="0.15"/>
				<path d="M0,0 C40,-5 80,-7 118,-6" fill="none" stroke="#8e5f2b" stroke-width="0.6" opacity="0.25"/>
			</g>

			<!-- Swaying Botanical Grass / Cane Stalk Base -->
			<g id="vst-grass-tuft-left">
				<path d="M0,1000 C30,850 15,700 80,560" fill="none" stroke="#1b4d27" stroke-width="2.2" opacity="0.16"/>
				<path d="M15,1000 C50,880 70,760 140,640" fill="none" stroke="#2a6639" stroke-width="1.8" opacity="0.14"/>
				<path d="M0,1000 C-10,870 -5,740 40,610" fill="none" stroke="#3d854e" stroke-width="1.5" opacity="0.12"/>
				<!-- Foliage nodes -->
				<use href="#vst-leaf-blade-a" x="65" y="580" transform="scale(0.55) rotate(-35 65 580)"/>
				<use href="#vst-leaf-blade-b" x="125" y="660" transform="scale(0.45) rotate(20 125 660)"/>
				<use href="#vst-leaf-blade-a" x="30" y="630" transform="scale(-0.5, 0.5) rotate(-25 30 630)"/>
			</g>

			<g id="vst-grass-tuft-right">
				<path d="M1600,1000 C1560,860 1580,720 1510,580" fill="none" stroke="#1b4d27" stroke-width="2.2" opacity="0.15"/>
				<path d="M1580,1000 C1540,890 1520,770 1460,660" fill="none" stroke="#2a6639" stroke-width="1.8" opacity="0.14"/>
				<!-- Foliage nodes -->
				<use href="#vst-leaf-blade-b" x="1525" y="600" transform="scale(-0.55, 0.55) rotate(-25 1525 600)"/>
				<use href="#vst-leaf-blade-a" x="1475" y="680" transform="scale(-0.45, 0.45) rotate(15 1475 680)"/>
			</g>
		</defs>

		<!-- 1. Bottom Botanical Grass Clustered Tufts (Swaying in gentle wind) -->
		<g class="vst-breeze-grass-group">
			<use class="vst-grass-sway-left" href="#vst-grass-tuft-left" x="0" y="0"/>
			<use class="vst-grass-sway-right" href="#vst-grass-tuft-right" x="0" y="0"/>
		</g>

		<!-- 2. Realistic Leaves Drifting Along Wind Currents -->
		<g class="vst-breeze-leaves-group">
			<!-- Wind Wave 1: Gentle High Alt Drift -->
			<g class="vst-drifting-leaf vst-drifting-leaf--1">
				<use href="#vst-leaf-blade-a" transform="scale(0.7)"/>
			</g>
			<!-- Wind Wave 2: Mid-Air Swirl & Glide -->
			<g class="vst-drifting-leaf vst-drifting-leaf--2">
				<use href="#vst-leaf-blade-b" transform="scale(0.85)"/>
			</g>
			<!-- Wind Wave 3: Autumn Gold Cane Leaf Floating Down -->
			<g class="vst-drifting-leaf vst-drifting-leaf--3">
				<use href="#vst-leaf-blade-gold" transform="scale(0.65)"/>
			</g>
			<!-- Wind Wave 4: Deep Green Fluttering Blade -->
			<g class="vst-drifting-leaf vst-drifting-leaf--4">
				<use href="#vst-leaf-blade-a" transform="scale(0.6) rotate(180)"/>
			</g>
			<!-- Wind Wave 5: Soft Micro Leaf Breeze -->
			<g class="vst-drifting-leaf vst-drifting-leaf--5">
				<use href="#vst-leaf-blade-b" transform="scale(0.5)"/>
			</g>
			<!-- Wind Wave 6: Floating Golden Fragment -->
			<g class="vst-drifting-leaf vst-drifting-leaf--6">
				<use href="#vst-leaf-blade-gold" transform="scale(0.55)"/>
			</g>
		</g>

		<!-- 3. Golden Sunlit Pollen Motes Floating in Air Flow -->
		<g class="vst-breeze-pollen-group">
			<circle class="vst-pollen-1" cx="300" cy="400" r="1.6" fill="#caa06d" opacity="0.3"/>
			<circle class="vst-pollen-2" cx="700" cy="250" r="1.4" fill="#f6d599" opacity="0.35"/>
			<circle class="vst-pollen-3" cx="1150" cy="550" r="1.8" fill="#caa06d" opacity="0.25"/>
			<circle class="vst-pollen-4" cx="500" cy="750" r="1.3" fill="#f6d599" opacity="0.3"/>
			<circle class="vst-pollen-5" cx="1350" cy="300" r="1.5" fill="#caa06d" opacity="0.28"/>
			<circle class="vst-pollen-6" cx="900" cy="800" r="1.7" fill="#f6d599" opacity="0.32"/>
		</g>
	</svg>
</div>

