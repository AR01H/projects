<?php
/**
 * VintageSoulTheme - Reusable Multi-Stage Botanical Lifecycle Stepper Component
 *
 * Renders an interactive tabbed stepper with progress connectors and detailed stage panels
 * with meta badges, bullet points, master note callout, next/prev navigation, and photo frames.
 *
 * Props:
 *   items (array) - Array of stage step objects: { number, label, headline, duration, fact, desc, bullets, image, icon }
 */

use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) ? (array) $items : array();

if ( empty( $items ) ) {
	return;
}
?>

<!-- Master Botanical Stepper Timeline Navigation -->
<div class="lifecycle-stepper-wrap" role="tablist" aria-label="Sugarcane Life Cycle Stages">
	<div class="lifecycle-stepper-track">
		<?php foreach ( $items as $l_idx => $l_step ) :
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
			<?php if ( $l_idx < count( $items ) - 1 ) : ?>
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
	<?php foreach ( $items as $l_idx => $l_step ) :
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

<script>
(function() {
	function initLifecycleStepper() {
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
				var totalSteps = panels.length || 7;
				var dir = this.getAttribute('data-lifecycle-nav');
				var nextNum = dir === 'next' ? (currentNum >= totalSteps ? 1 : currentNum + 1) : (currentNum <= 1 ? totalSteps : currentNum - 1);
				switchStage(nextNum);
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initLifecycleStepper);
	} else {
		initLifecycleStepper();
	}
})();
</script>
