<?php
defined( 'ABSPATH' ) || exit;
$_d           = CH_Shared_Data::section_heading( 'menu_builder' );
$sizes        = ch_get_menu_sizes();
$cane_types   = ch_get_cane_types();
$textures     = ch_get_textures();
$flavours     = ch_get_flavours();
?>

<section id="build" class="ch-build-section">
	<?php get_template_part( 'components/section-header', null, [
		'tag'           => $_d['tag']   ?? '',
		'title'         => $_d['title'] ?? '',
		'body'          => $_d['body']  ?? '',
		'wrapper_class' => 'ch-build__header',
	] ); ?>

	<div class="ch-build-grid">

		<!-- CANE TYPE + TEXTURE -->
		<div class="ch-option-card fade-right">
			<div class="ch-option-header">
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
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="ch-option-card fade-left" >
			<div class="ch-option-header" style="margin-top:.5rem;">
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
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- FLAVOURS -->
		<div class="ch-option-card fade-up ch-option-card--full">
			<div class="ch-option-header">
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
						<div class="ch-chip-price"><?php echo esc_html( $fl['type'] ?? '' ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
			<p class="ch-build__disclaimer">
				* As different cane types are freshly pressed in a shared machine, slight variation in colour and taste may occur. Contains natural sugars - please consume responsibly.
			</p>
		</div>

	</div>
</section>
