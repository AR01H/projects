<?php
/**
 * components/parts/main_footer.php - Component: Site Footer
 *
 * Props: $footer (from adn_service_site_chrome()['footer'])
 *        { brand, social[], columns[], copyright, made_with, bottom_links[], disclaimer }
 * Usage: adn_component( 'parts/main_footer', array( 'footer' => $ctx['chrome']['footer'] ) );
 */

defined( 'ABSPATH' ) || exit;

$footer = isset( $footer ) && is_array( $footer ) ? $footer : array();

$brand        = isset( $footer['brand'] ) ? (array) $footer['brand'] : array();
$social       = isset( $footer['social'] ) ? (array) $footer['social'] : array();
$columns      = isset( $footer['columns'] ) ? (array) $footer['columns'] : array();
$bottom_links = isset( $footer['bottom_links'] ) ? (array) $footer['bottom_links'] : array();
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand">
                <div class="footer-logo">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo">
                        <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logos/logo_with_text.png' ); ?>" alt="<?php echo esc_attr( defined( 'COMPANY_NAME' ) ? COMPANY_NAME : '' ); ?>" width="200" />
                    </a>
                </div>
                <p class="footer-desc"><?php echo esc_html( isset( $brand['description'] ) ? $brand['description'] : '' ); ?></p>
                <?php
                $_ft_phone   = defined( 'COMPANY_PHONE_NO' ) ? COMPANY_PHONE_NO : '';
                $_ft_email   = defined( 'COMPANY_EMAIL' ) ? COMPANY_EMAIL : '';
                $_ft_address = function_exists( 'adn_get_contact_setting' ) ? adn_get_contact_setting( 'address' ) : '';
                if ( $_ft_phone || $_ft_email || $_ft_address ) : ?>
                <ul class="footer-contact-list">
                    <?php if ( $_ft_phone ) : ?>
                    <li><a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $_ft_phone ) ); ?>" class="footer-contact-item"><i class="fa-solid fa-phone" aria-hidden="true"></i> <?php echo esc_html( $_ft_phone ); ?></a></li>
                    <?php endif; ?>
                    <?php if ( $_ft_email ) : ?>
                    <li><a href="mailto:<?php echo esc_attr( $_ft_email ); ?>" class="footer-contact-item"><i class="fa-solid fa-envelope" aria-hidden="true"></i> <?php echo esc_html( $_ft_email ); ?></a></li>
                    <?php endif; ?>
                    <?php if ( $_ft_address ) : ?>
                    <li><span class="footer-contact-item"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?php echo esc_html( $_ft_address ); ?></span></li>
                    <?php endif; ?>
                </ul>
                <?php endif; ?>
                <div class="footer-social">
                    <?php foreach ( $social as $item ) : ?>
                        <a href="<?php echo esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) ); ?>"
                           class="social-btn"
                           aria-label="<?php echo esc_attr( isset( $item['label'] ) ? $item['label'] : '' ); ?>"><?php echo adn_icon( isset( $item['icon'] ) ? $item['icon'] : '' ); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php foreach ( $columns as $column ) : ?>
                <div class="footer-col">
                    <h4><?php echo esc_html( isset( $column['title'] ) ? $column['title'] : '' ); ?></h4>
                    <div class="footer-links">
                        <?php foreach ( (array) ( isset( $column['links'] ) ? $column['links'] : array() ) as $link ) : ?>
                            <a href="<?php echo esc_url( adn_link( isset( $link['url'] ) ? $link['url'] : '' ) ); ?>" class="footer-link"><?php echo esc_html( isset( $link['label'] ) ? $link['label'] : '' ); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="footer-bottom">
            <span><?php echo esc_html( isset( $footer['copyright'] ) ? $footer['copyright'] : '' ); ?></span>
            <div class="footer-bottom-links">
                <?php foreach ( $bottom_links as $link ) : ?>
                    <a href="<?php echo esc_url( adn_link( isset( $link['url'] ) ? $link['url'] : '' ) ); ?>" class="footer-link"><?php echo esc_html( isset( $link['label'] ) ? $link['label'] : '' ); ?></a>
                <?php endforeach; ?>
            </div>
            <span><?php echo esc_html( isset( $footer['made_with'] ) ? $footer['made_with'] : '' ); ?></span>
        </div>
    </div>

    <?php if ( ! empty( $footer['disclaimer'] ) ) : ?>
        <div class="footer-disclaimer">
            <div class="container"><?php echo esc_html( $footer['disclaimer'] ); ?></div>
        </div>
    <?php endif; ?>
</footer>
