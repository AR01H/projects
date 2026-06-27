<?php
/**
 * components/sections/hero_home.php - Section: Home Hero
 *
 * Props: $hero { title_lines[], description, actions[], trust_items[], diagram }
 *   title_lines : [ { text, accent(bool) } ]   rendered with <br> between lines
 *   actions     : [ { label, url, style: primary|outline } ]
 *   diagram     : { center_icon, center_lines[], nodes: [ { icon, label } ] }
 *
 * Usage: adn_component( 'sections/hero_home', array( 'hero' => $ctx['hero'] ) );
 */

defined( 'ABSPATH' ) || exit;

$hero = isset( $hero ) && is_array( $hero ) ? $hero : array();

$title_lines = isset( $hero['title_lines'] ) ? (array) $hero['title_lines'] : array();
$actions     = isset( $hero['actions'] ) ? (array) $hero['actions'] : array();
$trust_items = isset( $hero['trust_items'] ) ? (array) $hero['trust_items'] : array();
$diagram     = isset( $hero['diagram'] ) ? (array) $hero['diagram'] : array();
$nodes       = isset( $diagram['nodes'] ) ? (array) $diagram['nodes'] : array();
$_default_img = get_template_directory_uri() . '/assets/images/backgrounds/home_hero.jpg';

$_hero_img    = get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: $_default_img;

?>
<?php adn_component( 'sections/page_hero_bg_banner', array( 'hero_img' => $_hero_img ) ); ?>

<div class="hero-home-inner">
    <div class="hero-content">
        <h1 class="hero-title">
            <?php
            $line_count = count( $title_lines );
            foreach ( array_values( $title_lines ) as $i => $line ) {
                $text = isset( $line['text'] ) ? $line['text'] : '';
                if ( ! empty( $line['accent'] ) ) {
                    echo '<span class="accent">' . esc_html( $text ) . '</span>';
                } else if($i==2) {
                    echo '<h2 class="hero-sub-line">'.esc_html( $text ).'</h2>';
                }else{
                    echo esc_html( $text );
                }
                if ( $i < $line_count - 1 ) {
                    // echo '<br>';
                }
            }
            ?>
        </h1>
        <p class="hero-desc"><?php echo esc_html( isset( $hero['description'] ) ? $hero['description'] : '' ); ?></p>
        <div class="hero-actions">
            <?php foreach ( $actions as $action ) :
                $style = isset( $action['style'] ) && 'outline' === $action['style'] ? 'btn-outline' : 'btn-primary';
                ?>
                <a href="<?php echo esc_url( adn_link( isset( $action['url'] ) ? $action['url'] : '' ) ); ?>"
                   class="btn <?php echo esc_attr( $style ); ?> btn-md"><?php echo esc_html( isset( $action['label'] ) ? $action['label'] : '' ); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="hero-visual hero-visual--in-hero">
        <?php adn_component( 'sections/hero_home_diagram', array( 'diagram' => $diagram ) ); ?>
    </div>
</div>
