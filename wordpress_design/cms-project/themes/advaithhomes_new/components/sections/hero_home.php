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
                } else {
                    echo esc_html( $text );
                }
                if ( $i < $line_count - 1 ) {
                    echo '<br>';
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
                   class="btn <?php echo esc_attr( $style ); ?> btn-lg"><?php echo esc_html( isset( $action['label'] ) ? $action['label'] : '' ); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="hero-visual">
        <div class="hero-process-diagram">
            <div class="process-circle"></div>
            <div class="process-center">
                <span class="process-center-icon"><?php echo adn_icon( isset( $diagram['center_icon'] ) ? $diagram['center_icon'] : '' ); ?></span>
                <span class="process-center-text"><?php
                    $center_lines = isset( $diagram['center_lines'] ) ? (array) $diagram['center_lines'] : array();
                    $first        = true;
                    foreach ( $center_lines as $center_line ) {
                        if ( ! $first ) {
                            echo '<br>';
                        }
                        echo esc_html( $center_line );
                        $first = false;
                    }
                ?></span>
            </div>
            <div class="process-nodes">
                <?php foreach ( array_values( $nodes ) as $i => $node ) : ?>
                    <div class="process-node node-<?php echo esc_attr( (string) ( $i + 1 ) ); ?>">
                        <div class="process-node-icon"><?php echo adn_icon( isset( $node['icon'] ) ? $node['icon'] : '' ); ?></div>
                        <div class="process-node-label"><?php echo esc_html( isset( $node['label'] ) ? $node['label'] : '' ); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
