<?php
defined( 'ABSPATH' ) || exit;

$_d        = nt_data( 'flavours' ) ?: [];
$types     = $_d['caneTypes'] ?? [];
$textures  = $_d['textures'] ?? [];
$flavours  = $_d['flavours'] ?? [];
?>

<section id="build" class="nt-build-section section">
	<div class="container wrapper">
		<?php get_template_part( 'components/parts/section-header', null, [
			'tag'           => 'OUR MENU',
			'title'         => 'Build Your Perfect Drink',
			'body'          => 'Choose your base, texture, and flavour.',
			'wrapper_class' => 'nt-build__header',
		] ); ?>

		<div class="nt-build-grid grid">

			<!-- BASE TYPE -->
			<div class="nt-option-card card fade-right">
				<div class="nt-option-header">
					<div>
						<div class="nt-option-title">Base Type</div>
						<div class="nt-option-sub">Choose your base</div>
					</div>
				</div>
				<div class="nt-price-rows" style="margin-bottom:2rem;">
					<?php foreach ( $types as $type ) :
						$type     = (array) $type;
						$featured = ! empty( $type['featured'] );
					?>
						<div class="nt-price-row<?php echo $featured ? ' nt-price-row--featured' : ''; ?>">
							<div class="nt-row-left">
								<div class="nt-row-icon"><?php echo esc_html( $type['icon'] ?? '🌾' ); ?></div>
								<div>
									<div class="nt-row-name"><?php echo esc_html( $type['name'] ?? '' ); ?></div>
									<div class="nt-row-desc"><?php echo esc_html( $type['desc'] ?? '' ); ?></div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- TEXTURE -->
			<div class="nt-option-card card fade-left" >
				<div class="nt-option-header" style="margin-top:.5rem;">
					<div>
						<div class="nt-option-title">Texture</div>
						<div class="nt-option-sub">How it's prepared</div>
					</div>
				</div>
				<div class="nt-price-rows">
					<?php foreach ( $textures as $tex ) :
						$tex      = (array) $tex;
						$featured = ! empty( $tex['featured'] );
					?>
						<div class="nt-price-row<?php echo $featured ? ' nt-price-row--featured' : ''; ?>">
							<div class="nt-row-left">
								<div class="nt-row-icon"><?php echo esc_html( $tex['icon'] ?? '🥢' ); ?></div>
								<div>
									<div class="nt-row-name"><?php echo esc_html( $tex['name'] ?? '' ); ?></div>
									<div class="nt-row-desc"><?php echo esc_html( $tex['desc'] ?? '' ); ?></div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- FLAVOURS -->
			<div class="nt-option-card card fade-up nt-option-card--full">
				<div class="nt-option-header">
					<div>
						<div class="nt-option-title">Flavour</div>
						<div class="nt-option-sub">Pick your blend</div>
					</div>
				</div>
				<div class="nt-flavour-grid grid">
					<?php foreach ( $flavours as $fl ) :
						$fl = (array) $fl;
					?>
						<div class="nt-flavour-chip feature">
							<span class="nt-chip-emoji"><?php echo esc_html( $fl['emoji'] ?? '🌿' ); ?></span>
							<div class="nt-chip-name"><?php echo esc_html( $fl['name'] ?? '' ); ?></div>
							<div class="nt-chip-price"><?php echo esc_html( $fl['type'] ?? '' ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

		</div>
	</div>
</section>
