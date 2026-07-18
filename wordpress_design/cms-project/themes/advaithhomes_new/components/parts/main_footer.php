<?php
/**
 * components/parts/main_footer.php - Component: Site Footer
 *
 * Props: $footer (from adn_service_site_chrome()['footer'])
 *        { brand, social[], columns[], copyright, made_with, bottom_links[], disclaimer }
 * Usage: adn_component( 'parts/main_footer', array( 'footer' => $ctx['chrome']['footer'] ) );
 */

defined( 'ABSPATH' ) || exit;

$footer       = isset( $footer ) && is_array( $footer ) ? $footer : array();
$brand        = isset( $footer['brand'] ) ? (array) $footer['brand'] : array();
$social       = isset( $footer['social'] ) ? (array) $footer['social'] : array();
$columns      = isset( $footer['columns'] ) ? (array) $footer['columns'] : array();
$bottom_links = isset( $footer['bottom_links'] ) ? (array) $footer['bottom_links'] : array();
$nl_nonce     = wp_create_nonce( 'ah_newsletter_nonce' );
?>
<?php 
$raw_settings = function_exists( 'adn_chrome_option' ) ? adn_chrome_option( 'ah_cms_footer' ) : array();
$show_desc    = ! empty( $raw_settings['brand_description'] );
$show_badge   = ! empty( $raw_settings['badge_text'] );
$show_cta     = ! empty( $raw_settings['cta'] ) && ! empty( $raw_settings['cta']['label'] );

if ( $show_desc || $show_badge || $show_cta ) : 
?>
<div class="footer-pre-banner" style="background: var(--client-color, #203c3e); border-bottom: 1px solid rgba(255,255,255,0.1); padding: 2px; color: #ffffff;">
    <div class="">
        <style>
            .footer-pre-inner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 24px;
                width: 100%;
            }
            .footer-pre-content-inline {
                display: flex;
                align-items: center;
                gap: 16px;
                flex: 1;
            }
            .footer-pre-desc {
                font-family: var(--font-accent, var(--font-display, serif));
                font-size: 16px;
                font-weight: 400;
                font-style: italic;
                color: rgba(255, 255, 255, 0.95);
                margin: 0;
                line-height: 1.5;
            }
            .footer-pre-badge-wrapper, .footer-pre-btn-wrapper {
                flex-shrink: 0;
            }
            @media (max-width: 768px) {
                .footer-pre-inner {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    text-align: center;
                    gap: 16px;
                    padding: 8px 0;
                }
                .footer-pre-content-inline {
                    flex-direction: column;
                    gap: 12px;
                    flex: none;
                    width: 100%;
                }
            }
        </style>
        <div class="footer-pre-inner">
            <div class="footer-pre-content-inline">
                <?php if ( $show_badge ) : ?>
                    <div class="footer-pre-badge-wrapper">
                        <span class="badge badge-gold" style="text-transform: uppercase; font-weight: 600; padding: 4px 10px; border-radius: 4px;"><?php echo esc_html( $footer['badge_text'] ); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ( $show_desc ) : ?>
                    <p class="footer-pre-desc"><?php echo esc_html( $brand['description'] ); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if ( $show_cta ) : ?>
                <div class="footer-pre-btn-wrapper">
                    <a href="<?php echo esc_url( adn_link( $footer['cta']['url'] ) ); ?>" class="btn btn-accent btn-sm" style="padding: 10px 20px; font-size: 13px;"><?php echo esc_html( $footer['cta']['label'] ); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<footer class="site-footer">

    <?php /* ── Top bar: logo + newsletter ── */ ?>
    <div class="footer-topbar">
        <div class="container">
            <div class="footer-topbar-inner">

                <div class="footer-topbar-logo">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo">
                        <img src="<?php echo esc_url( adn_versioned_url( get_template_directory_uri() . '/assets/images/logos/logo_with_text.png' ) ); ?>"
                             alt="<?php echo esc_attr( defined( 'COMPANY_NAME' ) ? COMPANY_NAME : '' ); ?>"
                             width="160" />
                    </a>
                </div>

                <div class="footer-topbar-nl">
                    <div class="footer-topbar-nl-text">
                        <strong><?php echo esc_html( defined( 'SITE_NEWSLETTER_TITLE' ) ? SITE_NEWSLETTER_TITLE : 'Stay Informed, Stay Ahead' ); ?></strong>
                        <span><?php echo esc_html( defined( 'SITE_NEWSLETTER_DESC' ) ? SITE_NEWSLETTER_DESC : 'The latest property news, guides and expert insights' ); ?></span>
                    </div>
                    <form class="footer-nl-form adn-nl-form" onsubmit="return false;"
                          data-nonce="<?php echo esc_attr( $nl_nonce ); ?>"
                          data-ajaxurl="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
                        <div class="footer-nl-row">
                            <input type="email" name="nl_email" class="adn-nl-email"
                                   placeholder="<?php echo esc_attr( defined( 'SITE_NEWSLETTER_PH' ) ? SITE_NEWSLETTER_PH : 'Add your email here' ); ?>"
                                   aria-label="Email address" required />
                            <button type="submit" class="adn-nl-btn footer-nl-btn">
                                <?php echo esc_html( defined( 'SITE_BTN_SUBSCRIBE' ) ? SITE_BTN_SUBSCRIBE : 'Subscribe' ); ?>
                            </button>
                        </div>
                        <div class="adn-nl-msg" style="display:none;margin-top:8px;font-size:13px;font-weight:500"></div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <?php /* ── Nav columns ── */ ?>
    <div class="footer-main">
        <div class="container">
            <div class="footer-cols-grid">
                <?php foreach ( $columns as $column ) : ?>
                    <div class="footer-col">
                        <h4><?php echo esc_html( isset( $column['title'] ) ? $column['title'] : '' ); ?></h4>
                        <div class="footer-links">
                            <?php foreach ( (array) ( isset( $column['links'] ) ? $column['links'] : array() ) as $link ) : ?>
                                <a href="<?php echo esc_url( adn_link( isset( $link['url'] ) ? $link['url'] : '' ) ); ?>"
                                   class="footer-link<?php echo ! empty( $link['highlight'] ) ? ' nav-link--highlight' : ''; ?>"><?php echo esc_html( isset( $link['label'] ) ? $link['label'] : '' ); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php /* ── Bottom bar ── */ ?>
    <div class="footer-bottom-bar">
        <div class="container">
            <div class="footer-bottom">
                <span><?php echo esc_html( isset( $footer['copyright'] ) ? $footer['copyright'] : '' ); ?></span>
                <div class="footer-bottom-links">
                    <?php foreach ( $bottom_links as $link ) : ?>
                        <a href="<?php echo esc_url( adn_link( isset( $link['url'] ) ? $link['url'] : '' ) ); ?>"
                           class="footer-link"><?php echo esc_html( isset( $link['label'] ) ? $link['label'] : '' ); ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="footer-bottom-right">
                    <?php if ( ! empty( $footer['made_with'] ) ) : ?>
                        <span class="footer-made-with"><?php echo esc_html( $footer['made_with'] ); ?></span>
                    <?php endif; ?>
                    <div class="footer-social">
                        <?php foreach ( $social as $item ) : ?>
                            <a href="<?php echo esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) ); ?>"
                               class="social-btn"
                               aria-label="<?php echo esc_attr( isset( $item['label'] ) ? $item['label'] : '' ); ?>"><?php echo adn_icon( isset( $item['icon'] ) ? $item['icon'] : '' ); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ( ! empty( $footer['disclaimer'] ) ) : ?>
        <div class="footer-disclaimer">
            <div class="container"><?php echo esc_html( $footer['disclaimer'] ); ?></div>
        </div>
    <?php endif; ?>

<script>
(function () {
    var form = document.querySelector('.footer-nl-form');
    if ( ! form ) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var email = form.querySelector('.adn-nl-email').value.trim();
        var btn   = form.querySelector('.footer-nl-btn');
        var msg   = form.querySelector('.adn-nl-msg');
        if ( ! email ) return;
        btn.disabled = true;
        var orig = btn.textContent;
        btn.textContent = '...';
        var fd = new FormData();
        fd.append('action', 'ah_newsletter_subscribe');
        fd.append('nonce',  form.dataset.nonce);
        fd.append('email',  email);
        fd.append('source', 'footer');
        fetch(form.dataset.ajaxurl, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                btn.disabled = false;
                btn.textContent = orig;
                if (msg) {
                    msg.style.display  = 'block';
                    msg.textContent    = res.data && res.data.message ? res.data.message : (res.success ? 'Thank you!' : 'Something went wrong.');
                    msg.style.color    = res.success ? '#4ade80' : '#f87171';
                }
                if (res.success) { form.reset(); }
            })
            .catch(function () {
                btn.disabled = false;
                btn.textContent = orig;
                if (msg) { msg.style.display = 'block'; msg.textContent = 'Request failed. Please try again.'; msg.style.color = '#f87171'; }
            });
    });
})();
</script>

</footer>