<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;

$tag        = (string) ( $tag ?? 'Our Gallery' );
$title      = (string) ( $title ?? 'A LOOK BACK IN TIME' );
$subtitle   = (string) ( $subtitle ?? ( $sub ?? 'A few of our favourite moments capturing the heritage, stall life, and smiles over the years.' ) );
$categories = (array) ( $categories ?? array(
	array( 'id' => 'all', 'label' => 'ALL' ),
	array( 'id' => 'sugarcane', 'label' => 'SUGARCANE' ),
	array( 'id' => 'our-stall', 'label' => 'OUR STALL' ),
	array( 'id' => 'events', 'label' => 'EVENTS' ),
	array( 'id' => 'drinks', 'label' => 'DRINKS' ),
	array( 'id' => 'community', 'label' => 'COMMUNITY' ),
) );

$default_items = array(
	array(
		'image'    => 'assets/images/sugarcane/hero_juice.jpg',
		'title'    => 'Fresh Extraction',
		'caption'  => 'Pure cane juice pouring fresh at our London stall.',
		'category' => 'sugarcane',
		'tag'      => 'Harvest & Press',
	),
	array(
		'image'    => 'assets/images/sugarcane/story_moments.jpg',
		'title'    => 'Market Day Smiles',
		'caption'  => 'Generations enjoying pure refreshment together.',
		'category' => 'community',
		'tag'      => 'Community',
	),
	array(
		'image'    => 'assets/images/sugarcane/combo.jpg',
		'title'    => 'Artisanal Flavours',
		'caption'  => 'Ginger, lemon & mint infused cold cane mocktails.',
		'category' => 'drinks',
		'tag'      => 'Signature Drinks',
	),
	array(
		'image'    => 'assets/images/sugarcane/stacks.jpg',
		'title'    => 'Clean Stalks Sourcing',
		'caption'  => 'Handpicked, pre-washed premium sugarcane stalks.',
		'category' => 'our-stall',
		'tag'      => 'Farm Sourced',
	),
	array(
		'image'    => 'assets/images/sugarcane/drink_classic.jpg',
		'title'    => 'The Classic Glass',
		'caption'  => '100% natural sugarcane juice — nothing added.',
		'category' => 'drinks',
		'tag'      => 'Original Taste',
	),
	array(
		'image'    => 'assets/images/sugarcane/drink_mint.jpg',
		'title'    => 'Garden Mint Infusion',
		'caption'  => 'Freshly muddled aromatic mint leaves.',
		'category' => 'drinks',
		'tag'      => 'Refreshing',
	),
	array(
		'image'    => 'assets/images/sugarcane/drink_lemon.jpg',
		'title'    => 'Zesty Lemon Splash',
		'caption'  => 'A citrus twist balancing sweet sugarcane notes.',
		'category' => 'drinks',
		'tag'      => 'Zesty Twist',
	),
	array(
		'image'    => 'assets/images/sugarcane/drink_masala.jpg',
		'title'    => 'Chaat Masala Spice',
		'caption'  => 'Traditional Indian spiced cane mocktail.',
		'category' => 'events',
		'tag'      => 'Heritage Special',
	),
);

$items = (array) ( $items ?? array() );
if ( empty( $items ) ) {
	$items = $default_items;
}
?>
<section class="section section--gallery look-back-vintage paper-rough" id="look-back-in-time">
	<div class="container look-back-vintage__container">
		
		<!-- Header -->
		<div class="look-back-vintage__header">
			<span class="vintage-ribbon-tag vintage-ribbon-tag--gold">
				<span><?php echo esc_html( $tag ); ?></span>
			</span>
			<h2 class="look-back-vintage__title"><?php echo esc_html( trim( strip_tags( $title ), " -—" ) ); ?></h2>
			<p class="look-back-vintage__sub"><?php echo esc_html( $subtitle ); ?></p>
		</div>

		<!-- Filter Tabs -->
		<div class="look-back-tabs">
			<?php foreach ( $categories as $idx => $cat ) :
				$c_id    = (string) ( $cat['id'] ?? ( is_string( $cat ) ? strtolower( str_replace( ' ', '-', $cat ) ) : 'all' ) );
				$c_label = (string) ( $cat['label'] ?? ( is_string( $cat ) ? $cat : 'ALL' ) );
			?>
				<button class="gallery-tab<?php echo 0 === $idx ? ' gallery-tab--active' : ''; ?>" type="button" data-filter="<?php echo esc_attr( $c_id ); ?>">
					<?php echo esc_html( $c_label ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<!-- 8-Photo Rough-Cut Gallery Grid -->
		<div class="look-back-grid">
			<?php foreach ( $items as $item ) :
				$img_src  = is_array( $item ) ? (string) ( $item['image'] ?? $item['src'] ?? '' ) : (string) $item;
				$img_url  = UrlHelper::resolve( $img_src );
				$img_ttl  = is_array( $item ) ? (string) ( $item['title'] ?? $item['label'] ?? 'Cane House Moment' ) : 'Cane House Moment';
				$img_cap  = is_array( $item ) ? (string) ( $item['caption'] ?? $item['desc'] ?? '' ) : '';
				$img_cat  = is_array( $item ) ? (string) ( $item['category'] ?? 'all' ) : 'all';
				$img_tag  = is_array( $item ) ? (string) ( $item['tag'] ?? 'Heritage' ) : 'Heritage';
				$cat_slug = strtolower( str_replace( ' ', '-', $img_cat ) );
			?>
				<div class="look-back-card frame--rough-cut" data-category="<?php echo esc_attr( $cat_slug ); ?>">
					<div class="look-back-card__media">
						<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $img_ttl ); ?>" loading="lazy">
						<span class="look-back-card__tag"><?php echo esc_html( $img_tag ); ?></span>
					</div>
					<div class="look-back-card__content">
						<h3 class="look-back-card__title"><?php echo esc_html( $img_ttl ); ?></h3>
						<?php if ( '' !== $img_cap ) : ?>
							<p class="look-back-card__caption"><?php echo esc_html( $img_cap ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Actions -->
		<div class="look-back-vintage__actions">
			<a class="btn btn--primary-vintage" href="<?php echo esc_url( RouteService::url( 'contact' ) ); ?>">
				<span>📍 VISIT OUR LIVE STALL</span>
			</a>
			<a class="btn btn--secondary-vintage" href="https://wa.me/447770461999" target="_blank" rel="noopener">
				<span>💬 BOOK OUR STALL FOR EVENTS</span>
			</a>
		</div>

	</div>
</section>

<!-- Filter Script -->
<script>
(function() {
	function initLookBackFilter() {
		var section = document.getElementById('look-back-in-time');
		if (!section) return;

		var tabs = section.querySelectorAll('.gallery-tab');
		var cards = section.querySelectorAll('.look-back-card');

		tabs.forEach(function(tab) {
			tab.addEventListener('click', function() {
				tabs.forEach(function(t) { t.classList.remove('gallery-tab--active'); });
				tab.classList.add('gallery-tab--active');

				var filter = tab.getAttribute('data-filter') || 'all';

				cards.forEach(function(card) {
					var cat = card.getAttribute('data-category') || 'all';
					if (filter === 'all' || cat === filter || cat === 'all') {
						card.style.display = 'block';
					} else {
						card.style.display = 'none';
					}
				});
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initLookBackFilter);
	} else {
		initLookBackFilter();
	}
})();
</script>
