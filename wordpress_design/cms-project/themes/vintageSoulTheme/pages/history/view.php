<?php
/**
 * VintageSoulTheme - All About Sugarcane (History, Heritage & Botanical Science)
 *
 * Rich botanical storytelling, historical timeline, heirloom varieties,
 * nutritional science, zero-waste circular uses, and connoisseur freshness rules.
 */

use VintageSoul\Controllers\HistoryController;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new HistoryController() )->prepare();

$hero                = (array) ( $data['hero'] ?? array() );
$accent              = (array) ( $data['accent'] ?? array() );
$intro               = (array) ( $data['intro'] ?? array() );
$why                 = (array) ( $data['why'] ?? array() );
$timeline            = (array) ( $data['timeline'] ?? array() );
$story               = (array) ( $data['story'] ?? array() );
$history             = (array) ( $data['history'] ?? array() );
$life_cycle          = (array) ( $data['life_cycle'] ?? array() );
$varieties           = (array) ( $data['varieties'] ?? array() );
$goodness            = (array) ( $data['goodness'] ?? array() );
$nutritional_alchemy = (array) ( $data['nutritional_alchemy'] ?? array() );
$benefits            = (array) ( $data['benefits'] ?? array() );
$uses                = (array) ( $data['uses'] ?? array() );
$culture             = (array) ( $data['culture'] ?? array() );
$storage_guide       = (array) ( $data['storage_guide'] ?? array() );
$why_everyone_loves  = (array) ( $data['why_everyone_loves'] ?? array() );
$faq                 = (array) ( $data['faq'] ?? array() );
$closing             = (array) ( $data['closing'] ?? array() );
?>

<div class="history-page">
	
	<!-- Subtle Botanical Background Particle Layer -->
	<?php View::component( 'background/parchment-botanical-bg', array( 'seed' => 47 ) ); ?>

	<!-- ═══════════ 1. MASTER SUBPAGE HERO ═══════════ -->
	<?php if ( ! empty( $hero ) ) : ?>
		<?php
		View::component(
			'subpage-hero/subpage-hero',
			array(
				'id'    => 'history-hero',
				'tag'   => (string) ( $hero['tag'] ?? 'The Story of Sugarcane' ),
				'title' => 'ALL ABOUT <em>Sugar Cane</em>',
				'sub'   => (string) ( $hero['sub'] ?? 'From its origins to your glass — explore the heritage, science, varieties, and culture of nature’s purest drink.' ),
				'image' => 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg',
			)
		);
		?>
	<?php endif; ?>

	<!-- ═══════════ 2. INTRO: BOTANICAL HERITAGE BANNER ═══════════ -->
	<?php if ( ! empty( $intro ) ) : ?>
		<section class="section history-intro-section paper-rough">
			<div class="container container--narrow">
				<?php
				View::component(
					'banner/banner',
					array(
						'tag'   => (string) ( $intro['tag'] ?? 'More Than Just A Crop' ),
						'title' => (string) ( $intro['title'] ?? 'Sugarcane Has Fed, Healed And Sweetened Millions Of Lives' ),
						'sub'   => (string) ( $intro['sub'] ?? 'It feeds, heals, sweetens, and sustains millions of lives. At The Cane House, we celebrate its journey, its goodness, and its incredible story.' ),
						'image' => (string) ( $intro['image'] ?? 'assets/images/sugarcane/stacks.jpg' ),
					)
				);
				?>
			</div>
		</section>
	<?php endif; ?>

	<!-- ═══════════ 3. WHY SUGARCANE: 4 FOUNDATIONAL PILLARS ═══════════ -->
	<?php if ( ! empty( $why['items'] ) ) : ?>
		<section class="section history-why-section">
			<div class="container">
				<?php
				View::component(
					'section-header/section-header',
					array(
						'tag'   => (string) ( $why['tag'] ?? 'A Journey Through Time' ),
						'title' => (string) ( $why['title'] ?? 'Why Sugarcane?' ),
					)
				);
				View::component(
					'feature-row/feature-row',
					array( 'items' => (array) ( $why['items'] ?? array() ) )
				);
				?>
			</div>
		</section>
	<?php endif; ?>

	<!-- ═══════════ 4. CHRONICLES OF SWEETNESS: HISTORICAL TIMELINE ═══════════ -->
	<?php if ( ! empty( $timeline['milestones'] ) ) :
		$milestones = (array) ( $timeline['milestones'] ?? array() );
	?>
		<section class="section history-timeline-section paper-rough" id="timeline">
			<div class="container">
				<?php
				View::component(
					'section-header/section-header',
					array(
						'tag'   => (string) ( $timeline['tag'] ?? '8,000 BC to Present' ),
						'title' => (string) ( $timeline['title'] ?? 'The Ancient <em>Odyssey</em> Of Cane' ),
						'sub'   => (string) ( $timeline['sub'] ?? 'Trace how a wild Pacific grass became the world\'s most cherished botanical elixir.' ),
					)
				);
				?>

				<div class="vintage-timeline-grid">
					<?php foreach ( $milestones as $m_idx => $mile ) :
						$m_era  = (string) ( $mile['era'] ?? '' );
						$m_loc  = (string) ( $mile['location'] ?? '' );
						$m_ttl  = (string) ( $mile['title'] ?? '' );
						$m_dsc  = (string) ( $mile['desc'] ?? '' );
						$m_ico  = (string) ( $mile['icon'] ?? '🌱' );
					?>
						<div class="timeline-step-card frame--rough-cut">
							<div class="timeline-step-card__header">
								<div class="timeline-step-card__badge">
									<span class="timeline-step-card__icon"><?php echo esc_html( $m_ico ); ?></span>
									<span class="timeline-step-card__era"><?php echo esc_html( $m_era ); ?></span>
								</div>
								<span class="timeline-step-card__loc">📍 <?php echo esc_html( $m_loc ); ?></span>
							</div>
							<h3 class="timeline-step-card__title"><?php echo esc_html( $m_ttl ); ?></h3>
							<p class="timeline-step-card__desc"><?php echo esc_html( $m_dsc ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ═══════════ 5. HEIRLOOM CANE VARIETIES SHOWCASE ═══════════ -->
	<?php if ( ! empty( $varieties['items'] ) ) :
		$var_items = (array) ( $varieties['items'] ?? array() );
	?>
		<section class="section history-varieties-section" id="varieties">
			<div class="container">
				<?php
				View::component(
					'section-header/section-header',
					array(
						'tag'   => (string) ( $varieties['tag'] ?? 'Botanical Cultivars' ),
						'title' => (string) ( $varieties['title'] ?? 'Heirloom Cane <em>Varieties</em>' ),
						'sub'   => (string) ( $varieties['sub'] ?? 'Different soils and climates produce distinct cane cultivars, each with unique mineral profiles.' ),
					)
				);
				?>

				<div class="varieties-showcase-grid">
					<?php foreach ( $var_items as $v_item ) :
						$v_name    = (string) ( $v_item['name'] ?? '' );
						$v_species = (string) ( $v_item['species'] ?? '' );
						$v_origin  = (string) ( $v_item['origin'] ?? '' );
						$v_brix    = (string) ( $v_item['brix'] ?? '' );
						$v_prof    = (string) ( $v_item['profile'] ?? '' );
						$v_badge   = (string) ( $v_item['badge'] ?? '' );
						$v_img     = UrlHelper::resolve( (string) ( $v_item['image'] ?? 'assets/images/sugarcane/hero_juice.jpg' ) );
					?>
						<div class="variety-card frame--ornate" 
							 tabindex="0" 
							 role="button" 
							 aria-haspopup="dialog"
							 aria-label="<?php echo esc_attr( $v_name ); ?>"
							 data-story-modal="true"
							 data-story-title="<?php echo esc_attr( $v_name . ' (' . $v_species . ')' ); ?>"
							 data-story-badge="<?php echo esc_attr( $v_badge ); ?>"
							 data-story-meta="<?php echo esc_attr( $v_origin . ' • ' . $v_brix ); ?>"
							 data-story-quote="<?php echo esc_attr( $v_prof ); ?>"
							 data-story-image="<?php echo esc_url( $v_img ); ?>">
							<div class="variety-card__media">
								<img src="<?php echo esc_url( $v_img ); ?>" alt="<?php echo esc_attr( $v_name ); ?>" loading="lazy">
								<?php if ( '' !== $v_badge ) : ?>
									<span class="variety-card__badge"><?php echo esc_html( $v_badge ); ?></span>
								<?php endif; ?>
							</div>
							<div class="variety-card__body">
								<span class="variety-card__origin">📍 <?php echo esc_html( $v_origin ); ?></span>
								<h3 class="variety-card__title"><?php echo esc_html( $v_name ); ?></h3>
								<span class="variety-card__species"><em><?php echo esc_html( $v_species ); ?></em></span>
								<div class="variety-card__brix-tag">
									<span>⚡ <?php echo esc_html( $v_brix ); ?></span>
								</div>
								<p class="variety-card__desc"><?php echo esc_html( $v_prof ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ═══════════ 6. 7-STAGE BOTANICAL LIFE CYCLE ═══════════ -->
	<?php if ( ! empty( $life_cycle['items'] ) ) :
		$life_items = (array) ( $life_cycle['items'] ?? array() );
	?>
		<section class="section history-lifecycle-section paper-rough" id="lifecycle">
			<div class="container">
				<?php
				View::component(
					'section-header/section-header',
					array(
						'tag'   => (string) ( $life_cycle['tag'] ?? 'Field To Glass' ),
						'title' => (string) ( $life_cycle['title'] ?? 'Life Cycle Of <em>Sugarcane</em>' ),
						'sub'   => (string) ( $life_cycle['sub'] ?? 'Explore every biological stage of the ancient Saccharum officinarum plant.' ),
					)
				);
				?>

				<div class="vintage-carousel-wrapper">
					<button class="vintage-carousel-ctrl vintage-carousel-ctrl--prev" type="button" aria-label="Previous Step" onclick="document.getElementById('lifecycle-carousel-track').scrollBy({left: -320, behavior: 'smooth'})">‹</button>
					<div class="vintage-card-carousel" id="lifecycle-carousel-track">
						<?php foreach ( $life_items as $idx => $step ) :
							$step_num   = (string) ( $step['number'] ?? ( $idx + 1 ) );
							$step_label = (string) ( $step['label'] ?? '' );
							$step_dur   = (string) ( $step['duration'] ?? '' );
							$step_fact  = (string) ( $step['fact'] ?? '' );
							$step_desc  = (string) ( $step['desc'] ?? '' );
							$step_img   = UrlHelper::resolve( (string) ( $step['image'] ?? 'assets/images/sugarcane/hero_juice.jpg' ) );
						?>
							<div class="lifecycle-carousel-card frame--rough-cut"
								 tabindex="0" 
								 role="button" 
								 aria-haspopup="dialog"
								 aria-label="<?php echo esc_attr( $step_label ); ?>"
								 data-story-modal="true"
								 data-story-title="<?php echo esc_attr( 'Stage ' . $step_num . ': ' . $step_label ); ?>"
								 data-story-badge="<?php echo esc_attr( $step_dur ); ?>"
								 data-story-meta="<?php echo esc_attr( $step_fact ); ?>"
								 data-story-quote="<?php echo esc_attr( $step_desc ); ?>"
								 data-story-image="<?php echo esc_url( $step_img ); ?>">
								<div class="lifecycle-carousel-card__photo frame--ornate-sm">
									<img src="<?php echo esc_url( $step_img ); ?>" alt="<?php echo esc_attr( $step_label ); ?>" loading="lazy">
									<span class="lifecycle-carousel-card__badge">STAGE <?php echo esc_html( $step_num ); ?></span>
								</div>
								<div class="lifecycle-carousel-card__body">
									<?php if ( $step_dur ) : ?>
										<span class="lifecycle-carousel-card__duration">⏳ <?php echo esc_html( $step_dur ); ?></span>
									<?php endif; ?>
									<h4 class="lifecycle-carousel-card__title"><?php echo esc_html( $step_label ); ?></h4>
									<?php if ( $step_fact ) : ?>
										<span class="lifecycle-carousel-card__fact">🌿 <?php echo esc_html( $step_fact ); ?></span>
									<?php endif; ?>
									<p class="lifecycle-carousel-card__desc"><?php echo esc_html( $step_desc ); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<button class="vintage-carousel-ctrl vintage-carousel-ctrl--next" type="button" aria-label="Next Step" onclick="document.getElementById('lifecycle-carousel-track').scrollBy({left: 320, behavior: 'smooth'})">›</button>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ═══════════ 7. MINERAL ALCHEMY & NUTRITIONAL SCIENCE ═══════════ -->
	<?php if ( ! empty( $nutritional_alchemy['minerals'] ) ) :
		$minerals = (array) ( $nutritional_alchemy['minerals'] ?? array() );
	?>
		<section class="section history-minerals-section" id="nutrition">
			<div class="container">
				<?php
				View::component(
					'section-header/section-header',
					array(
						'tag'   => (string) ( $nutritional_alchemy['tag'] ?? "Nature's Electrolyte Elixir" ),
						'title' => (string) ( $nutritional_alchemy['title'] ?? 'The Mineral <em>Alchemy</em> Of Cane' ),
						'sub'   => (string) ( $nutritional_alchemy['sub'] ?? 'Raw sugarcane is a whole-plant electrolyte elixir, naturally mineral-rich and unrefined.' ),
					)
				);
				?>

				<div class="mineral-grid">
					<?php foreach ( $minerals as $min ) : ?>
						<div class="mineral-card frame--rough-cut">
							<div class="mineral-card__symbol"><?php echo esc_html( (string) ( $min['symbol'] ?? '' ) ); ?></div>
							<h4 class="mineral-card__name"><?php echo esc_html( (string) ( $min['name'] ?? '' ) ); ?></h4>
							<span class="mineral-card__amount"><?php echo esc_html( (string) ( $min['amount'] ?? '' ) ); ?></span>
							<p class="mineral-card__benefit"><?php echo esc_html( (string) ( $min['benefit'] ?? '' ) ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ═══════════ 8. CIRCULAR ZERO-WASTE USES ═══════════ -->
	<?php if ( ! empty( $uses['items'] ) ) :
		$use_items = (array) ( $uses['items'] ?? array() );
	?>
		<section class="section history-uses-section paper-rough" id="uses">
			<div class="container">
				<?php
				View::component(
					'section-header/section-header',
					array(
						'tag'   => (string) ( $uses['tag'] ?? 'One Plant, Every Purpose' ),
						'title' => (string) ( $uses['title'] ?? 'Uses Of <em>Sugarcane</em>' ),
						'sub'   => (string) ( $uses['sub'] ?? 'From raw nutrition to zero-waste circular tableware and bio-energy.' ),
					)
				);
				?>

				<div class="uses-grid">
					<?php foreach ( $use_items as $u_item ) :
						$u_ttl  = (string) ( $u_item['title'] ?? '' );
						$u_dsc  = (string) ( $u_item['desc'] ?? '' );
						$u_tag  = (string) ( $u_item['tag'] ?? 'Pure Cane' );
						$u_icon = (string) ( $u_item['icon'] ?? 'leaf' );
					?>
						<div class="use-card frame--rough-cut">
							<div class="use-card__top">
								<span class="use-card__tag"><?php echo esc_html( $u_tag ); ?></span>
								<div class="use-card__icon-box">
									<?php echo IconHelper::render( $u_icon, '#f6d599', 20 ); // phpcs:ignore ?>
								</div>
							</div>
							<h3 class="use-card__title"><?php echo esc_html( $u_ttl ); ?></h3>
							<p class="use-card__desc"><?php echo esc_html( $u_dsc ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ═══════════ 9. CONNOISSEUR STORAGE & FRESHNESS GUIDE ═══════════ -->
	<?php if ( ! empty( $storage_guide['tips'] ) ) :
		$tips = (array) ( $storage_guide['tips'] ?? array() );
	?>
		<section class="section history-storage-section" id="storage-guide">
			<div class="container">
				<?php
				View::component(
					'section-header/section-header',
					array(
						'tag'   => (string) ( $storage_guide['tag'] ?? "Connoisseur's Guide" ),
						'title' => (string) ( $storage_guide['title'] ?? 'How To Store & <em>Enjoy</em>' ),
						'sub'   => (string) ( $storage_guide['sub'] ?? 'Raw sugarcane juice contains live enzymes. Follow our artisanal handling rules for peak flavour.' ),
					)
				);
				?>

				<div class="storage-steps-grid">
					<?php foreach ( $tips as $tip ) : ?>
						<div class="storage-step-card frame--rough-cut">
							<span class="storage-step-card__num"><?php echo esc_html( (string) ( $tip['step'] ?? '' ) ); ?></span>
							<h4 class="storage-step-card__title"><?php echo esc_html( (string) ( $tip['title'] ?? '' ) ); ?></h4>
							<p class="storage-step-card__desc"><?php echo esc_html( (string) ( $tip['desc'] ?? '' ) ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ═══════════ 10. FREQUENTLY ASKED QUESTIONS ═══════════ -->
	<?php if ( ! empty( $faq['items'] ) ) : ?>
		<?php
		View::component(
			'faq/faq',
			array(
				'tag'     => 'Botanical Wisdom',
				'heading' => 'FREQUENTLY ASKED <em>Questions</em>',
				'items'   => (array) ( $faq['items'] ?? array() ),
				'id'      => 'history-faq',
			)
		);
		?>
	<?php endif; ?>

	<!-- ═══════════ 11. CLOSING HERITAGE CTA ═══════════ -->
	<?php if ( ! empty( $closing ) ) : ?>
		<section class="section history-closing-cta-section" style="padding: 50px 20px 30px; text-align: center;">
			<div class="container container--narrow">
				<div class="history-closing-box frame--ornate" style="background: linear-gradient(135deg, #184b25 0%, #0d2f16 100%); border: 2px solid #caa06d; border-radius: 12px; padding: 44px 32px; box-shadow: inset 0 0 0 1.5px #8e622d, 0 16px 48px rgba(0, 0, 0, 0.35); position: relative; overflow: hidden;">
					<span class="vintage-ribbon-tag" style="margin: 0 auto 12px;"><?php esc_html_e( 'The Cane House Legacy', 'vintagesoul' ); ?></span>
					<h2 style="font-family: 'Cinzel', serif; font-size: clamp(22px, 4vw, 34px); color: #f6d599; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 14px;"><?php echo esc_html( (string) ( $closing['title'] ?? 'The Sweetest Story Ever Told' ) ); ?></h2>
					<p style="font-family: 'EB Garamond', serif; font-size: 17.5px; line-height: 1.65; color: #ebd4b3; max-width: 680px; margin: 0 auto 26px;"><?php echo esc_html( (string) ( $closing['sub'] ?? '' ) ); ?></p>
					<div class="hero-sugarcane__buttons" style="justify-content: center; gap: 16px;">
						<a class="btn btn--primary-vintage" href="<?php echo esc_url( RouteService::url( 'home' ) ); ?>">
							<span><?php esc_html_e( 'VISIT THE CANE HOUSE', 'vintagesoul' ); ?></span>
						</a>
						<a class="btn btn--secondary-vintage" href="<?php echo esc_url( RouteService::url( 'contact' ) ); ?>">
							<span><?php esc_html_e( 'SPEAK WITH US', 'vintagesoul' ); ?></span>
						</a>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Trust Accreditation Ribbon -->
	<?php View::component( 'sections/trust-ribbon-section' ); ?>

</div>
