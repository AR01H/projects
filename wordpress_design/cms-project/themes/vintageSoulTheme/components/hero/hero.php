<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;

$title       = isset( $title ) ? trim( (string) $title ) : '';
$subtitle    = isset( $subtitle ) ? (string) $subtitle : '';
$description = isset( $description ) ? (string) $description : '';
$image       = isset( $image ) ? (string) $image : '';
$video       = isset( $video ) ? (string) $video : '';

$checks = isset( $checks ) && is_array( $checks ) ? array_values( array_filter( array_map( 'strval', $checks ) ) ) : array();

$buttons = isset( $buttons ) && is_array( $buttons ) ? $buttons : array();
$buttons = array_values(
	array_filter(
		array_map(
			static function ( $btn ) {
				$btn = (array) $btn;
				return array(
					'label' => trim( (string) ( $btn['label'] ?? '' ) ),
					'icon'  => (string) ( $btn['icon'] ?? '' ),
					'route' => (string) ( $btn['route'] ?? '' ),
					'ghost' => 'ghost' === ( $btn['style'] ?? '' ),
				);
			},
			$buttons
		),
		static function ( $btn ) {
			return '' !== $btn['label'] && '' !== $btn['route'];
		}
	)
);

if ( '' === $title ) {
	return;
}

$title_lines = array_values( array_filter( array_map( 'trim', preg_split( '/(?<=\.)\s+/', $title ) ) ) );
if ( empty( $title_lines ) ) {
	$title_lines = array( $title );
}
$line_variants = array( 'a', 'b', 'c' );

$has_media = ( '' !== $image || '' !== $video );

$hero_accents = array( 'tex-organic-a', 'tex-organic-b', 'tex-botanical-a', 'tex-stamp-a', 'tex-cane-ribbon-a' );
$hero_accent  = $hero_accents[ array_rand( $hero_accents ) ];
$hero_media_class = 'hero__media' . ( $has_media ? ' tex-vintage-grain-a ' . $hero_accent : '' );
?>
<div class="hero">
	<?php if ( $has_media ) : ?>
		<div class="<?php echo esc_attr( $hero_media_class ); ?>">
			<?php if ( '' !== $video ) : ?>
				<video
					src="<?php echo esc_url( $video ); ?>"
					<?php echo ( '' !== $image ) ? 'poster="' . esc_url( $image ) . '"' : ''; ?>
					autoplay
					muted
					loop
					playsinline
				></video>
			<?php else : ?>
				<img src="<?php echo esc_url( $image ); ?>" alt="" loading="eager">
			<?php endif; ?>
			<span class="hero__scrim" aria-hidden="true"></span>
		</div>
	<?php endif; ?>
	<div class="hero__content">
		<h1 class="hero__title">
			<?php foreach ( $title_lines as $i => $line ) : ?>
				<span class="hero__title-line hero__title-line--<?php echo esc_attr( $line_variants[ $i % count( $line_variants ) ] ); ?>"><?php echo esc_html( $line ); ?></span>
			<?php endforeach; ?>
		</h1>
		<?php if ( '' !== $subtitle ) : ?>
			<p class="hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $description ) : ?>
			<p class="hero__desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $checks ) || ! empty( $buttons ) ) : ?>
			<div class="hero__rule" aria-hidden="true"><span class="hero__rule-mark"></span></div>
		<?php endif; ?>
		<?php if ( ! empty( $checks ) ) : ?>
			<ul class="hero__checks">
				<?php foreach ( $checks as $check ) : ?>
					<li class="hero__check">
						<span class="hero__check-icon" aria-hidden="true"></span>
						<?php echo esc_html( $check ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php if ( ! empty( $buttons ) ) : ?>
			<div class="hero__actions">
				<?php foreach ( $buttons as $btn ) : ?>
					<a class="btn<?php echo $btn['ghost'] ? ' btn--outline' : ''; ?>" href="<?php echo esc_url( RouteService::url( $btn['route'] ) ); ?>">
						<?php if ( '' !== $btn['icon'] ) : ?>
							<span aria-hidden="true"><?php echo esc_html( $btn['icon'] ); ?></span>
						<?php endif; ?>
						<?php echo esc_html( $btn['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
