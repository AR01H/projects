<?php

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) && is_array( $items ) ? $items : array();

$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) {
				$item = (array) $item;
				return array(
					'title' => trim( (string) ( $item['title'] ?? '' ) ),
					'desc'  => (string) ( $item['desc'] ?? '' ),
					'image' => (string) ( $item['image'] ?? '' ),
					'alt'   => (string) ( $item['alt'] ?? '' ),
				);
			},
			$items
		),
		static function ( $item ) {
			return '' !== $item['title'];
		}
	)
);

if ( empty( $items ) ) {
	return;
}

$presets = array( 'a', 'b', 'c' );
?>
<?php

?>
<ol class="process-steps tex-ground-soil-a">
	<?php foreach ( $items as $i => $item ) :
		$preset = $presets[ $i % count( $presets ) ];
	?>
		<li class="process-steps__item">
			<?php if ( '' !== $item['image'] ) : ?>
				<div class="process-steps__media">
					<div class="sticker-<?php echo esc_attr( $preset ); ?> tex-vintage-grain-a">
						<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( '' !== $item['alt'] ? $item['alt'] : $item['title'] ); ?>" loading="lazy">
					</div>
					<span class="process-steps__index"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				</div>
			<?php endif; ?>
			<h3 class="process-steps__title"><?php echo esc_html( $item['title'] ); ?></h3>
			<?php if ( '' !== $item['desc'] ) : ?>
				<p class="process-steps__desc"><?php echo esc_html( $item['desc'] ); ?></p>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ol>
