<?php
/**
 * Filter cards - a tagged card grid with filter tabs above it.
 *
 * GENERIC: any tagged collection (menu items, services, portfolio, courses).
 * Filtering is handled by initFilters() in assets/js/common.js purely from data
 * attributes, so this component ships no JS of its own. With JS disabled every
 * card simply stays visible - the content is never hidden behind script.
 *
 * Switch data per page with `source`.
 * Data: { tag, title (em allowed), sub,
 *         filters[] { key, label },            // "all" shows everything
 *         items[]   { name, desc, image, meta, tags[] } }
 */
defined( 'ABSPATH' ) || exit;

$fc_source = ( isset( $source ) && $source ) ? (string) $source : 'filter_cards';
$data      = App_Helpers::data( $fc_source );
$items     = ( is_array( $data ) && ! empty( $data['items'] ) ) ? (array) $data['items'] : array();
if ( empty( $items ) ) {
	return;
}

$tag     = $data['tag']     ?? '';
$title   = $data['title']   ?? '';
$sub     = $data['sub']     ?? '';
$filters = ( ! empty( $data['filters'] ) ) ? (array) $data['filters'] : array();
$uid     = 'app-fc-' . wp_rand( 1000, 9999 );
?>
<section class="app-fcards" id="filter-cards">
	<div class="container" data-nt-filter>

		<?php get_template_part( 'components/parts/section-header', null, array(
	'tag'   => $tag ?? '',
	'title' => $title ?? '',
	'body'  => $sub ?? ''
) ); ?>

		<?php if ( ! empty( $filters ) ) : ?>
			<div class="app-fcards__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Filter items', NT_TEXT_DOMAIN ); ?>">
				<?php foreach ( $filters as $i => $filter ) :
					$filter = (array) $filter;
					$key    = $filter['key'] ?? '';
					$label  = $filter['label'] ?? '';
					if ( '' === $key || '' === $label ) {
						continue;
					}
					$active = ( 0 === $i );
				?>
					<button type="button" role="tab"
					        class="app-fcards__tab<?php echo $active ? ' is-active' : ''; ?>"
					        aria-selected="<?php echo $active ? 'true' : 'false'; ?>"
					        data-nt-filter-btn="<?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( $label ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="app-fcards__grid" id="<?php echo esc_attr( $uid ); ?>">
			<?php foreach ( $items as $item ) :
				$item = (array) $item;
				$name = $item['name'] ?? '';
				if ( '' === trim( (string) $name ) ) {
					continue;
				}
				$tags = ( ! empty( $item['tags'] ) ) ? array_map( 'sanitize_title', (array) $item['tags'] ) : array();
			?>
				<article class="app-fcard" data-nt-filter-item data-tags="<?php echo esc_attr( implode( ' ', $tags ) ); ?>">
					<?php if ( ! empty( $item['image'] ) ) : ?>
						<figure class="app-fcard__media">
							<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
						</figure>
					<?php endif; ?>
					<div class="app-fcard__body">
						<h3 class="app-fcard__name"><?php echo esc_html( $name ); ?></h3>
						<?php if ( ! empty( $item['desc'] ) ) : ?>
							<p class="app-fcard__desc"><?php echo esc_html( $item['desc'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $item['meta'] ) ) : ?>
							<span class="app-fcard__meta"><?php echo esc_html( $item['meta'] ); ?></span>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<p class="app-fcards__empty" data-nt-filter-empty hidden>
			<?php esc_html_e( 'Nothing in this category yet.', NT_TEXT_DOMAIN ); ?>
		</p>

	</div>
</section>
