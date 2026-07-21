<?php
/**
 * Franchise Section - Vintage Traditional Layout
 * Matches reference: Hero banner, Why Partner cards, How it works steps, CTA banner.
 */
defined( 'ABSPATH' ) || exit;

// Pull data from franchise JSON
$f_data = NT_Data_Provider::get('franchise') ?: [];

$hero_img     = $f_data['hero_img'] ?? '';
$hero_img_alt = $f_data['hero_img_alt'] ?? '';
$cta_img      = $f_data['cta_img']  ?? '';

$cards = $f_data['cards'] ?? [];
$steps = $f_data['steps'] ?? [];

$hero_title        = $f_data['hero_title'] ?? '';
$hero_subtitle     = $f_data['hero_subtitle'] ?? '';
$stamp_lines       = $f_data['stamp_lines'] ?? [];
$section_why_title = $f_data['section_why_title'] ?? '';
$section_how_title = $f_data['section_how_title'] ?? '';
$cta_title         = $f_data['cta_title'] ?? '';
$cta_subtitle      = $f_data['cta_subtitle'] ?? '';
$cta_btn_text      = $f_data['cta_btn_text'] ?? '';
$cta_btn_link      = $f_data['cta_btn_link'] ?? '';
?>

<!-- ── Hero Banner ── -->
<section class="nt-franchise-hero">
	<div class="container nt-franchise-hero__inner">
		<?php if ( ! empty($stamp_lines) ) : ?>
		<div class="nt-vintage-stamp">
			<span><?php echo wp_kses( $stamp_lines[0] ?? '', ['br'=>[]] ); ?></span>
			<?php if ( isset($stamp_lines[1]) ) : ?><hr><span><?php echo esc_html($stamp_lines[1]); ?></span><?php endif; ?>
			<?php if ( isset($stamp_lines[2]) ) : ?><hr><span><?php echo esc_html($stamp_lines[2]); ?></span><?php endif; ?>
		</div>
		<?php endif; ?>
		<div>
			<h1 class="nt-franchise-hero__title"><?php echo wp_kses( $hero_title, ['br'=>[]] ); ?></h1>
			<p class="nt-franchise-hero__sub"><?php echo wp_kses( $hero_subtitle, ['br'=>[]] ); ?></p>
		</div>
		<div>
			<img src="<?php echo esc_url($hero_img); ?>" alt="<?php echo esc_attr($hero_img_alt); ?>" class="nt-franchise-hero__img" loading="lazy">
		</div>
	</div>
</section>

<!-- ── Why Partner Cards ── -->
<section class="nt-franchise-why">
	<div class="container">
		<h2 class="nt-franchise-why__title"><?php echo esc_html( $section_why_title ); ?></h2>
		<div class="nt-franchise-why__grid">
			<?php foreach ( $cards as $card ) : ?>
				<article class="nt-franchise-card">
					<div class="nt-franchise-card__icon"><?php echo esc_html($card['icon'] ?? ''); ?></div>
					<h3 class="nt-franchise-card__title"><?php echo esc_html($card['title'] ?? ''); ?></h3>
					<p class="nt-franchise-card__desc"><?php echo esc_html($card['desc'] ?? ''); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ── How It Works Steps ── -->
<section class="nt-franchise-how">
	<div class="container">
		<h2 class="nt-franchise-why__title"><?php echo esc_html( $section_how_title ); ?></h2>
		<div class="nt-franchise-how__grid">
			<?php 
			$step_num = 1;
			foreach ( $steps as $step ) : ?>
				<div class="nt-franchise-step">
					<div class="nt-franchise-step__num"><?php echo esc_html($step_num); ?></div>
					<h3 class="nt-franchise-step__title"><?php echo esc_html($step['title'] ?? ''); ?></h3>
					<p class="nt-franchise-step__desc"><?php echo esc_html($step['desc'] ?? ''); ?></p>
				</div>
			<?php 
			$step_num++;
			endforeach; ?>
		</div>
	</div>
</section>

<!-- ── Bottom CTA ── -->
<section class="nt-franchise-cta">
	<div class="nt-franchise-cta__bg" style="background-image: url('<?php echo esc_url($cta_img); ?>');"></div>
	<div class="container">
		<div class="nt-franchise-cta__inner">
			<h2 class="nt-franchise-cta__title"><?php echo wp_kses( $cta_title, ['br'=>[]] ); ?></h2>
			<p class="nt-franchise-cta__sub"><?php echo esc_html( $cta_subtitle ); ?></p>
			<button type="button" class="btn" data-nt-open="nt-franchise-modal">
				<?php echo wp_kses( $cta_btn_text, ['rarr'=>[]] ); ?>
			</button>
		</div>
	</div>
</section>

<?php
get_template_part( 'components/parts/form-modal', null, array(
	'id'     => 'nt-franchise-modal',
	'title'  => __( 'Franchise Enquiry 💼', NT_TEXT_DOMAIN ),
	'sub'    => __( 'Tell us a little about you - we reply within 24 hours.', NT_TEXT_DOMAIN ),
	'config' => nt_data( 'form_franchise' ),
) );
?>
