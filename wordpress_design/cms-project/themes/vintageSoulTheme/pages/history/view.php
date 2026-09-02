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
$deckled_edge_url = UrlHelper::resolve( 'assets/images/textures/border/deckled-edge.svg' );
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
				'tag'   => (string) ( $hero['tag'] ?? '' ),
				'title' => (string) ( $hero['title'] ?? '' ),
				'sub'   => (string) ( $hero['sub'] ?? '' ),
				'image' => (string) ( $hero['image'] ?? 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg' ),
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
						'tag'   => (string) ( $intro['tag'] ?? '' ),
						'title' => (string) ( $intro['title'] ?? '' ),
						'sub'   => (string) ( $intro['sub'] ?? '' ),
						'image' => (string) ( $intro['image'] ?? '' ),
					)
				);
				?>
			</div>
		</section>
		<div class="deckled-divider" aria-hidden="true">
			<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
		</div>
	<?php endif; ?>

	<!-- ═══════════ 3. WHY SUGARCANE: 4 FOUNDATIONAL PILLARS ═══════════ -->
	<?php if ( ! empty( $why['items'] ) ) : ?>
		<section class="section history-why-section">
			<div class="container">
				<?php
				View::component(
					'section-header/section-header',
					array(
						'tag'   => (string) ( $why['tag'] ?? '' ),
						'title' => (string) ( $why['title'] ?? '' ),
					)
				);
				View::component(
					'feature-row/feature-row',
					array( 'items' => (array) ( $why['items'] ?? array() ) )
				);
				?>
			</div>
		</section>
		<div class="deckled-divider" aria-hidden="true">
			<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
		</div>
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
						'tag'   => (string) ( $timeline['tag'] ?? '' ),
						'title' => (string) ( $timeline['title'] ?? '' ),
						'sub'   => (string) ( $timeline['sub'] ?? '' ),
					)
				);
				?>

				<div class="vintage-timeline-roadmap">
					<div class="timeline-roadmap-spine" aria-hidden="true"></div>
					<?php foreach ( $milestones as $m_idx => $mile ) :
						$m_era  = (string) ( $mile['era'] ?? '' );
						$m_loc  = (string) ( $mile['location'] ?? '' );
						$m_ttl  = (string) ( $mile['title'] ?? '' );
						$m_dsc  = (string) ( $mile['desc'] ?? '' );
						$m_ico  = (string) ( $mile['icon'] ?? '🌱' );
						$is_even = ( 0 === $m_idx % 2 );
					?>
						<div class="timeline-milestone-row<?php echo $is_even ? ' timeline-milestone-row--left' : ' timeline-milestone-row--right'; ?>">
							<div class="timeline-milestone-node" aria-hidden="true">
								<span class="timeline-milestone-node__num"><?php echo esc_html( sprintf( '%02d', $m_idx + 1 ) ); ?></span>
							</div>
							<div class="timeline-milestone-card frame--rough-cut">
								<div class="timeline-milestone-card__header">
									<div class="timeline-milestone-card__era-badge">
										<span class="timeline-milestone-card__icon"><?php echo esc_html( $m_ico ); ?></span>
										<strong class="timeline-milestone-card__era"><?php echo esc_html( $m_era ); ?></strong>
									</div>
									<?php if ( '' !== $m_loc ) : ?>
										<span class="timeline-milestone-card__loc">📍 <?php echo esc_html( $m_loc ); ?></span>
									<?php endif; ?>
								</div>
								<h3 class="timeline-milestone-card__title"><?php echo esc_html( $m_ttl ); ?></h3>
								<p class="timeline-milestone-card__desc"><?php echo esc_html( $m_dsc ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<div class="deckled-divider" aria-hidden="true">
			<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
		</div>
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
						'tag'   => (string) ( $varieties['tag'] ?? '' ),
						'title' => (string) ( $varieties['title'] ?? '' ),
						'sub'   => (string) ( $varieties['sub'] ?? '' ),
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
						$v_img     = UrlHelper::resolve( (string) ( $v_item['image'] ?? '' ) );
					?>
						<div class="variety-specimen-card frame--ornate" 
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
							<div class="variety-specimen-card__media">
								<img src="<?php echo esc_url( $v_img ); ?>" alt="<?php echo esc_attr( $v_name ); ?>" loading="lazy">
								<?php if ( '' !== $v_badge ) : ?>
									<span class="variety-specimen-card__badge"><?php echo esc_html( $v_badge ); ?></span>
								<?php endif; ?>
							</div>
							<div class="variety-specimen-card__body">
								<?php if ( '' !== $v_origin ) : ?>
									<span class="variety-specimen-card__origin">📍 <?php echo esc_html( $v_origin ); ?></span>
								<?php endif; ?>
								<h3 class="variety-specimen-card__title"><?php echo esc_html( $v_name ); ?></h3>
								<?php if ( '' !== $v_species ) : ?>
									<span class="variety-specimen-card__species"><em><?php echo esc_html( $v_species ); ?></em></span>
								<?php endif; ?>
								<?php if ( '' !== $v_brix ) : ?>
									<div class="variety-specimen-card__brix-gauge">
										<span class="brix-gauge__label">Natural Sweetness:</span>
										<span class="brix-gauge__val">⚡ <?php echo esc_html( $v_brix ); ?></span>
									</div>
								<?php endif; ?>
								<?php if ( '' !== $v_prof ) : ?>
									<p class="variety-specimen-card__desc"><?php echo esc_html( $v_prof ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<div class="deckled-divider" aria-hidden="true">
			<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
		</div>
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
						'tag'   => (string) ( $life_cycle['tag'] ?? '' ),
						'title' => (string) ( $life_cycle['title'] ?? '' ),
						'sub'   => (string) ( $life_cycle['sub'] ?? '' ),
					)
				);
				?>

				<!-- Master Botanical Stepper Timeline Navigation -->
				<div class="lifecycle-stepper-wrap" role="tablist" aria-label="Sugarcane Life Cycle Stages">
					<div class="lifecycle-stepper-track">
						<?php foreach ( $life_items as $l_idx => $l_step ) :
							$step_num   = (string) ( $l_step['number'] ?? ( $l_idx + 1 ) );
							$step_label = (string) ( $l_step['label'] ?? '' );
							$step_icon  = (string) ( $l_step['icon'] ?? 'plant' );
							$is_active  = 0 === $l_idx;
						?>
							<button class="lifecycle-step-btn<?php echo $is_active ? ' is-active' : ''; ?>" 
									type="button" 
									role="tab" 
									aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
									aria-controls="lifecycle-panel-<?php echo esc_attr( $step_num ); ?>"
									id="lifecycle-tab-<?php echo esc_attr( $step_num ); ?>"
									data-lifecycle-step="<?php echo esc_attr( $step_num ); ?>">
								<span class="lifecycle-step-btn__circle">
									<span class="lifecycle-step-btn__icon"><?php echo IconHelper::render( $step_icon, '#f6d599', 20 ); // phpcs:ignore ?></span>
								</span>
								<span class="lifecycle-step-btn__label"><?php echo esc_html( $step_label ); ?></span>
								<span class="lifecycle-step-btn__num">0<?php echo esc_html( $step_num ); ?></span>
							</button>
							<?php if ( $l_idx < count( $life_items ) - 1 ) : ?>
								<span class="lifecycle-step-connector" aria-hidden="true">
									<span class="lifecycle-step-connector__line"></span>
									<span class="lifecycle-step-connector__arrow">›</span>
								</span>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Master Showcase Stage Panels -->
				<div class="lifecycle-showcase-container">
					<?php foreach ( $life_items as $l_idx => $l_step ) :
						$step_num      = (string) ( $l_step['number'] ?? ( $l_idx + 1 ) );
						$step_label    = (string) ( $l_step['label'] ?? '' );
						$step_headline = (string) ( $l_step['headline'] ?? $step_label );
						$step_dur      = (string) ( $l_step['duration'] ?? '' );
						$step_fact     = (string) ( $l_step['fact'] ?? '' );
						$step_desc     = (string) ( $l_step['desc'] ?? '' );
						$step_bullets  = (array) ( $l_step['bullets'] ?? array() );
						$step_img      = UrlHelper::resolve( (string) ( $l_step['image'] ?? '' ) );
						$is_active     = 0 === $l_idx;
					?>
						<div class="lifecycle-panel frame--rough-cut<?php echo $is_active ? ' is-active' : ''; ?>"
							 id="lifecycle-panel-<?php echo esc_attr( $step_num ); ?>"
							 role="tabpanel"
							 aria-labelledby="lifecycle-tab-<?php echo esc_attr( $step_num ); ?>"
							 data-lifecycle-panel="<?php echo esc_attr( $step_num ); ?>">
							
							<div class="lifecycle-panel__inner">
								<!-- Left Column: Rich Botanical Information -->
								<div class="lifecycle-panel__content">
									<div class="lifecycle-panel__meta">
										<span class="lifecycle-panel__stage-tag">STAGE <?php echo esc_html( $step_num ); ?> OF 07</span>
										<?php if ( '' !== $step_dur ) : ?>
											<span class="lifecycle-panel__dur-tag">⏳ <?php echo esc_html( $step_dur ); ?></span>
										<?php endif; ?>
									</div>

									<h3 class="lifecycle-panel__title"><?php echo esc_html( $step_headline ); ?></h3>
									<p class="lifecycle-panel__desc"><?php echo esc_html( $step_desc ); ?></p>

									<?php if ( ! empty( $step_bullets ) ) : ?>
										<ul class="lifecycle-panel__bullets">
											<?php foreach ( $step_bullets as $bullet ) : ?>
												<li>
													<span class="lifecycle-bullet-dot">✓</span>
													<span><?php echo esc_html( (string) $bullet ); ?></span>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>

									<?php if ( '' !== $step_fact ) : ?>
										<div class="lifecycle-panel__fact-box">
											<span class="lifecycle-panel__fact-icon">🌿</span>
											<span class="lifecycle-panel__fact-text"><strong>Master Note:</strong> <?php echo esc_html( $step_fact ); ?></span>
										</div>
									<?php endif; ?>

									<div class="lifecycle-panel__nav">
										<button type="button" class="btn btn--secondary-vintage btn--outline-vintage lifecycle-nav-btn lifecycle-nav-btn--prev" data-lifecycle-nav="prev">
											<span>← Previous Stage</span>
										</button>
										<button type="button" class="btn btn--primary-vintage lifecycle-nav-btn lifecycle-nav-btn--next" data-lifecycle-nav="next">
											<span>Next Stage →</span>
										</button>
									</div>
								</div>

								<!-- Right Column: Ornate Botanical Photo Frame -->
								<div class="lifecycle-panel__media">
									<div class="lifecycle-panel__photo-frame frame--ornate">
										<img class="lifecycle-panel__img" src="<?php echo esc_url( $step_img ); ?>" alt="<?php echo esc_attr( $step_headline ); ?>" loading="lazy">
										<div class="lifecycle-panel__photo-overlay"></div>
										<span class="lifecycle-panel__photo-badge"><?php echo esc_html( $step_label ); ?></span>
									</div>
								</div>
							</div>

						</div>
					<?php endforeach; ?>
				</div>

			</div>
		</section>
		<div class="deckled-divider" aria-hidden="true">
			<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
		</div>

		<script>
		document.addEventListener('DOMContentLoaded', function() {
			var tabButtons = document.querySelectorAll('[data-lifecycle-step]');
			var panels = document.querySelectorAll('[data-lifecycle-panel]');
			if (!tabButtons.length || !panels.length) return;

			function switchStage(stepNum) {
				tabButtons.forEach(function(btn) {
					var active = btn.getAttribute('data-lifecycle-step') === String(stepNum);
					btn.classList.toggle('is-active', active);
					btn.setAttribute('aria-selected', active ? 'true' : 'false');
				});
				panels.forEach(function(panel) {
					var active = panel.getAttribute('data-lifecycle-panel') === String(stepNum);
					panel.classList.toggle('is-active', active);
				});
			}

			tabButtons.forEach(function(btn) {
				btn.addEventListener('click', function() {
					var step = this.getAttribute('data-lifecycle-step');
					switchStage(step);
				});
			});

			document.querySelectorAll('[data-lifecycle-nav]').forEach(function(navBtn) {
				navBtn.addEventListener('click', function() {
					var currentActive = document.querySelector('.lifecycle-panel.is-active');
					var currentNum = currentActive ? parseInt(currentActive.getAttribute('data-lifecycle-panel'), 10) : 1;
					var dir = this.getAttribute('data-lifecycle-nav');
					var nextNum = dir === 'next' ? (currentNum >= 7 ? 1 : currentNum + 1) : (currentNum <= 1 ? 7 : currentNum - 1);
					switchStage(nextNum);
				});
			});
		});
		</script>
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
						'tag'   => (string) ( $nutritional_alchemy['tag'] ?? '' ),
						'title' => (string) ( $nutritional_alchemy['title'] ?? '' ),
						'sub'   => (string) ( $nutritional_alchemy['sub'] ?? '' ),
					)
				);
				?>

				<div class="nutrition-apothecary-layout">
					<!-- Left Side: Master Electrolyte Spotlight Banner -->
					<div class="nutrition-spotlight-card">
						<div class="nutrition-spotlight-card__header">
							<span class="spotlight-badge">RAW BOTANICAL SCIENCE</span>
							<h3 class="spotlight-title">100% LIVING PLANT WATER</h3>
						</div>
						<div class="spotlight-metrics">
							<div class="spotlight-metric-item">
								<span class="spotlight-metric-val">420<small>mg</small></span>
								<span class="spotlight-metric-lbl">Bio-Potassium per 330ml</span>
							</div>
							<div class="spotlight-metric-item">
								<span class="spotlight-metric-val">~43</span>
								<span class="spotlight-metric-lbl">Low Glycemic Index (GI)</span>
							</div>
							<div class="spotlight-metric-item">
								<span class="spotlight-metric-val">0%</span>
								<span class="spotlight-metric-lbl">Refined Sugar or Syrups</span>
							</div>
						</div>
						<p class="spotlight-summary">Unlike processed table sugar that causes rapid insulin spikes, raw sugarcane delivers organic glucose and fructose naturally bound to bioavailable plant minerals, phenolic flavonoids, and soluble fibers for sustained cellular hydration.</p>
					</div>

					<!-- Right Side: Periodic Mineral Grid -->
					<div class="mineral-apothecary-grid">
						<?php foreach ( $minerals as $min ) : ?>
							<div class="mineral-apothecary-card frame--rough-cut">
								<div class="mineral-apothecary-card__top">
									<div class="mineral-symbol-stamp"><?php echo esc_html( (string) ( $min['symbol'] ?? '' ) ); ?></div>
									<span class="mineral-amount-badge"><?php echo esc_html( (string) ( $min['amount'] ?? '' ) ); ?></span>
								</div>
								<h4 class="mineral-name"><?php echo esc_html( (string) ( $min['name'] ?? '' ) ); ?></h4>
								<p class="mineral-benefit"><?php echo esc_html( (string) ( $min['benefit'] ?? '' ) ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
		<div class="deckled-divider" aria-hidden="true">
			<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
		</div>
	<?php endif; ?>

	<!-- ═══════════ 8. THE WHOLE-CANE ZERO-WASTE ECOSYSTEM: TRADITION, FOOD, BIOFUEL & MATERIALS ═══════════ -->
	<?php if ( ! empty( $uses['items'] ) ) :
		$use_items      = (array) ( $uses['items'] ?? array() );
		$use_categories = (array) ( $uses['categories'] ?? array() );
	?>
		<section class="section history-uses-section paper-rough" id="uses">
			<div class="container">
				<?php
				View::component(
					'section-header/section-header',
					array(
						'tag'   => (string) ( $uses['tag'] ?? '' ),
						'title' => (string) ( $uses['title'] ?? '' ),
						'sub'   => (string) ( $uses['sub'] ?? '' ),
					)
				);
				?>

				<!-- Interactive Category Filter Bar -->
				<?php if ( ! empty( $use_categories ) ) : ?>
					<div class="uses-category-filter-bar" role="tablist" aria-label="Sugarcane Uses Filter">
						<?php foreach ( $use_categories as $c_idx => $cat_name ) :
							$cat_slug  = 0 === $c_idx ? 'all' : strtolower( trim( preg_replace( '/[^a-z0-9]+/i', '-', $cat_name ), '-' ) );
							$is_active = 0 === $c_idx;
						?>
							<button class="uses-filter-pill<?php echo $is_active ? ' is-active' : ''; ?>"
									type="button"
									role="tab"
									aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
									data-filter-category="<?php echo esc_attr( $cat_slug ); ?>">
								<?php echo esc_html( $cat_name ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<!-- Dynamic Multi-Use Ecosystem Cards Grid -->
				<div class="uses-ecosystem-grid" id="uses-ecosystem-grid">
					<?php foreach ( $use_items as $u_item ) :
						$u_ttl    = (string) ( $u_item['title'] ?? '' );
						$u_dsc    = (string) ( $u_item['desc'] ?? '' );
						$u_cat    = (string) ( $u_item['category'] ?? '' );
						$u_catslug = strtolower( trim( preg_replace( '/[^a-z0-9]+/i', '-', $u_cat ), '-' ) );
						$u_tag    = (string) ( $u_item['tag'] ?? '' );
						$u_impact = (string) ( $u_item['impact'] ?? '' );
						$u_fact   = (string) ( $u_item['fact'] ?? '' );
						$u_icon   = (string) ( $u_item['icon'] ?? 'leaf' );
						$u_img    = UrlHelper::resolve( (string) ( $u_item['image'] ?? '' ) );
					?>
						<article class="use-ecosystem-card frame--rough-cut" data-use-category="<?php echo esc_attr( $u_catslug ); ?>">
							<div class="use-ecosystem-card__top">
								<div class="use-ecosystem-card__icon-box">
									<?php echo IconHelper::render( $u_icon, '#f6d599', 24 ); // phpcs:ignore ?>
								</div>
								<div class="use-ecosystem-card__tags">
									<?php if ( '' !== $u_tag ) : ?>
										<span class="use-ecosystem-card__tag"><?php echo esc_html( $u_tag ); ?></span>
									<?php endif; ?>
									<?php if ( '' !== $u_impact ) : ?>
										<span class="use-ecosystem-card__impact-badge">🌱 <?php echo esc_html( $u_impact ); ?></span>
									<?php endif; ?>
								</div>
							</div>

							<h3 class="use-ecosystem-card__title"><?php echo esc_html( $u_ttl ); ?></h3>
							<p class="use-ecosystem-card__desc"><?php echo esc_html( $u_dsc ); ?></p>

							<?php if ( '' !== $u_fact ) : ?>
								<div class="use-ecosystem-card__fact">
									<span class="fact-leaf">✦</span>
									<span class="fact-text"><?php echo esc_html( $u_fact ); ?></span>
								</div>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>

			</div>
		</section>
		<div class="deckled-divider" aria-hidden="true">
			<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
		</div>

		<!-- Interactive Category Filter Script -->
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			var filterButtons = document.querySelectorAll('[data-filter-category]');
			var cards = document.querySelectorAll('[data-use-category]');
			if (!filterButtons.length || !cards.length) return;

			filterButtons.forEach(function(btn) {
				btn.addEventListener('click', function() {
					var cat = this.getAttribute('data-filter-category');
					
					filterButtons.forEach(function(b) {
						b.classList.remove('is-active');
						b.setAttribute('aria-selected', 'false');
					});
					this.classList.add('is-active');
					this.setAttribute('aria-selected', 'true');

					cards.forEach(function(card) {
						var cardCat = card.getAttribute('data-use-category');
						if (cat === 'all' || cardCat === cat) {
							card.style.display = '';
							card.style.opacity = '0';
							card.style.transform = 'translateY(8px)';
							setTimeout(function() {
								card.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
								card.style.opacity = '1';
								card.style.transform = 'translateY(0)';
							}, 20);
						} else {
							card.style.display = 'none';
						}
					});
				});
			});
		});
		</script>
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
						'tag'   => (string) ( $storage_guide['tag'] ?? '' ),
						'title' => (string) ( $storage_guide['title'] ?? '' ),
						'sub'   => (string) ( $storage_guide['sub'] ?? '' ),
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
		<div class="deckled-divider" aria-hidden="true">
			<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
		</div>
	<?php endif; ?>

	<!-- ═══════════ 10. FREQUENTLY ASKED QUESTIONS ═══════════ -->
	<?php if ( ! empty( $faq['items'] ) ) : ?>
		<?php
		View::component(
			'faq/faq',
			array(
				'tag'     => (string) ( $faq['tag'] ?? '' ),
				'heading' => (string) ( $faq['title'] ?? '' ),
				'items'   => (array) ( $faq['items'] ?? array() ),
				'id'      => 'history-faq',
			)
		);
		?>
	<?php endif; ?>

	<!-- ═══════════ 11. CLOSING HERITAGE CTA ═══════════ -->
	<?php if ( ! empty( $closing ) ) : ?>
		<?php View::component( 'cta-banner/cta-banner', (array) $closing ); ?>
	<?php endif; ?>

	<!-- Trust Accreditation Ribbon -->
	<?php View::component( 'sections/trust-ribbon-section' ); ?>

</div>
