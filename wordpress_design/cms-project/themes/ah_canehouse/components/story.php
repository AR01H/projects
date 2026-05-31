<?php
defined( 'ABSPATH' ) || exit;
$story = ch_get_story_settings();
$facts = (array) ( $story['facts'] ?? ch_mock_story_settings()['facts'] );
?>

<section id="story" class="ch-story-section">
	<div class="ch-story-inner">
		<div class="ch-story-visual fade-left" aria-hidden="true">
			<div class="ch-story-main-card">
				<svg width="180" height="200" viewBox="0 0 180 200" xmlns="http://www.w3.org/2000/svg" style="position:relative;z-index:1">
					<rect x="50" y="20" width="18" height="160" rx="6" fill="rgba(200,232,48,0.3)"/>
					<rect x="78" y="10" width="18" height="170" rx="6" fill="rgba(200,232,48,0.4)"/>
					<rect x="106" y="25" width="18" height="155" rx="6" fill="rgba(200,232,48,0.3)"/>
					<rect x="48" y="70" width="22" height="4" rx="2" fill="rgba(255,255,255,0.2)"/>
					<rect x="48" y="120" width="22" height="4" rx="2" fill="rgba(255,255,255,0.2)"/>
					<rect x="76" y="55" width="22" height="4" rx="2" fill="rgba(255,255,255,0.2)"/>
					<rect x="76" y="110" width="22" height="4" rx="2" fill="rgba(255,255,255,0.2)"/>
					<rect x="104" y="80" width="22" height="4" rx="2" fill="rgba(255,255,255,0.2)"/>
					<rect x="104" y="135" width="22" height="4" rx="2" fill="rgba(255,255,255,0.2)"/>
					<path d="M68 20 Q90 0 110 15" stroke="rgba(200,232,48,0.6)" stroke-width="4" fill="none" stroke-linecap="round"/>
					<path d="M96 10 Q118 -5 135 12" stroke="rgba(200,232,48,0.5)" stroke-width="4" fill="none" stroke-linecap="round"/>
					<text x="90" y="195" text-anchor="middle" font-family="Nunito,sans-serif" font-weight="900" font-size="10" fill="rgba(255,255,255,0.4)" letter-spacing="2">THE CANE HOUSE</text>
				</svg>
			</div>
			<div class="ch-story-secondary-card">
				<p class="ch-story-quote"><?php echo esc_html( $story['quote'] ?? '"Sugarcane - one of nature\'s most generous gifts from the Indian subcontinent. Pure energy, pressed fresh."' ); ?></p>
			</div>
			<div class="ch-story-year-badge">
				<span><?php echo nl2br( esc_html( $story['badge_text'] ?? "2,000+\nYears\nof Cane" ) ); ?></span>
			</div>
		</div>

		<div class="fade-right">
			<div class="ch-section-tag"><?php echo esc_html( $story['tag'] ?? 'Story of Sugarcane' ); ?></div>
			<h2 class="ch-section-title"><?php echo wp_kses( $story['headline'] ?? 'Beyond the <span class="accent">Juice</span>', [ 'span' => [ 'class' => [] ], 'em' => [] ] ); ?></h2>
			<p class="ch-section-body" style="margin-top:1rem;">
				<?php echo esc_html( $story['body_1'] ?? 'Sugarcane has been cherished for over 2,000 years, originating in South and Southeast Asia - particularly the Indian subcontinent - where it has been a cornerstone of Ayurvedic medicine, spiritual offerings, and everyday refreshment.' ); ?>
			</p>
			<p class="ch-section-body" style="margin-top:1rem;font-size:.9rem;">
				<?php echo esc_html( $story['body_2'] ?? 'At The Cane House, we bring this centuries-old tradition to the heart of the UK. Every glass honours that heritage - pressed live, served cool, with the same love and craft that has always made sugarcane juice special.' ); ?>
			</p>
			<div class="ch-story-facts">
				<?php foreach ( $facts as $fact ) :
					$fact = (array) $fact;
				?>
					<div class="ch-story-fact">
						<div class="ch-fact-icon" aria-hidden="true"><?php echo esc_html( $fact['icon'] ?? '🌿' ); ?></div>
						<div class="ch-fact-title"><?php echo esc_html( $fact['title'] ?? '' ); ?></div>
						<div class="ch-fact-desc"><?php echo esc_html( $fact['desc'] ?? '' ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
