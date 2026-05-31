<?php
/**
 * Template Name: Our Story
 */
defined( 'ABSPATH' ) || exit;
get_header();

$cards    = ch_get_story_cards();
$s        = ch_get_settings();
$heading  = $s['story_cards_heading'] ?? 'The Sugarcane Story';
$subtext  = $s['story_cards_sub']     ?? 'From ancient fields to your cup - pressed live, served cool, every single time.';
?>

<main class="ch-main" id="main-content">

<!-- ── Hero ─────────────────────────────────────────────────────────────────── -->
<section class="ch-page-hero ch-page-hero--sugarcane">
	<div class="container">
		<div class="fade-up" style="display:flex;flex-direction:column;align-items:center;text-align:center;">
			<div class="section-tag">The Journey</div>
			<h1 class="ch-page-hero__title"><?php echo wp_kses( $heading, [ 'span' => [ 'class' => [] ], 'em' => [] ] ); ?></h1>
			<p class="ch-page-hero__desc"><?php echo esc_html( $subtext ); ?></p>
		</div>
	</div>
</section>

<!-- ── Full story (alternating sections) ────────────────────────────────────── -->
<section style="background:var(--ch-white);padding:5rem 2rem;">
	<div class="container">
		<div class="ch-storypage">
			<?php foreach ( $cards as $i => $card ) :
				$card  = (array) $card;
				$facts = (array) ( $card['facts'] ?? [] );
				$imgs  = ch_card_images( $card );
				$rev   = $i % 2 === 1; // alternate image side
			?>
				<div class="ch-storypage-row<?php echo $rev ? ' ch-storypage-row--reverse' : ''; ?> fade-up">
					<div class="ch-storypage-content">
						<div class="ch-storypage-icon"><?php echo esc_html( $card['icon'] ?? '🌿' ); ?></div>
						<div class="ch-storypage-eyebrow"><?php echo esc_html( $card['label'] ?? '' ); ?></div>
						<h2 class="ch-storypage-heading"><?php echo esc_html( $card['heading'] ?? '' ); ?></h2>
						<p class="ch-storypage-body"><?php echo esc_html( $card['body'] ?? '' ); ?></p>
						<?php if ( ! empty( $facts ) ) : ?>
							<ul class="ch-sc-facts">
								<?php foreach ( $facts as $fact ) : ?>
									<li><?php echo esc_html( $fact ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
					<div class="ch-storypage-visual">
						<?php if ( ! empty( $imgs ) ) : ?>
							<img src="<?php echo esc_url( $imgs[0] ); ?>"
								alt="<?php echo esc_attr( $card['label'] ?? '' ); ?>"
								loading="lazy" class="ch-storypage-img">
							<?php if ( count( $imgs ) > 1 ) : ?>
								<div class="ch-storypage-thumbs">
									<?php foreach ( array_slice( $imgs, 1, 3 ) as $thumb ) : ?>
										<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						<?php else : ?>
							<div class="ch-storypage-emoji"><?php echo esc_html( $card['icon'] ?? '🌿' ); ?></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ── CTA ──────────────────────────────────────────────────────────────────── -->
<section class="ch-inner-cta">
	<div class="container">
		<div class="ch-inner-cta__box fade-up">
			<h2>Taste 2,000 Years of Tradition</h2>
			<p>Fresh sugarcane juice, pressed live and served cool - just for you.</p>
			<div class="ch-inner-cta__btns">
				<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn-lime">🥤 Hire Us</a>
				<a href="<?php echo esc_url( home_url( '/events/' ) ); ?>" class="btn-outline ch-btn-outline-light">Book for Events →</a>
			</div>
		</div>
	</div>
</section>

</main>
<?php get_footer(); ?>
