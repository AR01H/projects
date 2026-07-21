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

	<?php if ( nt_section_visible( 'media_banner' ) ) get_template_part( 'components/media-carousel' ); ?>

	<?php if ( nt_section_visible( 'home_banner' ) ) : ?>
		<?php get_template_part( 'components/home-banner' ); ?>
	<?php endif; ?>

<?php if ( nt_section_visible( 'stats' ) ) :
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
<?php endif; ?>

	<?php if ( nt_section_visible( 'our_story' ) )         get_template_part( 'components/our-story-home' ); ?>
	<?php if ( nt_section_visible( 'our_drinks' ) )        get_template_part( 'components/our-drinks' ); ?>
	<?php if ( nt_section_visible( 'signature_bottles' ) ) get_template_part( 'components/signature-flavours' ); ?>
	<?php if ( nt_section_visible( 'events_catering' ) )   get_template_part( 'components/events-catering' ); ?>
	<?php if ( nt_section_visible( 'reviews' ) )           get_template_part( 'components/reviews' ); ?>
	<?php if ( nt_section_visible( 'photo_carousel' ) )    get_template_part( 'components/photo-carousel' ); ?>
	<?php if ( nt_section_visible( 'faqs' ) )              get_template_part( 'components/faqs' ); ?>
	<?php if ( nt_section_visible( 'contact' ) )           get_template_part( 'components/contact-section' ); ?>

</main>

<?php get_footer(); ?>
