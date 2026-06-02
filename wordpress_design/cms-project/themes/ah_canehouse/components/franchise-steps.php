<?php
defined( 'ABSPATH' ) || exit;

$tag   = $args['tag']   ?? 'Getting Started';
$title = $args['title'] ?? 'How It <span class="accent">Works</span>';

$default_steps = [
	[ 'emoji' => '📞', 'title' => 'Enquire',        'desc' => 'Fill in the form below or call us. We\'ll schedule a call to discuss the opportunity in your city.' ],
	[ 'emoji' => '📋', 'title' => 'Discovery Call',  'desc' => 'We walk you through the model, margins, requirements, and answer all your questions honestly.' ],
	[ 'emoji' => '🖊️', 'title' => 'Agreement',       'desc' => 'Sign the franchise agreement, complete training, and receive your equipment and branding pack.' ],
	[ 'emoji' => '🎉', 'title' => 'Launch!',          'desc' => 'Start trading at events in your area with full The Cane House support behind you from day one.', 'highlight' => true ],
];

$steps   = $args['steps'] ?? $default_steps;
$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="ch-franchise-steps-section">
	<div class="container">
		<?php get_template_part( 'components/section-header', null, [
			'tag'   => $tag,
			'title' => $title,
		] ); ?>

		<div class="ch-fstep-carousel fade-up">
			<div class="ch-fstep-track" id="ch-fstep-track">
				<?php foreach ( $steps as $i => $step ) :
					$highlight = ! empty( $step['highlight'] );
				?>
					<div class="ch-step-card<?php echo $highlight ? ' ch-step-card--highlight' : ''; ?><?php echo $i === 0 ? ' active' : ''; ?>">
						<div class="ch-step-num<?php echo $highlight ? ' ch-step-num--highlight' : ''; ?>"><?php echo $i + 1; ?></div>
						<div class="ch-step-emoji"><?php echo esc_html( $step['emoji'] ?? '' ); ?></div>
						<div class="ch-step-title"><?php echo esc_html( $step['title'] ?? '' ); ?></div>
						<div class="ch-step-desc"><?php echo esc_html( $step['desc'] ?? '' ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="ch-fstep-nav">
				<div class="ch-fstep-dots" id="ch-fstep-dots" role="tablist" aria-label="Steps navigation">
					<?php foreach ( $steps as $i => $_ ) : ?>
						<button class="ch-dot<?php echo $i === 0 ? ' active' : ''; ?>"
							role="tab" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
							aria-label="Step <?php echo $i + 1; ?>"></button>
					<?php endforeach; ?>
				</div>
				<div class="ch-fstep-arrows">
					<button class="ch-v-btn" id="ch-fstep-prev" aria-label="Previous step">←</button>
					<button class="ch-v-btn" id="ch-fstep-next" aria-label="Next step">→</button>
				</div>
			</div>
		</div>
	</div>
</section>
