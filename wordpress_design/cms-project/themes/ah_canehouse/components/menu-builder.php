<?php
defined( 'ABSPATH' ) || exit;
$_d           = CH_Shared_Data::section_heading( 'menu_builder' );
$sizes        = ch_get_menu_sizes();
$cane_types   = ch_get_cane_types();
$textures     = ch_get_textures();
$flavours     = ch_get_flavours();
$show_prices  = ch_show_prices();
?>

<section id="build" class="ch-build-section">
	<?php get_template_part( 'components/section-header', null, [
		'tag'           => $_d['tag']   ?? '',
		'title'         => $_d['title'] ?? '',
		'body'          => $_d['body']  ?? '',
		'wrapper_class' => 'ch-build__header',
	] ); ?>

	<div class="ch-build-grid">

		<!-- SIZES -->
		<!-- <div class="ch-option-card fade-left">
			<div class="ch-option-header">
				<div class="ch-option-num">1</div>
				<div>
					<div class="ch-option-title">Size</div>
					<div class="ch-option-sub">Pick your cup size</div>
				</div>
			</div>
			<div class="ch-price-rows">
				<?php foreach ( $sizes as $size ) :
					$size     = (array) $size;
					$featured = ! empty( $size['featured'] );
				?>
					<div class="ch-price-row<?php echo $featured ? ' ch-price-row--featured' : ''; ?>">
						<div class="ch-row-left">
							<div class="ch-row-icon"><?php echo esc_html( $size['icon'] ?? '🥤' ); ?></div>
							<div>
								<div class="ch-row-name"><?php echo esc_html( $size['name'] ?? '' ); ?></div>
								<div class="ch-row-desc"><?php echo esc_html( $size['desc'] ?? '' ); ?></div>
							</div>
						</div>
						<div class="ch-row-right">
							<?php if ( ! empty( $size['badge'] ) ) : ?>
								<span class="ch-row-badge"><?php echo esc_html( $size['badge'] ); ?></span>
							<?php endif; ?>
							<?php if ( $show_prices && ! empty( $size['price'] ) ) : ?>
								<div class="ch-row-price"><?php echo esc_html( $size['price'] ); ?></div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div> -->

		<!-- CANE TYPE + TEXTURE -->
		<div class="ch-option-card fade-right">
			<div class="ch-option-header">
				<!-- <div class="ch-option-num"></div> -->
				<div>
					<div class="ch-option-title">Cane Type</div>
					<div class="ch-option-sub">Choose your cane</div>
				</div>
			</div>
			<div class="ch-price-rows" style="margin-bottom:2rem;">
				<?php foreach ( $cane_types as $cane ) :
					$cane     = (array) $cane;
					$featured = ! empty( $cane['featured'] );
				?>
					<div class="ch-price-row<?php echo $featured ? ' ch-price-row--featured' : ''; ?>">
						<div class="ch-row-left">
							<div class="ch-row-icon"><?php echo esc_html( $cane['icon'] ?? '🌾' ); ?></div>
							<div>
								<div class="ch-row-name"><?php echo esc_html( $cane['name'] ?? '' ); ?></div>
								<div class="ch-row-desc"><?php echo esc_html( $cane['desc'] ?? '' ); ?></div>
							</div>
						</div>
						<div class="ch-row-right">
							<?php if ( ! empty( $cane['badge'] ) ) : ?>
								<!-- <span class="ch-row-badge"><?php echo esc_html( $cane['badge'] ); ?></span> -->
							<?php endif; ?>
							<?php if ( $show_prices && ! empty( $cane['price'] ) ) : ?>
								<div class="ch-row-price"><?php echo esc_html( $cane['price'] ); ?></div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="ch-option-card fade-left" >
			<div class="ch-option-header" style="margin-top:.5rem;">
				<!-- <div class="ch-option-num"></div> -->
				<div>
					<div class="ch-option-title">Texture</div>
					<div class="ch-option-sub">How it's pressed</div>
				</div>
			</div>
			<div class="ch-price-rows">
				<?php foreach ( $textures as $tex ) :
					$tex      = (array) $tex;
					$featured = ! empty( $tex['featured'] );
				?>
					<div class="ch-price-row<?php echo $featured ? ' ch-price-row--featured' : ''; ?>">
						<div class="ch-row-left">
							<div class="ch-row-icon"><?php echo esc_html( $tex['icon'] ?? '🥢' ); ?></div>
							<div>
								<div class="ch-row-name"><?php echo esc_html( $tex['name'] ?? '' ); ?></div>
								<div class="ch-row-desc"><?php echo esc_html( $tex['desc'] ?? '' ); ?></div>
							</div>
						</div>
						<div class="ch-row-right">
							<?php if ( ! empty( $tex['badge'] ) ) : ?>
								<!-- <span class="ch-row-badge"><?php echo esc_html( $tex['badge'] ); ?></span> -->
							<?php endif; ?>
							<?php if ( $show_prices && ! empty( $tex['price'] ) ) : ?>
								<div class="ch-row-price"><?php echo esc_html( $tex['price'] ); ?></div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- FLAVOURS -->
		<div class="ch-option-card fade-up ch-option-card--full">
			<div class="ch-option-header">
				<!-- <div class="ch-option-num"></div> -->
				<div>
					<div class="ch-option-title">Flavour</div>
					<div class="ch-option-sub">Pick your blend</div>
				</div>
			</div>
			<div class="ch-flavour-grid">
				<?php foreach ( $flavours as $fl ) :
					$fl = (array) $fl;
				?>
					<div class="ch-flavour-chip">
						<span class="ch-chip-emoji ch-shaking-leaf"><?php echo esc_html( $fl['emoji'] ?? '🌿' ); ?></span>
						<div class="ch-chip-name"><?php echo esc_html( $fl['name'] ?? '' ); ?></div>
						<?php if ( $show_prices ) : ?>
							<div class="ch-chip-price"><?php echo esc_html( $fl['desc'] ?? '' ); ?></div>
						<?php else : ?>
							<div class="ch-chip-price"><?php echo esc_html( $fl['type'] ?? '' ); ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			<p class="ch-build__disclaimer">
				* As different cane types are freshly pressed in a shared machine, slight variation in colour and taste may occur. Contains natural sugars - please consume responsibly.
			</p>
		</div>

	</div>
</section>
