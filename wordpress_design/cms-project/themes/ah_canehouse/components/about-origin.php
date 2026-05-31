<?php
/**
 * Business origin / "Why We Started" section for the About page.
 *
 * Args (all optional):
 *  tag      (string)  Eyebrow tag.    Default: 'How It Started'
 *  title    (string)  Heading HTML.   Default: preset
 *  paras    (array)   Body paragraphs. Default: preset
 *  milestones (array) Timeline items: [ 'year', 'text' ]
 *  image    (string)  Side image URL.
 */
defined( 'ABSPATH' ) || exit;

$tag   = $args['tag']   ?? 'How It Started';
$title = $args['title'] ?? 'A Passion Born From <span class="accent">Childhood Summers</span>';

$default_paras = [
	'Growing up, the smell of freshly pressed sugarcane juice meant summer, family, and happiness. Street vendors in South Asia would press whole stalks right in front of you - the sound of the machine, the green juice flowing, the ginger and lemon hit. It was pure, instant joy.',
	'When we moved to the UK, that experience was gone. Bottled juice couldn\'t replicate it. Supermarkets had nothing close. We missed it. So we decided to bring it back - properly, with a commercial press, fresh stalks, and the same love.',
	'The Cane House was born from that simple feeling: everyone deserves to experience the real thing. Not a substitute. Not a powder sachet. Live-pressed, ice cold, served with a smile.',
];

$default_milestones = [
	[ 'year' => '2019', 'text' => 'First market stall with a borrowed press and a dream.' ],
	[ 'year' => '2021', 'text' => 'First wedding booking — 400 guests, standing ovation from the bar queue.' ],
	[ 'year' => '2022', 'text' => 'Custom stainless steel machine built. First full event season.' ],
	[ 'year' => '2023', 'text' => 'Franchise enquiries start flooding in. First partner launched.' ],
	[ 'year' => '2024', 'text' => 'The Cane House brand formally launched across the UK.' ],
];

$paras      = $args['paras']      ?? $default_paras;
$milestones = $args['milestones'] ?? $default_milestones;
$image      = $args['image']      ?? 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=600&h=700&q=80';
$allowed    = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="ch-about-origin-section">
	<div class="container">
		<div class="ch-origin-grid">

			<div class="ch-origin-content fade-left">
				<div class="section-tag"><?php echo esc_html( $tag ); ?></div>
				<h2 class="section-title"><?php echo wp_kses( $title, $allowed ); ?></h2>
				<?php foreach ( $paras as $para ) : ?>
					<p class="section-body" style="margin-top:1rem;"><?php echo esc_html( $para ); ?></p>
				<?php endforeach; ?>
			</div>

			<div class="ch-origin-visual fade-right">
				<img src="<?php echo esc_url( $image ); ?>"
					alt="The Cane House origins"
					loading="lazy"
					class="ch-origin-img">
			</div>

		</div>

		<!-- Milestone timeline -->
		<div class="ch-origin-timeline fade-up">
			<?php foreach ( $milestones as $ms ) : ?>
				<div class="ch-timeline-item">
					<div class="ch-timeline-year"><?php echo esc_html( $ms['year'] ?? '' ); ?></div>
					<div class="ch-timeline-dot"></div>
					<div class="ch-timeline-text"><?php echo esc_html( $ms['text'] ?? '' ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
