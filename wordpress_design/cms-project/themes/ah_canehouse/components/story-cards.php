<?php
defined( 'ABSPATH' ) || exit;
$cards = ch_get_story_cards();
if ( empty( $cards ) ) return;
$s        = ch_get_settings();
$heading  = $s['story_cards_heading'] ?? 'The Sugarcane <span class="accent">Story</span>';
$subtext  = $s['story_cards_sub']     ?? 'From ancient fields to your cup — pressed live, served cool, every single time.';
?>

<section id="story-cards" class="ch-sc-section">
	<div class="container">

		<div class="ch-sc-header fade-up">
			<div class="section-tag">The Journey</div>
			<h2 class="section-title"><?php echo wp_kses( $heading, [ 'span' => [ 'class' => [] ], 'em' => [] ] ); ?></h2>
			<p class="section-body"><?php echo esc_html( $subtext ); ?></p>
		</div>

		<!-- ── Tab cards ────────────────────────────────────────────────────── -->
		<div class="ch-sc-tabs fade-up" role="tablist" aria-label="Sugarcane story steps">
			<?php foreach ( $cards as $i => $card ) :
				$card = (array) $card;
				$id   = esc_attr( $card['id'] ?? 'card-' . $i );
			?>
				<button class="ch-sc-tab<?php echo $i === 0 ? ' active' : ''; ?>"
					role="tab"
					id="ch-sc-tab-<?php echo $id; ?>"
					aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
					aria-controls="ch-sc-panel-<?php echo $id; ?>"
					data-target="ch-sc-panel-<?php echo $id; ?>">
					<span class="ch-sc-tab-icon"><?php echo esc_html( $card['icon'] ?? '🌿' ); ?></span>
					<span class="ch-sc-tab-label"><?php echo esc_html( $card['label'] ?? '' ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>

		<!-- ── Content panels ───────────────────────────────────────────────── -->
		<div class="ch-sc-panels">
			<?php foreach ( $cards as $i => $card ) :
				$card  = (array) $card;
				$id    = esc_attr( $card['id'] ?? 'card-' . $i );
				$facts = (array) ( $card['facts'] ?? [] );
				$img   = $card['image'] ?? '';
			?>
				<div class="ch-sc-panel<?php echo $i === 0 ? ' active' : ''; ?>"
					id="ch-sc-panel-<?php echo $id; ?>"
					role="tabpanel"
					aria-labelledby="ch-sc-tab-<?php echo $id; ?>">

					<div class="ch-sc-panel-inner">

						<!-- Left: icon + heading + body + facts -->
						<div class="ch-sc-panel-content">
							<!-- <div class="ch-sc-panel-icon"><?php echo esc_html( $card['icon'] ?? '🌿' ); ?></div> -->
							<h3 class="ch-sc-panel-heading"><?php echo esc_html( $card['heading'] ?? '' ); ?></h3>
							<p class="ch-sc-panel-body"><?php echo esc_html( $card['body'] ?? '' ); ?></p>
							<?php if ( ! empty( $facts ) ) : ?>
								<ul class="ch-sc-facts">
									<?php foreach ( $facts as $fact ) : ?>
										<li><?php echo esc_html( $fact ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>

						<!-- Right: image or decorative visual -->
						<div class="ch-sc-panel-visual">
							<?php if ( $img ) : ?>
								<img src="<?php echo esc_url( $img ); ?>"
									alt="<?php echo esc_attr( $card['label'] ?? '' ); ?>"
									loading="lazy" class="ch-sc-panel-img">
							<?php else : ?>
								<div class="ch-sc-panel-placeholder">
									<span class="ch-sc-placeholder-icon"><?php echo esc_html( $card['icon'] ?? '🌿' ); ?></span>
									<div class="ch-sc-placeholder-rings">
										<div class="ch-sc-ring"></div>
										<div class="ch-sc-ring"></div>
										<div class="ch-sc-ring"></div>
									</div>
								</div>
							<?php endif; ?>
						</div>

					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Progress bar -->
		<div class="ch-sc-progress" aria-hidden="true">
			<?php foreach ( $cards as $i => $card ) : ?>
				<div class="ch-sc-progress-dot<?php echo $i === 0 ? ' active' : ''; ?>"
					data-idx="<?php echo $i; ?>"></div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
