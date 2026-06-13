<?php
/**
 * components/cards/regulation_item.php - Component: Regulation List Item
 * Props: $item { badge_lines[], title, date, url }
 */

defined( 'ABSPATH' ) || exit;

$item        = isset( $item ) && is_array( $item ) ? $item : array();
$badge_lines = isset( $item['badge_lines'] ) ? (array) $item['badge_lines'] : array();
?>
<a href="<?php echo esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) ); ?>" class="regulation-item">
    <div class="gov-badge"><?php
        $first = true;
        foreach ( $badge_lines as $line ) {
            if ( ! $first ) {
                echo '<br>';
            }
            echo esc_html( $line );
            $first = false;
        }
    ?></div>
    <div class="regulation-content">
        <div class="card-title-highlight"><?php echo esc_html( isset( $item['title'] ) ? $item['title'] : '' ); ?></div>
        <div class="card-desc-text"><?php echo esc_html( isset( $item['date'] ) ? $item['date'] : '' ); ?></div>
    </div>
</a>
