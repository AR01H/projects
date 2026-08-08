<?php
defined( 'ABSPATH' ) || exit;

$_d        = App_Helpers::data( 'flavours' ) ?: [];
$types     = $_d['caneTypes'] ?? [];
$textures  = $_d['textures'] ?? [];
$flavours  = $_d['flavours'] ?? [];
?>

<section id="build" class="app-build-section section">
	<div class="container wrapper">
		<?php get_template_part( 'components/parts/section-header', null, [
			'tag'           => 'OUR MENU',
			'title'         => 'Build Your Perfect Drink',
			'body'          => 'Choose your base, texture, and flavour.',
			'wrapper_class' => 'app-build__header',
		] ); ?>

		<div class="app-build-grid grid">

			<!-- BASE TYPE -->
			<div class="app-option-card card fade-right">
				<div class="app-option-header">
					<div>
						<div class="app-option-title">Base Type</div>
						<div class="app-option-sub">Choose your base</div>
					</div>
				</div>
				<div class="app-price-rows" style="margin-bottom:2rem;">
					<?php foreach ( $types as $type ) :
						$type     = (array) $type;
						$featured = ! empty( $type['featured'] );
					?>
						<div class="app-price-row<?php echo $featured ? ' app-price-row--featured' : ''; ?>">
							<div class="app-row-left">
								<div class="app-row-icon"><?php echo esc_html( $type['icon'] ?? '🌾' ); ?></div>
								<div>
									<div class="app-row-name"><?php echo esc_html( $type['name'] ?? '' ); ?></div>
									<div class="app-row-desc"><?php echo esc_html( $type['desc'] ?? '' ); ?></div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- TEXTURE -->
			<div class="app-option-card card fade-left" >
				<div class="app-option-header" style="margin-top:.5rem;">
					<div>
						<div class="app-option-title">Texture</div>
						<div class="app-option-sub">How it's prepared</div>
					</div>
				</div>
				<div class="app-price-rows">
					<?php foreach ( $textures as $tex ) :
						$tex      = (array) $tex;
						$featured = ! empty( $tex['featured'] );
					?>
						<div class="app-price-row<?php echo $featured ? ' app-price-row--featured' : ''; ?>">
							<div class="app-row-left">
								<div class="app-row-icon"><?php echo esc_html( $tex['icon'] ?? '🥢' ); ?></div>
								<div>
									<div class="app-row-name"><?php echo esc_html( $tex['name'] ?? '' ); ?></div>
									<div class="app-row-desc"><?php echo esc_html( $tex['desc'] ?? '' ); ?></div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- FLAVOURS -->
			<div class="app-option-card card fade-up app-option-card--full">
				<div class="app-option-header">
					<div>
						<div class="app-option-title">Flavour</div>
						<div class="app-option-sub">Pick your blend</div>
					</div>
				</div>
				<div class="app-flavour-grid grid">
					<?php foreach ( $flavours as $fl ) :
						$fl = (array) $fl;
					?>
						<div class="app-flavour-chip feature">
							<span class="app-chip-emoji"><?php echo esc_html( $fl['emoji'] ?? '🌿' ); ?></span>
							<div class="app-chip-name"><?php echo esc_html( $fl['name'] ?? '' ); ?></div>
							<div class="app-chip-price"><?php echo esc_html( $fl['type'] ?? '' ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

		</div>
	</div>
</section>
