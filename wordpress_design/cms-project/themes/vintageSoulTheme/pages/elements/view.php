<?php

use VintageSoul\Controllers\PageController;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new PageController() )->prepare();
?>
<div class="section">
	<div class="container">
		<h1><?php echo esc_html( $data['title'] ); ?></h1>
		<p>Every component this base theme ships, in one place, for visual + interaction testing only.</p>

		<section class="section section--sm">
			<h2>Hero</h2>
			<p>From <code>components/hero/hero.php</code> + <code>assets/css/components/hero.css</code> - matching <code>data/content/hero.json</code>'s shape. The photo carries a sepia/contrast color grade (<code>filter: sepia() saturate() contrast()</code> on the <code>&lt;img&gt;</code>, composing with the Ken Burns zoom which is a separate <code>transform</code> animation on the same element - filter and transform don't fight each other) for a warmer, more vintage tone than a raw photo. Text sits directly on the photo, not a card - legibility comes from a dark bottom-weighted gradient on <code>.hero__media::after</code> plus a second radial scrim anchored specifically behind <code>.hero__content</code> and a text-shadow stack on every line. <code>title</code> cycles three photo-safe colors (<code>--color-primary-contrast</code>/<code>--color-secondary</code>/<code>--color-primary-shine</code>). The photo layer (<code>.hero__media</code>) is a clean full-width rectangle - no torn-edge mask on Hero specifically (removed on request) - just the vintage-grain vignette wash and one random corner accent (<code>tex-organic-*</code>/<code>tex-botanical-a</code>/<code>tex-stamp-a</code>). Content cascades in on load - title lines first, then subtitle/rule, then checks, then actions - and the photo carries both the Ken Burns zoom and real scroll-linked parallax (<code>assets/js/core/parallax.js</code>) independently. Reload this page a few times to see the random corner accent change.</p>
			<?php
			View::component(
				'hero/hero',
				array(
					'title'   => 'Watch It. Taste It. Love It.',
					'subtitle' => 'Fresh Sugarcane Juice',
					'checks'  => array( 'Freshly Pressed', 'Naturally Refreshing', 'Always Made With Care' ),
					'image'   => 'https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=1200&q=72&auto=format&fit=crop',
					'buttons' => array(
						array( 'label' => 'Explore More', 'icon' => '📍', 'route' => 'game', 'style' => 'ghost' ),
						array( 'label' => 'Book Us For Your Event', 'icon' => '📅', 'route' => 'contact' ),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Textures</h2>
			<p>From <code>assets/images/textures/</code> (41 real SVG assets across 20 category folders) + <code>assets/images/backgrounds/</code> (a few real hand-illustrated PNGs - leaf-fall, ground-cane, ground-soil, the footer's own brand watermark) + <code>assets/css/textures.css</code> (<code>tex-*</code> classes). Every visual comes from an actual asset file - CSS only positions/sizes/blends/tints it, never draws the texture itself. Image paths for the illustrated PNGs are <code>variables.css</code> tokens (<code>--tex-*-image</code>), not hardcoded inline in <code>textures.css</code> - same reason the footer's own watermark is data, not a literal path (see that component's docblock). Prefixed <code>tex-</code> on purpose: <code>shape.css</code> already owns plain names like <code>.grain-a</code>/<code>.roughness-a</code>/<code>.fade-a</code> as CSS-generated effects, so this system doesn't collide with or override anything already in use. Three composable slots - <code>::before</code> (one full-bleed tile), <code>::after</code> (one accent mark - brush/ink/stain/dust/stamp/organic/botanical/border - front, or a shadow behind), <code>mask-image</code> on the element (one shape change: fade/edge/cut) - see the file's own top comment for the full mixing rules, including why there's no border-image slot (didn't render reliably here, so tex-border-* is a mask+background-color accent instead). Already wired into real components: Hero's image (above), Gallery's photos, and the Banner panel below. Deliberately <strong>not</strong> added to Testimonial card - its <code>::before</code> is already claimed by that component's own torn-edge treatment, and its avatar is a bare <code>&lt;img&gt;</code>, which doesn't reliably render generated <code>::before</code>/<code>::after</code> content in most browsers.</p>

			<h3>Section cut - a strip between two sections, not a filter on either one</h3>
			<p>Give it the colour of whichever section comes <em>after</em> it, pull it up over the section before it, and the boundary reads as one bleeding into the other instead of a flat rectangular stack.</p>
			<div style="background: var(--color-bg-alt); padding: var(--space-lg) var(--space-sm) var(--space-2xl);">Section A (<code>--color-bg-alt</code>)</div>
			<div class="section-cut tex-cut-brush-a" style="background-color: var(--color-bg);" aria-hidden="true"></div>
			<div style="background: var(--color-bg); padding: var(--space-2xl) var(--space-sm) var(--space-lg);">Section B (<code>--color-bg</code>)</div>

			<h3>Category swatches (one variant shown per family - most have a second, see the folder)</h3>
			<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: var(--space-md);">
				<div class="tex-grain-a" style="height: 110px; background: var(--color-bg-alt); display: flex; align-items: center; justify-content: center;">grain-a</div>
				<div class="tex-roughness-b" style="height: 110px; background: var(--color-bg-alt); display: flex; align-items: center; justify-content: center;">roughness-b</div>
				<div class="tex-paper-aged-a" style="height: 110px; background: var(--color-bg-alt); display: flex; align-items: center; justify-content: center;">paper-aged-a</div>
				<div class="tex-film-grain-a" style="height: 110px; background-image: url('https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=300&q=60&auto=format&fit=crop'); background-size: cover; display: flex; align-items: center; justify-content: center; color: #fff;">film-grain-a</div>
				<div class="tex-wash-warm-a" style="height: 110px; background-image: url('https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=300&q=60&auto=format&fit=crop'); background-size: cover; display: flex; align-items: center; justify-content: center; color: #fff;">wash-warm-a</div>
				<div class="tex-vintage-grain-a tex-edge-a" style="height: 110px; background-image: url('https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=300&q=60&auto=format&fit=crop'); background-size: cover; display: flex; align-items: center; justify-content: center; color: #fff;">vintage-grain-a + edge-a</div>
				<div class="tex-brush-a" style="height: 110px; margin-bottom: 0.7em; background: var(--color-bg-alt); display: flex; align-items: center; justify-content: center;">brush-a (::after, look below)</div>
				<div class="tex-stamp-a" style="height: 110px; background: var(--color-bg-alt); display: flex; align-items: center; justify-content: center;">stamp-a (corner)</div>
				<div class="tex-organic-a" style="height: 110px; background: var(--color-bg-alt); display: flex; align-items: center; justify-content: center; overflow: hidden;">organic-a</div>
				<div class="tex-border-vintage-a" style="height: 110px; display: flex; align-items: center; justify-content: center;">border-vintage-a</div>
				<div class="tex-leaf-fall-a" style="height: 110px; background: var(--color-bg-alt); display: flex; align-items: center; justify-content: center;">leaf-fall-a</div>
			</div>

			<h3>Ground-line strips (illustrated PNG, sits along a section's bottom edge)</h3>
			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: var(--space-md);">
				<div class="tex-ground-cane-a" style="height: 90px; background: var(--color-bg-alt);"></div>
				<div class="tex-ground-soil-a" style="height: 90px; background: var(--color-primary);"></div>
			</div>
		</section>

		<section class="section section--sm">
			<h2>Scroll reveal</h2>
			<p>From <code>assets/css/animations.css</code> + <code>assets/js/core/reveal.js</code> - theme-wide, not a component you call. One shared <code>IntersectionObserver</code> watches a fixed selector list; each element gets one class added (<code>.is-revealed</code>) once ~15% visible, then stops being watched - a one-shot reveal, not a repeat-every-scroll effect. Siblings under the same parent stagger by 90ms (capped at 6). What differs PER component is not the mechanism but the CSS each one defines for that same class - a deliberate library of distinct reveal styles, not one fade-up reused everywhere:</p>
			<ul>
				<li><strong>Rise</strong> - opacity + translateY(28px). Cards, feature-row/step-chain/product-list/FAQ items.</li>
				<li><strong>Scale</strong> - opacity + scale(0.88). Photo grid items, the standalone photo-stamp.</li>
				<li><strong>Pan</strong> - opacity + scale(0.92) + translateX(30px), 900ms. Gallery items - a camera-pan-into-frame feel, distinct from the plain scale above.</li>
				<li><strong>Scale-pop</strong> - a deeper scale(0.8), snappier. Stats items.</li>
				<li><strong>Photograph development</strong> - <code>filter: blur(10px) grayscale(85%) sepia(15%)</code> + a -2.5deg rotation, clearing to sharp/full-colour/level over 1000ms. Testimonial cards, video testimonial cards, memories - photos being placed on a table and coming into focus, not just fading in.</li>
				<li><strong>Mechanical click-in</strong> - rotate(-10deg) scale(0.82) snapping to level, 700ms. Process steps - no literal gear artwork exists yet, so this is an honest stand-in for that "clicking into place" feel until real machine-part imagery is sourced.</li>
				<li><strong>Cinematic film-wipe</strong> - two-part, not one. <code>.banner__media</code> reveals via <code>clip-path: inset()</code> sliding open from one edge (the side flips with <code>--reverse</code>); <code>.banner__content</code> fades/rises in 350ms behind it. The image uncovers first, the copy follows - see <code>components/banner</code>'s own CSS for the exact clip-path values.</li>
				<li><strong>Fade only</strong> - no movement, just opacity. Section headers - deliberately the quietest reveal in the set, so the louder ones (film-wipe, photo development) keep reading as louder.</li>
				<li><strong>Sepia fade</strong> - opacity + <code>filter: sepia(65%) brightness(0.65)</code> clearing to natural colour over 1200ms, the slowest reveal in the library. The site footer only - reads as the page settling into an aged photograph rather than a hard cut to "here's the footer."</li>
				<li><strong>Generic text rise / button pop</strong> - <code>.container &gt; h1/h2/h3/p</code> and <code>.container &gt; .btn/.badge</code> (direct children only, so text/buttons already inside an animating card or banner don't double-animate) get a plain rise or scale-pop - the catch-all for loose copy that isn't part of a more specific component.</li>
			</ul>
			<p>No flash-of-hidden-content: every hidden-by-default rule above is gated behind an <code>html.js</code> class set by a tiny synchronous inline script at the very top of <code>header.php</code>'s <code>&lt;head&gt;</code>, so it's present before anything in <code>&lt;body&gt;</code> paints. JS disabled, or <code>IntersectionObserver</code> unsupported, both leave content fully visible from the start instead of stuck invisible. Respects <code>prefers-reduced-motion</code> throughout. To add a new component, add its class to BOTH the relevant rule in <code>animations.css</code> and the <code>SELECTOR</code> string in <code>reveal.js</code> - they're not shared since one's CSS and the other's JS. The Hero and Hero carousel above don't use this system at all - they're above the fold, so their own entrance (a fixed page-load cascade for Hero, an <code>.is-active</code>-triggered one that replays per slide for Hero carousel) fires immediately instead of waiting on scroll position. Hero also gets real scroll-linked parallax (<code>assets/js/core/parallax.js</code> - a rAF-throttled scroll listener nudging <code>.hero__media</code>'s own <code>translateY</code>, independent of the Ken Burns zoom already on the <code>&lt;img&gt;</code> itself, composing into a combined push-in-with-depth feel) - not IntersectionObserver-driven, since it needs to track scroll position continuously rather than fire once.</p>
		</section>

		<section class="section section--sm">
			<h2>Buttons</h2>
			<p>
				<button type="button" class="btn">Primary</button>
				<button type="button" class="btn btn--secondary">Secondary</button>
				<button type="button" class="btn btn--outline">Outline</button>
				<button type="button" class="btn btn--danger">Danger</button>
				<button type="button" class="btn" disabled>Disabled</button>
			</p>
			<p>
				<button type="button" class="btn btn--sm">Small</button>
				<button type="button" class="btn">Default</button>
				<button type="button" class="btn btn--lg">Large</button>
			</p>

			<h3>+ shape treatments (assets/css/shape.css - generic, not button-specific)</h3>
			<p>
				<button type="button" class="btn cut-b">Cut B</button>
				<button type="button" class="btn roughness-b">Roughness B</button>
				<button type="button" class="btn edge-a">Edge A</button>
				<button type="button" class="btn btn--secondary cut-a">Secondary + Cut A</button>
				<button type="button" class="btn btn--danger roughness-c">Danger + Roughness C</button>
				<button type="button" class="btn btn--lg edge-b">Large + Edge B</button>
			</p>
		</section>

		<section class="section section--sm">
			<h2>Shape treatments (generic)</h2>
			<p>From <code>assets/css/shape.css</code> - not scoped to buttons. Group A (cut/edge/roughness) changes the outline; Group B (grain/noise/texture/distress/fade/gradient) changes the surface finish; Group C (shadow/border) are small additive utilities. See that file's own comment for exactly what can/can't combine.</p>

			<h3>Cuts - unsized + sized</h3>
			<p>
				<button type="button" class="btn cut-a">cut-a</button>
				<button type="button" class="btn cut-b">cut-b</button>
				<button type="button" class="btn cut-c">cut-c</button>
				<button type="button" class="btn cut-md-a">cut-md-a</button>
				<button type="button" class="btn cut-lg-a">cut-lg-a</button>
				<button type="button" class="btn cut-xl-a">cut-xl-a</button>
			</p>

			<h3>Edges - three characters, sized</h3>
			<p>
				<button type="button" class="btn edge-a">edge-a (soft)</button>
				<button type="button" class="btn edge-b">edge-b (jagged)</button>
				<button type="button" class="btn edge-c">edge-c (wavy)</button>
				<button type="button" class="btn edge-md-b">edge-md-b</button>
				<button type="button" class="btn edge-lg-b">edge-lg-b</button>
				<button type="button" class="btn edge-xl-b">edge-xl-b</button>
			</p>

			<h3>Roughness - unsized + sized</h3>
			<p>
				<button type="button" class="btn roughness-a">roughness-a</button>
				<button type="button" class="btn roughness-b">roughness-b</button>
				<button type="button" class="btn roughness-c">roughness-c</button>
				<button type="button" class="btn roughness-md-b">roughness-md-b</button>
				<button type="button" class="btn roughness-lg-b">roughness-lg-b</button>
				<button type="button" class="btn roughness-xl-b">roughness-xl-b</button>
			</p>

			<h3>Grain (::before) vs Noise (::before, alternative) - sized</h3>
			<p>
				<button type="button" class="btn grain-sm-b">grain-sm-b</button>
				<button type="button" class="btn grain-md-b">grain-md-b</button>
				<button type="button" class="btn grain-lg-b">grain-lg-b</button>
				<button type="button" class="btn grain-xl-b">grain-xl-b</button>
				<button type="button" class="btn noise-b">noise-b</button>
			</p>

			<h3>Texture (::after) vs Distress (::after, alternative)</h3>
			<p>
				<button type="button" class="btn texture-b">texture-b</button>
				<button type="button" class="btn distress-b">distress-b</button>
			</p>

			<h3>Fade (mask-image) - sized</h3>
			<p>
				<button type="button" class="btn btn--secondary fade-a">fade-a</button>
				<button type="button" class="btn btn--secondary fade-md-b">fade-md-b</button>
				<button type="button" class="btn btn--secondary fade-lg-b">fade-lg-b</button>
				<button type="button" class="btn btn--secondary fade-xl-c">fade-xl-c (vignette)</button>
			</p>

			<h3>Gradient (background-image) - sized</h3>
			<p>
				<button type="button" class="btn gradient-a">gradient-a</button>
				<button type="button" class="btn gradient-md-a">gradient-md-a</button>
				<button type="button" class="btn gradient-lg-a">gradient-lg-a</button>
				<button type="button" class="btn gradient-xl-a">gradient-xl-a</button>
			</p>

			<h3>Shadow / Border - finishing touches</h3>
			<p>
				<button type="button" class="btn shadow-a">shadow-a</button>
				<button type="button" class="btn shadow-b">shadow-b</button>
				<button type="button" class="btn shadow-c">shadow-c</button>
				<button type="button" class="btn border-a">border-a</button>
				<button type="button" class="btn border-b">border-b</button>
				<button type="button" class="btn border-c">border-c</button>
			</p>

			<h3>Border-line - concentric double/triple-rule frame</h3>
			<p>A certificate/stamp-style nested border, not a plain <code>border-a/-b/-c</code> single rule - the element's own border plus one <code>::before</code> (and, for <code>-c</code>, one <code>::after</code> too) inset a few pixels in as extra inner lines. <code>-c</code> uses both pseudo-element slots, so it can't combine with <code>grain-*/noise-*/texture-*/distress-*</code> - only <code>-a/-b</code> (::before only) can still pair with one of those on the free <code>::after</code> slot. It CAN pair with Group A (cut-*/edge-*/roughness-*) too - those change clip-path/mask-image on the element's own box, a different property from the border/::before/::after this family uses, so nothing collides; the outline just reads straighter than cut-*/roughness-* alone since border-line's own border-radius comes later in this file and wins over cut-*'s border-radius:0.</p>
			<p>
				<button type="button" class="btn border-line-a">border-line-a</button>
				<button type="button" class="btn border-line-b">border-line-b</button>
				<button type="button" class="btn border-line-c">border-line-c</button>
				<button type="button" class="btn border-line-a cut-a">border-line-a + cut-a</button>
				<button type="button" class="btn btn--secondary border-line-b roughness-a">border-line-b + roughness-a</button>
			</p>

			<h3>Combinations - multiple families on one element</h3>
			<p>
				<button type="button" class="btn cut-lg-b grain-md-b shadow-c">cut-lg-b + grain-md-b + shadow-c</button>
				<button type="button" class="btn btn--secondary roughness-lg-a shadow-b">roughness-lg-a + shadow-b</button>
				<button type="button" class="btn gradient-xl-c edge-md-a">gradient-xl-c + edge-md-a</button>
				<button type="button" class="btn btn--danger texture-c border-a">texture-c + border-a</button>
				<button type="button" class="btn noise-c cut-a">noise-c + cut-a</button>
				<button type="button" class="btn border-line-a texture-b">border-line-a + texture-b</button>
				<button type="button" class="btn btn--secondary border-line-b gradient-md-a">border-line-b + gradient-md-a</button>
				<button type="button" class="btn border-line-c shadow-b">border-line-c + shadow-b</button>
			</p>

			<h3>On cards, badges, and a plain section - not button-specific</h3>
			<div class="grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:var(--space-sm);">
				<div class="card roughness-a">
					<div class="card__body">
						<h3 class="card__title">Card + roughness-a</h3>
						<p>Same class, applied to a card instead of a button.</p>
					</div>
				</div>
				<div class="card cut-c">
					<div class="card__body">
						<h3 class="card__title">Card + cut-c</h3>
						<p>All four corners clipped.</p>
					</div>
				</div>
				<div class="card grain-lg-b texture-b">
					<div class="card__body">
						<h3 class="card__title">Card + grain-lg-b + texture-b</h3>
						<p>::before and ::after both in use at once.</p>
					</div>
				</div>
				<div class="card cut-lg-b shadow-c">
					<div class="card__body">
						<h3 class="card__title">Card + cut-lg-b + shadow-c</h3>
						<p>Shape family + a finishing-touch family.</p>
					</div>
				</div>
				<div class="card gradient-md-c edge-a">
					<div class="card__body">
						<h3 class="card__title">Card + gradient-md-c + edge-a</h3>
						<p>Background wash + soft organic corners.</p>
					</div>
				</div>
				<div class="card roughness-lg-a border-b">
					<div class="card__body">
						<h3 class="card__title">Card + roughness-lg-a + border-b</h3>
						<p>Torn edge, then a thick accent border on top.</p>
					</div>
				</div>
				<div class="card noise-b cut-c">
					<div class="card__body">
						<h3 class="card__title">Card + noise-b + cut-c</h3>
						<p>Scratchy overlay + all four corners clipped.</p>
					</div>
				</div>
				<div class="card distress-c fade-lg-b">
					<div class="card__body">
						<h3 class="card__title">Card + distress-c + fade-lg-b</h3>
						<p>::after worn patches + a masked fade band.</p>
					</div>
				</div>
				<div class="card border-line-b">
					<div class="card__body">
						<h3 class="card__title">Card + border-line-b</h3>
						<p>The nested-frame look reads better at card size than on a small button.</p>
					</div>
				</div>
				<div class="card border-line-c shadow-c">
					<div class="card__body">
						<h3 class="card__title">Card + border-line-c + shadow-c</h3>
						<p>Triple rule + a drop shadow lifting it off the page.</p>
					</div>
				</div>
				<div class="card border-line-b cut-b">
					<div class="card__body">
						<h3 class="card__title">Card + border-line-b + cut-b</h3>
						<p>Nested frame + clipped corners - Group A and this family don't collide.</p>
					</div>
				</div>
			</div>
			<p>
				<span class="badge badge--primary cut-b">Label + cut-b</span>
				<span class="badge edge-a">Label + edge-a</span>
				<span class="badge gradient-b">Label + gradient-b</span>
			</p>
			<div class="frame distress-a" style="padding:var(--space-sm);">Plain section (.frame) + distress-a</div>

		</section>

		<section class="section section--sm">
			<h2>Marquee</h2>
			<p>A real component now (<code>components/marquee/marquee.php</code> + <code>assets/css/components/marquee.css</code>), not a test-page hack - pure CSS scroll animation, pauses on hover, respects <code>prefers-reduced-motion</code>. <code>.marquee--a/-b/-c/-d</code> are its own combination presets (see that CSS file's comment), every one of them carrying some roughness now, just in different amounts. <code>id</code>/<code>class</code> args attach a unique id and/or extra classes (e.g. a shape.css combination) without needing a matching marquee.css preset for every possibility - shown on the last one below.</p>
			<?php
			View::component( 'marquee/marquee', array( 'items' => array( 'Freshly Pressed', 'Naturally Refreshing', 'Always Made With Care' ), 'variant' => 'a' ) );
			View::component( 'marquee/marquee', array( 'items' => array( 'Gradient + Texture Together' ), 'variant' => 'b' ) );
			View::component( 'marquee/marquee', array( 'items' => array( 'Clipped Corners + Edge Fade' ), 'variant' => 'c' ) );
			View::component( 'marquee/marquee', array( 'items' => array( 'Heavier Torn Edge + Distress' ), 'variant' => 'd' ) );
			View::component( 'marquee/marquee', array( 'items' => array( 'Plain + id="el-marquee-custom" + shape.css shadow-c' ), 'id' => 'el-marquee-custom', 'class' => 'shadow-c' ) );
			?>
		</section>

		<section class="section section--sm">
			<h2>Banner</h2>
			<p>From <code>components/banner/banner.php</code> + <code>assets/css/components/banner.css</code> - a full-width promo/CTA band (tag + title + sub + image + buttons), matching <code>data/content/cta.json</code>'s shape (also the reference design's "Direct Enquiry"/closing-CTA and "Make It A Combo!" bands). Not a page hero - a reusable band any page's content can drop in. <code>title</code> accepts safe HTML for inline emphasis (<code>&lt;em&gt;</code>); <code>variant: 'reverse'</code> flips the image to the left. The panel carries <code>tex-paper-aged-a</code> (surface) on <code>.banner</code> and <code>tex-ink-brush-a</code> (underline accent) on <code>.banner__content</code>. <code>tex-border-vintage-a</code> was here too but got dropped after actually rendering it on this wide a panel - see <code>textures.css</code>'s own note on why that mask only holds up on roughly-square elements.</p>
			<?php
			View::component(
				'banner/banner',
				array(
					'tag'     => 'Taste It Yourself',
					'title'   => 'Something New, <em>Made Today</em>',
					'sub'     => 'Get in touch, or find us in person.',
					'image'   => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=800&q=72&auto=format&fit=crop',
					'buttons' => array(
						array( 'label' => 'Get In Touch', 'route' => 'contact' ),
						array( 'label' => 'See More', 'route' => 'game', 'style' => 'ghost' ),
					),
				)
			);
			?>
			<div style="margin-top: var(--space-md);">
				<?php
				View::component(
					'banner/banner',
					array(
						'tag'     => 'Our Range',
						'title'   => 'Fresh, <em>Made To Order</em>',
						'sub'     => 'variant: reverse - image on the left instead.',
						'image'   => 'https://images.unsplash.com/photo-1546173159-315724a31696?w=800&q=72&auto=format&fit=crop',
						'variant' => 'reverse',
						'buttons' => array( array( 'label' => 'View Full Range', 'route' => 'game' ) ),
					)
				);
				?>
			</div>
			<div style="margin-top: var(--space-md);">
				<p><code>items</code> (a checklist between sub and buttons - <code>data/content/franchise-teaser.json</code>'s icon+title shape, or a plain string array like <code>data/content/hero.json</code>'s "checks"):</p>
				<?php
				View::component(
					'banner/banner',
					array(
						'tag'     => 'Opportunities',
						'title'   => 'Own A <em>Cane House</em> Franchise',
						'sub'     => 'Join a growing network of franchise partners. We\'ll walk you through every step.',
						'image'   => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800&q=72&auto=format&fit=crop',
						'items'   => array(
							array( 'icon' => '💼', 'label' => 'Full training & ongoing support' ),
							array( 'icon' => '📍', 'label' => 'Exclusive territory rights' ),
							array( 'icon' => '🚀', 'label' => 'Launch-ready in weeks, not months' ),
						),
						'buttons' => array( array( 'label' => 'Enquire Now', 'route' => 'contact' ) ),
					)
				);
				?>
			</div>
			<div style="margin-top: var(--space-md);">
				<p>The reference design's Franchise-page hero (text left, machine photo right, a corner stamp) - real local asset (<code>assets/images/backgrounds/machine/old_machine.png</code>), not a placeholder, so referenced through <code>VINTAGESOUL_URI</code> like every other theme-relative asset in this codebase rather than a raw path. The <code>stamp</code> arg renders and positions the corner stamp for you - no wrapper markup needed at the call site.</p>
				<?php
				View::component(
					'banner/banner',
					array(
						'id'      => 'el-machine',
						'tag'     => 'A Growing Opportunity',
						'title'   => 'Bring The Tradition <em>To Your City</em>',
						'sub'     => 'A timeless drink. A growing opportunity.',
						'image'   => VINTAGESOUL_URI . '/assets/images/backgrounds/machine/old_machine.png',
						'stamp'   => array(
							'top'    => 'Family Business',
							'center' => 'Proven Model',
							'bottom' => 'Full Support',
							'size'   => 110,
						),
						'buttons' => array(
							array( 'label' => 'Start Your Enquiry', 'route' => 'contact' ),
							array( 'label' => 'See How It Works', 'route' => 'about', 'style' => 'ghost' ),
						),
					)
				);
				?>
			</div>
		</section>

		<section class="section section--sm">
			<h2>Section header</h2>
			<p>From <code>components/section-header/section-header.php</code> + <code>assets/css/components/section-header.css</code> - the "— TAG —" eyebrow + title lead-in repeated across this theme's section intros, pulled out once instead of each section duplicating it. <code>align: 'left'</code> drops the centering and the left-hand rule; <code>variant: 'dark'</code> swaps text colors for a dark photo/wood background instead of parchment.</p>
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => 'Happy Customers',
					'title' => 'Hear From <em>Our Guests</em>',
					'sub'   => 'Short clips from real events - weddings, parties, and celebrations across the UK.',
				)
			);
			?>
			<div class="frame" style="padding:var(--space-lg); background:var(--color-primary);">
				<?php
				View::component(
					'section-header/section-header',
					array(
						'tag'     => 'Dark Variant',
						'title'   => 'Section Header <em>Dark</em>',
						'sub'     => 'Used on a dark photo or wood background instead of parchment.',
						'variant' => 'dark',
					)
				);
				?>
			</div>
		</section>

		<section class="section section--sm">
			<h2>Process steps</h2>
			<p>From <code>components/process-steps/process-steps.php</code> + <code>assets/css/components/process-steps.css</code> - a numbered step-by-step timeline (<code>data/content/process-steps.json</code>'s shape). Pairs with the section-header above for the lead-in; this component only renders the numbered steps. Carries <code>tex-ground-soil-a</code> - a soil/grass ground-line strip along the bottom (see the Textures section above).</p>
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => 'From Start To Finish',
					'title' => 'Our <em>Process</em>',
				)
			);
			View::component(
				'process-steps/process-steps',
				array(
					'items' => array(
						array( 'title' => 'Sourcing', 'desc' => 'Working with partners who share our standards for quality and fair pay.', 'image' => 'https://images.unsplash.com/photo-1560493676-04071c5f467b?w=800&q=72&auto=format&fit=crop' ),
						array( 'title' => 'Selection', 'desc' => 'Chosen by hand at the right moment, when quality is at its best.', 'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=800&q=72&auto=format&fit=crop' ),
						array( 'title' => 'Preparation', 'desc' => 'Prepared within hours so nothing of the original character is lost to time.', 'image' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=800&q=72&auto=format&fit=crop' ),
						array( 'title' => 'Delivery', 'desc' => 'Delivered to your door or handed over fresh at the counter in front of you.', 'image' => 'https://images.unsplash.com/photo-1543269865-cbf427effbad?w=800&q=72&auto=format&fit=crop' ),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Step chain</h2>
			<p>From <code>components/step-chain/step-chain.php</code> + <code>assets/css/components/step-chain.css</code> - a flowing left-to-right sequence of small circles connected by arrows (the reference design's "Life Cycle Of Sugarcane" sequence - real History page usage). Distinct from Process steps above (a card grid, big photos, no connecting arrow) - this is ONE sequence. Distinct from Photo grid below (an unordered grid of even-weight photo tiles, no flow/sequence implied) - use step-chain when order matters, photo grid when it doesn't. Arrows drop below 640px width rather than trying to land correctly on a wrap boundary - see this file's own comment for why that specific trick isn't reliable in pure CSS.</p>
			<p>With photos + numbers + a description (Life Cycle):</p>
			<?php
			View::component(
				'step-chain/step-chain',
				array(
					'items' => array(
						array( 'number' => '1', 'label' => 'Planting', 'desc' => 'Healthy setts planted in rich soil.', 'image' => 'https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?w=200&q=70&auto=format&fit=crop' ),
						array( 'number' => '2', 'label' => 'Growth', 'desc' => 'Grows strong under sun, water & care.', 'image' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=200&q=70&auto=format&fit=crop' ),
						array( 'number' => '3', 'label' => 'Maturation', 'desc' => 'Over 10-14 months, reaches full maturity.', 'image' => 'https://images.unsplash.com/photo-1500651230702-0e2d8a49d4ad?w=200&q=70&auto=format&fit=crop' ),
						array( 'number' => '4', 'label' => 'Harvesting', 'desc' => 'Hand-cut with care at the perfect time.', 'image' => 'https://images.unsplash.com/photo-1592982537447-7440770cbfc9?w=200&q=70&auto=format&fit=crop' ),
						array( 'number' => '5', 'label' => 'Transport', 'desc' => 'Carried fresh to the mill.', 'image' => 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?w=200&q=70&auto=format&fit=crop' ),
						array( 'number' => '6', 'label' => 'Extraction', 'desc' => 'Juice pressed fresh from the cane.', 'image' => 'https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=200&q=70&auto=format&fit=crop' ),
						array( 'number' => '7', 'label' => 'Our Juice', 'desc' => 'Pure, fresh, naturally delicious.', 'image' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=200&q=70&auto=format&fit=crop' ),
					),
				)
			);
			?>
			<p style="margin-top: var(--space-lg);">Icons only, no numbers/description:</p>
			<?php
			View::component(
				'step-chain/step-chain',
				array(
					'items' => array(
						array( 'icon' => '💧', 'label' => 'Washed' ),
						array( 'icon' => '⚙️', 'label' => 'Crushed' ),
						array( 'icon' => '🫙', 'label' => 'Pressed' ),
						array( 'icon' => '🧪', 'label' => 'Filtered' ),
						array( 'icon' => '❄️', 'label' => 'Clarified' ),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Photo grid</h2>
			<p>From <code>components/photo-grid/photo-grid.php</code> + <code>assets/css/components/photo-grid.css</code> - a plain 2-up (wider screens: <code>auto-fill</code>) grid of photo tiles, each with an always-visible label underneath. The reference design's "From Cane To Many Goodness" section - real History page usage. Distinct from Gallery above (caption only reveals on hover, built for a photography/lightbox feel) - here the label is part of the tile, always readable, closer to a product/process catalogue than a photo browser.</p>
			<?php
			View::component(
				'photo-grid/photo-grid',
				array(
					'items' => array(
						array( 'image' => 'https://images.unsplash.com/photo-1578338244251-6258f907691b?w=400&q=72&auto=format&fit=crop', 'label' => 'Washed' ),
						array( 'image' => 'https://images.unsplash.com/photo-1764426381444-697bf787844a?w=400&q=72&auto=format&fit=crop', 'label' => 'Crushed' ),
						array( 'image' => 'https://images.unsplash.com/photo-1775590080706-7ed46df539ba?w=400&q=72&auto=format&fit=crop', 'label' => 'Pressed' ),
						array( 'image' => 'https://images.unsplash.com/photo-1758522965167-42e34a12bf13?w=400&q=72&auto=format&fit=crop', 'label' => 'Filtered' ),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Feature row</h2>
			<p>From <code>components/feature-row/feature-row.php</code> + <code>assets/css/components/feature-row.css</code> - a horizontal row of icon+title+description items divided by thin rules, fitting <code>data/content/feature-badges.json</code>'s icon/label/note shape directly. Distinct from Feature cards above (separate bordered tiles in a grid) - this is ONE row. <code>variant: 'boxed'</code> adds the outer panel + numbered corner tab (<code>number</code>/<code>heading</code> args); the plain default has neither.</p>
			<p>Plain (<code>variant</code> omitted):</p>
			<?php
			View::component(
				'feature-row/feature-row',
				array(
					'items' => array(
						array( 'icon' => 'leaf', 'label' => 'Premium Cane', 'note' => 'Rich in juice, naturally potent.' ),
						array( 'icon' => 'flame', 'label' => 'Traditional Pressing', 'note' => 'Cold pressed with care & precision.' ),
					),
				)
			);
			?>
			<p style="margin-top: var(--space-lg);"><code>variant: 'boxed'</code> - icons here are real line-icons (known-name allowlist, see this component's own docblock), not emoji like the plain example above; both are valid, this just shows the difference side by side:</p>
			<?php
			View::component(
				'feature-row/feature-row',
				array(
					'variant' => 'boxed',
					'number'  => '01',
					'heading' => 'Order Information',
					'items'   => array(
						array( 'icon' => 'wheat', 'label' => 'Freshly Pressed', 'note' => 'We press fresh for every order.' ),
						array( 'icon' => 'building', 'label' => 'Event Orders', 'note' => 'Book us for your events, parties & celebrations.' ),
						array( 'icon' => 'scooter', 'label' => 'Home Delivery', 'note' => 'Enjoy fresh juice at your doorstep.' ),
						array( 'icon' => 'bottles', 'label' => 'Bulk Orders', 'note' => 'Ideal for offices, events & businesses.' ),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Gallery</h2>
			<p>From <code>components/gallery/gallery.php</code> + <code>assets/css/components/gallery.css</code> + <code>assets/js/components/gallery.js</code> - a filterable photo grid with category tabs (<code>data/content/gallery.json</code>'s shape, which already modeled <code>categories</code> + a per-image <code>category</code> before any component consumed it). Distinct from the Memories section below - that one is a simpler, unfiltered captioned grid using the torn-photo <code>memory-card-*</code> presets; this one is a flat grid with a click-to-filter tab bar. The first tab ("All") shows everything; click another to filter. Each photo now carries the "documentary photograph" texture recipe: <code>tex-film-grain-a</code> + <code>tex-dust-a</code> (see the Textures section above).</p>
			<?php
			View::component(
				'gallery/gallery',
				array(
					'categories' => array( 'All', 'On Location', 'Behind The Scenes', 'Events' ),
					'items'      => array(
						array( 'src' => 'https://images.unsplash.com/photo-1560493676-04071c5f467b?auto=format&fit=crop&w=400&q=80', 'category' => 'On Location', 'label' => 'Early Morning', 'desc' => 'The site at sunrise' ),
						array( 'src' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?auto=format&fit=crop&w=400&q=80', 'category' => 'Behind The Scenes', 'label' => 'In Progress', 'desc' => 'Work happening in real time' ),
						array( 'src' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=400&q=80', 'category' => 'Events', 'label' => 'Summer Wedding', 'desc' => 'Serving at a summer wedding' ),
						array( 'src' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=400&q=80', 'category' => 'On Location', 'label' => 'Natural Materials', 'desc' => 'Only the freshest to start with' ),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Hero carousel</h2>
			<p>From <code>components/hero-carousel/hero-carousel.php</code> + <code>assets/css/components/hero-carousel.css</code> + <code>assets/js/components/hero-carousel.js</code> - a full-bleed, multi-slide hero band (prev/next arrows + dots), matching the reference design's "Our Journey"/"From Our Farm To Your Glass" pattern. Each slide is the same shape <code>data/content/hero.json</code>'s single hero already uses, plus an optional <code>video</code>. No autoplay unless the caller passes <code>autoplay: true</code> (this demo does) - see this component's own docblock on why it's opt-in. With it on: an image slide holds 4s with a slow Ken Burns zoom timed to match, then advances; the video slide (slide 2 below) plays to its own end and advances on THAT instead of a fixed timer - watch it through, the slide won't get pulled out from under you mid-clip. Hover/focus the carousel, or use the pause button (bottom-right, required for WCAG 2.2.2), to stop it. Arrow/dot/pause-button shape is the shared <code>.carousel-arrow</code>/<code>.carousel-dots</code>/<code>.carousel-toggle</code> base (<code>assets/css/components/carousel-controls.css</code>) - also used by Video testimonial carousel below - only the colors are each component's own. All slides stack in the same CSS grid cell so the container auto-sizes to the tallest one, no JS height-measuring needed. Each slide's own subtitle/title/desc/actions cascade in on a stagger whenever that slide becomes active (<code>.is-active</code>-triggered <code>animation</code>, not a one-shot page-load effect - it replays every time you arrive at a slide, including via the dots/arrows) - the same cinematic-entrance idea the static Hero above uses, just re-triggerable instead of once.</p>
			<?php
			View::component(
				'hero-carousel/hero-carousel',
				array(
					'autoplay' => true,
					'slides'   => array(
						array(
							'subtitle'    => 'The Long Way Round',
							'title'       => 'Our Journey',
							'description' => 'Field, press, counter, glass.',
							'image'       => 'https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=1200&q=72&auto=format&fit=crop',
							'buttons'     => array( array( 'label' => 'Our Story', 'route' => 'about' ) ),
						),
						array(
							'subtitle'    => 'From Our Farm',
							'title'       => 'To Your Glass',
							'description' => 'Video slide - plays to its own end, no fixed timer.',
							'image'       => 'https://images.unsplash.com/photo-1560493676-04071c5f467b?w=1200&q=72&auto=format&fit=crop',
							'video'       => 'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4',
							'buttons'     => array( array( 'label' => 'Watch More', 'route' => 'game', 'style' => 'ghost' ) ),
						),
						array(
							'subtitle'    => 'Watch It, Taste It',
							'title'       => 'Love It',
							'description' => 'More than a drink - it\'s a memory.',
							'image'       => 'https://images.unsplash.com/photo-1546173159-315724a31696?w=1200&q=72&auto=format&fit=crop',
							'buttons'     => array( array( 'label' => 'Visit Us', 'route' => 'contact' ) ),
						),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Stamp</h2>
			<p>From <code>components/stamp/stamp.php</code> + <code>assets/css/components/stamp.css</code> - a rubber-stamp graphic (worn concentric rings + a gritty <code>feTurbulence</code> filter, matching <code>traditional_template/assets/images/stamp.svg</code>'s own technique) but with the text as real args instead of one message baked into an SVG file - pass <code>top</code>/<code>center</code>/<code>bottom</code> and get a stamp with those words. Renders inline SVG (curved <code>&lt;textPath&gt;</code> text needs the browser's real text engine, not a mask+background-color icon), colour from <code>currentColor</code>. <code>id</code> only matters if more than one stamp renders on one page - each instance's curved-text arcs need a page-unique id.</p>
			<div style="display: flex; flex-wrap: wrap; gap: var(--space-lg); align-items: center;">
				<?php
				View::component(
					'stamp/stamp',
					array(
						'id'     => 'el-stamp-a',
						'top'    => 'Family Business',
						'center' => 'Proven Model',
						'bottom' => 'Full Support',
					)
				);
				View::component(
					'stamp/stamp',
					array(
						'id'     => 'el-stamp-b',
						'top'    => 'Freshly Pressed',
						'center' => '100% Natural',
						'bottom' => 'Sugarcane Juice',
						'size'   => 130,
					)
				);
				View::component(
					'stamp/stamp',
					array(
						'id'     => 'el-stamp-c',
						'center' => 'Est. 2024',
						'size'   => 110,
					)
				);
				?>
			</div>
		</section>

		<section class="section section--sm">
			<h2>Photo stamp</h2>
			<p>From <code>components/photo-stamp</code> - a standalone accent photo with a <code>stamp/stamp</code> badge overlapping its top edge, self-positioned (no wrapper markup needed at the call site, unlike <code>banner</code>'s <code>stamp</code> arg which anchors to the banner's own media/content seam). For a small decorative photo that isn't part of a text+image banner pair - the History page's "Advaith's Gift" accent between its hero and intro text uses this.</p>
			<?php
			View::component(
				'photo-stamp/photo-stamp',
				array(
					'id'    => 'el-photo-stamp',
					'image' => 'https://images.unsplash.com/photo-1580123099720-9a6dd0d0c4e8?w=500&q=72&auto=format&fit=crop',
					'stamp' => array(
						'top'    => "Advaith's Gift",
						'center' => 'Ancient Craft',
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Video testimonial carousel</h2>
			<p>From <code>components/video-testimonial-carousel</code> (a scroll-snap row, no JS needed for touch/trackpad swipe) wrapping <code>components/video-testimonial-card</code> tiles - the reference design's "reels" testimonial section. The play button does NOT embed a video player - see that component's own docblock on why - it dispatches a <code>video-testimonial:play</code> event a real project listens for to open its own player/lightbox (e.g. <code>components/dialog</code>'s dynamic <code>create()</code> with an <code>&lt;iframe&gt;</code>). Try the arrows, or swipe/scroll the row directly - their circular shape is the same shared <code>.carousel-arrow</code> base Hero carousel above uses, just this component's own solid-surface color instead of translucent-on-photo. Hover a thumbnail for the shared site-wide <code>hover-zoom</code> (<code>shape.css</code>).</p>
			<?php
			View::component(
				'video-testimonial-carousel/video-testimonial-carousel',
				array(
					'items' => array(
						array(
							'name'      => 'Priya',
							'role'      => 'Wedding Host',
							'title'     => 'Best Drink at Our Mehndi Night!',
							'desc'      => 'Guests couldn\'t stop talking about the fresh-pressed cane juice.',
							'thumbnail' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=500&q=72&auto=format&fit=crop',
						),
						array(
							'name'      => 'Aman',
							'role'      => 'Corporate Event',
							'title'     => 'Our Office Loved the Live Stall',
							'desc'      => 'The live pressing was the highlight of our summer party.',
							'thumbnail' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=500&q=72&auto=format&fit=crop',
						),
						array(
							'name'      => 'Sara',
							'role'      => 'Birthday Party',
							'title'     => 'A Refreshing Surprise for Kids',
							'desc'      => 'Healthier than fizzy drinks and the children adored it.',
							'thumbnail' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=500&q=72&auto=format&fit=crop',
						),
						array(
							'name'      => 'Devika',
							'role'      => 'Festival Stall',
							'title'     => 'Tradition in Every Cup',
							'desc'      => 'Brought a taste of home to our festival crowd.',
							'thumbnail' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=500&q=72&auto=format&fit=crop',
						),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Stats</h2>
			<p>From <code>components/stats/stats.php</code> + <code>assets/css/components/stats.css</code> - a bare number/label strip (<code>data/content/stats.json</code>'s shape), deliberately not a <code>.card</code> grid. Large values are compacted via <code>Formatter::compact_number()</code> (e.g. <code>50000000</code> renders as "50M+") - callers pass the raw number, this component owns the display rule. Carries <code>tex-ground-cane-a</code> - a sugarcane/grass ground-line strip along the bottom (see the Textures section above); <code>.stats.tex-ground-cane-a</code> in stats.css adds the padding room it needs, since <code>.stats</code> is otherwise deliberately unpadded.</p>
			<?php
			View::component(
				'stats/stats',
				array(
					'items' => array(
						array( 'label' => 'Partners', 'value' => 500 ),
						array( 'label' => 'Customers Served', 'value' => 50000000 ),
						array( 'label' => 'Locations', 'value' => 200 ),
						array( 'label' => 'Years', 'value' => 39 ),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Product / Certificate / Feature cards</h2>
			<p>All three build on the generic <code>.card</code> from the Shape treatments section above (<code>assets/css/components/card.css</code>) rather than parallel components - each just adds the bits <code>.card</code> doesn't already have. <code>components/product-card</code> (image+name+price+CTA, <code>data/content/products.json</code>), <code>components/certificate-card</code> (icon+title+desc+badge, <code>data/content/certifications.json</code>), <code>components/feature-card</code> (icon+title+text+stat, <code>data/content/benefits.json</code>/<code>feature-badges.json</code>/<code>health-benefits.json</code> all fit this same shape). Icons are emoji, same as the content JSON already models them - no per-icon SVG asset needed. <code>certificate-card</code> now uses the shared <code>.card--horizontal</code> row layout (image/icon left, text right) instead of a centered layout - see the Certificate carousel section below for it with a real photo + "view" button, and <code>.certificate-card</code>'s own note in card.css for the hand-cut dashed seam replacing a plain border-right.</p>
			<div class="grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:var(--space-sm);">
				<?php
				View::component(
					'product-card/product-card',
					array(
						'name'   => 'Classic',
						'desc'   => 'Pure and simple - the original.',
						'price'  => '£3.00',
						'image'  => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?auto=format&fit=crop&w=600&q=80',
						'button' => array( 'label' => 'Order Now', 'route' => 'game' ),
					)
				);
				View::component(
					'certificate-card/certificate-card',
					array(
						'icon'  => '🏆',
						'title' => 'Top Quality',
						'desc'  => 'Awarded for excellence.',
						'badge' => '2026',
					)
				);
				View::component(
					'feature-card/feature-card',
					array(
						'icon'  => '🌿',
						'title' => 'Roots Of Tradition',
						'text'  => 'Made the way it always has been, preserving something honest from a simpler time.',
						'stat'  => 'Generations old',
					)
				);
				?>
			</div>
		</section>

		<section class="section section--sm">
			<h2>Certificate carousel</h2>
			<p>From <code>components/certificate-carousel</code> (a scroll-snap row, same technique as Video testimonial carousel below - no JS needed for touch/trackpad swipe) wrapping <code>components/certificate-card</code> tiles - the reference design's "Quality / Certifications" section. Arrow shape is the shared <code>.carousel-arrow</code> base (<code>assets/css/components/carousel-controls.css</code>); unlike Video testimonial carousel, these stay visible on narrow screens instead of hiding - the reference shows them there too, since only one card is visible at a time on mobile.</p>
			<?php
			View::component(
				'certificate-carousel/certificate-carousel',
				array(
					'items' => array(
						array(
							'image'  => 'https://images.unsplash.com/photo-1589330273594-fade1ee91647?w=300&q=70&auto=format&fit=crop',
							'title'  => 'NIFT',
							'desc'   => 'National Institute of Traditional Foods - certified for quality and safety.',
							'badge'  => 'NIFT',
							'button' => array( 'label' => 'View Certificate', 'route' => 'about' ),
						),
						array(
							'icon'   => '🛡️',
							'title'  => 'ISO 22000:2018',
							'desc'   => 'Food Safety Management System - certified for safe food practices.',
							'badge'  => 'ISO',
							'button' => array( 'label' => 'View Certificate', 'route' => 'about' ),
						),
						array(
							'icon'   => '🌱',
							'title'  => 'Organic Source',
							'desc'   => 'Organic sugarcane, carefully grown without chemicals or pesticides.',
							'button' => array( 'label' => 'View Certificate', 'route' => 'about' ),
						),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Product list</h2>
			<p>From <code>components/product-list/product-list.php</code> + <code>assets/css/components/product-list.css</code> - the reference design's menu-style list (a pill tag, then rows divided by a hand-cut dashed seam, ALL inside one shared bordered panel). Distinct from Product/Certificate/Feature cards above - those are separate bordered tiles in a grid with no shared panel or tag; this is one panel, one border, rows instead of tiles. Row layout reuses <code>.card--horizontal</code> (card.css) without <code>.card</code> itself, since the border belongs to the whole list here, not each row.</p>
			<?php
			View::component(
				'product-list/product-list',
				array(
					'tag'   => 'Our Juices',
					'items' => array(
						array(
							'image'  => 'https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=200&q=70&auto=format&fit=crop',
							'name'   => 'Classic Cane Juice',
							'desc'   => 'Pure & Natural',
							'price'  => '₹50.00',
							'button' => array( 'label' => 'Order Now', 'route' => 'game' ),
						),
						array(
							'image'  => 'https://images.unsplash.com/photo-1560493676-04071c5f467b?w=200&q=70&auto=format&fit=crop',
							'name'   => 'Pineapple Cane Juice',
							'desc'   => 'With a twist of pineapple',
							'price'  => '₹60.00',
							'button' => array( 'label' => 'Order Now', 'route' => 'game' ),
						),
						array(
							'image'  => 'https://images.unsplash.com/photo-1546173159-315724a31696?w=200&q=70&auto=format&fit=crop',
							'name'   => 'Lemon Cane Juice',
							'desc'   => 'Zesty and healthy',
							'price'  => '₹60.00',
							'button' => array( 'label' => 'Order Now', 'route' => 'game' ),
						),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Testimonial cards</h2>
			<p>From <code>components/testimonial-card/testimonial-card.php</code> + <code>assets/css/components/testimonial-card.css</code> - vintage by default (cream paper, a serif italic pull-quote, a gold decorative quotation mark) rather than a plain <code>.card</code>, since a review reads better as a handwritten note than a UI-kit tile. Renders the exact shape <code>VintageSoul\Services\TestimonialService::featured()</code> returns; fixed a real bug found while wiring this up - <code>JsonTestimonialRepository</code> was reading <code>role</code>/<code>quote</code> keys that don't exist in <code>data/content/testimonials.json</code> (its keys are <code>location</code>/<code>text</code>), so every testimonial silently rendered blank before this. Pairs well with a shape.css class for an even more torn/framed look - shown on the second one below.</p>
			<div class="grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:var(--space-sm);">
				<?php
				View::component(
					'testimonial-card/testimonial-card',
					array(
						'name'   => 'Ramesh P.',
						'role'   => 'Bengaluru',
						'rating' => 5,
						'quote'  => 'The most refreshing experience I\'ve had in a long time. Totally natural and full of care.',
					)
				);
				?>
				<div class="border-line-a">
					<?php
					View::component(
						'testimonial-card/testimonial-card',
						array(
							'name'   => 'Sarah M.',
							'role'   => 'Ely, UK',
							'rating' => 5,
							'quote'  => 'Booked for our wedding - it was the highlight of the day. Fresh, fun and absolutely wonderful.',
						)
					);
					?>
				</div>
			</div>
		</section>

		<section class="section section--sm">
			<h2>FAQ</h2>
			<p>From <code>components/faq/faq.php</code> + <code>assets/css/components/faq.css</code> - each question toggles independently (not a one-at-a-time accordion like the mobile drawer's submenu, see that file's own docblock for why). Every answer is in the initial markup, collapsed by CSS alone (<code>grid-template-rows: 0fr</code>) - nothing is hidden if JS fails. Real content lives in <code>data/content/faqs.json</code>; this is placeholder data for display only, same rule as everything else on this page. The panel now carries <code>tex-leaf-fall-a</code> (see the Textures section above) as a scattered-leaf background wash.</p>
			<?php
			View::component(
				'faq/faq',
				array(
					'heading' => 'Frequently Asked Questions',
					'items'   => array(
						array( 'question' => 'Is everything made fresh?', 'answer' => 'Yes - everything is prepared to order. Nothing is pre-made or stored.' ),
						array( 'question' => 'Do you use any additives?', 'answer' => 'Never. Everything is 100% natural with no added sugar, colours, or preservatives.' ),
						array( 'question' => 'Can I book you for a private event?', 'answer' => 'Absolutely - use the enquiry form to get a quote.' ),
						array( 'question' => 'Do you deliver?', 'answer' => 'Yes, place an order online and choose a slot.' ),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Memory cards</h2>
			<p>From <code>assets/css/memory-card.css</code> - five standalone "photo taped to the page" presets (rotation + a torn/cut edge + a shadow + a taped-on sticker). Each is its own recipe, not a base + modifiers - use whichever one fits: <code>.memory-card-a</code> through <code>.memory-card-e</code>. See that file's own comment for exactly what each combines.</p>
			<div class="grid" style="display:flex; flex-wrap:wrap; gap:var(--space-lg); align-items:flex-start;">
				<?php
				$el_memory_colors = array(
					'a' => '709060',
					'b' => 'a06848',
					'c' => '507068',
					'd' => '886040',
					'e' => '608850',
				);
				foreach ( $el_memory_colors as $el_variant => $el_color ) {
					$el_placeholder = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='300' height='375'><rect width='100%25' height='100%25' fill='%23{$el_color}'/></svg>";
					echo '<figure class="memory-card-' . esc_attr( $el_variant ) . '" style="width:160px;"><img src="' . esc_attr( $el_placeholder ) . '" alt=""></figure>';
				}
				?>
			</div>
		</section>

		<section class="section section--sm">
			<h2>Memories</h2>
			<p>From <code>components/memories/memories.php</code> - the plain, unfiltered captioned photo grid (<code>data/content/memories.json</code>'s shape; a future Gallery component would consume <code>data/content/gallery.json</code> instead, which additionally supports category filter tabs - see that file's own <code>_source</code> note). Each photo cycles through the five <code>.memory-card-*</code> presets above, and the caption sits OUTSIDE that masked/rotated box on purpose - see this component's own docblock for why a caption inside it risked getting clipped by the torn-edge displacement. The grid now carries <code>tex-leaf-fall-a</code> - the same slow-drifting scattered-leaf wash FAQ uses (see the Textures section above).</p>
			<?php
			View::component(
				'memories/memories',
				array(
					'items' => array(
						array( 'image' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=500&q=80', 'caption' => 'Summer Days' ),
						array( 'image' => 'https://images.unsplash.com/photo-1543269865-cbf427effbad?auto=format&fit=crop&w=500&q=80', 'caption' => 'Family Moments' ),
						array( 'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=500&q=80', 'caption' => 'Timeless Tradition' ),
						array( 'image' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=500&q=80', 'caption' => 'Simple Joys' ),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Badges</h2>
			<p>
				<span class="badge">Default</span>
				<span class="badge badge--primary">Primary</span>
				<span class="badge badge--success">Success</span>
				<span class="badge badge--warning">Warning</span>
				<span class="badge badge--danger">Danger</span>
				<span class="badge badge--outline">Outline</span>
			</p>
		</section>

		<section class="section section--sm">
			<h2>Cards</h2>
			<div class="grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:var(--space-sm);">
				<div class="card">
					<div class="card__body">
						<h3 class="card__title">Base card</h3>
						<p>Sub-parts: media, header, body, footer.</p>
					</div>
				</div>
				<div class="card card--flat">
					<div class="card__body">
						<h3 class="card__title">Flat variant</h3>
						<p>No border, alt background.</p>
					</div>
				</div>
				<div class="card card--elevated">
					<div class="card__body">
						<h3 class="card__title">Elevated variant</h3>
						<p>Shadow instead of a border.</p>
					</div>
					<div class="card__footer">
						<button type="button" class="btn btn--sm">Action</button>
					</div>
				</div>
			</div>
		</section>

		<section class="section section--sm">
			<h2>Forms</h2>
			<div class="form-group">
				<label class="form-label form-label--required" for="el-name">Name</label>
				<input class="form-input" type="text" id="el-name" placeholder="Placeholder text">
				<span class="form-help">Helper text goes here.</span>
			</div>
			<div class="form-group">
				<label class="form-label" for="el-select">Select</label>
				<select class="form-select" id="el-select">
					<option>Option A</option>
					<option>Option B</option>
				</select>
			</div>
			<div class="form-group">
				<label class="form-label" for="el-message">Message</label>
				<textarea class="form-textarea" id="el-message" placeholder="Placeholder text"></textarea>
			</div>
			<div class="form-group">
				<label class="form-label" for="el-invalid">Invalid state</label>
				<input class="form-input is-invalid" type="text" id="el-invalid" value="Bad value">
				<span class="form-error">This field has an error.</span>
			</div>
		</section>

		<section class="section section--sm">
			<h2>Alerts</h2>
			<?php
			foreach ( array( 'info', 'success', 'warning', 'danger' ) as $variant ) {
				View::component(
					'alert/alert',
					array(
						'message' => ucfirst( $variant ) . ' alert - static markup, dismissible.',
						'variant' => $variant,
						'icon'    => 'ℹ',
					)
				);
			}
			?>
			<p><button type="button" class="btn btn--sm" id="el-alert-dynamic">Create alert dynamically</button></p>
		</section>

		<section class="section section--sm">
			<h2>Toasts</h2>
			<p>
				<button type="button" class="btn btn--sm" data-el-toast="info">Info toast</button>
				<button type="button" class="btn btn--sm" data-el-toast="success">Success toast</button>
				<button type="button" class="btn btn--sm" data-el-toast="warning">Warning toast</button>
				<button type="button" class="btn btn--sm" data-el-toast="danger">Danger toast</button>
			</p>
		</section>

		<section class="section section--sm">
			<h2>Dialog</h2>
			<p>
				<button type="button" class="btn btn--sm" data-vs-dialog-open="el-dialog">Open dialog</button>
				<button type="button" class="btn btn--sm" data-vs-dialog-open="el-dialog-fullscreen">Open fullscreen dialog</button>
				<button type="button" class="btn btn--sm" id="el-dialog-dynamic">Create dialog dynamically</button>
			</p>
			<?php
			View::component(
				'dialog/dialog',
				array(
					'id'     => 'el-dialog',
					'title'  => 'Example dialog',
					'body'   => '<p>Static markup rendered by components/dialog/dialog.php.</p>',
					'footer' => '<button type="button" class="btn" data-vs-dialog-close>Close</button>',
				)
			);
			View::component(
				'dialog/dialog',
				array(
					'id'         => 'el-dialog-fullscreen',
					'title'      => 'Fullscreen dialog',
					'body'       => '<p>Same component, <code>fullscreen</code> arg set to true.</p>',
					'footer'     => '<button type="button" class="btn" data-vs-dialog-close>Close</button>',
					'fullscreen' => true,
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Booking wizard</h2>
			<p>From <code>components/booking-wizard/booking-wizard.php</code> + <code>assets/css/components/booking-wizard.css</code> + <code>assets/js/components/booking-wizard.js</code> - a 4-step booking modal (Choose Cane &rarr; Choose Flavours &rarr; Event Details &rarr; Confirm Booking), matching the reference design's booking flow. It IS a <code>.dialog</code> (same markup contract as the Dialog section above) - opening/closing/focus-trap/Escape/backdrop-click all come from <code>dialog.js</code> for free; this component's own JS only owns step navigation, option selection, and building Step 4's live summary. The reference's step-indicator row lists five labels (Cane, Texture, Flavour, Details, Confirm) but only shows four actual step screens - shipped the four that exist rather than inventing content for a step nothing was designed for (see this component's own docblock). No real booking backend exists in this base theme, so "Confirm Booking" dispatches a <code>booking-wizard:submit</code> CustomEvent with the collected field values instead of pretending to submit somewhere real - open the console and submit the form to see the payload.</p>
			<p>
				<button type="button" class="btn" data-vs-dialog-open="el-booking-wizard">Book Your Event</button>
			</p>
			<?php
			View::component(
				'booking-wizard/booking-wizard',
				array(
					'id'              => 'el-booking-wizard',
					'cane_options'    => array(
						array( 'value' => 'yellow', 'label' => 'Yellow Cane', 'desc' => 'Light golden, fresh & refreshing', 'image' => 'https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=300&q=70&auto=format&fit=crop' ),
						array( 'value' => 'red', 'label' => 'Red Cane', 'desc' => 'Naturally sweeter, rich golden-amber tone', 'image' => 'https://images.unsplash.com/photo-1560493676-04071c5f467b?w=300&q=70&auto=format&fit=crop' ),
					),
					'flavour_options' => array(
						array( 'value' => 'pure', 'label' => 'Pure Cane', 'icon' => '🌾' ),
						array( 'value' => 'lemon', 'label' => 'Lemon', 'icon' => '🍋' ),
						array( 'value' => 'ginger', 'label' => 'Ginger', 'icon' => '🫚' ),
						array( 'value' => 'mint', 'label' => 'Mint', 'icon' => '🌿' ),
						array( 'value' => 'pineapple', 'label' => 'Pineapple', 'icon' => '🍍' ),
						array( 'value' => 'watermelon', 'label' => 'Watermelon', 'icon' => '🍉' ),
						array( 'value' => 'mango', 'label' => 'Mango', 'icon' => '🥭' ),
						array( 'value' => 'passionfruit', 'label' => 'Passion Fruit', 'icon' => '🍈' ),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Dropdown</h2>
			<?php
			View::component(
				'dropdown/dropdown',
				array(
					'label' => 'Options',
					'items' => array(
						array(
							'label' => 'First item',
							'url'   => '#',
						),
						array(
							'label' => 'Second item',
							'url'   => '#',
						),
					),
				)
			);
			?>
		</section>

		<section class="section section--sm">
			<h2>Tooltip</h2>
			<p><button type="button" class="btn btn--sm" data-vs-tooltip="This is a tooltip">Hover or focus me</button></p>
		</section>

		<section class="section section--sm">
			<h2>Table</h2>
			<table class="table table--striped">
				<thead>
					<tr><th>Column A</th><th>Column B</th></tr>
				</thead>
				<tbody>
					<tr><td>Row 1</td><td>Value</td></tr>
					<tr><td>Row 2</td><td>Value</td></tr>
					<tr><td>Row 3</td><td>Value</td></tr>
				</tbody>
			</table>
		</section>

		<section class="section section--sm">
			<h2>List</h2>
			<ul class="list list--divided">
				<li class="list__item"><span class="list__icon">✓</span> First item</li>
				<li class="list__item"><span class="list__icon">✓</span> Second item</li>
				<li class="list__item"><span class="list__icon">✓</span> Third item</li>
			</ul>
		</section>

		<section class="section section--sm">
			<h2>Navigation</h2>
			<ul class="nav__list">
				<li><a class="nav__link is-active" href="#">Active link</a></li>
				<li><a class="nav__link" href="#">Link</a></li>
				<li><a class="nav__link" href="#">Link</a></li>
			</ul>
		</section>
	</div>
</div>
