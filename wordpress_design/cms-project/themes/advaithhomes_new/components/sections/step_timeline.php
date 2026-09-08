<?php
/**
 * components/sections/step_timeline.php
 *
 * Cinematic alternating step timeline. Each card supports an optional
 * "expand" button that reveals additional detail bullet points in a smooth
 * accordion. Expandable cards are purely JS + CSS — no page reload.
 *
 * Props: $timeline {
 *   eyebrow, heading, subheading,
 *   highlight_step  int  (0-indexed, optional)
 *   trust_note      string (optional)
 *   steps[] {
 *     number, icon, title, description,
 *     url?        string  — makes card a link / shows primary CTA button
 *     cta_label?  string  — label for the CTA button (defaults to "Get started")
 *     points[]?   string[] — expandable bullet details shown on click
 *   }
 * }
 */
defined( 'ABSPATH' ) || exit;

$_t          = isset( $timeline ) && is_array( $timeline ) ? $timeline : array();
$_steps      = isset( $_t['steps'] ) && is_array( $_t['steps'] ) ? $_t['steps'] : array();
if ( empty( $_steps ) ) return;

$_eyb        = isset( $_t['eyebrow'] )        ? (string) $_t['eyebrow']      : '';
$_hdg        = isset( $_t['heading'] )        ? (string) $_t['heading']      : '';
$_sub        = isset( $_t['subheading'] )     ? (string) $_t['subheading']   : '';
$_highlight  = isset( $_t['highlight_step'] ) ? (int) $_t['highlight_step']  : -1;
$_trust_note = isset( $_t['trust_note'] )     ? (string) $_t['trust_note']   : '';
$_uniq       = 'st-' . substr( md5( uniqid( '', true ) ), 0, 6 );
$_anim_bg    = get_template_directory_uri() . '/assets/images/backgrounds/hiw-process-animation.gif';
?>
<section class="hiw-process-section">

    <?php /* Fixed Sticky Animated Video/GIF Background (Plays in loop throughout process scroll) */ ?>
    <div class="hiw-process-bg" aria-hidden="true">
        <div class="hiw-process-video-track">
            <img src="<?php echo esc_url( $_anim_bg ); ?>" alt="" class="hiw-process-video-bg" loading="eager" decoding="async" />
        </div>
        <div class="hiw-process-overlay"></div>
        <span class="hiw-pbg-orb hiw-pbg-orb--1"></span>
        <span class="hiw-pbg-orb hiw-pbg-orb--2"></span>
        <div class="hiw-pbg-grid"></div>
    </div>

    <div class="container">

        <?php adn_component( 'parts/section_headers/eyebrow_heading', array(
            'eyebrow'       => $_eyb,
            'heading'       => $_hdg,
            'subheading'    => $_sub,
            'wrapper_class' => 'hiw-process-header',
        ) ); ?>

        <div class="hiw-timeline" id="<?php echo esc_attr( $_uniq ); ?>">
            <?php foreach ( $_steps as $_i => $_s ) :
                $_num      = esc_html( isset( $_s['number'] ) ? (string) $_s['number'] : str_pad( (string)( $_i + 1 ), 2, '0', STR_PAD_LEFT ) );
                $_ico      = adn_icon( isset( $_s['icon'] ) ? (string) $_s['icon'] : '' );
                $_ttl      = esc_html( isset( $_s['title'] )       ? (string) $_s['title']       : '' );
                $_dsc      = esc_html( isset( $_s['description'] ) ? (string) $_s['description'] : '' );
                $_url      = isset( $_s['url'] ) ? (string) $_s['url'] : '';
                $_cta_lbl  = esc_html( isset( $_s['cta_label'] ) ? (string) $_s['cta_label'] : 'Get started' );
                $_points     = isset( $_s['points'] ) && is_array( $_s['points'] ) ? $_s['points'] : array();
                $_step_cards = isset( $_s['cards'] ) && is_array( $_s['cards'] ) 
                    ? $_s['cards'] 
                    : ( ( 3 === $_i || '04' === $_num ) && isset( $_t['investigation_cards'] ) && is_array( $_t['investigation_cards'] ) ? $_t['investigation_cards'] : array() );
                $_has_cards  = ! empty( $_step_cards );
                $_side       = ( 0 === $_i % 2 ) ? 'hiw-step--left' : 'hiw-step--right';
                $_last       = ( $_i === count( $_steps ) - 1 );
                $_is_hi      = ( $_highlight >= 0 && $_i === $_highlight );
                $_has_exp    = ! empty( $_points ) || $_has_cards;
                $_card_cls   = 'hiw-step-card';
                if ( $_is_hi )   { $_card_cls .= ' hiw-step-card--highlight'; }
                if ( $_has_exp ) { $_card_cls .= ' hiw-step-card--expandable'; }
                $_exp_id     = $_uniq . '-exp-' . $_i;
            ?>
            <div class="hiw-step <?php echo esc_attr( $_side ); ?><?php echo $_last ? ' hiw-step--last' : ''; ?>">
                <div class="hiw-step-node<?php echo $_is_hi ? ' hiw-step-node--highlight' : ''; ?>">
                    <span class="hiw-step-icon" aria-hidden="true"><?php echo $_ico; ?></span>
                    <span class="hiw-step-num"  aria-hidden="true"><?php echo $_num; ?></span>
                </div>
                <div class="<?php echo esc_attr( $_card_cls ); ?>">

                    <div class="hiw-step-body">
                        <?php if ( $_is_hi ) : 
                            $_badge_txt = isset( $_s['badge'] ) ? (string) $_s['badge'] : ( 0 === $_i ? 'Start Here' : 'Key Stage' );
                        ?>
                        <span class="hiw-step-card-badge" aria-label="Key step">
                            <?php echo adn_icon( 'fa-solid fa-star' ); ?> <?php echo esc_html( $_badge_txt ); ?>
                        </span>
                        <?php endif; ?>

                        <h3><?php echo $_ttl; ?></h3>
                        <p><?php echo $_dsc; ?></p>

                        <?php /* Action buttons row */ ?>
                        <?php if ( '' !== $_url || $_has_exp ) : ?>
                        <div class="hiw-step-actions">
                            <?php if ( '' !== $_url ) : ?>
                            <a href="<?php echo esc_url( adn_link( $_url ) ); ?>"
                               class="btn btn-primary hiw-step-cta">
                                <?php echo $_cta_lbl; ?> <span aria-hidden="true">&rarr;</span>
                            </a>
                            <?php endif; ?>

                            <?php if ( $_has_exp ) : ?>
                            <button class="hiw-step-expand-btn"
                                    aria-expanded="false"
                                    aria-controls="<?php echo esc_attr( $_exp_id ); ?>">
                                <span class="hiw-step-expand-label">See details</span>
                                <span class="hiw-step-expand-icon" aria-hidden="true">
                                    <?php echo adn_icon( 'fa-solid fa-chevron-down' ); ?>
                                </span>
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php /* Expandable details panel */ ?>
                        <?php if ( $_has_exp ) : ?>
                        <div class="hiw-step-details"
                             id="<?php echo esc_attr( $_exp_id ); ?>"
                             role="region"
                             aria-hidden="true">
                            <?php if ( $_has_cards ) : ?>
                            <div class="hiw-step-mini-grid">
                                <?php
                                foreach ( $_step_cards as $_ci => $_fc ) :
                                    $_fc_ico = isset( $_fc['icon'] ) ? (string) $_fc['icon'] : 'fa-solid fa-check';
                                    $_fc_ttl = isset( $_fc['title'] ) ? (string) $_fc['title'] : '';
                                    $_fc_txt = isset( $_fc['text'] ) ? (string) $_fc['text'] : '';
                                ?>
                                <div class="hiw-step-mini-card" style="--mc-delay: <?php echo (int)( $_ci * 50 ); ?>ms;">
                                    <span class="hiw-step-mini-icon" aria-hidden="true"><?php echo adn_icon( $_fc_ico ); ?></span>
                                    <div class="hiw-step-mini-content">
                                        <h4 class="hiw-step-mini-title"><?php echo esc_html( $_fc_ttl ); ?></h4>
                                        <p class="hiw-step-mini-text"><?php echo esc_html( $_fc_txt ); ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php elseif ( ! empty( $_points ) ) : ?>
                            <ul class="hiw-step-points">
                                <?php foreach ( $_points as $_pt ) : ?>
                                <li><?php echo esc_html( (string) $_pt ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ( '' !== $_trust_note ) : ?>
        <p class="hiw-timeline-trust-note">
            <span class="hiw-timeline-trust-icon" aria-hidden="true"><?php echo adn_icon( 'fa-solid fa-shield-halved' ); ?></span>
            <?php echo esc_html( $_trust_note ); ?>
        </p>
        <?php endif; ?>

    </div>
</section>

<script>
/* Expandable step cards — smooth accordion, no jank */
(function() {
    'use strict';
    var timeline = document.getElementById('<?php echo esc_js( $_uniq ); ?>');
    if (!timeline) return;
    var btns = timeline.querySelectorAll('.hiw-step-expand-btn');
    btns.forEach(function(btn) {
        var panelId = btn.getAttribute('aria-controls');
        var panel   = document.getElementById(panelId);
        if (!panel) return;

        /* Set initial max-height to 0 for CSS transition */
        panel.style.maxHeight = '0';
        panel.style.overflow  = 'hidden';

        btn.addEventListener('click', function() {
            var expanded = btn.getAttribute('aria-expanded') === 'true';
            if (expanded) {
                /* Collapse */
                panel.style.maxHeight = panel.scrollHeight + 'px';
                requestAnimationFrame(function() {
                    panel.style.maxHeight = '0';
                });
                btn.setAttribute('aria-expanded', 'false');
                panel.setAttribute('aria-hidden', 'true');
                btn.querySelector('.hiw-step-expand-label').textContent = 'See details';
            } else {
                /* Expand */
                panel.style.maxHeight = panel.scrollHeight + 'px';
                btn.setAttribute('aria-expanded', 'true');
                panel.setAttribute('aria-hidden', 'false');
                btn.querySelector('.hiw-step-expand-label').textContent = 'Show less';
                /* Remove max-height once transition ends so content can reflow freely */
                panel.addEventListener('transitionend', function once() {
                    if (btn.getAttribute('aria-expanded') === 'true') {
                        panel.style.maxHeight = 'none';
                    }
                    panel.removeEventListener('transitionend', once);
                });
            }
        });
    });
})();
</script>
