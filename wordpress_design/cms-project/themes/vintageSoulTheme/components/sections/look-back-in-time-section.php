<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Services\SettingsService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$gallery_data = (array) ( JsonFileProvider::read( 'data/content/gallery.json' ) ?? array() );

$tag        = (string) ( $tag ?? $gallery_data['tag'] ?? 'Our Gallery' );
$title      = (string) ( $title ?? $gallery_data['title'] ?? 'LOOK BACK IN <em>Time</em>' );
$subtitle   = (string) ( $subtitle ?? ( $sub ?? ( $gallery_data['subtitle'] ?? ( $gallery_data['body'] ?? 'A few of our favourite moments capturing the heritage, stall life, and smiles over the years.' ) ) ) );
$categories = ! empty( $categories ) ? (array) $categories : (array) ( $gallery_data['categories'] ?? array() );
$items      = ! empty( $items ) ? (array) $items : (array) ( $gallery_data['items'] ?? ( $gallery_data['images'] ?? array() ) );

$actions_cfg   = (array) ( $actions ?? ( $gallery_data['actions'] ?? array() ) );
$primary_btn   = (array) ( $actions_cfg['primary'] ?? array( 'label' => 'VISIT OUR LIVE STALL', 'icon' => 'pin', 'route' => 'contact' ) );
$secondary_btn = (array) ( $actions_cfg['secondary'] ?? array( 'label' => 'BOOK OUR STALL FOR EVENTS', 'icon' => 'tent', 'url' => SettingsService::whatsapp_url() ) );

$primary_url = isset( $primary_btn['url'] ) ? (string) $primary_btn['url'] : RouteService::url( (string) ( $primary_btn['route'] ?? 'contact' ) );
$secondary_url = isset( $secondary_btn['url'] ) ? (string) $secondary_btn['url'] : ( isset( $secondary_btn['route'] ) ? RouteService::url( (string) $secondary_btn['route'] ) : SettingsService::whatsapp_url() );
?>
<section class="section section--gallery look-back-vintage paper-rough" id="look-back-in-time">
	<div class="container look-back-vintage__container">
		
		<!-- Header -->
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'    => $tag,
				'title'  => $title,
				'sub'    => $subtitle,
				'ribbon' => true,
			)
		);
		?>

		<!-- Filter Tabs -->
		<?php if ( ! empty( $categories ) ) : ?>
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
		<?php endif; ?>

		<!-- 8-Photo Rough-Cut Gallery Grid -->
		<div class="look-back-grid">
			<?php foreach ( $items as $item ) :
				$img_src  = is_array( $item ) ? (string) ( $item['image'] ?? ( $item['src'] ?? '' ) ) : (string) $item;
				$img_url  = UrlHelper::resolve( $img_src );
				$img_ttl  = is_array( $item ) ? (string) ( $item['title'] ?? ( $item['label'] ?? 'Cane House Moment' ) ) : 'Cane House Moment';
				$img_cap  = is_array( $item ) ? (string) ( $item['caption'] ?? ( $item['desc'] ?? '' ) ) : '';
				$img_cat  = is_array( $item ) ? (string) ( $item['category'] ?? 'all' ) : 'all';
				$img_tag  = is_array( $item ) ? (string) ( $item['tag'] ?? 'Heritage' ) : 'Heritage';
				$cat_slug = strtolower( str_replace( ' ', '-', $img_cat ) );
			?>
				<div class="look-back-card frame--rough-cut" 
					 data-category="<?php echo esc_attr( $cat_slug ); ?>"
					 tabindex="0"
					 role="button"
					 aria-haspopup="dialog"
					 aria-label="<?php echo esc_attr( $img_ttl ); ?>"
					 data-story-modal="true"
					 data-story-title="<?php echo esc_attr( $img_ttl ); ?>"
					 data-story-image="<?php echo esc_url( $img_url ); ?>"
					 data-story-quote="<?php echo esc_attr( $img_cap ); ?>"
					 data-story-meta="<?php echo esc_attr( $img_tag ); ?>">
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
			<a class="btn btn--primary-vintage" href="<?php echo esc_url( $primary_url ); ?>">
				<span class="btn__icon"><?php echo IconHelper::render( (string) ( $primary_btn['icon'] ?? 'pin' ), '#f6d599', 15 ); // phpcs:ignore ?></span>
				<span><?php echo esc_html( (string) ( $primary_btn['label'] ?? 'VISIT OUR LIVE STALL' ) ); ?></span>
			</a>
			<a class="btn btn--secondary-vintage" href="<?php echo esc_url( $secondary_url ); ?>" target="_blank" rel="noopener">
				<span class="btn__icon"><?php echo IconHelper::render( (string) ( $secondary_btn['icon'] ?? 'tent' ), '#f6d599', 15 ); // phpcs:ignore ?></span>
				<span><?php echo esc_html( (string) ( $secondary_btn['label'] ?? 'BOOK OUR STALL FOR EVENTS' ) ); ?></span>
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
			tab.addEventListener('click', function(e) {
				e.preventDefault();
				tabs.forEach(function(t) { t.classList.remove('gallery-tab--active'); });
				tab.classList.add('gallery-tab--active');

				var filter = (tab.getAttribute('data-filter') || 'all').toLowerCase().trim();

				cards.forEach(function(card) {
					var cat = (card.getAttribute('data-category') || '').toLowerCase().trim();
					if (filter === 'all' || cat === filter || (filter === 'drinks' && cat.indexOf('drink') !== -1) || (filter === 'sugarcane' && cat.indexOf('sugarcane') !== -1)) {
						card.classList.remove('is-hidden');
						card.hidden = false;
						card.style.removeProperty('display');
					} else {
						card.classList.add('is-hidden');
						card.hidden = true;
						card.style.setProperty('display', 'none', 'important');
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
