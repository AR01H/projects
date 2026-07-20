<?php
/**
 * Template Name: Home
 *
 * The main homepage pulling in all vintage-style json-driven components.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="site-main nt-trad-home" id="main-content">

	<?php get_template_part( 'components/home-banner' ); ?>

<?php
// Stats Bar – dynamic data from stats.json
$stats = NT_Data_Provider::get('stats') ?: [];
$icons = [
    'farms'    => '🌾',
    'glasses'  => '🥤',
    'outlets'  => '📍',
    'years'    => '🏆',
];
?>
<div class="nt-stats-bar">
    <div class="nt-stats-bar__inner container">
        <?php foreach ( $stats as $key => $value ) :
            $icon = $icons[ $key ] ?? '';
            // Format number display – add '+' for farms/outlets/years, 'M+' for glasses
            $display = is_int( $value ) ? number_format( $value ) : $value;
            if ( $key === 'glasses' ) {
                $display = number_format( $value / 1000000 ) . 'M+';
            } else {
                $display = $display . '+';
            }
            $label = ucfirst( $key );
        ?>
            <div class="nt-stats-bar__item">
                <span class="nt-stats-bar__icon"><?php echo esc_html( $icon ); ?></span>
                <div>
                    <div class="nt-stats-bar__num"><?php echo esc_html( $display ); ?></div>
                    <div class="nt-stats-bar__label"><?php echo esc_html( $label ); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

	<?php get_template_part( 'components/our-story-home' ); ?>
	<?php get_template_part( 'components/our-drinks' ); ?>
	<?php get_template_part( 'components/events-catering' ); ?>
	<?php get_template_part( 'components/photo-carousel' ); ?>
	<?php get_template_part( 'components/contact-section' ); ?>

</main>

<?php get_footer(); ?>
