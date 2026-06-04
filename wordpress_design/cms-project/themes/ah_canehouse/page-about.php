<?php
/**
 * Template Name: About Us
 * Slug: /about/
 *
 * The business story - origin, mission, team, equipment, values.
 */
defined( 'ABSPATH' ) || exit;
get_header();

$mvv           = ch_get_about_mvv();
$quality_items = ch_get_about_quality();

$team = ch_get_team_members();
?>

<main class="ch-main" id="main-content">

<!-- ── Hero ──────────────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/page-hero', null, [
	'tag'        => 'About Us',
	'heading'    => 'The Story Behind <em>The Cane House</em>',
	'desc'       => 'We believe in the power of nature\'s simplest gifts. Live-pressed, served cool, with nothing added - and a whole lot of heart behind every glass.',
	'modifier'   => 'ch-page-hero--sugarcane',
] ); ?>

<!-- ── Why We Started ────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/about-origin' ); ?>

<!-- ── Mission / Vision / Values ─────────────────────────────────────────────── -->
<section class="about-mission">
	<div class="container">
		<div style="text-align:center;margin-bottom:3rem;" class="fade-up">
			<div class="section-tag">What Drives Us</div>
			<h2 class="section-title">Our <span class="accent">Foundation</span></h2>
		</div>
		<div class="mission-carousel" data-oc data-oc-autoplay="4500">
			<div class="mission-grid" data-oc-track>
			<?php foreach ( $mvv as $i => $card ) :
				$card  = (array) $card;
				$anims = [ 'fade-left', 'fade-up', 'fade-right' ];
				$cls   = $anims[ $i % 3 ] ?? 'fade-up';
			?>
				<div class="mission-card <?php echo $cls; ?>">
					<div class="mission-icon"><?php echo esc_html( $card['icon'] ?? '🌿' ); ?></div>
					<h3><?php echo esc_html( $card['title'] ?? '' ); ?></h3>
					<p><?php echo esc_html( $card['text'] ?? '' ); ?></p>
				</div>
			<?php endforeach; ?>
			</div>

			<?php if ( count( $mvv ) > 1 ) : ?>
				<div class="mission-nav" aria-hidden="true">
					<button type="button" class="mission-arrow" data-oc-prev aria-label="Previous">‹</button>
					<div class="mission-dots" data-oc-dots>
						<?php foreach ( $mvv as $mi => $card ) : ?>
							<button type="button" class="mission-dot<?php echo $mi === 0 ? ' active' : ''; ?>" data-go="<?php echo (int) $mi; ?>"></button>
						<?php endforeach; ?>
					</div>
					<button type="button" class="mission-arrow" data-oc-next aria-label="Next">›</button>
				</div>
			<?php endif; ?>
		</div><!-- /.mission-carousel -->
	</div>
</section>

<!-- ── Team ──────────────────────────────────────────────────────────────────── -->
<?php if ( ! empty( $team ) ) : ?>
<section id="team" class="about-team">
	<div class="container">
		<div style="text-align:center;margin-bottom:3rem;" class="fade-up">
			<div class="section-tag">Meet the Team</div>
			<h2 class="section-title">The People Behind <span class="accent">The Cane House</span></h2>
		</div>
		<div class="team-grid">
			<?php foreach ( $team as $member ) :
				$member    = (array) $member;
				$name      = esc_html( $member['name']     ?? '' );
				$role      = esc_html( $member['role']     ?? '' );
				$bio       = wp_kses_post( $member['bio']  ?? '' );
				$image_url = esc_url( $member['image_url'] ?? '' );
			?>
				<div class="team-card fade-up">
					<div class="team-image">
						<?php if ( $image_url ) : ?>
							<img src="<?php echo $image_url; ?>" alt="<?php echo $name; ?>" loading="lazy">
						<?php else : ?>
							<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:4rem;">👤</div>
						<?php endif; ?>
					</div>
					<div class="team-info">
						<h3><?php echo $name; ?></h3>
						<?php if ( $role ) : ?><span class="team-role"><?php echo $role; ?></span><?php endif; ?>
						<?php if ( $bio )  : ?><p class="team-bio"><?php echo $bio; ?></p><?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ── About Gallery ─────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/media-gallery', null, [
	'tag'   => 'Our Gallery',
	'title' => 'View <span class="accent">Our Hygiene</span>',
	'body'  => 'A visual journey through our beginnings, our team, and the craft behind every glass.',
	'bg'    => 'var(--accent)',
	'id'    => 'mg-about',
	'items' => ch_get_about_gallery(),
] ); 

get_template_part( 'components/gallery-strip', null, [
	'tag'      => 'Behind the Scenes',
	'title'    => 'Our Equipment, <span class="accent">Our Craft</span>',
	'body'     => 'The machines, the setup, the ingredients - everything that goes into every perfect glass.',
	'modifier' => 'ch-gstrip--about',
	'id'       => 'gstrip-about',
	'bg'       => 'var(--ch-white)',
	'images'   => [
		[ 'src' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Commercial Press',    'desc' => 'Stainless steel, purpose-built machine' ],
		[ 'src' => 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Event Stall',         'desc' => 'Mobile setup, ready in 30 minutes' ],
		[ 'src' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Live Pressing',        'desc' => 'Fresh to order, every single time' ],
		[ 'src' => 'https://images.unsplash.com/photo-1546833998-877b37c2e5c6?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Fresh Ingredients',    'desc' => 'Whole stalks, ginger, lemon, mint' ],
		[ 'src' => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'First Glass Served',  'desc' => 'The moment it all comes together' ],
		[ 'src' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&w=560&h=420&q=80', 'label' => 'Our Team',            'desc' => 'Passionate about every pour' ],
	]] );
?>


<!-- ── Quality / Promise ─────────────────────────────────────────────────────── -->
<?php
ob_start();
foreach ( $quality_items as $item ) {
	echo '<li>✓ ' . esc_html( is_array( $item ) ? ( $item['text'] ?? '' ) : $item ) . '</li>';
}
$_about_values_extra = '<ul class="values-list">' . ob_get_clean() . '</ul>';

$_about_values_visual = '<div style="display:flex;align-items:center;justify-content:center;">'
	. '<div class="promise-card">'
	. '<span class="promise-icon">🌱</span>'
	. '<div class="promise-title">Our Promise</div>'
	. '<div class="promise-sub">Pressed Fresh. Served Cool.</div>'
	. '<div class="promise-tags">'
	. '<div class="promise-tag">No added sugar</div>'
	. '<div class="promise-tag">No preservatives</div>'
	. '<div class="promise-tag">Pure, natural refreshment</div>'
	. '<div class="promise-tag">Pressed live at every order</div>'
	. '<div class="promise-tag">Served chilled, always fresh</div>'
	. '</div>'
	. '</div>'
	. '</div>';

get_template_part( 'components/image-text-split', null, [
	'layout'        => 'image-right',
	'section_class' => 'about-values',
	'inner_class'   => 'values-content',
	'tag'           => 'Why We Do It',
	'title'         => 'What Makes <span class="accent">The Cane House</span> Different?',
	'body'          => 'At The Cane House, we serve freshly pressed sugarcane juice and natural fruit blends that are prepared fresh for every customer. Our drinks offer a refreshing alternative to fizzy drinks and processed juices, bringing a traditional summer favourite enjoyed by millions to the heart of Sutton.',
	'extra_html'    => $_about_values_extra,
	'visual_html'   => $_about_values_visual,
	'content_anim'  => 'fade-left',
	'visual_anim'   => 'fade-right',
] );
unset( $_about_values_extra, $_about_values_visual );
?>

<!-- ── Events preview ────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/events-preview', null, [
	'tag'     => 'Events & Hire',
	'heading' => 'Need Us at Your <span class="accent">Event?</span>',
	'body'    => 'From weddings to corporate events, we bring freshly-pressed sugarcane juice live to your guests.',
] ); ?>

<!-- ── CTA ────────────────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/cta-section', null, [
	'tag'        => 'Work With Us',
	'heading'    => 'Let\'s Do Something <span class="accent" style="color:var(--ch-lime);">Amazing</span>',
	'body'       => 'Book us for your next event, or take the leap and bring The Cane House to your city with a franchise.',
	'btn_label'  => '🥤 Book an Event',
	'btn_url'    => home_url( '/events/' ),
	'btn2_label' => 'Explore Franchise',
	'btn2_url'   => home_url( '/franchise/' ),
	'show_phone' => false,
] ); ?>

</main>
<?php get_footer(); ?>
