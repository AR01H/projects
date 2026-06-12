<?php
/**
 * components/sections/news_three_col.php — Section: News + Regulations + Hot Topics
 *
 * Props:
 *   $news        { heading, items[] }   (news_item cards)
 *   $regulations { heading, items[] }   (regulation_item cards)
 *   $hot_topics  { title, items[], cta } (hot_topic_item cards)
 *
 * Usage:
 *   adn_component( 'sections/news_three_col', array(
 *       'news'        => $ctx['news'],
 *       'regulations' => $ctx['regulations'],
 *       'hot_topics'  => $ctx['hot_topics'],
 *   ) );
 */

defined( 'ABSPATH' ) || exit;

$news        = isset( $news ) && is_array( $news ) ? $news : array();
$regulations = isset( $regulations ) && is_array( $regulations ) ? $regulations : array();
$hot_topics  = isset( $hot_topics ) && is_array( $hot_topics ) ? $hot_topics : array();

$hot_cta = isset( $hot_topics['cta'] ) ? (array) $hot_topics['cta'] : array();
?>
<div class="news-three-inner">

    <div class="news-col news-col--news mini_card_container_design">
        <?php
        adn_component( 'parts/section_headers/section_header', array(
            'heading'       => isset( $news['heading'] ) ? $news['heading'] : array(),
            'tag'           => 'h3',
            'wrapper_class' => 'news-col-title',
        ) );
        foreach ( (array) ( isset( $news['items'] ) ? $news['items'] : array() ) as $item ) {
            adn_component( 'cards/news_item', array( 'item' => $item ) );
        }
        ?>
    </div>

    <div class="news-col news-col--regulations mini_card_container_design">
        <?php
        adn_component( 'parts/section_headers/section_header', array(
            'heading'       => isset( $regulations['heading'] ) ? $regulations['heading'] : array(),
            'tag'           => 'h3',
            'wrapper_class' => 'news-col-title',
        ) );
        foreach ( (array) ( isset( $regulations['items'] ) ? $regulations['items'] : array() ) as $item ) {
            adn_component( 'cards/regulation_item', array( 'item' => $item ) );
        }
        ?>
    </div>

    <div class="hot-topics-col mini_card_container_design">
        <div class="hot-topics-title"><?php echo esc_html( isset( $hot_topics['title'] ) ? $hot_topics['title'] : '' ); ?></div>
        <?php
        foreach ( (array) ( isset( $hot_topics['items'] ) ? $hot_topics['items'] : array() ) as $item ) {
            adn_component( 'cards/hot_topic_item', array( 'item' => $item ) );
        }
        ?>
        <?php if ( ! empty( $hot_cta['label'] ) ) : ?>
            <a href="<?php echo esc_url( adn_link( isset( $hot_cta['url'] ) ? $hot_cta['url'] : '' ) ); ?>" class="btn btn-outline btn-sm hot-topics-cta"><?php echo esc_html( $hot_cta['label'] ); ?></a>
        <?php endif; ?>
    </div>

</div>
